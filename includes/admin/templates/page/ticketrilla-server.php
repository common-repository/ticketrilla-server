<div id="ttls-container" class="ttls">
	<div class="ttls__header">
		<div class="ttls__header-inner">
			<div class="col-left">
				<h2 class="h4 wp-heading-inline"><?php echo esc_html__('Ticketrilla: Server', 'ttls_translate'); ?></h2><a href="<?php echo esc_url( ttls_url( 'ticketrilla-server-general-settings' ) ); ?>" class="btn btn-xs btn-dark"><?php echo esc_html__("Settings", 'ttls_translate'); ?></a>
			</div>
			<div class="col-right">
			<?php
				$breadcrumbs = new \TTLS\Helpers\Breadcrumbs( __("Dashboard", 'ttls_translate') );
				$breadcrumbs->render();
			?>
			</div>
		</div>
		<hr class="clearfix">
	</div>
	<div class="ttls__content">
		<div class="row">
			<?php
				$class_widget = new TTLS_Widget();
				$class_widget->print_area( 'dashboard_sl' );
				$class_widget->print_area( 'dashboard_sr' );
			?>
		</div>
	</div>
	<div class="ttls__alerts"></div>
</div>