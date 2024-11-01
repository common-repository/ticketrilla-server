<?php
	$paged = ( isset( $_GET['t_paged'] ) AND $_GET['t_paged'] ) ? sanitize_key( $_GET['t_paged'] ) : 1;
	$l_type = empty( $_GET['l_type'] ) ? '' : sanitize_key( $_GET['l_type'] );
	$l_product = empty( $_GET['product_id'] ) ? '' : sanitize_key( $_GET['product_id'] );
?>
<div id="ttls-container" class="ttls">
	<div class="ttls__header">
		<div class="ttls__header-inner">
			<div class="col-left">
				<h2 class="h4 wp-heading-inline"><?php echo esc_html__('Ticketrilla: Server', 'ttls_translate'); ?></h2><a href="<?php echo esc_url( ttls_url( 'ticketrilla-server-general-settings' ) ); ?>" class="btn btn-xs btn-dark"><?php echo esc_html__('Settings', 'ttls_translate'); ?></a>
			</div>
			<div class="col-right">
			<?php
				$breadcrumbs = new \TTLS\Helpers\Breadcrumbs( __('Licenses', 'ttls_translate') );
				$breadcrumbs->render();
			?>
			</div>
		</div>
		<hr class="clearfix">
		<div class="ttls__header-title">
			<div class="ttls__filters">
			<?php
				$products = \TTLS\Models\Product::find_all();
				$product_filter = new \TTLS\Helpers\ProductDropdownFilter( '', $l_type, $products );
				$product_filter->render();

				$active_licenses = array_keys( \TTLS\Models\Product::license_models() );
				$filtered_active_licenses = $active_licenses;
				if ( $l_product ) {
					$selected_product = \TTLS\Models\Product::find_by_id( $l_product );
					if ( $selected_product ) {
						$filtered_active_licenses = array_keys( $selected_product->active_licenses() );
					}
				}
				$licenses_type_filter = new \TTLS\Helpers\LicensesTypeFilter( '', $filtered_active_licenses );
				$licenses_type_filter->render();
			?>
			</div>
			<a href="#" data-bs-toggle="modal" data-bs-target="#ttlsNewUser" class="btn btn-info"><i></i><?php echo esc_html__('New License', 'ttls_translate'); ?></a>
		</div>

	</div>
	<div class="ttls__content">
		<div class="ttls__license">
			<div class="ttls__license-inner">
				<?php $all_licenses = ( new TTLS_License( $l_type, '', $l_product ) )->get_all_list( $paged ); ?>
				<table class="table table-striped">
					<thead>
						<tr>
							<th><?php echo esc_html__('Client', 'ttls_translate'); ?></th>
							<th><?php echo esc_html__('Product', 'ttls_translate'); ?></th>
							<th><?php echo esc_html__('Purchase code', 'ttls_translate'); ?></th>
							<th><?php echo esc_html__('Support', 'ttls_translate'); ?></th>
							<th><?php echo esc_html__('Activity', 'ttls_translate'); ?></th>
							<th><?php echo esc_html__('Actions', 'ttls_translate'); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th><?php echo esc_html__('Client', 'ttls_translate'); ?></th>
							<th><?php echo esc_html__('Product', 'ttls_translate'); ?></th>
							<th><?php echo esc_html__('Purchase code', 'ttls_translate'); ?></th>
							<th><?php echo esc_html__('Support', 'ttls_translate'); ?></th>
							<th><?php echo esc_html__('Activity', 'ttls_translate'); ?></th>
							<th><?php echo esc_html__('Actions', 'ttls_translate'); ?></th>
						</tr>
					</tfoot>
					<tbody>
						<?php
						$ttls_license = new TTLS_License;
						$ttls_user = new TTLS_Users();
						foreach ( $all_licenses['licenses'] as $key => $lic) {
							$row_user_data = array();
							if ( $lic['owners'] ) {
								foreach ( $lic['owners'] as $key => $u_id) {
									$this_user = $ttls_user->get_user( $u_id );
									$tmp_u_d = array();

									if ( is_wp_error(  $this_user  ) ) {
										$tmp_u_d['rowspan'] = 1;
										$tmp_u_d['name'] = esc_html__('Error');
										$tmp_u_d['login'] = esc_html__( $this_user->get_error_message(), 'ttls_translate');
									} else {
										$tmp_u_d['rowspan'] = 1;
										$tmp_u_d['id'] = $u_id;
										$tmp_u_d['name'] = $this_user['name'];
										$tmp_u_d['login'] = $this_user['login'];
										$tmp_u_d['email'] = $this_user['email'];
									}
									$row_user_data[] = $tmp_u_d;
								}
							} else {
								$tmp_u_d['rowspan'] = 1;
								$tmp_u_d['login'] = esc_html__('No owners', 'ttls_translate');
								$tmp_u_d['name'] = '';
								$tmp_u_d['email'] = '';
								$row_user_data[] = $tmp_u_d;
							}
							ttls_render( $ttls_license->html_user_license( $row_user_data, false, $lic ) );
						}
						?>
					</tbody>
				</table>
			</div>

			<?php if ( $all_licenses['count_tickets'] > 10 ) {
				$max_pages = ceil( $all_licenses['count_tickets'] / 10 );
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
					echo '<li><a href="' . esc_url( add_query_arg( 't_paged', 1 ) ) . '"';
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
					echo esc_url( add_query_arg( 't_paged', $start_page ) ) . '"';
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
	</div>

	<div id="ttlsLicense" tabindex="-1" role="dialog" class="modal fade">
		<div role="document" class="modal-dialog">
			<div class="modal-content">
				<form class="form">
					<p><?php echo esc_html__('Loading data...', 'ttls_translate'); ?></p>
				</form>
			</div>
		</div>
	</div>

	<div id="ttlsNewUser" tabindex="-1" role="dialog" class="modal fade">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" data-bs-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
                    <h4
                    	data-addlicense="<?php echo esc_html__('New License', 'ttls_translate'); ?>"
                    	data-createuser="<?php echo esc_html__('New Client', 'ttls_translate'); ?>"
                    	class="modal-title"><?php echo esc_html__('New License', 'ttls_translate'); ?></h4>
                </div>
                <form class="form ttls-new-client">
                	<input type="hidden" name="form_type" value="license">
                    <div class="modal-body">
                    	<div class="form-group">
		                	<select name="user" class="form-control select2 ttls_license_form_changer">
		                		<option value="" disabled selected><?php echo esc_html__('Select user', 'ttls_translate'); ?></option>
								<option value="" data-bs-toggle="collapse" data-bs-target=".ttls__license-newuser"><?php echo esc_html__('New Client', 'ttls_translate'); ?></option>
							<?php
							$args = array(
								'role'				 => 'ttls_clients',
								'paged'				 => $paged,
								'number'			=> -1
							);
							$users = get_users( $args );
							foreach ( $users as $usr) {
								echo '<option value="'.esc_attr( $usr->ID ).'">'.esc_html( get_user_meta( $usr->ID, 'first_name', true ) ).' ('.esc_html( $usr->user_login ).')</option>';
							} ?>
							</select>
                    	</div>

	                        <div class="form-group ttls__license-newuser collapse">
	                            <div class="input-group">
	                            	<span class="input-group-addon"><i class="fa fa-at"></i></span>
	                                <input id="ttls__new-user-login" name="login" type="text" placeholder="<?php echo esc_html__('Username', 'ttls_translate'); ?>" class="form-control">
	                        		<input id="ttls__new-user-type" type="hidden" value="users" class="form-control">
	                            </div>
	                        </div>
	                        <div class="form-group ttls__license-newuser collapse">
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-user"></i></span>
									<input id="ttls__new-dev-name" name="name" type="text" placeholder="<?php echo esc_html__('Name', 'ttls_translate'); ?>" class="form-control">
								</div>
							</div>
	                        <div class="form-group ttls__license-newuser collapse">
	                            <div class="input-group">
	                            	<span class="input-group-addon"><i class="fa fa-envelope"></i></span>
	                                <input id="ttls__new-user-mail" name="email" type="email" placeholder="<?php echo esc_html__('Email', 'ttls_translate'); ?>" class="form-control">
	                            </div>
	                        </div>

						<div class="form-group">
							<select name="product_id" class="form-control select2 ttls_license_select_product">
								<option value="" disabled selected><?php echo esc_html__('Select product', 'ttls_translate'); ?></option>
							<?php foreach ( $products['items'] as $product ) {
								echo '<option value="'.esc_attr( $product->ID ).'">'.esc_html( $product->post_title ).'</option>';
							} ?>
							</select>
						</div>

            <div class="form-group">
							<select name="license_type" class="form-control select2 ttls_license_select_type">
								<option value="" disabled selected><?php echo esc_html__('Select license type', 'ttls_translate'); ?></option>
							<?php foreach ( $active_licenses as $lic_type ) {
								echo '<option data-bs-toggle="collapse" data-bs-target="#ttls__license-new-'.esc_attr( $lic_type ).'" value="'.esc_attr( $lic_type ).'">'.esc_html( $lic_type ).'</option>';
							} ?>
							</select>
						</div>
						<div id="ttls__license-new-standard" class="collapse fade">
							<div class="form-group row">
								<div class="col-md-12">
									<label><?php echo esc_html__('Purchase code', 'ttls_translate'); ?></label>
									<div class="input-group">
										<input
											id="ttls__edit-dev-password"
											data-password=""
											type="text"
											placeholder="<?php echo esc_html__('Add license', 'ttls_translate'); ?>"
											value=""
											name="license_token"
											class="form-control"
											disabled="disabled"
										>
										<span class="input-group-btn">
											<div
												data-change="<?php echo esc_html__('Select license', 'ttls_translate') ?>"
												data-cancel="<?php echo esc_html__('Generate new', 'ttls_translate') ?>"
												class="btn btn-info ttls_activate_password_changing"><?php echo esc_html__('Generate new', 'ttls_translate') ?></div>
										</span>
									</div>

								</div>
							</div>
							<div class="form-group row">
								<div class="col-md-6">
									<div class="checkbox">
										<input id="ttls__license-new-standard-verified" name="license_verified" type="checkbox" class="ttls__license_input" checked>
										<label for="ttls__license-new-standard-verified"><?php echo esc_html__('License confirmed', 'ttls_translate'); ?></label>
									</div>
									<div class="checkbox">
										<input id="ttls__license-new-standard-have-support" name="license_have_support" type="checkbox" class="ttls__license_input">
										<label for="ttls__license-new-standard-have-support"><?php echo esc_html__('Support is active', 'ttls_translate'); ?></label>
									</div>
								</div>
								<div class="col-md-6">
									<label><?php echo esc_html__('Support until', 'ttls_translate'); ?></label>
									<?php
										$until_date = new \DateTime('now');
										$until_date->modify('+' . 30 .' day');
									 ?>
									<input name="license_have_support_until" type="text"
                                	autocomplete="off" class="form-control ttls__datepicker" value="<?php echo esc_attr( $until_date->format('Y-m-d') ); ?>">
								</div>
							</div>
						</div>
						<?php do_action('ttls_new_client_settings' ); ?>
                    </div>
                    <div class="modal-footer">
                        <button
                        	data-copy_text="<?php echo esc_html__('Copy', 'ttls_translate'); ?>"
                        	data-addlicense="<?php echo esc_html__('Add License', 'ttls_translate'); ?>"
                        	data-createuser="<?php echo esc_html__('Create Client', 'ttls_translate'); ?>"
                        	type="submit"
                        	class="btn btn-dark"><?php echo esc_html__('Add License', 'ttls_translate'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>