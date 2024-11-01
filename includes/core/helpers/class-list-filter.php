<?php

namespace TTLS\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ListFilter' ) ) {

  class ListFilter extends Filter {

    public function render() {
      $html = '<div class="ttls__filter ttls__list-filter">';
      if ( ! empty( $this->get_label() ) ) {
        $html .= '<span>' . esc_html( $this->get_label() ) . ':</span>';
      }
      $html .= '<ul>';
      foreach( $this->get_data() as $filter_key => $filter_item ) {
        $item_html = '<li';
        if ( $this->get_active_key() == $filter_key ) {
          $item_html .= ' class="active"';
        }
        $item_html .= '><a href="' . esc_url( $this->get_url( $filter_key ) ) . '">' . wp_kses( $filter_item['label'], array(
          'i' => array(
            'class' => array(),
          ),
        ) ) . '</a></li>';
        $html .= $item_html;
      }
      
      $html .= '</ul>';
      $html .= '</div>';
      echo wp_kses_post( $html );
    }
  
  }

}