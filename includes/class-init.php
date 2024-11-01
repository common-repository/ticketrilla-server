<?php

	// Exit if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	if ( ! class_exists( 'TTLS' ) ) {
		/**
		 * Main TTLS Class
		 *
		 * @class TTLS
		 * @version 1.0
		 *
		 */
		final class TTLS extends TTLS_Functions {

			/**
			 * @var TTLS the single instance of the class
			 */
			protected static $instance = null;


			/**
			 * @var array all plugin's classes
			 */
			public $classes = array();


			/**
			 * Main TTLS Instance
			 *
			 *
			 * @since 1.0
			 * @static
			 * @see TTLS()
			 * @return TTLS - Main instance
			 */
			static public function instance() {
				if ( is_null( self::$instance ) ) {
					self::$instance = new self();
				}

				return self::$instance;
			}


			/**
			 * Create plugin classes - not sure if this is required!!!
			 *
			 * @since 1.0
			 * @see TTLS()
			 *
			 * @param       $name
			 * @param array $params
			 *
			 * @return mixed
			 */
			public function __call( $name, array $params ) {

				if ( empty( $this->classes[ $name ] ) ) {
					$this->classes[ $name ] = apply_filters( 'ttls_call_object_' . $name, false );
				}

				return $this->classes[ $name ];

			}

			/**
			 * Function for add classes to $this->classes
			 * for run using TTLS()
			 *
			 * @since 2.0
			 *
			 * @param string $class_name
			 * @param bool   $instance
			 */
			public function set_class( $class_name, $instance = false ) {
				if ( empty( $this->classes[ $class_name ] ) ) {
					$class                        = 'TTLS_' . $class_name;
					$this->classes[ $class_name ] = $instance ? $class::instance() : new $class;
				}
			}


			/**
			 * Cloning is forbidden.
			 *
			 * @since 1.0
			 */
			public function __clone() {
				_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'ttls_translate' ), '1.0' );
			}


			/**
			 * Unserializing instances of this class is forbidden.
			 *
			 * @since 1.0
			 */
			public function __wakeup() {
				_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'ttls_translate' ), '1.0' );
			}


			/**
			 * TTLS constructor.
			 *
			 * @since 1.0
			 */
			function __construct() {
				parent::__construct();

				//register loader for including TTLS classes
				$this->ttls_class_loader();

				if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {

					// include TTLS classes
					$this->includes();

					// include hook files
					add_action( 'plugins_loaded', array( $this, 'init' ), 0 );

					//include short non class functions
					require_once 'short-functions.php';
				}
			}

			/**
			 * Loader TTLS classes handler
			 *
			 * @since 1.0
			 *
			 * @param $class
			 */
			function ttls_class_loader() {
				require_once 'core/models/class-model.php';
				require_once 'core/models/class-post.php';
				require_once 'core/models/class-ticket.php';
				require_once 'core/models/class-response.php';
				require_once 'core/models/class-license.php';
				require_once 'core/models/class-standard-license.php';
				require_once 'core/models/class-product.php';
				require_once 'core/models/class-client-product.php';
				require_once 'core/services/class-service.php';
				require_once 'core/services/class-base-ticket-service.php';
				require_once 'core/services/class-ticket-service.php';
				require_once 'core/services/class-response-service.php';
				require_once 'core/helpers/class-html.php';
				require_once 'core/helpers/class-form-errors.php';
				require_once 'core/helpers/class-table.php';
				require_once 'core/helpers/class-breadcrumbs.php';
				require_once 'core/helpers/class-filter.php';
				require_once 'core/helpers/class-list-filter.php';
				require_once 'core/helpers/class-dropdown-filter.php';
				require_once 'core/helpers/class-product-dropdown-filter.php';
				require_once 'core/helpers/class-license-dropdown-filter.php';
				require_once 'core/helpers/class-product-filter.php';
				require_once 'core/helpers/class-licenses-type-filter.php';
				require_once 'core/helpers/class-ticket-html.php';
				require_once 'core/helpers/class-response-html.php';
				require_once 'core/class-common.php';
				require_once 'core/class-link.php';
				require_once 'core/class-capabilities.php';
				require_once 'core/class-roles.php';
				require_once 'core/class-users.php';
				require_once 'core/class-licenses.php';
				require_once 'core/class-attachments.php';
				require_once 'core/class-widget.php';
				if(file_exists( __DIR__ . '/core/modules/class-module.php' )) {
					require_once 'core/modules/class-module.php';
					require_once 'core/modules/license-envato/license-envato.php';
					require_once 'core/modules/newsletters/newsletters.php';
					require_once 'core/modules/guest-access/guest-access.php';
					require_once 'core/modules/client-info/client-info.php';
				}

				//Admin
				require_once 'admin/core/class-enqueue.php';
				require_once 'admin/core/class-page.php';
			}

			/**
			 * Include required core files used in admin and on the frontend.
			 *
			 * @since 1.0
			 *
			 * @return void
			 */
			public function includes() {
				$this->common();
				$this->link();
				$this->users();
				$this->roles();
				

				if ( $this->is_request( 'ajax' ) || $this->is_request( 'admin' ) ) {
					$this->admin_enqueue();
					$this->admin_page();
				}

			}

			/**
			 * @return TTLS_Enqueue()
			 */
			function enqueue() {
				if ( empty( $this->classes['enqueue'] ) ) {
					$this->classes['enqueue'] = new TTLS_Enqueue();
				}

				return $this->classes['enqueue'];
			}

			/**
			 * @return TTLS_Common()
			 */
			function common() {
				if ( empty( $this->classes['common'] ) ) {
					$this->classes['common'] = new TTLS_Common();
				}

				return $this->classes['common'];
			}

			/**
			 * @return TTLS_Link()
			 */
			function link() {
				if ( empty( $this->classes['link'] ) ) {
					$this->classes['link'] = new TTLS_Link();
				}

				return $this->classes['link'];
			}

			/**
			 * @return TTLS_Roles()
			 */
			function roles() {
				if ( empty( $this->classes['roles'] ) ) {
					$this->classes['roles'] = new TTLS_Roles();
				}

				return $this->classes['roles'];
			}


			/**
			 * @return TTLS_Admin_Enqueue()
			 */
			function admin_enqueue() {
				if ( empty( $this->classes['admin_enqueue'] ) ) {
					$this->classes['admin_enqueue'] = new TTLS_Admin_Enqueue();
				}

				return $this->classes['admin_enqueue'];
			}

			/**
			 * @return TTLS_Admin_Page()
			 */
			function admin_page() {
				if ( empty( $this->classes['admin_page'] ) ) {
					$this->classes['admin_page'] = new TTLS_Admin_Page();
				}

				return $this->classes['admin_page'];
			}

			/**
			 * @return TTLS\Services\TicketService()
			 */
			public function ticket_service() {
				if ( empty( $this->classes['ticket_service'] ) ) {
					$this->classes['ticket_service'] = new TTLS\Services\TicketService();
				}

				return $this->classes['ticket_service'];
			}

			/**
			 * @return TTLS\Services\ResponseService()
			 */
			public function response_service() {
				if ( empty( $this->classes['response_service'] ) ) {
					$this->classes['response_service'] = $this->ticket_service()->get_response_service();
				}

				return $this->classes['response_service'];
			}
			
			function init() {
				require_once 'core/ttls-actions.php';
				require_once 'core/ttls-filters.php';
			}

			public function on_activation() {
				$this->roles()->create_roles();
				$this->roles()->add_all_capabilities_to_admin();
			}

			public function on_deactivation() {

			}
		}
	}

	/**
	 * Function for calling TTLS methods and variables
	 *
	 * @return TTLS
	 */
	function TTLS() {
		return TTLS::instance();
	}

	// Global for backwards compatibility.
	$GLOBALS['TTLS'] = TTLS();