<?php
	// Exit if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	if ( ! class_exists( 'TTLS_Widget' ) ) {

		class TTLS_Widget {

			var $areas = array( 'dashboard_sl', 'dashboard_sr' );

			function __construct() {
				$this->areas = apply_filters( 'ttls_widget_areas', $this->areas );

				add_filter( 'ttls_widget_print_widget_last_activity', array( $this, 'html_last_activity' ) );
				add_filter( 'ttls_widget_print_widget_free_tickets', array( $this, 'html_free_tickets' ) );

				add_filter( 'ttls_widget_print_area_dashboard_sl', array( $this, 'html_dashboard_sl' ) );
				add_filter( 'ttls_widget_print_area_dashboard_sr', array( $this, 'html_dashboard_sr' ) );
			}


			/**
			 * html widgets output
			 *
			 * @param      array   $widget  The widget's data
			 *
			 * @return     string  html
			 */
			function print_widget( $widget ){
				if ( empty( $widget['slug'] ) ) {
					return '<div class="ttls__widget ttls__widget-error">
						<div class="ttls__widget-title"><i class="fa fa-warning"></i>
							<h4>'.esc_html__('Unknown widget slug', 'ttls_translate').'</h4>
						</div></div>';
				} else {
					$html = apply_filters( 'ttls_widget_print_widget_'.$widget['slug'], new WP_Error( 'ttls_widget_nohtml', 'A function for HTML output of this widget is missing', array( 'status' => 500 ) ) );
					if ( is_wp_error( $html ) ) {
						return '<div class="ttls__widget ttls__widget-error">
							<div class="ttls__widget-title"><i class="fa fa-warning"></i>
								<h4>'.esc_html__($html->get_error_message(), 'ttls_translate').'</h4>
							</div></div>';
					} else {
						return $html;
					}
				}
			}


			/**
			 * html areas output
			 *
			 * @param      string  $area   The area's slug
			 */
			function print_area( $area ){
				if ( empty( $area ) ) {
					echo '<div class="col-lg-12">'.esc_html__('Unknown area slug', 'ttls_translate').'</div>';
				} else {
					$html = apply_filters( 'ttls_widget_print_area_'.$area, new WP_Error( 'ttls_widget_nohtml', 'A function for HTML output of this area is missing', array( 'status' => 500 ) ) );
					if ( is_wp_error( $html ) ) {
						echo '<div class="col-lg-12">'.esc_html__($html->get_error_message(), 'ttls_translate').'</div>';
					} else {
						echo wp_kses_post( $html );
					}
				}
			}


			/**
			 * ajax save positions for active widgets
			 * ttls_widget_save_position
			 */
			function save_position(){
				parse_str( $_POST['fields'], $fields );
				$user_id = get_current_user_id();
				$area = sanitize_text_field( $fields['area'] );
				delete_user_meta( $user_id, $area );
				if ( !empty( $fields['widget'] ) ) {
					foreach ( $fields['widget'] as $wgt ) {
						add_user_meta( $user_id, $area, sanitize_text_field( $wgt ) );
					}
				}
				wp_send_json_success( array(
					'message' => esc_html__('Position saved', 'ttls_translate')
				) );
			}


			/**
			 * Receive data related to the widgets of current user
			 *
			 * @param      array  $areas    The areas
			 * @param      int    $user_id  The user identifier
			 *
			 * @return     array  The user widgets.
			 */
			function get_user_widgets( $areas = false, $user_id = false ){
				if ( empty( $areas ) ) {
					$areas = $this->areas;
				}
				if ( empty( $user_id ) ) {
					$user_id = get_current_user_id();
				}
				$widgets = array();
				if ( is_array( $areas ) ) {
					foreach ( $areas as $area ) {
						$widgets[$area] = get_user_meta( $user_id, $area );
					}
				} else {
					$widgets = get_user_meta( $user_id, $areas );
				}
				return $widgets;
			}


			/**
			 * html for the left column of the dashboard
			 */
			function html_dashboard_sl(){
				echo '<form class="col-lg-8 connectedSortable ttls-widget-area">';
					echo '<input type="hidden" name="area" value="dashboard_sl">';
					$widgets = $this->get_user_widgets( 'dashboard_sl' );
					$widgets = array( array( 'slug' => 'last_activity' ) );
					if ( empty( $widgets ) ) {
						echo '<p class="error">'.esc_html__('There were no widgets created for this area', 'ttls_translate').'</p>';
					} else {
						foreach ( $widgets as $w_slug => $w_data ) {
							echo wp_kses_post( $this->print_widget( $w_data ) );
						}
					}
				echo '</form>';
			}


			/**
			 * html for the right column of the dashboard
			 */
			function html_dashboard_sr(){
				echo '<form class="col-lg-4 connectedSortable ttls-widget-area">';
					echo '<input type="hidden" name="area" value="dashboard_sr">';
					$widgets = $this->get_user_widgets( 'dashboard_sr' );
					$widgets = array( array( 'slug' => 'free_tickets' ) );
					if ( empty( $widgets ) ) {
						echo '<p>'.esc_html__('There were no widgets created for this area', 'ttls_translate').'</p>';
					} else {
						foreach ( $widgets as $w_slug => $w_data ) {
							echo wp_kses_post( $this->print_widget( $w_data ) );
						}
					}
				echo '</form>';
			}


			/**
			 * html for "last activity" widget
			 *
			 * @return     string  html
			 */
			function html_last_activity(){
				$user = get_current_user_id();
				$html = '<div class="ttls__widget" id="ttls-latest-activity-widget">
					<input type="hidden" name="widget[last_activity][slug]" value="last_activity">
					<div class="ttls__widget-move"></div>
					<div class="ttls__widget-title"><i class="fa fa-clock"></i>
						<h4>'.esc_html__('Latest Activity', 'ttls_translate').'</h4>
					</div>
					<div class="ttls__widget-body">';
					$tl_query = new WP_Query;
					$ticket_list = $tl_query->query( array (
						'fields' => 'ids',
						'post_type' => 'ttls_ticket',
						'orderby' => 'date',
						'order' => 'DESC',
						'meta_query' => array(
							array(
								'key' => 'ttls_ticket_developer',
								'value' => $user
							)
						),
					) );

					$new_tickets = $tl_query->query( array (
						'fields' => 'ids',
						'post_type' => 'ttls_ticket',
						'orderby' => 'date',
						'order' => 'DESC',
						'meta_query' => array(
							'relation' => 'OR',
							array(
								'key' => 'ttls_ticket_developer',
								'value' => $user
							),
							array(
								'relation' => 'OR',
								array(
									'key' => 'ttls_response_status',
									'value' => 'response'
								),
								array(
									'key' => 'ttls_status',
									'value' => 'free'
								)
							)
						),
					) );


					$author_responses = $tl_query->query( array (
						'fields' => 'ids',
						'post_type' => 'ttls_ticket',
						'orderby' => 'date',
						'order' => 'DESC',
						'author' => $user,
					) );
					if ( !empty($ticket_list)) {
						$ticket_responses = $tl_query->query( array (
							'fields' => 'ids',
							'post_type' => 'ttls_ticket',
							'orderby' => 'date',
							'order' => 'DESC',
							'post_parent__in' => $ticket_list,
						) );
					} else {
						$ticket_responses = array();
					}

					$activity_responses = array_merge($ticket_responses,$author_responses,$new_tickets);

					$activity = $tl_query->query( array (
						'fields' => 'ids',
						'post_type' => 'ttls_ticket',
						'orderby' => 'date',
						'order' => 'DESC',
						'post__in' => $activity_responses,
					) );
					if ( empty( $activity ) ) {
						$html .= '<p>'.esc_html__('No activity, at this point','ttls_translate').'</p>';
					} else {

						$html .= '<ul class="ttls__tickets-activity">';
						$time_label = 0;
						foreach ($activity as $t_r) {
							$response = TTLS()->response_service()->prepare_response_data( new TTLS\Models\Response( get_post( $t_r ) ) );
							$print_time_label = '';
							if ( $time_label ) {
								if ( $time_label != (new \DateTime($response['time']))->format('d-m-Y') ) {
									$time_label = (new \DateTime($response['time']))->format('d-m-Y');

									$html .= '<li class="time-label"><span>'.esc_html( (new \DateTime($response['time']))->format('d-m-Y') ).'</span></li>';
								}
							} else {
								$time_label = (new \DateTime($response['time']))->format('d-m-Y');
								$html .= '<li class="time-label"><span>'.esc_html( (new \DateTime($response['time']))->format('d-m-Y') ).'</span></li>';
							}

							if ( empty( $response['parent_id'] ) ) {
								$ticket_name = '#'.esc_html( $response['id'].'. '.stripcslashes( get_post($response['id'])->post_title ) );
								$parent_link = '<a href="'.esc_url( ttls_url( 'ticketrilla-server-tickets', array('t_paged' => 1, 'ticket_id' => $response['id']) ) ).'">'.esc_html( $ticket_name ).'</a>';
								$product_id = get_post_meta( $response['id'], 'ttls_product_id', true );
							} else {
								$ticket_name = '#'.esc_html( $response['parent_id'].'. '.stripcslashes( get_post($response['parent_id'])->post_title ) );								
								$parent_link = '<a class="ttls__tickets-activity-header-blocklink" href="'. esc_url( ttls_url( 'ticketrilla-server-tickets', array('t_paged' => 1, 'ticket_id' => $response['parent_id']) ) ).'">'.esc_html( $ticket_name ).'</a>';
								$product_id = get_post_meta( $response['parent_id'], 'ttls_product_id', true );
							}

							$product_title = empty( $product_id ) ? '' : get_the_title( $product_id );
							
							if ( $response['type'] === 'response' ) {

								if ( user_can( $response['author_id'], 'ttls_clients' ) ) {
									$label = '<i class="fa fa-question"></i>';
								} else {
									$label = '<i class="fa fa-share"></i>';
								}

							} elseif ( $response['type'] === 'ticket' ) {

								$label = '<i class="fa fa-plus"></i>';

							} else {

								$label = '<i class="fa fa-cogs"></i>';

							}
							
							$response_title = TTLS()->response_service()::get_localized_title( array(
								'type' => $response['type'],
								'prepend' => esc_html( $response['author'] ),
							) );
							
							$html .= '<li>'.wp_kses_post( $label ).'
								<div class="ttls__tickets-activity-header">
									<span>'.esc_html( (new \DateTime($response['time']))->format('H:i') ).'</span>
									<div>' .
										wp_kses_post( $parent_link ) . 
										'<div class="ttls__tickets-activity-header-product-title">' . esc_html( $product_title ) . '</div>' .
										'<div class="ttls__tickets-activity-header-response-title">' . esc_html( $response_title ) . '</div>' .
									'</div>
								</div>
							</li>';
						}
						$html .= '</ul>';
					}
					$html .= '</div>
				</div>';
				return $html;
			}


			/**
			 * html of "Free tickets" widget
			 *
			 * @return     string  html
			 */
			function html_free_tickets(){
				$all_tickets = TTLS()->ticket_service()->get_list( 1, false, 'free' );
				$html = '<div class="ttls__widget ttls__widget-freeTickets" id="ttls-free-tickets-widget">
					<input type="hidden" name="widget[free_tickets][slug]" value="free_tickets">
					<div class="ttls__widget-move"></div>
					<div class="ttls__widget-title"><i class="fa fa-bug"></i>
						<h4>'.esc_html__('Unassigned Tickets', 'ttls_translate').'</h4>
					</div>';
					$html .= '<table class="table table-hover">
						<thead>
							<tr>
								<th>'.esc_html__('Tickets', 'ttls_translate').'</th>
								<th>'.esc_html__('Date', 'ttls_translate').'</th>
								<th>'.esc_html__('Action', 'ttls_translate').'</th>
							</tr>
						</thead>
						<tbody>';
				if ( is_wp_error( $all_tickets ) ) {
					$html .= '<tr class="ttls_no_free_ticket"><td colspan=3>'.esc_html__( $all_tickets->get_error_message(), 'ttls_translate' ).'</td></tr>';
				} else {
					foreach ( $all_tickets['tickets'] as $ticket ) {
						$html .= self::html_free_ticket( $ticket );
					}

				}
				$html .= '</tbody>
						<tfoot>
							<tr>
								<th>'.esc_html__('Tickets', 'ttls_translate').'</th>
								<th>'.esc_html__('Date', 'ttls_translate').'</th>
								<th>'.esc_html__('Action', 'ttls_translate').'</th>
							</tr>
						</tfoot>
					</table>';
				$html .= '</div>';
				return $html;
			}

			/**
			 * html of "Free tickets" widget for ajax
			 *
			 * @return     string  html
			 */

			// wp_action - ttls_widget_check_free
			
			public static function html_free_tickets_ajax() {
				
				$tickets = TTLS()->ticket_service()->get_list( 1, false, 'free' );
				
				if ( is_wp_error( $tickets ) ) {
					wp_send_json_error( array( 'message' => esc_html__('There are no free tickets', 'ttls_translate') ) );
				}

				$html_tickets = array();

				foreach ( $tickets['tickets'] as $ticket ) {
					
					$html_tickets[$ticket['id']] = self::html_free_ticket( $ticket );
				
				}
				
				wp_send_json_success( array(
					'tickets' => $html_tickets,
				) );
			
			}

			private static function html_free_ticket( $ticket ) {
				$product_id = get_post_meta( $ticket['id'], 'ttls_product_id', true );
				$product_title = empty( $product_id ) ? '' : get_the_title( $product_id );
				$html = '';
				$html .= '<tr class="ttls_free_ticket" data-ticket="'.esc_attr( $ticket['id'] ).'">';
				$html .= '<td><a href="'.esc_url( ttls_url( 'ticketrilla-server-tickets', array('ticket_id' => $ticket['id']) ) ). '">#'.esc_html( $ticket['id'].'. '.stripslashes($ticket['title']) ).'</a><div class="ttls__widget-freeTickets-product-title">' . $product_title . '</div></td>';
				$html .= '<td>'.esc_attr( $ticket['response_last_date'] ).'</td>';
				$html .= '<td><a href="#" data-ticket="'.esc_attr( $ticket['id'] ).'" class="btn btn-block btn-xs btn-info ttls_widget_take_ticket">'.esc_html__('Take', 'ttls_translate').'</a></td>';
				$html .= '</tr>';
				return $html;
			}

		}
	}