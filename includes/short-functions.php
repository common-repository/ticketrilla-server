<?php

function ttls_url( $page = 'ticketrilla-server', $params = array() ){
	$params['page'] = $page;
	return add_query_arg( $params, get_admin_url( null, 'admin.php' ) );
}

// Checking if .htaccess is present and disabling PHP in it

function ttls_check_plugin_settings(){
	$products = \TTLS\Models\Product::find_one();
	if ( empty( $products['items'] ) && current_user_can( 'ttls_plugin_admin') ) {
		add_action( 'admin_notices', 'ttls_show_link_plugin_settings' );
	}
}

function ttls_show_link_plugin_settings(){ ?>
	<div class="notice notice-warning is-dismissible">
		<p><?php echo esc_html__( 'It is required to configure your server.', 'ttls_translate' ) . ' <a href="' . esc_url( ttls_url( 'ticketrilla-server-product-settings' ) ) . '">' . esc_html__( 'Settings', 'ttls_translate' ) . '</a>'; ?></p>
	</div>
<?php }


function ttls_check_uploads_dir(){
	global $ttls_create_zip;
	global $is_apache;
	$uploads_dir = wp_upload_dir('ttls');
	if ( ( is_array( $uploads_dir ) && $uploads_dir['error'] === false ) && ttls_create_test( $uploads_dir ) ) {
		$remote_get = wp_remote_get( trailingslashit( $uploads_dir['url'] ) . 'test.php' );
		if ( is_array( $remote_get ) ) {
			$body = $remote_get['body'];
			if ( $body !== 'PHP' ) {
				return false;
				$ttls_create_zip = false;
			}
		}
		if ( $is_apache ) {
			// do htaccess check
			$htaccess = $uploads_dir['path'].'.htaccess';
			if ( file_exists( $htaccess ) ) {
				$engine = true;
				foreach ( file( $htaccess ) as $line ) {
					if ( preg_match( "/^\s{0,}[^\#]{0,}\s{0,}php_flag\s{1,}engine\s{1,}(0|off|on|1)/i", $line, $matches ) ) {
						if ( in_array( $matches[1], array('0','off') ) ) {
							$engine = false;
							$ttls_create_zip = false;
						} else {
							$engine = true;
						}
					}
				}
			} else {
				$engine = true;
			}

			if ( $engine ) {
				if ( current_user_can( 'ttls_plugin_admin') ) {
					add_action( 'admin_notices', array( 'TTLS_Attachments', 'print_uploads_error' ) );
				}
				add_action( 'ttls_show_htaccess_generator', 'ttls_show_htaccess_generator' );
				$ttls_create_zip = true;
				return false;
			}
		}
		// have correct htaccess but do php
		// show notice about server config
		if ( current_user_can( 'ttls_plugin_admin') ) {
			add_action( 'admin_notices', 'ttls_configure_server_notice' );
		}
		$ttls_create_zip = true;
		return false;
	}
	// cant write to directory with files
	if ( current_user_can( 'ttls_plugin_admin') ) {
		add_action( 'admin_notices', 'ttls_upload_dir_writable_notice' );
	}
	$ttls_create_zip = true;
	return false;
}

function ttls_configure_server_notice() {
	$upload_dir = wp_upload_dir( 'ttls' ); ?>
	<div class="notice notice-warning is-dismissible">
		<p><?php echo esc_html__( 'Please, configure your server to prevent file execution in directory: ', 'ttls_translate' ) . $upload_dir['path']; ?></p>
	</div>
<?php }

function ttls_upload_dir_writable_notice() { ?>
    <div class="notice notice-warning">
        <p><?php esc_html_e( 'Please, check if uploads directory is writable.', 'ttls_translate' ); ?></p>
    </div>
<?php }

function ttls_create_test( $upload_dir ){
	global $wp_filesystem;
	require_once ( ABSPATH . '/wp-admin/includes/file.php' );
	WP_Filesystem();
	$test_php_path = trailingslashit( $upload_dir['path'] ) . 'test.php';
	if ( $wp_filesystem->exists( $test_php_path ) ) {
		return true;
	} else {
		if ( $wp_filesystem->is_writable( $upload_dir['path'] ) ) {
			$wp_filesystem->put_contents( $test_php_path, '<?php echo "PHP"; ?>' );
		}
	}
	return false;
}

function ttls_show_htaccess_generator(){
	$uploads_dir = wp_upload_dir('ttls');
	echo '<div class="form-group">';
		echo '<a id="ttls_generate_htaccess" class="btn btn-block btn-danger btn-xs">'.esc_html__('Generate correct .htaccess', 'ttls_translate').'</a>';
		echo '<span class="help-block"><pre>'.esc_html__('File location:', 'ttls_translate').'<br>'.esc_html( $uploads_dir['path'] ).'.htaccess'.'<br>'.esc_html( sprintf( __( 'If file exists just add line: %s', 'ttls_translate' ), 'php_flag engine off' ) ).'</span>';
	echo '</div>';
}

