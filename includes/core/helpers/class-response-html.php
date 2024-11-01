<?php

namespace TTLS\Helpers;

use TTLS\Models\Response;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ResponseHTML' ) ) {

  class ResponseHTML extends HTML {
    
    public static function response_box( $response ) { // Prepared response
      if ( empty( $response ) || ! is_array( $response ) ) {
        return new \WP_Error( 'ttls_data_empty', 'There is no data for output', array( 'status' => 401 ) );
      }
      
      $html = '<li>';

        if ( $response['type'] == 'closed' ) {
          $html .= '<i class="fa fa-times"></i>';
        } elseif ( $response['type'] == 'reopen' ) {
          $html .= '<i class="fa fa-level-up"></i>';
        } elseif ( $response['type'] == 'response' ) {
          if ( user_can( $response['author_id'], 'ttls_clients' ) ) { // if client
            $html .= '<i class="fa fa-question"></i>';
          } else {
            $html .= '<i class="fa fa-share"></i>';
          }
        } else {
          $html .= '<i class="fa fa-cogs"></i>'; // other system messages
        }
      
      $html .= '<div class="ttls__tickets-responses-header">';
      $html .= '<span>' . esc_html( get_date_from_gmt( $response['time'], 'H:i' ) ) . '</span>';
      
      $html .= '<h4>';
            
      $response_title_prepend = esc_html( $response['author'] );
      
      if ( user_can( $response['author_id'], 'ttls_clients' ) ) { // if client
        $response_title_prepend .= ' <sup>'.esc_html__('Client', 'ttls_translate').'</sup>';
      } else {
        $response_title_prepend .= ' <sup>'.esc_html( $response['author_pos'] ).'</sup>';
      }

      $response['title'] = TTLS()->response_service()::get_localized_title( array(
        'type' => $response['type'],
        'prepend' => $response_title_prepend,
      ) );
      
      if ( ! empty( $response['title'] ) ) {
        $html .= wp_kses_post( $response['title'] );
      }

      $html .= '</h4>';
      $html .= '</div>';

      $response['content'] = apply_filters( 'ttls_response_box_content', $response['content'], $response );

      if ( ! empty( $response['attachment_list'] ) || ! empty( $response['content'] ) ) {
        $html .= '<div class="ttls__tickets-responses-body">';
        if ( $response['content'] ) {
          $html .= apply_filters( 'the_content', $response['content'] );
        }
        if ( ! empty( $response['attachment_list'] ) ) {
          $html .= '<ul class="ttls__attachments clearfix">';
          foreach ( $response['attachment_list'] as $key => $att_id) {
            $html .= (new \TTLS_Attachments)->print_single( $att_id, true );
          }
          $html .= '</ul>';
        }
        $html .= '</div>';
      } elseif( $response['reason'] ){
        $html .= '<div class="ttls__tickets-responses-body">';
        $html .= '<p>';
        switch ( $response['reason'] ) {
          case 'client_solved':
            $html .= esc_html__( 'The issue has been resolved', 'ttls_translate' );
            break;
          case 'client_cancel':
            $html .= esc_html__( 'Client closed the issue', 'ttls_translate' );
            break;
          case 'client_refund':
            $html .= esc_html__( 'Client was refunded', 'ttls_translate' );
            break;

          default:
            $html .= wp_kses_post( $response['reason'] );
            break;
        }
        $html .= '</p>';
        $html .= '</div>';
      }

      $html .= '</li>';
      return $html;

    }
  }
}