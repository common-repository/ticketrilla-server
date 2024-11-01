<?php

namespace TTLS\Models;
use TTLS\Models\Post;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Response' ) ) {

	class Response extends Post {
		
		const POST_TYPE = 'ttls_ticket';

    public function main_attributes() {
			return array(
        'ID' => __( 'id', 'ttls_translate' ),
        'post_parent' => __( 'Parent Post', 'ttls_translate' ),
        'post_title' => __( 'Title', 'ttls_translate' ),
        'post_content' => __( 'Content', 'ttls_translate' ),
        'post_author' => __( 'Author', 'ttls_translate' ),
        'post_date_gmt' => __( 'Date GMT', 'ttls_translate' ),
      );
		}

    public function meta_attributes() {
			return array(
        'response_status' => '',
        'response_reason' => '',
        'license' => '',
      );
    }

    public static function get_statuses() {

      $statuses = array(
        'response',
        'closed',
        'reopen',
      );

      return apply_filters( 'ttls_add_response_statuses', $statuses );
    }

    public function rules() {
      return array(
        array(
          array('response_status'),
          'in_list',
          array_keys( self::get_statuses() ),
        ),
      );
    }

    public static function get_title_templates() {
      return apply_filters( 'ttls_response_title_templates', array(
        'closed' => __( '%1$s has closed the ticket', 'ttls_translate' ),
        'reopen' => __( '%1$s has reopened the ticket', 'ttls_translate' ),
        'response' => __( '%1$s has replied', 'ttls_translate' ),
        'ticket' => __( '%1$s has added the ticket', 'ttls_translate' ),
      ) );
    }

    public static function get_close_reasons() {
      return array(
        'client_solved' => __( 'The issue has been resolved', 'ttls_translate' ),
        'client_cancel' => __( 'Client closed the issue', 'ttls_translate' ),
        'client_refund' => __( 'Client was refunded', 'ttls_translate' ),
        'autoclose' => __( 'Autoclose', 'ttls_translate' ),
      );
    }

    public static function get_close_reason( $key ) {
      $reasons = self::get_close_reasons();
      if ( array_key_exists( $key, $reasons ) ) {
        return $reasons[$key];
      }
      return false;
    }

  }
}