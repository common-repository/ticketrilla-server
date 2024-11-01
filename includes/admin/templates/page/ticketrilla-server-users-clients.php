<?php
	$args = array(
		'role'				 => 'ttls_clients',
		'paged'				 => $paged,
		'number'			=> 10
	);
	$users = get_users( $args );
	if ( empty( $users ) ) {
		echo '<p>'.esc_html__('There is no user', 'ttls_translate').'</p>';
	} else {
		$ttls_license = new TTLS_License;
		$ttls_user = new TTLS_Users(); ?>
	<div class="ttls__users">
        <div class="ttls__users-inner">
			<table class="table">
				<thead>
					<tr>
						<th rowspan="2"><?php echo esc_html__('Client', 'ttls_translate'); ?><span><?php echo esc_html__('Client', 'ttls_translate'); ?></span></th>
						<th rowspan="2"><?php echo esc_html__('Product', 'ttls_translate'); ?></th>
						<th colspan="2"><?php echo esc_html__('License', 'ttls_translate'); ?></th>
						<th colspan="2"><?php echo esc_html__('Data', 'ttls_translate'); ?></th>
						<th rowspan="2"><?php echo esc_html__('Control', 'ttls_translate'); ?><span><?php echo esc_html__('Control', 'ttls_translate'); ?></span></th>
					</tr>
					<tr>
						<th><?php echo esc_html__('Type', 'ttls_translate'); ?><span><?php echo esc_html__('Type', 'ttls_translate'); ?></span></th>
						<th><?php echo esc_html__('Purchase code', 'ttls_translate'); ?><span><?php echo esc_html__('Purchase code', 'ttls_translate'); ?></span></th>
						<th><?php echo esc_html__('Support', 'ttls_translate'); ?><span><?php echo esc_html__('Support', 'ttls_translate'); ?></span></th>
						<th><?php echo esc_html__('Activity', 'ttls_translate'); ?><span><?php echo esc_html__('Activity', 'ttls_translate'); ?></span></th>
					</tr>
				</thead>
				<tbody>
			<?php foreach ( $users as $usr) {
					$add_user = true;
					$this_user = $ttls_user->get_user($usr->data->ID);
					if ( is_wp_error( $this_user ) ) {
						echo '<tr class="ttls_license_row"><td class="ttls_user_label">';
						echo esc_html__( $this_user->get_error_message(), 'ttls_translate');
						echo '</tr></td>';
					} else {

						$row_user_data = array();
						$row_user_data['rowspan'] = $this_user['license_list']['count']+1;
						$row_user_data['id'] = $usr->data->ID;
						$row_user_data['name'] = $this_user['name'];
						$row_user_data['login'] = $this_user['login'];
						$row_user_data['email'] = $this_user['email'];
						if ( $this_user['license_list']['count'] > 0 ) {
							unset( $this_user['license_list']['count'] );
							foreach ( $this_user['license_list'] as $key => $license_list ) {
								$add_license = true;
								$row_license_data = array();
								$row_license_data['rowspan'] = count( $license_list );
								$row_license_data['name'] = $key;
								$row_license_data['user'] = $usr->data->ID;
								foreach ( $license_list as $l_key => $license_data) {
									if ( !$add_user ) {
										$row_user_data['display'] = 'none';
									}
									if ( !$add_license ) {
										$row_license_data['display'] = 'none';
									}
									$license_data['addon_classes'] = array();
									ttls_render( $ttls_license->html_user_license( array( $row_user_data ), $row_license_data, $license_data ) );
									$add_user = false;
									$add_license = false;
								} // end foreach $license_list
							} // end foreach $this_user['license_list']
						} else {
							echo '<tr class="ttls_license_row"><td data-user="'.esc_attr( $row_user_data['id'] ).'" class="ttls_user_label" rowspan="2">';
							if ( !empty( $row_user_data['name'] ) ) {
								echo '<p><i class="fa fa-at"></i> '.esc_html( $row_user_data['name'] ).'</p>';
							}
							if ( !empty( $row_user_data['login'] ) ) {
								echo '<p><i class="fa fa-user"></i> '.esc_html( $row_user_data['login'] ).'</p>';
							}
							if ( !empty( $row_user_data['email'] ) ) {
								echo '<p><i class="fa fa-envelope"></i> '.esc_html( $row_user_data['email'] ).'</p>';
							}
							echo '</td></tr>';
						}
						echo '<tr class="ttls_last_user_license">';
							echo '<td style="display: none;" data-user="'.esc_attr( $row_user_data['id'] ).'" class="ttls_user_label">';
							if ( !empty( $row_user_data['name'] ) ) {
								echo '<p><i class="fa fa-at"></i> '.esc_html( $row_user_data['name'] ).'</p>';
							}
							if ( !empty( $row_user_data['login'] ) ) {
								echo '<p><i class="fa fa-user"></i> '.esc_html( $row_user_data['login'] ).'</p>';
							}
							if ( !empty( $row_user_data['email'] ) ) {
								echo '<p><i class="fa fa-envelope"></i> '.esc_html( $row_user_data['email'] ).'</p>';
							}
							echo '</td>';
							echo '<td colspan=7><a data-userlogin="'.esc_attr( $this_user['login'] ).'" data-userid="'.esc_attr( $usr->data->ID ).'" data-bs-toggle="modal" data-bs-target="#ttlsNewLicense" class="btn btn-dark ttls_add_license">'.esc_html__('Add License', 'ttls_translate').'</a></td>';
						echo '</tr>';
					}
				} // end foreach $users
			?>
				</tbody>
			</table>
		</div>

		<?php
		$user_count = count_users();
		if ( !empty($user_count['avail_roles']['ttls_developers']) AND $user_count['avail_roles']['ttls_clients'] > 10 ) {
			$max_pages = ceil( $user_count['avail_roles']['ttls_clients'] / 10 );
			$this_page = 1;

			if ( $max_pages > 12 ) {
				$start_page = $paged - 3;
				if ( $start_page <= 0 ) {
					$start_page = 1;
				}
				if( $start_page >= $max_pages - 3 ){
					$start_page = $max_pages - 6;
				}
				$finish_page = $start_page + 6;
			} else {
				$start_page = 1;
				$finish_page = $max_pages;
			}
		?>
		<nav aria-label="Page navigation" class="text-center"><ul class="pagination">
			<?php
			if ( $start_page > 1 ) {
				echo '<li><a href="';
				echo esc_url( add_query_arg( 't_paged', 1 ) ).'"';
				echo '>'.esc_html__('First', 'ttls_translate').'</a></li>';
				echo '<li>...</li>';
			}
			if ( $paged > 1 ) { ?>
			<li><a href="<?php echo esc_url( add_query_arg( 't_paged', $paged-1 ) ); ?>" aria-label="Previous"><span aria-hidden="true">&larr;</span></a></li>
			<?php }
			while ( $start_page <= $finish_page ) {
				echo '<li';
				echo esc_html( ($paged == $start_page) ? ' class="active"':'' );
				echo '><a href="';
				echo esc_url( add_query_arg( 't_paged', $start_page ) ).'"';
				echo '>'.esc_html( $start_page ).'</a></li>';
				$start_page++;
			} ?>
			<?php if ( $paged < $max_pages ) { ?>
			<li><a href="<?php echo esc_url( add_query_arg( 't_paged', $paged+1 ) ); ?>" aria-label="Previous"><span aria-hidden="true">&rarr;</span></a></li>
			<?php }
			if ( $finish_page < $max_pages ) {
				echo '<li>...</li>';
				echo '<li><a href="';
				echo esc_url( add_query_arg( 't_paged', $max_pages ) ).'"';
				echo '>'.esc_html__('Last', 'ttls_translate').'</a></li>';
			}

			?>
		</ul></nav>
		<?php } ?>
	</div>

	<?php } // end if empty $users ?>