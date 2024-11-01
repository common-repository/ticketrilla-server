<?php

namespace TTLS\Services;

use TTLS\Services\BaseTicketService;
use TTLS\Services\TicketService;
use TTLS\Models\Ticket;
use TTLS\Models\Response;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ResponseService' ) ) {

  class ResponseService extends BaseTicketService {

    private $ticket_service;

    function __construct( $ticket_service ) {
      $this->ticket_service = $ticket_service;
    }

    
    public static function get_localized_title( $args ) {
      
      if ( empty( $args['type'] ) ) {
        return false;
      }
      
      $prepend = empty( $args['prepend'] ) ? '' : $args['prepend'];
      $append = empty( $args['append'] ) ? '' : $args['append'];
      
      $response_type = $args['type'];
      $localized_statuses = Ticket::get_statuses();
      $templates = Response::get_title_templates();
      $title_parts = array($prepend, '', $append);
      
      if ( array_key_exists( $response_type, $templates ) ) {
        $template = $templates[$response_type];
      } else {
        return implode( ' ', $title_parts );
      }
      
      return vsprintf( $template, $title_parts );

    }

    public function add_response( $raw_data, $caps = false, $parent_ticket ) {

      $license_id = $caps ? $caps->this_license['id'] : '';

      $data = array(
        'post_parent' => $parent_ticket->ID,
        'post_content' => '',
        'post_title' => '',
        'response_status' => 'response',
        'response_reason' => empty( $raw_data['response_reason'] ) ? '' : $raw_data['response_reason'],
        'license' => $license_id,
      );

      if ( ! empty( $raw_data['response_status'] ) ) {
          $data['response_status'] = $raw_data['response_status'];
      }

      if ( empty( $raw_data['content'] ) ) {
        $no_content = true;
      } else {
        $data['post_content'] = $raw_data['content'];
        $no_content = false;
      }

      if ( empty( $raw_data['attachments'] ) ) {
        $no_attachments = true;
      } else {
        $no_attachments = false;
      }

      if ( $data['response_status'] == 'response' && $no_attachments && $no_content ) {
        return new \WP_Error( 'ttls_add_ticket_nocontent', 'For submitting a response, attachment and/or message is required', array( 'status' => 400 ) );
      }

      $data['response_status'] = apply_filters( 'ttls_add_ticket_response_status', $data['response_status'], $raw_data );

      $new_response = new Response( $data );
      $new_response_id = $new_response->save();

      if ( is_wp_error( $new_response_id ) ) {
        return new \WP_Error( 'ttls_add_ticket_insertpost', $new_ticket_id->get_error_message(), array( 'status' => 500 ) );
      }

      if ( ! empty( $raw_data['attachments'] ) ) {
        $this->add_attachments( $raw_data['attachments'], $new_response_id, $caps );
      }

      if ( $license_id ) {
        update_post_meta( $license_id, 'ttls_license_last_ticket', (int) current_time( 'timestamp', 1 ) );
      }

      do_action( 'ttls_after_add_ticket', get_post( $new_response->ID ) );

      return array(
        'message'   => esc_html__( 'The ticket was added to the database', 'ttls_translate' ),
        'ticket_id' => $new_response_id,
        'ticket_parent' => $new_response->post_parent,
      );
      
    }
    
    public function prepare_ticket_response_data( $ticket_id, $paged, $order ) {
      $data = array();
      $condition = array (
        'post_parent' => $ticket_id,
        'paged' => $paged,
        'order' => $order,
        'orderby' => 'date',
      );
      $condition['meta_query'] = array(
        array(
          'key' => 'ttls_response_status',
          'value' => Response::get_statuses(),
          'compare' => 'IN',
        )
      );

      $responses = Response::find( $condition );
      $data['response_list'] = array();

      $response_list = array();

      foreach ( $responses['items'] as $response ) {
        $response_list[] = $this->prepare_response_data( $response );
      }

      $data['response_list'] = $response_list;
      $data['response_count'] = $responses['total'];

      return $data;
    }

    public function prepare_response_data( $response ) {

      $main_data = $this->prepare_response_main_data( $response );
      $author_data = empty( $response->post_author ) ? array() : $this->prepare_response_user_data( $response->post_author, 'author' );

      if ( current_user_can( 'ttls_clients' ) ) { // For Clients
  
      } else {
 
        if ( empty( $author_data ) ) {
          $author_data = array(
            'author_id' => '0',
            'author' => __('System', 'ttls_translate'),
            'author_pos' => __('System', 'ttls_translate'),
          );
        }
        
      }

      if ( empty( $author_data['author_pos'] ) ) {
        $author = get_user_by( 'ID' , $response->post_author );
        if ( $author ) {
          $author_data['author_pos'] = $author->roles[0];
        }
      }

      $data = array_merge( $main_data, $author_data );
      return $data;
    }

    private function prepare_response_main_data( $response ) {
      $data = array(
        'id' => $response->ID,
        'parent_id' => $response->post_parent,
        'title' => $response->post_title,
        'content' => apply_filters('ttls_pre_the_content', $response->post_content ),
        'time' => $response->post_date_gmt,
        'type' => $response->response_status, // response | closed | reopen
        'reason' => $response->response_reason,
        'attachment_list' => $this->get_attachments( get_post_meta( $response->ID, 'ttls_attachment' ) ),
      );
      return $data;
    }

    public function prepare_response_user_data( $user_id, $type ) {
      $data = array();
      $user = get_user_by( 'ID' , $user_id );
      if ( $user ) {
        $data[$type . '_id'] = $user_id;
        $first_name = get_user_meta( $user_id, 'first_name', true );
        if ( $first_name ) {
          $data[$type] = $first_name;
        } else {
          $data[$type] = $user->data->user_login;
        }
        $data[$type . '_pos'] = get_user_meta( $user_id, 'nickname', true );
      }
      return $data;
    }

  }
}