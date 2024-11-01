<?php
$product = empty( $data['product'] ) ?  '' : $data['product'];
$form_errors_helper = new \TTLS\Helpers\FormErrors();
$action = 'ttls_admin_save_product';
?>
<div id="ttls-container" class="ttls">
	<div class="ttls__header">
		<div class="ttls__header-inner">
			<div class="col-left">
				<h2 class="h4 wp-heading-inline"><?php echo esc_html__('Ticketrilla: Server', 'ttls_translate'); ?></h2>
			</div>
			<div class="col-right">
			<?php
				$breadcrumbs = new \TTLS\Helpers\Breadcrumbs( array(
					'title' => __('Product settings', 'ttls_translate'),
					'url' => ttls_url( 'ticketrilla-server-product-settings'),
				) );
				$breadcrumbs->add( $product && $product->post_title ? $product->post_title : __( 'Add product', 'ttls_translate' ) );
				$breadcrumbs->render();
			?>
			</div>
		</div>
		<hr class="clearfix">
	</div>
	<div class="ttls__content">
		<div class="ttls__settings ttls__product-settings">
			<div class="row">
				<div class="col-md-4 pull-right-md">
					<div id="ttls__settings-menu" class="ttls__settings-inner">
						<div class="ttls__settings-inner-header">
							<h4><?php echo esc_html__('Settings menu', 'ttls_translate'); ?></h4>
						</div>
						<div class="ttls__settings-inner-body">
							<ul class="nav ttls__settings-menu">
								<li><a href="#ttls__product" data-scroll><?php echo esc_html__('Product', 'ttls_translate'); ?></a></li>
								<li><a href="#ttls__license_standard" data-scroll><?php echo esc_html__('Standard license', 'ttls_translate'); ?></a></li>
								<?php do_action( 'ttls_product_settings_add_box_menu' ); ?>
							</ul>

						</div>
					</div>
					<div class="ttls__divider"></div>
				</div>
				<div class="col-md-8">
					<form action="#" class="ttls__settings-inner ttls-admin-save-product-form"  method="post" action="">
					<?php if ( ! empty( $product->ID ) ) { ?>
						<input type="hidden" name="ID" value="<?php echo esc_attr( $product->ID ); ?>">
					<?php } ?>
						<input type="hidden" name="action" value="<?php echo esc_attr( $action ); ?>">
						<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( $action ); ?>">
						<div id="ttls__product">
							<div class="ttls__settings-inner-header">
								<h4>
									<?php echo esc_html__('Support product settings', 'ttls_translate'); ?>
									<label><div class="ttls__label-info">
										<i class="fa fa-question-circle-o" aria-hidden="true"></i>
										<div class="ttls__label-info-hidden">
											<?php echo esc_html__('Client-side information', 'ttls_translate'); ?>
										</div>
									</div></label>
								</h4>
							</div>
							<div class="ttls__settings-inner-body">
								<div class="row">
									<div class="col-md-6">
										<?php
											$input_name = 'type';
											$input_label = $product->get_label( $input_name );
											$license_input_id_prefix = 'ttls__product-';
											$input_id = $license_input_id_prefix . $input_name;
										?>
										<div class="form-group <?php echo esc_attr( $product->has_errors( $input_name ) ? 'has-error' : '' ); ?>">
											<label for="<?php echo esc_attr( $input_id ); ?>">
												<?php echo esc_html( $input_label ); ?>
											</label>
											<select id="<?php echo esc_attr( $input_id ); ?>" class="form-control" name="<?php echo esc_attr( $input_name ); ?>">
												<?php
													foreach ( $product::product_types() as $key => $value) {
														echo '<option value="'.esc_attr( $key ).'"';
														echo esc_html( $key == $product->$input_name ? ' selected' : '' );
														echo '>'.esc_html( $value ).'</option>';
													}
												?>
											</select>
											<?php $form_errors_helper->render( $input_name, $product ); ?>
										</div>
										<?php
											$input_name = 'author_name';
											$input_label = $product->get_label( $input_name );
											$input_id = $license_input_id_prefix . $input_name;
										?>
										<div class="form-group <?php echo esc_attr( $product->has_errors( $input_name ) ? 'has-error' : '' ); ?>">
											<label for="<?php echo esc_attr( $input_id ); ?>">
											<?php echo esc_html( $input_label ); ?>
											</label>
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-user"></i></span>
												<input
													id="<?php echo esc_attr( $input_id ); ?>"
													name="<?php echo esc_attr( $input_name ); ?>"
													type="text"
													placeholder="<?php echo esc_attr( $input_label ); ?>"
													value="<?php echo esc_attr( $product->$input_name ); ?>"
													class="form-control"
												>
											</div>
											<?php $form_errors_helper->render( $input_name, $product ); ?>
										</div>
										<?php
											$input_name = 'author_link';
											$input_label = $product->get_label( $input_name );
											$input_id = $license_input_id_prefix . $input_name;
										?>
										<div class="form-group <?php echo esc_attr( $product->has_errors( $input_name ) ? 'has-error' : '' ); ?>">
											<label for="<?php echo esc_attr( $input_id ); ?>">
												<?php echo esc_html( $input_label ); ?>
											</label>
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-link"></i></span>
												<input
													id="<?php echo esc_attr( $input_id ); ?>"
													name="<?php echo esc_attr( $input_name ); ?>"
													type="text"
													placeholder="<?php echo esc_attr( $input_label ); ?>"
													value="<?php echo esc_attr( $product->$input_name ); ?>"
													class="form-control"
												>
											</div>
											<?php $form_errors_helper->render( $input_name, $product ); ?>
										</div>
										<?php
											$input_name = 'manual';
											$input_label = $product->get_label( $input_name );
											$input_id = $license_input_id_prefix . $input_name;
										?>
										<div class="form-group <?php echo esc_attr( $product->has_errors( $input_name ) ? 'has-error' : '' ); ?>">
											<label for="<?php echo esc_attr( $input_id ); ?>">
											<?php echo esc_html( $input_label ); ?>
											</label>
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-link"></i></span>
												<input
													id="<?php echo esc_attr( $input_id ); ?>"
													name="<?php echo esc_attr( $input_name ); ?>"
													type="text"
													placeholder="<?php echo esc_attr( $input_label ); ?>"
													value="<?php echo esc_attr( $product->$input_name ); ?>"
													class="form-control"
												>
											</div>
											<?php $form_errors_helper->render( $input_name, $product ); ?>
										</div>
										<?php
											$input_name = 'terms';
											$input_label = $product->get_label( $input_name );
											$input_id = $license_input_id_prefix . $input_name;
										?>
										<div class="form-group <?php echo esc_attr( $product->has_errors( $input_name ) ? 'has-error' : '' ); ?>">
											<label for="<?php echo esc_attr( $input_id ); ?>">
												<?php echo esc_html( $input_label ); ?>
											</label>
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-link"></i></span>
												<input
													id="<?php echo esc_attr( $input_id ); ?>"
													name="<?php echo esc_attr( $input_name ); ?>"
													type="text"
													placeholder="<?php echo esc_attr( $input_label ); ?>"
													value="<?php echo esc_attr( $product->$input_name ); ?>"
													class="form-control"
												>
											</div>
											<?php $form_errors_helper->render( $input_name, $product ); ?>
										</div>
										<?php
											$input_name = 'privacy';
											$input_label = $product->get_label( $input_name );
											$input_id = $license_input_id_prefix . $input_name;
										?>
										<div class="form-group <?php echo esc_attr( $product->has_errors( $input_name ) ? 'has-error' : '' ); ?>">
											<label for="<?php echo esc_attr( $input_id ); ?>">
												<?php echo esc_html( $input_label ); ?>
											</label>
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-link"></i></span>
												<input
													id="<?php echo esc_attr( $input_id ); ?>"
													name="<?php echo esc_attr( $input_name ); ?>"
													type="text"
													placeholder="<?php echo esc_attr( $input_label ); ?>"
													value="<?php echo esc_attr( $product->$input_name ); ?>"
													class="form-control"
												>
											</div>
											<?php $form_errors_helper->render( $input_name, $product ); ?>
										</div>

									</div>
									<div class="col-md-6">
									<?php
										$input_name = 'post_title';
										$input_label = $product->get_label( $input_name );
										$input_id = $license_input_id_prefix . $input_name;
										?>
										<div class="form-group <?php echo esc_attr( $product->has_errors( $input_name ) ? 'has-error' : '' ); ?>">
											<label for="<?php echo esc_attr( $input_id ); ?>">
												<?php echo esc_html( $input_label ); ?>
											</label>
											<input
													id="<?php echo esc_attr( $input_id ); ?>"
													name="<?php echo esc_attr( $input_name ); ?>"
													type="text"
													placeholder="<?php echo esc_attr( $input_label ); ?>"
													value="<?php echo esc_attr( $product->$input_name ); ?>"
													class="form-control"
											>
											<?php $form_errors_helper->render( $input_name, $product ); ?>
											<?php if ( $product->post_name ) { ?>
											<p class="help-block"><?php echo esc_html( $product->get_label( 'post_name' ) ); ?> - <code><?php echo esc_html( $product->post_name ); ?></code></p>
											<?php } ?>
										</div>
										<?php
											$input_name = 'post_content';
											$input_label = $product->get_label( $input_name );
											$input_id = $license_input_id_prefix . $input_name;
										?>
										<div class="form-group <?php echo esc_attr( $product->has_errors( $input_name ) ? 'has-error' : '' ); ?>">
											<label for="<?php echo esc_attr( $input_id ); ?>">
												<?php echo esc_html( $input_label ); ?>
											</label>
											<textarea id="<?php echo esc_attr( $input_id ); ?>" class="form-control" name="<?php echo esc_attr( $input_name ); ?>" rows="10" placeholder="<?php echo esc_attr( $input_label ); ?>"><?php echo esc_textarea( $product->$input_name ); ?></textarea>
											<?php $form_errors_helper->render( $input_name, $product ); ?>
										</div>
										<?php
											$input_name = 'image';
											$input_label = $product->get_label( $input_name );
											$input_id = $license_input_id_prefix . $input_name;
										?>
										<div class="form-group <?php echo esc_attr( $product->has_errors( $input_name ) ? 'has-error' : '' ); ?>">
											<label for="<?php echo esc_attr( $input_id ); ?>">
												<?php echo esc_html( $input_label ); ?>
											</label>
											<?php if ( empty( $product->$input_name ) ) { ?>
											<div title="<?php echo esc_html__('Select product image', 'ttls_translate'); ?>" class="ttls__settings-product-avatar ttls_add_image">
												<img src="" alt="Product avatar" style="display: none;">
												<input name="<?php echo esc_attr( $input_name ); ?>" type="hidden"  value="">
											</div>
											<span class="help-block"><?php echo esc_html__('Select product image', 'ttls_translate'); ?></span>
											<?php } else { ?>
											<div title="<?php echo esc_html__('Change', 'ttls_translate'); ?>" class="ttls__settings-product-avatar ttls_add_image">
												<img src="<?php echo wp_get_attachment_image_url( $product->$input_name , 'thumbnail' ); ?>" alt="Product image">
												<span class="fa fa-redo"></span>
												<input name="<?php echo esc_attr( $input_name ); ?>" type="hidden" value="<?php echo esc_attr( $product->$input_name ); ?>">
											</div>
											<span class="help-block"><?php echo esc_html__('Select image', 'ttls_translate'); ?></span>
											<?php } ?>
											<?php $form_errors_helper->render( $input_name, $product ); ?>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6">
										<?php
											$input_name = 'open_registration';
											$input_label = $product->get_label( $input_name );
											$input_id = $license_input_id_prefix . $input_name;
										?>
										<div class="form-group <?php echo esc_attr( $product->has_errors( $input_name ) ? 'has-error' : '' ); ?>">
											<div class="checkbox">
												<input
													id="<?php echo esc_attr( $input_id ); ?>"
													type="checkbox"
													value="y"
													name="<?php echo esc_attr( $input_name ); ?>"
													<?php echo esc_html( $product->$input_name ? 'checked' : '' ); ?>
												>
												<label for="<?php echo esc_attr( $input_id ); ?>">
													<?php echo esc_html( $input_label ); ?>
													<div class="ttls__label-info">
														<i class="fa fa-question-circle-o" aria-hidden="true"></i>
														<div class="ttls__label-info-hidden">
															<?php echo esc_html__('Any user can register', 'ttls_translate'); ?>
														</div>
													</div>
												</label>
											</div>
											<?php $form_errors_helper->render( $input_name, $product ); ?>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<span>
												<?php echo esc_html__('Product presets', 'ttls_translate'); ?>
												<div class="ttls__label-info">
													<i class="fa fa-question-circle-o" aria-hidden="true"></i>
													<div class="ttls__label-info-hidden">
														<?php
															echo esc_html__('Please input the preset settings to your product. The copied text should be inserted in the style.css file (for themes) or main php-file (for plugins) at the upper section of the comments.', 'ttls_translate');
															echo ' <a href="//ticketrilla.com/files/server/example.png" target="_blank">'.esc_html__( 'Example', 'ttls_translate').'</a>';
															echo '<br>' . esc_html__( 'Product title must be saved before preset settings generation.', 'ttls_translate' );
														?>
													</div>
												</div>
											</span>
											<a data-bs-toggle="modal" data-bs-target="#ttlsGenerator" class="btn btn-info"><?php echo esc_html__('Generate settings', 'ttls_translate') ?></a>
										</div>
									</div>
								</div>
							</div>
						</div>
						
						<div id="ttls__license_standard">
							<div class="ttls__settings-inner-header">
								<h4><?php echo esc_html__('Standard license settings', 'ttls_translate'); ?></h4>
							</div>
							<div class="ttls__settings-inner-body">
								<div class="row">
									<div class="col-md-6">
										<?php
											$license = $product->licenses['standard'];
											$input_template = 'licenses[standard][%s]';
											$attribute_name = 'enabled';
											$input_name = sprintf( $input_template, $attribute_name );
											$license_input_id_prefix = 'ttls__license-standard-';
											$input_id = $license_input_id_prefix . $attribute_name;
											$input_label = $license->get_label( $attribute_name );
											?>
										<div class="form-group <?php echo esc_attr( $license->has_errors( $attribute_name ) ? 'has-error' : '' ); ?>">
											<div class="checkbox">
												<input
													id="<?php echo esc_attr( $input_id ); ?>"
													type="checkbox"
													value="y"
													name="<?php echo esc_attr( $input_name ); ?>"
													<?php echo esc_html( $license->$attribute_name ? 'checked' : '' ); ?>
												>
												<label for="<?php echo esc_attr( $input_id ); ?>">
													<?php echo esc_html( $input_label ); ?>
												</label>
											</div>
											<?php $form_errors_helper->render( $attribute_name, $product ); ?>
										</div>
										<?php
											$attribute_name = 'multiple_users';
											$input_name = sprintf( $input_template, $attribute_name );
											$input_label = $license->get_label( $attribute_name );
											$input_id = $license_input_id_prefix . $attribute_name;
										?>
										<div class="form-group <?php echo esc_attr( $license->has_errors( $input_name ) ? 'has-error' : '' ); ?>">
											<div class="checkbox">
												<input
													id="<?php echo esc_attr( $input_id ); ?>"
													type="checkbox"
													value="y"
													name="<?php echo esc_attr( $input_name ); ?>"
													<?php echo esc_html( $license->$attribute_name ? 'checked' : '' ); ?>
												>
												<label for="<?php echo esc_attr( $input_id ); ?>">
													<?php echo esc_html( $input_label ); ?>
													<div class="ttls__label-info">
														<i class="fa fa-question-circle-o" aria-hidden="true"></i>
														<div class="ttls__label-info-hidden">
															<?php echo esc_html__('When enabled - unlimited amount of users will be allowed to use the same license', 'ttls_translate'); ?>
														</div>
													</div>
												</label>
											</div>
											<?php $form_errors_helper->render( $attribute_name, $license ); ?>
										</div>
										<?php
											$attribute_name = 'new_verified';
											$input_name = sprintf( $input_template, $attribute_name );
											$input_label = $license->get_label( $attribute_name );
											$input_id = $license_input_id_prefix . $attribute_name;
										?>
										<div class="form-group <?php echo esc_attr( $license->has_errors( $attribute_name ) ? 'has-error' : '' ); ?>">
											<div class="checkbox">
												<input
													id="<?php echo esc_attr( $input_id ); ?>"
													type="checkbox"
													value="y"
													name="<?php echo esc_attr( $input_name ); ?>"
													<?php echo esc_html( $license->$attribute_name ? 'checked' : '' ); ?>
												>
												<label for="<?php echo esc_attr( $input_id ); ?>">
													<?php echo esc_html( $input_label ); ?>
													<div class="ttls__label-info">
														<i class="fa fa-question-circle-o" aria-hidden="true"></i>
														<div class="ttls__label-info-hidden">
															<?php echo esc_html__('Licenses linked via the client plugin will be confirmed', 'ttls_translate'); ?>
														</div>
													</div>
												</label>
											</div>
											<?php $form_errors_helper->render( $attribute_name, $license ); ?>
										</div>
										<?php
											$attribute_name = 'new_support';
											$input_name = sprintf( $input_template, $attribute_name );
											$input_label = $license->get_label( $attribute_name );
											$input_id = $license_input_id_prefix . $attribute_name;
										?>
										<div class="form-group <?php echo esc_attr( $license->has_errors( $attribute_name ) ? 'has-error' : '' ); ?>">
											<div class="checkbox">
												<input
													id="<?php echo esc_attr( $input_id ); ?>"
													type="checkbox"
													value="y"
													name="<?php echo esc_attr( $input_name ); ?>"
													<?php echo esc_html( $license->$attribute_name ? 'checked' : '' ); ?>
												>
												<label for="<?php echo esc_attr( $input_id ); ?>">
													<?php echo esc_html( $input_label ); ?>
													<div class="ttls__label-info">
														<i class="fa fa-question-circle-o" aria-hidden="true"></i>
														<div class="ttls__label-info-hidden">
															<?php echo esc_html__('Licenses linked via the client plugin will have an active subscription enabled', 'ttls_translate'); ?>
														</div>
													</div>
												</label>
											</div>
											<?php $form_errors_helper->render( $attribute_name, $license ); ?>
										</div>
									</div>
									<div class="col-md-6">
										<?php
											$attribute_name = 'extend_support_link';
											$input_name = sprintf( $input_template, $attribute_name );
											$input_label = $license->get_label( $attribute_name );
											$input_id = $license_input_id_prefix . $attribute_name;
										?>
										<div class="form-group <?php echo esc_attr( $license->has_errors( $attribute_name ) ? 'has-error' : '' ); ?>">
											<label for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $input_label ); ?></label>
											<div class="input-group">
												<span class="input-group-addon"><i class="fa fa-link"></i></span>
												<input
													id="<?php echo esc_attr( $input_id ); ?>"
													name="<?php echo esc_attr( $input_name ); ?>"
													type="text"
													placeholder="<?php echo esc_attr( $input_label ); ?>"
													value="<?php echo esc_attr( $license->$attribute_name ); ?>"
													class="form-control"
												>
											</div>
											<?php $form_errors_helper->render( $attribute_name, $license ); ?>
										</div>
										<?php
											$attribute_name = 'expiry_date';
											$input_name = sprintf( $input_template, $attribute_name );
											$input_label = $license->get_label( $attribute_name );
											$input_id = $license_input_id_prefix . $attribute_name;
										?>
										<div class="form-group <?php echo esc_attr( $license->has_errors( $attribute_name ) ? 'has-error' : '' ); ?>">
											<label for="<?php echo esc_attr( $input_id ); ?>">
												<?php echo esc_html( $input_label ); ?>
												<div class="ttls__label-info">
													<i class="fa fa-question-circle-o" aria-hidden="true"></i>
													<div class="ttls__label-info-hidden">
														<?php echo esc_html__('Amount of license days remaining - starting from the creation date', 'ttls_translate'); ?>
													</div>
												</div>
											</label>
											<div class="input-group">
												<input
													id="<?php echo esc_attr( $input_id ); ?>"
													name="<?php echo esc_attr( $input_name ); ?>"
													type="number"
													placeholder="<?php echo esc_attr( $input_label ); ?>"
													value="<?php echo esc_attr( $license->$attribute_name ); ?>"
													class="form-control"
													min="1"
												><span class="input-group-addon"><?php echo esc_html__('days', 'ttls_translate'); ?></span>
											</div>
											<?php $form_errors_helper->render( $attribute_name, $license ); ?>
										</div>
									</div>
								</div>
							</div>
						</div>
						<?php do_action( 'ttls_product_settings_add_section', $product ); ?>
						<div class="ttls__settings-inner-footer">
						<?php if ( $product->has_errors( '_global' ) ) { ?>
							<div class="has-error">
							<?php $form_errors_helper->render( '_global', $product ); ?>
							</div>
						<?php } else { ?>
							<div class="text-success"><?php echo empty( $data['status'] ) ? '' : esc_html__( 'Product settings successfully saved', 'ttls_translate' ); ?></div>
						<?php } ?>
							<button class="btn btn-dark"><?php echo esc_html__('Save', 'ttls_translate'); ?></button>
						</div>

					</form>

				</div>
			</div>
		</div>
	</div>

	<div class="ttls__alerts"></div>

	<div id="ttlsGenerator" tabindex="-1" role="dialog" class="modal fade">
		<div role="document" class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" data-bs-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
					<h4 class="modal-title"><?php echo esc_html__('Generate inclusion code', 'ttls_translate'); ?></h4>
				</div>
				<form class="form ttls-settings-generator">
					<input type="hidden" name="slug" value="<?php echo esc_html( $product->post_name ); ?>">
					<div class="modal-body">
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-code-fork"></i></span>
								<input name="server" type="text" value="<?php echo esc_url( get_site_url() ); ?>" placeholder="<?php echo esc_html__('Server', 'ttls_translate'); ?>" class="form-control">
							</div>
						</div>
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-comment"></i></span>
								<input name="description" type="text" value="<?php echo esc_attr( $product->post_content ); ?>" placeholder="<?php echo esc_html__('Description', 'ttls_translate'); ?>" class="form-control">
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-dark" data-copy_text="<?php echo esc_html__('Copy', 'ttls_translate'); ?>"><?php echo esc_html__('Generate', 'ttls_translate'); ?></button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>