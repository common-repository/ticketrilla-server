<?php
	$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : false;

	if ( $action == 'add' && current_user_can( 'ttls_clients' ) ) {
				
		TTLS()->admin_page()->render_template_page( 'ticketrilla-server-add-ticket' );

	} else {

	$ticket_type = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 'all'; // free | pending | replied | closed
	$paged = isset( $_GET['t_paged'] ) ? sanitize_key( $_GET['t_paged'] ) : 1;
	$order = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ): 'ASC';
	$ticket_id = isset( $_GET['ticket_id'] ) ? sanitize_key( $_GET['ticket_id'] ): false;
	$ticket_types = array(
		'all' => __('All', 'ttls_translate'),
		'free' => __('Unassigned', 'ttls_translate'),
		'replied' => ( current_user_can( 'ttls_developers' ) ? __('Replied', 'ttls_translate') : __('Needs Attention', 'ttls_translate') ),
		'pending' => __('Pending', 'ttls_translate'),
		'closed' => __('Closed', 'ttls_translate'),
	);
	if ( $ticket_id ) {
		$main_ticket = TTLS()->ticket_service()->get_single( $ticket_id, $paged, $order );
		if ( current_user_can( 'ttls_clients' ) ) {
			if ( $main_ticket['client_id'] != get_current_user_id() ) {
				wp_die( __( 'Ticket not found.', 'ttls_translate' ) );
			}
		}
	}
?>

<div id="ttls-container" class="ttls">
  <div class="ttls__header">
    <div class="ttls__header-inner">
      <div class="col-left">
        <h2 class="h4 wp-heading-inline"><?php echo esc_html__('Ticketrilla: Server', 'ttls_translate'); ?></h2>
				<?php if ( current_user_can( 'ttls_developers' ) ) { ?>
				<a href="<?php echo esc_url( ttls_url( 'ticketrilla-server-general-settings' ) ); ?>" class="btn btn-xs btn-dark"><?php echo esc_html__('Settings', 'ttls_translate'); ?></a>
				<?php } ?>
      </div>
      <div class="col-right">
			<?php
				$breadcrumbs = new \TTLS\Helpers\Breadcrumbs();
				$breadcrumbs->add( array(
					'url' => ttls_url( 'ticketrilla-server-tickets' ),
					'title' => __('Tickets', 'ttls_translate'),
				) );
			?>

		<?php
			if ( $ticket_id ) {
				$breadcrumbs_title = is_wp_error( $main_ticket ) ? __('Error','ttls_translate') : '#' . esc_html( $main_ticket['id'] . '. ' . stripslashes( $main_ticket['title'] ) );
			} else {
				$breadcrumbs_title = $ticket_types[$ticket_type];
		} ?>
		<?php
				$breadcrumbs->add( array('title' => $breadcrumbs_title ) );
				$breadcrumbs->render();
		?>
      </div>
    </div>
    <hr class="clearfix">

  <?php if ( $ticket_id ) {
	  	if ( is_wp_error( $main_ticket ) ) {
	  		echo esc_html__( $main_ticket->get_error_message(), 'ttls_translate');
	  	} else {
	  		require_once 'ticketrilla-server-tickets-single.php';
	  	}
	} else {
		require_once 'ticketrilla-server-tickets-list.php';
	} ?>

	<div class="ttls__alerts"></div>

	<div id="ttlsLicense" tabindex="-1" role="dialog" class="modal fade">
		<div role="document" class="modal-dialog">
			<div class="modal-content">
				<form class="form">
					<p><?php echo esc_html__('Loading info...', 'ttls_translate'); ?></p>
				</form>
			</div>
		</div>
	</div>
</div>
<?php } ?>