function ttls_generate_htaccess(){
	if ( current_user_can( 'ttls_plugin_admin' ) ) {
		$response = array( 'status' => false, 'message' => '' );
		global $wp_filesystem;
		require_once ( ABSPATH . '/wp-admin/includes/file.php' );
		WP_Filesystem();
		$uploads_dir = wp_upload_dir('ttls');
		$htaccess = $uploads_dir['path'].'.htaccess';

		if ( $wp_filesystem->exists( $htaccess ) ) {
			$response['message'] = esc_html__('.htaccess file is present.', 'ttls_translate');
		} else {
			$response['message'] = esc_html__('.htaccess has been created.', 'ttls_translate');
		}

		if ( $wp_filesystem->put_contents( $htaccess, "\n php_flag engine 0" ) ) {
			$response['status'] = true;
			$response['message'] .= '<br>'.esc_html__('.htaccess has been updated.', 'ttls_translate');
		} else {
			$response['status'] = false;
			$response['message'] .= '<br>'.esc_html__('Error writing to file.', 'ttls_translate');
		}
	} else {
		$response = array( 'status' => false, 'message' => esc_html__('You do not have sufficient rights.', 'ttls_translate') );
	}

	if ( $response['status'] ) {
		wp_send_json_success( array( 'message' => $response['message'] ) );
	} else {
		wp_send_json_error( array( 'message' => $response['message'] ) );
	}
}

function ttls_settings_generate(){
	parse_str( $_POST['fields'], $fields );
	if ( !empty( $fields['server'] ) ) {
		$code = 'TTL Server: '. esc_url( $fields['server'] );
	} else {
		$code = 'TTL Server: '. esc_url( get_site_url() );
	}


	if ( ! empty( $fields['description'] ) ) {
		// we do not translate this as it is used in style.css of theme or main file in plugin
		$code .= "<br>" . 'TTL Description: '. esc_html( $fields['description'] );
	}
	if ( empty( $fields['slug'] ) ) {
		$message = '<p class="text-danger">' . esc_html__( 'Product slug is undefined. Please, enter product title in support product settings section and save section.', 'ttls_translate' ) . '</p>';
	} else {
		// we do not translate this as it is used in style.css of theme or main file in plugin
		$code .= "<br>" . 'TTL Slug: '.esc_html( sanitize_text_field( $fields['slug'] ) );
		$message = '<pre>' . wp_kses_post( $code ) . '</pre>';
	}
	wp_send_json_success( array(
		'message' => $message,
	) );
}

function ttls_upgrade_plugin_version() {
	if ( is_admin() ) {
		$upgraded_ver = get_option( 'ttls_upgraded_version' );
		if ( ! $upgraded_ver || version_compare( $upgraded_ver, TTLS_PLUGIN_VERSION, '<' ) ) {
			ttls_upgrade_tickets();
			ttls_migrate_single_product();
			update_option( 'ttls_upgraded_version', TTLS_PLUGIN_VERSION );
			
		}
	}
}

function ttls_upgrade_tickets() {
	
	// Delete ttls_status meta = free of ticket responses
	
	$args = array(
	    'post_type'  => 'ttls_ticket',
	    'nopaging'   => true,
	    'meta_query' => array(
	        array(
	            'key'     => 'ttls_status',
	            'value'   => 'free',
	            'compare' => '=',
	        ),
	    ),
	);
	foreach ( get_posts( $args ) as $ticket ) {
		if( $ticket->post_parent ) {
			delete_post_meta( $ticket->ID, 'ttls_status' );
		}
	}
}

