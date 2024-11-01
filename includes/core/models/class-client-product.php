<?php

namespace TTLS\Models;
use TTLS\Models\Model;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ClientProduct' ) ) {

	class ClientProduct extends Model {

    public $newsletters = 'y';

		public function attributes() {
			return array(
        'license_id' => '',
        'license' => __( 'License', 'ttls_translate' ),
        'license_token' => __( 'Purchase code', 'ttls_translate' ),
        'newsletters' => __( 'Newsletters', 'ttls_translate' ),
        'terms' => __( 'Terms', 'ttls_translate' ),
        'product_id' => '',
      );
		}

		public function rules() {
			return array(
        array(
          array('product_id', 'license', 'terms'),
          'required',
        ),
    );
		}

  }
}