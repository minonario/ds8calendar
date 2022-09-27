<?php

class DS8Calendar_Admin {
  
	private static $initiated = false;
	private static $notices   = array();

	public static function init() {
		if ( ! self::$initiated ) {
			self::init_hooks();
		}
	}

	public static function init_hooks() {
		// The standalone stats page was removed in 3.0 for an all-in-one config and stats page.
		// Redirect any links that might have been bookmarked or in browser history.
		if ( isset( $_GET['page'] ) && 'ds8calendar-stats-display' == $_GET['page'] ) {
			wp_safe_redirect( esc_url_raw( self::get_page_url( 'stats' ) ), 301 );
			die;
		}

		self::$initiated = true;

		add_action( 'admin_init', array( 'DS8Calendar_Admin', 'admin_init' ) );
		add_action( 'admin_menu', array( 'DS8Calendar_Admin', 'admin_menu' ), 5 );
		//add_action( 'admin_notices', array( 'DS8Calendar_Admin', 'display_notice' ) );
		add_action( 'admin_enqueue_scripts', array( 'DS8Calendar_Admin', 'load_resources' ) );
		add_filter( 'plugin_action_links', array( 'DS8Calendar_Admin', 'plugin_action_links' ), 10, 2 );
		add_filter( 'plugin_action_links_'.plugin_basename( plugin_dir_path( __FILE__ ) . 'ds8calendar.php'), array( 'DS8Calendar_Admin', 'admin_plugin_settings_link' ) );
		//add_filter( 'all_plugins', array( 'DS8Calendar_Admin', 'modify_plugin_description' ) );
                
                add_action( 'add_meta_boxes', array( 'DS8Calendar_Admin', 'add_meta_box' ) );
		add_action( 'save_post_calendar',  array( 'DS8Calendar_Admin', 'save' ) );
	}
        
        /**
	 * Adds the meta box container.
	 */
	public static function add_meta_box( $post_type ) {
                add_meta_box(
                        'calendar-meta-box',
                        __( 'Date in the Calendar', 'ds8calendar' ),
                        array( 'DS8Calendar_Admin', 'render_meta_box_content' ),
                        array('calendar'),
                        'advanced',
                        'high'
                );
	}
        