function ttls_migrate_single_product() {
	$products = \TTLS\Models\Product::find_one();
	if ( empty( $products['items'] ) ) {
		// Get single product data
		$active_licenses = get_option( 'ttls_active_licenses', array('standard'));
		$product = new \TTLS\Models\Product( array(
			'post_title' => get_option('ttls_product_title', ''),
			'post_name' => get_option('ttls_product_slug', ''),
			'post_content' => get_option('ttls_product_description', ''),
			'type' => get_option('ttls_product_type', 'theme'),
			'author_name' => get_option('ttls_product_author_name', ''),
			'author_link' => get_option('ttls_product_author_link', ''),
			'manual' => get_option('ttls_product_manual', ''),
			'terms' => get_option('ttls_product_terms', ''),
			'privacy' => get_option('ttls_product_privacy', ''),
			'image' => get_option('ttls_product_image', ''),
			'open_registration' => get_option('ttls_open_registration', true ),
			'licenses' => array(
				'standard' => array(
					'enabled' => in_array( 'standard', $active_licenses ) ? 'y' : '',
					'multiple_users' => get_option('ttls_license_multiple_users_standard', false ) ? 'y' : '',
					'new_verified' => get_option('ttls_standard_license_new', false ) ? 'y' : '',
					'new_support' => get_option('ttls_standard_license_new_support', false ) ? 'y' : '',
					'extend_support_link' => get_option('ttls_standard_support_renew', ''),
					'expiry_date' => get_option('ttls_standard_license_new_support_until', 30 ),
				),
				'envato' => array(
					'enabled' => in_array( 'envato', $active_licenses ) ? 'y' : '',
					'token' => get_option( 'ttls_envato_author_token', false ),
					'product_id' => get_option( 'ttls_envato_author_item', false ),
					'extend_support_link' => get_option( 'ttls_envato_support_renew', '' ),
					'cache_time' => get_option( 'ttls_envato_cache_time', 24 ),
					'multiple_users' => get_option( 'ttls_license_multiple_users_envato', false ) ? 'y' : '',
				),
			),
			) 
		);

		$product_id = $product->save();

		if ( ! is_wp_error( $product_id ) ) {
			$licenses = \TTLS\Models\License::find_all();
			if ( ! empty( $licenses['items'] ) ) {
				foreach( $licenses['items'] as $license ) {
					update_post_meta( $license->ID, 'ttls_product_id', $product_id );
				}
			}
			$tickets = \TTLS\Models\Ticket::find_all();
			if ( ! empty( $tickets['items'] ) )  {
				foreach( $tickets['items'] as $ticket ) {
					update_post_meta( $ticket->ID, 'ttls_product_id', $product_id );
				}
			}
		}
	}
}


function ttls_calculate_tickets( $status = array(), $author = '', $license_id = '' ) {
	$args = array(
	    'post_type'  => 'ttls_ticket',
	    'nopaging'   => true,
			'author' => $author,
	    'meta_query' => array(
	        array(
	            'key'     => 'ttls_status',
	            'value'   => $status,
	            'compare' => 'IN',
	        ),
	    ),
	);
	if ( $license_id ) {
		$args['meta_query'][] = array(
			'key' => 'ttls_license',
			'value' => $license_id,
		);
	}
	$query = new WP_Query( $args );
	return $query->found_posts;
}

function ttls_calculate_pending_tickets() {
	return ttls_calculate_tickets( array('free', 'pending') );
}

function ttls_calculate_replied_tickets( $client_id, $license_id = '' ) {
	return ttls_calculate_tickets( array('replied'), $client_id, $license_id );
}

function ttls_update_pending_tickets_count() {
	update_option( 'ttls_pending_tickets_count', ttls_calculate_pending_tickets() );
}

function ttls_update_replied_tickets_count( $ticket ) {
	if ( empty( $ticket->post_parent ) ) {
		$parent_ticket = $ticket;
	} else {
		$parent_ticket = get_post( $ticket->post_parent );
	}
	$client_id = $parent_ticket->post_author;
	update_user_meta( $client_id, 'ttls_replied_tickets_count', ttls_calculate_replied_tickets( $client_id ) );
}

function ttls_pending_count_html( $count, $subject ) {
	$class = sprintf( 'ttls__pending-%s-count update-plugins', $subject );
	return $count ? sprintf( ' <span class="%s">%d</span>', esc_attr( $class ), esc_html( $count ) ) : sprintf( ' <span class="%s count-0"></span>', esc_attr( $class ) );
}

function ttls_telegram_get_me( $token ) {
	return ttls_telegram_api_request( $token, 'getMe' );
}

function ttls_telegram_get_chat( $token, $chat_id ) {
	return ttls_telegram_api_request( $token, 'getChat', array('chat_id' => $chat_id) );
}

function ttls_telegram_send_message( $ticket ) {
	$token = get_option( 'ttls_notifications_telegram_token', '' );
	$chat_id = get_option( 'ttls_notifications_telegram_chat_id', '' );

	if ( $token && $chat_id ) {
		$text = ttls_format_telegram_notification( $ticket );
		if ( $text ) {
			return ttls_telegram_api_request( $token, 'sendMessage', array('chat_id' => $chat_id, 'text' => $text) );
		}
	}
	return false;
}

function ttls_telegram_api_request( $token, $method, $params = array() ) {
	$url = 'https://api.telegram.org/bot' . $token .'/' . $method;
	$options = array(
	    'http' => array(
	        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
	        'method'  => 'POST',
	        'content' => http_build_query( $params ),
	    )
	);
	$context = stream_context_create( $options );
	$result = file_get_contents( $url, false, $context );
	return $result;
}

