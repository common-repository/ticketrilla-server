<?php
	$paged = isset($_GET['t_paged']) ? sanitize_key( $_GET['t_paged'] ) : 1;
	$user_type = isset($_GET['type']) ? sanitize_text_field( $_GET['type'] ) : 'clients'; // clients | developers

	$table_developers = array(
		'developers' => esc_html__('Agents', 'ttls_translate')
	);
	$table_developers = array_merge( $table_developers, apply_filters( 'ttls_add_custom_tables_of_users', array() ) );

	$table_cols = array(
	);
?>
<div id="ttls-container" class="ttls">
	<div class="ttls__header">
		<div class="ttls__header-inner">
			<div class="col-left">
				<h2 class="h4 wp-heading-inline"><?php echo esc_html__('Ticketrilla: Server', 'ttls_translate'); ?></h2><a href="<?php echo esc_url( ttls_url( 'ticketrilla-server-general-settings' ) ); ?>" class="btn btn-xs btn-dark"><?php echo esc_html__('Settings', 'ttls_translate'); ?></a>
			</div>
			<div class="col-right">
			<?php
				$breadcrumbs = new \TTLS\Helpers\Breadcrumbs( $user_type == 'clients' ? __('Clients', 'ttls_translate') : __('Agents', 'ttls_translate') );
				$breadcrumbs->render();
			?>
			</div>
		</div>
		<hr class="clearfix">
		<div class="ttls__header-title">
			<div class="ttls__filter"><span><?php echo esc_html__('Show', 'ttls_translate'); ?>:</span>
				<ul class="ttls__filter">
					<li <?php if ('clients' == $user_type) { echo 'class="active"'; } ?>><a href="<?php echo esc_url( remove_query_arg( array( 'type', 't_paged' ) ) ); ?>"><?php echo esc_html__('Clients', 'ttls_translate'); ?></a></li>
					<?php foreach ( $table_developers as $key => $value) { ?>
					<li <?php if ( $key == $user_type) { echo 'class="active"'; } ?>><a href="<?php echo esc_url( add_query_arg( array( 'type' => $key, 't_paged' => 1 ) ) ); ?>"><?php echo esc_html( $value ); ?></a></li>
					<?php } ?>
				</ul>
			</div>
			<?php if ('developers' == $user_type) { ?>
				<a href="#" data-bs-toggle="modal" data-bs-target="#ttlsNewDeveloper" class="btn btn-info"><i></i><?php echo esc_html__('New Agent', 'ttls_translate'); ?></a>
			<?php } ?>
			<?php if ('clients' == $user_type) { ?>
				<a href="#" data-bs-toggle="modal" data-bs-target="#ttlsNewUser" class="btn btn-info"><i></i><?php echo esc_html__('New Client', 'ttls_translate'); ?></a>
			<?php } ?>
		</div>

	</div>
	<div class="ttls__content">
		<?php
		switch ( $user_type ) {
			case 'clients':
				require_once 'ticketrilla-server-users-clients.php';
				break;

			case 'developers':
				require_once 'ticketrilla-server-users-developers.php';
				break;

			default:
				do_action( 'ttls_print_tables_of_'.$user_type );
				break;
		}
		?>
	</div>

	<div class="ttls__alerts"></div>
