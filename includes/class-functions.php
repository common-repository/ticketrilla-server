<?php
	// Exit if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	if ( ! class_exists( 'TTLS_Functions' ) ) {

		class TTLS_Functions {

			var $options;


			/**
			 * @var array variable for Flags
			 */
			var $screenload_flags;


			function __construct() {

				$this->init_variables();

			}


			/**
			 * What type of request is this?
			 *
			 * @param string $type String containing name of request type (ajax, frontend, cron or admin)
			 *
			 * @return bool
			 */
			public function is_request( $type ) {
				switch ( $type ) {
					case 'admin' :
						return is_admin();
					case 'ajax' :
						return defined( 'DOING_AJAX' );
					case 'cron' :
						return defined( 'DOING_CRON' );
					case 'frontend' :
						return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
				}

				return false;
			}


			/**
			 * Set variables
			 */
			function init_variables() { return false; }


			/**
			 * Locate a template and return the path for inclusion.
			 *
			 * @access public
			 *
			 * @param string $template_name
			 * @param string $path (default: '')
			 *
			 * @return string
			 */
			function locate_template( $template_name, $path = '' ) {
				// check if there is a template at theme folder
				$template = locate_template( array(
					trailingslashit( 'ticketrilla-server/' . $path ) . $template_name
				) );

				if ( ! $template ) {
					if ( $path ) {
						$template = trailingslashit( trailingslashit( WP_PLUGIN_DIR ) . $path );
					} else {
						$template = trailingslashit( TTLS_PATH );
					}
					$template .= 'templates/' . $template_name;
				}

				// Return what we found.
				return apply_filters( 'ttls_locate_template', $template, $template_name, $path );
			}

			function _get_list_table( $class ) {
				if ( ! class_exists( 'WP_List_Table' ) ) {
					require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
				}
				require_once( TTLS_PATH . 'includes/admin/core/list-tables/class-wp-list-table-' . strtolower( $class ) . '.php' );
				$class = "\WP_List_Table_{$class}";
				return new $class();
			}
		}

	}