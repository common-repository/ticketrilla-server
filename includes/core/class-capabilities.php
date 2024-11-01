<?php
	// Exit if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	if ( ! class_exists( 'TTLS_Capabilities' ) ) {
		class TTLS_Capabilities {

			var $user = false;
			var $license_token = false;
			var $license_type = false;
			var $this_license = false;


			function __construct( $license_token = false, $license_type = false, $usermeta = false ) {
				$this->user = wp_get_current_user();
				$this->license_token = $license_token;
				$this->license_type  = $license_type;

				if ( current_user_can( 'ttls_plugin_admin' ) ) {
					$this->license_token = 'plugin_admin';
					$this->license_type  = 'plugin_admin';
				} elseif ( current_user_can( 'ttls_developers' ) ) {
					$this->license_token = 'plugin_developer';
					$this->license_type  = 'plugin_developer';
				}

				if ( $usermeta ) {
					$this->ttls_servers  = $usermeta['ttls_servers'];
					$this->ttls_site_urls  = $usermeta['ttls_site_urls'];
				} else {
					$this->ttls_servers  = '';
					$this->ttls_site_urls  = '';
				}
				$this->check_license( true );
			}

			/**
			 * License and support check
			 *
			 * @param      boolean           $stealth  Quite mode - no error
			 *                                         logging
			 *
			 * @return     WP_Error|boolean  	Error or true
			 */
			function check_license( $stealth = false ){
				if ( current_user_can( 'ttls_plugin_admin' ) ) {
					if ( !$stealth ) {
						return true;
					}
				} elseif ( current_user_can( 'ttls_developers' ) ) {
					if ( !$stealth ) {
						return true;
					}
				} else {

					if ( ! $this->license_type ) {
						$this->this_license =  new WP_Error( 'ttls_license_notype', 'No license type', array( 'status' => 403) );
						return $this->this_license;
					}

					if ( ! $this->license_token ) {
						$this->this_license =  new WP_Error( 'ttls_license_notype', 'No license purchase code', array( 'status' => 403) );
						return $this->this_license;
					}

					$license_checker = new TTLS_License( $this->license_type, $this->license_token);

					if ( is_wp_error( $license_checker->license_info ) ) {
						$this->this_license = $license_checker->license_info;
						return $this->this_license;
					} else {
						$this->this_license = $license_checker->license_info;
					}

					if ( $this->ttls_servers ) {
						if ( in_array( $this->ttls_servers, $this->this_license['disallow_servers'] ) ) {
							$this->this_license = new WP_Error( 'ttls_license_disallowserver', 'Your host ip is not supported. Ticketing options are disabled. Please contact your admin directly.', array( 'status' => 403 ) );
							return $this->this_license;
						} else {
							if ( !empty( $this->this_license['allow_servers'] ) ) {
								if ( !in_array( $this->ttls_servers, $this->this_license['allow_servers'] ) ) {
									$this->this_license =  new WP_Error( 'ttls_license_disallowserver', 'Your host ip is not supported. Ticketing options are disabled. Please contact your admin directly.', array( 'status' => 403 ) );
									return $this->this_license;
								}
							}
							$license_checker->add_server( $this->ttls_servers );
						}
					}

					if ( $this->ttls_site_urls ) {
						if ( in_array( $this->ttls_site_urls, $this->this_license['disallow_site_urls'] ) ) {
							$this->this_license =  new WP_Error( 'ttls_license_disallowsite_url', 'Your host URL is not supported. Ticketing options are disabled. Please contact your admin directly.', array( 'status' => 403 ) );
							return $this->this_license;
						} else {
							if ( !empty( $this->this_license['allow_site_urls'] ) ) {
								if ( !in_array( $this->ttls_site_urls, $this->this_license['allow_site_urls'] ) ) {
									
									$this->this_license =  new WP_Error( 'ttls_license_disallowsite_url', 'Your host URL is not supported. Ticketing options are disabled. Please contact your admin directly.', array( 'status' => 403 ) );
									return $this->this_license;
								}
							}
							$license_checker->add_url( $this->ttls_site_urls );
						}
					}

					if ( !in_array( $this->user->ID, $license_checker->license_info['owners'] ) ) {
						$this->this_license =  new WP_Error( 'ttls_license_notyour', 'License purchase code not your', array( 'status' => 401 ) );
						return $this->this_license;
					}
					if ( $this->license_type == 'standard') {

						if ( !$license_checker->license_info['verified'] ) {
							$this->this_license =  new WP_Error( 'ttls_license_unverified', 'This license has not been verified.', array( 'status' => 403 ) );
							return $this->this_license;
						}

					} else {
						$license_info = array(
							'status'		=> false,
							'license_object' => $license_checker,
							'stealth'		=> $stealth,
							'errors' => array(
								array(
									'code' => 'ttls_license_nofunction_check',
									'message' => 'A function for verifying this type of license is missing',
									'error_status' => 500,
								),
							),
						);
						$license_info = apply_filters( 'ttls_check_license_'.$this->license_type, $license_info );
						if ( $license_info['status'] ) {
							$this->this_license = $license_info['license'];
							return $this->this_license;
						} else {
							$this->this_license = new WP_Error;
							foreach ( $license_info['errors'] as $e_key => $error_data) {
								$this->this_license->add(
									$error_data['code'],
									$error_data['message'],
									array( 'status' => $error_data['error_status'] )
								);
							}
							return $this->this_license;
						}
					}
				}
			}

			/**
			 * A check to verify if the current user has sufficient rights for uploading attachments
			 *
			 * @return     WP_Error|boolean  	Error, or true
			 */
			function can_load_attachments(){
				if ( current_user_can( 'ttls_plugin_admin' OR current_user_can( 'ttls_developers' )) ) {
					return true;
				} else {
					if ( !$this->this_license ) {
						$this->check_license( true );
					}
					if ( is_wp_error( $this->this_license ) ) {
						return $this->this_license;
					}
					if ( $this->this_license['verified'] ) {
						if ( get_option('ttls_attachments_autoload_license', false) ) {
							if ( $this->this_license['have_support'] ) {
								return true;
							} else {
								new WP_Error( 'ttls_attachment_nosupport', 'For uploading attachments, an active license subscription (with support) is required.', array( 'status' => 403 ) );
							}
						} else {
							return true;
						}
					} else {
						new WP_Error( 'ttls_license_unverified', 'This license is not verified', array( 'status' => 403 ) );
					}
				}
			}

			/**
			 * A check to verify if the current user has sufficient rights for editing tickets
			 *
			 * @param      int               	$ticket_id  	Ticket ID
			 *
			 * @return     WP_Error|boolean  	Error, true, or licensing data (for client)
			 */
			function can_edit_ticket( $ticket_id ){
				$ticket = get_post( $ticket_id );
				if ( current_user_can( 'ttls_plugin_admin' ) ) {
					return true;
				} else {
					if ( $ticket->post_author == get_current_user_id() ) {
						if ( !$this->this_license ) {
							$this->check_license( true );
						}
						if ( !is_wp_error( $this->this_license['tickets'] ) AND in_array( $ticket->ID, $this->this_license['tickets'] ) ) {
							return array(
								'status' => $ticket->ttls_status,
								'license_info' => array(
									'license_token' => $this->license_token,
									'license_type' => $this->license_type,
									'usermeta' => array(
										'ttls_servers' => $this->ttls_servers,
										'ttls_site_urls' => $this->ttls_site_urls,
									)
								)
							);
						} else {
							return new WP_Error( 'ttls_capabilities_notyour', 'This ticket is linked to a another (different) subscription', array( 'status' => 403 ) );
						}
					} else {
						return new WP_Error( 'ttls_capabilities_notyour', 'This is not your ticket', array( 'status' => 403 ) );
					}
				}
			}

			/**
			 * A check to verify if the current user has sufficient rights for receiving ticket data
			 *
			 * @param      int               	$ticket_id  	Ticket ID
			 *
			 * @return     WP_Error|boolean  	Error, true, or licensing data (for client)
			 */
			function can_get_ticket( $ticket_id ){
				if ( current_user_can( 'ttls_plugin_admin' ) ) {
					return true;
				} else {
					$this_ticket = get_post( $ticket_id );
					if ( ! $this_ticket ) {
						return new WP_Error( 'ttls_capabilities_ticket_notfound', 'Ticket not found', array( 'status' => 404 ) );
					}

					if ( current_user_can( 'ttls_developers' ) ) {
						if ( get_post_meta( $ticket_id, 'ttls_status', true ) == 'free' ) {
							return true;
						} else {
							if ( $this->user->ID == get_post_meta( $ticket_id, 'ttls_ticket_developer', true ) ) {
								return true;
							} else {
								return new WP_Error( 'ttls_capabilities_notyour', 'In work, handled by another agent', array( 'status' => 403 ) );
							}
						}
					} else {
						if ( $this_ticket->post_author == $this->user->ID ) {
							if ( !$this->this_license ) {
								$this->check_license( true );
							}
							if ( is_wp_error( $this->this_license ) ) {
								return $this->this_license;
							}
							if ( $this->this_license['tickets'] AND in_array( $this_ticket->ID, $this->this_license['tickets'] ) ) {
								return true;
							} else {
								return new WP_Error( 'ttls_capabilities_notyour', 'This ticket is attached to another license', array( 'status' => 403 ) );
							}

						} else {
							return new WP_Error( 'ttls_capabilities_notyour', 'This is not your ticket', array( 'status' => 403 ) );
						}
					}
				}
			}

			/**
			 * A check to verify if the current user has sufficient rights for replying to a ticket
			 *
			 * @param      int               $ticket_parent  	Ticket ID
			 *
			 * @return     WP_Error|boolean  WP_Error|boolean  	Error, true, or licensing data (for client)
			 */
			function can_add_response( $ticket_parent ){
				$this_ticket = get_post( $ticket_parent );
				if ( is_null( $this_ticket ) ) {
					return new WP_Error( 'ttls_data_noticketid', 'Ticket ID is missing', array( 'status' => 400 ) );
				}
				if ( current_user_can( 'ttls_plugin_admin' ) ) {
					return true;
				} else {

					if ( current_user_can( 'ttls_developers' ) ) {
						if ( get_post_meta( $this_ticket->ID, 'ttls_status', true ) == 'free' ) {
							return true;
						} else {
							$dev_of_ticket = get_post_meta( $this_ticket->ID, 'ttls_ticket_developer', true );
							if ( empty( $dev_of_ticket ) ) {
								return true;
							}
							if ( $this->user->ID == $dev_of_ticket ) {
								return true;
							} else {
								return new WP_Error( 'ttls_capabilities_notyour', 'In work, handled by another agent', array( 'status' => 403 ) );
							}
						}
					} else {
						if ( $this_ticket->post_author == $this->user->ID ) {
							if ( !$this->this_license ) {
								$this->check_license( true );
							}
							if ( is_wp_error( $this->this_license ) ) {
								return $this->this_license;
							}
							if ( in_array( $this_ticket->ID, $this->this_license['tickets'] ) ) {
								return true;
							} else {
								return new WP_Error( 'ttls_capabilities_notyour', 'This ticket is attached to another license', array( 'status' => 403 ) );
							}
						} else {
							return new WP_Error( 'ttls_capabilities_notyour', 'This is not your ticket', array( 'status' => 403 ) );
						}
					}
				}
			}

			/**
			 * A check to verify if the current user has sufficient rights for creating new tickets
			 *
			 * @return     WP_Error|boolean  WP_Error|boolean  		Error, true, or licensing data (for client)
			 */
			function can_add_ticket(){
				if ( current_user_can( 'ttls_plugin_admin' ) ) {
					return true;
				} else {
					if ( current_user_can( 'ttls_clients' ) ) {
						if ( !$this->this_license ) {
							$this->check_license( true );
						}
						if ( is_wp_error( $this->this_license ) ) {
							return $this->this_license;
						}
						if ( $this->this_license['verified'] ) {
							$current_user = wp_get_current_user();
							$clients_add_ticket_time = get_option('ttls_clients_add_ticket_time', 0); // if not set - clients can add tickets always
							// compare this
							if ( $clients_add_ticket_time ) { // if disabled
								$license_last_ticket = get_post_meta( $this->this_license['id'], 'ttls_license_last_ticket', true);
								if ( $license_last_ticket ) { // last client ticket time
									$now_timestamp = (new \DateTime('now'))->getTimestamp();
									$compare = $clients_add_ticket_time * 60; // need in seconds
									if ( $now_timestamp > $compare + $license_last_ticket ) {
										return array( 'id' => $this->this_license['id'], 'ttls_servers' => $this->ttls_servers, 'ttls_site_urls' => $this->ttls_site_urls, 'user_mail' => $current_user->user_email );
									} else {
										return new WP_Error( 'ttls_capabilities_timing', 'You are sending tickets too frequently', array( 'status' => 403 ) );
									}
								} else { // if null - no tickets by this license
									return array( 'id' => $this->this_license['id'], 'ttls_servers' => $this->ttls_servers, 'ttls_site_urls' => $this->ttls_site_urls, 'user_mail' => $current_user->user_email );
								}
							} else {
								return array( 'id' => $this->this_license['id'], 'ttls_servers' => $this->ttls_servers, 'ttls_site_urls' => $this->ttls_site_urls, 'user_mail' => $current_user->user_email );
							}
						} else {
							return new WP_Error( 'ttls_license_unverified', 'This license is not supported', array( 'status' => 403 ) );
						}
					} else {
						return new WP_Error( 'ttls_capabilities_noclient', 'Only clients can open tickets', array( 'status' => 403 ) );
					}
				}
			}

		}
	}