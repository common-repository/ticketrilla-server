<?php

namespace TTLS\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ProductFilter' ) ) {

  class ProductFilter extends ListFilter {

    public function get_data() {
			$data = parent::get_data();
			foreach ( \TTLS\Models\Product::product_types() as $type_key => $type_label ) {
				$data[$type_key] = array('label' => $type_label );
			}
      return $data;

    }
  }

}