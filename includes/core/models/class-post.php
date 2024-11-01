<?php

namespace TTLS\Models;
use TTLS\Models\Model;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Post' ) ) {

	class Post extends Model {
		
		const POST_TYPE = 'post';
		const META_PREFIX = 'ttls';

		protected $_wp_post_obj;

		function __construct( $data = false ) {
			if ( $data ) {
				if ( is_object( $data ) ) {
					if ( $data instanceof \WP_Post ) {
						$this->import_wp_post( $data );
					}
				} else {
					$this->import_array( $data  );
				}
			}
		}
		
		public function import_wp_post( $data ) {
			if ( $data instanceof \WP_Post ) {
				$attribute_names = $this->main_attribute_names();
				foreach ( $attribute_names as $attribute ) {
					if( isset( $data->$attribute ) ) {
						$this->$attribute = $data->$attribute;
					}
				}
				$meta_attribute_names = $this->meta_attribute_names();
				foreach ( $meta_attribute_names as $meta_attribute ) {
					$meta_attribute_key = static::get_meta_key( $meta_attribute );
					$load_method = 'load_post_meta_' . $meta_attribute;
					if ( method_exists( $this, $load_method ) ) {
						$this->$meta_attribute = call_user_func( array($this, $load_method), $data->ID, $meta_attribute_key );
					} else {
						$this->$meta_attribute = get_post_meta( $data->ID, $meta_attribute_key, true );
					}
				}
				$this->_wp_post_obj = $data;
			}
		}

		public function main_attributes() {
			return array();
		}

		public function meta_attributes() {
			return array();
		}

		public function attributes() {
			return array_merge( $this->main_attributes(), $this->meta_attributes() );
		}

		public function main_attribute_names() {
			return array_keys( $this->main_attributes() );
		}

		public function meta_attribute_names() {
			return array_keys( $this->meta_attributes() );
		}

		public function attribute_names() {
			return array_keys( $this->attributes() );
		}

		public function export_main_attr_array() {
			return $this->export_array( $this->main_attribute_names() );
		}

		public function save( $run_validation = true ) {
			if ( $run_validation && ! $this->validate() ) {
				return new \WP_Error( 'ttls_save_validation_error', __( 'Post not saved due to validation error', 'ttls_translate' ) );
			}

			$post_data = $this->prepare_post_data();
			$result = wp_insert_post( wp_slash( $post_data ), true );
			
			if ( ! is_wp_error( $result ) ) {
				$this->ID = $result;
			}
			
			return $result;
		}
		
		protected function prepare_post_data() {
			$post_data = array_merge( $this->default_post_data(), $this->export_main_attr_array() );
			$post_data['post_title'] = empty( $post_data['post_title'] ) ? '' : wp_strip_all_tags( $post_data['post_title'] );
			$post_data['post_type' ] = static::POST_TYPE;
			$post_data['meta_input'] = $this->prepare_post_meta();    		
    	return $post_data;
		}

		protected function prepare_post_meta() {
			$post_meta = array();
			foreach( $this->meta_attribute_names() as $attribute ) {
				if ( isset( $this->$attribute ) ) {
					$meta_key = static::get_meta_key( $attribute );
					$prepare_method = 'prepare_post_meta_' . $attribute;
					if ( method_exists( $this, $prepare_method ) ) {
						$post_meta[$meta_key] = call_user_func( array( $this, $prepare_method), $this->$attribute );
					} else {
						$post_meta[$meta_key] = $this->$attribute;
					}
				}
			}
			return $post_meta;
		}

		protected function default_post_data() {
			return array(
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_status'    => 'publish',
			);
		}
		
		public function trash() {
			if ( ! wp_trash_post( $this->id ) ) {
				return new \WP_Error( 'ttls_trash_error', __( 'Post not trashed', 'ttls_translate' ) );
			}
			return true;
		}
		
		public function untrash() {
			if ( ! wp_untrash_post( $this->id ) ) {
				return new \WP_Error( 'ttls_untrash_error', __( 'Post not untrashed', 'ttls_translate' ) );
			}
			return true;
		}

		protected static function get_meta_key( $attribute ) {
			return static::META_PREFIX ? static::META_PREFIX . '_' . $attribute : $attribute;
		}

		public static function find( $condition = array() ) {
			$args = array(
				'post_type' => static::POST_TYPE,
			);
			$query = new \WP_Query( array_merge_recursive( $args, $condition ) );
			$posts = array();
			foreach( $query->posts as $wp_post ) {
				$posts[] = new static( $wp_post );
			}
			return array(
				'items' => $posts,
				'total' => $query->found_posts,
			);
		}
		
		public static function find_one( $condition = array() ) {
			return static::find( array_merge( $condition, array('posts_per_page' => 1) ) );
		}			

		public static function find_all( $condition = array() ) {
			return static::find( array_merge( $condition, array('posts_per_page' => -1, 'nopaging' => true ) ) );
		}			

		public static function find_by_id( $id ) {
			$wp_post = get_post( $id );
			if ( $wp_post ) {
				return new static( $wp_post );
			}
			return false;
		}
	}
}