function ttls_format_telegram_notification( $ticket ) {
	$response = TTLS()->response_service()->prepare_response_data( new TTLS\Models\Response( $ticket ) );
	
	if ( $response['author_pos'] == 'Client' ) {

		$date = (new \DateTime($response['time']))->format('d-m-Y H:i');

		if ( empty( $response['parent_id'] ) ) {
			$ticket_id = $response['id'];
		} else {
			$ticket_id = $response['parent_id'];
		}
		
		$title = '#'.esc_html( $ticket_id . ': '.stripcslashes( get_post($ticket_id)->post_title ) );
	
		$action = TTLS()->response_service()::get_localized_title( array(
			'type' => $response['type'],
			'prepend' => esc_html( $response['author'] ),
		) );
		
		$text = $action . ': ' . $title . ' [' . $date . ']';
		return $text;
	}
	
	return false;
}

function ttls_telegram_notify( $ticket ) {
	ttls_telegram_send_message( $ticket );
}

/**
 * adds attribute for links
 *
 * @param      string  $content  The content
 *
 * @return     string  filtered content
 */

function ttls_pre_the_content( $content ) {
	$re = "/(<a\\b[^<>]*href=['\"]?http[^<>]+)>/is";
	$subst = "$1 target=\"_blank\">";
	return preg_replace( $re, $subst, $content );
}

/**
 * defines if rest api registration is on
 *
 * @return     bool
 */

function ttls_open_rest_api_registration() {
	$open = false;
	$products = \TTLS\Models\Product::find_all_available();
	if ( ! empty( $products['items'] ) ) {
		foreach( $products['items'] as $product ) {
			if ( $product->open_registration ) {
				$open = true;
				break;
			}
		}
	}
	return $open;
}

/**
 * checks if client registration is on
 *
 * @return     void
 */

function ttls_check_client_registration_on() {
	if ( current_user_can( 'ttls_plugin_admin') && ( ! get_option( 'users_can_register' ) || get_option( 'default_role' ) != 'ttls_clients' ) && ttls_open_rest_api_registration() ) {
		add_action( 'admin_notices', 'ttls_client_registration_off_notice' );
	}
}

function ttls_client_registration_off_notice() { ?>
	<div class="notice notice-warning is-dismissible">
		<p><?php esc_html_e( 'Some of your products are open for registration through Ticketrilla: Client, but standard WordPress registration is not set up.', 'ttls_translate' ); ?></p>
		<p><?php esc_html_e( 'Required:', 'ttls_translate' ); ?></p>
		<p>- <?php esc_html_e( 'Enable registration (Anyone can register)', 'ttls_translate' ); ?></p>
		<p>- <?php esc_html_e( 'Set the default role to "Client"', 'ttls_translate' ); ?></p>
		<p><?php echo sprintf( esc_html__( 'You can configure it here: %s', 'ttls_translate' ), '<a href="' . esc_url( get_admin_url( null, 'options-general.php' ) ) . '">' . __( 'General Settings' ) . '</a>' ); ?></p>
	</div>
<?php
}

function ttls_remove_addons_notices_check() {
	if ( ! empty( $GLOBALS['TTLN'] ) ) {
		remove_action( 'admin_notices', array($GLOBALS['TTLN'], 'no_addon_class_notice') );
		add_action( 'admin_notices', 'ttls_remove_addons_notice' );
	}
	if ( ! empty( $GLOBALS['TTLCSU'] ) ) {
		remove_action( 'admin_notices', array($GLOBALS['TTLCSU'], 'no_addon_class_notice') );
		add_action( 'admin_notices', 'ttls_remove_addons_notice' );
	}
	if ( ! empty( $GLOBALS['TTLCSI'] ) ) {
		remove_action( 'admin_notices', array($GLOBALS['TTLCSI'], 'no_addon_class_notice') );
		add_action( 'admin_notices', 'ttls_remove_addons_notice' );
	}
	if ( class_exists( 'TTLS_Addon_License_Envato' ) ) {
		require_once ABSPATH .'/wp-admin/includes/plugin.php';
		deactivate_plugins( 'ticketrilla-server-addon-license-envato/license-envato.php');
		add_action( 'admin_notices', 'ttls_remove_addons_notice' );
	}
}

function ttls_remove_addons_notice() { ?>
	<div class="notice notice-warning is-dismissible">
		<p><?php esc_html_e( 'Addons functionality is included in Ticketrilla: Server. It is recommended to remove addons.', 'ttls_translate' ); ?></p>
	</div>
<?php
}

function ttls_render( $html ) {
	echo $html;
}
