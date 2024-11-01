<?php
	// Exit if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	if ( ! class_exists( 'TTLS_Users' ) ) {

		class TTLS_Users {

			var $userdata = false;
			var $usermeta = array(
				'ttls_license' => false,
				'ttls_servers' => false,
				'ttls_site_urls' => false,

				'ttls_open_tickets' => 0,
				'ttls_all_tickets' => 0,
				'first_name' => 'John Doe',
				'nickname' => 'Support agent',
			);

			function __construct() {
				$new_metas = array();
				$all_metas = $this->usermeta;
				$this->usermeta['nickname'] = esc_html__('Support agent', 'ttls_translate');
				$this->usermeta = array_merge( $all_metas, apply_filters( 'ttls_add_custom_usermeta', $new_metas ) );
			}


			/**
			 * create client from adminpanel
			 * ttls_create_client
			 */
			static function ajax_create_client(){
				parse_str( $_POST['fields'], $fields );
				if ( current_user_can( 'ttls_plugin_admin' ) ) {

					$userdata = array();

					if ( empty( $fields['email'] ) ) {
						$errors['email'] = esc_html__('Email is required', 'ttls_translate');
					} else {
						if ( is_email( $fields['email'] ) ) {
							$userdata['user_email'] = sanitize_email( $fields['email'] );
						} else {
							$errors['email'] = esc_html__('Email is wrong', 'ttls_translate');
						}
					}

					if ( empty( $fields['login'] ) ) {
						$errors['login'] = esc_html__('Username is required', 'ttls_translate');
					} else {
						$userdata['user_login'] = sanitize_text_field( $fields['login'] );
					}

					if ( !empty( $fields['name'] ) ) {
						$userdata['first_name'] = sanitize_text_field( $fields['name'] );
					}

					$userdata['user_pass'] = wp_generate_password();

					if ( !empty( $errors ) ) {
						if ( empty( $fields['name'] ) AND empty( $fields['email'] ) ) {
							$errors['user'] = esc_html__('Please make a selection', 'ttls_translate');
						}
						wp_send_json_error( array( 'message' => esc_html__('Encountered an error with creating a client', 'ttls_translate'), 'errors' => $errors ) );
					}

					$userdata['role'] = 'ttls_clients';
					$user_id = wp_insert_user( $userdata );

					if( ! is_wp_error( $user_id ) ) {
						if ( !empty( $fields['position'] ) ) {
							update_user_meta( $user_id, 'nickname', 'Client' );
						}
						wp_send_json_success(
							array(
								'user' => $user_id,
								'message' => esc_html__('A new client has been created', 'ttls_translate').'<br>'.
									'<br><pre>'.esc_html__('Username', 'ttls_translate').': '.esc_html( $userdata['user_login'] ).
									'<br>'.esc_html__('Password', 'ttls_translate').': '.esc_html( $userdata['user_pass'] ).'</pre>'
							)
						);
					} else {
						wp_send_json_error( array( 'message' => esc_html__( $user_id->get_error_message(), 'ttls_translate') ) );
					}
				} else {
					wp_send_json_error( array( 'message' => esc_html__('You do not have sufficient rights for this action', 'ttls_translate') ) );
				}

			}

			/**
			 * create agent from adminpanel
			 * ttls_create_developer
			 */
			static function ajax_create_developer(){
				parse_str( $_POST['fields'], $fields );
				if ( current_user_can( 'ttls_plugin_admin' ) ) {

					$userdata = array();

					if ( empty( $fields['email'] ) ) {
						$errors['email'] = esc_html__('Email is required', 'ttls_translate');
					} else {
						if ( is_email( $fields['email'] ) ) {
							$userdata['user_email'] = sanitize_email( $fields['email'] );
						} else {
							$errors['email'] = esc_html__('Email is wrong', 'ttls_translate');
						}
					}

					if ( empty( $fields['login'] ) ) {
						$errors['login'] = esc_html__('Username is required', 'ttls_translate');
					} else {
						$userdata['user_login'] = sanitize_text_field( $fields['login'] );
					}

					if ( empty( $fields['name'] ) ) {
						$errors['name'] = esc_html__('Name is required', 'ttls_translate');
					} else {
						$userdata['first_name'] = sanitize_text_field( $fields['name'] );
					}

					if ( empty( $fields['position'] ) ) {
						$errors['position'] = esc_html__('Position is required', 'ttls_translate');
					}

					if ( empty( $fields['ttls_user_password'] ) ) {
						$userdata['user_pass'] = wp_generate_password();
					} else {
						$userdata['user_pass'] = sanitize_text_field( $fields['ttls_user_password'] );
					}

					if ( !empty( $errors ) ) {
						wp_send_json_error( array( 'message' => esc_html__('Encountered an error with creating a developer', 'ttls_translate'), 'errors' => $errors ) );
					}

					$userdata['role'] = 'ttls_developers';
					$user_id = wp_insert_user( $userdata );

					if( ! is_wp_error( $user_id ) ) {
						if ( !empty( $fields['position'] ) ) {
							update_user_meta( $user_id, 'nickname', sanitize_text_field( $fields['position'] ) );
						}
						if ( !empty( $fields['caps'] ) ) {
							$caps_editor = new WP_User( $user_id );
							foreach ( $fields['caps'] as $c_k => $c_v ) {
								if ( $c_v ) {
									$caps_editor->add_cap( sanitize_text_field( $c_k ) );
								} else {
									$caps_editor->remove_cap( sanitize_text_field( $c_k ) );
								}
							}
						}
						wp_send_json_success(
							array(
								'message' => esc_html__('New agent created', 'ttls_translate').'<br>'.
									'<br><pre>'.esc_html__('Username', 'ttls_translate').': '.esc_html( $userdata['user_login'] ).
									'<br>'.esc_html__('Password', 'ttls_translate').': '.esc_html( $userdata['user_pass'] ).'</pre>'
							)
						);
					} else {
						wp_send_json_error( array( 'message' => esc_html__( $user_id->get_error_message(), 'ttls_translate') ) );
					}
				} else {
					wp_send_json_error( array( 'message' => esc_html__('You do not have sufficient rights for this action', 'ttls_translate') ) );
				}

			}

			/**
			 * update agent data from adminpanel
			 * ttls_update_developer
			 */
			static function ajax_update_developer(){
				parse_str( $_POST['fields'], $fields );
				if ( current_user_can( 'ttls_plugin_admin' ) ) {

					if ( empty( $fields['user_id'] ) ) {
						wp_send_json_error( array( 'message' => esc_html__('Missing user ID', 'ttls_translate') ) );
					}
					$message = '';
					$errors = array();
					$user_id = (int) sanitize_key( $fields['user_id'] );
					foreach ( $fields as $fields_key => $value) {
						switch ( $fields_key ) {
							case 'first_name':
								$first_name = sanitize_text_field( $fields['first_name'] );
								if ( get_user_meta( $user_id, 'first_name', true) != $first_name ) {
									if ( !update_user_meta( $user_id, 'first_name', $first_name ) ) {
										$errors['first_name'] = esc_html__('Encountered an error while updating', 'ttls_translate');
									}
								}
								break;
							case 'nickname':
								$nickname = sanitize_text_field( $fields['nickname'] );
								if ( get_user_meta( $user_id, 'nickname', true) != $nickname ) {

									if ( !update_user_meta( $user_id, 'nickname', $nickname ) ) {
										$errors['nickname'] = esc_html__('Encountered an error while updating', 'ttls_translate');
									}
								}
								break;
							case 'email':
								if ( is_email( $fields['email'] ) ) {
									$userdata = array(
										'ID' => $user_id,
										'user_email' => sanitize_email( $fields['email'] ),
									);
									$user_update = wp_update_user( $userdata );
									if ( is_wp_error( $user_update ) ) {
										$errors['email'] = esc_html__( $user_update->get_error_message(), 'ttls_translate');
									}
								} else {
									$errors['email'] = esc_html__('Email is wrong', 'ttls_translate');
								}
								break;
							case 'ttls_user_password':
								$userdata = array(
									'ID' => $user_id
								);
								if ( empty( $fields['ttls_user_password'] ) ) {
									$errors['password'] = esc_html__('A new password is not specified', 'ttls_translate');
									break;
								} else {
									$userdata['user_pass'] = sanitize_text_field( $fields['ttls_user_password'] );
								}
								$user_update = wp_update_user( $userdata );
								if ( !is_wp_error( $user_update ) ) {
									$message .= '<br><pre>'.esc_html__( 'New password', 'ttls_translate' ).': '.esc_html( $userdata['user_pass'] ).'</pre>';
								} else {
									$errors['password'] = esc_html__( $user_update->get_error_message(), 'ttls_translate');
								}
								break;

							case 'caps':
								$caps_editor = new WP_User( $user_id );
								foreach ( $value as $c_k => $c_v ) {
									if ( $c_v ) {
										$caps_editor->add_cap( sanitize_text_field( $c_k ) );
									} else {
										$caps_editor->remove_cap( sanitize_text_field( $c_k ) );
									}
								}
								break;

							default:
								// do nothing
								break;
						}
					}
					if ( empty( $errors ) ) {
						wp_send_json_success( array( 'message' => esc_html__('Updated! ', 'ttls_translate') . wp_kses_post( $message ) ) );
					} else {
						wp_send_json_error( array( 'message' => esc_html__('Encountered errors while updating'), 'errors' => $errors ) );
					}
				} else {
					wp_send_json_error( array( 'message' => esc_html__('You do not have sufficient rights for this action', 'ttls_translate') ) );
				}
			}


			/**
			 * print html of editor for agent for adminpanel
			 * ttls_edit_developer
			 */
			static function ajax_edit_developer(){
				if ( current_user_can( 'ttls_plugin_admin' ) ) {
					$this_user = (new TTLS_Users)->get_user( sanitize_key( $_POST['developer'] ) );
					if ( is_wp_error( $this_user ) ) { ?>
						<div class="modal-header">
							<button type="button" data-bs-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
							<h4 class="modal-title" id="myModalLabel"><?php echo esc_html__('Change agent', 'ttls_translate'); ?></h4>
						</div>
						<div class="modal-body">
							<div class="alert alert-warning alert-dismissible">
								<h4>
									<i class="icon fa fa-warning"></i>
									<?php echo esc_html__('Error', 'ttls_translate'); ?>
								</h4>
								<?php echo esc_html__( $this_user->get_error_message(), 'ttls_translate' ); ?>
							</div>
						</div>

						<div class="modal-footer">
							<button type="button" data-bs-dismiss="modal" class="btn btn-default"><?php echo esc_html__('Close', 'ttls_translate'); ?></button>
						</div>
					<?php } else { ?>
						<div class="modal-header">
							<button type="button" data-bs-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
							<h4 class="modal-title"><?php echo esc_html__('Change agent', 'ttls_translate'); ?></h4>
						</div>
						<form class="form ttls-developer-settings">
							<div class="modal-body">
								<input name="user_id" type="hidden" value="<?php echo esc_attr( $this_user['id'] ); ?>">
								<div class="form-group">
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-at"></i></span>
										<input id="ttls__edit-dev-login" type="text" placeholder="<?php echo esc_html__( 'Username', 'ttls_translate'); ?>" value="<?php echo esc_attr( $this_user['login'] ); ?>" class="form-control" disabled>
									</div>
								</div>
								<div class="form-group">
									<div class="input-group"><span class="input-group-addon"><i class="fa fa-user"></i></span>
										<input id="ttls__edit-dev-name" type="text" name="first_name" placeholder="<?php echo esc_html__('Name', 'ttls_translate'); ?>" value="<?php echo esc_attr( $this_user['meta_first_name'][0] ); ?>" class="form-control">
									</div>
								</div>
								<div class="form-group">
									<div class="input-group"><span class="input-group-addon"><i class="fa fa-user-tie"></i></span>
										<input id="ttls__edit-dev-position" name="nickname" type="text" placeholder="<?php echo esc_html__('Position', 'ttls_translate'); ?>" value="<?php echo esc_attr( $this_user['meta_nickname'][0] ); ?>" class="form-control">
									</div>
								</div>
								<div class="form-group">
									<div class="input-group"><span class="input-group-addon"><i class="fa fa-envelope"></i></span>
										<input id="ttls__edit-dev-mail" name="email" type="email" placeholder="<?php echo esc_html__('Email', 'ttls_translate'); ?>" value="<?php echo esc_attr( $this_user['email'] ); ?>" class="form-control">
									</div>
								</div>
								<div class="form-group">
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-lock"></i></span>
										<input
											id="ttls__edit-dev-password"
											data-password="<?php echo wp_generate_password(); ?>"
											type="text"
											placeholder="<?php echo esc_html__('Password', 'ttls_translate') ?>"
											value="<?php echo esc_html__('Password', 'ttls_translate') ?>"
											name="ttls_user_password"
											class="form-control"
											disabled="disabled"
										>
										<span class="input-group-btn">
											<div
												data-change="<?php echo esc_html__('Change password', 'ttls_translate') ?>"
												data-cancel="<?php echo esc_html__('Do not change', 'ttls_translate') ?>"
												class="btn btn-info ttls_activate_password_changing"><?php echo esc_html__('Change password', 'ttls_translate') ?></div>
										</span>
									</div>
								</div>
								<div class="form-group">
									<div class="checkbox">
										<input type="hidden" name="caps[ttls_plugin_admin]" value="">
										<input id="ttls__edit-dev-caps-ttls_plugin_admin" type="checkbox" name="caps[ttls_plugin_admin]" value="true" <?php echo ( user_can( $this_user['id'], 'ttls_plugin_admin' ) ) ? 'checked' : ''; ?>>
										<label for="ttls__edit-dev-caps-ttls_plugin_admin"><?php echo esc_html__('Plugin administrator', 'ttls_translate') ?></label>
									</div>
								</div>
							</div>
							<div class="modal-footer">
								<button type="submit" class="btn btn-dark"><?php echo esc_html__('Update', 'ttls_translate') ?></button>
							</div>
						</form>
					<?php }
				} else { ?>
					<div class="modal-header">
						<button type="button" data-bs-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
						<h4 class="modal-title" id="myModalLabel"><?php echo esc_html__('Change agent', 'ttls_translate'); ?></h4>
					</div>
					<div class="modal-body">
						<div class="alert alert-warning alert-dismissible">
							<h4>
								<i class="icon fa fa-warning"></i>
								<?php echo esc_html__('You do not have sufficient rights', 'ttls_translate'); ?>
							</h4>
							<?php echo esc_html__('You do not have sufficient rights for this action', 'ttls_translate'); ?>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" data-bs-dismiss="modal" class="btn btn-default"><?php echo esc_html__('Close', 'ttls_translate') ?></button>
					</div>
				<?php
				}
				wp_die();
			}


			/**
			 * delete user from adminpanel
			 * ttls_delete_user
			 */
			static function ajax_delete_user(){

				if ( current_user_can( 'ttls_plugin_admin' ) ) {
					parse_str( $_POST['fields'], $fields );

					if ( empty( $fields['recepient'] ) ) {
						wp_send_json_error( array( 'message' => esc_html__('Error', 'ttls_translate'), 'errors' => array( 'recepient' => esc_html__('Chose the user for replacing', 'ttls_translate') ) ) );
					} else {
						$user = sanitize_key( $fields['user'] );
						$recepient = sanitize_key( $fields['recepient'] );
						if ( $user == $recepient ) {
							wp_send_json_error( array( 'message' => esc_html__('Error'), 'errors' => array( 'recepient' => esc_html__('The users deleted and replaced - are the same user', 'ttls_translate') ) ) );
						} else {
							if ( wp_delete_user( $user, $recepient ) ) {
								wp_send_json_success( array( 'message' => esc_html__('Deleted', 'ttls_translate') ) );
							} else {
								wp_send_json_error( array( 'message' => esc_html__('Encountered errors while deleting user', 'ttls_translate') ) );
							}
						}
					}
				} else {
					wp_send_json_error( array( 'message' => esc_html__( 'Insufficient rights', 'ttls_translate' ) ) );
				}
			}

			/**
			 * Gets the user's data.
			 *
			 * @param      int             $user_id  The user identifier
			 *
			 * @return     WP_Error|array  error of user data
			 */
			function get_user( $user_id ){
				$error = new WP_Error;
				$has_error = false;
				$user = get_user_by( 'id', $user_id );
				if ( $user ) {
					$user_data = array();
					foreach ( $this->usermeta as $key => $value) {
						$user_data['meta_'.$key] = get_user_meta( $user->data->ID, $key );
						if ( empty( $user_data['meta_'.$key] ) ) {
							$user_data['meta_'.$key] = array( $this->usermeta[$key] );
						}
					}
					$user_data['id'] = $user->data->ID;
					$user_data['login'] = $user->data->user_login;
					$user_data['email'] = $user->data->user_email;
					$user_data['name'] = get_user_meta( $user->data->ID, 'first_name', true );

					$ttls_license = new TTLS_License();
					$user_data['license_list'] = array();
					$user_data['license_list'] = $ttls_license->get_list($user_id);
					return $user_data;
				} else {
					return new WP_Error( 'ttls_users_notfound', 'This user does not exist', array( 'status' => 404 ) );
				}
			}

			/**
			 * create new user
			 *
			 * @param      array  $data   new user data
			 *
			 * @return     array  new user data
			 */
			function create( $data ){

				$user_id = wp_insert_user( $data );

				if( ! is_wp_error( $user_id ) ) {
					if ( !empty( $data['position'] ) ) {
						update_user_meta( $user_id, 'nickname', $data['position'] );
					}
					return array(
						'id' => $user_id,
						'login' => $data['user_login'],
						'password' => $data['user_pass']
					);
				} else {
					return $user_id;
				}
			}

		}
	}