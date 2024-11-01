<?php

namespace TTLS\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ProductDropdownFilter' ) ) {

  class ProductDropdownFilter extends DropdownFilter {

    protected $param_name = 'product_id';
    protected $products = array();
    protected $select_class = 'form-control ttls_license_filter_product';
    protected $license_type = '';

    function __construct( $base_url, $license_type, $products ) {
      parent::__construct( $base_url );
      $this->license_type = $license_type;
      $this->products = $products['items'];
    }

    public function get_data() {
			$data = parent::get_data();
			foreach ( $this->products as $product ) {
        if ( ! $this->license_type || array_key_exists( $this->license_type, $product->active_licenses() ) ) {
          $data[$product->ID] = array('label' => $product->post_title );
        }
			}
      return $data;

    }

    protected function get_label() {
      return __( 'Filter by product', 'ttls_translate' );
    }

  }

}