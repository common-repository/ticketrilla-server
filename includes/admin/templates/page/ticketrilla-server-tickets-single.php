</div>
<?php $ttls_attachments = new TTLS_Attachments; ?>
	<div class="ttls__content">
		<div class="ttls__tickets">
			<div class="row">
				<div class="col-md-4 pull-right-md">
					<div id="ttls-ticket-status"><?php echo wp_kses_post( TTLS\Helpers\TicketHTML::status_box( $main_ticket ) ); ?></div>
					<?php echo wp_kses_post( TTLS\Helpers\TicketHTML::client_box( $main_ticket ) ); ?>
				</div>

				<div class="col-md-8" id="ttls-tickets-inner">
					<div class="ttls__tickets-inner">
						<div class="ttls__tickets-ticket">
							<div class="ttls__tickets-ticket-header">
								<h4><?php echo '#'.esc_html( $main_ticket['id'].'. '.stripslashes($main_ticket['title']) ); ?></h4>
								<?php if ( current_user_can( 'ttls_clients' ) ) { ?>
									<a class="btn btn-default ttls-client-ticket-edit" href="#">
										<form>
											<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'ttls_client_edit_ticket' ); ?>">
											<input type="hidden" name="parent" value="<?php echo esc_attr( $main_ticket['id'] ); ?>">
											<input type="hidden" name="status" value="<?php echo esc_attr( $main_ticket['status'] == 'closed' ? 'reopen' : 'closed' ); ?>">
										</form>
										<i class="fa fa-cog"></i> 
										<span><?php echo esc_html( $main_ticket['status'] == 'closed' ? __( 'Open', 'ttls_translate' ) : __( 'Close', 'ttls_translate' ) ); ?></span>
									</a>
								<?php } ?>
								<hr>
							</div>
							<div class="ttls__tickets-ticket-body">
								<?php
								echo apply_filters('the_content', $main_ticket['content'] );
								if ( !empty( $main_ticket['ticket_attachments'] ) ) {
									echo '<hr>';
									echo '<h5>'.esc_html__('Attachment', 'ttls_translate').'</h5>';
									echo '<ul id="ttls_ticket_attachments" class="ttls__attachments clearfix">';
										foreach ( $main_ticket['ticket_attachments'] as $key => $att_id) {
											echo wp_kses_post( $ttls_attachments->print_single($att_id, true) );
										}
									echo '</ul>';
								}
								?>
							</div>
							<div class="ttls__tickets-ticket-footer">
								<hr><a href="#ttls__response" data-scroll id="addResponse" class="btn btn-dark"><?php echo esc_html__('Add response', 'ttls_translate'); ?></a>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="ttls__divider"></div>
			<div class="row">
				<div class="col-md-4 pull-right-md">
					<div class="ttls__allattachments">
						<div class="ttls__allattachments-header">
							<h4><?php echo esc_html__('All Attachments', 'ttls_translate'); echo ( empty( $main_ticket['attachment_list'] ) ) ? ' ( <span id="ttls_all_attachments_count">0</span> )' : ' ( <span id="ttls_all_attachments_count">'. count( $main_ticket['attachment_list'] ) . '</span> )'; ?></h4>
						</div>
						<div class="ttls__allattachments-body">

							<?php
								echo '<ul id="ttls_all_attachments" class="ttls__attachments clearfix">';
								if ( empty( $main_ticket['attachment_list'] ) ) {
									echo '<p>'.esc_html__('There are no attachments', 'ttls_translate').'</p>';
								} else {
									foreach ( $main_ticket['attachment_list'] as $key => $att_id) {
										echo wp_kses_post( $ttls_attachments->print_single($att_id, true) );
									}
								}
								echo '</ul>';
							 ?>
						</div>
					</div>
				</div>
				<div class="col-md-8">
					<?php if ( $main_ticket['response_count'] ) { ?>

						<?php if ( current_user_can( 'ttls_developers' ) && $main_ticket['response_count'] > 3 + $paged * 10 ) { ?>
					<div class="ttls__tickets-inner">
	                        <form action="#" class="ttls__tickets-form ttls-ticket-control">
	                        	<input type="hidden" name="ttls_ticket_id" value="<?php echo esc_attr( $main_ticket['id'] ); ?>">
	                            <h4><?php echo esc_html__('Ticket controls', 'ttls_translate'); ?></h4>
															<hr>
	                            <div class="row">
	                                <div class="col-md-6">
	                                    <div class="form-group">
	                                        <label><?php echo esc_html__('Change ticket status', 'ttls_translate'); ?></label>
	                                        <select name="ttls_status" class="ttls_license_select_type form-control">
	                                            <option value=""><?php echo esc_html__('Select new status', 'ttls_translate'); ?></option>
	                                            <option value="free"><?php echo esc_html__('Available ticket', 'ttls_translate'); ?></option>
	                                            <option value="pending"><?php echo esc_html__('Waiting for agent\'s response', 'ttls_translate'); ?></option>
	                                            <option value="replied"><?php echo esc_html__('Agent replied', 'ttls_translate'); ?></option>
	                                            <option value="closed" data-bs-target=".ttls__ticketClose"><?php echo esc_html__('Close ticket', 'ttls_translate'); ?></option>
	                                        </select>
	                                        <!-- span.help-block state-->
	                                    </div>
	                                </div>
	                                <div class="col-md-6">
	                                    <div class="form-group">
	                                        <label><?php echo esc_html__('Change agent', 'ttls_translate'); ?></label>
	                                        <select name="ttls_developer" class="form-control">
	                                            <option value=""><?php echo esc_html__('Select agent', 'ttls_translate'); ?></option>
																							<?php
																								$args = array('role__in' => array( 'ttls_developers', 'administrator'), );
																								$users = get_users( $args );
																								foreach ( $users as $key => $usr) { ?>
	                                            		<option value="<?php echo esc_attr( $usr->ID ); ?>"><?php echo esc_html( $usr->user_login ); ?></option>
	                                        			<?php } ?>
																						</select>
																						<!-- span.help-block state-->
	                                    </div>
	                                </div>
	                                <div class="col-md-12 collapse fade ttls__ticketClose">
	                                    <div class="form-group">
	                                        <label><?php echo esc_html__('Close ticket', 'ttls_translate'); ?></label>
	                                        <select name="ttls_close_reason" class="ttls_license_select_type form-control">
	                                            <option value="client_solved"><?php echo esc_html__('Issue resolved', 'ttls_translate'); ?></option>
	                                            <option value="client_cancel"><?php echo esc_html__('Client closed ticket', 'ttls_translate'); ?></option>
	                                            <option value="client_refund"><?php echo esc_html__('Client was refunded', 'ttls_translate'); ?></option>
	                                            <option value="" data-bs-target=".ttls__reason"><?php echo esc_html__('Other', 'ttls_translate'); ?></option>
	                                        </select>
	                                        <!-- span.help-block state-->
	                                    </div>
	                                    <div class="form-group collapse fade ttls__reason">
	                                        <input name="ttls_close_reason_text" type="text" placeholder="<?php echo esc_html__('Please specify the reason for closing this ticket', 'ttls_translate'); ?>" value="" class="form-control">
	                                    </div>
	                                </div>
	                                <div class="col-md-12 text-right">
																	<?php do_action( 'ttls_ticket_control_before_submit' ); ?>
	                                  <button type="submit" class="btn btn-dark"><?php echo esc_html__('Save', 'ttls_translate'); ?></button>
	                                </div>
	                            </div>
	                        </form>
	                    </div>
	                    <div class="ttls__divider"></div>
	          <?php } ?>

					<div class="ttls__tickets-controls ttls__tickets-controls-top">
						<div class="ttls__tickets-sort"><span><?php echo esc_html__('Sort', 'ttls_translate'); ?></span>
							<div title="" class="btn-group">
								<a href="<?php echo esc_url( add_query_arg( array( 't_paged' => 1, 'order' => 'ASC' ) ) ); ?>" title="<?php echo esc_attr__('old > recent', 'ttls_translate'); ?>" class="btn btn-default <?php echo esc_attr( ( $order == 'ASC') ? ' active' : '' ); ?>"><i class="fa fa-chevron-down"></i></a>
								<a href="<?php echo esc_url( add_query_arg( array( 't_paged' => 1, 'order' => 'DESC' ) ) ); ?>" title="<?php echo esc_attr__('recent > old', 'ttls_translate'); ?>" class="btn btn-default <?php echo esc_attr( ( $order == 'DESC') ? ' active' : '' ); ?>"><i class="fa fa-chevron-up"></i></a></div>
						</div>
					</div>

					<ul class="ttls__tickets-responses">
						<?php
						$time_label = 0;
						foreach ( $main_ticket['response_list'] as $key => $response) {
							$print_time_label = '';
							if ( $time_label ) {
								if ( $time_label != (new \DateTime($response['time']))->format('d-m-Y') ) {
									$time_label = (new \DateTime($response['time']))->format('d-m-Y');
									$print_time_label = '<li class="time-label"><span>'.esc_html( (new \DateTime($response['time']))->format('d-m-Y') ).'</span></li>';
								}
							} else {
								$time_label = (new \DateTime($response['time']))->format('d-m-Y');
								$print_time_label = '<li class="time-label"><span>'.esc_html( (new \DateTime($response['time']))->format('d-m-Y') ).'</span></li>';
							}
							echo wp_kses_post( $print_time_label );
							echo wp_kses_post( TTLS\Helpers\ResponseHTML::response_box( $response ) );
						} ?>
					</ul>

					<div class="ttls__tickets-controls">
						<a href="#ttls-container" data-scroll class="btn btn-default">&uarr; <?php echo esc_html__('Up', 'ttls_translate'); ?></a>

						<?php if ( $main_ticket['response_count'] > 10 ) {
							$max_pages = ceil( $main_ticket['response_count'] / 10 );
						?>
						<ul class="pagination">
							<?php for ( $i=1; $i <= $max_pages; $i++ ) { ?>
							<li <?php echo esc_html( ( $i == $paged ) ? 'class=active' : '' ); ?>><a href="<?php echo esc_url( add_query_arg( array( 't_paged' => $i ) ) ); ?>"><?php echo esc_html( $i ); ?></a></li>
							<?php } ?>
							<?php if ( $paged < $max_pages ) { ?>
							<li><a id="ttls_load_more_responses" class="btn btn-info" href="<?php echo esc_url( add_query_arg( array( 't_paged' => $paged+1 ) ) ); ?>"><?php echo esc_html__('Load more', 'ttls_translate'); ?></a></li>
							<?php } ?>
						</ul>
						<?php } ?>

						<div class="ttls__tickets-sort"><span><?php echo esc_html__('Sort', 'ttls_translate'); ?></span>
							<div title="" class="btn-group">
								<a href="<?php echo esc_url( add_query_arg( array( 't_paged' => 1, 'order' => 'ASC' ) ) ); ?>" title="<?php echo esc_attr__('old > recent', 'ttls_translate'); ?>" class="btn btn-default <?php echo esc_attr( ( $order == 'ASC') ? ' active' : '' ); ?>"><i class="fa fa-chevron-down"></i></a>
								<a href="<?php echo esc_url( add_query_arg( array( 't_paged' => 1, 'order' => 'DESC' ) ) ); ?>" title="<?php echo esc_attr__('recent > old', 'ttls_translate'); ?>" class="btn btn-default <?php echo esc_attr( ( $order == 'DESC') ? ' active' : '' ); ?>"><i class="fa fa-chevron-up"></i></a>
							</div>
						</div>
					</div>

					<?php } ?>
					<?php if ( current_user_can( 'ttls_developers' ) ) { ?>
					<div class="ttls__tickets-inner">
                        <form action="#" class="ttls__tickets-form ttls-ticket-control">
                        	<input type="hidden" name="ttls_ticket_id" value="<?php echo esc_attr( $main_ticket['id'] ); ?>">
                            <h4><?php echo esc_html__('Ticket controls', 'ttls_translate'); ?></h4>
							<hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><?php echo esc_html__('Change ticket status', 'ttls_translate'); ?></label>
                                        <select name="ttls_status" class="ttls_license_select_type form-control">
                                            <option value=""><?php echo esc_html__('Select new status', 'ttls_translate'); ?></option>
                                            <option value="free"><?php echo esc_html__('Available ticket', 'ttls_translate'); ?></option>
                                            <option value="pending"><?php echo esc_html__('Waiting for agent\'s response', 'ttls_translate'); ?></option>
                                            <option value="replied"><?php echo esc_html__('Agent replied', 'ttls_translate'); ?></option>
                                            <option value="closed" data-bs-target=".ttls__ticketClose"><?php echo esc_html__('Close ticket', 'ttls_translate'); ?></option>
                                        </select>
                                        <!-- span.help-block state-->
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><?php echo esc_html__('Change agent', 'ttls_translate'); ?></label>
                                        <select name="ttls_developer" class="form-control">
                                            <option value=""><?php echo esc_html__('Select agent', 'ttls_translate'); ?></option>
                                            <?php $args = array(
												'role__in'				 => array( 'ttls_developers', 'administrator'),
											);
											$users = get_users( $args );
											foreach ( $users as $key => $usr) { ?>
                                            <option value="<?php echo esc_attr( $usr->ID ); ?>"><?php echo esc_html( $usr->user_login ); ?></option>
                                        	<?php } ?>
                                        </select>
                                        <!-- span.help-block state-->
                                    </div>
                                </div>
                                <div class="col-md-12 collapse fade ttls__ticketClose">
                                    <div class="form-group">
                                        <label><?php echo esc_html__('Close ticket', 'ttls_translate'); ?></label>
                                        <select name="ttls_close_reason" class="ttls_license_select_type form-control">
                                            <option value="client_solved"><?php echo esc_html__('Issue resolved', 'ttls_translate'); ?></option>
                                            <option value="client_cancel"><?php echo esc_html__('Client closed ticket', 'ttls_translate'); ?></option>
                                            <option value="client_refund"><?php echo esc_html__('Client was refunded', 'ttls_translate'); ?></option>
                                            <option value="" data-bs-target=".ttls__reason"><?php echo esc_html__('Other', 'ttls_translate'); ?></option>
                                        </select>
                                        <!-- span.help-block state-->
                                    </div>
                                    <div class="form-group collapse fade ttls__reason">
                                        <input name="ttls_close_reason_text" type="text" placeholder="<?php echo esc_html__('Please specify the reason for closing this ticket', 'ttls_translate'); ?>" value="" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-12 text-right">
                                <?php do_action( 'ttls_ticket_control_before_submit' ); ?>
                                    <button type="submit" class="btn btn-dark"><?php echo esc_html__('Save', 'ttls_translate'); ?></button>
                                </div>
                            </div>
                        </form>
                    </div>
					<?php } ?>

					<div id="ttls__response" class="ttls__tickets-inner">
						<form action="#" class="ttls__tickets-form ttls-send-response">
							<input type="hidden" name="ttls_parent" value="<?php echo esc_attr( $main_ticket['id'] ); ?>">
							<h4><?php echo esc_html__('Add response', 'ttls_translate'); ?></h4>
							<div class="form-group">
								<textarea name="ttls_message" id="ttls-ckeditor" rows="10" placeholder="<?php echo esc_html__('Include a message', 'ttls_translate'); ?>" class="form-control"></textarea>
							</div>
							<div class="form-group">
							<?php echo TTLS\Helpers\TicketHTML::add_attachments_box( $main_ticket ); ?>
							</div>
							<div class="ttls__tickets-form-footer form-inline">
								<div class="form-group">
								<?php if ( current_user_can( 'ttls_developers' ) ) { ?>
									<label><?php echo esc_html__('After sending change ticket\'s status', 'ttls_translate'); ?></label>
									<select class="form-control" name="ttls_status">
										<option value="free" <?php if ( 'free' == get_option('ttls_after_reply_status', 'replied')) { echo 'selected'; } ?>><?php echo esc_html__('Available ticket', 'ttls_translate'); ?></option>
										<option value="pending" <?php if ( 'pending' == get_option('ttls_after_reply_status', 'replied')) { echo 'selected'; } ?>><?php echo esc_html__('Waiting for agent\'s response', 'ttls_translate'); ?></option>
										<option value="replied" <?php if ( 'replied' == get_option('ttls_after_reply_status', 'replied')) { echo 'selected'; } ?>><?php echo esc_html__('Agent replied', 'ttls_translate'); ?></option>
										<option value="closed" <?php if ( 'closed' == get_option('ttls_after_reply_status', 'replied')) { echo 'selected'; } ?>><?php echo esc_html__('Close ticket', 'ttls_translate'); ?></option>
									</select>
								<?php } ?>
								</div>
									<button type="submit" class="btn btn-dark"><?php echo esc_html__('Send response', 'ttls_translate'); ?></button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>