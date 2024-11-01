<?php

namespace TTLS\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'DropdownFilter' ) ) {

  class DropdownFilter extends Filter {

    protected $select_class = 'form-control';

    public function render() {
      $html = '<div class="ttls__filter ttls__dropdown-filter">';
      if ( ! empty( $this->get_label() ) ) {
        $html .= '<span>' . esc_html( $this->get_label() ) . ':</span>';
      }
      $html .= '<select value="' . esc_attr( $this->get_active_key() ) . '" class="' . esc_attr( $this->select_class ) . '">';
      foreach( $this->get_data() as $filter_key => $filter_item ) {
        $item_html = '<option value="' . esc_url( $this->get_url( $filter_key ) ) . '"';
        if ( $this->get_active_key() == $filter_key ) {
          $item_html .= ' selected';
        }
        $item_html .= '>' . esc_html( $filter_item['label'] ) . '</option>';
        $html .= $item_html;
      }
      
      $html .= '</select>';
      $html .= '</div>';
      echo wp_kses( $html, array_merge(wp_kses_allowed_html( 'post' ), ['select' => ['value' => true, 'class' => true], 'option' => ['value' => true, 'selected' => true]]));
    }
  
  }


}