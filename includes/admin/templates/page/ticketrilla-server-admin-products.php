<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}
$title = __( 'Supported Themes and Plugins', 'ttls_translate' );
$page_url = ttls_url( 'ticketrilla-server-product-settings' );
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
			<h1><?php echo esc_html( $title ); ?></h1><a href="<?php echo esc_url( add_query_arg( array('action' => 'add'), $page_url )); ?>" class="btn btn-info"><i></i><?php esc_html_e( 'Add product', 'ttls_translate' ); ?></a>
		</div>
		<?php
	    $filter_check = \TTLS\Models\Product::find_one();
			
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

		$condition = array();
		if ( $filter_key ) {
			$condition['meta_query'] = array(
				array(
					'key' => 'ttls_type',
					'value' => $filter_key,
				),
			);
		}
		$product_list = \TTLS\Models\Product::find_all( $condition );
    if ( empty( $product_list['items'] ) ) {
      echo '<p>' . esc_html__('No added products found', 'ttls_translate' ) . '</p>';
    } else {
  ?>

		<div class="ttls__cards">
			<?php
		    foreach ( $product_list['items'] as $product ) {
			    $product_url = add_query_arg( array('product_id' => $product->ID), $page_url );
			?>
			<article class="ttls__card plugin"><a href="<?php echo esc_url( $product_url ); ?>" title="<?php esc_html_e( 'Settings', 'ttls_translate' ); ?>" class="ttls__card-settings"><i class="fa fa-cog"></i></a>
				
				<div class="ttls__card-thumbnail">
					<a href="<?php echo esc_url( $product_url  ); ?>">
					<?php if ( $product->image ) { ?>
						<img src="<?php echo esc_url( wp_get_attachment_image_url( $product->image ) ); ?>" alt="<?php echo isset( $product->post_title ) ? esc_attr( $product->post_title ) : ''; ?>">
					<?php } else { ?>
						<span class="fa fa-image"></span>
					<?php } ?>
					</a><span class="badge label-danger"></span>
				</div>
				<div class="ttls__card-entry">
					<header class="ttls__card-header">
						<h3 class="ttls__card-title"><a href="<?php echo esc_url( $product_url ) ?>"><?php echo isset( $product->post_title ) ? esc_html( $product->post_title ) : ''; ?></a></h3>
					</header>
					<?php if ( isset( $product->post_content ) ) { ?>
					<div class="ttls__card-excerpt">
						<p><?php echo wp_kses_post( $product->post_content ); ?></p>
					</div>
					<?php } ?>
					<?php if ( $product->author_name ) { ?>
					<div class="ttls__card-authors">
						<cite>
						<?php
							esc_html_e( 'By', 'ttls_translate' );
						if ( ! empty( $product->author_link ) ) {								
						?> <a href="<?php echo esc_url( $product->author_link ); ?>"><?php echo esc_html( $product->author_name ); ?></a>
						<?php } else {
							echo ' ' . esc_html( $product->author_name );
						}
						?>
						</cite>
					</div>
					<?php } ?>
				</div>
			</article>
			<?php } ?>
		</div>

		<?php } ?>
		<hr>
	</div>

</div>