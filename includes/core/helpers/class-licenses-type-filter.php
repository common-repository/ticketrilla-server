<?php

namespace TTLS\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'LicensesTypeFilter' ) ) {

  class LicensesTypeFilter extends ListFilter {

    protected $param_name = 'l_type';
    protected $license_types = array();

    function __construct( $base_url, $license_types ) {
      parent::__construct( $base_url );
      $this->license_types = $license_types;
    }

    public function get_data() {
			$data = parent::get_data();
			foreach ( $this->license_types as $license_key ) {
				$data[$license_key] = array('label' => $license_key );
			}
      return $data;

    }

    protected function get_label() {
      return __( 'Filter by license type', 'ttls_translate' );
    }

  }

}