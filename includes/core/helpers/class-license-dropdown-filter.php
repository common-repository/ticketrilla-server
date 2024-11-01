<?php

namespace TTLS\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'LicenseDropdownFilter' ) ) {

  class LicenseDropdownFilter extends DropdownFilter {

    protected $param_name = 'license_id';
    protected $licenses = array();
    protected $select_class = 'form-control ttls_license_filter_product';

    function __construct( $base_url ) {
      parent::__construct( $base_url );
      $this->licenses = \TTLS_License::get_client_licenses_titles();
    }

    public function get_data() {
			$data = parent::get_data();
			foreach ( $this->licenses as $license_id => $license_label ) {
        $data[$license_id] = array('label' =>  $license_label);
			}
      return $data;

    }

    protected function get_label() {
      return __( 'Filter by license', 'ttls_translate' );
    }

  }

}