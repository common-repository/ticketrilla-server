<?php

namespace TTLS\Models;
use TTLS\Models\Model;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Product' ) ) {

	class Product extends Post {

		const POST_TYPE = 'ttls_product';

		public $type = 'theme';
		public $author_name = '';
		public $author_link = '';
		public $manual = '';
		public $terms = '';
		public $privacy = '';
		public $post_title = '';
		public $post_name = '';
		public $post_content = '';
		public $image = '';
		public $open_registration = '';
		public $licenses = array();

		function __construct( $data = false ) {
			parent::__construct( $data );
			$this->create_license_models();
		}

		private function create_license_models() {
			foreach ( self::license_models() as $license_key => $license_model ) {
				$license_data = empty( $this->licenses[$license_key] ) ? array() : $this->licenses[$license_key];
				$this->licenses[$license_key] = new $license_model( $license_data );
			}
		}

		public static function license_models(){
			$license_models = array('standard' => '\TTLS\Models\StandardLicense');
			return apply_filters( 'ttls_license_models', $license_models );
		}

		protected function prepare_post_meta_licenses( $licenses ) {
			$prepared_licenses = array();
			if ( is_array( $licenses ) ) {
				foreach ( $licenses as $license_key => $license_data ) {
					if ( $license_data instanceof Model ) {
						$prepared_licenses[$license_key] = $license_data->export_attr_array();
					}
				}
			}
			return $prepared_licenses;
		}

		public function main_attributes() {
			return array(
				'ID' => __( 'ID', 'ttls_translate' ),
				'post_title' => __( 'Product title', 'ttls_translate' ),
				'post_name' => __( 'Product slug', 'ttls_translate' ),
				'post_content' => __('Product description', 'ttls_translate'),
			);
		}

		public function meta_attributes() {
			return array(
				'type' => __( 'Product type', 'ttls_translate' ),
				'author_name' => __( 'Author name', 'ttls_translate' ),
				'author_link' => __( 'Developer URL', 'ttls_translate' ),
				'manual' => __( 'Manual URL', 'ttls_translate' ),
				'terms' => __( 'Terms of service URL', 'ttls_translate'),
				'privacy' => __('Privacy policy URL', 'ttls_translate'),
				'image' => __( 'Product image', 'ttls_translate' ),
				'open_registration' => __('Open registration', 'ttls_translate'),
				'licenses' => __( 'Active licenses', 'ttls_translate' ),
      );
		}

		public static function product_types() {
			return array(
				'theme' => esc_html__( 'Theme', 'ttls_translate'),
				'plugin' => esc_html__( 'Plugin', 'ttls_translate'),
				'html' => esc_html__( 'HTML', 'ttls_translate'),
				'design' => esc_html__( 'Design-Mockup', 'ttls_translate'),
				'other' => esc_html__( 'Other', 'ttls_translate'),
			);
		}

		public function rules() {
			return array(
				array(
					array('type', 'author_name', 'author_link', 'manual', 'terms', 'privacy', 'post_title'),
					'required',
				),
				array(
					array('type'),
					'in_list',
					array_keys( static::product_types() ),
				),
				array(
					array('author_link', 'manual', 'terms', 'privacy'),
					'url',
				),
      );
		}

		public function active_licenses() {
			return array_filter( $this->licenses, function( $license ) {
				return $license instanceof Model && $license->enabled;
			} );
		}

		public function validate( $clear_errors = true ) {
			$valid = parent::validate( $clear_errors );
			if ( ! empty( $this->licenses ) && is_array( $this->licenses ) ) {
				foreach ( $this->licenses as $license_data ) {
					if ( $license_data instanceof Model && $license_data->enabled && ! $license_data->validate() ) {
						$valid = false;
					}
				}
			}
			return $valid;
		}

		public static function find_all_available( $filter_key = false ) {
			$condition = array();
			if ( $filter_key && array_key_exists( $filter_key, static::product_types() ) ) {
				$condition['meta_query'] = array(
					array(
						'key' => 'ttls_type',
						'value' => $filter_key,
					),
				);
			}
			return static::find_all( $condition );
		}

  }
}