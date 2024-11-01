<?php
$product_id = empty( $_GET['product_id'] ) ? '' : sanitize_key( $_GET['product_id'] );
$add_product = ! empty( $_GET['action'] ) && $_GET['action'] == 'add';
if ( $add_product) {
	$this->render_template_page( 'ticketrilla-server-save-product', array('product' => new \TTLS\Models\Product()) );
} elseif( $product_id ) {
	$product = \TTLS\Models\Product::find_by_id( $product_id );
	$this->render_template_page( 'ticketrilla-server-save-product', array('product' => $product) );
} else {
	$this->render_template_page( 'ticketrilla-server-admin-products' );
}
