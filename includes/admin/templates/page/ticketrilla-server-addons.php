<div id="ttls-container" class="ttls">
	<div class="ttls__header">
		<div class="ttls__header-inner">
			<div class="col-left">
				<h2 class="h4 wp-heading-inline"><?php echo esc_html__('Ticketrilla: Server', 'ttls_translate'); ?></h2>
				<a href="<?php echo esc_url( ttls_url( 'ticketrilla-server-general-settings' ) ); ?>" class="btn btn-xs btn-dark"><?php echo esc_html__('Settings', 'ttls_translate'); ?></a>
			</div>
			<div class="col-right">
			<?php
				$breadcrumbs = new \TTLS\Helpers\Breadcrumbs( __('Addons', 'ttls_translate') );
				$breadcrumbs->render();
			?>
			</div>
		</div>
		<hr class="clearfix">
	</div>
	<?php
		$request = wp_remote_get('https://ticketrilla.com/files/addons/addons.xml');
		$xmlstr = wp_remote_retrieve_body( $request );
		if ( $xmlstr ) {
		$addon_list = new SimpleXMLElement($xmlstr);
		$active_addons = '';
	?>
	<div class="ttls__content">
		<div class="ttls__cards">
			<?php foreach ( $addon_list as $addon) {
				if ( !empty( $addon->plugin ) AND is_plugin_active( (string) $addon->plugin ) ) {
					$installed_plugin = get_plugin_data( WP_PLUGIN_DIR.'/'.$addon->plugin );
					$active_addons .= '<tr>';
						$active_addons .= '<td>';
							$active_addons .= '<h4>'.esc_html( $addon->item->title ).'</h4>';
							$active_addons .= '<p>'.esc_html( $addon->item->description ).'</p>';
						$active_addons .= '</td>';
						$active_addons .= '<td>'.esc_html( $installed_plugin['Version'] ).'</td>';
						$active_addons .= '<td>'.esc_html( $addon->item->version ).'</td>';
						if ( $installed_plugin['Version'] == $addon->item->version ) {
							$active_addons .= '<td>'.esc_html__('You have the latest version', 'ttls_translate').'</td>';
						} else {
							$active_addons .= '<td><a target="_blank" href="'.esc_url( $addon->item->link ).'" class="btn btn-block btn-dark">'.esc_html__('Update', 'ttls_translate').'</a></td>';
						}

					$active_addons .= '</tr>';
				}  else { ?>
			<article class="ttls__card plugin">
				<?php if ( !empty( $addon->item->image ) ) { ?>
				<div class="ttls__card-thumbnail"><img src="<?php echo esc_url( $addon->item->image ); ?>" alt="<?php echo esc_html( $addon->item->title ); ?>"></div>
				<?php } ?>
				<div class="ttls__card-entry">
					<header class="ttls__card-header">
						<h3 class="ttls__card-title"><?php echo esc_html( $addon->item->title ); ?></h3>
					</header>
					<div class="ttls__card-excerpt">
						<p><?php echo esc_html( $addon->item->description ); ?></p>
					</div>
					<?php if ( !empty( $addon->author->name ) ) { ?>
					<div class="ttls__card-authors">
						<?php if ( empty( $addon->author->link ) ) { ?>
						<cite><?php echo esc_html__('by', 'ttls_translate'); ?> <?php echo esc_html( $addon->author->name ); ?></cite>
						<?php } else { ?>
						<cite><?php echo esc_html__('by', 'ttls_translate'); ?> <a target="_blank" href="<?php echo esc_url( $addon->author->link ); ?>" target="_blank"><?php echo esc_html( $addon->author->name ); ?></a></cite>
						<?php } ?>
					</div>
					<?php } ?>
				</div>
				<div class="ttls__card-footer">
					<div class="ttls__card-footer-inner">
						<div class="ttls__card-price">
							<?php if ( !empty( $addon->item->price ) ) { ?>
							<small><?php echo esc_html__('Price', 'ttls_translate'); ?>:</small> <span>$<?php echo esc_html( $addon->item->price ); ?></span>
							<?php } else { ?>
							<span><?php echo esc_html__('FREE', 'ttls_translate'); ?></span>
							<?php } ?>
						</div>
						<?php if ( !empty( $addon->item->link ) ) { ?>
						<a target="_blank" href="<?php echo esc_url( $addon->item->link ); ?>" class="btn btn-dark"><?php echo esc_html__('Buy now', 'ttls_translate'); ?></a>
						<?php } ?>
					</div>
				</div>
			</article>
				<?php } // is_plugin_active ?>
			<?php } // foreach $addon_list ?>
		</div>

		<hr>
		<div class="ttls__instaled">
			<h3><?php echo esc_html__('Installed', 'ttls_translate'); ?></h3>
			<div class="ttls__instaled-inner">
				<table class="table table-striped">
					<thead>
						<tr>
							<th><?php echo esc_html__('Addon', 'ttls_translate'); ?></th>
							<th><?php echo esc_html__('Your version', 'ttls_translate'); ?></th>
							<th><?php echo esc_html__('Latest version', 'ttls_translate'); ?></th>
							<th><?php echo esc_html__('Actions', 'ttls_translate'); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th><?php echo esc_html__('Addon', 'ttls_translate'); ?></th>
							<th><?php echo esc_html__('Your version', 'ttls_translate'); ?></th>
							<th><?php echo esc_html__('Latest version', 'ttls_translate'); ?></th>
							<th><?php echo esc_html__('Actions', 'ttls_translate'); ?></th>
						</tr>
					</tfoot>
					<tbody><?php echo wp_kses_post( $active_addons ); ?></tbody>
				</table>
			</div>
		</div>
	</div>
	<?php } ?>
</div>