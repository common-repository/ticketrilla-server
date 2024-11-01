<?php
	// Exit if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	if ( ! class_exists( 'TTLS_Roles' ) ) {

		class TTLS_Roles {

			private $roles = array(
				'clients' => array(
					'name' => 'Client',
					'capabilities' => array(
						'ttls_see_plugin',
						'ttls_see_tickets',
						'ttls_add_ticket'
					)
				),
				'developers' => array(
					'name' => 'Agent',
					'capabilities' => array(
						'ttls_see_plugin',
						'ttls_see_tickets',
						'ttls_take_ticket',
						'ttls_work_ticket'
					)
				)
			);
			
			private $capabilities = array(
				'ttls_plugin_admin' => 'Plugin administrator',
				'ttls_see_plugin'   => 'Can see main plugin page',
				'ttls_see_tickets'  => 'Can see ticket list',
				'ttls_see_users'    => 'Can see users',
				'ttls_see_licenses' => 'Can see licenses of users',
				'ttls_see_addons'   => 'Can see addons page of Last Support',
				// clients caps
				'ttls_add_ticket'  => 'Can add tickets.',
				// developers caps
				'ttls_take_ticket'  => 'Can see and take free ticket in work.',
				'ttls_work_ticket'  => 'Can add responce to ticket in work if he take it.',
				'ttls_developers'   => 'Agent caps'
			);

			/**
			 * Register new roles
			 */
			public function create_roles(){
				$wp_roles = wp_roles();
				$roles = array_merge( $this->roles, apply_filters( 'ttls_add_custom_roles', array() ) );
				foreach ( $roles as $role => $role_data ) {
					$wp_roles->add_role( 'ttls_' . $role, $role_data['name'],
						array(
							'read'         => true,
							'edit_posts'   => false,
							'delete_posts' => false,
							'upload_files' => false
						)
					);
					foreach ( $role_data['capabilities'] as $role_data_cap ) {
						$wp_roles->add_cap( 'ttls_' . $role, $role_data_cap, true );
					}
				}
			}

			/**
			 * Add the capabilities for admin
			 */
			public function add_all_capabilities_to_admin(){
				$wp_roles = wp_roles();
				foreach ( $this->capabilities as $single_cap => $cap_name ) {
					$wp_roles->add_cap( 'administrator', $single_cap, true );
				}
			}

			/**
			 * Add the capabilities for role
			 *
			 * @param      int   $user_id     The user identifier
			 * @param      string   $capability  The capability name
			 * @param      boolean  $grant       The grant
			 *
			 * @return     array   result
			 */
			function edit_cap_for_user( $user_id, $capability, $grant = true ){
				$cap_user = new WP_User( $user_id );
				$cap_user->add_cap( $capability, $grant );
				return array( 'status' => true, 'message' => esc_html__( 'We add this cap to user.', 'ttls_translate' ) );
			}

			/**
			 * Gets the capabilities for role
			 *
			 * @param      string  $role   The role name
			 *
			 * @return     array  The capabilities for role
			 */
			function get_caps_for( $role = false ){
				if ( $role ) {
					if ( isset($this->roles[$role]) ) {
						foreach ( $this->roles[$role]['capabilities'] as $cap_id) {
							$roles[$cap_id] = $this->capabilities[$cap_id];
						}
					} else {
						return array( 'status' => false, 'message' => esc_html__( 'Send correct role id.', 'ttls_translate' ) );
					}
					return array( 'status' => true, 'message' => esc_html__( 'Ok.', 'ttls_translate' ), 'roles' => $roles );
				} else {
					return array( 'status' => false, 'message' => esc_html__( 'Send role id.', 'ttls_translate' ) );
				}
			}
		}
	}