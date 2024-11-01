<?php

	$args = array(
		'role'				 => 'ttls_developers',
		'paged'				 => $paged,
		'number'			=> 10
	);
	$users = get_users( $args );
	$table_developers_rows = array(
		10 => array(
			'data' => 'login',
			'name' => 'Login'
		),
		20 => array(
			'data' => 'meta_first_name',
			'name' => 'Name'
		),
		30 => array(
			'data' => 'meta_nickname',
			'name' => 'Position'
		),
		40 => array(
			'data' => 'email',
			'name' => 'Email'
		),
		50 => array(
			'data' => 'meta_ttls_all_tickets',
			'name' => 'All tickets'
		),
		60 => array(
			'data' => 'meta_ttls_open_tickets',
			'name' => 'Open'
		),
	);
?>
<div class="ttls__developers">
	<div class="ttls__developers-inner">
		<table class="table table-striped">
			<thead>
				<tr>
				<?php foreach ( $table_developers_rows as $key => $row_param) { ?>
					<th><?php echo esc_html__( $row_param['name'], 'ttls_translate') ?></th>
				<?php } ?>
				</tr>
			</thead>
			<tfoot>
				<tr>
				<?php foreach ( $table_developers_rows as $key => $row_param) { ?>
					<th><?php echo esc_html__( $row_param['name'], 'ttls_translate') ?></th>
				<?php } ?>
				</tr>
			</tfoot>
			<tbody>
			<?php
					$ttls_user = new TTLS_Users();
					foreach ( $users as $usr) {
						$this_user = $ttls_user->get_user($usr->data->ID);
						if ( !is_wp_error( $this_user ) ) {
							echo '<tr>';
							foreach ( $table_developers_rows as $key => $row_param) {
								echo '<td>';
								switch ( $row_param['data'] ) {
									case 'login':
										echo esc_html( $this_user['login'] );
										break;

									case 'meta_first_name':
										if ( !empty( $this_user['meta_first_name'] ) ) {
											echo esc_html( $this_user['meta_first_name'][0] );
										}
										break;

									case 'meta_nickname':
										if ( !empty( $this_user['meta_nickname'] ) ) {
											echo esc_html( $this_user['meta_nickname'][0] );
										}
										break;

									case 'email':
										if ( !empty( $this_user['email'] ) ) {
											echo esc_html( $this_user['email'] );
										}
										break;

									case 'meta_ttls_open_tickets':
										if ( !empty( $this_user['meta_ttls_open_tickets'] ) ) {
											echo esc_html( $this_user['meta_ttls_open_tickets'][0] );
										}
										break;

									case 'meta_ttls_all_tickets':
										if ( !empty( $this_user['meta_ttls_all_tickets'] ) ) {
											echo esc_html( $this_user['meta_ttls_all_tickets'][0] );
										}
										break;

									default:
										do_action('ttls_print_developer_'.$row_param['data']);
										break;
								}
								echo '</td>';
							}
							echo '<td><a data-developer="'.esc_attr( $usr->data->ID ).'" data-bs-toggle="modal" data-bs-target="#ttlsEditDeveloper" class="btn btn-xs btn-default ttls_edit_developer"><i class="fa fa-pen"></i> '.esc_html__('Change', 'ttls_translate').'</a><a data-developer="'.esc_attr( $usr->data->ID ).'" data-login="'.esc_attr( $this_user['login'] ).'" data-bs-toggle="modal" data-bs-target="#ttlsNewDeveloperDelete" class="btn btn-xs btn-danger ttls_developer_delete">'.esc_html__('Delete', 'ttls_translate').'</a></td>';

						echo '</tr>';
						}
					}
				?>
			</tbody>
		</table>
	</div>

	<?php
	$user_count = count_users();
	if ( !empty($user_count['avail_roles']['ttls_developers']) AND $user_count['avail_roles']['ttls_developers'] > 10 ) {
		$max_pages = ceil( $user_count['avail_roles']['ttls_developers'] / 10 );
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
			echo esc_url( add_query_arg( 't_paged', $start_page ) ) .'"';
			echo '>'.esc_html( $start_page ).'</a></li>';
			$start_page++;
		} ?>
		<?php if ( $paged < $max_pages ) { ?>
		<li><a href="<?php echo esc_url( add_query_arg( 't_paged', $paged+1 ) ); ?>" aria-label="Previous"><span aria-hidden="true">&rarr;</span></a></li>
		<?php }
		if ( $finish_page < $max_pages ) {
			echo '<li>...</li>';
			echo '<li><a href="';
			echo esc_url( add_query_arg( 't_paged', $max_pages ) ) .'"';
			echo '>'.esc_html__('Last', 'ttls_translate').'</a></li>';
		}

		?>
	</ul></nav>
	<?php } ?>
</div>