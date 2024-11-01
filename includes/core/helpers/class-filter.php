<?php

namespace TTLS\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Filter' ) ) {

  class Filter extends HTML {

    protected $base_url;
    protected $param_name = 'filter';

    function __construct( $base_url ) {
      $this->base_url = empty( $base_url ) ? $_SERVER['REQUEST_URI'] : $base_url;
    }

    protected function get_active_key() {
      return isset( $_GET[$this->param_name] ) && array_key_exists( $_GET[$this->param_name], $this->get_data() ) ? sanitize_text_field( $_GET[$this->param_name] ) : 'all';
    }

    public function get_data() {
			$data = array(
        'all' => array(
          'label' => __( 'All', 'ttls_translate' ),
        ),
			);
      return $data;
    }

    protected function get_url( $filter_key ) {
      return $filter_key === 'all' ? remove_query_arg( $this->param_name, $this->base_url ) : add_query_arg( $this->param_name, $filter_key, $this->base_url );
    }

    protected function get_label() {
      return __( 'Show', 'ttls_translate' );
    }

    public function render() {}
  
  }

}