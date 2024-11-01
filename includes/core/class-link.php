<?php
	// Exit if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	if ( ! class_exists( 'TTLS_Link' ) ) {

		class TTLS_Link {

			var $routes;

			function __construct() {
				$this->routes = array(
					// receive ticket with responses and attachments
					// add new ticket/response
					// receive a list of tickets
					'ticket'    => array(
						'methods'  => 'POST',
						'callback' => array( $this, 'ticket' ),
						'permission_callback' => function () {
							return current_user_can( 'ttls_clients' );
						},
					),
					// receive user data
					'user'    => array(
						'methods'  => 'POST',
						'callback' => array( $this, 'user' ),
						'permission_callback' => '__return_true',
					),
					// receive server data
					'server'    => array(
						'methods'  => 'POST',
						'callback' => array( $this, 'server' ),
						'permission_callback' => '__return_true',
					)
				);
				add_action( 'rest_api_init', array( $this, 'create_routes' ) );
			}



			/**
			 * Creates routes.
			 */
			function create_routes() {

				$all_routes = $this->routes;
				$new_routes = array();

				$all_routes = array_merge( $all_routes, apply_filters( 'ttls_add_custom_routes', $new_routes ) );

				foreach ( $all_routes as $r_key => $r_route ) {

					if ( ! empty( $r_route['methods'] ) && ! empty( $r_route['callback'] ) ) {

						register_rest_route( 'ttls/v1', '/' . $r_key, array(
							'methods'  => $r_route['methods'],
							'callback' => $r_route['callback'],
							'permission_callback' => empty( $r_route['permission_callback'] ) ? '__return_true' : $r_route['permission_callback'],
						) );
					}

				}
			}

			public function get_user_info( $route, $mode = false ) {
				$user_info = array();
				// a check for mode presence
				// rights verification
				$user_info['mode'] = ( ! empty( $mode ) ) ? $mode : ( ! empty( $_POST['mode'] ) ? sanitize_text_field( $_POST['mode'] ) : '' );
				// a check for license type presence
				if ( empty( $_POST['license_token'] ) ) {
					return new WP_Error( 'ttls_license_notoken', 'Send purchase code', array( 'status' => 400 ) );
				}

				$user_info['license_token'] = sanitize_text_field( $_POST['license_token'] );

				// a check for token presence
				if ( empty( $_POST['license_type'] ) ) {
					return new WP_Error( 'ttls_license_notype', 'Send license type', array( 'status' => 400 ) );
				}

				$user_info['license_type'] = apply_filters( 'ttls_link_license_type', sanitize_text_field( $_POST['license_type'] ), $route, $mode );

				// assemble the data from the request origin
				$user_info['ttls_servers'] = $_SERVER['REMOTE_ADDR'];

				$user_agent = explode(';', $_SERVER['HTTP_USER_AGENT']);
				// $user_agent[0] - generator - WP version
				$host = parse_url( trim( $user_agent[1] ), PHP_URL_HOST );
				if ( ! empty( $host ) ) {								
					$url_path = parse_url( trim( $user_agent[1] ), PHP_URL_PATH );
					$user_info['ttls_site_urls'] = empty( $url_path ) ? $host : $host . $url_path;
				}
				return $user_info;
			}

			/**
			 * a function for verifying user's rights for actions of REST
			 *
			 * @param      string                  $route  The route
			 * @param      string                  $mode   The mode
			 *
			 * @return     WP_Error|boolean|array  Error, true, or data array
			 */
			function can( $route, $mode = false ){
				$error = new WP_Error;

				$user_info = $this->get_user_info( $route, $mode );

				if ( is_wp_error( $user_info ) ) {
					return $user_info;
				}

				if ( empty( $user_info['ttls_servers'] ) ) {
					$error->add( 'ttls_license_noserver', 'The server has not been identified', array( 'status' => 500 ) );
					return $error;
				}

				if ( empty( $user_info['ttls_site_urls'] ) ) {

					$error->add( 'ttls_license_nourl', 'The URL has not been identified', array( 'status' => 500 ) );
					return $error;
				}

				// required checks depending on route and mode
				switch ( $route ) {

					case 'user':
						switch ( $user_info['mode'] ) {
							case 'register':
								$error->add( 'ttls_nofunction', 'Missing function for '.$route.' - mode -'.$user_info['mode'], array( 'status' => 404 ) );
								return $error;
								break;
							case 'license':
								if ( empty( $_POST['product_id'] )  ) {
									$error->add( 'ttls_license_noproduct', 'Send product ID', array( 'status' => 400 ) );
									return $error;
								}
								$product_id = sanitize_key( $_POST['product_id'] );
								$current_user = wp_get_current_user();
								if ( 0 != $current_user->ID ) {
									$caps = new TTLS_Capabilities( $user_info['license_token'], $user_info['license_type'], $user_info );
									if ( is_wp_error( $caps->this_license ) ) {
										return $caps->this_license;
									} else {
										if ( $product_id != $caps->this_license['product_id'] ) {
											$error->add( 'ttls_license_used', 'This purchase code is already being used', array( 'status' => 400 ) );
											return $error;
										}
										$response['license_token'] = $caps->this_license['token'];
										$response['license_type'] = $caps->this_license['type'];
										$response['verified'] = $caps->this_license['verified'];
										$response['have_support'] = $caps->this_license['have_support'];
										if ( $caps->this_license['have_support'] ) {
											$response['have_support_until'] = $caps->this_license['have_support_until'];
										}
										$response['support_link'] = $caps->this_license['support_link'];
										return $response;
									}
								} else {
									$license_object = new TTLS_License();
									$old_license = $license_object->get( $user_info['license_type'], $user_info['license_token'] );
									if ( is_wp_error( $old_license ) && $old_license->get_error_code() === 'ttls_license_notfound' ) {
										return array( 'message' => 'This purchase code could be used' );
									} else {
										if ( $product_id == $old_license['product_id'] && ( empty( $old_license['owners'] ) || $license_object->license_settings->multiple_users ) ) {
											return array( 'message' => 'This purchase code could be used' );
										} else {
											$error->add( 'ttls_license_used', 'This purchase code is already being used', array( 'status' => 400 ) );
											return $error;
										}
									}
								}
								break;
							case 'login':
								$current_user = wp_get_current_user();
								if ( 0 != $current_user->ID ) {
									return true;
								} else {
									$error->add( 'ttls_auth_error', 'You need to login', array( 'status' => 401 ) );
									return $error;
								}
								break;

							default:
								$error->add( 'ttls_link_wrongmode', 'Wrong mode for this route '.$route, array( 'status' => 400 ) );
								return $error;
								break;
						}
					break;

					case 'ticket':
						// create an object for rights verification
						$caps = new TTLS_Capabilities( $user_info['license_token'], $user_info['license_type'], $user_info );
						switch ( $user_info['mode'] ) {
							case 'get':
								$current_user = wp_get_current_user();
								if ( 0 != $current_user->ID ) {
									if ( empty( $_POST['ticket_id'] ) ) {
										$error->add( 'ttls_data_noticketid', 'No ticket ID', array( 'status' => 400 ) );
										return $error;
									} else {
										if ( is_numeric( $_POST['ticket_id'] ) ) {
											return $caps->can_get_ticket( sanitize_key( $_POST['ticket_id'] ) );
										} else {
											$error->add( 'ttls_data_badticket', 'ID should be numeric', array( 'status' => 400 ) );
											return $error;
										}
									}
								} else {
									$error->add( 'ttls_auth_error', 'You need to login', array( 'status' => 401 ) );
									return $error;
								}

								break;
							case 'add':
								$current_user = wp_get_current_user();
								if ( 0 != $current_user->ID ) {
									if ( empty( $_POST['parent'] ) ) {
										$this->caps = $caps;
										return $caps->can_add_ticket();
									} else {
										if ( is_numeric( $_POST['parent'] ) ) {
											$this->caps = $caps;
											$can_add = $caps->can_add_response( sanitize_key( $_POST['parent'] ) );
											if ( is_wp_error( $can_add ) ) {
												return $can_add;
											}
											return $user_info;
										} else {
											$error->add( 'ttls_data_badticket', 'ID should be numeric', array( 'status' => 400 ) );
											return $error;
										}
									}
								} else {
									$error->add( 'ttls_auth_error', 'You need to login', array( 'status' => 401 ) );
									return $error;
								}

								break;
							case 'list':
								$current_user = wp_get_current_user();
								if ( 0 != $current_user->ID ) {
									if ( is_wp_error( $caps->this_license ) ) {
										return $caps->this_license;
									} else {
										return $caps->this_license['tickets'];
									}
								} else {
									$error->add( 'ttls_auth_error', 'You need to login', array( 'status' => 401 ) );
									return $error;
								}
								break;

							case 'edit':
								$current_user = wp_get_current_user();
								if ( 0 != $current_user->ID ) {
									if ( empty( $_POST['parent'] ) ) {
										$error->add( 'ttls_data_noticketid', 'No ticket ID', array( 'status' => 400 ) );
										return $error;
									} else {
										if ( is_numeric( $_POST['parent'] ) ) {
											return $caps->can_edit_ticket( sanitize_key( $_POST['parent'] ) );
										} else {
											$error->add( 'ttls_data_badticket', 'ID should be numeric', array( 'status' => 400 ) );
											return $error;
										}
									}
								} else {
									$error->add( 'ttls_auth_error', 'You need to login', array( 'status' => 401 ) );
									return $error;
								}
								break;

							default:
								$error->add( 'ttls_link_wrongmode', 'Wrong mode for this route '.$route, array( 'status' => 400 ) );
								return $error;
								break;
						}
					break;

					default:
						$error->add( 'ttls_link_wrongroute', 'Route does not exist', array( 'status' => 400 ) );
								return $error;
						break;
				}
			}

			/**
			 * actions for rest route "ticket"
			 *
			 * @return     TTLS_Ticket|WP_Error  data
			 */
			function ticket(){
				$can_do = $this->can( 'ticket' );
				if ( !is_wp_error( $can_do ) ) {
					$mode = sanitize_text_field( $_POST['mode'] );
					switch ( $mode ) {
						case 'get':
							$order = ( isset( $_POST['order'] ) ) ? sanitize_text_field( $_POST['order'] ) : 'ASC';
							$paged = ( isset( $_POST['paged'] ) ) ? sanitize_key( $_POST['paged'] ) : 1;
							$ticket_id = sanitize_key( $_POST['ticket_id'] );
							return apply_filters( 'ttls_link_response_data', TTLS()->ticket_service()->get_single( $ticket_id, $paged, $order ), __FUNCTION__, $mode );
							break;
						case 'add':
							$ticket_data = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
							$ticket_data['content'] = empty( $_POST['content'] ) ? '' : wp_kses_post( $_POST['content'] );
							$ticket_data['ttls_servers'] = $can_do['ttls_servers'];
							$ticket_data['ttls_site_urls'] = $can_do['ttls_site_urls'];
							return apply_filters( 'ttls_link_response_data', TTLS()->ticket_service()->add( $ticket_data, $this->caps ), __FUNCTION__, $mode );
							break;
						case 'list':
							$paged = ( empty( $_POST['paged'] ) ) ? 1 : sanitize_key( $_POST['paged'] );
							$status = ( empty( $_POST['status'] ) ) ? false : sanitize_text_field( $_POST['status'] );
							$ticket_list = TTLS()->ticket_service()->get_list( $paged, $can_do, $status );
							return apply_filters( 'ttls_link_response_data', $ticket_list, __FUNCTION__, $mode );
							break;

						case 'edit':
							$data = array(
								'parent' => sanitize_key( $_POST['parent'] ),
								'status' => empty( $_POST['status'] ) ? 'closed' : sanitize_text_field( $_POST['status'] ),
							);
							if ( $data['status'] == 'closed' ) {
								if ( $can_do['status'] == 'closed') {
									return new WP_Error( 'ttls_ticket_edit_closed_already', 'This ticket is already closed', array( 'status' => 400 ) );
								}
							} elseif( $data['status'] == 'reopen' ){
								if ( $can_do['status'] != 'closed') {
									return new WP_Error( 'ttls_ticket_edit_open_already', 'This ticket is open', array( 'status' => 400 ) );
								}
							} else {
								return new WP_Error( 'ttls_ticket_edit_wrongstatus', 'Unknown status', array( 'status' => 400 ) );
							}
							$edit_ticket = TTLS()->ticket_service()->client_edit_ticket( $data );
							$result = new WP_Error();
							if ( is_wp_error( $edit_ticket ) ) {
								$result = $edit_ticket;
							} elseif( ! empty( $edit_ticket['ticket_id'] ) ) {
								$result = $edit_ticket['ticket_id'];
							}
							return apply_filters( 'ttls_link_response_data', $result, __FUNCTION__, $mode );
							break;
					}
				} else {
					return $can_do;
				}
			}

			/**
			 * actions for rest route "user"
			 *
			 * @return     TTLS_Ticket|WP_Error  data
			 */
			function user(){
				$mode = sanitize_text_field( $_POST['mode'] );
				switch ( $mode ) {
					case 'can':
						$can_route = ( !empty( $_POST['can_route'] ) ) ? sanitize_text_field( $_POST['can_route'] ) : 'user';
						$can_mode = ( !empty( $_POST['can_mode'] ) ) ? sanitize_text_field( $_POST['can_mode'] ) : false;
						return $this->can( $can_route, $can_mode );
						break;
					case 'set_name':
						$current_user = wp_get_current_user();
						if ( 0 != $current_user->ID ) {
							if ( empty( $_POST['name'] ) ) {
								return new WP_Error( 'ttls_data_empty', 'Send new name', array( 'status' => 401 ) );
							} else {
								$name = sanitize_text_field( $_POST['name'] );
								if ( get_user_meta( $current_user->ID, 'first_name', true) != $name ) {
									if ( update_user_meta( $current_user->ID, 'first_name', $name ) ) {
										return true;
									} else {
										return new WP_Error( 'ttls_db_error', 'Issues encountered while updating the database', array( 'status' => 500 ) );
									}
								} else {
									return new WP_Error( 'ttls_data_bad', 'Old name is the same', array( 'status' => 400 ) );
								}
							}
						} else {
							return new WP_Error( 'ttls_auth_error', 'Some errors with authorization', array( 'status' => 401 ) );
						}
						break;
					case 'register':
						$error = new WP_Error;
						$has_error = false;
						$current_user = wp_get_current_user();
						if ( 0 != $current_user->ID ) {
							return new WP_Error( 'ttls_auth_twicereg', 'Can\'t register twice', array( 'status' => 401 ) );
						} else {
							if ( ttls_open_rest_api_registration() ) {

								if ( empty( $_POST['email'] ) ) {
									$has_error = true;
									$error->add( 'ttls_registration_email', "No email", array( 'status' => 400 ) );
								} else {
									// check if the email is correct ???
									$userdata['user_email'] = sanitize_email( $_POST['email'] );
								}

								if ( empty( $_POST['login'] ) ) {
									$has_error = true;
									$error->add( 'ttls_registration_login', "No username", array( 'status' => 400 ) );
								} else {
									$userdata['user_login'] = sanitize_text_field( $_POST['login'] );
								}

								if ( !empty( $_POST['name'] ) ) {
									$userdata['first_name'] = sanitize_text_field( $_POST['name'] );
								} else {
									$userdata['user_login'] = sanitize_text_field( $_POST['login'] );
								}



								if ( empty( $_POST['password'] ) ) {
									$userdata['user_pass'] = wp_generate_password();
								} else {
									$userdata['user_pass'] = sanitize_text_field( $_POST['password'] );
								}

								if ( $has_error ) {
									return $error;
								}

								$userdata['role'] = 'ttls_clients';
								$userdata['position'] = 'Client';
								$new_client = (new TTLS_Users)->create( $userdata );
								if ( is_wp_error( $new_client ) ) {
									$error->add( 'ttls_registration_fail', $new_client->get_error_message(), array( 'status' => 500 ) );
									return $error;
								} else {
									unset( $new_client['id'] );
									return apply_filters( 'ttls_link_response_data', $new_client, __FUNCTION__, $mode );
								}

							} else {
								return new WP_Error( 'ttls_registration_closed', "This server's registration is restricted", array( 'status' => 403 ) );
							}
						}
						return $error;
						break;
					case 'license':
						$error = new WP_Error;
						$has_error = false;
						$current_user = wp_get_current_user();
						if ( 0 != $current_user->ID ) {
							if ( empty( $_POST['product_id'] ) ) {
								$error->add( 'ttls_license_noproduct', 'Send product ID', array( 'status' => 400 ) );
								$has_error = true;
							} else {
								$licensedata['product_id'] = sanitize_key( $_POST['product_id'] );
							}

							if ( empty( $_POST['license_type'] ) ) {
								$error->add( 'ttls_license_notype', 'Send license type', array( 'status' => 400 ) );
								$has_error = true;
							} else {
								$licensedata['license_type'] = apply_filters( 'ttls_link_license_type', sanitize_text_field( $_POST['license_type'] ), __FUNCTION__, $mode );
							}

							if ( ! empty( $_POST['license_token'] ) ) {
								$licensedata['license_token'] = sanitize_text_field( $_POST['license_token'] );
							}

							// assemble the info for request origin
							$licensedata['servers'] = $_SERVER['REMOTE_ADDR'];
							$user_agent = explode(';', $_SERVER['HTTP_USER_AGENT']);
							// $user_agent[0] - generator - WP version
							$host = parse_url( trim( $user_agent[1] ), PHP_URL_HOST );

							if ( ! empty( $host ) ) {								
								$url_path = parse_url( trim( $user_agent[1] ), PHP_URL_PATH );
								$licensedata['site_url'] = empty( $url_path ) ? $host : $host . $url_path;
							}

							if ( empty( $licensedata['servers'] ) ) {
								$error->add( 'ttls_license_noserver', 'The server has not been identified', array( 'status' => 500 ) );
								$has_error = true;
							}

							if ( empty( $licensedata['site_url'] ) ) {
								$error->add( 'ttls_license_nourl', 'The site URL has not been identified', array( 'status' => 500 ) );
								$has_error = true;
							}

							if ( $has_error ) {
								return $error;
							}

							$licensedata = apply_filters( 'ttls_link_license_data', $licensedata, filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING ) );

							// Adding the license to current user
							$new_license = (new TTLS_License)->add( $licensedata );
							if ( is_wp_error( $new_license ) ) {
								return $new_license;
							} else {
								return apply_filters( 'ttls_link_response_data', array(
									'license_token' => $new_license['token'],
									'type' => $new_license['type'],
									'verified' => $new_license['verified'],
									'have_support'  => $new_license['have_support'],
									'have_support_until'  => $new_license['have_support_until'],
								), __FUNCTION__, $mode );
							}
						} else {
							return new WP_Error( 'ttls_auth_error', 'You should login', array( 'status' => 401 ) );
						}
						break;
					case 'reset_pass':
						$key = '';
						$user_login = '';
						$new_password = '';
						if ( empty( $_POST['login'] ) || ! is_string( $_POST['login'] ) ) { // a check for email and login presence
							return new WP_Error( 'ttls_no_data_for_reset_pass', 'No username or email for reset', array( 'status' => 400 ) );
				        } elseif ( strpos( $_POST['login'], '@' ) ) { // if the @ symbol is present then it is an email
				            $user_data = get_user_by( 'email', sanitize_email( wp_unslash( trim( $_POST['login'] ) ) ) ); // receive user's data via email
				            if ( empty( $user_data ) ){ // when this user is not found in the email
				            	return new WP_Error( 'ttls_invalid_username', 'This user is not found', array( 'status' => 404 ) );
				            }
				        } else {
				            $login = trim( sanitize_text_field( $_POST['login'] ) );
				            $user_data = get_user_by( 'login', $login); // receive login data
				        }

				        if ( empty( $user_data ) ) { // when this user is not found based on the login
				            return new WP_Error( 'ttls_invalid_username', 'This user is not found', array( 'status' => 404 ) );
				        }

			            $user_login = $user_data->user_login;
			            $user_email = $user_data->user_email;

						if ( !empty( $_POST['reset_key'] ) ) { // when the reset key has already been received
							$user = check_password_reset_key( $_POST['reset_key'], $user_login ); // check the key
							if ( is_wp_error( $user ) ) { // when the check produced an error
								$ttls_error = new WP_Error;
								foreach ( $user->errors as $code => $message ) {
									switch ( $code ) {
										case 'invalid_key': // when the key is incorrect
											$ttls_error->add('ttls_reset_invalid_key', 'This code is incorrect', array( 'status' => 400 ));
											break;
										case 'expired_key': // when the key expired
											$ttls_error->add('ttls_reset_expired_key', 'This code is incorrect', array( 'status' => 400 ));
											break;
									}
								}
								return $ttls_error;
							} else { // when the check was successful
								$new_password = wp_generate_password(); // generating new password
								wp_set_password( $new_password, $user_data->data->ID ); // setting the new password to the user
								$standart_message = "Your password for accessing the technical support server was changed.\r\nYour new password: %password%.\r\nIf during the process of resetting the password you checked the option of the automatic password change, then in the settings of support server the password has been updated. Otherwise you will need to update it manually.\r\n\r\nThank you for your support!\r\n";
								$message = get_option( 'ttls_password_reset_message', $standart_message );
								$title = get_option('ttls_password_reset_title', 'Your new password for the technical support server');

								// replace variables in headings and the body of the mail

								$vars = array(
									'%secure_code%' => $key,
									'%login%' => $user_login,
									'%password%' => $new_password,
								);

								$vars = apply_filters( 'ttls_replace_mail_vars', $vars );
					            $title = strtr( $title, $vars);
					            $message = strtr( $message, $vars);

								wp_mail( $user_email, wp_specialchars_decode( $title ), $message );
								return apply_filters( 'ttls_link_response_data', array( 'password' => $new_password, 'message' => 'Password was reseted' ), __FUNCTION__, $mode ); // sending him the password via message
							}
						} // END when the rest key has already been received

			            $key = get_password_reset_key( $user_data ); // generate rest key for reset

			            $standart_message = "Hello. You, or someone on your team have made an attempt to reset the password for the technical support server.\r\nIf you made this request please use the secure code provdied within the next 24 hours.\r\nYour secure code is - %secure_code%.\r\nIf you did not make this request, or were able to gain access since, you can simply ignore this email and continue to use your old password.\r\n\r\nThank you for your support.\r\n";
			            $message = get_option( 'ttls_password_reset_code_message', $standart_message );
						$title = get_option('ttls_password_reset_code_title', 'Resetting the password for the technical support server');

			            // replace variables in headings and the body of the mail
			            $vars = array(
							'%secure_code%' => $key,
							'%login%' => $user_login,
							'%password%' => $new_password,
						);

						$vars = apply_filters( 'ttls_replace_mail_vars', $vars );
			            $title = strtr( $title, $vars);
			            $message = strtr( $message, $vars);

			            if ( $message && !wp_mail( $user_email, wp_specialchars_decode( $title ), $message ) ){ // when there were errors with sending the email
			                return new WP_Error( 'ttls_mail_error', 'Some problem with sending email', array( 'status' => 500 ) );
			            } else { // send a response email with login and a short message
			            	return apply_filters( 'ttls_link_response_data', array( 'login' => $user_login, 'message' => 'A message with login credentials has been sent to your email' ), __FUNCTION__, $mode );
			            }
						break;
				}
			}
			/**
			 * actions for rest route "server"
			 *
			 * @return     array|WP_Error  data
			 */
			function server(){
				$error = new WP_Error;
				$has_error = false;
				// gathering the data regarding the request origin
				$user_info['ttls_servers'] = $_SERVER['REMOTE_ADDR'];
				$user_agent = explode(';', $_SERVER['HTTP_USER_AGENT']);
				// $user_agent[0] - generator - WP version
				$host = parse_url( trim( $user_agent[1] ), PHP_URL_HOST );
				if ( ! empty( $host ) ) {								
					$url_path = parse_url( trim( $user_agent[1] ), PHP_URL_PATH );
					$user_info['ttls_site_urls'] = empty( $url_path ) ? $host : $host . $url_path;
				}

				if ( empty( $user_info['ttls_servers'] ) ) {
					$error->add( 'ttls_license_noserver', 'The server has not been identified', array( 'status' => 500 ) );
					$has_error = true;
				}

				if ( empty( $user_info['ttls_site_urls'] ) ) {
					$error->add( 'ttls_license_nourl', 'The URL has not been identified', array( 'status' => 500 ) );
					$has_error = true;
				}

				if ( $has_error ) {
					return $error;
				}

				$products = $this->get_server_products();
				$product_list = array_merge( $products, apply_filters(  'ttls_link_server_addproduct',  array() ) );
				if ( empty( $product_list ) ) {
					$error->add( 'ttls_server_noconfig', 'The server has not been configured', array( 'status' => 500 ) );
					return $error;
				}

				return apply_filters( 'ttls_link_response_data', array('product_list' => $product_list), __FUNCTION__, false );

			}

			private function get_server_products() {
				$server_products = array();
				$products = \TTLS\Models\Product::find_all_available();
				if ( ! empty( $products['items'] ) ) {
					foreach( $products['items'] as $product ) {
						if ( ! empty( $product->active_licenses() ) ) {
							$product_array = $product->export_attr_array();
							unset( $product_array['licenses'] );
							$product_array['license_list'] = \TTLS_License::get_license_list( $product );
							$product_array['image'] = wp_get_attachment_image_url( $product_array['image'], 'full' ); // need to get the link
							$server_products[] = $product_array;
						}
					}
				}
				return $server_products;
			}
		}
	}