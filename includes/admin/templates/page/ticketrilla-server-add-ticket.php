<?php

$client_data = TTLS()->ticket_service()->prepare_ticket_client_data( get_current_user_id() );
$ticket = new TTLS\Models\Ticket();
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
					'url' => ttls_url( 'ticketrilla-server-tickets' ),
					'title' => __('Tickets', 'ttls_translate'),
        ) );
        $breadcrumbs->add( __('Add ticket', 'ttls_translate') );
				$breadcrumbs->render();
			?>
      </div>
    </div>
    <hr class="clearfix">
    <div class="ttls__header-title">
				<h1><?php echo esc_html__( 'Add New Ticket', 'ttls_translate' ); ?></h1>
		</div>
  </div>
  <div class="ttls__content">
		<div class="ttls__tickets">
			<div class="row">
				<div class="col-md-4 pull-right-md">
					<?php echo TTLS\Helpers\TicketHTML::client_box_header( $client_data ); ?>
				</div>
				<div class="col-md-8">
          <div id="ttls__ticket" class="ttls__tickets-inner">
            <?php TTLS()->admin_page()->render_template_page( 'ticketrilla-server-add-ticket-form', array('ticket' => $ticket, 'licenses' => \TTLS_License::get_client_licenses_titles() ) ); ?>
          </div>
				</div>
			</div>
		</div>
	</div>
  <div class="ttls__alerts"></div>
</div>