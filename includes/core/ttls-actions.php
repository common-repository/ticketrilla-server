<?php

	add_action( 'wp_ajax_ttls_add_license', array( 'TTLS_License', 'add_license' ) );
	add_action( 'wp_ajax_ttls_edit_license', array( 'TTLS_License', 'edit_license' ) );
	add_action( 'wp_ajax_ttls_update_license', array( 'TTLS_License', 'update_license' ) );
	add_action( 'wp_ajax_ttls_delete_license', array( 'TTLS_License', 'delete_license' ) );

	add_action( 'wp_ajax_ttls_save_product', array( 'TTLS_License', 'save_product' ) );
	add_action( 'wp_ajax_ttls_admin_save_product', array( TTLS()->admin_page(), 'admin_save_product' ) );

	add_action( 'wp_ajax_ttls_move_server', array( 'TTLS_License', 'move_server' ) );
	add_action( 'wp_ajax_ttls_move_url', array( 'TTLS_License', 'move_url' ) );

	add_action( 'wp_ajax_ttls_check_ticket_status', array( TTLS()->ticket_service(), 'ajax_check_status' ) );

	add_action( 'wp_ajax_ttls_update_ticket', array( TTLS()->ticket_service(), 'ajax_edit_ticket' ) );
	add_action( 'wp_ajax_ttls_take_ticket', array( TTLS()->ticket_service(), 'ajax_take_ticket' ) );
	add_action( 'wp_ajax_ttls_widget_check_free', array( 'TTLS_Widget', 'html_free_tickets_ajax' ) );
	add_action( 'ttls_developers_tickets', array( TTLS()->ticket_service(), 'recalc_agents_tickets' ) );

	add_action( 'wp_ajax_ttls_add_ticket', array( TTLS()->ticket_service(), 'ajax_add_ticket' ) );
	add_action( 'wp_ajax_ttls_add_response', array( TTLS()->ticket_service(), 'ajax_add_response' ) );
	add_action( 'wp_ajax_ttls_client_edit_ticket', array( TTLS()->ticket_service(), 'ajax_client_edit_ticket' ) );

	add_action( 'wp_ajax_ttls_add_response_attachment', array( 'TTLS_Attachments', 'ajax_load_temp_files' ) );
	add_action( 'wp_ajax_ttls_delete_response_attachment', array( 'TTLS_Attachments', 'delete_temp_files' ) );
	add_action( 'wp_ajax_ttls_manual_load_attachment', array( 'TTLS_Attachments', 'manual_load_attachment' ) );


	add_action( 'wp_ajax_ttls_delete_user', array( 'TTLS_Users', 'ajax_delete_user' ) );
	add_action( 'wp_ajax_ttls_edit_developer', array( 'TTLS_Users', 'ajax_edit_developer' ) );
	add_action( 'wp_ajax_ttls_update_developer', array( 'TTLS_Users', 'ajax_update_developer' ) );
	add_action( 'wp_ajax_ttls_create_developer', array( 'TTLS_Users', 'ajax_create_developer' ) );
	add_action( 'wp_ajax_ttls_create_client', array( 'TTLS_Users', 'ajax_create_client' ) );


	add_action( 'wp_ajax_ttls_widget_save_position', array( 'TTLS_Widget', 'save_position' ) );



	add_action( 'init', 'ttls_check_uploads_dir' );
	add_action( 'init', 'ttls_check_plugin_settings' );
	add_action( 'init', 'ttls_upgrade_plugin_version' );
	add_action( 'init', 'ttls_check_client_registration_on' );
	
	add_action( 'wp_ajax_ttls_generate_htaccess', 'ttls_generate_htaccess' );
	add_action( 'wp_ajax_ttls_settings_generate', 'ttls_settings_generate' );


	// hook for registering automatic ticket closure
	add_action( 'init', 'ttls_autoclose');
	// hook for automatic ticket closure
	add_action('ttls_autoclose_ticket', array( TTLS()->ticket_service(), 'autoclose' ) );
	
	add_action( 'ttls_after_add_ticket', 'ttls_update_pending_tickets_count' );
	add_action( 'ttls_after_add_ticket', 'ttls_update_replied_tickets_count' );
	add_action( 'ttls_after_add_ticket', 'ttls_telegram_notify' );

	add_action( 'wp_loaded', 'ttls_remove_addons_notices_check' );
	/**
	 * Create cron actions if autoclose enabled
	 */
	function ttls_autoclose() {
		if ( get_option( 'ttls_autoclose_ticket', false ) ) { // when automatic closure is enabled

			$autoclose_ticket_time = get_option('ttls_autoclose_ticket_time', 'hourly' );

			if( !wp_next_scheduled( 'ttls_autoclose_ticket' ) ) { // when there are no events - registering

				update_option( 'ttls_autoclose_ticket_time_now', $autoclose_ticket_time );
				wp_schedule_event( time(), $autoclose_ticket_time, 'ttls_autoclose_ticket');

			} elseif( $autoclose_ticket_time != get_option('ttls_autoclose_ticket_time_now', 'hourly' ) ) { // when events are present, but the time parameter of automatic closure was altered.

				wp_clear_scheduled_hook('ttls_autoclose_ticket'); // deleting events
				wp_schedule_event( time(), $autoclose_ticket_time, 'ttls_autoclose_ticket'); // creating with a new parameter
				update_option( 'ttls_autoclose_ticket_time_now', $autoclose_ticket_time ); // the the parameter of the created event

			}

		} else { // when automatic closure is disabled
			if( wp_next_scheduled( 'ttls_autoclose_ticket' ) ) { // check if it was created
				wp_clear_scheduled_hook('ttls_autoclose_ticket'); // kill
			}
		}
	}
