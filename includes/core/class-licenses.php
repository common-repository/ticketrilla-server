<?php
	// Exit if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	if ( ! class_exists( 'TTLS_License' ) ) {

		class TTLS_License {

			public $license_type = false;
			public $license_info = false;
			public $license_product_id = false;
			public $license_settings;

			/**
			 * While updating, it is possible to send the type and token of the license in
			 * $license_info will be saved the info related to the licenses, such
			 * initialization is required for the method of addding URLs
			 * and servers to the license
			 *
			 * @param      string  $license_type   license type
			 * @param      string  $license_token  license token
			 */
			function __construct( $license_type = '', $license_token = '', $license_product_id = ''  ) {
				if ( $license_type ) {
					$this->license_type = $license_type;
					if ( $license_token ) {
						$this->license_info = $this->get( $license_type, $license_token );
					}
				}
				if ( $license_product_id ) {
					$this->license_product_id = $license_product_id;
				}
			}

			/**
			 * Attaching the user to license
			 *
			 * @param      <type>    $license_id  The license identifier
			 * @param      <type>    $user_id     The user identifier
			 *
			 * @return     WP_Error  ( description_of_the_return_value )
			 */
			function add_user( $license_id, $user_id ){
				$owners = get_post_meta( $license_id, 'ttls_owners' );
				if ( !in_array( $user_id, $owners ) ) {
					if ( add_post_meta( $license_id, 'ttls_owners', $user_id ) ) {
						return $this->get_by_id( $license_id );
					} else {
						return new WP_Error( 'ttls_license_fail', 'An error while attaching the user to license', array( 'status' => 500 ) );
					}
				} else {
					return new WP_Error( 'ttls_license_alreadyhave', 'This user already has this license', array( 'status' => 400 ) );
				}
			}

			/**
			 * Function of generating html for output of license in the table of
			 * users and general licenses list
			 *
			 * @param      array   $user          data for user's column
			 * @param      array   $license_type  data for license type column
			 * @param      array   $license_data  license data
			 *
			 * @return     string  html 	table rows (tr)
			 */
			function html_user_license( $users = array(), $license_type = array(), $license_data = array() ){
				if ( empty( $license_data['addon_classes'] ) ) {
					$html = '<tr class="ttls_license_row">';
				} else {
					$html = '<tr class="ttls_license_row '.esc_attr( implode(' ', $license_data['addon_classes'] ) ).'">';
				}

				if ( !empty( $users ) ) {
					if ( count( $users ) > 1 ) {
						$html .= '<td class="ttls_user_label">';
					}
					foreach ( $users as $key => $user) {
						$display = ( empty( $user['display'] ) ) ? '' : 'display:none;';
						$user['id'] = ( empty( $user['id'] ) ) ? 0 : $user['id'];
						if ( count( $users ) < 2 ) {
						$html .= '<td style="'.esc_attr( $display ).'" data-user="'.esc_attr( $user['id'] ).'" class="ttls_user_label" rowspan="'.esc_attr( $user['rowspan'] ).'">';
						}
							if ( !empty( $user['name'] ) ) {
								$html .= '<p><i class="fa fa-at"></i> '.esc_html( $user['name'] ).'</p>';
							}
							if ( !empty( $user['login'] ) ) {
								$html .= '<p><i class="fa fa-user"></i> '.esc_html( $user['login'] ).'</p>';
							}
							if ( !empty( $user['email'] ) ) {
								$html .= '<p><i class="fa fa-envelope"></i> '.esc_html( $user['email'] ).'</p>';
							}
						if ( count( $users ) < 2 ) {
						$html .= '</td>';
						} elseif( $key != count( $users ) - 1 ) {
							$html .= '<span class="diviner"></span>';
						}
					}
					if ( count( $users ) > 1 ) {
						$html .= '</td>';
					}
				}

				$html .= '<td data-license="'.esc_attr( $license_data['id'] ).'" class="ttls_edit_license_link ttls__license-product-title"><a class="ttls__license-url">'.esc_html( $license_data['product_title'] ).'</a></td>';

				if ( !empty( $license_type ) ) {

					$display = ( empty( $license_type['display'] ) ) ? '' : 'display:none;';

					$html .= '<td style="'.esc_attr( $display ).'" data-user="'.esc_attr( $license_type['user'] ).'" data-license="'.esc_attr( $license_type['name'] ).'" class="ttls_license_label" rowspan="'.esc_attr( $license_type['rowspan'] ).'">';
						$html .= '<div class="label label-primary">'.esc_html( $license_type['name'] ).'</div>';
					$html .= '</td>';
				}

					$html .= '<td data-license="'.esc_attr( $license_data['id'] ).'" class="ttls_edit_license_link" ><a class="ttls__license-url">'.esc_html( $license_data['token'] ).'</a></td>';

					if ( $license_data['have_support'] ) {
						$html .= '<td data-license="'.esc_attr( $license_data['id'] ).'" class="ttls_edit_license_link" ><i class="fa fa-check text-success" aria-hidden="true"></i>';
						if ( $license_data['have_support_until'] ) {
							$html .= '<span>'.esc_html( $license_data['have_support_until'] ).'</span>';
						}
						$html .= '</td>';
					} else {
						$html .= '<td data-license="'.esc_attr( $license_data['id'] ).'" class="ttls_edit_license_link" ><i class="fa fa-times text-red" aria-hidden="true"></i></td>';
					}

					if ( $license_data['verified'] ) {
						$html .= '<td data-license="'.esc_attr( $license_data['id'] ).'" class="ttls_edit_license_link" ><i class="fa fa-check text-success" aria-hidden="true"></i></td>';
					} else {
						$html .= '<td data-license="'.esc_attr( $license_data['id'] ).'" class="ttls_edit_license_link" ><i class="fa fa-times text-red" aria-hidden="true"></i></td>';
					}

					$html .= '<td><a data-licensetext="'.esc_attr( $license_data['token'] ).'" data-license="'.esc_attr( $license_data['id'] ).'" class="btn btn-xs btn-block btn-default ttls_delete_license">'.esc_html__('Delete', 'ttls_translate').'</a></td>';
				$html .= '</tr>';
				return $html;
			}

			/**
			 * AJAX function for deleting licenses via admin. action =
			 * ttls_delete_license
			 *
			 * @uses       integer $_POST['license'] 	License ID
			 */
			static function delete_license(){
				if ( current_user_can( 'ttls_plugin_admin' ) ) {
					$license_id = (int) sanitize_key( $_POST['license'] );
					$license_adder = get_post( $license_id );
					if ( is_wp_error( $license_adder ) ) {
						wp_send_json_error( array( 'message' => esc_html__( $license_adder->get_error_message(), 'ttls_translate') ) );
					}
					if ( wp_delete_post( $license_id, true ) ) {
						wp_send_json_success( array( 'message' => esc_html__('Deleted', 'ttls_translate') ) );
					} else {
						wp_send_json_error( array( 'message' => esc_html__('Error deleting', 'ttls_translate') ) );
					}
				} else {
					wp_send_json_error( array( 'message' => esc_html__('Insufficient rights', 'ttls_translate') ) );
				}
				wp_die();
			}


			/**
			 * Adds a license from admin. Action = ttls_add_license
			 */
			static function add_license( $fields ){
				parse_str( $_POST['fields'], $fields );
				$new_license = array();

				if ( empty( $fields['license_type'] ) ) {
					wp_send_json_error( array( 'message' => esc_html__('Select license type', 'ttls_translate') ) );
				}
				$new_license['license_type'] = sanitize_text_field( $fields['license_type'] );
				if ( empty( $fields['product_id'] ) ) {
					wp_send_json_error( array( 'message' => esc_html__('Select product', 'ttls_translate') ) );
				}
				$new_license['product_id'] = sanitize_key( $fields['product_id'] );
				$new_license['license_verified'] = empty( $fields['license_verified'] ) ? false : sanitize_text_field( $fields['license_verified'] );
				$new_license['license_have_support'] = empty( $fields['license_have_support'] ) ? false : sanitize_text_field( $fields['license_have_support'] );
				$new_license['user'] = sanitize_key( $fields['user'] );
				if( ! empty( $fields['license_token'] ) ) {
					$new_license['license_token'] = sanitize_text_field( $fields['license_token'] );
				}

				$license_adder = (new TTLS_License)->add( $new_license );
				if ( is_wp_error( $license_adder ) ) {
					wp_send_json_error( array( 'message' => esc_html__( $license_adder->get_error_message(), 'ttls_translate') ) );
				} else {

					$this_user = (new TTLS_Users)->get_user( $new_license['user'] );
					$row_user_data = array(
						'rowspan' => 1,
						'id' => $new_license['user'],
						'name' => $this_user['name'],
						'login' => $this_user['login'],
						'email' => $this_user['email'],
						'display' => 'none',
					);

					$row_license_data = array(
						'rowspan' => 1,
						'name' => $license_adder['type'],
						'user' => $new_license['user']
					);
					if ( !empty( $fields['form_type'] ) AND $fields['form_type'] == 'license' ) {
						unset($row_user_data['display']);
						$html_new_license = (new TTLS_License)->html_user_license( array( $row_user_data ), false, $license_adder );
					} else {
						$html_new_license = (new TTLS_License)->html_user_license( array( $row_user_data ), $row_license_data, $license_adder );
					}

					wp_send_json_success( array(
						'message' => ' <pre>'.esc_html__('License type', 'ttls_translate'). ': ' . esc_html( $license_adder['type'] ).'<br>' . esc_html__('Purchase code').': '.esc_html( $license_adder['token'] ).'</pre>',
						'box' => $html_new_license
					) );
				}
			}

			static function get_license_list( $product ) {
				$license_list = array();
				foreach ( $product->active_licenses() as $license_type => $license_data ) {
					if ( $license_type == 'standard' ) {
						$login_token = 'required'; // possible/required/false
						$register_token = 'possible'; // possible/required/false

						$license_list[$license_type] = array(
							'title' => 'Standard',
							'fields' => array(
								'license_token' => array(
									'title' => 'Purchase code',
									'type' => 'text',
									'login' => $login_token,
									'register' => $register_token,
								),
							)
						);
					} else {
						$custom_license = apply_filters(  'ttls_link_server_' . $license_type . '_fields',  array(), $license_data );
						if ( ! empty( $custom_license ) ) {
							$license_list[$license_type] = $custom_license;
						}
					}
				}
				return $license_list;
			}

			static function get_client_licenses_titles( $client_id = '' ) {
				if ( empty( $client_id ) ) {
					$client_id = get_current_user_id();
				}
				$licenses = \TTLS\Models\License::find_all( array('meta_query' => array( array( 'key' => 'ttls_owners', 'value' => $client_id ))) );
				$client_licenses = array();
				foreach ( $licenses['items'] as $license ) {
					$site_urls = get_post_meta( $license->ID, 'ttls_site_urls' );
					$label = get_the_title( $license->product_id ) . ': ' . ( empty( $site_urls[0] ) ? $license->license_token : $site_urls[0] );
					$client_licenses[$license->ID] = $label;
				}
				return $client_licenses;
			}

			static function save_product() {
				check_ajax_referer( 'ttls_client_save_product', '_wpnonce' );
				$filtered_data = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
				$client_product = new \TTLS\Models\ClientProduct( $filtered_data );
				if ( empty( $client_product->license_id ) ) {
					$client_product->validate();
					if ( ! empty( $_POST['license_token-checkbox'] ) && empty( $client_product->license_token ) ) {
						$client_product->add_error( 'license_data', __( 'This field is required' ) );
					}

					if ( ! $client_product->has_errors() ) {
						$license_data = array(
							'product_id' => $client_product->product_id,
							'license_type' => $client_product->license,
							'license_token' => empty( $client_product->license_token ) ? '' : $client_product->license_token,
							'servers' => '',
							'site_url' => '',
						);
						$license_data = apply_filters( 'ttls_link_license_data', $license_data, $filtered_data );
						// Adding the license to current user
						$new_license = (new self)->add( $license_data );
						if ( is_wp_error( $new_license ) ) {
							$client_product->add_error( '_global', $new_license->get_error_message() );
						} else {
							$license_id = $new_license['id'];
						}
					}
				} else {
					if ( is_wp_error( self::check_license( $client_product->license_id, get_current_user_id() ) ) ) {
						$client_product->add_error( '_global', __( 'This license is not yours', 'ttls_translate' ) );
					} else {
						$license_id = $client_product->license_id;
					}
				}

				if ( $client_product->has_errors() ) {
					$product = \TTLS\Models\Product::find_by_id( $client_product->product_id );
					$template_data = array( 'product' => $product, 'client_product' => $client_product, 'uniq_id' => uniqid() );
					wp_send_json_error( array('html' => TTLS()->admin_page()->buffer_template( 'ticketrilla-server-product-settings-modal', $template_data ) ) );
				}
				if ( ! empty( $license_id ) ) {
					update_post_meta( $license_id, 'ttls_newsletters', empty( $client_product->newsletters ) ? '' : true );
				}
				wp_send_json_success( array('html' => TTLS()->admin_page()->buffer_template( 'ticketrilla-server-products') ) );
			}

			static function check_license( $license_id, $user_id ) {
				$license_check = \TTLS\Models\License::find_one( array(
					'p' => $license_id,
					'meta_key' => 'ttls_owners',
					'meta_value' => $user_id,
				) );
				if ( empty( $license_check['items'] ) ) {
					return new WP_Error( __( 'License not found', 'ttls_translate' ) );
				}
				return true;
		}

			/**
			 * Renews license data. Action = ttls_update_license
			 */
			static function update_license(){
				if ( current_user_can( 'ttls_plugin_admin' ) ) {
					parse_str( $_POST['fields'], $fields );
					$license_fields = array();
					$license_fields['license_id'] = sanitize_key( $fields['license_id'] );
					$license_fields['ttls_license_verified'] = empty( $fields['ttls_license_verified'] ) ? false : sanitize_text_field( $fields['ttls_license_verified'] );
					$license_fields['ttls_license_have_support'] = empty( $fields['ttls_license_have_support'] ) ? false : sanitize_text_field( $fields['ttls_license_have_support'] );
					$license_fields['ttls_license_have_support_until'] = sanitize_text_field( $fields['ttls_license_have_support_until'] );

					$license = get_post( $license_fields['license_id'] );
					if ( is_wp_error( $license ) ) {
						wp_send_json_error( array( 'message' => esc_html__( $license->get_error_message(), 'ttls_translate') ) );
					}
					if ( $license->ttls_license_type == 'standard') {
						$errors = array();
						if ( empty( $license_fields['ttls_license_verified'] ) ) {
							if ( get_post_meta( $license->ID, 'ttls_license_verified', true ) ) {
								if ( !delete_post_meta( $license->ID, 'ttls_license_verified' ) ) {
									$errors['ttls_license_verified'] = esc_html__('Error', 'ttls_translate');
								}
							}
						} else {
							if ( !get_post_meta( $license->ID, 'ttls_license_verified', true ) ) {
								if ( !update_post_meta( $license->ID, 'ttls_license_verified', 'true' ) ) {
									$errors['ttls_license_verified'] = esc_html__('Error', 'ttls_translate');
								}
							}
						}

						if ( empty( $license_fields['ttls_license_have_support'] ) ) {
							if ( get_post_meta( $license->ID, 'ttls_license_have_support', true ) ) {
								if ( !delete_post_meta( $license->ID, 'ttls_license_have_support' ) ) {
									$errors['ttls_license_have_support'] = esc_html__('Error', 'ttls_translate');
								}
							}
						} else {
							if ( !get_post_meta( $license->ID, 'ttls_license_have_support', true ) ) {
								if ( !update_post_meta( $license->ID, 'ttls_license_have_support', 'true' ) ) {
									$errors['ttls_license_have_support'] = esc_html__('Error', 'ttls_translate');
								}
							}
						}

						
						if ( get_post_meta( $license->ID, 'ttls_license_have_support_until', true ) != $license_fields['ttls_license_have_support_until'] ) {
							if ( !update_post_meta( $license->ID, 'ttls_license_have_support_until', $license_fields['ttls_license_have_support_until'] ) ) {
								$errors['ttls_license_have_support_until'] = esc_html__('Error', 'ttls_translate');
							}
						}
						if ( empty( $errors ) ) {
							wp_send_json_success( array( 'message' => esc_html__('Renewed', 'ttls_translate') ) );
						} else {
							wp_send_json_error( array( 'message' => esc_html__('Renewing errors', 'ttls_translate'), 'errors' => $errors ) );
						}
					} else {
						$response = array(
							'status' => false,
							'message' => esc_html__( 'No update function for this license type' ),
							'fields' => $license_fields
						);
						$response = apply_filters( 'ttls_ajax_update_license_'.$license->ttls_license_type, $response );
						if ( $response['status'] ) {
							wp_send_json_success( $response );
						} else {
							wp_send_json_error( array( 'message' => $response['message'], 'errors' => $response['errors'] ) );
						}
					}
				} else {
					$response = array( 'status' => false, 'message' => esc_html__( 'No access', 'ttls_translate' ) );
				}
				echo json_encode( $response );
				wp_die();
			}

			// wp_action - ttls_edit_license


			/**
			 * Generates html for popup for editing licenses
			 */
			static function edit_license(){
				if ( current_user_can( 'ttls_plugin_admin' ) ) {
					$license = get_post( sanitize_key( $_POST['license'] ) );
					if ( $license->ttls_license_type == 'standard') {
					?>
					<div class="modal-header">
	                    <button type="button" data-bs-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
	                    <h4 class="modal-title"><?php echo esc_html__('License', 'ttls_translate') ?> <?php echo esc_html( $license->ttls_license_type );?></h4>
	                    <div class="text-muted"><?php echo esc_html( $license->ttls_license_token );?></div>
	                </div>
	                <form class="form ttls-license-control">
	                	<input type="hidden" value="<?php echo esc_attr( $license->ID ); ?>" name="license_id">
	                    <div class="modal-body">
	                        <div class="row">
	                            <div class="col-md-6">
	                                <table class="table">
	                                    <thead>
	                                        <tr>
	                                            <th colspan="2"><?php echo esc_html__('Servers', 'ttls_translate') ?></th>
	                                        </tr>
	                                    </thead>
	                                    <tbody>
	                                    	<?php foreach ( get_post_meta( $license->ID, 'ttls_servers') as $srv) {
	                                    		echo '<tr>
		                                            <td class="text-muted">'.esc_html( $srv ).'</td>
		                                            <td>
		                                                <div title="'.esc_html__('Allow this server', 'ttls_translate').'" data-mode="allow" data-server="'.esc_attr( $srv ).'" data-license="'.esc_attr( $license->ID ).'" class="btn btn-xs btn-defult ttls_server_move"><i class="fa fa-check"></i></div>
		                                                <div title="'.esc_html__('Forbid this server', 'ttls_translate').'" data-mode="disallow" data-server="'.esc_attr( $srv ).'" data-license="'.esc_attr( $license->ID ).'" class="btn btn-xs btn-defult ttls_server_move"><i class="fa fa-times"></i></div>
		                                            </td>
		                                        </tr>';
											} ?>

											<?php foreach ( get_post_meta( $license->ID, 'ttls_allow_servers') as $srv) {
												echo '<tr>
		                                            <td class="text-success">'.esc_html( $srv ).'</td>
		                                            <td>
		                                                <div data-server="'.esc_attr( $srv ).'" data-license="'.esc_attr( $license->ID ).'" class="btn btn-xs btn-defult ttls_server_move"><i class="fa fa-bullseye"></i></div>
		                                                <div title="'.esc_html__('Forbid this server', 'ttls_translate').'" data-mode="disallow" data-server="'.esc_attr( $srv ).'" data-license="'.esc_attr( $license->ID ).'" class="btn btn-xs btn-defult ttls_server_move"><i class="fa fa-times"></i></div>
		                                            </td>
		                                        </tr>';
											} ?>

											<?php foreach ( get_post_meta( $license->ID, 'ttls_disallow_servers') as $srv) {
												echo '<tr>
		                                            <td class="text-danger">'.esc_html( $srv ).'</td>
		                                            <td>
		                                                <div title="'.esc_html__('Allow this server', 'ttls_translate').'" data-mode="allow" data-server="'.esc_attr( $srv ).'" data-license="'.esc_attr( $license->ID ).'" class="btn btn-xs btn-defult ttls_server_move"><i class="fa fa-check"></i></div>
		                                                <div data-server="'.esc_attr( $srv ).'" data-license="'.esc_attr( $license->ID ).'" class="btn btn-xs btn-defult ttls_server_move"><i class="fa fa-bullseye"></i></div>
		                                            </td>
		                                        </tr>';
											} ?>
	                                    </tbody>
	                                </table>
	                            </div>
	                            <div class="col-md-6">
	                                <table class="table">
	                                    <thead>
	                                        <tr>
	                                            <th colspan="2"><?php echo esc_html__('Site URLs', 'ttls_translate'); ?></th>
	                                        </tr>
	                                    </thead>
	                                    <tbody>
	                                    	<?php foreach ( get_post_meta( $license->ID, 'ttls_site_urls') as $url) {
	                                    		echo '<tr>
		                                            <td class="text-muted">'.esc_html( $url ).'</td>
		                                            <td>
		                                                <div title="'.esc_html__('Allow this URL', 'ttls_translate').'" data-mode="allow" data-url="'.esc_attr( $url ).'" data-license="'.esc_attr( $license->ID ).'" class="btn btn-xs btn-defult ttls_url_move"><i class="fa fa-check"></i></div>
		                                                <div title="'.esc_html__('Forbid this URL', 'ttls_translate').'" data-mode="disallow" data-url="'.esc_attr( $url ).'" data-license="'.esc_attr( $license->ID ).'" class="btn btn-xs btn-defult ttls_url_move"><i class="fa fa-times"></i></div>
		                                            </td>
		                                        </tr>';
											} ?>

											<?php foreach ( get_post_meta( $license->ID, 'ttls_allow_site_urls') as $url) {
												echo '<tr>
		                                            <td class="text-success">'.esc_html( $url ).'</td>
		                                            <td>
		                                                <div data-url="'.esc_attr( $url ).'" data-license="'.esc_attr( $license->ID ).'" class="btn btn-xs btn-defult ttls_url_move"><i class="fa fa-bullseye"></i></div>
		                                                <div title="'.esc_html__('Forbid this URL', 'ttls_translate').'" data-mode="disallow" data-url="'.esc_attr( $url ).'" data-license="'.esc_attr( $license->ID ).'" class="btn btn-xs btn-defult ttls_url_move"><i class="fa fa-times"></i></div>
		                                            </td>
		                                        </tr>';
											} ?>

											<?php foreach ( get_post_meta( $license->ID, 'ttls_disallow_site_urls') as $url) {
												echo '<tr>
		                                            <td class="text-danger">'.esc_html( $url ).'</td>
		                                            <td>
		                                                <div title="'.esc_html__('Allow this URL', 'ttls_translate').'" data-mode="allow" data-url="'.esc_attr( $url ).'" data-license="'.esc_attr( $license->ID ).'" class="btn btn-xs btn-defult ttls_url_move"><i class="fa fa-check"></i></div>
		                                                <div data-url="'.esc_attr( $url ).'" data-license="'.esc_attr( $license->ID ).'" class="btn btn-xs btn-defult ttls_url_move"><i class="fa fa-bullseye"></i></div>
		                                            </td>
		                                        </tr>';
											} ?>
	                                    </tbody>
	                                </table>
	                            </div>
	                        </div>
	                        <div class="form-group">
	                            <div class="checkbox">
	                                <input
	                                	id="ttls__standard_license_new"
	                                	type="checkbox"
	                                	name="ttls_license_verified"
	                                	value="true"
																		<?php echo get_post_meta( $license->ID, 'ttls_license_verified', true ) ? 'checked' : ''; ?>
	                                	>
	                                <label for="ttls__standard_license_new"><?php echo esc_html__('Standard license has been confirmed', 'ttls_translate') ?></label>
	                            </div>
	                            <div class="help-block"><?php echo esc_html__("License confirmed", 'ttls_translate'); ?></div>
	                        </div>
	                        <div class="form-group">
	                            <div class="checkbox">
	                                <input
	                                	id="ttls__standard_license_new_support"
	                                	type="checkbox"
	                                	name="ttls_license_have_support"
	                                	value="true"
																		<?php echo get_post_meta( $license->ID, 'ttls_license_have_support', true ) ? 'checked' : ''; ?>
	                                	>
	                                <label for="ttls__standard_license_new_support"><?php echo esc_html__('Standard license - support is active', 'ttls_translate') ?></label>
	                            </div>
	                            <div class="help-block"><?php echo esc_html__("Support is active", 'ttls_translate'); ?></div>
	                        </div>
							<div class="form-group">
                                <label for="ttls__standard_license_support_until"><?php echo esc_html__('Standard license - support period ended', 'ttls_translate') ?></label>
                                <div class="input-group">
                                	<span class="input-group-addon"><i class="fa fa-key"></i></span>
                                    <input
                                    	id="ttls__standard_license_support_until"
                                    	name="ttls_license_have_support_until"
                                    	type="text"
                                    	autocomplete="off"
                                    	value="<?php echo esc_attr( get_post_meta( $license->ID, 'ttls_license_have_support_until', true ) ); ?>" class="form-control ttls__datepicker">
                                </div>
                            </div>

	                    </div>
	                    <div class="modal-footer">
	                        <button type="submit" class="btn btn-dark"><?php echo esc_html__('Save', 'ttls_translate') ?></button>
	                    </div>
	                </form>
				<?php } else { // when license is not built-it
						$custom_editor_of_license['status'] = false;
						$custom_editor_of_license['message'] = $license->ttls_license_type.esc_html__('editor not present', 'ttls_translate');
						$custom_editor_of_license['license'] = $license;
						$custom_editor_of_license = apply_filters( 'ttls_license_edit_'.$license->ttls_license_type, $custom_editor_of_license );
						if ( $custom_editor_of_license['status'] ) {
							ttls_render( $custom_editor_of_license['box'] );
						} else {
							echo '<div class="modal-header"><button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
							<h4 class="modal-title" id="myModalLabel">License editor</h4></div><div class="modal-body"><div class="alert alert-warning alert-dismissible"><h4><i class="icon fa fa-warning"></i> ';
							echo esc_html( $custom_editor_of_license['message'] );
							echo '</h4>';
							echo '</div></div>';
						}
					}
				} else {
					echo '<div class="modal-header"><button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title" id="myModalLabel">'.esc_html__( 'License editor', 'ttls_translate').'</h4></div><div class="modal-body"><div class="alert alert-warning alert-dismissible"><h4><i class="icon fa fa-warning"></i> ';
	                echo esc_html__('You do not have sufficient rights', 'ttls_translate');
	                echo '</h4>';
	                echo esc_html__('You do not have sufficient rights for this action', 'ttls_translate');
	                echo '</div></div>';
				}
				wp_die();
			}


			/**
			 * Change URL status. URLs could be in one of the three possible statuses:
			 * <ul><li>Neutral<li><li>Allowed<li><li>Forbidden<li></ul>
			 */
			static function move_url(){
				if ( current_user_can( 'ttls_plugin_admin' ) ) {
					if ( !empty( $_POST['url'] ) ) {
						$url = sanitize_text_field( $_POST['url'] );
					} else {
						wp_send_json_error( array( 'message' => esc_html__( 'URL was not sent', 'ttls_translate' ) ) );
					}

					if ( !empty( $_POST['license'] ) ) {
						$license = (int) sanitize_key( $_POST['license'] );
					} else {
						wp_send_json_error( array( 'message' => esc_html__( 'License ID was not sent', 'ttls_translate' ) ) );
					}

					$all_urls = get_post_meta( $license, 'ttls_site_urls' );
					$allow_urls = get_post_meta( $license, 'ttls_allow_site_urls' );
					$disallow_urls = get_post_meta( $license, 'ttls_disallow_site_urls' );

					if ( in_array( $url, $all_urls ) ) {
						$mode = ( isset( $_POST['mode'] ) AND ($_POST['mode'] == 'allow' OR $_POST['mode'] == 'disallow') ) ? sanitize_text_field( $_POST['mode'] ) : get_option('ttls_license_unchecked_urls', 'allow');
						switch ( $mode ) {
							case 'allow':

								delete_post_meta( $license, 'ttls_site_urls', $url );
								add_post_meta( $license, 'ttls_allow_site_urls', $url );

								wp_send_json_success( array(
									'message' => esc_html__('URL is allowed', 'ttls_translate') ,
									'box' => '<tr>
                                        <td class="text-success">'.esc_html( $url ).'</td>
                                        <td>
                                            <div data-url="'.esc_attr( $url ).'" data-license="'.esc_attr( $license ).'" class="btn btn-xs btn-defult ttls_url_move"><i class="fa fa-bullseye"></i></div>
                                            <div title="'.esc_html__('Forbid this URL', 'ttls_translate').'" data-mode="disallow" data-url="'.esc_attr( $url ).'" data-license="'.esc_attr( $license ).'" class="btn btn-xs btn-defult ttls_url_move"><i class="fa fa-times"></i></div>
                                        </td>
                                    </tr>'
								) );
								break;
							case 'disallow':
								delete_post_meta( $license, 'ttls_site_urls', $url );
								add_post_meta( $license, 'ttls_disallow_site_urls', $url );
								wp_send_json_success( array(
									'message' => esc_html__('URL is forbidden', 'ttls_translate') ,
									'box' => '<tr>
                                        <td class="text-danger">'.esc_html( $url ).'</td>
                                        <td>
                                            <div title="'.esc_html__('Allow this URL', 'ttls_translate').'" data-mode="allow" data-url="'.esc_attr( $url ).'" data-license="'.esc_attr( $license ).'" class="btn btn-xs btn-defult ttls_url_move"><i class="fa fa-check"></i></div>
                                            <div data-url="'.esc_attr( $url ).'" data-license="'.esc_attr( $license ).'" class="btn btn-xs btn-defult ttls_url_move"><i class="fa fa-bullseye"></i></div>
                                        </td>
                                    </tr>'
								) );
								break;
						}
					} elseif( in_array( $url, $allow_urls ) ){
						if ( isset( $_POST['mode'] ) AND $_POST['mode'] == 'disallow' ) {
							delete_post_meta( $license, 'ttls_allow_site_urls', $url );
							add_post_meta( $license, 'ttls_disallow_site_urls', $url );
							$box = '<tr>
                                        <td class="text-danger">'.esc_html( $url ).'</td>
                                        <td>
                                            <div title="'.esc_html__('Allow this URL', 'ttls_translate').'" data-mode="allow" data-url="'.esc_attr( $url ).'" data-license="'.esc_attr( $license ).'" class="btn btn-xs btn-defult ttls_url_move"><i class="fa fa-check"></i></div>
                                            <div data-url="'.esc_attr( $url ).'" data-license="'.esc_attr( $license ).'" class="btn btn-xs btn-defult ttls_url_move"><i class="fa fa-bullseye"></i></div>
                                        </td>
                                    </tr>';
						} else {
							delete_post_meta( $license, 'ttls_allow_site_urls', $url );
							add_post_meta( $license, 'ttls_site_urls', $url );
							$box = '<tr>
                                <td class="text-muted">'.esc_html( $url ).'</td>
                                <td>
                                    <div title="'.esc_html__('Allow this URL', 'ttls_translate').'" data-mode="allow" data-url="'.esc_attr( $url ).'" data-license="'.esc_attr( $license ).'" class="btn btn-xs btn-defult ttls_url_move"><i class="fa fa-check"></i></div>
                                    <div title="'.esc_html__('Forbid this URL', 'ttls_translate').'" data-mode="disallow" data-url="'.esc_attr( $url ).'" data-license="'.esc_attr( $license ).'" class="btn btn-xs btn-defult ttls_url_move"><i class="fa fa-times"></i></div>
                                </td>
                            </tr>';
						}

						wp_send_json_success( array(
							'message' => esc_html__('URL status changed') ,
							'box' => $box
						) );
					} elseif( in_array( $url, $disallow_urls ) ){
						if ( isset( $_POST['mode'] ) AND $_POST['mode'] == 'allow' ) {
							delete_post_meta( $license, 'ttls_disallow_site_urls', $url );
							add_post_meta( $license, 'ttls_allow_site_urls', $url );
							$box = '<tr>
                                <td class="text-success">'.esc_html( $url ).'</td>
                                <td>
                                    <div data-url="'.esc_attr( $url ).'" data-license="'.esc_attr( $license ).'" class="btn btn-xs btn-defult ttls_url_move"><i class="fa fa-bullseye"></i></div>
                                    <div title="'.esc_html__('Forbid this URL', 'ttls_translate').'" data-mode="disallow" data-url="'.esc_attr( $url ).'" data-license="'.esc_attr( $license ).'" class="btn btn-xs btn-defult ttls_url_move"><i class="fa fa-times"></i></div>
                                </td>
                            </tr>';
						} else {
							delete_post_meta( $license, 'ttls_disallow_site_urls', $url );
							add_post_meta( $license, 'ttls_site_urls', $url );
							$box = '<tr>
                                <td class="text-muted">'.esc_html( $url ).'</td>
                                <td>
                                    <div title="'.esc_html__('Allow this URL', 'ttls_translate').'" data-mode="allow" data-url="'.esc_attr( $url ).'" data-license="'.esc_attr( $license ).'" class="btn btn-xs btn-defult ttls_url_move"><i class="fa fa-check"></i></div>
                                    <div title="'.esc_html__('Forbid this URL', 'ttls_translate').'" data-mode="disallow" data-url="'.esc_attr( $url ).'" data-license="'.esc_attr( $license ).'" class="btn btn-xs btn-defult ttls_url_move"><i class="fa fa-times"></i></div>
                                </td>
                            </tr>';
						}

						wp_send_json_success( array(
							'message' => esc_html__('URL status changed', 'ttls_translate') ,
							'box' => $box
						) );
					} else {
						wp_send_json_error( array( 'message' => esc_html__('Unknown URL', 'ttls_translate') ) );
					}

				} else {
					wp_send_json_error( array( 'message' => esc_html__('Insufficient rights', 'ttls_translate') ) );
				}
			}

			/**
			 * Change server status. Servers could be in one of the three possible statuses:
			 * <ul><li>Neutral<li><li>Allowed<li><li>Forbidden<li></ul>
			 */
			static function move_server(){
				if ( current_user_can( 'ttls_plugin_admin' ) ) {
					if ( !empty( $_POST['server'] ) ) {
						$srv = sanitize_text_field( $_POST['server'] );
					} else {
						wp_send_json_error( array( 'message' => esc_html__( 'Server was not sent', 'ttls_translate' ) ) );
					}

					if ( !empty( $_POST['license'] ) ) {
						$license = (int) sanitize_key( $_POST['license'] );
					} else {
						wp_send_json_error( array( 'message' => esc_html__( 'License ID was not sent', 'ttls_translate' ) ) );
					}

					$all_servers = get_post_meta( $license, 'ttls_servers' );
					$allow_servers = get_post_meta( $license, 'ttls_allow_servers' );
					$disallow_servers = get_post_meta( $license, 'ttls_disallow_servers' );

					if ( in_array( $srv, $all_servers ) ) {
						$mode = ( isset( $_POST['mode'] ) AND ($_POST['mode'] == 'allow' OR $_POST['mode'] == 'disallow') ) ? sanitize_text_field( $_POST['mode'] ) : get_option('ttls_license_unchecked_servers', 'allow');
						switch ( $mode ) {
							case 'allow':
								delete_post_meta( $license, 'ttls_servers', $srv );
								add_post_meta( $license, 'ttls_allow_servers', $srv );
								wp_send_json_success( array(
									'message' => esc_html__('Server forbidden', 'ttls_translate') ,
									'box' => '<tr>
                                        <td class="text-success">'.esc_html( $srv ).'</td>
                                        <td>
                                            <div data-server="'.esc_attr( $srv ).'" data-license="'.esc_attr( $license ).'" class="btn btn-xs btn-defult ttls_server_move"><i class="fa fa-bullseye"></i></div>
                                            <div title="'.esc_html__('Forbid this server', 'ttls_translate').'" data-mode="disallow" data-server="'.esc_attr( $srv ).'" data-license="'.esc_attr( $license ).'" class="btn btn-xs btn-defult ttls_server_move"><i class="fa fa-times"></i></div>
                                        </td>
                                    </tr>'
								) );
								break;
							case 'disallow':
								delete_post_meta( $license, 'ttls_servers', $srv );
								add_post_meta( $license, 'ttls_disallow_servers', $srv );
								wp_send_json_success( array(
									'message' => esc_html__('Server disallowed') ,
									'box' => '<tr>
                                        <td class="text-danger">'.esc_html( $srv ).'</td>
                                        <td>
                                            <div title="'.esc_html__('Allow this server', 'ttls_translate').'" data-mode="allow" data-server="'.esc_attr( $srv ).'" data-license="'.esc_attr( $license ).'" class="btn btn-xs btn-defult ttls_server_move"><i class="fa fa-check"></i></div>
                                            <div data-server="'.esc_attr( $srv ).'" data-license="'.esc_attr( $license ).'" class="btn btn-xs btn-defult ttls_server_move"><i class="fa fa-bullseye"></i></div>
                                        </td>
                                    </tr>'
								) );
								break;
						}
					} elseif( in_array( $srv, $allow_servers ) ){
						if ( isset( $_POST['mode'] ) AND $_POST['mode'] == 'disallow' ) {
							delete_post_meta( $license, 'ttls_allow_servers', $srv );
							add_post_meta( $license, 'ttls_disallow_servers', $srv );
							$box = '<tr>
                                        <td class="text-danger">'.esc_html( $srv ).'</td>
                                        <td>
                                            <div title="'.esc_html__('Allow this server', 'ttls_translate').'" data-mode="allow" data-server="'.esc_attr( $srv ).'" data-license="'.esc_attr( $license ).'" class="btn btn-xs btn-defult ttls_server_move"><i class="fa fa-check"></i></div>
                                            <div data-server="'.esc_attr( $srv ).'" data-license="'.esc_attr( $license ).'" class="btn btn-xs btn-defult ttls_server_move"><i class="fa fa-bullseye"></i></div>
                                        </td>
                                    </tr>';
						} else {
							delete_post_meta( $license, 'ttls_allow_servers', $srv );
							add_post_meta( $license, 'ttls_servers', $srv );
							$box = '<tr>
                                <td class="text-muted">'.esc_html( $srv ).'</td>
                                <td>
                                    <div title="'.esc_html__('Allow this server', 'ttls_translate').'" data-mode="allow" data-server="'.esc_attr( $srv ).'" data-license="'.esc_attr( $license ).'" class="btn btn-xs btn-defult ttls_server_move"><i class="fa fa-check"></i></div>
                                    <div title="'.esc_html__('Forbid this server', 'ttls_translate').'" data-mode="disallow" data-server="'.esc_attr( $srv ).'" data-license="'.esc_attr( $license ).'" class="btn btn-xs btn-defult ttls_server_move"><i class="fa fa-times"></i></div>
                                </td>
                            </tr>';
						}

						wp_send_json_success( array(
							'message' => esc_html__('Server status changed') ,
							'box' => $box
						) );
					} elseif( in_array( $srv, $disallow_servers ) ){
						if ( isset( $_POST['mode'] ) AND $_POST['mode'] == 'allow' ) {
							delete_post_meta( $license, 'ttls_disallow_servers', $srv );
							add_post_meta( $license, 'ttls_allow_servers', $srv );
							$box = '<tr>
                                <td class="text-success">'.esc_html( $srv ).'</td>
                                <td>
                                    <div data-server="'.esc_attr( $srv ).'" data-license="'.esc_attr( $license ).'" class="btn btn-xs btn-defult ttls_server_move"><i class="fa fa-bullseye"></i></div>
                                    <div title="'.esc_html__('Forbid this server', 'ttls_translate').'" data-mode="disallow" data-server="'.esc_attr( $srv ).'" data-license="'.esc_attr( $license ).'" class="btn btn-xs btn-defult ttls_server_move"><i class="fa fa-times"></i></div>
                                </td>
                            </tr>';
						} else {
							delete_post_meta( $license, 'ttls_disallow_servers', $srv );
							add_post_meta( $license, 'ttls_servers', $srv );
							$box = '<tr>
                                <td class="text-muted">'.esc_html( $srv ).'</td>
                                <td>
                                    <div title="'.esc_html__('Allow this server', 'ttls_translate').'" data-mode="allow" data-server="'.esc_attr( $srv ).'" data-license="'.esc_attr( $license ).'" class="btn btn-xs btn-defult ttls_server_move"><i class="fa fa-check"></i></div>
                                    <div title="'.esc_html__('Forbid this server', 'ttls_translate').'" data-mode="disallow" data-server="'.esc_attr( $srv ).'" data-license="'.esc_attr( $license ).'" class="btn btn-xs btn-defult ttls_server_move"><i class="fa fa-times"></i></div>
                                </td>
                            </tr>';
						}

						wp_send_json_success( array(
							'message' => esc_html__('Server status changed', 'ttls_translate') ,
							'box' => $box
						) );
					} else {
						wp_send_json_error( array( 'message' => esc_html__('Unknown server', 'ttls_translate') ) );
					}

				} else {
					wp_send_json_error( array( 'message' => esc_html__('Insufficient rights', 'ttls_translate') ) );
				}
			}

			/**
			 * Adding server to license. It is required to initialize the class
			 * by specifying the token and license type.
			 *
			 * @param      string            $server  Server
			 *
			 * @return     WP_Error|boolean  true when the server was added, or WP_Error specifying the errors encountered
			 */
			function add_server( $server ){
				if ( $this->license_info ) {
					if ( !is_wp_error( $this->license_info ) ) {
						$servers = get_post_meta( $this->license_info['id'], 'ttls_servers' );
						$allowed_servers = get_post_meta( $this->license_info['id'], 'ttls_allow_servers' );
						$disallowed_servers = get_post_meta( $this->license_info['id'], 'ttls_disallow_servers' );
						if ( empty($servers[0]) ) {
							delete_post_meta( $this->license_info['id'], 'ttls_servers', '');
						}
						if ( !in_array( $server, array_merge( $servers, $allowed_servers, $disallowed_servers ) ) ) {
							if ( add_post_meta( $this->license_info['id'], 'ttls_servers', $server ) ) {
								return true;
							} else {
								return new WP_Error( 'ttls_license_failserver', 'An error encountered while adding the server to database', array( 'status' => 500 ) );
							}
						}
					} else {
						return $this->license_info;
					}
				} else {
					return new WP_Error( 'ttls_license_badinit', 'Incorrect initialization of the example', array( 'status' => 500 ) );
				}
			}

			/**
			 * Adding URL to license. It is required to initialize the class
			 * by specifying the token and license type
			 *
			 * @param      string            $url  URL
			 *
			 * @return     WP_Error|boolean  true when the URL was added, or WP_Error specifying the errors encountered
			 */
			function add_url( $url ){
				if ( $this->license_info ) {
					if ( !is_wp_error( $this->license_info ) ) {
						$urls = get_post_meta( $this->license_info['id'], 'ttls_site_urls' );
						$allowed_urls = get_post_meta( $this->license_info['id'], 'ttls_allow_site_urls' );
						$disallowed_urls = get_post_meta( $this->license_info['id'], 'ttls_disallow_site_urls' );
						if ( empty( $urls[0] ) ) {
							delete_post_meta( $this->license_info['id'], 'ttls_site_urls', '');
						}
						if ( !in_array( $url, array_merge( $urls, $allowed_urls, $disallowed_urls ) ) ) {
							if ( add_post_meta( $this->license_info['id'], 'ttls_site_urls', $url ) ) {
								return true;
							} else {
								return new WP_Error( 'ttls_license_failurl', 'An error encountered while adding the URL to database', array( 'status' => 500 ) );
							}
						}
					} else {
						return $this->license_info;
					}
				} else {
					return new WP_Error( 'ttls_license_badinit', 'Incorrect initialization of the example', array( 'status' => 500 ) );
				}
			}

			/**
			 * Gets list of all licenses.
			 *
			 * @param      integer  $paged  The paged
			 *
			 * @return     array   list of licenses
			 */
			function get_all_list( $paged = 1){
				$all_licenses = array();
				$tl_query = new WP_Query;
				$tl_args = array(
					'post_type'		 => 'ttls_license',
					'posts_per_page' => 10,
					'paged'			 => $paged,
					'meta_query' => array(),
				);
				if ( $this->license_type ) {
					$tl_args['meta_query'][] = array(
						'key' => 'ttls_license_type',
						'value' => $this->license_type
					);
				}
				if ( $this->license_product_id ) {
					$tl_args['meta_query'][] = array(
						'key' => 'ttls_product_id',
						'value' => $this->license_product_id
					);
				}
				$tl_licenses = $tl_query->query( $tl_args );
				$response['count_tickets'] = $tl_query->found_posts;
				foreach ($tl_licenses as $key => $lic) {
					$this_lic = array();
					$this_lic['id'] = $lic->ID;
					$this_lic['verified'] = get_post_meta( $lic->ID, 'ttls_license_verified', true );
					$this_lic['have_support'] = get_post_meta( $lic->ID, 'ttls_license_have_support', true );
					$this_lic['have_support_until'] = get_post_meta( $lic->ID, 'ttls_license_have_support_until', true );

					$this_lic['type'] = get_post_meta( $lic->ID, 'ttls_license_type', true );
					$this_lic['token'] = get_post_meta( $lic->ID, 'ttls_license_token', true );
					$this_lic['servers'] = get_post_meta( $lic->ID, 'ttls_servers' );
					$this_lic['allow_servers'] = get_post_meta( $lic->ID, 'ttls_allow_servers' );
					$this_lic['disallow_servers'] = get_post_meta( $lic->ID, 'ttls_disallow_servers' );
					$this_lic['site_urls'] = get_post_meta( $lic->ID, 'ttls_site_urls' );
					$this_lic['allow_site_urls'] = get_post_meta( $lic->ID, 'ttls_allow_site_urls' );
					$this_lic['disallow_site_urls'] = get_post_meta( $lic->ID, 'ttls_disallow_site_urls' );
					$this_lic['owners'] = get_post_meta( $lic->ID, 'ttls_owners' );
					$product_id = get_post_meta( $lic->ID, 'ttls_product_id', true );
					$this_lic['product_id'] = $product_id;
					$this_lic['product_title'] = empty( $product_id ) ? '' : get_the_title( $product_id );
					if ( current_user_can( 'ttls_clients' ) ) {
						$filter_tickets = new WP_Query;
						$f_tickets = $filter_tickets->query( array(
							'post_type'	=> 'ttls_ticket',
							'nopaging' => true,
							'post__in'	=> get_post_meta( $lic->ID, 'ttls_tickets' ),
							'author'	=> get_current_user_id(),
							'fields'	=> 'ids'
						) );
						$license_info['tickets'] = $f_tickets;
					} else {
						$this_lic['tickets'] = get_post_meta( $lic->ID, 'ttls_tickets' );
					}
					$this_lic = apply_filters( 'ttls_add_license_info_'.$this_lic['type'], $this_lic );
					$all_licenses[] = $this_lic;
				}
				$response['licenses'] = $all_licenses;
				return $response;
			}

			/**
			 * Получает лицензии пользователя
			 *
			 * @param      int  $user_id  The user identifier
			 *
			 * @return     array   list of licenses
			 */
			function get_list( $user_id ){
				$all_licenses = array();
				$tl_query = new WP_Query;
				$tl_args = array(
					'post_type' => 'ttls_license',
					'nopaging' => true,
					'meta_query' => array(
						array(
							'key' => 'ttls_owners',
							'value' => $user_id
						),
					)
				);
				$tl_licenses = $tl_query->query( $tl_args );
				$all_licenses['count'] = $tl_query->found_posts;
				foreach ($tl_licenses as $key => $lic) {
					$this_lic = array();
					$this_lic['id'] = $lic->ID;
					$this_lic['verified'] = get_post_meta( $lic->ID, 'ttls_license_verified', true );
					$this_lic['have_support'] = get_post_meta( $lic->ID, 'ttls_license_have_support', true );
					$this_lic['have_support_until'] = get_post_meta( $lic->ID, 'ttls_license_have_support_until', true );

					$this_lic['type'] = get_post_meta( $lic->ID, 'ttls_license_type', true );
					$this_lic['token'] = get_post_meta( $lic->ID, 'ttls_license_token', true );
					$this_lic['servers'] = get_post_meta( $lic->ID, 'ttls_servers' );
					$this_lic['allow_servers'] = get_post_meta( $lic->ID, 'ttls_allow_servers' );
					$this_lic['disallow_servers'] = get_post_meta( $lic->ID, 'ttls_disallow_servers' );
					$this_lic['site_urls'] = get_post_meta( $lic->ID, 'ttls_site_urls' );
					$this_lic['allow_site_urls'] = get_post_meta( $lic->ID, 'ttls_allow_site_urls' );
					$this_lic['disallow_site_urls'] = get_post_meta( $lic->ID, 'ttls_disallow_site_urls' );
					$this_lic['owners'] = get_post_meta( $lic->ID, 'ttls_owners' );
					$this_lic['product_id'] = get_post_meta( $lic->ID, 'ttls_product_id', true );
					$this_lic['product_title'] = $this_lic['product_id'] ? get_the_title( $this_lic['product_id'] ) : '';
					if ( current_user_can( 'ttls_clients' ) ) {
						$filter_tickets = new WP_Query;
						$f_tickets = $filter_tickets->query( array(
							'post_type'	=> 'ttls_ticket',
							'nopaging' => true,
							'post__in'	=> get_post_meta( $lic->ID, 'ttls_tickets' ),
							'author'	=> get_current_user_id(),
							'fields'	=> 'ids'
						) );
						$this_lic['tickets'] = $f_tickets;
					} else {
						$this_lic['tickets'] = get_post_meta( $lic->ID, 'ttls_tickets' );
					}
					$this_lic = apply_filters( 'ttls_add_license_info_'.$this_lic['type'], $this_lic );
					$all_licenses[$this_lic['type']][] = $this_lic;
				}
				return $all_licenses;
			}

			/**
			 * Receives license info based on ID
			 *
			 * @param      int             $license_id  The license identifier
			 *
			 * @return     WP_Error|array  			Array with data, or WP_Error
			 */
			function get_by_id( $license_id = 0 ){
				if ( $license_id ) {
					$this_license = get_post( $license_id );
					if ( is_null( $this_license ) ) {
						return new WP_Error( 'ttls_license_notfound', 'License was not found in database', array( 'status' => 404 ) );
					}
					$lic_type = get_post_meta( $this_license->ID, 'ttls_license_type', true );
					$product_id = get_post_meta( $this_license->ID, 'ttls_product_id', true );
					$license_info = array(
						'id'				=> $this_license->ID,
						'product_id' => $product_id,
						'product_title' => empty( $product_id ) ? '' : get_the_title( $product_id ),
						'owners'			=> get_post_meta( $this_license->ID, 'ttls_owners' ),
						'token'				=> get_post_meta( $this_license->ID, 'ttls_license_token', true ),
						'type'				=> $lic_type,
						'servers'			=> get_post_meta( $this_license->ID, 'ttls_servers' ),
						'allow_servers' 	=> get_post_meta( $this_license->ID, 'ttls_allow_servers' ),
						'disallow_servers'	=> get_post_meta( $this_license->ID, 'ttls_disallow_servers' ),
						'site_urls'			=> get_post_meta( $this_license->ID, 'ttls_site_urls' ),
						'allow_site_urls' 	=> get_post_meta( $this_license->ID, 'ttls_allow_site_urls' ),
						'disallow_site_urls'	=> get_post_meta( $this_license->ID, 'ttls_disallow_site_urls' ),
						'support_link' => get_option('ttls_'.$lic_type.'_support_renew', ''),

						'verified' 	=> get_post_meta( $this_license->ID, 'ttls_license_verified', true ),
						'have_support' 	=> get_post_meta( $this_license->ID, 'ttls_license_have_support', true ),
						'have_support_until'	=> get_post_meta( $this_license->ID, 'ttls_license_have_support_until', true ),
					);
					if ( current_user_can( 'ttls_clients' ) ) {
						$filter_tickets = new WP_Query;
						$f_tickets = $filter_tickets->query( array(
							'post_type'	=> 'ttls_ticket',
							'nopaging' => true,
							'post__in'	=> get_post_meta( $this_license->ID, 'ttls_tickets' ),
							'author'	=> get_current_user_id(),
							'fields'	=> 'ids'
						) );
						$license_info['tickets'] = $f_tickets;
					} else {
						$license_info['tickets'] = get_post_meta( $this_license->ID, 'ttls_tickets' );
					}
					$license_info = apply_filters( 'ttls_add_license_info_'.$license_info['type'], $license_info );
					return $license_info;
				} else {
					return new WP_Error( 'ttls_license_notfound', 'License was not found in database', array( 'status' => 404 ) );
				}
			}


			/**
			 * Receives license info based on type and token
			 *
			 * @param      string    $license_type   License type
			 * @param      string    $license_token  License token
			 *
			 * @return     WP_Error  ( description_of_the_return_value )
			 */
			function get( $license_type = '', $license_token = '', $license_product_id = '' ){
				$error = new WP_Error;
				$has_error = false;
				$wp_licenses = new WP_Query;

				if ( empty( $license_type ) ) {
					$error->add( 'ttls_license_notype', 'Send license type', array( 'status' => 400 ) );
					$has_error = true;
				}

				if ( empty( $license_token ) ) {
					$error->add( 'ttls_license_notoken', 'Send purchase code', array( 'status' => 400 ) );
					$has_error = true;
				}

				if ( $has_error ) {
					return $error;
				}

				$license_meta_query = array(
					'relation' => 'AND',
					array(
						'key' => 'ttls_license_type',
						'value' => $license_type
					),
					array(
						'key' => 'ttls_license_token',
						'value' => $license_token
					)
				);

				if ( $license_product_id ) {
					$license_meta_query[] = array(
						'key' => 'ttls_product_id',
						'value' => $license_product_id,
					);
				}

				// starting the request
				$licenses = $wp_licenses->query( array(
					'post_type' => 'ttls_license',
					'meta_query' => $license_meta_query,
				) );

				if ( empty( $licenses ) ) {
					$error->add( 'ttls_license_notfound', 'License was not found in database', array( 'status' => 404 ) );
					return $error;
				} else {
					$product = \TTLS\Models\Product::find_by_id( get_post_meta( $licenses[0]->ID, 'ttls_product_id', true ) );
					if ( $product ) {
						$product_licenses = $product->active_licenses();
						if ( ! array_key_exists( $license_type, $product_licenses ) ) {
							$error->add( 'ttls_license_unsupported', 'This license type does not appear to be active', array( 'status' => 404 ) );
							return $error;
						}
					} else {
						$error->add( 'ttls_product_notfound', 'Product was not found in database', array( 'status' => 404 ) );
						return $error;
					}

					$license_data = $product_licenses[$license_type];
					$this->license_settings = $license_data;
					$license_info = array(
						'id'				=> $licenses[0]->ID,
						'owners'				=> get_post_meta( $licenses[0]->ID, 'ttls_owners' ),
						'token'				=> $license_token,
						'type'				=> $license_type,
						'servers'			=> get_post_meta( $licenses[0]->ID, 'ttls_servers' ),
						'allow_servers' 	=> get_post_meta( $licenses[0]->ID, 'ttls_allow_servers' ),
						'disallow_servers'	=> get_post_meta( $licenses[0]->ID, 'ttls_disallow_servers' ),
						'site_urls'			=> get_post_meta( $licenses[0]->ID, 'ttls_site_urls' ),
						'allow_site_urls' 	=> get_post_meta( $licenses[0]->ID, 'ttls_allow_site_urls' ),
						'disallow_site_urls'	=> get_post_meta( $licenses[0]->ID, 'ttls_disallow_site_urls' ),
						'support_link' => get_option('ttls_'.$license_type.'_support_renew', ''),
						'verified' 	=> get_post_meta( $licenses[0]->ID, 'ttls_license_verified', true ),
						'have_support' 	=> get_post_meta( $licenses[0]->ID, 'ttls_license_have_support', true ),
						'have_support_until' => get_post_meta( $licenses[0]->ID, 'ttls_license_have_support_until', true ),
						'product_id' => $product->ID,
					);
					$license_tickets = get_post_meta( $licenses[0]->ID, 'ttls_tickets' );
					if ( current_user_can( 'ttls_clients' ) ) {
						if ( empty( $license_tickets ) ) {
							$license_info['tickets'] = array();
						} else {
							$filter_tickets = new WP_Query;
							$f_tickets = $filter_tickets->query( array(
								'post_type'	=> 'ttls_ticket',
								'nopaging' => true,
								'post__in'	=> $license_tickets,
								'author'	=> get_current_user_id(),
								'fields'	=> 'ids'
							) );
							$license_info['tickets'] = $f_tickets;
						}
					} else {
						$license_info['tickets'] = empty( $license_tickets ) ? array() : $license_tickets;
					}
					return apply_filters( 'ttls_add_license_info_'.$license_type, $license_info );
				}
			}

			/**
			 * Checking license data and adding to system
			 *
			 * @param      array           $new_license  Data of new
			 *                                           license
			 *
			 * @return     WP_Error|array  Error, or array with license data
			 */
			function add( $new_license = false ){
				$error = new WP_Error;
				$has_error = false;
				$good_license = array();
				if ( empty( $new_license ) ) {
					$error->add( 'ttls_license_nodata', 'There is no data for new license', array( 'status' => 400 ) );
					$has_error = true;
				} else {

					if ( empty( $new_license['product_id'] ) ) {
						$error->add( 'ttls_license_noproduct', 'Send product ID', array( 'status' => 400 ) );
						return $error;
					} else {
						$product = \TTLS\Models\Product::find_by_id( $new_license['product_id'] );
						if ( ! $product ) {
							$error->add( 'ttls_license_product_not_found', 'Product not found', array( 'status' => 400 ) );
							return $error;
						}
					}

					if ( isset( $new_license['user'] ) ) {
						$good_license['user'] = $new_license['user'];
					} else {
						$good_license['user'] = get_current_user_id();
					}

					if ( empty( $new_license['license_type'] ) ) {
						$error->add( 'ttls_license_notype', 'Send license type', array( 'status' => 400 ) );
						return $error;
					} else {
						// filter for active licenses for multiple items addon
						$active_licenses = $product->active_licenses();
						if ( array_key_exists( $new_license['license_type'], $active_licenses ) ) {
							$good_license['license_type'] = $new_license['license_type'];
						} else {
							$error->add( 'ttls_license_unsupported', 'This license does not appear to be active', array( 'status' => 404 ) );
							return $error;
						}
					}

					$license_settings = $active_licenses[$good_license['license_type']];

					if ( $good_license['license_type'] == 'standard' ) {

						if ( isset( $new_license['license_verified'] ) ) {
							$good_license['license_verified'] = $new_license['license_verified'];
						} else {
							$good_license['license_verified'] = $license_settings->new_verified;
						}

						$until_date = new \DateTime('now');
						if ( isset( $new_license['license_have_support'] ) ) {
							$good_license['license_have_support'] = $new_license['license_have_support'];
							if ( isset( $new_license['license_have_support_until'] ) ) {
								$good_license['license_have_support_until'] = $new_license['license_have_support_until'];
							} else {
								$until_date->modify('+' . $license_settings->expiry_date . ' day');
								$good_license['license_have_support_until'] = $until_date->format('Y-m-d');
							}
						} else {
							$good_license['license_have_support'] = $license_settings->new_support;
							if ( $good_license['license_have_support'] ) {
								$until_date->modify('+' . $license_settings->expiry_date . ' day');
								$good_license['license_have_support_until'] = $until_date->format('Y-m-d');
							} else {
								$good_license['license_have_support_until'] = $until_date->format('Y-m-d');
							}
						}
						// generate license token
						if ( empty( $new_license['license_token'] ) ) {
							$new_license['license_token'] = wp_generate_uuid4();
							$old_license = $this->get( $good_license['license_type'], $new_license['license_token'] );
							while ( !is_wp_error( $old_license ) ) {
								$new_license['license_token'] = wp_generate_uuid4();
								$old_license = $this->get( $good_license['license_type'], $new_license['license_token'] );
							}
						}
						$good_license['license_token'] = $new_license['license_token'];

					} else {
						$new_license['license_token'] = ( isset( $new_license['license_token'] ) ) ? $new_license['license_token'] : false;

						$license_checker = apply_filters(
							'ttls_sanitize_license_'.$good_license['license_type'],
							array(
								'status' => false,
								'new_license' => $new_license,
								'license_settings' => $license_settings,
								'errors' => array(
									array(
										'code' => 'ttls_license_nofunction_sanitize',
										'message' => 'The verification function of this license type is missing',
										'error_status' => 500,
									),
								),
							)
						);

						if ( $license_checker['status'] ) {
							$good_license = array_merge( $good_license, $license_checker['new_license'] );
						} else {
							foreach ( $license_checker['errors'] as $e_key => $error_data) {
								$error->add(
									$error_data['code'],
									$error_data['message'],
									array( 'status' => $error_data['error_status'] )
								);
							}
							$has_error = true;
						}
					}

					if ( isset( $new_license['servers'] ) ) {
						$good_license['servers'] = $new_license['servers'];
					} else {
						if ( current_user_can( 'ttls_plugin_admin' ) ) {
							$good_license['servers'] = '';
						} else {
							$has_error = true;
							$error->add( 'ttls_license_noserver', 'Server is not identified', array( 'status' => 500 ) );
						}
					}

					if ( isset( $new_license['site_url'] ) ) {
						$good_license['site_url'] = $new_license['site_url'];
					} else {
						if ( current_user_can( 'ttls_plugin_admin' ) ) {
							$good_license['site_url'] = '';
						} else {
							$has_error = true;
							$error->add( 'ttls_license_nositeurl', 'Site URL is not identified', array( 'status' => 500 ) );
						}
					}
				}

				if ( $has_error ) {
					return $error;
				}

				$old_license = $this->get( $good_license['license_type'], $good_license['license_token'] );
				if( ! is_wp_error( $old_license ) ) {
					$multiple_users = empty( $license_settings->multiple_users ) ? false : $license_settings->multiple_users;
					if ( $product->ID === $old_license['product_id'] && ( empty( $old_license['owners'] ) || $multiple_users ) ) {
						return $this->add_user( $old_license['id'], $good_license['user'] );
					} else {
						$error->add( 'ttls_license_used', 'This license is already been used', array( 'status' => 400 ) );
						return $error;
					}

				} else {
					if ( $good_license['license_type'] == 'standard' ) {
						$new_license_args = array(
							'comment_status' => 'closed',
							'ping_status'    => 'closed',
							'post_status'    => 'publish',
							'post_type'      => 'ttls_license',
							'meta_input'     => array(
								'ttls_license_verified' => $good_license['license_verified'],
								'ttls_license_have_support' => $good_license['license_have_support'],
								'ttls_license_have_support_until' => $good_license['license_have_support_until'],
								'ttls_license_type' => $good_license['license_type'],
								'ttls_license_token' => $good_license['license_token'],
								'ttls_owners' => $good_license['user'],
							),
						);

						if ( $good_license['servers'] ) {
							$new_license_args['meta_input']['ttls_servers'] = $good_license['servers'];
						}

						if ( $good_license['site_url'] ) {
							$new_license_args['meta_input']['ttls_site_urls'] = $good_license['site_url'];
						}

						$new_license_id = wp_insert_post( wp_slash( $new_license_args ) );

						if ( is_wp_error( $new_license_id ) ) {
							$error->add( 'ttls_license_fail', 'An error encountered while adding the license to database', array( 'status' => 500 ) );
						} else {
							$license = get_post( $new_license_id );
							if ( is_null( $license ) ) {
								$error->add( 'ttls_license_fail', 'An error encountered while adding the license to database', array( 'status' => 500 ) );
							} else {
								update_post_meta( $new_license_id, 'ttls_product_id', $product->ID );
								return $this->get_by_id( $new_license_id );
							}
						}
					} else {
						$license_adder = apply_filters(
							'ttls_add_license_'. $good_license['license_type'],
							array(
								'status' => false,
								'new_license' => $good_license,
								'license_settings' => $license_settings,
								'errors' => array(
									array(
										'code' => 'ttls_license_nofunction_add',
										'message' => 'The license type inclusion function is missing',
										'error_status' => 500,
									),
								),
							)
						);

						if ( $license_adder['status'] ) {
							update_post_meta( $license_adder['new_license']['id'], 'ttls_product_id', $product->ID );
							return $license_adder['new_license'];
						} else {
							foreach ( $license_adder['errors'] as $e_key => $error_data) {
								$error->add(
									$error_data['code'],
									$error_data['message'],
									array( 'status' => $error_data['error_status'] )
								);
							}
						}

					}
				}

				return $error;
			}
		}
	}