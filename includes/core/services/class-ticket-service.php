<?php

namespace TTLS\Services;
use TTLS\Services\BaseTicketService;
use TTLS\Services\ResponseService;
use TTLS\Models\Ticket;
use TTLS\Models\Response;
use TTLS\Helpers\TicketHTML;
use TTLS\Helpers\ResponseHTML;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TicketService' ) ) {

  class TicketService extends BaseTicketService {

    private $response_service;

    function __construct() {
      $this->response_service = new ResponseService( $this );
    }

    public function get_response_service() {
      return $this->response_service;
    }

    /**
     * Get list of tickets
     *
     * @param      integer         $paged            The paged
     * @param      array           $license_tickets  The license tickets
     * @param      string          $status           The status
     *
     * @return     WP_Error|array  Ticket List.
     */

    public function get_list( $paged = 1, $license_tickets = false, $status = false, $product_id = false ){

      $condition = $this->prepare_ticket_list_condition( $paged, $license_tickets, $status, $product_id );

      if ( is_wp_error( $condition ) ) {
        return $condition;
      }

      $tickets = Ticket::find( $condition );

      if ( ! empty( $tickets['items'] ) ) { // when tickets are found
        $prepared_tickets = array();
        foreach ( $tickets['items'] as $ticket) {
          $prepared_tickets[] = $this->prepare_ticket_list_item( $ticket );
        }
        return array(
          'tickets' => $prepared_tickets,
          'count_tickets' => $tickets['total'],
          'status' => true,
          'message' => esc_html__( 'Your tickets', 'ttls_translate' ),
        );

      }
      
      return new \WP_Error( 'ttls_no_tickets', 'These are no tickets matching your criteria', array( 'status' => 404 ) );      
    }

    private function prepare_ticket_list_condition( $paged, $license_tickets, $status, $product_id ) {
      $condition = array(
        'posts_per_page' => 10, // 10 per page
        'paged' => $paged, // pagination
        'post_parent' => "0", // just tickets, no responses
        'meta_query' => array(),
      );

      if ( $product_id ) {
        $condition['meta_query'][] = array(
          'key' => 'ttls_product_id',
          'value' => $product_id,
        );
      }

      if ( current_user_can( 'ttls_plugin_admin' ) ){ // plugin admin
        if ( $status ) { // if status specified - adding status for filtering options
          $condition['meta_query'][] = array(
            'key' => 'ttls_status',
            'value' => $status
          );
        }
      } elseif ( current_user_can( 'ttls_developers' ) ) { // support agent
        if ( $status && $status != 'free' ) { // when status is specified yet taken
          $condition['meta_query'][] = array(
            array( // belongs to this developer
              'key' => 'ttls_ticket_developer',
              'value' => get_current_user_id()
            ),
            'relation' => 'AND', // and
            array( // with specified status
              'key' => 'ttls_status',
              'value' => $status
            ),
          );
        } elseif( $status == 'free' ) { // when is just free
          $condition['meta_query'][] = array( // just free
            'key' => 'ttls_status',
            'value' => $status
          );
        } else { // without filtering options
          $condition['meta_query'][] = array(
            array( // belongs to this developer
              'key' => 'ttls_ticket_developer',
              'value' => get_current_user_id()
            ),
            'relation' => 'OR', // or
            array( // free
              'key' => 'ttls_status',
              'value' => 'free'
            ),
          );
        }
      } else { // all others - clients

        if ( $license_tickets ) {
          $condition['post__in'] = $license_tickets;
          $condition['author'] = get_current_user_id();

          if ( ! empty( $status ) ) {
            if ( $status == 'pending' ) {
              $condition['meta_query'][] = array(
                'key' => 'ttls_status',
                'value' => array('free', 'pending'),
                'compare' => 'IN',
              );
            } else {
              $condition['meta_query'][] = array(
                'key' => 'ttls_status',
                'value' => $status
              );
            }
          }
        } else {
          return new \WP_Error( 'ttls_no_tickets', 'There are no tickets with this license', array( 'status' => 404 ) );
        }

      }
      return $condition;
    }

    private function prepare_ticket_list_item( $ticket ) {
      $product_id = get_post_meta( $ticket->license, 'ttls_product_id', true );
      $data = array(
        'id' => $ticket->ID,
        'title' => $ticket->post_title,
        'status' => $ticket->status,
        'response_last_date' => $ticket->last_response_date,
        'product_title' => empty( $ticket->product_id ) ? '' : get_the_title( $ticket->product_id ),
      );

      $client = get_user_by( 'ID', $ticket->post_author );

      if ( $client ) {
        $license_type = get_post_meta( $ticket->license, 'ttls_license_type', true );
        if ( $license_type ) {
          $data['client_license_type'] = $license_type;
        } else {
          $data['client_license_type'] = esc_html__('License deleted', 'ttls_translate');
        }
        $data['client_login'] = $client->data->user_login;
      }

      $data['developer_position'] = '';
      $developer = get_user_by( 'ID' , $ticket->ticket_developer );

      if ( $developer ) {
        $data['developer_id'] = $developer->data->ID;
        $developer_first_name = get_user_meta( $developer->ID, 'first_name', true );
        if ( $developer_first_name ) {
          $data['developer'] = $developer_first_name;
        } else {
          $data['developer'] = $developer->user_login;
        }

        if ( current_user_can( 'ttls_developers' ) ) {
          $data['developer_position'] = get_user_meta( $developer->data->ID, 'nickname', true );
        }
 
      } else {
        $data['developer_id'] = '';
        $data['developer'] = esc_html( Ticket::get_status( 'free' ) );
      }
      
      if ( current_user_can( 'ttls_clients' ) ) {
        if ( $ticket->status == 'free' ) {
          $data['status'] = 'pending';
        }
      }

      $responses_condition = array(
        'post_parent' => $ticket->ID,
      );

      $responses_condition['meta_query'] = array(
        array(
          'key' => 'ttls_response_status',
          'value' => Response::get_statuses(),
          'compare' => 'IN',
        )
      );

      $responses = Response::find( $responses_condition );
      $data['response_count'] = $responses['total'];

      $data['attachment_count'] = count( get_post_meta( $ticket->ID, 'ttls_all_attachment' ) );
      
      return $data;
    }

    /**
     * Get single ticket
     *
     * @param      int      $ticket_id    The ticket id
     * @param      int      $paged        page number
     * @param      string   $order        sort param
     *
     * @return     WP_Error|array  Ticket.
     */

    public function get_single( $ticket_id, $paged = 1, $order = 'ASC' ) {

      $ticket = Ticket::find_by_id( $ticket_id );

      if ( $ticket ) {

        $main_data = $this->prepare_ticket_main_data( $ticket );
        $client_data = $this->prepare_ticket_client_data( $ticket->post_author );
        $license_data = $this->prepare_ticket_license_data( $ticket->license );
        $developer_data = $this->prepare_ticket_developer_data( empty( $ticket->ticket_developer ) ? '' : $ticket->ticket_developer );
        $response_data = $this->response_service->prepare_ticket_response_data( $ticket_id, $paged, $order );

        $ticket_data = array_merge( $main_data, $client_data, $license_data, $developer_data, $response_data );

        return $ticket_data;
      }

      return new \WP_Error( 'ttls_data_noticket', 'A ticket with this ID was not found', array( 'status' => 404 ) );

    }

    private function prepare_ticket_main_data( $ticket ) {
      $data = array();
      $data['id'] = $ticket->ID;
      $data['title'] = $ticket->post_title;
      $data['content'] = apply_filters( 'ttls_pre_the_content', $ticket->post_content );
      $data['status'] = $ticket->status;
      $data['client_site_url'] = $ticket->site_urls;
      $data['client_license_id'] = $ticket->license;

      if ( current_user_can( 'ttls_clients' ) ) {
        if ( $data['status'] == 'free' ) {
          $data['status'] = 'pending';
        }
      }

      $data['ticket_attachments'] = $this->get_attachments( get_post_meta( $ticket->ID, 'ttls_attachment' ) );
      $data['attachment_list'] = $this->get_attachments( get_post_meta( $ticket->ID, 'ttls_all_attachment' ) );
      $data['attachment_templist'] = (new \TTLS_Attachments())->get_tempattachments_of_ticket( $ticket->ID );
      return $data;
    }

    public function prepare_ticket_client_data( $id ) {
      $data = array();
      $client = get_user_by( 'ID' , $id );
      $data['client'] = $client->data->user_login;
      $data['client_name'] = get_user_meta( $client->data->ID, 'first_name', true );
      $data['client_id'] = $client->ID;
      $data['client_email'] = $client->data->user_email;       
      return $data;
    }

    public function prepare_ticket_license_data( $id ) {
      $data = array();
      $license= (new \TTLS_License)->get_by_id( $id );

      if ( is_wp_error( $license ) ) {
        $data['client_license'] = $license;
      } else {
        $data['client_license'] = true;

        if ( $license['verified'] ) {
          $data['client_license_verified'] = true;
        }

        if ( $license['have_support'] ) {
          $data['client_license_have_support'] = true;
          $data['client_license_have_support_until'] = $license['have_support_until'];
        } else {
          $data['client_license_have_support'] = false;
        }

        $data['client_license_support_link'] = empty( $license['support_link'] ) ? '' : $license['support_link'];
        $data['client_license_type'] = $license['type'];
        $data['client_license_token'] = $license['token'];
      }

      return $data;
    }

    private function prepare_ticket_developer_data( $id = '' ) {
      $data = array();
      $data['developer_id'] = $id;
      if ( $id ) {
        $developer = get_user_by( 'ID' , $id );
        $data['developer'] = get_user_meta( $developer->ID, 'first_name', true );
        if ( ! $data['developer'] ) {
          $data['developer'] = $developer->user_login;
        }
        if ( current_user_can( 'ttls_developers' ) ) {
          $data['developer_pos'] = get_user_meta( $developer->ID, 'nickname', true );
        }
      } else {
        $data['developer'] = esc_html__('Free ticket', 'ttls_translate');
        if ( current_user_can( 'ttls_clients' ) ) {
          $data['developer'] = 'Agent is not set';
        }
        if ( current_user_can( 'ttls_developers' ) ) {
          $data['developer_pos'] = '';
        }
      }
      return $data;
    }

    /**
     * add new ticket, response or system message
     *
     * @param      array   $ticket_data  The ticket data
     * @param      object  $caps    The capabilities of current user
     *
     * @return     WP_Error|array  The list.
     */

    public function add( $ticket_data, $caps = false ) {

      if ( ! empty( $ticket_data ) ) {

        if ( isset( $ticket_data['parent'] ) ) { // if a response

          if ( ! is_numeric( $ticket_data['parent'] ) ) {
            return new \WP_Error( 'ttls_add_ticket_badparent', 'Send number var for parent ticket', array( 'status' => 400 ) );
          }
    
          $parent_ticket = Ticket::find_by_id( $ticket_data['parent'] );
          
          if ( ! $parent_ticket ) {
            return new \WP_Error( 'ttls_add_ticket_badparent', 'Parent ticket not found', array( 'status' => 400 ) );
          }
          
          if ( current_user_can( 'ttls_developers' ) ) {

            // Agent reopens the ticket through a response

            if ( $parent_ticket->status == 'closed' ) {
              $ticket_data['response_status'] = 'reopen';
            } elseif( $ticket_data['parent_status'] == 'closed' ) { // Agent closes the ticket through a response
              $ticket_data['response_status'] = 'closed';
              if ( ! empty( $ticket_data['content'] ) ) {
                $ticket_data['response_reason'] = $ticket_data['content'];
                $ticket_data['content'] = '';
              }
            }
          
          } else {
            
            if (  $parent_ticket->status !== 'free' ) {
              $ticket_data['parent_status'] = 'pending';
            }

            // Client reopens the ticket through a response
            
            if ( $parent_ticket->status == 'closed' ) {

              if ( $parent_ticket->ticket_developer ) {
                $ticket_data['parent_status'] = 'pending';
              } else {
                $ticket_data['parent_status'] = 'free';
              }
              $ticket_data['response_status'] = 'reopen';
    
            }

          }

          $ticket_data = apply_filters('ttls_before_add_response', $ticket_data, $parent_ticket );

          if ( ! empty( $ticket_data['parent_status'] ) ) {
            $this->change_status( $parent_ticket, $ticket_data['parent_status'] );
          }

          return $this->response_service->add_response( $ticket_data, $caps, $parent_ticket );

        }
        
        // if a ticket
        $ticket_data['license_id'] = $caps->this_license['id'];
        return $this->add_ticket( $ticket_data );

      }

      return new \WP_Error( 'ttls_add_ticket_nocontent', 'Unable to create ticket - content missing', array( 'status' => 400 ) );
    }

    public function add_ticket( $raw_data ) {

      if ( empty( $raw_data['license_id'] ) ) {
        return new \WP_Error( 'ttls_license_missing', 'No license info', array( 'status' => 400 ) );
      }

      $license_id = $raw_data['license_id'];
      
      $data = array(
        'license' => $license_id,
        'status' => 'free',
        'servers' => empty( $raw_data['ttls_servers'] ) ? array() : $raw_data['ttls_servers'],
        'site_urls' => empty( $raw_data['ttls_site_urls'] ) ? array() : $raw_data['ttls_site_urls'],
        'last_response_date' => current_time( 'Y-m-d H:i:s', 1 ),
        'response_status' => apply_filters( 'ttls_add_ticket_response_status', 'ticket', $raw_data ),
        'product_id' => get_post_meta( $license_id, 'ttls_product_id', true ),
      );

      if ( ! empty( $raw_data['title'] ) ) {
        $data['post_title'] = $raw_data['title'];
        $no_title = false;
      } else {
        $no_title = true;
      }

      if ( ! empty( $raw_data['content'] ) ) {
        $data['post_content'] = $raw_data['content'];
        $no_content = false;
      } else {
        if ( $no_title ) {
          $no_content = true;
        } else { // if have title - set content same with ticket
          $no_content = false;
          $data['post_content'] = $raw_data['title'];
        }
      }

      if ( $no_content ) {
        return new \WP_Error( 'ttls_add_ticket_nocontent', 'For creating a ticket, title and/or message is required', array( 'status' => 400 ) );
      }

      $new_ticket = new Ticket( $data );
      $new_ticket_id = $new_ticket->save();

      if ( is_wp_error( $new_ticket_id ) ) {
        return new \WP_Error( 'ttls_add_ticket_insertpost', $new_ticket_id->get_error_message(), array( 'status' => 500 ) );
      }

      // when the ticket doesn't have a title
      // for example when incorrect WordPress data was received
        
      if ( empty( $new_ticket->post_title ) ) {
        $new_ticket->post_title = sprintf( 'Ticket#%d', $new_ticket_id );
        $new_ticket->save();
      }

      if ( ! empty( $raw_data['attachments'] ) ) {
        $license_type = get_post_meta( $license_id, 'ttls_license_type', true );
        $license_token = get_post_meta( $license_id, 'ttls_license_token', true );
        $caps = new \TTLS_Capabilities( $license_token, $license_type );
        $this->add_attachments( $raw_data['attachments'], $new_ticket_id, $caps );
      }

      update_post_meta( $license_id, 'ttls_license_last_ticket', (int) current_time( 'timestamp', 1 ) );

      // add ticket ID to license for future caps check

      add_post_meta( $license_id, 'ttls_tickets', $new_ticket_id );
      
      do_action( 'ttls_after_add_ticket', get_post( $new_ticket->ID ) );

      return array(
        'message'   => esc_html ( __( 'The ticket was added to the database', 'ttls_translate' ) ),
        'ticket_id' => $new_ticket_id,
      );

    }

    // wp_action - ajax ttls_add_ticket

    public function ajax_add_ticket() {
      check_ajax_referer( 'ttls_add_ticket', '_wpnonce' );
      define( 'ALLOW_UNFILTERED_UPLOADS', true );
      $title = empty( $_POST['ttls_title'] ) ? '' : sanitize_text_field( $_POST['ttls_title'] );
      $content = empty( $_POST['ttls_message'] ) ? '' : wp_kses_post( $_POST['ttls_message'] );
      $license_id = empty( $_POST['ttls_license_id'] ) ? '' : sanitize_key( $_POST['ttls_license_id'] );
      $can_add_ticket = \TTLS_License::check_license( $license_id, get_current_user_id() );
      if ( is_wp_error( $can_add_ticket ) ) {
        wp_send_json_error( array( 'message' => esc_html( $can_add_ticket->get_error_message()) ) );
      }

      $add_ticket = $this->add_ticket( array(
        'title' => $title,
        'content' => $content,
        'license_id' => $license_id,
      ) );
      if ( is_wp_error( $add_ticket ) ) {
        $html = '';
        if ( $add_ticket->get_error_code() == 'ttls_add_ticket_nocontent' ) {
          $ticket = new Ticket();
          $ticket->add_error( 'post_title' );
          $ticket->license = $license_id ;
          $html = TTLS()->admin_page()->buffer_template( 'ticketrilla-server-add-ticket-form', array('ticket' => $ticket, 'licenses' => \TTLS_License::get_client_licenses_titles()) );
        }
        wp_send_json_error( array( 'message' => esc_html( $add_ticket->get_error_message()), 'html' => $html ) );
      }
      if ( empty( $add_ticket['ticket_id'] ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'Unknown server error', 'ttls_translate' ) ) );
      }

      if ( ! empty( $_FILES['ttls_attachment'] ) ) {
        $license_type = get_post_meta( $license_id, 'ttls_license_type', true );
        $license_token = get_post_meta( $license_id, 'ttls_license_token', true );
        $caps = new \TTLS_Capabilities( $license_token, $license_type );
        $can_load_attachments = $caps->can_load_attachments();
        if ( ! is_wp_error( $can_load_attachments ) ) {
          $ttls_attachments = new \TTLS_Attachments();
          $attachments = array();
          foreach ( $_FILES['ttls_attachment']['error'] as $key => $error ) {
            if ( $error == UPLOAD_ERR_OK ) {
              $name = sanitize_file_name( basename( $_FILES['ttls_attachment']['name'][$key] ) );
              $loaded_attachment_id = $ttls_attachments->load_file( $_FILES['ttls_attachment']['tmp_name'][$key], $name, $add_ticket['ticket_id'] );
              if ( ! is_wp_error( $loaded_attachment_id ) ) {
                $attachments[] = array('id' => $loaded_attachment_id);
              }
            }
          }
          $ttls_attachments->link( $add_ticket['ticket_id'], $attachments );
        }
      }

      wp_send_json_success( array( 'ticket_url' => ttls_url('ticketrilla-server-tickets', array('ticket_id' => $add_ticket['ticket_id']) )) );
    }

    // wp_action - ajax ttls_take_ticket

    public function ajax_take_ticket() {
  
      if ( current_user_can( 'ttls_developers' ) ) {

        if ( empty( $_POST['ticket'] ) ) {
          wp_send_json_error( array( 'message' => esc_html__('Submit a ticket ID for editing', 'ttls_translate') ) );
        }

        $ticket_id = sanitize_key( $_POST['ticket'] );
        $parent_ticket = Ticket::find_by_id( $ticket_id );

        if ( ! $parent_ticket ) {
          wp_send_json_error( array( 'message' => esc_html__('Ticket not found', 'ttls_translate') ) );
        }

        if ( $parent_ticket->status !== 'free' ) {
          wp_send_json_error( array( 'message' => esc_html__('This ticket is already being handled by another agent', 'ttls_translate') ) );
        }

        $change_agent = $this->change_agent( $parent_ticket, get_current_user_id() );
        if ( is_wp_error( $change_agent ) ) {
          wp_send_json_error( array('message' => esc_html( $change_agent->get_error_message() ) ) );
        }
      
        wp_send_json_success( array(
          'message' => esc_html__('Received ticket - in the works', 'ttls_translate'),
          'button' => esc_html__('To ticket', 'ttls_translate'),
          'link' => ttls_url( 'ticketrilla-server-tickets', array('ticket_id' => $ticket_id) ),
        ) );

      }
      wp_send_json_error( array( 'message' => esc_html__('You do not have sufficient rights for working with tickets', 'ttls_translate') ) );
    }

    // wp_action - ajax ttls_add_response

    public function ajax_add_response() {

      parse_str( $_POST['fields'], $fields );

      if ( empty( $fields['ttls_parent'] ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'Submit a ticket ID for adding a response', 'ttls_translate') ) );
      }

      $ticket_id = sanitize_key( $fields['ttls_parent'] );

      $license_token = false;
      $license_type = false;

      if ( current_user_can( 'ttls_clients' ) ) {
        $license_id = get_post_meta( $ticket_id, 'ttls_license', true );
        $license_type = get_post_meta( $license_id, 'ttls_license_type', true );
        $license_token = get_post_meta( $license_id, 'ttls_license_token', true );
      }
      
      $can_add = (new \TTLS_Capabilities( $license_token, $license_type ))->can_add_response( $ticket_id );

      if ( is_wp_error( $can_add ) ) {
        wp_send_json_error( array( 'message' => esc_html__( $can_add->get_error_message(), 'ttls_translate') ) );
      }

      $data = array(
        'parent' => $ticket_id,
        'content' => empty( $fields['ttls_message'] ) ? '' : wp_kses_post( $fields['ttls_message'] ),
        'attachments' => empty( $fields['ttls_attachment'] ) ? array() : array_map( function( $attachment_id ) {
          return array('id' => sanitize_key( $attachment_id ));
        }, $fields['ttls_attachment'] ),
      );

      if ( current_user_can( 'ttls_developers' ) ) {
        $data['parent_status'] = empty( $fields['ttls_status'] ) ? '' : sanitize_text_field( $fields['ttls_status'] );
        $data['parent_developer'] = get_current_user_id();
      }
      
      $add_response = $this->add( $data );

      if ( is_wp_error( $add_response ) ) {
        wp_send_json_error( array( 'message' => esc_html( $add_response->get_error_message() ) ) );
      }

      $response = Response::find_by_id( $add_response['ticket_id'] );

      if ( ! $response ) {
        wp_send_json_error( array( 'message' => esc_html__( 'Response not found', 'ttls_translate') ) );
      }
              
      $box = ResponseHTML::response_box( $this->response_service->prepare_response_data( $response ) );
        
      wp_send_json_success( array( 'message' => esc_html__('Response added', 'ttls_translate'), 'box' => $box ) );
        
    }

    public function ajax_edit_ticket() {
      parse_str( $_POST['fields'], $fields );
      
      if ( current_user_can( 'ttls_developers' ) ) {

        if ( empty( $fields['ttls_ticket_id'] ) ) {
          wp_send_json_error( array( 'message' => esc_html__('Submit a ticket ID for editing', 'ttls_translate') ) );
        }

        $ticket = Ticket::find_by_id( sanitize_key( $fields['ttls_ticket_id'] ) );
        
        if ( ! $ticket ) {
          wp_send_json_error( array( 'message' => esc_html__('Ticket not found', 'ttls_translate') ) );
        }

        $new_status = empty( $fields['ttls_status'] ) ? '' : sanitize_text_field( $fields['ttls_status'] );
        $new_agent = empty( $fields['ttls_developer'] ) ? '' : sanitize_key( $fields['ttls_developer'] );

        if ( $new_status == 'free' && $new_agent ) {
          wp_send_json_error( array( 'message' => esc_html__('Unable to change status to free on agent change', 'ttls_translate') ) );
        }

        if ( $new_status ) {

          $prev_status = $ticket->status;

          $content = '';

          if ( $new_status == 'closed' ) {
            if ( empty( $fields['ttls_close_reason'] ) ) {
              if ( empty( $fields['ttls_close_reason_text'] ) ) {
                wp_send_json_error( array( 'message' => esc_html__('Specify a reason for closing this ticket', 'ttls_translate') ) );
              }
              $content = sanitize_text_field( $fields['ttls_close_reason_text'] );
            } else {
              $close_reason = Response::get_close_reason( sanitize_text_field( $fields['ttls_close_reason'] ) );
              $content = empty( $close_reason ) ? '' : $close_reason;
            }
          }

          $set_agent = empty( $new_agent ); // Set current agent if no agent specified

          $change_status = $this->change_status( $ticket, $new_status, $set_agent );
          if ( is_wp_error( $change_status ) ) {
            wp_send_json_error( array('message' => esc_html( $change_status->get_error_message() ) ) );
          }
          $result = array('message' => esc_html( $change_status['message'] ));

          $response = $this->log_response( $ticket, $new_status, $prev_status, $content );
          
          if ( $response ) {
            $result['box'] = ResponseHTML::response_box( $this->response_service->prepare_response_data( $response ) );
          }
          if ( ! $new_agent ) {
            wp_send_json_success( $result );
          }
        }

        if ( $new_agent ) {
          $change_agent = $this->change_agent( $ticket, $new_agent );
          if ( is_wp_error( $change_agent ) ) {
            wp_send_json_error( array('message' => esc_html( $change_agent->get_error_message() ) ) );
          }
          if ( $new_status ) {
            wp_send_json_success( array('message' => esc_html__('Status and agent has been changed', 'ttls_translate') ) );
          } else {
            wp_send_json_success( array('message' => esc_html( $change_agent['message'] ) ) );
          }
        }

        wp_send_json_error( array( 'message' => esc_html__('Status or agent is not specified', 'ttls_translate') ) );

      }

      wp_send_json_error( array( 'message' => esc_html__('You do not have sufficient rights for updating this ticket', 'ttls_translate') ) );

    }

    private function change_status( $ticket, $new_status, $set_agent = true ) {

      if ( $ticket->status == $new_status ) {
        return new \WP_Error( 'ttls_change_status_wrong_status', __('This status is already set', 'ttls_translate') );
      }

      $recalc_agent_tickets = array();

      // If new status is 'free' - unset prev agent, recalc agent's tickets

      if ( $new_status == 'free' ) {
        $recalc_agent_tickets[] = $ticket->ticket_developer;
        $ticket->ticket_developer = '';
      } else {

        // If ticket is free - set current user as agent

        if ( $set_agent && current_user_can( 'ttls_developers' ) && ! $ticket->ticket_developer ) {
          $new_agent = get_current_user_id();
          $ticket->ticket_developer = $new_agent;
          $recalc_agent_tickets[] = $new_agent;
        }
      }

      $ticket->status = $new_status;
      
      $ticket_saved = $ticket->save();
      
      if ( is_wp_error( $ticket_saved ) ) {
        return new \WP_Error( 'ttls_add_ticket_insertpost', $ticket_saved->get_error_message(), array( 'status' => 500 ) );
      }
      
      do_action( 'ttls_developers_tickets', $recalc_agent_tickets );

      return array('message' => __('Status changed', 'ttls_translate') );
    }

    private function log_response( $ticket, $new_status, $prev_status, $content = '' ) {

      $response_data = array();

      if ( $new_status == 'closed' ) {
        $response_data['response_status'] = 'closed';
        if ( ! empty( $content ) ) {
          $response_data['response_reason'] = $content;
        }
      } elseif( $prev_status == 'closed' ) {
        $response_data['response_status'] = 'reopen';
      }

      if ( empty( $response_data ) ) {
        return false;
      }

      $result = $this->response_service->add_response( $response_data, false, $ticket );
      if ( is_wp_error( $result ) ) {
        return $result;
      }
      return Response::find_by_id( $result['ticket_id'] );
      
    }

    private function change_agent( $ticket, $new_agent ) {
      
      if ( $ticket->ticket_developer == $new_agent ) {
        return new \WP_Error( 'ttls_change_agent_wrong_agent', __('This agent is already working on this ticket', 'ttls_translate') );
      }

      $prev_agent = $ticket->ticket_developer;
      $ticket->ticket_developer = $new_agent;

      if ( $ticket->status == 'free' ) {
        $ticket->status = 'pending';
      }
      
      $ticket_saved = $ticket->save();
      
      if ( is_wp_error( $ticket_saved ) ) {
        return new \WP_Error( 'ttls_add_ticket_insertpost', $ticket_saved->get_error_message(), array( 'status' => 500 ) );
      }
      
      do_action( 'ttls_developers_tickets', array($prev_agent, $new_agent) );

      return array('message' => __( 'Agent changed', 'ttls_translate' ) );
    }

    public function ajax_check_status() {
      $ticket_id = sanitize_key( $_POST['ticket'] );
      $prev_status = isset( $_POST['prev_status'] ) ? sanitize_text_field( $_POST['prev_status'] ) : 'free';
      $prev_dev = isset( $_POST['prev_dev'] ) ? sanitize_key( $_POST['prev_dev'] ) : '';
      $now_status = get_post_meta( $ticket_id, 'ttls_status', true);
      if ( current_user_can( 'ttls_clients' ) ) {
        if ( $now_status == 'free' ) {
          $now_status = 'pending';
        }
      }
      $now_dev = get_post_meta( $ticket_id, 'ttls_ticket_developer', true);
      $message = esc_html__('No changes');
      $status = false;

      if ( $prev_status != $now_status ) {
        $status = true;
        $message = esc_html__('Status changed', 'ttls_translate');
      }

      if ( $prev_dev != $now_dev ) {
        if ( $status ) {
          $message = esc_html__('Status and agent were changed', 'ttls_translate');
        } else {
          $message = esc_html__('Agent changed', 'ttls_translate');
        }
        $status = true;
      }

      if ( $status ) {
        
        $data = array(
          'id' => $ticket_id,
          'status' => $now_status,
          'developer_id' => $now_dev,
        );
        
        $data = array_merge( $data, $this->prepare_ticket_developer_data( $now_dev ) );
        $box = TicketHTML::status_box( $data );

        wp_send_json_success( array( 'message' => $message, 'box' => $box ) );
      }
    
      wp_send_json_error( array( 'message' => $message ) );

    }

    public function ajax_client_edit_ticket() {

      check_ajax_referer( 'ttls_client_edit_ticket', '_wpnonce' );

      if ( ! current_user_can( 'ttls_clients' ) ) {
        wp_send_json_error( array( 'message' => __('You do not have sufficient rights for updating this ticket', 'ttls_translate') ) );
      }
      if ( empty( $_POST['parent'] ) ) {
        wp_send_json_error( array( 'message' => __('Send number var for parent ticket', 'ttls_translate') ) );
      }
      if ( empty( $_POST['status'] ) || ! in_array( $_POST['status'], array('closed', 'reopen') ) ) {
        wp_send_json_error( array( 'message' => __('Wrong ticket status', 'ttls_translate') ) );
      }
      
      $data = array(
        'parent' => sanitize_key( $_POST['parent'] ),
        'status' => sanitize_text_field( $_POST['status'] ),
      );
      
      $edit_ticket = $this->client_edit_ticket( $data );
      if ( is_wp_error( $edit_ticket ) ) {
        wp_send_json_error( array('message' => esc_html( $edit_ticket->get_error_message() )) );
      }

      if ( $data['status'] == 'closed' ) {
        $edit_ticket['new_status'] = 'reopen';
        $edit_ticket['new_status_text'] = esc_html__( 'Open', 'ttls_translate' );
      }

      if ( $data['status'] == 'reopen' ) {
        $edit_ticket['new_status'] = 'closed';
        $edit_ticket['new_status_text'] = esc_html__( 'Close', 'ttls_translate' );
      }

      wp_send_json_success( $edit_ticket );
    }
    
    public function client_edit_ticket( $raw_data ) {

      if ( ! current_user_can( 'ttls_clients' ) ) {
        return new \WP_Error( 'ttls_ticket_edit_notcapable', __('You do not have sufficient rights for updating this ticket', 'ttls_translate'), array( 'status' => 400 ) );
      }

      if ( empty( $raw_data['parent'] ) || ! is_numeric( $raw_data['parent'] ) ) {
        return new \WP_Error( 'ttls_ticket_edit_badparent', 'Send number var for parent ticket', array( 'status' => 400 ) );
      }

      $ticket = Ticket::find_by_id( $raw_data['parent'] );
      if ( ! $ticket ) {
        return new \WP_Error( 'ttls_ticket_edit_badparent', 'Parent ticket not found', array( 'status' => 400 ) );
      }

      if ( $ticket->post_author != get_current_user_id() ) {
        return new \WP_Error( 'ttls_capabilities_notyour', 'This is not your ticket', array( 'status' => 403 ) );
      }

      $ticket_dev = $ticket->ticket_developer; // Prev Developer
      $ticket_status = $ticket->status; // Prev Status

      $content = '';

      if ( $raw_data['status'] == 'closed' ) {
        if ( $ticket_status == 'closed' ) {
          return new \WP_Error( 'ttls_ticket_edit_closed_already', 'This ticket is already closed', array( 'status' => 400 ) );
        }
        $new_status = 'closed';
        $content = 'client_solved';

      } elseif ( $raw_data['status'] == 'reopen' ) {
        if ( $ticket_status !== 'closed' ) {
          return new \WP_Error( 'ttls_ticket_edit_open_already', 'This ticket is open', array( 'status' => 400 ) );
        }
        if ( $ticket_dev ) {
          $new_status = 'replied';
        } else {
          $new_status = 'free';
        }
      }
      
      $change_status = $this->change_status( $ticket, $new_status );
      if ( is_wp_error( $change_status ) ) {
        return $change_status;
      }
      
      $result = array('message' => esc_html( $change_status['message'] ), 'ticket_id' => $ticket->ID);

      $response = $this->log_response( $ticket, $new_status, $ticket_status, $content );
      
      if ( $response ) {
        $result['box'] = ResponseHTML::response_box( $this->response_service->prepare_response_data( $response ) );
      }
  
      return $result;;

    }

    public function recalc_agents_tickets( $agent_ids = array() ) {
      if ( ! empty( $agent_ids ) ) {
        $get_posts = new \WP_Query;
        $args = array(
          'numberposts' => -1,
          'post_type'   => 'ttls_ticket',
          'post_parent' => 0,
          'fields' => 'ids'
        );
        foreach ( $agent_ids as $agent_id ) {
          if ( $agent_id ) {
            $args['meta_query'] = array(
              array(
                'key' => 'ttls_ticket_developer',
                'value' => $agent_id
              )
            );
            $get_posts->query( $args );
            $all_tickets = $get_posts->found_posts;
            
            $args['meta_query'] = array(
              'relation' => 'AND',
              array(
                'key' => 'ttls_ticket_developer',
                'value' => $agent_id
              ),
              array(
                'key' => 'ttls_status',
                'compare' => '!=',
                'value' => 'closed',
              )
            );
            $get_posts->query( $args );
            $open_tickets = $get_posts->found_posts;

            update_user_meta( $agent_id, 'ttls_open_tickets', $open_tickets);
            update_user_meta( $agent_id, 'ttls_all_tickets', $all_tickets);
          }
        }
      }
    }

    /**
     * Close tickets if autoclose enabled
     */
    public function autoclose(){
      
      // current time minus the wait time which is set in settings
      $waiting_time = (int) current_time( 'timestamp', 1 ) - ( (int) get_option( 'ttls_autoclose_ticket_time_waiting', 7 ) * DAY_IN_SECONDS );

      $autoclose_condition = array(
        'post_parent' => 0, // just the parent tickets (not responses)
        'meta_query' => array(
          'relation' => 'AND',
          array(
            'key' => 'ttls_status', // status
            'value' => 'replied', // client wait
          ),
          array(
            'key' => 'ttls_last_client_response_date', // client wait time
            'compare' => '<=', // smaller than
            'value' => $waiting_time // maximum wait time
          )
        )
      );
      
      $tickets = Ticket::find_all( $autoclose_condition );
      
      if ( ! empty( $tickets['items'] ) ) { // when there are corresponding tickets

        foreach ( $tickets['items'] as $ticket ) {

          $prev_status = $ticket->status;
          $new_status = 'closed';
          $change_status = $this->change_status( $ticket, $new_status );
          if ( ! is_wp_error( $change_status ) ) {
            $this->log_response( $ticket, $new_status, $prev_status, Response::get_close_reason( 'autoclose' ) );
          }

        }
      }
    }

  }
}