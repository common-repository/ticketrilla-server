<?php

namespace TTLS\Models;
use TTLS\Models\Model;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'StandardLicense' ) ) {

	class StandardLicense extends Model {

    public $enabled = 'y';
    public $multiple_users = '';
    public $new_verified = 'y';
    public $new_support = '';
    public $extend_support_link = '';
    public $expiry_date = 30;

    public function attributes() {
      return array(
        'enabled' => __('Enable standard licensing', 'ttls_translate'),
        'multiple_users' => __('Unlimited users', 'ttls_translate'),
        'new_verified' => __('Verified license', 'ttls_translate'),
        'new_support' => __('Support present', 'ttls_translate'),
        'extend_support_link' => __('Link for extending support period ', 'ttls_translate'),
        'expiry_date' => __('License expiry date', 'ttls_translate'),
      );
    }

    public function rules() {
			return array(
				array(
					array('extend_support_link'),
					'url',
				),
				array(
					array('expiry_date'),
					'natural',
				),
      );
    }
  }
}