        /**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public static function render_meta_box_content( $post ) {

		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'myplugin_inner_custom_box', 'myplugin_inner_custom_box_nonce' );

		// Use get_post_meta to retrieve an existing value from the database.
		$result = get_post_meta( $post->ID, '_ds8_calendar_meta_value_key', true );
                $combined = json_decode($result);

		// Display the form, using the current value.
		?>
                <table class='custom-fd-dates'>
                  <thead>
                    <tr>
                      <td>
                        <label>Row</label>
                      </td>
                      <td>
                        <label>Date</label>
                      </td>
                      <td>
                        <label>Description</label>
                      </td>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $count = 1;
                    foreach ($combined as $date => $description){
                      ?>
                    <tr class='ds8-row' data-id="<?php echo $count; ?>">
                      <td class='rowid'><?php echo $count; ?></td>
                      <td class='fddate'><input class="fd-datepicker" type="text" id="myplugin_new_field_<?php echo $count; ?>_['date']" name="myplugin_new_field[date][]" value="<?php echo esc_attr( $date ); ?>" size="25" /></td>
                      <td class='fddescrip'>
                        <div class='ds8-input'>
                          <input type="text" id="myplugin_new_field_<?php echo $count; ?>_['description']" name="myplugin_new_field[description][]" value="<?php echo esc_attr( $description ); ?>" />
                        </div>
                      </td>
                      <td class='ds8-row-handle remove'><a class="ds8-icon -minus small ds8-js-tooltip" href="#" data-event="remove-row" title="Remove row">X</a></td>
                    </tr>
                    
                    <?php
                      $count++;
                    }
                    ?>
                  </tbody>
                </table>
                <div style='margin-top: 20px;position:relative;text-align: right'>
                  <input type="button" name="addfd" id="newfd-submit" class="button" value="Add new date">
                </div>
		<?php
	}
        
        /**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public static function save( $post_id ) {

		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['myplugin_inner_custom_box_nonce'] ) ) {
			return $post_id;
		}

		$nonce = $_POST['myplugin_inner_custom_box_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'myplugin_inner_custom_box' ) ) {
			return $post_id;
		}

		/*
		 * If this is an autosave, our form has not been submitted,
		 * so we don't want to do anything.
		 */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
		}

		/* OK, it's safe for us to save the data now. */

		// Sanitize the user input.
		//$mydata = sanitize_text_field( $_POST['myplugin_new_field'] );
                $combined = array_combine($_POST['myplugin_new_field']['date'], $_POST['myplugin_new_field']['description']);
                $json_res = json_encode($combined,JSON_UNESCAPED_UNICODE);
		// Update the meta field.
		update_post_meta( $post_id, '_ds8_calendar_meta_value_key', $json_res );
	}

	public static function admin_init() {
		if ( get_option( 'Activated_DS8Calendar' ) ) {
			delete_option( 'Activated_DS8Calendar' );
			if ( ! headers_sent() ) {
				wp_redirect( add_query_arg( array( 'page' => 'ds8calendar-key-config', 'view' => 'start' ), class_exists( 'Jetpack' ) ? admin_url( 'admin.php' ) : admin_url( 'options-general.php' ) ) );
			}
		}
                
                // JLMA - FEATURE 01-09-2022
                if(isset($_POST) && isset($_POST['option_page']) &&  $_POST['option_page'] === 'ds8-settings-group') {
                    update_option('plugin_permalinks_flushed', 0);
                }
                
                register_setting('ds8-settings-group', 'ds8_calendar_page');
		load_plugin_textdomain( 'ds8calendar' );
	}

	public static function admin_menu() {
			self::load_menu();
	}

	public static function admin_head() {
		if ( !current_user_can( 'manage_options' ) )
			return;
	}
	
	public static function admin_plugin_settings_link( $links ) { 
  		$settings_link = '<a href="'.esc_url( self::get_page_url() ).'">'.__('Settings', 'ds8calendar').'</a>';
  		array_unshift( $links, $settings_link ); 
  		return $links; 
	}

	public static function load_menu() {
		
                //$hook = add_options_page( __('DS8 Calendar', 'ds8calendar'), __('DS8 Calendar', 'ds8calendar'), 'manage_options', 'ds8calendar-key-config', array( 'DS8Calendar_Admin', 'display_page' ) );
          
          add_menu_page(__('DS8 Calendar', 'ds8calendar'), __('DS8 Calendar', 'ds8calendar'), 'manage_options', 'ds8calendar-key-config', array( 'DS8Calendar_Admin', 'display_page' ), null);
          add_submenu_page( 'edit.php?post_type=calendar', 'Calendar Import', 'Import', 'manage_options', 'import-calendar', array( 'DS8Calendar_Admin', 'ds8calendar_view' ));
		
		/*if ( $hook ) {
			add_action( "load-$hook", array( 'DS8Calendar_Admin', 'admin_help' ) );
		}*/
	}
        
        // Hook into WordPress init; this function performs report generation when the admin form is submitted
        public static function ds8_on_init() {
                global $pagenow;
                // Check if we are in admin and on the report page
                if (!is_admin())
                        return;
                if ( ! function_exists( 'wp_handle_upload' ) ) {
                    require_once( ABSPATH . 'wp-admin/includes/file.php' );
                }
                //admin.php when is options
                if ($pagenow == 'edit.php' && !empty($_POST['uploadds8']) && $_POST['uploadds8'] === "Upload") {
                    // Upload file
                    if(isset($_POST['uploadds8'])){
                        if($_FILES['file']['name'] != ''){
                            $uploadedfile = $_FILES['file'];
                            $upload_overrides = array( 'test_form' => false );
                            $uploaded = wp_handle_upload( $uploadedfile, $upload_overrides );
                            if (is_wp_error($uploaded)) {
                                //echo "Error uploading file: " . $uploaded->get_error_message();
                            } else {
                                //echo "File upload successful!";
                                self::get_parsed_excel($uploaded);
                            }
                        }
                    }
                }
        }
        
        public static function get_parsed_excel($uploaded){

            try {
                
                $row = 1;
                $combined = array();
                if (($handle = fopen($uploaded['file'], "r")) !== FALSE) {
                    while (($data = fgetcsv($handle,null, ";")) !== FALSE) {
                        $num = count($data);
                        $line = array_filter(explode(",", $data[0]));
                        $parsed = date_parse_from_format("j/n/Y", $line[1]);
                        
                        //get_page_by_path
                        //url_to_postid
                        //post_exists
                        $post_exists = post_exists($line[0].' '.$parsed['year'], '', '', '');
                        $url_exists = url_to_postid($line[0].'-'.$parsed['year']);
                        
                        if ($post_exists > 0 ) {
                          $custom_fields = get_post_custom_values('_ds8_calendar_meta_value_key', $post_exists);
                        }else{
                          //NEW DATES
                          if (isset($combined[$line[0]])){
                             $combined[$line[0]][$parsed['year']] += array(trim($line[1]) => trim($line[2]) );
                          }else{
                            $combined[$line[0]][$parsed['year']] = array( trim($line[1]) => trim($line[2]) );
                          }
                        }
                        
                        echo "<p> $num fields in line $row: <br /></p>\n";
                        $row++;
                        for ($c=0; $c < $num; $c++) {
                            echo $data[$c] . "<br />\n";
                        }
                    }
                    fclose($handle);
                    
                    foreach ($combined as $country => $years){
                      foreach ($years as $year => $dates){
                        
                        $post_exists = post_exists($country.' '.$year, '', '', '');
                        if ($post_exists == 0 ) {
                          $calendar_post = array(
                                      'post_title'    => wp_strip_all_tags( $country.' '.$year ),
                                      'post_content'  => '', //$_POST['post_content'],
                                      'post_status'   => 'publish',
                                      'post_type'     => 'calendar'
                                    );

                          // Insert the post into the database
                          $post_id = wp_insert_post( $calendar_post );
                          $json_res = json_encode($dates,JSON_UNESCAPED_UNICODE);
                          update_post_meta( $post_id, '_ds8_calendar_meta_value_key', $json_res );
                        }
                          
                      }
                    }
                }
            }catch (Exception $e) {
                //TO-DO
            }

        }
        
        public static function ds8calendar_view() {
        
            ?>
            <div class="wrap">
                <h1>Import Calendar (CSV Format)</h1>

                <?php
                $url = add_query_arg(array(
                'page'=> basename(__FILE__),
                'page'=>'massive-excel-inv'
               ), admin_url('admin.php'));
                ?>

                <?php 

                self::ds8_on_init();

                ?>

                <form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>" enctype='multipart/form-data'>

                    <table>
                            <tr>
                                    <td>Load new Calendar...</td>
                                    <td><input type='file' name='file' onchange="ValidateSingleInput(this)"></td>
                            </tr>
                            <tr>
                                    <td>&nbsp;</td>
                                    <td><?php submit_button('Upload', '', 'uploadds8', false); ?></td>
                            </tr>
                    </table>

                </form>
            </div>
        <?php
        }
        
        public static function display_page() {
		if ( ( isset( $_GET['view'] ) && $_GET['view'] == 'start'  ) || $_GET['page'] == 'ds8calendar-key-config' ){
			//self::display_start_page();
                        //DS8Calendar::view( 'start' );
                        // FEATURE JLMA 29-08-2022
                        $options = array(
                            array("name" => "Página tabla",
                                "desc" => "Para la creación y validación de las URL's del shortcode",
                                "id" => "ds8_calendar_page",
                                "type" => "select-page",
                                "std" => ""
                            )
                        );
                        DS8Calendar::view( 'start', array(
                                'front_page_elements' => null,
                                'options' => $options
                        ) );
                }
	}

	public static function load_resources() {
		global $hook_suffix;

		if ( in_array( $hook_suffix, apply_filters( 'ds8calendar_admin_page_hook_suffixes', array(
			'index.php', # dashboard
			'post.php',
                        'post-new.php',
			'plugins.php',
		) ) ) ) {
                        $screen = get_current_screen();
                  
			wp_register_style( 'ds8calendar.css', plugin_dir_url( __FILE__ ) . '_inc/ds8calendar.css', array(), DS8CALENDAR_VERSION );
			wp_enqueue_style( 'ds8calendar.css');
                        
                        wp_enqueue_style( 'jquery-ui-smoothness', // wrapped for brevity
                        '//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css', [], null );

                        if( is_object( $screen ) && 'calendar' == $screen->post_type ){
                          wp_register_script( 'ds8calendar.js', plugin_dir_url( __FILE__ ) . '_inc/ds8calendar.js', array('jquery','jquery-ui-datepicker'), DS8CALENDAR_VERSION );
                          wp_enqueue_script( 'ds8calendar.js' );
                        }
		
			$inline_js = array(
				'comment_author_url_nonce' => wp_create_nonce( 'comment_author_url_nonce' ),
				'strings' => array(
					'Remove this URL' => __( 'Remove this URL' , 'ds8calendar'),
					'Removing...'     => __( 'Removing...' , 'ds8calendar'),
					'URL removed'     => __( 'URL removed' , 'ds8calendar'),
					'(undo)'          => __( '(undo)' , 'ds8calendar'),
					'Re-adding...'    => __( 'Re-adding...' , 'ds8calendar'),
				)
			);

			if ( isset( $_GET['ds8calendar_recheck'] ) && wp_verify_nonce( $_GET['ds8calendar_recheck'], 'ds8calendar_recheck' ) ) {
				$inline_js['start_recheck'] = true;
			}

			if ( apply_filters( 'ds8calendar_enable_mshots', true ) ) {
				$inline_js['enable_mshots'] = true;
			}

			wp_localize_script( 'ds8calendar.js', 'WPDS8Calendar', $inline_js );
		}
	}	

	public static function plugin_action_links( $links, $file ) {
		if ( $file == plugin_basename( plugin_dir_url( __FILE__ ) . '/ds8calendar.php' ) ) {
			$links[] = '<a href="' . esc_url( self::get_page_url() ) . '">'.esc_html__( 'Settings' , 'ds8calendar').'</a>';
		}

		return $links;
	}

	public static function display_alert() {
		DS8Calendar::view( 'notice', array(
			'type' => 'alert',
			'code' => (int) get_option( 'ds8calendar_alert_code' ),
			'msg'  => get_option( 'ds8calendar_alert_msg' )
		) );
	}
        
        public static function get_page_url( $page = 'config' ) {

		$args = array( 'page' => 'edit.php?post_type=calendar&page=import-calendar');//'ds8calendar-key-config' );

		$url = add_query_arg( $args,  admin_url( 'options-general.php' ) );

		return $url;
	}
        
        public static function plugin_deactivation( ) {
          
        }
        
        public static function create_form($options) {
            foreach ($options as $value) {
                switch ($value['type']) {
                    case "textarea";
                        self::create_section_for_textarea($value);
                        break;
                    case "text";
                        self::create_section_for_text($value);
                        break;
                    case "select":
                        self::create_section_for_taxonomy_select($value);
                        break;
                    case "select-page":
                        self::combo_select_page_callback($value);
                        break;
                }
            }
        }
        
        public static function ds8_get_formatted_page_array() {

            $ret = array();
            $pages = get_pages();
            if ($pages != null) {
                foreach ($pages as $page) {
                    $ret[$page->ID] = array("name" => $page->post_title, "id" => $page->ID);
                }
            }

            return $ret;
        }

        public static function combo_select_page_callback($value) {
            echo '<tr valign="top">';
            echo '<th scope="row">' . $value['name'] . '</th>';
            echo '<td>';

            echo "<select id='" . $value['id'] . "' class='post_form' name='" . $value['id'] . "'>\n";
            echo "<option value='0'>-- Select page --</option>";

            $pages = get_pages();

            foreach ($pages as $page) {
                $checked = ' ';

                if (get_option($value['id']) == $page->ID) {
                    $checked = ' selected="selected" ';
                } else if (get_option($value['id']) === FALSE && $value['std'] == $page->ID) {
                    $checked = ' selected="selected" ';
                } else {
                    $checked = '';
                }

                echo '<option value="' . $page->ID . '" ' . $checked . '/>' . $page->post_title . "</option>\n";
            }
            echo "</select>";
            echo "</td>";
            echo '</tr>';
        }

        public static function create_section_for_taxonomy_select($value) {
            echo '<tr valign="top">';
            echo '<th scope="row">' . $value['name'] . '</th>';
            echo '<td>';

            echo "<select id='" . $value['id'] . "' class='post_form' name='" . $value['id'] . "'>\n";
            echo "<option value='0'>-- Seleccione --</option>";

            foreach ($value['options'] as $option_value => $option_list) {
                $checked = ' ';

                if (get_option($value['id']) == $option_value) {
                    $checked = ' selected="selected" ';
                } else if (get_option($value['id']) === FALSE && $value['std'] == $option_list) {
                    $checked = ' selected="selected" ';
                } else {
                    $checked = '';
                }

                echo '<option value="' . $option_value . '" ' . $checked . '/>' . $option_list . "</option>\n";
            }
            echo "</select>";
            echo "</td>";
            echo '</tr>';
        }

        public static function create_section_for_textarea($value) {
            echo '<tr valign="top">';
            echo '<th scope="row">' . $value['name'] . '</th>';

            $text = "";
            if (get_option($value['id']) === FALSE) {
                $text = $value['std'];
            } else {
                $text = get_option($value['id']);
            }

            echo '<td><textarea rows="6" cols="80" id="' . $value['id'] . '" name="' . $value['id'] . '">'.strip_tags($text).'</textarea></td>';
            echo '</tr>';
        }

        public static function create_section_for_text($value) {
            echo '<tr valign="top">';
            echo '<th scope="row">' . $value['name'] . '</th>';

            $text = "";
            if (get_option($value['id']) === FALSE) {
                $text = $value['std'];
            } else {
                $text = get_option($value['id']);
            }

            echo '<td><input type="text" id="' . $value['id'] . '" name="' . $value['id'] . '" value="' . $text . '" /></td>';
            echo '</tr>';
        }
	
}
