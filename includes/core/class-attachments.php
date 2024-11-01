<?php
	// Exit if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	if ( ! class_exists( 'TTLS_Attachments' ) ) {

		class TTLS_Attachments {

			var $create_zip = true;
			var $image_types = array( 'png', 'jpg', 'jpeg' ); // is required to determine which extension will be used to generate the thumb
			var $code_types = array( 'php', 'js', 'css', 'html', 'less' ); // hasn't been used yet, as the approach changed

			function __construct( $caps = false ){ // an object with TTLS_Capabilities rights is inserted
				global $ttls_create_zip;
				$this->create_zip = $ttls_create_zip;
				if ( $caps ) {
					$this->caps = $caps; // writing the inserted object with TTLS_Capabilities rights
				} else {
					$this->caps = new TTLS_Capabilities(); // If the rights weren't inserted, we generate it
				}
			}

			/**
			 * A notice for actions related to generating htaccess - to settings
			 * add_action('admin_notices', array( 'TTLS_Attachments', 'print_uploads_error' ));
			 */
			static function print_uploads_error(){
				echo '<div class="notice notice-error is-dismissible"><p>';
				echo esc_html__('You currently have public execution rights in the uploads folder of the plugin! Please create an .htaccess file manually which will include the following parameter:','ttls_translate');
				echo '<pre>php_flag engine off</pre>';
				echo esc_html__(' or generate the .htaccess file automatically in: ','ttls_translate');
				echo '<a href="' . esc_url( ttls_url( 'ticketrilla-server-general-settings' ) . '#ttls__attachment' ) . '">'.esc_html__('Settings','ttls_translate'). '</a>';
				echo '</p></div>';
			}

			/**
			 * Determines if archive.
			 *
			 * @param      string   $tmp_name        The temporary file path
			 * @param      string   $saved_file_ext  Extension of file
			 *
			 * @return     boolean  True if archive, False otherwise.
			 */
			function is_archive( $tmp_name, $saved_file_ext ) {
				$archive_types = array(
					'zip' => 'application/zip',
					'rar' => 'application/x-rar-compressed',
					'7z' => 'application/x-7z-compressed',
					'tar' => 'application/x-tar',
					'tgz' => 'application/x-gzip',
					'gz' => 'application/x-gzip',
				);
				return array_key_exists( $saved_file_ext, $archive_types ) && $archive_types[$saved_file_ext] === mime_content_type( $tmp_name );
			}


			/**
			 * the method for generating html for output is receiving data from the method get() in
			 * attachment_data you can send data via array or ID
			 *
			 * @param      array         $attachment_data  		attachment data
			 * @param      boolean       $only_box         		output by status or
			 *                                             		just html
			 *
			 * @return     array|string  		html or array of message and html
			 */
			function print_single( $attachment_data, $only_box = false ){
				if ( is_array( $attachment_data ) ) {
					$get_att = $attachment_data;
				} else {
					$get_att = $this->get( $attachment_data );
				}
				if ( !is_wp_error( $get_att ) ) { // when the data was received without errors
					$attachment = $get_att;
					$response = array( 'status' => true );
					switch ( $attachment['type'] ) { // Depending on the extension an icon/thumb is generated
						case 'txt':
							$att_icon = '<span class="ttls__attachments-icon"><i class="fa fa-file-text-o"></i></span>';
							break;
						case 'png':
						case 'jpg':
						case 'jpeg':
							if ( $attachment['thumb'] ) {
								$att_icon = '<span class="ttls__attachments-icon has-img"><img src="'.esc_attr( $attachment['thumb'] ).'" alt="'.esc_attr( $attachment['name'] ).'"></span>';
							} else {
								$att_icon = '<span class="ttls__attachments-icon"><i class="fa fa-file-image-o"></i></span>';
							}
							break;

						case 'pdf':
							$att_icon = '<span class="ttls__attachments-icon"><i class="fa fa-file-pdf-o"></i></span>';
							break;
						case 'xls':
						case 'xlsx':
							$att_icon = '<span class="ttls__attachments-icon"><i class="fa fa-file-excel-o"></i></span>';
							break;
						case 'doc':
						case 'docx':
							$att_icon = '<span class="ttls__attachments-icon"><i class="fa fa-file-word-o"></i></span>';
							break;
						case 'ppt':
						case 'pptx':
							$att_icon = '<span class="ttls__attachments-icon"><i class="fa fa-file-powerpoint-o"></i></span>';
							break;
						case 'rar':
						case 'zip':
							$att_icon = '<span class="ttls__attachments-icon"><i class="fa fa-file-archive"></i></span>';
							break;
						case 'css':
						case 'less':
						case 'js':
						case 'php':
						case 'html':
							$att_icon = '<span class="ttls__attachments-icon"><i class="fa fa-file-code-o"></i></span>';
							break;

						case 'mp3':
							$att_icon = '<span class="ttls__attachments-icon"><i class="fa fa-file-audio-o"></i></span>';
							break;

						case 'avi':
							$att_icon = '<span class="ttls__attachments-icon"><i class="fa fa-file-video-o"></i></span>';
							break;


						default:
							$att_icon = '<span class="ttls__attachments-icon"><i class="fa fa-file"></i></span>';
							break;
					}


					$response['box'] = '<li class="ttls_attachment" data-attachment="'.esc_attr( $attachment['id'] ).'">';
					$response['box'] .= '<input type="hidden" name="ttls_attachment[]" value="'.esc_attr( $attachment['id'] ).'">';
					$response['box'] .= $att_icon;
					$response['box'] .= '<div class="ttls__attachments-info">';
					$response['box'] .= '<div class="ttls__attachments-name">'.esc_attr( $attachment['name'] ).'</div>';
					// translate bytes to KB or MB
					if ( $attachment['size'] > 1024 * 1024 ) { // > MB
						$size = round( $attachment['size'] / 1024 / 1024, 2).' MB';
					} elseif ( $attachment['size'] > 1024 * 10 ) { // > 10 KB
						$size = round( $attachment['size'] / 1024, 0).' KB';
					} elseif ( $attachment['size'] > 1024 ) { // > 1 KB
						$size = round( $attachment['size'] / 1024, 2).' KB';
					} else {
						$size = $attachment['size'].' B';
					}
					$response['box'] .= '<div class="ttls__attachments-size"><span>'.esc_html( $size ).'</span>';



					if ( current_user_can( 'ttls_developers' ) && $attachment['location'] == 'external' ) { // when the file is from an external site, a manual upload button is presented
						$response['box'] .= '<a data-attachment="'.esc_attr( $attachment['id'] ).'" title="Load to server" class="ttls__attachments-load btn btn-xs btn-info ttls_manual_load_attachment"><i class="fa fa-cloud-download-alt"></i>'.esc_html__('To server', 'ttls_translate').'</a>';
					}

					// can delete attachment if it is temporary
					if ( $attachment['location'] == 'temp' ) {
						$response['box'] .= '<a data-attachment="'.esc_attr( $attachment['id'] ).'" title="'.esc_html__('Delete this attachment', 'ttls_translate').'" class="ttls__attachments-delete btn btn-xs btn-danger ttls_delete_temp_attachment"><i class="fa fa-trash"></i> Delete</a>';
					}


					$response['box'] .= '</div></div><a href="'.esc_attr( $attachment['link'] ).'" target="_blank" title="'.esc_html__('Download', 'ttls_translate').'" class="ttls__attachments-link" download></a>';

					$response['box'] .= '</li>';
				} else {
					$error_message = '';
					foreach ( $get_att->get_error_messages() as $key => $error_string) {
						$error_message .=  esc_html__( $error_string, 'ttls_translate').'<br>';
					}
					$response = array( 'status' => false, 'box' => '<li class="ttls__attachments-error"><span class="ttls__attachments-icon"><i class="fa fa-times"></i></span>
						<div class="ttls__attachments-info">
							<div title="'.esc_attr( $error_message ).'" class="ttls__attachments-name">'.esc_html( $error_message ).'</div>
							<div class="ttls__attachments-size"><span></span><a href="#" title="'.esc_html__('Delete this attachment', 'ttls_translate').'" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> '.esc_html__('Delete', 'ttls_translate').'</a></div>
						</div>
					</li>' );
				}
				if ( $only_box ) {
					return $response['box'];
				} else {
					return $response;
				}
			}


			/**
			 * receive data from the database regarding the attachment and
			 * generate the preview images if they are not present
			 *
			 * @param      int             $attachment_id  The attachment identifier
			 *
			 * @return     array|WP_Error  array with data related to attachments or error message
			 */
			function get( $attachment_id ){
				$error = new WP_Error;
				$attach = get_post( $attachment_id );
				if ( is_null( $attach ) ) {
					$error->add( 'ttls_attachment_noattachment', 'There are no attachments with this ID', array( 'status' => 404 ) );
					return $error;
				} else {
					if ( $attach->ttls_attach_location != 'external') { // when the file is not on external server
						if ( in_array( $attach->ttls_attach_type, $this->image_types ) ) { // is an image
							if ( $attach->ttls_attach_thumb ) { // if there is a generated thumb
								$att_info['thumb'] = $attach->ttls_attach_thumb; // sending the completed preview
							} else { // if we have not generated thumb - then we will create it and save in the uploads folder
								$thumbname = 'thumb-'.basename( parse_url( $attach->ttls_attach_link, PHP_URL_PATH ) ); // filename with the prefix "thumb-"
								$thumb_gen = wp_get_image_editor( $attach->ttls_attach_link ); // built-in WordPress editor
								if ( is_wp_error( $thumb_gen ) ) {
									return $thumb_gen;
								} else {
									$thumb_gen->resize( 140, 130, true ); // the size for our adaptation
								}
								$uploads_dir = wp_upload_dir('ttls');
								$thumb_gen->save( $uploads_dir['path'].$thumbname );
								update_post_meta( $attach->ID, 'ttls_attach_thumb', trim( $uploads_dir['url'], '/' ) . '/' . $thumbname ); // including a preview field - will be loaded from here in the future
								$att_info['thumb'] = $attach->ttls_attach_thumb;
							}
						}
					} else {
						$att_info['thumb'] = false;
					}
					$att_info['id'] = $attach->ID; // ID on the server
					$att_info['name'] = $attach->post_title; // custom name
					$att_info['location'] = $attach->ttls_attach_location; // the location of: temp | server | external
					$att_info['size'] = $attach->ttls_attach_size; // size in bytes
					$att_info['type'] = $attach->ttls_attach_type; // image depndant on file type
					$att_info['link'] = $attach->ttls_attach_link; // a link to the file on the server
					$att_info['ticket'] = $attach->ttls_attach_ticket; // ticket ID

					$att_info['external_id'] = $attach->ttls_attach_external_id; // ID of the attachment on external server
					$att_info['external_link'] = $attach->ttls_attach_external_link; // link on external server
					$att_info['md5'] = $attach->ttls_attach_external_md5; // md5 hash for check during the upload process


					return $att_info;
				}
			}


			/**
			 * method for generating a list of attchments for the ticket, which were uploaded
			 * by the current user, but were not sent to client
			 *
			 * @param      int             $ticket  ID
			 *
			 * @return     WP_Error|array  an error or array with data regarding the attachments
			 */
			function get_tempattachments_of_ticket( $ticket ){
				$error = new WP_Error;
				$att_query = new WP_Query;
				$att_args = array(
					'post_type' 	=> 'ttls_attachments',
					'nopaging' 		=> true,
					'author' 	=> get_current_user_id(),
					'meta_query' 	=> array(
						array(
							'key' 	=> 'ttls_attach_location',
							'value' => 'temp'
						),
						array(
							'key' 	=> 'ttls_attach_ticket',
							'value' => $ticket
						)
					)
				);

				$temp_atts = $att_query->query( $att_args );
				if ( !empty( $temp_atts ) ) {
					$attachments = array();
					foreach ( $temp_atts as $key => $att) {
						$attachments[] = $this->get( $att->ID );
					}
					return $attachments;
				} else {
					$error->add( 'ttls_attachment_notemp', 'Temporary attachments not found', array( 'status' => 404 ) );
					return $error;
				}
			}

			/**
			 * Method of uploading attachments via admin when a ticket is being replied via
			 * ajax uploaded with status temp wp_ajax - action:
			 * 'ttls_add_response_attachment'
			 */
			static function ajax_load_temp_files(){

				if ( ! empty( $_FILES['file'] ) && ! $_FILES['file']['error'] ) { // If there are files that require processing

					$error_message = '';
					$ttls_attachments = new self();

					if ( isset( $_POST['ticket'] ) && $_POST['ticket'] ) { // when the ticket is specified
						$ticket_id = sanitize_key( $_POST['ticket'] );
						$name = sanitize_file_name( basename( $_FILES['file']['name'] ) );
						$loaded_attachment = $ttls_attachments->load_file( $_FILES['file']['tmp_name'], $name, $ticket_id );
						if ( is_wp_error( $loaded_attachment ) ) {
							$error_message = $loaded_attachment->get_error_message();
						} else {
							wp_send_json_success( array(
								'box' => $ttls_attachments->print_single( $loaded_attachment, true ),
							) );		
						}

					} else { // when ticket is not specified
						$error_message = __('No ticket ID', 'ttls_translate');
					}

					if ( $error_message ) {
						wp_send_json_error( array(
							'box' => '<li class="ttls__attachments-error"><span class="ttls__attachments-icon"><i class="fa fa-times"></i></span>
									<div class="ttls__attachments-info">
										<div title="' . esc_attr( $error_message ) . '" class="ttls__attachments-name">' . esc_html( $error_message ) . '</div>
										<div class="ttls__attachments-size"><span></span><a href="#" title="' . esc_html__('Delete this attachment', 'ttls_translate') . '" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> '.esc_html__('Delete', 'ttls_translate').'</a></div>
									</div>
								</li>',
						) );
					}

				}
				// when the function didn't get files
				wp_send_json_error( array(
					'box' => '<li class="ttls__attachments-error">
						<button class="close" onclick="jQuery(this).parent().remove()">×</button>
						<span class="ttls__attachments-icon"><i class="fa fa-ban"></i></span>
						<div class="ttls__attachments-info">
							<div title="'. esc_attr__('No attachments', 'ttls_translate') . '" class="ttls__attachments-name">'. esc_html__('Loading error', 'ttls_translate') .'</div>
						</div>
					</li>'
				) );
			}

			function load_file( $tmp_name, $name, $ticket_id ) {
				$saved_file_name = $name;
				$saved_file_ext = pathinfo( $name, PATHINFO_EXTENSION ); // determine file type based on extension

				if ( ! $saved_file_ext ) { // when unable to determine file type
					$saved_file_ext = 'txt'; // defined as txt
				}

				$uploads_dir = wp_upload_dir('ttls'); // receive folder path for upload

				if ( $uploads_dir['error'] ) { // check for errors with folder path
					return new WP_Error( '', $uploads_dir['error'] );
				}
				global $wp_filesystem;
				require_once ( ABSPATH . '/wp-admin/includes/file.php' );
				WP_Filesystem();
				if ( ! $wp_filesystem->is_writable( $uploads_dir['path'] )  ) { // check for write permissions in folder
					return new WP_Error( '', sprintf( __( 'You do not have sufficient permissions to write to folder: %s', 'ttls_translate'), $uploads_dir['path'] ) );
				}

				$new_file_name = $ticket_id.'-'.mt_rand().'.'.$saved_file_ext; // generate random filename
				$new_file_name = wp_unique_filename( $uploads_dir['path'], $new_file_name ); // confirm that generated filename is unique
				$ttls_attachment = new self; // create an example of an attachments class, as whith ajax requests this method works as a seperate function

				if ( $ttls_attachment->create_zip && ! $ttls_attachment->is_archive( $tmp_name, $saved_file_ext ) ) {
					require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
					$new_file_name = $new_file_name . '.zip';
					$saved_file_name = $saved_file_name . '.zip';
					$archive = new PclZip( $uploads_dir['path'] . $new_file_name );
					$result = $archive->create( array(
						array(
							PCLZIP_ATT_FILE_NAME => $name,
							PCLZIP_ATT_FILE_CONTENT => $wp_filesystem->get_contents( $tmp_name ),
						)
					) );
					if ( $result == 0 ) {
						return new WP_Error( '', __('Can\'t zip file.', 'ttls_translate') );
					}
				} else {
					if ( ! copy( $tmp_name, $uploads_dir['path'] . $new_file_name ) ) { // copying file to the attachments folder
						return new WP_Error( '', __('Unable to write file', 'ttls_translate') );
					}
				}

				// generate md5 for file
				$new_md5 = md5_file( $uploads_dir['path'] . $new_file_name );
				if ( ! $new_md5 ) {
					return new WP_Error( '', __('Unable to generate md5 for file', 'ttls_translate') );
				}

				// Defining permissions to files
				$wp_filesystem->chmod( $new_file_name, 0000666 );

				// Generate an array for including files in database

				$saved_attachment = array(
					'link' => trim( $uploads_dir['url'], '/' ) . '/' . $new_file_name,
					'type' => $saved_file_ext,
					'size' => filesize( $tmp_name ),
					'ticket' => $ticket_id,
					'location' => 'temp',
					'name' => $saved_file_name,
					'md5' => $new_md5,
				);

				$loaded_attachment = $ttls_attachment->create( $saved_attachment ); // include the uploaded attachment to the database

				if ( is_wp_error( $loaded_attachment ) ) { // when added successfully to database - sending the upoloaded file
					// otherwise generate an error message
					$wp_error = new WP_Error();
					foreach ( $loaded_attachment->get_error_messages() as $key => $error_string) {
						$wp_error->add( '', $error_string );
					}
					return $wp_error;
				}
				
				return $loaded_attachment;

			}

			/**
			 * the primary method for adding attachments
			 *
			 * @param      int             $ticket       	Ticket ID
			 * @param      array           $attachments  	An array with attachments
			 *                                           	uploaded
			 *
			 * @return     WP_Error|array  		An error, or an array of data with the results of the upload
			 */
			function add( $ticket = false, $attachments = array() ){
				$error = new WP_Error;
				$ticket_in_work = get_post( $ticket ); // receive ticket data

				if ( is_wp_error( $ticket_in_work ) ) { // when incorrect ticket ID provided, generate error
					$error->add( 'ttls_data_noticket', 'Unable to locate a ticket with this ID', array( 'status' => 404 ) );
					return $error;
				}

				if ( empty( $attachments ) ) { // when function didnn't get any attachments
					$error->add( 'ttls_data_attachments', 'No attachments', array( 'status' => 400 ) );
					return $error;
				}

				$response['status'] = true;
				$response['message'] = '';
				$response['attachments'] = array();

				foreach ( $attachments as $attach) { // review sent attachments

					// when an array includes ID - it originated from the admin
					// it will be handled by a call to the link() method
					if ( !isset( $attach['id'] ) ) { // when an attachment ID is specified, then it was loaded an a database entry exists
						$attach['ticket'] = $ticket_in_work->ID; // will add to data the attachment ID
						// will check if autoload is on for attachments
						if ( get_option('ttls_attachments_autoload', false) ) { // if autoload active load it with checks
							$cap_check = $this->caps->can_load_attachments();
							if ( !is_wp_error( $cap_check ) ) { // check if autoload is possible
								$autoload = $this->load( $attach ); // loading the attachment to server

								if ( !is_wp_error( $autoload ) ) { // upload results
									$attach = $autoload; // when upload successful - rewrite the variable with attachment
								} else { // if there was an error during the upload - reattach as external
									$attach['external_link'] = $attach['link']; // adding
									$response['message'] .= esc_html__( $autoload->get_error_message(), 'ttls_translate'); // logging error
									$response['status_code'][] = $autoload->get_error_code(); // logging error code
								}
							} else { // when the user is not allowed to load attachments - will add as external
								$attach['external_link'] = $attach['link']; // adding
								$response['message'] .= esc_html__( $cap_check->get_error_message(), 'ttls_translate'); // logging error
							}

						} else { // if disabled just add info
							$attach['external_link'] = $attach['link']; // when autoload is off - will add the data via internal link
						}


						// add file to DB
						if ( $attach ) { // when attachment is present
							$loaded_attachment = $this->create( $attach ); // include in database

							if ( !is_wp_error( $loaded_attachment ) ) { // when loaded in database
								$link_att = $this->link( $ticket_in_work->ID, array( array( 'id' => $loaded_attachment ) ) ); // add attachment to ticket

								$response['message'] .= '<br>' . esc_html( $link_att['message'] ); // write 'log' type
								$response['attachments'][] = $loaded_attachment; // write included attachment ID for response
							} else {
								$response['message'] .= '<br>' . esc_html__( $loaded_attachment->get_error_message(), 'ttls_translate'); // couldn't add - log error
							}
						} else {
							$response['message'] .= '<br>' . esc_html( $autoload['message'] );
						}
					} else { // when ID provided - attachment is present in database, will just link
						$link_att = $this->link( $ticket_in_work->ID, array( $attach ) );

						$response['message'] .= '<br>' . $link_att['message'];
						$response['attachments'][] = $attach;
					}
				}

				return $response;
			}


			/**
			 * Function for loading attachments
			 *
			 * @param      array     $attachment  	Attachment data
			 *
			 * @return     WP_Error  	Error loading, or result of method check_n_save
			 */
			function load( $attachment ){
				$error = new WP_Error;

				if ( !empty( $attachment['size'] ) AND $attachment['size'] > get_option('ttls_attachments_max_size', 5) * 1024 * 1024 ) {
					$error->add( 'ttls_attachment_toolarge', 'The file is too large and can not be uploaded to the server' , array( 'status' => 400 ) );
					return $error;
				}

				// load file
				if ( $attachment['link'] ) { // when there is a link for file
					// create temp file
					$url_filename = 'ttls_temp'; // temporary filename
					require_once ABSPATH . 'wp-admin/includes/file.php'; // connect WordPress file class
					$tmpfname = wp_tempnam( $url_filename ); // create a unique empty temporary file
					if ( ! $tmpfname ){ // when couldn't create
						$error->add( 'ttls_attachment_temp', 'Unable to create a temporary file', array( 'status' => 500 ) );
						return $error;
					}
					// creating loading parameters
					$args['limit_response_size'] = get_option('ttls_attachments_max_size', 5) * 1024 * 1024; // maximum attachment size (in megabytes)
					$args['timeout'] = get_option('ttls_attachments_max_time', 30); // maximum timeout
					$args['filename'] = $tmpfname; // temporary filename
					$args['stream'] = true; // as this class is being used in all examples related to loading files it was included.  More details: WP includes/class-http.php
					// load file to temp
					$response = wp_remote_get( $attachment['link'], $args ); // generate request
					// check for loading errors
					if ( 200 != wp_remote_retrieve_response_code( $response ) ){
						unlink( $tmpfname );
						if ( wp_remote_retrieve_response_code( $response ) == 404 ) {
							$error->add( 'ttls_attachment_http', 'External file not found' , array( 'status' => 404 ) );
						} else {

							$error->add( 'ttls_attachment_http', 'Error '.wp_remote_retrieve_response_code( $response ) , array( 'status' => 400 ) );
						}
						return $error;
					}

					// sending all data for testing
					return $this->check_n_save( $attachment, $tmpfname, $response  );

				} else {
					$error->add( 'ttls_attachment_nolink', 'Attachment link missing' , array( 'status' => 400 ) );
					return $error;
				}
			}

			/**
			 * Tests after loading file from external server
			 *
			 * @param      array           $old_att        Attachment data
			 * @param      string          $tmpfname       Link to temporary
			 *                                             file
			 * @param      array           $response_data  Array with results for
			 *                                             uploading file
			 *
			 * @return     WP_Error|array  An error, or an array of data with the results of the upload
			 */
			function check_n_save( $old_att, $tmpfname, $response_data ){
				$error = new WP_Error;

				// save filesize
				$saved_file_size = filesize( $tmpfname );

				// md5 check
				if ( $old_att['md5'] ) { // when provided
					$md5_check = verify_file_md5( $tmpfname, $old_att['md5'] ); // a check by a WordPress function
					if ( is_wp_error( $md5_check ) ) {
						unlink( $tmpfname );
						$error->add( 'ttls_attachment_md5', $md5_check->get_error_message() , array( 'status' => 400 ) );
						return $error;
					}
				} else {
					$error->add( 'ttls_attachment_md5', 'MD5 is not provided' , array( 'status' => 400 ) );
					return $error;
				}
				// end md5 check

				// Writing file to folder and collect the info regarding the upload for including it in the database
				$saved_file_ext = pathinfo( $old_att['link'], PATHINFO_EXTENSION );
				if ( !$saved_file_ext ) { // if file haven't extension set txt
					$saved_file_ext = 'txt';
				}

				$uploads_dir = wp_upload_dir('ttls'); // load it into uploads/ttls

				if ( $uploads_dir['error'] ) {
					$error->add( 'ttls_attachment_save', $uploads_dir['error'] , array( 'status' => 500 ) );
					return $error;
				}

				global $wp_filesystem;
				require_once ( ABSPATH . '/wp-admin/includes/file.php' );
				WP_Filesystem();
				if ( !$wp_filesystem->is_writable( $uploads_dir['path'] )  ) {
					$error->add( 'ttls_attachment_save', 'You do not have sufficient permissions to write to the attachments folder' , array( 'status' => 500 ) );
					return $error;
				}

				$new_file_name = $old_att['ticket'].'-'.mt_rand().'.'.$saved_file_ext; // generate random name
				$new_file_name = wp_unique_filename( $uploads_dir['path'], $new_file_name ); // make sure it's unique

				if ( $this->create_zip AND !$this->is_archive( $tmpfname, $saved_file_ext ) ) {
					require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
					$new_zip_name = $old_att['name'];
					$new_file_name = $new_file_name . '.zip';
					$archive = new PclZip( $uploads_dir['path'].$new_file_name );

					$result = $archive->create( array(
						array(
							PCLZIP_ATT_FILE_NAME => $new_zip_name,
							PCLZIP_ATT_FILE_CONTENT => $wp_filesystem->get_contents( $tmpfname ),
						)
					) );
					if ( $result == 0 ) {
						$error->add( 'ttls_attachment_errzip', 'Can\'t zip file.' , array( 'status' => 500 ) );
						return $error;
					}
					$old_att['name'] = $old_att['name'].".zip";
				} else {
					if ( !copy( $tmpfname, $uploads_dir['path'].$new_file_name ) ) { // copy temp file
						$error->add( 'ttls_attachment_save', 'Unable to move file' , array( 'status' => 500 ) );
						return $error;
					}
				}
				unlink( $tmpfname ); // delete temp file

				// Set correct file permissions.
				$wp_filesystem->chmod( $new_file_name, 0000666 );


				$saved_attachment['link'] = trim( $uploads_dir['url'], '/' ) . '/' . $new_file_name;
				$saved_attachment['type'] = $saved_file_ext;
				$saved_attachment['size'] = $saved_file_size;
				$saved_attachment['ticket'] = $old_att['ticket'];
				$saved_attachment['md5'] = $old_att['md5'];
				$saved_attachment['location'] = 'server';
				$saved_attachment['name'] = $old_att['name'];
				$saved_attachment['external_id'] = ( isset( $old_att['external_id'] ) ) ? $old_att['external_id'] : '';
				$saved_attachment['external_link'] = $old_att['link'];

				return $saved_attachment;
			}

			/**
			 * Create ttls_attachment with data related to the uploaded file
			 *
			 * @param      array           $attachment  	File data
			 *
			 * @return     WP_Error|array  		Error, or ttls_attachment data
			 */
			function create( $attachment ){
				$error = new WP_Error;
				$has_error = false;

				// recheck the received data
				if ( !isset( $attachment['link'] ) ) { // when link exists
					$error->add( 'ttls_attachment_nolink', 'Attachment link is missing' , array( 'status' => 400 ) );
					$has_error = true;
				}

				if ( !isset( $attachment['external_link'] ) ) { // when there is a link to an external site. if there isn't, will copy just the link
					$attachment['external_link'] = $attachment['link'];
				}

				if ( !isset( $attachment['external_id'] ) ) { // when attachment ID wasn't sent from external server
					$attachment['external_id'] = ''; // empty value, to avoid notices
				}

				if ( !isset( $attachment['type'] ) ) { // if type weren't provided - generate via link
					$attachment['type'] = pathinfo( $attachment['link'], PATHINFO_EXTENSION );
					if ( !$attachment['type'] ) { // if file doesn't have an extension - set it to txt
						$attachment['type'] = 'txt';
					}
				}

				if ( !isset( $attachment['name'] ) ) { // name must be specified
					$error->add( 'ttls_attachment_noname', 'Attachment name is missing' , array( 'status' => 400 ) );
					$has_error = true;
				}

				if ( !isset( $attachment['size'] ) ) { // size must be specified
					$error->add( 'ttls_attachment_nosize', 'Attachment size is missing' , array( 'status' => 400 ) );
					$has_error = true;
				}

				if ( !isset( $attachment['location'] ) AND empty($attachment['location']) ) { // when the attachment loaction is not set
					$attachment['location'] = 'external'; // set it as external
				}

				if ( !isset( $attachment['md5'] ) ) { // md5 should be specified
					$error->add( 'ttls_attachment_md5', 'Attachment MD5 is missing' , array( 'status' => 400 ) );
					$has_error = true;
				}

				if ( $has_error ) {
					return $error;
				}

				// assemble options list for database inclusion
				$new_attach_args = array(
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
					'post_status'    => 'publish',
					'post_title'     => wp_strip_all_tags( $attachment['name'] ),
					'post_type'      => 'ttls_attachments',
					'post_author'	 => get_current_user_id(),
					'meta_input'     => array(
						'ttls_attach_location' => $attachment['location'], // server | external | temp - it is required for adding "load to server" button
						'ttls_attach_size' => $attachment['size'], // in bytes
						'ttls_attach_type' => $attachment['type'], // it is required for visual
						'ttls_attach_link' => $attachment['link'], // link of file
						'ttls_attach_ticket' => $attachment['ticket'], // will need for addon of control
						'ttls_attach_external_id' => $attachment['external_id'], // ID of attachment of same file on client server
						'ttls_attach_external_link' => $attachment['external_link'], // same file on client server
						'ttls_attach_external_md5' => strval( $attachment['md5'] ), // md5 of file in link
					),
				);

				$new_attach_id = wp_insert_post( $new_attach_args ); // write to database

				if ( is_wp_error( $new_attach_id ) ) { // confirm that the data was transmitted correctly to database
					$error->add( 'ttls_attachment_fail', 'Received an error while adding the attachment to the database' , array( 'status' => 500 ) );
					return $error;
				} else {
					return $new_attach_id;
				}
			}


			/**
			 * link attachment to the ticket - after linking the temporary attachment it will become
			 * permanent (temp->server)
			 *
			 * @param      int             $ticket       	Ticket ID
			 * @param      array           $attachments  	Attachments array
			 *
			 * @return     WP_Error|array  		An error, or an array of data with the results
			 */
			function link( $ticket = false, $attachments = array() ){
				$error = new WP_Error;
				$has_error = false;
				if ( $ticket ) { // when ticket ID is spcified
					if ( !empty( $attachments ) ) { // when attachments are given
						$response['message'] = false;
						$ticket = get_post( $ticket ); // receive ticket data
						foreach ( $attachments as $key => $att) { // review the attachments
							// when the attachment is temporary - bring it to server
							if ( get_post_meta( $att['id'], 'ttls_attach_location', true) == 'temp') {
								update_post_meta( $att['id'], 'ttls_attach_location', 'server');
							}
							// link attachment to ticket
							add_post_meta( $ticket->ID, 'ttls_attachment', $att['id']);
							if ( $ticket->post_parent ) { // when the attachment was a part of a response
								// general attachments list
								add_post_meta( $ticket->post_parent, 'ttls_all_attachment', $att['id']);

								update_post_meta( $att['id'], 'ttls_attach_ticket', $ticket->post_parent);
								// link ticket to attachment
								// it is required as,
								// the link is a 2 way connection attachment->ticket and ticket->attachment
							} else {
								// and to the general attachments list
								add_post_meta( $ticket->ID, 'ttls_all_attachment', $att['id']);
								update_post_meta( $att['id'], 'ttls_attach_ticket', $ticket->ID);
							}
						}
						if ( !$response['message'] ) {
							$response['message'] = esc_html__('All attachments are linked', 'ttls_translate');
						}
						return $response;
					} else {
						$error->add( 'ttls_data_attachments', 'Files were not transmitted', array( 'status' => 400 ) );
						return $error;
					}
				} else {
					$error->add( 'ttls_data_noticketid', 'Ticket ID not provided', array( 'status' => 400 ) );
					return $error;
				}
			}



			/**
			 * manual attachments upload wp_action - ttls_manual_load_attachment
			 */
			static function manual_load_attachment(){
				if ( isset( $_POST['attachment'] ) AND $_POST['attachment'] ) { // when attachment ID weren't specified
					$attachment_id = sanitize_key( $_POST['attachment'] );
					$ttls_attach = new TTLS_Attachments(); // as the ajax call didn't define example
					$attach = $ttls_attach->get( $attachment_id ); // received attachment data
					if ( !is_wp_error( $attach ) ) { // received successfully
						$load_result = $ttls_attach->load( $attach ); // loading the attachment
						if ( !is_wp_error( $load_result ) ) { // when successfully
							// renew data
							update_post_meta( $attach['id'], 'ttls_attach_location', $load_result['location'] );
							update_post_meta( $attach['id'], 'ttls_attach_size', $load_result['size'] );
							update_post_meta( $attach['id'], 'ttls_attach_type', $load_result['type'] );
							update_post_meta( $attach['id'], 'ttls_attach_link', $load_result['link'] );
							update_post_meta( $attach['id'], 'ttls_attach_external_link', $load_result['external_link'] );
							// with response generated attachment from server
							wp_send_json_success( $ttls_attach->print_single( $attach['id'] ) );
						} else { // otherwise logging an inclusion error
							wp_send_json_error( array( 'message' => esc_html__( $load_result->get_error_message(), 'ttls_translate') ) );
						}
					} else {
						wp_send_json_error( array( 'message' => esc_html__( $attach->get_error_message(), 'ttls_translate') ) ); // error receiving attachment data
					}
				} else {
					wp_send_json_error( array( 'message' => esc_html__('An attachment with the specified ID does not exist', 'ttls_translate' ) ) );
				}
				wp_die();
			}


			/**
			 * manual removal of the temporary attachment wp_action - ttls_delete_response_attachment
			 */
			static function delete_temp_files(){
				// if ttls_attach_location is temp
				if ( isset( $_POST['attachment'] ) AND $_POST['attachment'] ) { // when attachment exists
					$attachment_id = sanitize_key( $_POST['attachment'] );
					$attach = get_post( $attachment_id );

					if ( $attach->post_author == get_current_user_id() ) { // when the attachment was created by current user

						if ( $attach->ttls_attach_location == 'temp' ) { // when the attachment has a temporary status and it was sent to client

							if ( $attach->ttls_attach_thumb ) { // when there is a preview thumb, will delete the thumb
								$thumb_path = $_SERVER['DOCUMENT_ROOT'] . parse_url( $attach->ttls_attach_thumb, PHP_URL_PATH );
								unlink( $thumb_path ); // delete image thumb
							}
							$link_path = $_SERVER['DOCUMENT_ROOT'] . parse_url( $attach->ttls_attach_link, PHP_URL_PATH );
							unlink( $link_path ); // delete the primary file
							if ( wp_delete_post( $attach->ID, true ) ) { // delete the info from database
								wp_send_json_success( array( 'message' => esc_html__('Attachment deleted', 'ttls_translate') ) );
							} else {
								wp_send_json_error( new WP_Error( 'ttls_attachment_server', 'Unable to delete the attachment from the database', array( 'status' => 500 ) ) );
							}

						} else {
							wp_send_json_error( new WP_Error( 'ttls_attachment_secure', 'Was able to just delete the temporary attachment', array( 'status' => 403 ) ) );
						}
					} else {
						wp_send_json_error( new WP_Error( 'ttls_attachment_secure', 'This is not your attachment', array( 'status' => 403 ) ) );
					}
				} else {
					wp_send_json_error( new WP_Error( 'ttls_attachment_noattachment', 'An attachment with the specified ID is not found', array( 'status' => 404 ) ) );
				}
				wp_die();
			}
		}
	}