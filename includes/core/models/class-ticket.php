<?php

namespace TTLS\Models;
use TTLS\Models\Post;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Ticket' ) ) {

	class Ticket extends Post {
		
		const POST_TYPE = 'ttls_ticket';

    public function main_attributes() {
			return array(
        'ID' => __( 'id', 'ttls_translate' ),
        'post_title' => __( 'Title', 'ttls_translate' ),
        'post_content' => __( 'Content', 'ttls_translate' ),
        'post_author' => __( 'Author', 'ttls_translate' ),
        'post_date_gmt' => __( 'Date GMT', 'ttls_translate' ),
      );
		}

    public function meta_attributes() {
			return array(
        'status' => '',
        'site_urls' => '',
        'servers' => '',
        'license' => '',
        'ticket_developer' => '',
        'response_status' => '',   
        'last_response_date' => '', 
        'last_client_response_date' => '',
        'product_id' => '',
      );
		}

    public function rules() {
      return array(
        array(
          array('license', 'status'),
          'required',
        ),
        array(
          array('status'),
          'in_list',
          array_keys( self::get_statuses() ),
        ),
      );
    }

    public static function get_statuses() {
      return array(
        'free' => __( 'Available ticket', 'ttls_translate' ),
        'replied' => __( 'Agent replied', 'ttls_translate' ),
        'pending' => __( 'Waiting for agent\'s response', 'ttls_translate' ),
        'closed' => __( 'Ticket closed', 'ttls_translate' ),
      );
    }

    public static function get_client_statuses() {
      return array(
        'replied' => __('Needs Attention', 'ttls_translate'),
        'pending' => __('Pending', 'ttls_translate'),
        'closed' => __('Closed', 'ttls_translate'),
      );
    }

    public static function get_status( $key ) {
      $statuses = self::get_statuses();
      if ( array_key_exists( $key, $statuses ) ) {
        return $statuses[$key];
      }
      return false;
    }

    public static function get_client_status( $key ) {
      $statuses = self::get_client_statuses();
      if ( array_key_exists( $key, $statuses ) ) {
        return $statuses[$key];
      }
      return false;
    }

    public function save( $run_validation = true ) {

      $this->last_response_date = current_time( 'Y-m-d H:i:s', 1 );

      // If ticket status going to be replied - update last client response date

      if (  $this->status == 'replied' ) {
        $this->last_client_response_date = (int) current_time( 'timestamp', 1 );
      }

      return parent::save( $run_validation );
    }

  }
}