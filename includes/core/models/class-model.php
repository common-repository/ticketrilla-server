<?php

namespace TTLS\Models;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Model' ) ) {

	class Model {

		/**
		 * The name of the default scenario.
		 */
		const SCENARIO_DEFAULT = 'default';
		
		/**
		 * @var array validation errors (attribute name => array of errors)
		 */
		private $_errors;

		/**
		 * @var string current scenario
		 */
		private $_scenario = self::SCENARIO_DEFAULT;
		
		public function set_scenario( $scenario ) {
			$this->_scenario = $scenario;
		}

		public function get_scenario( ) {
			return $this->_scenario;
		}

		public function scenarios() {
				return array();
		}		    				

		public function attributes() {
			return array();
		}

		public function get_label( $attribute ) {
			$attributes = $this->attributes();
			if ( array_key_exists( $attribute, $attributes ) ) {
				return $attributes[$attribute];
			}
			return '';
		}

		public function rules() {
			return array();
		}
		
		function __construct( $data = false ) {
			if ( $data ) {
				if ( is_array( $data ) ) {
					$this->import_array( $data );
				}
			}
		}

		public function import_array( $data ) {
			if ( is_array( $data ) ) {
				foreach ( $this->attribute_names() as $attribute ) {
					if ( isset( $data[$attribute] ) ) {
						$this->$attribute = $data[$attribute];
					}
				}
			}
		}

		public function export_array( $attributes ) {
			$data = array();
			foreach ( $attributes as $attribute ) {
				if ( isset( $this->$attribute ) ) {
					$data[$attribute] = $this->$attribute;
				}
			}
			return $data;
		}

		public function export_attr_array() {
			return $this->export_array( $this->attribute_names() );
		}
		
		public function attribute_names() {
			return array_keys( $this->attributes() );
		}
		
		public function validate( $clear_errors = true ) {
			if ( $clear_errors ) {
				$this->clear_errors();
			}
			
			foreach ( $this->rules() as $rule ) {
				if ( isset( $rule['on'] ) && $rule['on'] !== $this->_scenario ) {
					continue;
				}
				$error_message = isset( $rule['error_message'] ) ? $rule['error_message'] : false;
				$this->validate_attributes( $rule[0], $rule[1], $error_message, isset( $rule[2] ) ? $rule[2] : false );
			}
			
			return ! $this->has_errors();
		}
		
		public function clear_errors( $attribute = null ) {
			if ( $attribute === null ) {
	            $this->_errors = array();
	        } else {
	            unset( $this->_errors[$attribute] );
	        }
		}
		
		protected function validate_attributes( $attribute_names, $validation_type, $error_message = false, $additional_data = false ) {
			foreach ( $attribute_names as $attribute ) {
				$error = false;
				$attribute_val = empty( $this->$attribute ) ? '' : trim( $this->$attribute );
				switch ( $validation_type ) {
					case 'required':
						if ( empty( $attribute_val ) ) {
							$error = true;
							$default_error_message = __( 'This field is required', 'ttls_translate' );
						}
						break;
					case 'email':
						if ( ! is_email( $attribute_val ) ) {
							$error = true;
							$default_error_message = __( 'This E-mail is invalid', 'ttls_translate' );
						}
						break;
					case 'number':
						if ( ! is_numeric( $attribute_val ) ) {
							$error = true;
							$default_error_message = __( 'This field must be numeric', 'ttls_translate' );
						}
						break;
					case 'natural':
						if ( $attribute_val < 1 ) {
							$error = true;
							$default_error_message = __( 'The value must be greater than 0', 'ttls_translate' );
						}
						break;
					case 'in_list':
						if ( is_array( $additional_data ) && ! in_array( $attribute_val, $additional_data ) ) {
							$error = true;
							$default_error_message = __( 'The value is not in a list', 'ttls_translate' );
						}
						break;
					case 'url':
						if ( ! empty( $attribute_val ) && ! wp_http_validate_url( $attribute_val ) ) {
							$error = true;
							$default_error_message = __( 'This URL is invalid', 'ttls_translate' );
						}
						break;
				}
				if ( $error ) {
					$this->add_error( $attribute, $error_message ? $error_message : $default_error_message );
				}
			}
		}

		public function add_error( $attribute, $error = '' ) {
			$this->_errors[$attribute][] = $error;
		}			

		public function has_errors( $attribute = null ) {
			return $attribute === null ? ! empty( $this->_errors ) : isset( $this->_errors[$attribute] );
		}

		public function get_errors( $attribute = null ) {
			if ( $attribute === null ) {
					return $this->_errors === null ? array() : $this->_errors;
			}
			return isset( $this->_errors[$attribute] ) ? $this->_errors[$attribute] : array();
		}
	    
	}
}
