<?php
$ticket = $data['ticket'];
$licenses = $data['licenses'];
?>
<form action="#" class="ttls__tickets-form ttls-send-ticket">
  <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'ttls_add_ticket' ); ?>">
  <div class="form-group <?php echo esc_attr( $ticket->has_errors( 'license' ) ? 'has-error' : '' ); ?>">
  <?php $selected_license = isset( $ticket->license ) ? $ticket->license : ( empty( $_GET['license_id'] ) ? '' : sanitize_key( $_GET['license_id'] ) ); ?>
    <select name="ttls_license_id" id="ttls-license_id" class="form-control" value="<?php echo esc_attr( $selected_license ); ?>">
      <option disabled value="" <?php echo empty( $selected_license ) ? 'selected' : ''; ?>><?php echo esc_html__( 'Select license', 'ttls_translate' ); ?></option>
   <?php foreach( $licenses as $license_id => $license_title ) { ?>
      <option value="<?php echo esc_attr( $license_id ); ?>" <?php echo esc_html( $selected_license == $license_id ? 'selected' : '' ); ?>><?php echo esc_html( $license_title ); ?></option>
    <?php } ?>
    </select>
  </div>
  <div class="form-group <?php echo esc_attr( $ticket->has_errors( 'post_title' ) ? 'has-error' : '' ); ?>">
    <input name="ttls_title" id="ttls-title" type="text" maxlength="256" class="form-control" placeholder="<?php esc_html_e( 'Ticket Title', 'ttls_translate' ); ?>" value="<?php echo isset( $ticket->post_title ) ? esc_attr( $ticket->post_title ) : ''; ?>">
  </div>
  <div class="form-group">
    <textarea name="ttls_message" id="ttls-ckeditor" rows="10" placeholder="<?php echo esc_html__('Include a message', 'ttls_translate'); ?>" class="form-control"><?php echo isset( $ticket->post_content ) ? wp_kses_post( $ticket->post_content ) : ''; ?></textarea>
  </div>
  <div class="form-group">
    <ul id="ttls-mailbox-attachments" class="ttls__attachments clearfix">
      <li class="ttls-attachment-template hidden">
        <span class="ttls__attachments-icon"><i class="fa fa-file"></i></span>
        <div class="ttls__attachments-info">
          <div class="ttls__attachments-name title"></div>
          <div class="ttls__attachments-size">
            <span class="size"></span>
            <a href="#" title="<?php esc_html_e( 'Delete this attachment', 'ttls_translate' ); ?>" class="ttls__attachments-delete ttls-ticket-attachment-delete btn btn-xs btn-danger"><i class="fa fa-trash"></i> <?php esc_html_e( 'Delete', 'ttls_translate' ); ?></a>
          </div>
        </div>
      </li>
      <li class="btn btn-file ticket-attachment-btn">
        <input id="ttls-add-ticket-attachment" type="file" name="ttls_attachment" multiple="">
        <label for="ttls-add-ticket-attachment"><span class="ttls__attachments-icon"><i class="fa fa-plus"></i></span>
          <div class="ttls__attachments-info"><span><?php echo esc_html__('Add attachment', 'ttls_translate'); ?></span></div>
        </label>
      </li>
    </ul>
    <p class="help-block"><?php echo esc_html__('Maximum size', 'ttls_translate'); ?> <?php echo esc_html( get_option('ttls_attachments_max_size', 5) ); ?> <?php echo esc_html__('MB', 'ttls_translate'); ?></p>
  </div>
  <div class="ttls__tickets-form-footer form-inline">
    <div class="form-group"></div>
    <button type="submit" class="btn btn-dark"><?php echo esc_html__('Send ticket', 'ttls_translate'); ?></button>
  </div>
</form>
