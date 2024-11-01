<?php

namespace TTLS\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Breadcrumbs' ) ) {

  class Breadcrumbs extends HTML {

    private $items = array();

    function __construct( $item = '' ) {
      $this->add( $this->get_first_item() );
      $this->add( $item );
    }

    public function add( $item ) {
      if ( ! empty( $item ) ) {
        $this->items[] = is_array( $item ) ? $item : array(
          'url' => '',
          'title' => $item,
          'prepend' => '',
      );
      }
    }

    private function get_first_item() {
      return array(
        'url' => current_user_can( 'ttls_clients' ) ? ttls_url( 'ticketrilla-server-products' ) : ttls_url(),
        'title' => __('Ticketrilla: Server', 'ttls_translate'),
        'prepend' => '<i class="fa fa-dashboard"></i> ',
      );
    }

    public function render() {
      $items_count = count( $this->items );
      $html = '';
      if ( $items_count ) {
        $html .= '<ul class="breadcrumb">';
        for ( $i = 0; $i < $items_count; $i++ ) {
          $item = $this->items[$i];
          if ( $i === $items_count - 1 ) {
            $html .= '<li class="active">' . esc_html( $item['title'] ) . '</li>';
          } else {
            $html .= '<li><a href="' . esc_attr( $item['url'] ) . '">' . ( empty( $item['prepend'] ) ? '' : $item['prepend'] ) . esc_html( $item['title'] ) . '</a></li>';
          }
        }
        $html .= '</ul>';
      }
      echo wp_kses_post( $html );
    }
  }
}