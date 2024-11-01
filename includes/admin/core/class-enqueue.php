<?php
	// Exit if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	if ( ! class_exists( 'TTLS_Admin_Enqueue' ) ) {

		class TTLS_Admin_Enqueue {
			var $js_url;
			var $css_url;
			var $libs;

			function __construct() {
				$this->js_path = 'includes/admin/assets/js/';
				$this->css_path = 'includes/admin/assets/css/';
				$this->js_url = TTLS_URL . $this->js_path;
				$this->css_url = TTLS_URL . $this->css_path;

				add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

			}

			/**
			 * @register scripts and styles
			 */
			function admin_enqueue_scripts() {
				if ( TTLS()->admin_page()->is_ttls() ) {
					$this->register_css();
					$this->register_js();
				}
			}

			/**
			 * @register Admin Page Scripts
			 */
			function register_js() {
				wp_enqueue_script( 'jquery');
				wp_enqueue_script( 'jquery-ui' );
				wp_enqueue_script( 'jquery-ui-datepicker');
				if ( ! did_action( 'wp_enqueue_media' ) ) {
					wp_enqueue_media();
				}
				$array_scripts = array(
					'bootstrap' => 'bootstrap.min.js',
					'ckeditor' => 'ckeditor/ckeditor.js',
					'ticketrilla' => 'ticketrilla.js',
				);
				foreach ( $array_scripts as $key => $value ) {
					wp_enqueue_script( "ttls_admin_{$key}", $this->js_url . $value, array( 'jquery', 'jquery-ui-datepicker', 'heartbeat' ), filemtime( TTLS_PATH . $this->js_path . $value ), true );
				}

				$add_scripts = apply_filters( 'ttls_enqueue_scripts', array() );
				foreach ( $add_scripts as $key => $value ) {
					wp_enqueue_script( "ttls_admin_{$key}", $value, array( 'jquery', 'jquery-ui-datepicker' ), TTLS_PLUGIN_VERSION, true );
				}

				wp_localize_script( 'ttls_admin_ticketrilla', 'TTLSL', $this->localized_strings() );
			}

			/**
			 * @register Admin Page Styles
			 */
			function register_css() {

				$array_styles = array(
					'styles' => 'main.css',
				);
				foreach ( $array_styles as $key => $value ) {
					wp_register_style( "ttls_admin_{$key}", $this->css_url . $value, array(), filemtime( TTLS_PATH . $this->css_path . $value ) );
					wp_enqueue_style( "ttls_admin_{$key}" );
				}

				$add_styles = apply_filters( 'ttls_enqueue_styles', array() );
				foreach ( $add_styles as $key => $value ) {
					wp_register_style( "ttls_admin_{$key}", $value, array(), TTLS_PLUGIN_VERSION );
					wp_enqueue_style( "ttls_admin_{$key}" );
				}

			}

			private function localized_strings() {
				return array(
					'license_delete_confirmation' => esc_html__( 'Are you sure you want to delete this license?', 'ttls_translate' ),
				);
			}
		}
	}