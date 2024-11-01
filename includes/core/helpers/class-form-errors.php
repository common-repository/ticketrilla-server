<?php

namespace TTLS\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FormErrors' ) ) {

  class FormErrors extends HTML {
    public function render( $attribute, $model ) {
      if ( $model->has_errors( $attribute ) ) {
        foreach ( $model->get_errors( $attribute ) as $error_message ) {
          echo '<div class="help-block">' . esc_html( $error_message ) . '</div>';	
        }
      }
    }
  }
}