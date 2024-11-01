<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}
$title = __( 'Supported Themes and Plugins', 'ttls_translate' );
$client_id = get_current_user_id();
?>
<div id="ttls-container" class="ttls">
  <div class="ttls__header">
    <div class="ttls__header-inner">
      <div class="col-left">
        <h2 class="h4 wp-heading-inline"><?php echo esc_html__('Ticketrilla: Server', 'ttls_translate'); ?></h2>
      </div>
      <div class="col-right">
			<?php
				$breadcrumbs = new \TTLS\Helpers\Breadcrumbs( $title );
				$breadcrumbs->render();
		?>
      </div>
    </div>
    <hr class="clearfix">

		<div class="ttls__header-title">
			<h1><?php echo esc_html( $title ); ?></h1>
		</div>
		<?php
	    $filter_check = \TTLS\Models\License::find_one( array(
				'meta_key' => 'ttls_owners',
				'meta_value' => $client_id,
			) );
			
			$filter = new \TTLS\Helpers\ProductFilter( '' );

	    if ( ! empty( $filter_check['items'] ) ) {
				$filter->render();
			}
		?>
	</div>

	<div class="ttls__content">

  <?php
    
    $filter_key = false;
    
    if ( isset( $_GET['filter'] ) && array_key_exists( $_GET['filter'], $filter->get_data() ) ) {
      $filter_key = sanitize_text_field( $_GET['filter'] );
    }
		$filtered_products = \TTLS\Models\Product::find_all( array(
			'meta_query' => $filter_key ? array(
				array(
					'key' => 'ttls_type',
					'value' => $filter_key,
				),
			) : false,
		) );
		$filtered_products_ids = array_map( function( $product ) {
			return $product->ID;
		}, $filtered_products['items'] );

		$connected_list = empty( $filtered_products_ids ) ? array() : \TTLS\Models\License::find_all( array(
			'meta_query' => array(
				array(
					'key' => 'ttls_owners',
					'value' => get_current_user_id(),
				),
				array(
					'key' => 'ttls_product_id',
					'value' => $filtered_products_ids,
					'compare' => 'IN',
				),
			),
		) );

    if ( empty( $connected_list['items'] ) ) {
      echo '<p>' . esc_html__('No added products found', 'ttls_translate' ) . '</p>';
    } else {
  ?>

		<div class="ttls__cards">
			<?php
				$tickets_url = ttls_url();
		    foreach ( $connected_list['items'] as $connected_license ) {
			    $connected_license_url = ttls_url( 'ticketrilla-server-tickets', array('license_id' => $connected_license->ID) );
					$product = \TTLS\Models\Product::find_by_id( $connected_license->product_id );
					if ( ! $product ) {
						continue;
					}
					$product_thumbnail = $product->image;
					$product_title = $product->post_title;
					$product_description = $product->post_content;
					$product_author_url = $product->author_link;
					$product_author_name = $product->author_name;
					$product_manual = $product->manual;
					$uniq_id = uniqid();
					$replied_tickets_count = ttls_calculate_replied_tickets( $client_id, $connected_license->ID );
					$connected_license_sites = get_post_meta( $connected_license->ID, 'ttls_site_urls' );
			?>
			<article class="ttls__card plugin"><a href="#" title="<?php esc_html_e( 'Settings', 'ttls_translate' ); ?>" data-bs-toggle="modal" data-bs-target="#ttls-modal-product-<?php echo esc_attr( $uniq_id ); ?>" class="ttls__card-settings"><i class="fa fa-cog"></i></a>
				
				<div class="ttls__card-thumbnail">
					<a href="<?php echo esc_url( $connected_license_url ); ?>">
					<?php if ( $product_thumbnail ) { ?>
						<img src="<?php echo esc_url( wp_get_attachment_image_url( $product_thumbnail ) ); ?>" alt="<?php echo isset( $product_title ) ? esc_attr( $product_title ) : ''; ?>">
					<?php } else { ?>
						<span class="fa fa-image"></span>
					<?php } ?>
					</a><span class="badge label-danger"></span>
				</div>
				<div class="ttls__card-entry">
					<header class="ttls__card-header">
						<h3 class="ttls__card-title"><a href="<?php echo esc_url( $connected_license_url ) ?>"><?php echo isset( $product_title ) ? esc_html( $product_title ) : ''; ?></a></h3>
					</header>
					<?php if ( isset( $product_description ) ) { ?>
					<div class="ttls__card-excerpt">
						<p><?php echo wp_kses_post( $product_description ); ?></p>
					</div>
					<?php } ?>
					<?php if ( ! empty( $connected_license_sites[0] ) ) { ?>
					<div class="ttls__card-authors">
						<cite><?php echo esc_html( sprintf( __('Your website: %s', 'ttls_translate' ) , $connected_license_sites[0] ) ); ?></cite>
					</div>
					<?php } ?>
					<?php if ( ! empty( $connected_license->license_token ) ) { ?>
					<div class="ttls__card-authors">
						<cite><?php echo esc_html( sprintf( __('Your purchase code: %s', 'ttls_translate' ) , $connected_license->license_token ) ); ?></cite>
					</div>
					<?php } ?>
				</div>
	            <div class="ttls__card-footer">
		            <div class="ttls__card-footer-inner">
			        <?php
				    	if ( ! empty( $product_manual ) ) {
					?>
						<a target="_blank" href="<?php echo esc_url( $product_manual ); ?>" class="btn btn-dark ttls-product-manual"><?php echo esc_html__( 'Documentation', 'ttls_translate' ); ?></a>
					<?php } ?>
		            	<a href="<?php echo esc_url( $connected_license_url ) ?>" class="btn btn-default ttls__pending-tickets-product-count" data-license-id="<?php echo esc_attr( $connected_license->ID ); ?>"><i class="fa fa-file-archive"></i> <?php echo esc_html( 'View tickets', 'ttls_translate' ) . ttls_pending_count_html( $replied_tickets_count, 'tickets-' . $connected_license->ID ); ?></a>
		            </div>
	            </div>
			</article>
			<?php
				$this->render_template_page( 'ticketrilla-server-product-settings-modal', array(
					'uniq_id' => $uniq_id,
					'product' => $product,
					'client_product' => new \TTLS\Models\ClientProduct( array(
						'product_id' => $connected_license->product_id,
						'license_id' => $connected_license->ID,
						'license' => $connected_license->license_type,
						'license_token' => $connected_license->license_token,
						'newsletters' => $connected_license->newsletters,
						'terms' => true,
					) ),
				) );
			?>
			<?php } ?>
		</div>

		<?php } ?>
		<hr>
		<div class="ttls__available">
			<h3><?php esc_html_e( 'Add a new license for', 'ttls_translate' ); ?></h3>
      <?php
			$available_list = \TTLS\Models\Product::find_all_available( $filter_key );
			if ( empty( $available_list['items'] ) ) {
          echo '<p>' . esc_html__('No available for support products found', 'ttls_translate' ) . '</p>';
        } else { ?>

			<div class="ttls__available-inner">
				<?php
					$table_available = new \TTLS\Helpers\Table(
						'support-available',
						array(
							'title' => array(
								'label' => esc_html__( 'Product Name', 'ttls_translate' ),
								'value' => function( $data ) {
									$html = '<h4>' . esc_html( $data->post_title ) . '</h4>';
									if ( isset( $data->post_content ) ) {
										$html .= '<p>' . wp_kses_post( $data->post_content ) . '</p>';
									}
									return $html;
								}
							),
							'actions' => array(
								'label' => esc_html__( 'Actions', 'ttls_translate' ),
								'value' => function( $data ) {
									ob_start();
									$uniq_id = uniqid();
									?>
									<a href="#" class="btn btn-block btn-dark" data-bs-toggle="modal" data-bs-target="#ttls-modal-product-<?php echo esc_attr( $uniq_id ); ?>"><?php esc_html_e( 'Add','ttls_translate' ); ?></a>
									<?php
									$this->render_template_page( 'ticketrilla-server-product-settings-modal', array('uniq_id' => $uniq_id, 'product' => $data, 'client_product' => new \TTLS\Models\ClientProduct( array('product_id' => $data->ID) ) ) );
									return ob_get_clean();
								},
							),
						),
						$available_list['items']
					);
					$table_available->render( 'table table-striped' );
				?>

			</div>
		
		<?php } ?>
		</div>
	</div>

</div>