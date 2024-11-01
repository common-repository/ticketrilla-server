<?php
$client_product = $data['client_product']; // Model
$product = $data['product'];
$licenses = \TTLS_License::get_license_list( $product );
$uniq_id = $data['uniq_id'];
$form_id = 'ttls-client-save-product-' . $uniq_id;
$form_action = empty( $client_product->license_id ) ? 'add' : 'save';
?>
<div id="ttls-modal-product-<?php echo esc_attr( $uniq_id ); ?>" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
	<div role="document" class="modal-dialog">
    <div class="modal-content modal-product collapse fade in">
      <div class="modal-header">
        <button type="button" data-bs-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true">×</span></button>
        <h4 class="modal-title"><?php esc_html_e( 'Product Settings', 'ttls_translate' ); ?></h4>
      </div>

      <div class="modal-body">
        <form class="form-horizontal ttls-client-save-product-form">
          <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'ttls_client_save_product' ); ?>">
          <input type="hidden" name="product_id" value="<?php echo esc_attr( $client_product->product_id ); ?>">
          <?php if ( ! empty( $client_product->license_id ) ) { ?>
          <input type="hidden" name="license_id" value="<?php echo esc_attr( $client_product->license_id ); ?>">
          <?php } ?>
          <div class="form-group">
          <?php
            $input_id = $form_id . '-title';
          ?>
        		<label for="<?php echo esc_attr( $input_id ); ?>" class="col-md-3 control-label"><?php esc_html_e( 'Product', 'ttls_translate' ); ?></label>
            <div class="col-md-9">
              <input disabled name="title" id="<?php echo esc_attr( $input_id ); ?>" type="text" aria-label="..." value="<?php echo isset( $product->post_title ) ? esc_attr( $product->post_title ) : ''; ?>" class="form-control">
            </div>
	        </div>

          <div class="form-group <?php echo esc_attr( $client_product->has_errors( 'license' ) ? 'has-error' : '' );?>">
          <?php
            $input_id = $form_id . '-license';
          ?>
		        <label for="<?php echo esc_attr( $input_id ); ?>" class="col-md-3 control-label"><?php esc_html_e( 'License', 'ttls_translate' ); ?></label>
            <div class="col-md-9">
              <select name="license" id="<?php echo esc_attr( $input_id ); ?>" class="form-control ttls-license-select" value="<?php echo isset( $client_product->license ) ? esc_attr( $client_product->license ) : ''; ?>"<?php echo empty( $client_product->license_id ) ? '' : ' disabled'; ?>>
                <?php
                  $selected_license = isset( $client_product->license ) ? $client_product->license : key($licenses);
                  foreach ( $licenses as $license_type => $license_data ) {
              ?>
                <option value="<?php echo esc_attr( $license_type ); ?>" <?php echo esc_html( $selected_license === $license_type ? 'selected="selected"' : '' ); ?>><?php echo esc_html( $license_data['title'] ); ?></option>
              <?php } ?>
              </select>
              <?php
                if ( $client_product->has_errors( 'license' ) ) {
                  foreach ( $client_product->get_errors( 'license' ) as $error_message ) {
                    echo '<div class="help-block">' . esc_html( $error_message ) . '</div>';	
                  }
                }
              ?>
            </div>
	        </div>

          <div class="ttls-license-fields">
	        <?php foreach( $licenses as $license_type => $license_data ) { ?>
		        <div class="ttls-license-fields-<?php echo esc_attr( $license_type ); ?> <?php echo esc_attr( $selected_license === $license_type ? '' : 'collapse' ); ?>">
          <?php
            foreach( $license_data['fields'] as $license_field_name => $license_field_data ) {
              $license_field_mode = false;
              if ( $form_action === 'save' && $license_field_data['login'] ) {
                $license_field_mode = $license_field_data['login'];
              } elseif( $form_action === 'add' && $license_field_data['register'] ) {
                $license_field_mode = $license_field_data['register'];
              }
              if ( $license_field_mode ) {
                $license_field_uniqid = uniqid();
                $license_field_id = $form_id . '-' . $license_field_name . '-' . $license_field_uniqid;
                $license_field_disabled = $selected_license === $license_type && $form_action === 'add' ? '' : 'disabled="disabled"';
                if ( $license_field_mode === 'possible' ) {
                  $license_field_checkbox_id = $form_id . '-' . $license_field_name . '-checkbox-' . $license_field_uniqid;
                  $license_field_checkbox_name = $license_field_name . '-checkbox';
                  $license_field_checkbox_on = ! empty( $_POST[$license_field_checkbox_name] );
                  $license_field_disabled = $license_field_checkbox_on ? '' : 'disabled="disabled"';
		            ?>
              <div class="form-group">
                <div class="col-md-9 col-md-offset-3">
                  <div class="checkbox">
                    <input type="checkbox" id="<?php echo esc_attr( $license_field_checkbox_id ); ?>" name="<?php echo esc_attr( $license_field_checkbox_name ); ?>" class="form-control ttls-license-field-checkbox" <?php echo esc_html( $license_field_checkbox_on ? 'checked="checked"' : '' ); ?> value="<?php echo esc_attr( $license_field_id ); ?>">
                    <label for="<?php echo esc_attr( $license_field_checkbox_id ); ?>"><?php esc_html_e( 'I have a license', 'ttls_translate' ); ?></label>
                  </div>
                </div>
              </div>
          <?php } ?>

              <div class="form-group <?php echo esc_attr( $selected_license === $license_type && $client_product->has_errors( 'license_data' ) ? 'has-error' : '' );?> <?php echo esc_attr( $license_field_mode === 'possible' && $license_field_disabled ? 'collapse' : '' ); ?>">
                <label for="<?php echo esc_attr( $license_field_id ); ?>" class="col-md-3 control-label" ><?php echo esc_html( $license_field_data['title'] ); ?></label>
                <div class="col-md-9">
                  <input <?php echo esc_attr( $license_field_disabled ); ?> name="<?php echo esc_attr( $license_field_name ); ?>" id="<?php echo esc_attr( $license_field_id ); ?>" type="<?php echo esc_attr( $license_field_data['type'] ); ?>" placeholder="<?php echo esc_attr( sprintf( __( 'Enter %s', 'ttls_translate' ), $license_field_data['title'] ) ); ?>" class="form-control" value="<?php echo esc_attr( $selected_license === $license_type && isset( $client_product->$license_field_name ) ? $client_product->$license_field_name : '' ); ?>">
                <?php
                  if ( $selected_license === $license_type && $client_product->has_errors( 'license_data' ) ) {
                    foreach ( $client_product->get_errors( 'license_data' ) as $error_message ) {
                      echo '<div class="help-block">' . esc_html( $error_message ) . '</div>';	
                    }
                  }
                ?>
                </div>
              </div>
          <?php
            }
          }
          ?>
		</div>
	  <?php } ?>
	        </div>

          <div class="form-group <?php echo esc_attr( $client_product->has_errors( 'newsletters' ) ? 'has-error' : '' ); ?>">
          <?php $input_id = $form_id . '-newsletters'; ?>					
            <div class="col-md-9 col-md-offset-3">
              <div class="checkbox">
                <input name="newsletters" type="checkbox" id="<?php echo esc_attr( $input_id ); ?>" <?php echo empty( $client_product->newsletters ) ? '' : 'checked'; ?> value="y">
                <label for="<?php echo esc_attr( $input_id ); ?>"> 
                <?php
                  echo esc_html__( 'I want to receive newsletters', 'ttls_translate' );
                ?>
                </label>
              </div>
              <?php
                if ( $client_product->has_errors( 'newsletters' ) ) {
                  foreach ( $client_product->get_errors( 'newsletters' ) as $error_message ) {
                    echo '<div class="help-block">' . esc_html( $error_message ) . '</div>';	
                  }
                }
              ?>
            </div>
          </div>

          <?php if ( empty( $client_product->license_id ) ) { ?>
          <div class="form-group <?php echo esc_attr( $client_product->has_errors( 'terms' ) ? 'has-error' : '' ); ?>">
          <?php $input_id = $form_id . '-terms'; ?>					
            <div class="col-md-9 col-md-offset-3">
              <div class="checkbox">
                <input name="terms" type="checkbox" id="<?php echo esc_attr( $input_id ); ?>" <?php echo empty( $client_product->terms ) ? '' : 'checked'; ?> value="y">
                <label for="<?php echo esc_attr( $input_id ); ?>"> 
                <?php
                  printf(
                    esc_html__( 'I agree to your %1$sterms of service%3$s and %2$sprivacy statement.%3$s', 'ttls_translate' ),
                    '<a target="_blank" href="' . esc_url( $product->terms ) . '">',
                    '<a target="_blank" href="' . esc_url( $product->privacy ) . '">',
                    '</a>'
                  );
                ?>
                </label>
              </div>
              <?php
                if ( $client_product->has_errors( 'terms' ) ) {
                  foreach ( $client_product->get_errors( 'terms' ) as $error_message ) {
                    echo '<div class="help-block">' . esc_html( $error_message ) . '</div>';	
                  }
                }
              ?>
            </div>
          </div>
          <?php } ?>
          
          <?php if ( $client_product->has_errors( '_global' ) ) {?>
	        <div class="form-group has-error">
		        <div class="col-md-9 col-md-offset-3">
            <?php
                foreach ( $client_product->get_errors( '_global' ) as $error_message ) {
                  echo '<div class="help-block">' . esc_html( $error_message ) . '</div>';	
                }
            ?>
		        </div>
	        </div>
          <?php
            }
          ?>

        </form>
      </div>
      <div class="modal-footer">
				<button type="button" data-bs-dismiss="modal" class="btn btn-default"><?php esc_html_e( 'Close', 'ttls_translate' ); ?></button>
				<button type="submit" class="btn btn-dark ttls-product-save-btn"><?php esc_html_e( 'Save Changes', 'ttls_translate' ); ?></button>
			</div>
    </div>
  </div>
</div>