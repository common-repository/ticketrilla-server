<?php

namespace TTLS\Helpers;

use TTLS\Models\Ticket;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TicketHTML' ) ) {

  class TicketHTML extends HTML {
    public static function status_box( $data ) {
      $html = '<div class="ttls__status ' . esc_attr( $data['status'] ) . '" data-ticket="' . esc_attr( $data['id'] ) . '" data-status="' . esc_attr( $data['status'] ) . '" data-dev="' . esc_attr( $data['developer_id'] ) . '">';
        $html .= '<div class="ttls__status-inner">';
          $html .= '<span>' . esc_html( $data['developer'] ) . '</span>';
          if ( current_user_can( 'ttls_developers' ) ) {
            if ( ! empty( $data['developer_pos'] ) ) {
              $html .= '<span>' . esc_html( $data['developer_pos'] ) . '</span>';
            }
            $status = Ticket::get_status( $data['status'] );
          } else {
            $status = Ticket::get_client_status( $data['status'] );
          }
          if ( ! empty( $status ) ) {
            $html .= '<div class="ttls__status-badge label">' . esc_html( $status ) . '</div>';
          }
        $html .= '</div>';
      $html .= '</div>';
      return $html;
    }

    public static function client_box( $data ) {
      return '<div class="ttls__user" id="ttls-user">' . self::client_box_header( $data ) . self::client_box_body( $data ) . '</div>';
    }

    public static function client_box_header( $data ) {
      ob_start();
    ?>
      <div class="ttls__user-header">
      <?php
        $avatar = get_avatar( $data['client_email'], 128, '', '', '' );
        if ( $avatar ) {
      ?>
        <div class="ttls__user-avatar"><?php echo wp_kses_post( $avatar ); ?></div>
        <?php } ?>
        <div class="ttls__user-name">
          <span><?php echo esc_html( $data['client_name'] ); ?></span>
          <span><?php echo esc_html( $data['client'] ); ?></span>
        </div>
      </div>
    <?php
      return ob_get_clean();
    }

    private static function client_box_body( $data ) {
      ob_start();
      ?>
      <div class="ttls__user-body">
      <?php
        echo self::client_box_license( $data );
        if ( current_user_can( 'ttls_developers' ) ) {
          echo self::client_box_site_url( $data );
          do_action( 'ttls_single_ticket_after_user_data', $data );
        }
      ?>
      </div>
      <?php
      return ob_get_clean();
    }

    private static function client_box_license( $data ) {
      ob_start();
      if ( is_wp_error( $data['client_license'] ) ) {
    ?>
      <div class="ttls__user-license">
        <span><?php echo esc_html__( $data['client_license']->get_error_message(), 'ttls_translate'); ?></span>
      </div>
    <?php } else { ?>
      <?php if ( ! empty( $data['client_license_type'] ) ) { ?>
      <div class="ttls__user-license">
        <?php echo esc_html__('License type', 'ttls_translate'); ?>
        <?php if ( current_user_can( 'ttls_developers' ) ) { ?>
        <a href="#"
          data-license="<?php echo esc_attr( $data['client_license_id'] ); ?>"
          data-bs-toggle="modal"
          data-bs-target="#ttlsLicense"
          title="License settings"
          class="label label-primary ttls_edit_license_link"><?php echo esc_html( $data['client_license_type'] ); ?></a>
        <?php } else { ?>
        <span class="label label-primary"><?php echo esc_html( $data['client_license_type'] ); ?></span>
        <?php } ?>
      </div>
      <?php } ?>
      <div class="ttls__user-support">
      <?php if ( $data['client_license_have_support'] ) { ?>
        <span><?php echo esc_html__('Support until', 'ttls_translate'); ?>: <?php echo esc_html( $data['client_license_have_support_until'] ); ?></span>
      <?php } else { ?>
        <span><?php echo esc_html__('No support', 'ttls_translate'); ?></span>
      <?php } ?>
      <?php if ( current_user_can( 'ttls_clients' ) && ! empty( $data['client_license_support_link'] ) ) { ?>
        <a href="<?php echo esc_url( $data['client_license_support_link'] ); ?>" target="_blank" class="btn btn-info"><?php esc_html_e( 'Extend support', 'ttls_translate' ); ?></a>
      <?php } ?>
      </div>
    <?php } ?>
      <?php
      return ob_get_clean();
    }

    private static function client_box_site_url( $data ) {
      ob_start();
      if ( ! empty( $data['client_site_url'] ) ) {
      ?>
      <div class="ttls__user-support">
        <span><?php echo esc_html__('Site URL', 'ttls_translate'); ?></span>
        <a href="<?php echo esc_url( $data['client_site_url'] ); ?>" target="blank_"><?php echo esc_html( $data['client_site_url'] ); ?></a>
      </div>
      <?php } ?>
      <?php
      return ob_get_clean();
    }

    public static function add_attachments_box( $data ) {
      $ttls_attachments = new \TTLS_Attachments;
      ob_start();
      ?>
      <ul id="ttls-mailbox-attachments" class="ttls__attachments clearfix">
      <?php if ( ! is_wp_error( $data['attachment_templist'] ) ) {
        foreach ( $data['attachment_templist'] as $temp_attach ) {
          echo wp_kses_post( $ttls_attachments->print_single( $temp_attach, true ) );
        }
      } ?>
      <li class="btn btn-file ticket-attachment-btn">
        <input data-textdownload="<?php echo esc_html__('Loading...', 'ttls_translate'); ?>" data-ticket="<?php echo esc_attr( $data['id'] ); ?>" id="ttls-ticket-attachment" type="file" name="ttls-ticket-attachment" multiple="">
        <label for="ttls-ticket-attachment"><span class="ttls__attachments-icon"><i class="fa fa-plus"></i></span>
          <div class="ttls__attachments-info"><span><?php echo esc_html__('Add attachment', 'ttls_translate'); ?></span></div>
        </label>
      </li>
    </ul>
    <p class="help-block"><?php echo esc_html__('Maximum size', 'ttls_translate'); ?> <?php echo esc_html( get_option('ttls_attachments_max_size', 5) ); ?> <?php echo esc_html__('MB', 'ttls_translate'); ?></p>
    <?php
    return ob_get_clean();
    }
  }
}