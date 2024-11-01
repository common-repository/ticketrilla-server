<?php

	add_filter( 'login_redirect', 'ttls_redirect_client_after_login', 10, 3 );

	function ttls_redirect_client_after_login( $redirect_to, $request, $user ) {
    if ( isset( $user->roles ) && is_array( $user->roles ) ) {
			if ( in_array( 'ttls_clients', $user->roles ) ) {
				$redirect_to = ttls_url( 'ticketrilla-server-products' );
			}
		}
	return $redirect_to;
	}

	add_filter( 'ttls_pre_the_content', 'ttls_pre_the_content' );
	
	function ttls_json_basic_auth_handler( $user ) {
		global $wp_json_basic_auth_error;
		$wp_json_basic_auth_error = null;
		// Don't authenticate twice
		if ( ! empty( $user ) ) {
			return $user;
		}
		// Check that we're trying to authenticate
		if ( !isset( $_SERVER['PHP_AUTH_USER'] ) ) {
			return $user;
		}
		$username = $_SERVER['PHP_AUTH_USER'];
		$password = $_SERVER['PHP_AUTH_PW'];
		/**
		 * In multi-site, wp_authenticate_spam_check filter is run on authentication. This filter calls
		 * get_currentuserinfo which in turn calls the determine_current_user filter. This leads to infinite
		 * recursion and a stack overflow unless the current function is removed from the determine_current_user
		 * filter during authentication.
		 */
		remove_filter( 'determine_current_user', 'ttls_json_basic_auth_handler', 20 );
		$user = wp_authenticate( $username, $password );
		add_filter( 'determine_current_user', 'ttls_json_basic_auth_handler', 20 );
		if ( is_wp_error( $user ) ) {
			$ttls_error = new WP_Error;
			foreach ( $user->errors as $code => $message ) {
				switch ( $code ) {
					case 'invalid_username':
						$ttls_error->add('ttls_invalid_username', 'This user is not found', array( 'status' => 401 ));
						break;
					case 'incorrect_password':
						$ttls_error->add('ttls_incorrect_password', 'Incorrect password for this user', array( 'status' => 401 ));
						break;
				}

			}
			$wp_json_basic_auth_error = $ttls_error;
			return null;
		}
		$wp_json_basic_auth_error = true;
		return $user->ID;
	}
	add_filter( 'determine_current_user', 'ttls_json_basic_auth_handler', 20 );

	function ttls_json_basic_auth_error( $error ) {
		// Passthrough other errors
		if ( ! empty( $error ) ) {
			return $error;
		}
		global $wp_json_basic_auth_error;
		return $wp_json_basic_auth_error;
	}
	add_filter( 'rest_authentication_errors', 'ttls_json_basic_auth_error' );
	
	function ttlc_link_license_type_compatibility( $license_type ) {
		if ( $license_type === 'ticketrilla' ) {
			$license_type = 'standard';
		}
		return $license_type;
	}	
	add_filter( 'ttls_link_license_type', 'ttlc_link_license_type_compatibility' );
	
	function ttls_link_server_product_license_type_compatibility( $product ) {
		
		// Make Old Clients compatible with standard license

		if ( array_key_exists( 'standard', $product['license_list'] ) && empty( $_POST['ttlc_ver'] ) ) {
			$product['license_list']['ticketrilla'] = $product['license_list']['standard'];
			$product['license_list']['ticketrilla']['title'] = 'Ticketrilla';
			unset( $product['license_list']['standard'] );
		}

		return $product;
	}
	add_filter( 'ttls_link_server_firstproduct', 'ttls_link_server_product_license_type_compatibility' );
	
	function ttls_link_response_data_add_plugin_ver( $data ) {
		if ( is_array( $data ) ) {
			$data['ttls_ver'] = TTLS_PLUGIN_VERSION;
		}
		return $data;
	}
	add_filter( 'ttls_link_response_data', 'ttls_link_response_data_add_plugin_ver' );
	
	add_filter( 'ttls_link_server_firstproduct', 'ttls_link_response_data_add_plugin_ver' );

	add_filter( 'heartbeat_send', 'ttls_heartbeat_send', 10, 2 );
	 
	function ttls_heartbeat_send( $response, $data ) {
		$tickets_count = current_user_can( 'ttls_clients' ) ? get_user_meta( get_current_user_id(), 'ttls_replied_tickets_count', true ) : get_option( 'ttls_pending_tickets_count' );
		if ( (int) $tickets_count ) {
		    $response['ttls_pending_counts'][] = array('selector' => '.ttls__pending-tickets-count', 'value' => $tickets_count);
		}
	  return $response;
	}