<?php
$products = \TTLS\Models\Product::find_all();
$active_licenses = array_keys( \TTLS\Models\Product::license_models() );
?>
	<div id="ttlsNewUser" tabindex="-1" role="dialog" class="modal fade">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" data-bs-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title"><?php echo esc_html__('New Client', 'ttls_translate'); ?></h4>
                </div>
                <form class="form ttls-new-client">
                    <div class="modal-body">
                        <input id="ttls__new-user-type" type="hidden" value="users" class="form-control">
                        <div class="form-group">
                            <div class="input-group">
                            	<span class="input-group-addon"><i class="fa fa-at"></i></span>
                                <input id="ttls__new-user-login" name="login" type="text" placeholder="<?php echo esc_html__('Username', 'ttls_translate'); ?>" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-user"></i></span>
								<input id="ttls__new-dev-name" name="name" type="text" placeholder="<?php echo esc_html__('Name', 'ttls_translate'); ?>" class="form-control">
							</div>
						</div>
                        <div class="form-group">
                            <div class="input-group">
                            	<span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                                <input id="ttls__new-user-mail" name="email" type="email" placeholder="<?php echo esc_html__('Email', 'ttls_translate'); ?>" class="form-control">
                            </div>
                        </div>
						<div class="form-group">
							<select name="product_id" class="form-control select2 ttls_license_select_product">
								<option value="" disabled selected><?php echo esc_html__('Select product', 'ttls_translate'); ?></option>
							<?php
								foreach ( $products['items'] as $product ) {
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
											placeholder="<?php echo esc_html__('Add License', 'ttls_translate'); ?>"
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
										$until_date->modify('+'. 30 .' day');
									 ?>
									<input name="license_have_support_until" type="text"
                                	autocomplete="off" class="form-control ttls__datepicker" value="<?php echo esc_attr( $until_date->format('Y-m-d') ); ?>">
								</div>
							</div>
						</div>
						<?php do_action('ttls_new_client_settings' ); ?>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-dark" data-copy_text="<?php echo esc_html__('Copy', 'ttls_translate'); ?>"><?php echo esc_html__('Create', 'ttls_translate'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

	<div id="ttlsNewDeveloper" tabindex="-1" role="dialog" class="modal fade">
		<div role="document" class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" data-bs-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
					<h4 class="modal-title"><?php echo esc_html__('New agent', 'ttls_translate'); ?></h4>
				</div>
				<form class="form ttls-new-developer">
					<div class="modal-body">
						<input id="ttls__new-dev-type" type="hidden" value="developers">
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-at"></i></span>
								<input id="ttls__new-dev-login" name="login" type="text" placeholder="<?php echo esc_html__('Username', 'ttls_translate'); ?>" class="form-control">
							</div>
						</div>
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-user"></i></span>
								<input id="ttls__new-dev-name" name="name" type="text" placeholder="<?php echo esc_html__('Name', 'ttls_translate'); ?>" class="form-control">
							</div>
						</div>
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-envelope"></i></span>
								<input id="ttls__new-dev-mail" name="email" type="email" placeholder="<?php echo esc_html__('Email', 'ttls_translate'); ?>" class="form-control">
							</div>
						</div>
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-user-tie"></i></span>
								<input id="ttls__new-dev-position" name="position" type="text" placeholder="<?php echo esc_html__('Position', 'ttls_translate'); ?>" class="form-control">
							</div>
						</div>
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-lock"></i></span>
								<input
									id="ttls__edit-dev-password"
									data-password="<?php echo wp_generate_password(); ?>"
									type="text"
									placeholder="<?php echo esc_html__('Password', 'ttls_translate') ?>"
									value="<?php echo esc_html__('Password', 'ttls_translate') ?>"
									name="ttls_user_password"
									class="form-control"
									disabled="disabled"
								>
								<span class="input-group-btn">
									<div
										data-change="<?php echo esc_html__('Change passowrd', 'ttls_translate') ?>"
										data-cancel="<?php echo esc_html__('Do not change', 'ttls_translate') ?>"
										class="btn btn-info ttls_activate_password_changing"><?php echo esc_html__('Change passowrd', 'ttls_translate') ?></div>
								</span>
							</div>
						</div>
						<div class="checkbox">
							<input type="hidden" name="caps[ttls_plugin_admin]" value="">
							<input id="ttls__new-dev-caps-ttls_plugin_admin" type="checkbox" name="caps[ttls_plugin_admin]" value="true">
							<label for="ttls__new-dev-caps-ttls_plugin_admin"><?php echo esc_html__('Plugin administrator', 'ttls_translate') ?></label>
						</div>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-dark"  data-copy_text="<?php echo esc_html__('Copy', 'ttls_translate'); ?>"><?php echo esc_html__('Create agent', 'ttls_translate') ?></button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div id="ttlsEditDeveloper" tabindex="-1" role="dialog" class="modal fade">
		<div role="document" class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" data-bs-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
					<h4 class="modal-title"><?php echo esc_html__('Change agent', 'ttls_translate'); ?></h4>
				</div>
			</div>
		</div>
	</div>
	<div id="ttlsNewDeveloperDelete" tabindex="-1" role="dialog" class="modal fade">
		<div role="document" class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" data-bs-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
					<h4 class="modal-title"><?php echo esc_html__('Delete agent', 'ttls_translate'); ?></h4>
				</div>
				<form class="form ttls-delete-developer">
					<div class="modal-body">
						<input id="ttls_delete_user_id" name="user" type="hidden" value="">
						<div class="form-group">
							<label><?php echo esc_html__("Select an agent who should be reassigned to all materials",'ttls_translate'); ?></label>
							<select id="ttls_recepient_users" name="recepient" class="form-control">
								<option value="" disabled selected><?php echo esc_html__('Select agent', 'ttls_translate'); ?></option>
								<?php
									$args = array(
										'role__in' => array('ttls_developers', 'Administrator'),
										'fields' => array( 'ID', 'user_login', 'display_name' ),
										'number' => -1
									);
									$users = get_users( $args );
									foreach ( $users as $key => $usr) {
										echo '<option title="'.esc_attr( $usr->user_login ).'" value="'.esc_attr( $usr->ID ).'">'.esc_html( get_user_meta( $usr->ID, 'first_name', true).' - '.get_user_meta( $usr->ID, 'nickname', true) ).'</option>';
									}
								 ?>
							</select>
						</div>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-dark"><?php echo esc_html__('Delete', 'ttls_translate'); ?></button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div id="ttlsNewLicense" tabindex="-1" role="dialog" class="modal fade">
		<div role="document" class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" data-bs-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
					<h4 class="modal-title"><?php echo esc_html__('New license', 'ttls_translate'); ?></h4>
				</div>
				<form action="#" class="form ttls-license-add">
					<div class="modal-body">
						<div class="form-group">
							<input id="ttls_new_license_user" type="hidden" name="user" value="">
							<input id="ttls_new_license_user_login" type="text" name="ttls_userlogin" disabled value="" class="form-control">
						</div>
						<div class="form-group">
							<select name="product_id" class="form-control select2 ttls_license_select_product">
								<option value="" disabled selected><?php echo esc_html__('Select product', 'ttls_translate'); ?></option>
							<?php
								foreach ( $products['items'] as $product ) {
								echo '<option value="'.esc_attr( $product->ID ).'">'.esc_html( $product->post_title ).'</option>';
							} ?>
							</select>
						</div>

            <div class="form-group">
							<select name="license_type" class="form-control select2 ttls_license_select_type">
								<option value="" disabled selected><?php echo esc_html__('Select license type', 'ttls_translate'); ?></option>
							<?php foreach ( $active_licenses as $lic_type ) {
								echo '<option data-bs-toggle="collapse" data-bs-target="#ttls__license-add-'.esc_attr( $lic_type ).'" value="'.esc_attr( $lic_type ).'">'.esc_html( $lic_type ).'</option>';
							} ?>
							</select>
						</div>
						<div id="ttls__license-add-standard" class="collapse fade">
							<div class="form-group row">
								<div class="col-md-12">
									<label><?php echo esc_html__('Purchase code', 'ttls_translate'); ?></label>
									<div class="input-group">
										<input
											id="ttls__edit-dev-password"
											data-password=""
											type="text"
											placeholder="<?php echo esc_html__('Add License', 'ttls_translate'); ?>"
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
										<input id="ttls__license-add-standard-verified" name="license_verified" type="checkbox" class="ttls__license_input" checked>
										<label for="ttls__license-add-standard-verified"><?php echo esc_html__('License confirmed', 'ttls_translate'); ?></label>
									</div>
									<div class="checkbox">
										<input id="ttls__license-add-standard-have-support" name="license_have_support" type="checkbox" class="ttls__license_input">
										<label for="ttls__license-add-standard-have-support" data-bs-toggle="collapse" data-bs-target="#ttls__license-standard-support-until"><?php echo esc_html__('Support is active', 'ttls_translate'); ?></label>
									</div>
								</div>
								<div class="col-md-6">
									<label><?php echo esc_html__('Support until', 'ttls_translate'); ?></label>
									<?php
										$until_date = new \DateTime('now');
										$until_date->modify('+'. 30 .' day');
									 ?>
									<input name="license_have_support_until" type="text"
                                	autocomplete="off" class="form-control ttls__datepicker" value="<?php echo esc_attr( $until_date->format('Y-m-d') ); ?>">
								</div>
							</div>
						</div>
						<?php do_action('ttls_new_license_settings' ); ?>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-dark" data-copy_text="<?php echo esc_html__('Copy', 'ttls_translate'); ?>"><?php echo esc_html__('Add License', 'ttls_translate'); ?></button>
					</div>
				</form>
			</div>
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

</div>