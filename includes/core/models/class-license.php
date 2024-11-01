<?php

namespace TTLS\Models;
use TTLS\Models\Post;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'License' ) ) {

	class License extends Post {
		
		const POST_TYPE = 'ttls_license';

    public function main_attributes() {
			return array(
        'ID' => __( 'id', 'ttls_translate' ),
      );
		}

    public function meta_attributes() {
			return array(
        'license_type' => '',
        'license_token' => '',
        'newsletters' => '',
        'product_id' => '',
      );
    }
  }
}
