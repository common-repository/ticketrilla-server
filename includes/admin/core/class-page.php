<?php
	// Exit if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	if ( ! class_exists( 'TTLS_Admin_Page' ) ) {

		class TTLS_Admin_Page {

			/**
			 * @var string
			 */
			var $pagehook;
			var $subpages;
			var $template_path;

			function __construct() {
				$this->template_path = TTLS_PATH . 'includes/admin/templates/page/';
				$this->slug     = 'ticketrilla-server';
				
				add_action( 'admin_menu', array( $this, 'primary_admin_menu' ), 0 );
				add_action( 'admin_menu', array( $this, 'secondary_menu_items' ), 1000 );

				add_action( 'ttls_extend_admin_menu', array( $this, 'ttls_extend_admin_menu' ) );

				add_action( 'wp_ajax_ttls_update_option', array( $this, 'update_option' ) );
				add_action( 'wp_ajax_ttls_update_settings', array( $this, 'update_settings' ) );
				add_action( 'wp_ajax_ttls_update_profile', array( $this, 'update_profile' ) );

				// a presence check for required email variables
				add_filter( 'ttls_check_option-ttls_password_reset_message', array( $this, 'check_reset_pass' ) );
				add_filter( 'ttls_check_option-ttls_password_reset_code_message', array( $this, 'check_reset_key' ) );
				// update used licenses
				add_filter( 'ttls_check_option-ttls_active_licenses_standard', array( $this, 'check_active_licenses' ) );
				add_filter( 'ttls_check_option-ttls_product_description', array( $this, 'check_product_description' ) );
				add_filter( 'ttls_check_option-ttls_notifications_telegram_token', array( $this, 'check_notifications_telegram_token') );
				add_filter( 'ttls_check_option-ttls_notifications_telegram_chat_id', array( $this, 'check_notifications_telegram_chat_id') );
			}

			function check_reset_pass( $data ){
				if ( strripos ( $data['value'], '%password%' ) === false ) {
					return array( 'error' => esc_html__('The password variable is not specified in the body text', 'ttls_translate') );
				}
				return $data;
			}

			function check_reset_key( $data ){
				if ( strripos ( $data['value'], '%secure_code%' ) === false ) {
					return array( 'error' => esc_html__('The reset-key variable is not specified in the body text', 'ttls_translate') );
				}
				return $data;
			}
			
			function check_product_description( $data ) {
				$data = wp_unslash( $data );
				return $data;
			}
			
			public function check_notifications_telegram_token( $data ) {
				$check = ttls_telegram_get_me( $data['value'] );
				if ( $check ) {
					$check = json_decode( $check );
				}
				if ( empty( $check->ok ) || ! $check->ok ) {
					return array( 'error' => esc_html__('Token is invalid', 'ttls_translate') );
				}
				return $data;
			}
			
			public function check_notifications_telegram_chat_id( $data ) {
				$token = get_option( 'ttls_notifications_telegram_token', '' );
				
				if ( ! $token ) {
					return array( 'error' => esc_html__('First enter valid token', 'ttls_translate') );
				}
				
				$check = ttls_telegram_get_chat( $token, $data['value'] );
				if ( $check ) {
					$check = json_decode( $check );
				}
				if ( empty( $check->ok ) || ! $check->ok ) {
					return array( 'error' => esc_html__('Chat ID is invalid', 'ttls_translate') );
				}
				return $data;
			}

			function update_profile(){
				parse_str( $_POST['fields'], $fields );
				$message = '';
				$user_id = get_current_user_id();

				foreach ( $fields as $fields_key => $fields_val) {
					switch ( $fields_key ) {
						case 'first_name':
							$first_name = sanitize_text_field( $fields['first_name'] );
							if ( get_user_meta( $user_id, 'first_name', true) != $first_name ) {
								if ( !update_user_meta( $user_id, 'first_name', $first_name ) ) {
									$errors['first_name'] = esc_html__('Errors with updating', 'ttls_translate');
								}
							}
							break;
						case 'nickname':
							$nickname = sanitize_text_field( $fields['nickname'] );
							if ( get_user_meta( $user_id, 'nickname', true) != $nickname ) {

								if ( !update_user_meta( $user_id, 'nickname', $nickname ) ) {
									$errors['nickname'] = esc_html__('Errors with updating', 'ttls_translate');
								}
							}
							break;
						case 'email':
							$email = sanitize_email( $fields['email'] );
							if ( !is_email( $email ) ) {
								$errors['email'] = esc_html__('It is not e-mail', 'ttls_translate');
							}
							$userdata = array(
								'ID' => $user_id,
								'user_email' => $email,
							);
							$user_update = wp_update_user( $userdata );
							if ( is_wp_error( $user_update ) ) {
								$errors['email'] = esc_html__( $user_update->get_error_message(), 'ttls_translate');
							}
							break;
						case 'ttls_user_password':
							$ttls_user_password = sanitize_text_field( $fields['ttls_user_password'] );
							$userdata = array(
								'ID' => $user_id
							);
							if ( empty( $ttls_user_password ) ) {
								$errors['password'] = esc_html__('New password is empty', 'ttls_translate');
								break;
							} else {
								$userdata['user_pass'] = $ttls_user_password;
							}
							$user_update = wp_update_user( $userdata );
							if ( !is_wp_error( $user_update ) ) {
								$message .= '<br><pre>'.esc_html__( 'New password', 'ttls_translate' ).': '.esc_html( $userdata['user_pass'] ).'</pre>';
							} else {
								$errors['password'] = esc_html__( $user_update->get_error_message(), 'ttls_translate');
							}
							break;
					}
				}
				if ( empty( $errors ) ) {
					wp_send_json_success( array( 'message' => esc_html__('Updated! ', 'ttls_translate').wp_kses_post( $message ) ) );
				} else {
					wp_send_json_error( array( 'message' => esc_html__('Some problems with updating', 'ttls_translate'), 'errors' => $errors ) );
				}
			}

			public function admin_save_product() {
				check_ajax_referer( 'ttls_admin_save_product', '_wpnonce' );
				$filtered_data = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
				$product = new \TTLS\Models\Product( $filtered_data );
				$errors = false;

				if ( $product->validate() ) {
					$save_product = $product->save();
					if ( is_wp_error( $save_product ) ) {
						$errors = true;
						$product->add_error( '_global', __( 'Error on saving product', 'ttls_translate' ) );
					} else {
						$product = \TTLS\Models\Product::find_by_id( $save_product );
					}
				} else {
					$errors = true;
					$product->add_error( '_global', __( 'One or more fields are filled incorrectly', 'ttls_translate' ) );
				}

				$html = $this->buffer_template( 'ticketrilla-server-save-product', array('product' => $product, 'status' => ! $errors) );

				if ( $errors ) {
					wp_send_json_error( array('html' => $html ) );
				}
				wp_send_json_success( array('html' => $html ) );
			}

			function update_settings(){
				parse_str( $_POST['fields'], $fields );
				if ( current_user_can( 'ttls_plugin_admin' ) ){

					$errors = array();

					foreach ( $fields as $key => $value) {
						$sanitize = apply_filters( 'ttls_check_option-'.$key, array(
							'value' => $value,
							'old' => get_option( $key )
						));
						if ( !empty( $sanitize['error'] ) ) {
							$errors[$key] = $sanitize['error'];
						} else {
							if ( empty( $sanitize['value'] ) ) {
								if ( get_option( $key ) ) {
									if ( !delete_option( $key ) ) {
										$errors[$key] = esc_html__( 'Encountered an error while updating', 'ttls_translate' );
									}
								}
							} else {

								if ( $sanitize['value'] != get_option( $key ) ) {
									if ( !update_option( $key, $sanitize['value'] ) ) {
										$errors[$key] = esc_html__( 'Encountered an error while updating', 'ttls_translate' );
									}
								}
							}
						}
					}

					if ( !empty( $errors ) ) {
						wp_send_json_error( array(
							'message' => esc_html__( 'Encountered an error while updating', 'ttls_translate' ),
							'errors' => $errors
						) );
					} else {
						wp_send_json_success( array(
							'message' => esc_html__('Settings updated', 'ttls_translate'),
						) );
					}
				} else {
					wp_send_json_error( array(
						'message' => esc_html__('You do not have sufficient rights for these changes', 'ttls_translate'),
					) );
				}
				wp_die();
			}

			function is_ttls() {
				if ( ! empty( $_GET['page'] ) && strpos( $_GET['page'], $this->slug ) === false ) {
					return false;
				} else if ( ! empty( $_GET['page'] ) ) {
					return true;
				}
			}

			/**
			 * Setup admin menu
			 */
			function primary_admin_menu() {

				if ( current_user_can( 'ttls_clients' ) ) {
					$tickets_count = get_user_meta( get_current_user_id(), 'ttls_replied_tickets_count', true );
				} else {
					$tickets_count = get_option( 'ttls_pending_tickets_count' );
				}
				
				
				$this->subpages = apply_filters( 'ttls_admin_subpages', array(
					'ttls-dashboard'  => array(
						'parent_slug' => $this->slug,
						'page_title'  => esc_html__( 'Dashboard', 'ttls_translate' ),
						'menu_title'  => esc_html__( 'Dashboard', 'ttls_translate' ),
						'capability'  => 'ttls_developers',
						'menu_slug'   => $this->slug,
						'function'    => array( $this, 'admin_page_render' )
					),
					'ttls-products'  => array(
						'parent_slug' => $this->slug,
						'page_title'  => esc_html__( 'Products', 'ttls_translate' ),
						'menu_title'  => esc_html__( 'Products', 'ttls_translate' ),
						'capability'  => 'ttls_clients',
						'menu_slug'   => $this->slug . '-products',
						'function'    => array( $this, 'admin_page_render' )
					),
					'ttls-tickets'  => array(
						'parent_slug' => $this->slug,
						'page_title'  => esc_html__( 'Tickets', 'ttls_translate' ),
						'menu_title'  => esc_html__( 'Tickets', 'ttls_translate' ) . ttls_pending_count_html( $tickets_count, 'tickets' ),
						'capability'  => 'ttls_see_tickets',
						'menu_slug'   => $this->slug . '-tickets',
						'function'    => array( $this, 'admin_page_render' )
					),
					'ttls-users'  => array(
						'parent_slug' => $this->slug,
						'page_title'  => esc_html__( 'Users', 'ttls_translate' ),
						'menu_title'  => esc_html__( 'Users', 'ttls_translate' ),
						'capability'  => 'ttls_plugin_admin',
						'menu_slug'   => $this->slug . '-users',
						'function'    => array( $this, 'admin_page_render' )
					),
					'ttls-licences' => array(
						'parent_slug' => $this->slug,
						'page_title'  => esc_html__( 'Licenses', 'ttls_translate' ),
						'menu_title'  => esc_html__( 'Licenses', 'ttls_translate' ),
						'capability'  => 'ttls_plugin_admin',
						'menu_slug'   => $this->slug . '-licences',
						'function'    => array( $this, 'admin_page_render' )
					),
					'ttls-general-settings' => array(
						'parent_slug' => $this->slug,
						'page_title'  => esc_html__( 'General settings', 'ttls_translate' ),
						'menu_title'  => esc_html__( 'General settings', 'ttls_translate' ),
						'capability'  => 'ttls_plugin_admin',
						'menu_slug'   => $this->slug . '-general-settings',
						'function'    => array( $this, 'admin_page_render' )
					),
					'ttls-product-settings' => array(
						'parent_slug' => $this->slug,
						'page_title'  => esc_html__( 'Product settings', 'ttls_translate' ),
						'menu_title'  => esc_html__( 'Product settings', 'ttls_translate' ),
						'capability'  => 'ttls_plugin_admin',
						'menu_slug'   => $this->slug . '-product-settings',
						'function'    => array( $this, 'admin_page_render' )
					),
				), $this->slug );
				$this->pagehook = add_menu_page( esc_html__( 'Ticketrilla Server', 'ttls_translate' ), esc_html__( 'Ticketrilla Server', 'ttls_translate' ), 'ttls_developers', $this->slug, array(
					$this,
					'admin_page_render'
				), 'dashicons-welcome-learn-more' );

			}

			/**
			 * Secondary admin menu
			 */
			function secondary_menu_items() {
				do_action( 'ttls_extend_admin_menu' );
			}

			function ttls_extend_admin_menu() {
				if ( ! empty( $this->subpages ) ) {
					foreach ( $this->subpages as $subpage ) {
						$position = empty( $subpage['position'] ) ? null : $subpage['position'];
						add_submenu_page( $subpage['parent_slug'], $subpage['page_title'], $subpage['menu_title'],
							$subpage['capability'], $subpage['menu_slug'], $subpage['function'], $position );
					}
				}
			}


			function admin_page_render() {
				$page = sanitize_text_field( $_REQUEST['page'] );
				$this->render_template_page( $page );
				do_action( 'ttls_page_render', $page );
			}

			public function buffer_template( $template_name, $data = array() ) {
				ob_start();
				$this->render_template_page( $template_name, $data );
				return ob_get_clean();
			}

			public function render_template_page( $template_name, $data = array() ) {
				$file_name = TTLS_PATH . 'includes/admin/templates/page/';
				$file_name .= $template_name;
				$file_name .= '.php';
				if ( file_exists( $file_name ) ) {
					require $file_name;
				}
			}
		}
	}