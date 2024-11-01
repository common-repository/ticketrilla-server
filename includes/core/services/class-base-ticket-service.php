<?php

namespace TTLS\Services;

use TTLS\Services\Service;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'BaseTicketService' ) ) {
	class BaseTicketService extends Service {

		protected function get_attachments( $ids ) {
      $attachments = array();
      foreach ( $ids as $id) {
        $attachments[] = (new \TTLS_Attachments())->get( $id );
      }
      return $attachments;
    }

    protected function add_attachments( $attachments, $ticket_id, $caps ) {
      if ( empty( $attachments ) ) {
        return __( 'No attachments', 'ttls_translate' );
      }
      return ( new \TTLS_Attachments( $caps ) )->add( $ticket_id, $attachments );  
    }

  }
}