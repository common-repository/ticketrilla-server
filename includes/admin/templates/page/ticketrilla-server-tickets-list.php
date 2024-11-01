<div class="ttls__header-title">
	<div class="ttls__filters">
<?php
	$product_id = empty( $_GET['product_id'] ) ? false : sanitize_key( $_GET['product_id'] );
	$license_id = empty( $_GET['license_id'] ) ? false : sanitize_key( $_GET['license_id'] );

	if ( current_user_can( 'ttls_developers' ) ) {
		$products = \TTLS\Models\Product::find_all();
		$product_filter = new \TTLS\Helpers\ProductDropdownFilter( '', '', $products );
	} else {
		$product_filter = new \TTLS\Helpers\LicenseDropdownFilter( '' );
	}
	$product_filter->render();
?>
	<div class="ttls__filter"><span><?php echo esc_html__('Filter by status:', 'ttls_translate'); ?></span>
		<ul>
		<?php
			foreach ( $ticket_types as $ticket_types_key => $ticket_types_title ) {
				if ( 'all' == $ticket_types_key ) {
					$status_filter_url = remove_query_arg( array('status', 't_paged') );
				} else {
					$status_filter_url = add_query_arg( array('status' => $ticket_types_key ), remove_query_arg( 't_paged' ) );
				}
				if ( current_user_can( 'ttls_clients' ) && $ticket_types_key == 'free' ) {
					continue;
				}
				?>
			<li <?php echo esc_html( ( $ticket_types_key == $ticket_type ) ? 'class=active' : '' ); ?>><a href="<?php echo esc_url( $status_filter_url ) ?>"><?php echo esc_html( $ticket_types_title ); ?></a></li>
		<?php
			}
		?>
		</ul>
	</div>

	</div>
	<?php
		if ( current_user_can( 'ttls_clients' ) ) {
			$new_ticket_url_params = array('action' => 'add');
			if ( ! empty( $license_id ) ) {
				$new_ticket_url_params['license_id'] = $license_id;
			}
	?>
	<a href="<?php echo esc_url( add_query_arg( $new_ticket_url_params ) ); ?>" class="btn btn-info"><?php esc_html_e( 'New Ticket', 'ttls_translate' ); ?></a>
	<?php } ?>
