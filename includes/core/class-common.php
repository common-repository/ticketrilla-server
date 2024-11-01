<?php


	// Exit if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	if ( ! class_exists( 'TTLS_Common' ) ) {

		class TTLS_Common {

			public $taxonomies = array();


			function __construct() {
				add_action( 'init', array( $this, 'create_post_types' ), 1 );
			}


			/**
			 * Creating user-defined types of post "ttls_ticket", "ttls_license",
			 * "ttls_attachments"
			 */
			function create_post_types() {

				register_post_type('ttls_product', array(
					'labels' => array(
						'name'               => esc_html__( 'Products', 'ttls_translate' ),
						'singular_name'      => esc_html__( 'Product', 'ttls_translate' ),
						'add_new'            => esc_html__( 'Add product', 'ttls_translate' ),
						'add_new_item'       => esc_html__( 'Add new product', 'ttls_translate' ),
						'edit_item'          => esc_html__( 'Edit product', 'ttls_translate' ),
						'new_item'           => esc_html__( 'New product', 'ttls_translate' ),
						'view_item'          => esc_html__( 'View product', 'ttls_translate' ),
						'search_items'       => esc_html__( 'Search product', 'ttls_translate' ),
						'not_found'          => esc_html__( 'Not found', 'ttls_translate' ),
						'not_found_in_trash' => esc_html__( 'Not found in trash', 'ttls_translate' ),
						'parent_item_colon'  => esc_html__( 'Product parent', 'ttls_translate' ),
						'menu_name'          => esc_html__( 'Products', 'ttls_translate' ),
					),
					'description'         => '',
					'public'=> true,
					'exclude_from_search' => true,
					'publicly_queryable'  => true,
					'show_in_menu'        => false, // Show in admin menu
					'show_in_rest'        => false, // Show in REST API. From WordPress 4.7
					'hierarchical'        => true,
					'supports'            => array('title','editor','author', 'custom-fields'),
				) );

				register_post_type('ttls_ticket', array(
					'labels' => array(
						'name'               => esc_html__( 'Tickets', 'ttls_translate' ),
						'singular_name'      => esc_html__( 'Ticket', 'ttls_translate' ),
						'add_new'            => esc_html__( 'Add ticket', 'ttls_translate' ),
						'add_new_item'       => esc_html__( 'Add new ticket', 'ttls_translate' ),
						'edit_item'          => esc_html__( 'Edit ticket', 'ttls_translate' ),
						'new_item'           => esc_html__( 'New ticket', 'ttls_translate' ),
						'view_item'          => esc_html__( 'View ticket', 'ttls_translate' ),
						'search_items'       => esc_html__( 'Search ticket', 'ttls_translate' ),
						'not_found'          => esc_html__( 'Not found', 'ttls_translate' ),
						'not_found_in_trash' => esc_html__( 'Not found in trash', 'ttls_translate' ),
						'parent_item_colon'  => esc_html__( 'Ticket parent', 'ttls_translate' ),
						'menu_name'          => esc_html__( 'Tickets', 'ttls_translate' ),
					),
					'description'         => '',
					'public'=> true,
					'exclude_from_search' => true,
					'publicly_queryable'  => true,
					'show_in_menu'        => false, // Show in admin menu
					'show_in_rest'        => false, // Show in REST API. From WordPress 4.7
					'hierarchical'        => true,
					'supports'            => array('title','editor','author', 'custom-fields'),
				) );

				register_post_type('ttls_license', array(
					'labels' => array(
						'name'               => esc_html__( 'Licenses', 'ttls_translate' ),
						'singular_name'      => esc_html__( 'Licenses', 'ttls_translate' ),
						'add_new'            => esc_html__( 'Add License', 'ttls_translate' ),
						'add_new_item'       => esc_html__( 'Add New License', 'ttls_translate' ),
						'edit_item'          => esc_html__( 'Edit License', 'ttls_translate' ),
						'new_item'           => esc_html__( 'New License', 'ttls_translate' ),
						'view_item'          => esc_html__( 'View License', 'ttls_translate' ),
						'search_items'       => esc_html__( 'Search License', 'ttls_translate' ),
						'not_found'          => esc_html__( 'Not Found', 'ttls_translate' ),
						'not_found_in_trash' => esc_html__( 'Not Found In Trash', 'ttls_translate' ),
						'parent_item_colon'  => esc_html__( 'Licenses Parent', 'ttls_translate' ),
						'menu_name'          => esc_html__( 'Licenses', 'ttls_translate' ),
					),
					'description'         => '',

					'exclude_from_search' => true,
					'publicly_queryable'  => true,
					'show_in_menu'        => false, // Show menu in admin
					'show_in_rest'        => false, // Show in REST API. From WordPress 4.7
					'hierarchical'        => false,
					'supports'            => array('title','editor','author', 'custom-fields'),
				) );

				register_post_type('ttls_attachments', array(
					'labels' => array(
						'name'               => esc_html__( 'Attachments', 'ttls_translate'),
						'singular_name'      => esc_html__( 'Attachment', 'ttls_translate'),
						'add_new'            => esc_html__( 'Add attachment', 'ttls_translate'),
						'add_new_item'       => esc_html__( 'New attachment', 'ttls_translate'),
						'edit_item'          => esc_html__( 'Edit attachment', 'ttls_translate'),
						'new_item'           => esc_html__( 'New attachment', 'ttls_translate'),
						'view_item'          => esc_html__( 'Preview attachment', 'ttls_translate'),
						'search_items'       => esc_html__( 'Search attachments', 'ttls_translate'),
						'not_found'          => esc_html__( 'Not found', 'ttls_translate'),
						'not_found_in_trash' => esc_html__( 'Not found in trash', 'ttls_translate'),
						'parent_item_colon'  => esc_html__( 'Attachments parent', 'ttls_translate'),
						'menu_name'          => esc_html__( 'Attachments', 'ttls_translate'),
					),
					'description'         => '',

					'exclude_from_search' => true,
					'publicly_queryable'  => true,
					'show_in_menu'        => false, // Show menu in admin
					'show_in_rest'        => false, // Show in REST API. From WordPress 4.7
					'hierarchical'        => false,
					'supports'            => array('title','author', 'custom-fields'),
				) );
			}
		}
	}