<div id="ttls-container" class="ttls">
	<div class="ttls__header">
		<div class="ttls__header-inner">
			<div class="col-left">
				<h2 class="h4 wp-heading-inline"><?php echo esc_html__('Ticketrilla: Server', 'ttls_translate'); ?></h2>
			</div>
			<div class="col-right">
			<?php
				$breadcrumbs = new \TTLS\Helpers\Breadcrumbs( __('Settings', 'ttls_translate') );
				$breadcrumbs->render();
			?>
			</div>
		</div>
		<hr class="clearfix">
	</div>
	<div class="ttls__content">
		<div class="ttls__settings">
			<div class="row">
				<div class="col-md-4 pull-right-md">
					<div id="ttls__settings-menu" class="ttls__settings-inner">
						<div class="ttls__settings-inner-header">
							<h4><?php echo esc_html__('Settings menu', 'ttls_translate'); ?></h4>
						</div>
						<div class="ttls__settings-inner-body">
							<ul class="nav ttls__settings-menu">
								<li><a href="#ttls__profile" data-scroll><?php echo esc_html__('Profile', 'ttls_translate'); ?></a></li>
								<li><a href="#ttls__ticket" data-scroll><?php echo esc_html__('Tickets', 'ttls_translate'); ?></a></li>
								<li><a href="#ttls__notifications" data-scroll><?php echo esc_html__('Notifications', 'ttls_translate'); ?></a></li>
								<li><a href="#ttls__attachment" data-scroll><?php echo esc_html__('Attachments', 'ttls_translate'); ?></a></li>
								<li><a href="#ttls__password" data-scroll><?php echo esc_html__('Reset password', 'ttls_translate'); ?></a></li>
								<?php do_action( 'ttls_settings_add_box_menu' ); ?>
							</ul>

						</div>
					</div>
					<div class="ttls__divider"></div>
				</div>
				<div class="col-md-8">

					<form id="ttls__profile" class="ttls__settings-inner ttls-setting-form ttls-profile-form"  method="post" action="">
						<?php $this_user = (new TTLS_Users)->get_user( get_current_user_id() ); ?>
						<div class="ttls__settings-inner-header">
							<h4><?php echo esc_html__('Your profile', 'ttls_translate'); ?></h4>
						</div>
						<div class="ttls__settings-inner-body">
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label>
											<?php echo esc_html__('Name', 'ttls_translate'); ?>
											<div class="ttls__label-info">
												<i class="fa fa-question-circle-o" aria-hidden="true"></i>
												<div class="ttls__label-info-hidden">
													<?php echo esc_html__('Displayed name', 'ttls_translate'); ?>
												</div>
											</div>
										</label>
										<div class="input-group">
											<span class="input-group-addon"><i class="fa fa-address-card"></i></span>
											<input name="first_name" type="text" value="<?php echo esc_attr( $this_user['meta_first_name'][0] ); ?>" class="form-control">
										</div>
									</div>
									<div class="form-group">
										<label>
											<?php echo esc_html__('Password', 'ttls_translate'); ?>
											<div class="ttls__label-info">
												<i class="fa fa-question-circle-o" aria-hidden="true"></i>
												<div class="ttls__label-info-hidden">
													<?php echo esc_html__('You can change your password', 'ttls_translate'); ?>
												</div>
											</div>
										</label>
										<div class="input-group">
											<span class="input-group-addon"><i class="fa fa-lock"></i></span>
											<input
												id="ttls__edit-dev-password"
												data-password="<?php echo wp_generate_password(); ?>"
												type="text"
												placeholder="<?php echo esc_html__('Password', 'ttls_translate'); ?>"
												value="<?php echo esc_html__('Password', 'ttls_translate'); ?>"
												name="ttls_user_password"
												class="form-control"
												disabled="disabled"
											>
											<span class="input-group-btn">
												<div
													data-change="<?php echo esc_html__('Change password', 'ttls_translate') ?>"
													data-cancel="<?php echo esc_html__('Don\'t change', 'ttls_translate') ?>"
													class="btn btn-info ttls_activate_password_changing"><?php echo esc_html__('Change password', 'ttls_translate') ?></div>
											</span>
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label>
											<?php echo esc_html__('Email', 'ttls_translate'); ?>
											<div class="ttls__label-info">
												<i class="fa fa-question-circle-o" aria-hidden="true"></i>
												<div class="ttls__label-info-hidden">
													<?php echo esc_html__('You can change your email', 'ttls_translate'); ?>
												</div>
											</div>
										</label>
										<div class="input-group">
											<span class="input-group-addon"><i class="fa fa-address-card"></i></span>
											<input name="email" type="e-mail" value="<?php echo esc_attr( $this_user['email'] ); ?>" class="form-control">
										</div>
									</div>
									<div class="form-group">
										<label>
											<?php echo esc_html__('Position', 'ttls_translate'); ?>
											<div class="ttls__label-info">
												<i class="fa fa-question-circle-o" aria-hidden="true"></i>
												<div class="ttls__label-info-hidden">
													<?php echo esc_html__('You can change your position', 'ttls_translate'); ?>
												</div>
											</div>
										</label>
										<div class="input-group">
											<span class="input-group-addon"><i class="fa fa-address-card"></i></span>
											<input name="nickname" type="text" value="<?php echo esc_attr( $this_user['meta_nickname'][0] ); ?>" class="form-control">
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="ttls__settings-inner-footer">
							<span class="text-muted"><?php echo esc_html__('There were no changes', 'ttls_translate'); ?></span>
							<button class="btn btn-dark"><?php echo esc_html__('Save', 'ttls_translate'); ?></button>
						</div>
					</form>

					<form action="#" id="ttls__ticket" class="ttls__settings-inner ttls-setting-form"  method="post" action="">
						<div class="ttls__settings-inner-header">
							<h4><?php echo esc_html__('Ticket settings', 'ttls_translate'); ?></h4>
						</div>
						<div class="ttls__settings-inner-body">
							<div class="row">
								<div class="col-md-6">

									<div class="form-group">
										<label>
											<?php echo esc_html__('Time limit for adding new tickets', 'ttls_translate'); ?>
											<div class="ttls__label-info">
												<i class="fa fa-question-circle-o" aria-hidden="true"></i>
												<div class="ttls__label-info-hidden">
													<?php echo esc_html__('How ofter are user\'s allowed to open new tickets', 'ttls_translate'); ?><br>
													<?php echo esc_html__('0 - no limit', 'ttls_translate'); ?>
												</div>
											</div>
										</label>
										<div class="input-group">
											<input
												id="ttls__ticketSettings-ticket-time"
												data-name="ttls__ticketSettings-ticket-time"
												name="ttls_clients_add_ticket_time"
												type="number"
												value="<?php echo esc_attr( get_option('ttls_clients_add_ticket_time', 0) ); ?>"
												class="form-control"
												>
											<span class="input-group-addon"><?php echo esc_html__('min', 'ttls_translate'); ?></span>
										</div>
									</div>

									<div class="form-group">
										<label>
											<?php echo esc_html__('Default ticket status for responses', 'ttls_translate'); ?>
											<div class="ttls__label-info">
												<i class="fa fa-question-circle-o" aria-hidden="true"></i>
												<div class="ttls__label-info-hidden">
													<?php echo esc_html__('While adding a new response, a ticket status could be set by default', 'ttls_translate'); ?>
												</div>
											</div>
										</label>
										<select class="form-control" name="ttls_after_reply_status">
											<?php
												$options = TTLS\Models\Ticket::get_statuses();
												$ttls_after_reply_status = get_option('ttls_after_reply_status', 'replied');
												foreach ( $options as $key => $value) {
													echo '<option value="'.esc_attr( $key ).'"';
													echo esc_html( $key == $ttls_after_reply_status ? ' selected' : '' );
													echo '>'.esc_html( $value ).'</option>';
												}
											 ?>
										</select>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<div class="checkbox">
											<input type="hidden" name="ttls_autoclose_ticket" value="">
											<input
												id="ttls__ticketSettings-autoclose"
												type="checkbox"
												name="ttls_autoclose_ticket"
												data-name="ttls__ticketSettings-autoclose"
												value="true"
												<?php echo ( get_option('ttls_autoclose_ticket', false) ) ? 'checked' : ''; ?>
											>
											<label for="ttls__ticketSettings-autoclose">
												<?php echo esc_html__('Automatic ticket closure', 'ttls_translate'); ?>
												<div class="ttls__label-info">
													<i class="fa fa-question-circle-o" aria-hidden="true"></i>
													<div class="ttls__label-info-hidden">
														<?php echo esc_html__('If enabled - the ticket will be automatically closed, when it doesn\'t receives a response from client', 'ttls_translate'); ?>
													</div>
												</div>
											</label>
										</div>
									</div>
									<div class="form-group">
										<label>
											<?php echo esc_html__('Automatic ticket closure - time limit', 'ttls_translate'); ?>
											<div class="ttls__label-info">
												<i class="fa fa-question-circle-o" aria-hidden="true"></i>
												<div class="ttls__label-info-hidden">
													<?php echo esc_html__('Time period for automatically closing tickets', 'ttls_translate'); ?>
												</div>
											</div>
										</label>
										<select class="form-control" name="ttls_autoclose_ticket_time">
											<?php
												$options = array(
													'hourly' => esc_html__( 'Every hour', 'ttls_translate' ),
													'twicedaily' => esc_html__( 'Twice a day', 'ttls_translate' ),
													'daily' => esc_html__( 'Every day', 'ttls_translate' ),
												);
												$ttls_autoclose_ticket_time = get_option('ttls_autoclose_ticket_time', 'hourly' );

												foreach ( $options as $key => $value ) {
													echo '<option value="'.esc_attr( $key ).'"';
													echo esc_html( $key == $ttls_autoclose_ticket_time ? ' selected' : '' );
													echo '>'.esc_html( $value ).'</option>';
												}
											 ?>
										</select>
									</div>
									<div class="form-group">
										<label>
											<?php echo esc_html__('Maximum period for closing tickets which didn\'t receive a response from client', 'ttls_translate'); ?>
											<div class="ttls__label-info">
												<i class="fa fa-question-circle-o" aria-hidden="true"></i>
												<div class="ttls__label-info-hidden">
													<?php echo esc_html__('After the pre-set period of time, inactive tickets will be closed automatically', 'ttls_translate'); ?>
												</div>
											</div>
										</label>
										<div class="input-group">
											<input
												id="ttls__ticketSettings-time-waiting"
												data-name="ttls__ticketSettings-time-waiting"
												name="ttls_autoclose_ticket_time_waiting"
												type="number"
												value="<?php echo esc_attr( get_option('ttls_autoclose_ticket_time_waiting', 7) ); ?>"
												class="form-control"
											>
											<span class="input-group-addon"><?php echo esc_html__('days', 'ttls_translate'); ?></span>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="ttls__settings-inner-footer">
							<span class="text-muted"><?php echo esc_html__('There were no changes', 'ttls_translate'); ?></span>
							<button class="btn btn-dark"><?php echo esc_html__('Save', 'ttls_translate'); ?></button>
						</div>
					</form>

					<form action="#" id="ttls__notifications" class="ttls__settings-inner ttls-setting-form">
						<div class="ttls__settings-inner-header">
							<h4><?php echo esc_html__('New events notifications', 'ttls_translate'); ?></h4>
						</div>
						<div class="ttls__settings-inner-body">
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<div class="checkbox">
										<?php
											$input_id = 'ttls__notificationsSettings-telegram-enable';
											$input_name = 'ttls_notifications_telegram_enable';
										?>
											<input type="hidden" name="<?php echo esc_attr( $input_name ); ?>" value="">
											<input
												id="<?php echo esc_attr( $input_id ); ?>"
												data-name="<?php echo esc_attr( $input_id ); ?>"
												name="<?php echo esc_attr( $input_name ); ?>"
												type="checkbox"
												value="true"
												class="form-control"
												<?php echo get_option($input_name, false) ? 'checked' : ''; ?>
											>
										<label for="<?php echo esc_attr( $input_id ); ?>">
											<?php echo esc_html__('Enable for Telegram', 'ttls_translate'); ?>
											<div class="ttls__label-info">
												<i class="fa fa-question-circle-o" aria-hidden="true"></i>
												<div class="ttls__label-info-hidden">
													<?php echo esc_html__('If enabled then you get a message about new events on your Telegram', 'ttls_translate'); ?>
												</div>
											</div>
										</label>
											
										</div>
									</div>
								</div>
								<div class="col-md-12">
									<div class="form-group">
										<?php
											$input_id = 'ttls__notificationsSettings-telegram-token';
											$input_name = 'ttls_notifications_telegram_token';
										?>
										<label for="<?php echo esc_attr( $input_id ); ?>">
											<?php echo esc_html__('Telegram token', 'ttls_translate'); ?>
											<div class="ttls__label-info">
												<i class="fa fa-question-circle-o" aria-hidden="true"></i>
												<div class="ttls__label-info-hidden">
												<?php echo esc_html__('For getting a token you must send for @BotFather next commands:', 'ttls_translate'); ?>
													<ol>
														<li>/newbot</li>
														<li><?php echo esc_html__( 'name for your bot', 'ttls_translate' ); ?></li>
														<li><?php echo esc_html__( "username for your bot with end in 'bot'", 'ttls_translate' ); ?></li>
													</ol>
												<?php echo esc_html__('You get a token.', 'ttls_translate'); ?>
												</div>
											</div>
										</label>
										<div class="input-group">
											<span class="input-group-addon"><i class="fa fa-key"></i></span>
											<input
												id="<?php echo esc_attr( $input_id ); ?>"
												data-name="<?php echo esc_attr( $input_id ); ?>"
												name="<?php echo esc_attr( $input_name ); ?>"
												type="text"
												value="<?php echo esc_attr( get_option( $input_name, '' ) ); ?>"
												class="form-control"
												>
										</div>
									</div>
								</div>
								<div class="col-md-12">
									<div class="form-group">
										<?php
											$input_id = 'ttls__notificationsSettings-telegram-chatid';
											$input_name = 'ttls_notifications_telegram_chat_id';
										?>
										<label for="<?php echo esc_attr( $input_id ); ?>">
											<?php echo esc_html__('Chat ID', 'ttls_translate'); ?>
											<div class="ttls__label-info">
												<i class="fa fa-question-circle-o" aria-hidden="true"></i>
												<div class="ttls__label-info-hidden">
												<?php echo esc_html__('For getting a Chat ID you have to:', 'ttls_translate'); ?>
													<ol>
														<li><?php echo esc_html__( 'create bot and to get a token', 'ttls_translate' ); ?></li>
														<li><?php echo esc_html__( 'create new group and to add your bot in it', 'ttls_translate' ); ?></li>
														<li><?php echo esc_html__( 'add bot @MyChatInfoBot on this group', 'ttls_translate' ) ?></li>
														<li><?php echo esc_html__( '@MyChatInfoBot will give Chat ID and will leave the group', 'ttls_translate' ) ?></li>
														<li><?php echo esc_html__( 'Chat ID may be with minus ("-"). You sholud copy Chat ID with it', 'ttls_translate' ) ?></li>
													</ol>
												<?php echo esc_html__('You get a Chat ID.', 'ttls_translate'); ?>
												</div>
											</div>
										</label>
										<div class="input-group">
											<span class="input-group-addon"><i class="fa fa-key"></i></span>
											<input
												id="<?php echo esc_attr( $input_id ); ?>"
												data-name="<?php echo esc_attr( $input_id ); ?>"
												name="<?php echo esc_attr( $input_name ); ?>"
												type="text"
												value="<?php echo esc_attr( get_option( $input_name, '' ) ); ?>"
												class="form-control"
												>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="ttls__settings-inner-footer">
							<span class="text-muted"><?php echo esc_html__('There were no changes', 'ttls_translate'); ?></span>
							<button class="btn btn-dark"><?php echo esc_html__('Save', 'ttls_translate'); ?></button>
						</div>
					</form>

					<form action="#" id="ttls__attachment" class="ttls__settings-inner ttls-setting-form">
						<div class="ttls__settings-inner-header">
							<h4><?php echo esc_html__('Attachments settings', 'ttls_translate'); ?></h4>
						</div>
						<div class="ttls__settings-inner-body">
							<div class="row">
								<div class="col-md-6">
									<?php do_action( 'ttls_show_htaccess_generator'); ?>
									<div class="form-group">
										<label>
											<?php echo esc_html__('Maximum file size', 'ttls_translate'); ?>
											<div class="ttls__label-info">
												<i class="fa fa-question-circle-o" aria-hidden="true"></i>
												<div class="ttls__label-info-hidden">
													<?php echo esc_html__('When the maximum file size is exceeded, the files will be linked from the client\'s site', 'ttls_translate'); ?>
												</div>
											</div>
										</label>
										<div class="input-group">
											<input
												id="ttls__attachment-max-size"
												data-name="ttls__attachment-max-size"
												name="ttls_attachments_max_size"
												type="number"
												value="<?php echo esc_attr( get_option('ttls_attachments_max_size', 5) ); ?>"
												class="form-control">
											<span class="input-group-addon"><?php echo esc_html__('MB', 'ttls_translate'); ?></span>
										</div>
									</div>
									<div class="form-group">
										<div class="checkbox">
											<input type="hidden" name="ttls_attachments_autoload" value="">
											<input
												id="ttls__attachment-autoload"
												type="checkbox"
												name="ttls_attachments_autoload"
												data-name="ttls__attachment-autoload"
												<?php echo ( get_option('ttls_attachments_autoload', false) ) ? 'checked' : ''; ?>
											>
											<label for="ttls__attachment-autoload">
												<?php echo esc_html__('Auto-load attachments', 'ttls_translate'); ?>
												<div class="ttls__label-info">
													<i class="fa fa-question-circle-o" aria-hidden="true"></i>
													<div class="ttls__label-info-hidden">
														<?php echo esc_html__('When enabled - a copy of the attachments will be automatically loaded to the developer\'s server', 'ttls_translate'); ?>
													</div>
												</div>
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label>
											<?php echo esc_html__('Maximum upload timeout', 'ttls_translate'); ?>
											<div class="ttls__label-info">
												<i class="fa fa-question-circle-o" aria-hidden="true"></i>
												<div class="ttls__label-info-hidden">
													<?php echo esc_html__('A time limitation for loading attachments to the server', 'ttls_translate'); ?>
												</div>
											</div>
										</label>
										<div class="input-group">
											<input
												id="ttls__attachment-max-time"
												data-name="ttls__attachment-max-time"
												name="ttls_attachments_max_time"
												type="number"
												value="<?php echo esc_attr( get_option('ttls_attachments_max_time', 30) ); ?>"
												class="form-control">
												<span class="input-group-addon"><?php echo esc_html__('sec.', 'ttls_translate'); ?></span>
										</div>
									</div>
									<div class="form-group">
										<div class="checkbox">
											<input type="hidden" name="ttls_attachments_autoload_license" value="">
											<input
												id="ttls__attachment-autoloading"
												type="checkbox"
												name="ttls_attachments_autoload_license"
												data-name="ttls__attachment-autoloading"
												<?php echo ( get_option('ttls_attachments_autoload_license', false) ) ? 'checked' : ''; ?>
											>
											<label for="ttls__attachment-autoloading">
												<?php echo esc_html__('Support subscription requirement for attachments', 'ttls_translate'); ?>
												<div class="ttls__label-info">
													<i class="fa fa-question-circle-o" aria-hidden="true"></i>
													<div class="ttls__label-info-hidden">
														<?php echo esc_html__('When enabled - only the clients with active support subscription will be able to upload attachments', 'ttls_translate'); ?>
													</div>
												</div>
											</label>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="ttls__settings-inner-footer">
							<span class="text-muted"><?php echo esc_html__('There were no changes', 'ttls_translate'); ?></span>
							<button class="btn btn-dark"><?php echo esc_html__('Save', 'ttls_translate'); ?></button>
						</div>
					</form>
					<form action="#" id="ttls__password" class="ttls__settings-inner ttls-setting-form">
						<div class="ttls__settings-inner-header">
							<h4><?php echo esc_html__('Password reset via email', 'ttls_translate'); ?></h4>
						</div>
						<div class="ttls__settings-inner-body">
							<div class="row">
								<div class="col-md-12">
									<h4><?php echo esc_html__('Restoring access email', 'ttls_translate'); ?></h4>
									<div class="form-group">
										<label>
											<?php echo esc_html__('Email title', 'ttls_translate'); ?>
											<div class="ttls__label-info">
												<i class="fa fa-question-circle-o" aria-hidden="true"></i>
												<div class="ttls__label-info-hidden">
													<?php echo esc_html__('Email variables', 'ttls_translate'); ?>
													<ul>
														<li><code>%secure_code%</code> - <?php echo esc_html__('secure code', 'ttls_translate'); ?></li>
														<li><code>%login%</code> - <?php echo esc_html__('Username', 'ttls_translate'); ?></li>
														<li><code>%password%</code> - <?php echo esc_html__('password', 'ttls_translate'); ?></li>
													</ul>
												</div>
											</div>
										</label>
										<input
											name="ttls_password_reset_code_title"
											type="text"
											value="<?php echo esc_attr( get_option('ttls_password_reset_code_title', 'Restoring access for support server of: "[server name]"') ); ?>"
											class="form-control"
											>
									</div>
									<div class="form-group">
										<label>
											<?php echo esc_html__('Email text', 'ttls_translate'); ?>
											<div class="ttls__label-info">
												<i class="fa fa-question-circle-o" aria-hidden="true"></i>
												<div class="ttls__label-info-hidden">
													<?php echo esc_html__('Email variables', 'ttls_translate'); ?>
													<ul>
														<li><code>%secure_code%</code> - <?php echo esc_html__('secure code', 'ttls_translate'); ?></li>
														<li><code>%login%</code> - <?php echo esc_html__('Username', 'ttls_translate'); ?></li>
														<li><code>%password%</code> - <?php echo esc_html__('password', 'ttls_translate'); ?></li>
													</ul>
												</div>
											</div>
										</label>
										<?php
										$standart_message = "Hello! If you or someone on your team requested resoring the support access of: \"[server name]\".\r\nThis link will be available for the following 24 hours. \r\nYour secure code is - %secure_code%.\r\nIf you did not request this (or were able to access the system successfully since), please ignore this email and continue to use your old login credentials.\r\n\r\nWe highly apreciate your support.  -The developer team of: \"[server name]\"";;
										$message = get_option( 'ttls_password_reset_code_message', $standart_message );
										?>
										<textarea class="form-control" name="ttls_password_reset_code_message" rows="10"><?php echo esc_textarea( $message ); ?></textarea>
									</div>
									<h4><?php echo esc_html__('Password reset via email', 'ttls_translate'); ?></h4>
									<div class="form-group">
										<label>
											<?php echo esc_html__('Email title', 'ttls_translate'); ?>
											<div class="ttls__label-info">
												<i class="fa fa-question-circle-o" aria-hidden="true"></i>
												<div class="ttls__label-info-hidden">
													<?php echo esc_html__('Email variables', 'ttls_translate'); ?>
													<ul>
														<li><code>%secure_code%</code> - <?php echo esc_html__('secure code', 'ttls_translate'); ?></li>
														<li><code>%login%</code> - <?php echo esc_html__('Username', 'ttls_translate'); ?></li>
														<li><code>%password%</code> - <?php echo esc_html__('password', 'ttls_translate'); ?></li>
													</ul>
												</div>
											</div>
										</label>
										<input
											name="ttls_password_reset_title"
											type="text"
											value="<?php echo esc_attr( get_option('ttls_password_reset_title', 'Restoring access for support server of: "[server name]"') ); ?>"
											class="form-control"
										>
									</div>

									<div class="form-group">
										<label>
											<?php echo esc_html__('Email text', 'ttls_translate'); ?>
											<div class="ttls__label-info">
												<i class="fa fa-question-circle-o" aria-hidden="true"></i>
												<div class="ttls__label-info-hidden">
													<?php echo esc_html__('Email variables', 'ttls_translate'); ?>
													<ul>
														<li><code>%secure_code%</code> - <?php echo esc_html__('secure code', 'ttls_translate'); ?></li>
														<li><code>%login%</code> - <?php echo esc_html__('Username', 'ttls_translate'); ?></li>
														<li><code>%password%</code> - <?php echo esc_html__('password', 'ttls_translate'); ?></li>
													</ul>
												</div>
											</div>
										</label>
										<?php
										$standart_message = "Your password for accessing the support server of: \"[server name]\" has been changed.\r\nYour new password is: %password%.\r\nWhile restoring the password if you set the option of auto password update, then the password already has been updated. Otherwise you will need to update it manually. \r\n\r\nWe appreciate your support! -The developer team of: \"[server name]\"";
										$message = get_option( 'ttls_password_reset_message', $standart_message );
										?>
										<textarea cols="30" rows="10" class="form-control" name="ttls_password_reset_message" ><?php echo esc_textarea( $message ); ?></textarea>
									</div>
								</div>
							</div>
						</div>
						<div class="ttls__settings-inner-footer">
							<span class="text-muted"><?php echo esc_html__('There were no changes', 'ttls_translate'); ?></span>
							<button class="btn btn-dark"><?php echo esc_html__('Save', 'ttls_translate'); ?></button>
						</div>
					</form>					
					<?php do_action( 'ttls_settings_add_box' ); ?>
				</div>
			</div>
		</div>
	</div>

	<div class="ttls__alerts"></div>

</div>