</div>
<div class="ttls__content">
	<div class="ttls__tickets" id="ttls-tickets">
		<?php

			$license_tickets = false;

			if ( current_user_can( 'ttls_clients' ) ) {
				$license_condition = array('meta_query' => array(
					array(
						'key' => 'ttls_owners',
						'value' => get_current_user_id(),
					)	
				));

				if ( $license_id ) {
					$license_condition['p'] = $license_id;
				}

				$license_list = \TTLS\Models\License::find_all( $license_condition );
				$all_licenses_tickets = array();
				foreach( $license_list['items'] as $license ) {
					$_license_tickets = get_post_meta( $license->ID, 'ttls_tickets' );
					if ( ! empty( $_license_tickets ) && is_array( $_license_tickets ) ) {
						$all_licenses_tickets = array_merge( $all_licenses_tickets, $_license_tickets );
					}
				}

				if ( ! empty( $all_licenses_tickets ) ) {
					$license_tickets = $all_licenses_tickets;
				}
			}

			$all_tickets = TTLS()->ticket_service()->get_list( $paged, $license_tickets, ( $ticket_type === 'all' ? false : $ticket_type ), $product_id );

			if ( ! is_wp_error( $all_tickets ) ) {
		?>
		<div class="ttls__tickets-inner">
			<table class="table table-striped">
				<thead>
					<tr>
						<th><?php echo esc_html__('Status', 'ttls_translate'); ?></th>
						<th><?php echo esc_html__('Product', 'ttls_translate'); ?></th>
						<th><?php echo esc_html__('Title', 'ttls_translate'); ?></th>
						<?php if ( current_user_can( 'ttls_developers' ) ) { ?>
						<th><?php echo esc_html__('Client', 'ttls_translate'); ?></th>
						<th><?php echo esc_html__('Agent', 'ttls_translate'); ?></th>
						<?php } ?>
						<th><?php echo esc_html__('Latest response', 'ttls_translate'); ?></th>
						<th><?php echo esc_html__('Attachment', 'ttls_translate'); ?></th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th><?php echo esc_html__('Status', 'ttls_translate'); ?></th>
						<th><?php echo esc_html__('Product', 'ttls_translate'); ?></th>
						<th><?php echo esc_html__('Title', 'ttls_translate'); ?></th>
						<?php if ( current_user_can( 'ttls_developers' ) ) { ?>
						<th><?php echo esc_html__('Client', 'ttls_translate'); ?></th>
						<th><?php echo esc_html__('Agent', 'ttls_translate'); ?></th>
						<?php } ?>
						<th><?php echo esc_html__('Latest response', 'ttls_translate'); ?></th>
						<th><?php echo esc_html__('Attachment', 'ttls_translate'); ?></th>
					</tr>
				</tfoot>
				<tbody>
					<?php foreach ( $all_tickets['tickets'] as $ticket) {
						$label = '';
						switch ( $ticket['status'] ) {
							case 'free': 		$label = '<span class="btn btn-block btn-xs btn-default">'.esc_html( $ticket_types[$ticket['status']] ).'</span>'; break;
							case 'pending':		$label = '<span class="btn btn-block btn-xs btn-warning">'.esc_html( $ticket_types[$ticket['status']] ).'</span>'; break;
							case 'replied':		$label = '<span class="btn btn-block btn-xs btn-success">'.esc_html( $ticket_types[$ticket['status']] ).'</span>'; break;
							case 'closed': 		$label = '<span class="btn btn-block btn-xs btn-danger">'.esc_html( $ticket_types[$ticket['status']] ).'</span>'; break;
						}
						?>
					<tr>
						<td><?php echo wp_kses_post( $label ); ?></td>
						<td><?php echo esc_html( $ticket['product_title'] ); ?></td>
						<td><a href="<?php echo esc_url( add_query_arg( array( 't_paged' => 1, 'ticket_id' => $ticket['id'] ) ) ); ?>" class="ttls__tickets-url"><?php echo '#'.esc_html( $ticket['id'] . '. '.stripslashes($ticket['title']) ); ?></a></td>
						<?php if ( current_user_can( 'ttls_developers' ) ) { ?>
						<td>
							<div><?php echo esc_html( $ticket['client_login'] ); ?></div>
							<div class="label label-primary"><?php echo esc_html( $ticket['client_license_type'] ); ?></div>
						</td>
						<td>
							<?php if ( $ticket['developer_id'] ) { ?>
							<div><?php echo esc_html( $ticket['developer'] ); ?></div><small><?php echo esc_html( $ticket['developer_position'] ); ?></small>
							<?php } else { ?>
								<small><?php echo esc_html( $ticket['developer'] ); ?></small>
							<?php  }  ?>
						</td>
						<?php } ?>
						<td><span class="ttls__tickets-response"><?php echo esc_html( $ticket['response_count'] ); ?></span><span class="text-muted"> :	</span><span><?php echo esc_html( $ticket['response_last_date'] ); ?></span></td>
						<td>
							<div class="ttls__tickets-attach btn-block"><?php echo esc_html( $ticket['attachment_count'] ); ?></div>
						</td>
					</tr>


					<?php } ?>
				</tbody>
			</table>
		</div>

		<?php if ( $all_tickets['count_tickets'] > 10 ) {
			$max_pages = ceil( $all_tickets['count_tickets'] / 10 );
			$this_page = 1;

			if ( $max_pages > 12 ) {
				$start_page = $paged - 3;
				if ( $start_page <= 0 ) {
					$start_page = 1;
				}
				if( $start_page >= $max_pages - 5 ){
					$start_page = $max_pages - 6;
				}
				$finish_page = $start_page + 6;
				if ( $finish_page > $max_pages ) {
					$finish_page = $max_pages;
				}
			} else {
				$start_page = 1;
				$finish_page = $max_pages;
			}
		?>
		<nav aria-label="Page navigation" class="text-center"><ul class="pagination">
			<?php
			if ( $start_page > 1 ) {
				echo '<li><a href="';
				echo esc_url( add_query_arg( 't_paged', 1 ) ) .'"';
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
				echo esc_url( add_query_arg( 't_paged', $max_pages ) ) .'"';
				echo '>'.esc_html__('Latest', 'ttls_translate').'</a></li>';
			}

			?>
		</ul></nav>
		<?php } ?>
	<?php } else { ?>
		<?php echo esc_html__( $all_tickets->get_error_message(), 'ttls_translate'); ?>
	<?php } ?>
	</div>
</div>