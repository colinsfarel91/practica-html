<?php

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

if( !function_exists( 'cp_get_form_hidden_fields' ) ) {
	function cp_get_form_hidden_fields( $a ){
		/** = Form options
		 *	Mailer - We will also optimize this by filter. If in any style we need the form then apply filter otherwise nope.
		 *-----------------------------------------------------------*/

		$mailer 			= explode( ":", $a['mailer'] );
		$on_success_action  = $on_success = $on_redirect = '';
		$mailer_id 			= $list_id = '';
		$style_id 			= isset( $a['style_id'] ) ? esc_attr( $a['style_id'] ) : '';

		if( $a['mailer'] !== '' && $a['mailer'] != "custom-form" ) {
		    $smile_lists = get_option('smile_lists');

		    $list = ( isset( $smile_lists[$a['mailer']] ) ) ? $smile_lists[$a['mailer']] : '';
		    $mailer = ( $list != '' ) ? $list['list-provider'] : '';

		    if( $mailer === 'Convert Plug' ) {
		        $mailer_id = 'cp';
		        $list_id = esc_attr( $a['mailer'] );
		    } else {
		        $mailer_id = strtolower($mailer);
		        $list_id = ( $list != '' ) ? $list['list'] : '';
		    }

		    $on_success = ( isset($a['on_success']) ) ? stripslashes( $a['on_success'] ) : '';
		    if( isset($on_success) && $on_success === "redirect" )  {
		    	$on_success_action = esc_url( $a['redirect_url'] );
		    	if( isset($a['on_redirect']) && $a['on_redirect'] !== '' ) {
		    		$on_redirect .= '<input type="hidden" name="redirect_to" value="'.esc_url( $a['on_redirect'] ).'" />';
		    		if( $a['on_redirect'] === 'download' && isset($a['download_url']) && $a['download_url']!=='' ){
		    		$on_redirect .= '<input type="hidden" name="download_url" value="'.esc_url( $a['download_url'] ).'" />';
		    		}

		    	}
		    } else if( isset( $a['success_message'] ) ) {
		    	$on_success_action = do_shortcode( html_entity_decode( stripcslashes( htmlspecialchars( $a['success_message'] ) ) ) );
		    }
		}

		ob_start();
		$uid = md5(uniqid(rand(), true));

		global $wp;
		$current_url = home_url(add_query_arg(array(),$wp->request));

		wp_nonce_field( 'cp-submit-form-'.$style_id );
		?>
		<input type="hidden" name="cp-page-url" value="<?php echo esc_url( $current_url ); ?>" />
		<input type="hidden" name="param[user_id]" value="cp-uid-<?php echo $uid; ?>" />
        <input type="hidden" name="param[date]" value="<?php echo esc_attr( date("j-n-Y") ); ?>" />
		<input type="hidden" name="list_parent_index" value="<?php echo isset( $a['mailer'] ) ? $a['mailer'] : ''; ?>" />
		<input type="hidden" name="action" value="<?php echo $mailer_id; ?>_add_subscriber" />
        <input type="hidden" name="list_id" value="<?php echo $list_id; ?>" />
        <input type="hidden" name="style_id" value="<?php echo $style_id; ?>" />
        <input type="hidden" name="msg_wrong_email" value='<?php echo isset( $a['msg_wrong_email'] ) ? do_shortcode( html_entity_decode( stripcslashes( htmlspecialchars( $a['msg_wrong_email'] ) ) ) ) : ''; ?>' />
        <input type="hidden" name="<?php echo $on_success; ?>" value="<?php echo esc_attr($on_success_action); ?>" />
       
<?php
        $html = ob_get_clean();
        echo $html;
	}
}

add_filter( 'cp_form_hidden_fields', 'cp_get_form_hidden_fields', 10, 1 );

/**
 *	Filter 'cp_valid_mx_email' for MX - Email validation
 *
 * @since 1.0
 */
add_filter( 'cp_valid_mx_email', 'cp_valid_mx_email_init' );

if( !function_exists( "cp_valid_mx_email_init" ) ) {
	function cp_valid_mx_email_init( $email ) {
		//	Proceed If global check box enabled for MX Record from @author tab
		if( apply_filters( 'cp_enabled_mx_record', $email ) ) {
			if( cp_is_valid_mx_email($email) ) {
				return true;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}
}

if( !function_exists( "cp_is_valid_mx_email" ) ){
	function cp_is_valid_mx_email( $email, $record = 'MX' ) {
		list( $user, $domain ) = explode( '@', $email );
		return checkdnsrr( $domain, $record );
	}
}

/**
 * 	Check MX record globally enabled or not [Setting found in @author tab]
 */
add_filter( 'cp_enabled_mx_record', 'cp_enabled_mx_record_init' );
function cp_enabled_mx_record_init() {
	$data = get_option( 'convert_plug_settings' );
	$is_enable_mx_records = isset($data['cp-enable-mx-record']) ? $data['cp-enable-mx-record'] : 0;
	if( $is_enable_mx_records ) {
		return true;
	} else {
		return false;
	}
}

/**
 * 	Check if style is visible here or not
 * @Since 2.1.0
 */
function cp_is_style_visible($settings) {

	global $post;
	$post_id    = ( !is_404() && !is_search() && !is_archive() && !is_home() ) ? @$post->ID : '';
	$category   = get_queried_object_id();	
	$cat_ids    = wp_get_post_categories( $post_id );	
	$post_type  = get_post_type( $post );
	$taxonomies = get_post_taxonomies( $post );
	$term_cat_id = '';
	$tag_arr = array();
	$show_module = true;
	$taxtterm_id = get_the_tags(); //tags
	if ($taxtterm_id) {
	  foreach($taxtterm_id as $tag) {	   
	    array_push($tag_arr, $tag->term_id);
	  }
	}
	$hide_on_devices 	= isset($settings['hide_on_device']) ?  apply_filters('smile_render_setting', $settings['hide_on_device']) : '';
	if( $hide_on_devices !== '' ){
		$show_module = cplus_is_current_device($hide_on_devices);
	}

	if( $show_module ){
		$global_display		= isset($settings['global']) ? apply_filters('smile_render_setting', $settings['global']) : '';

		$exclude_from 		= isset($settings['exclude_from']) ? apply_filters('smile_render_setting', $settings['exclude_from']) : '';
		$exclude_from		= str_replace( "post-", "", $exclude_from );
		$exclude_from		= str_replace( "tax-", "", $exclude_from );
		$exclude_from		= str_replace( "special-", "", $exclude_from );
		$exclude_from 		= ( !$exclude_from == "" ) ? explode( ",", $exclude_from ) : '';

		$exclusive_on 		= isset($settings[ 'exclusive_on' ]) ? apply_filters('smile_render_setting', $settings[ 'exclusive_on' ]) : '';
		$exclusive_on		= str_replace( "post-", "", $exclusive_on );
		$exclusive_on		= str_replace( "tax-", "", $exclusive_on );
		$exclusive_on		= str_replace( "special-", "", $exclusive_on );
		$exclusive_on 		= ( !$exclusive_on == "" ) ? explode( ",", $exclusive_on ) : '';

		// exclude post type
		$exclude_cpt 		= isset($settings[ 'exclude_post_type' ]) ? apply_filters('smile_render_setting', $settings[ 'exclude_post_type' ]) : '';
		$exclude_cpt		= str_replace( "post-", "", $exclude_cpt );
		$exclude_cpt		= str_replace( "tax-", "", $exclude_cpt );
		$exclude_cpt		= str_replace( "special-", "", $exclude_cpt );
		$exclude_cpt 		= ( !$exclude_cpt == "" ) ? explode( ",", $exclude_cpt ) : '';

		// exclusive taxonomy
		$exclusive_tax 		= isset($settings[ 'exclusive_post_type' ]) ? apply_filters('smile_render_setting', $settings[ 'exclusive_post_type' ]) : '';

		$exclusive_tax		= str_replace( "post-", "", $exclusive_tax );
		$exclusive_tax		= str_replace( "tax-", "", $exclusive_tax );
		$exclusive_tax		= str_replace( "special-", "", $exclusive_tax );
		$exclusive_tax 		= ( !$exclusive_tax == "" ) ? explode( ",", $exclusive_tax ) : '';

		if( !$global_display ){
			if( !$settings['enable_custom_class'] ) {
				$settings['custom_class'] = 'priority_modal';
				$settings['enable_custom_class'] = true;
			} else {
				$settings['custom_class'] = $settings['custom_class'].',priority_modal';
			}
		}

		$show_for_logged_in = isset($settings['show_for_logged_in'] ) ? $settings['show_for_logged_in'] : '';
		
		
		$all_users = isset($settings['all_users'] ) ? $settings['all_users'] : '';

		if( $all_users ){
			$show_for_logged_in = 0;
		}

		if( $global_display ) {
			$display = true;
			if( is_404() ){
				if( is_array( $exclude_from ) && in_array( '404', $exclude_from ) ){
					$display = false;
				}
			}
			if( is_search() ){
				if( is_array( $exclude_from ) && in_array( 'search', $exclude_from ) ){
					$display = false;
				}
			}
			if( is_front_page() ){
				if( is_array( $exclude_from ) && in_array( 'front_page', $exclude_from ) ){
					$display = false;
				}
			}
			if( is_home() ){
				if( is_array( $exclude_from ) && in_array( 'blog', $exclude_from ) ){
					$display = false;
				}
			}
			if( is_author() ){
				if( is_array( $exclude_from ) && in_array( 'author', $exclude_from ) ){
					$display = false;
				}
			}			

			if( is_archive() ){
				$term_id = '';
				$obj = get_queried_object();
				if( $obj !=='' && $obj !== null ){
					if( isset($obj->term_id) ) {
						$term_id = $obj->term_id;
					}
				}
				
				//check if this woocomerce archive page
				if ( function_exists( 'is_shop' ) ) {
					if( is_shop() ){
						$term_id = woocommerce_get_page_id('shop');	
					}
				}

				if( is_array( $exclude_from ) && in_array( $term_id, $exclude_from ) ){
					$display = false;
				} elseif( is_array( $exclude_from ) && in_array( 'archive', $exclude_from ) ){
					$display = false;
				}
			}

			if( $post_id ) {
				if( is_array( $exclude_from ) && in_array( $post_id, $exclude_from ) ){
					$display = false;
				}
			}

			if( !empty( $cat_ids ) ) {	
				foreach( $cat_ids as $cat_id ){				
					$term = get_term_by('id', $cat_id, 'category') ;
					if( isset($term->term_id) ) {
						$term_cat_id = $term->term_id;
					}
					//check for cat
					if( is_array( $exclude_from ) && in_array( $term_cat_id, $exclude_from ) ){
						$display = false;
					}
				}
			}
			//check for tag
			if( !empty( $tag_arr ) ) {	
				foreach( $tag_arr as $tag_id ){				
					if( is_array( $exclude_from ) && in_array( $tag_id, $exclude_from ) ){
						$display = false;
					}
				}
			}

			if( !empty( $exclude_cpt ) && is_array( $exclude_cpt ) ){
				foreach( $exclude_cpt as $taxonomy ) {
					$taxonomy = str_replace( "cp-", "", $taxonomy );

					if( is_singular($taxonomy) ) {
						$display = false;
					}

					if( is_category($taxonomy) ){
						$display = false;
					}

					if( is_tag($taxonomy) ){
						$display = false;
					}

					if( is_tax($taxonomy) ){
						$display = false;
					}
				}
			}

		} else {
			$display = false;

			if( is_array( $exclusive_on ) && !empty( $exclusive_on ) ){
				foreach( $exclusive_on as $page ){
					if( is_page( $page ) ){
						$display = true;
					}
				}
			}

			if( is_404() ){
				if( is_array( $exclusive_on ) && in_array( '404', $exclusive_on ) ){
					$display = true;
				}
			}
			if( is_search() ){
				if( is_array( $exclusive_on ) && in_array( 'search', $exclusive_on ) ){
					$display = true;
				}
			}
			if( is_front_page() ){
				if( is_array( $exclusive_on ) && in_array( 'front_page', $exclusive_on ) ){
					$display = true;
				}
			}
			if( is_home() ){
				if( is_array( $exclusive_on ) && in_array( 'blog', $exclusive_on ) ){
					$display = true;
				}
			}
			if( is_author() ){
				if( is_array( $exclusive_on ) && in_array( 'author', $exclusive_on ) ){
					$display = true;
				}
			}
			if( is_archive() ){
				$obj = get_queried_object();
				$term_id ='';
				if( $obj !=='' &&  $obj !== null){
					$term_id = $obj->term_id;
				}

				//check if this woocomerce archive page
				if ( function_exists( 'is_shop' ) ) {
					if( is_shop() ){
						$term_id = woocommerce_get_page_id('shop');	
					}
				}
				
				if( is_array( $exclusive_on ) && in_array( $term_id, $exclusive_on ) ){
					$display = true;
				} elseif( is_array( $exclusive_on ) && in_array( 'archive', $exclusive_on ) ){
					$display = true;
				}
			}

			if( $post_id ) {
				if( is_array( $exclusive_on ) && in_array( $post_id, $exclusive_on ) ){
					$display = true;
				}
			}

			if( !empty( $cat_ids ) ) {
				foreach( $cat_ids as $cat_id ){
					$term = get_term_by('id', $cat_id, 'category') ;
					if( isset($term->term_id) ) {
						$term_cat_id = $term->term_id;
					}
					if( is_array( $exclusive_on ) && in_array( $term_cat_id, $exclusive_on ) ){
						$display = true;
					}
				}
			}
			//check for tag
			if( !empty( $tag_arr ) ) {	
				foreach( $tag_arr as $tag_id ){				
					if( is_array( $exclusive_on ) && in_array( $tag_id, $exclusive_on ) ){
						$display = true;
					}
				}
			}
			if( !empty( $exclusive_tax ) ){
				foreach( $exclusive_tax as $taxonomy ) {
					$taxonomy = str_replace( "cp-", "", $taxonomy );

					if( is_singular($taxonomy) ) {
						$display = true;
					}

					if( is_category($taxonomy) ){
						$display = true;
					}

					if( is_tag($taxonomy) ){
						$display = true;
					}

					if( is_tax($taxonomy) ){
						$display = true;
					}
				}
			}
		}


		if( !$show_for_logged_in ){		
			$exc_flag = false;
			$excl_visible_to_users = isset($settings['excl_visible_to_users'] ) ? apply_filters('smile_render_setting', $settings['excl_visible_to_users']) : '';	
			$exc_flag = cp_check_user_role($excl_visible_to_users);

			if( is_user_logged_in() && !$exc_flag ){
				$display = false;
			}

		}else{		

			$visible_to_users = isset($settings['visible_to_users'] ) ? apply_filters('smile_render_setting', $settings['visible_to_users']) : '';

			$user_present = cp_check_user_role($visible_to_users);		
			if( $user_present ){
				$display = false;
			}
		}

		$style_id = $settings['style_id'];

		// filter target page settings
		$display = apply_filters( 'cp_target_page_settings', $display, $style_id );

		return $display;
	}else{
		return false;
	}
}

/**
 * Check current user role is selected or not
 * @return [type] [description]
 */
function cp_check_user_role( $user_val){
	$user_present = false;
	if( $user_val ){
		$user_role = explode('|', $user_val);
		$user_role = array_map('strtolower', $user_role);
		$current_user = wp_get_current_user();
		$current_role = strtolower( $current_user->roles ? $current_user->roles[0] : false) ;
		if (in_array($current_role, $user_role))
		 {
		 	$user_present = true;
		 }
	}
	return $user_present;
}

/**
 * 	display style inline
 * @Since 2.1.0
 */
function cp_display_style_inline() {

	$before_content_string = '';
	$after_content_string  = '';

	$cp_modules = get_option('convert_plug_modules');

	if( is_array($cp_modules) ) {

		foreach( $cp_modules as $module ) {

			$module = strtolower( str_replace( "_Popup", "" , $module) );
			$style_arrays = cp_get_live_styles($module);

			if( is_array($style_arrays) ) {

				foreach( $style_arrays as $key => $style_array ){

					$display = false;
					$display_inline = false;
					$settings_encoded = '';
					$style_settings = array();
					$settings_array = unserialize($style_array[ 'style_settings' ]);
					foreach($settings_array as $key => $setting){
						$style_settings[$key] = apply_filters( 'smile_render_setting',$setting );
					}

					$style_id = $style_array[ 'style_id' ];
					$modal_style = $style_settings[ 'style' ];

					if( is_array($style_settings) && !empty($style_settings) ){
						$settings = unserialize( $style_array[ 'style_settings' ] );

						if( isset( $settings['enable_display_inline'] ) && $settings['enable_display_inline'] === '1' ) {
							$display_inline = true;
							$inline_position = $settings['inline_position'];
						}

						$css = isset( $settings['custom_css'] ) ? urldecode($settings['custom_css']) : '';
						$display = cp_is_style_visible($settings);
						$settings = serialize( $settings );
						$settings_encoded 	= base64_encode( $settings );
					}

					if( $display && $display_inline ) {

						ob_start();

						echo do_shortcode( '[smile_'.$module.' display="inline" style_id = '.$style_id.' style="'.$modal_style.'" settings_encoded="' . $settings_encoded . ' "][/smile_'.$module.']' );
						apply_filters('cp_custom_css',$style_id, $css);

						switch($inline_position) {
							case "before_post":
								$before_content_string .= ob_get_contents();
							break;
							case "after_post":
								$after_content_string .= ob_get_contents();
							break;
							case "both":
								$after_content_string .= ob_get_contents();
								$before_content_string .= ob_get_contents();
							break;
						}

						ob_end_clean();
					}
				}
			}
		}
	}

	$output_string = array($before_content_string, $after_content_string);
	return $output_string;
}


/**
 * 	Get live styles list for particular module
 * @Since 2.1.0
 */
function cp_get_live_styles($module) {

	$styles = get_option( 'smile_'.$module.'_styles' );
	$style_variant_tests = get_option( $module.'_variant_tests' );
	$live_array = array();
	if( !empty( $styles ) ) {
		foreach( $styles as $key => $style ){
			$settings = unserialize( $style[ 'style_settings' ] );

			$split_tests = isset( $style_variant_tests[$style['style_id']] ) ? $style_variant_tests[$style['style_id']] : '';
			if( is_array( $split_tests ) && !empty( $split_tests ) ) {
				$split_array = array();
				$live = isset( $settings[ 'live' ] ) ? (int)$settings[ 'live' ] : false;
				if( $live ){
					array_push( $split_array, $styles[ $key ] );
				}
				foreach( $split_tests as $key => $test ) {
					$settings = unserialize( $test[ 'style_settings' ] );
					$live = isset( $settings[ 'live' ] ) ? (int)$settings[ 'live' ] : false;
					if( $live ){
						array_push( $split_array, $test );
					}
				}
				if( !empty( $split_array ) ) {
					$key 	= array_rand( $split_array, 1 );
					$array 	= $split_array[$key];
					array_push( $live_array, $array );
				}
			} else {
				$live = isset( $settings[ 'live' ] ) ? (int)$settings[ 'live' ] : false;
				if( $live ){
					array_push( $live_array, $styles[ $key ] );
				}
			}
		}
	}

	return $live_array;
}


/**
 * Notify form submission errors to admin
 * @since 2.3.0
 */
if( !function_exists('cp_notify_error_to_admin') ) {
	function cp_notify_error_to_admin($page_url) {

		// prepare content for email
		$subject  = 'Issue with the ' . CP_PLUS_NAME . ' configuration';

		$body = "Hello there, <p>There appears to be an issue with the " . CP_PLUS_NAME . " configuration on your website. Someone tried to fill out " . CP_PLUS_NAME . " form on ".esc_url( $page_url )." and regretfully, it didn't go through.</p>";

		$body .= "Please try filling out the form yourself or read more why this could happen here.";

		$body .= "<br>---<p>This e-mail was sent from " . CP_PLUS_NAME . " on ". get_bloginfo('name') ." (". site_url() . ")</p>";

		// get admin email
		$to = sanitize_email( get_option( 'admin_email' ) );

		$admin_notifi_time = get_option( 'cp_notified_admin_time' );

		if( !$admin_notifi_time ) {
			cp_send_mail( $to, $subject, $body );
			update_option( 'cp_notified_admin_time', date('Y-m-d H:i:s') );
		} else {
			// getting previously saved notification time
			$saved_timestamp = strtotime($admin_notifi_time);

			// getting current date
			$cDate = strtotime(date('Y-m-d H:i:s'));

			// Getting the value of current date - 24 hours
			$oldDate = $cDate - 86400; // 86400 seconds in 24 hrs

			// if last email was sent time is greater than 24 hours, sent one more notification email
			if ( $oldDate > $saved_timestamp ) {
				cp_send_mail( $to, $subject, $body );
				update_option( 'cp_notified_admin_time', date('Y-m-d H:i:s') );
			}
		}
	}
}

/**
 * Sends an email
 * @since 2.3.0
 */
if( !function_exists('cp_send_mail') ) {
	function cp_send_mail( $to, $subject, $body ) {

		// set headers for email
		$headers = array('Content-Type: text/html; charset=UTF-8');

		if( wp_mail( $to, $subject, $body, $headers ) ) {
			$msg = "success";
		} else {
			$msg = "error";
		}		
		return $msg;
	}
}

function cp_generate_scheduled_info($style_settings) {

	$scheduleData = unserialize($style_settings);
	$title = '';

    if( isset($scheduleData['schedule']) ) {
        $scheduledArray = $scheduleData['schedule'];
        if( is_array($scheduledArray) ) {
            $startDate = date("j M Y ",strtotime($scheduledArray['start']));
            $endDate = date("j M Y ",strtotime($scheduledArray['end']));
            $first = date('j-M-Y (h:i A)', strtotime($scheduledArray['start']));
            $second = date('j-M-Y (h:i A)', strtotime($scheduledArray['end']));
            $title = "Scheduled From ".$first." To ".$second;
        }
    }

    $status = '<span class="change-status"><span data-live="2" class="cp-status"><i class="connects-icon-clock"></i><span class="scheduled-info" title="'.$title.'">'.__( "Scheduled", "smile" ).'</span></span>';

   	return $status;
}

if( !function_exists( 'cp_get_live_preview_settings' ) ) {
	function cp_get_live_preview_settings( $module, $settings_method, $style_options, $template_name) {

		$settings = array();
		if ( $settings_method === 'internal' ) {

			foreach( $style_options as $key => $value ) {
				$settings[$value['name']] = $value['opts']['value'];
			}

			$settings['affiliate_setting'] = false;
			$settings['style'] = 'preview';
			$settings_encoded = base64_encode( serialize( $settings ) );

		} else {

			$settings = get_option( 'cp_'.$module.'_' . $template_name , '' );

			if( is_array($settings) ) {

				$settings = get_option( 'cp_'.$module.'_' . $template_name , '' );

				$style_setting_arr = $settings['style_settings'];
				$style_setting_arr['style'] = 'preview';

			} else {
				$demo_dir = CP_BASE_DIR . 'modules/'.$module.'/presets/'.$template_name.'.txt';

				$handle = fopen($demo_dir, "r");

				$settings = fread($handle, filesize($demo_dir));

				$settings = json_decode($settings, TRUE);

				$style_setting_arr = $settings['style_settings'];

				$style_setting_arr['style'] = 'preview';
			}

			$style_setting_arr['cp_image_link_url'] = 'external';

			$import_style = array();
			foreach( $style_setting_arr as $title => $value ){
				if( !is_array( $value ) ){
					$value = htmlspecialchars_decode($value);
					$import_style[$title] = $value;
				} else {
					foreach( $value as $ex_title => $ex_val ) {
							$val[$ex_title] = htmlspecialchars_decode($ex_val);
					}
					$import_style[$title] = $val;
				}
			}

			$settings_encoded =  base64_encode( serialize ( $import_style ) );
		}

		return $settings_encoded;

	}
}


if( !function_exists('cp_is_connected') )  {
	function cp_is_connected() {

	    $is_conn = false;
        $response = wp_remote_get( 'http://downloads.brainstormforce.com' );

        $response_code = wp_remote_retrieve_response_code( $response );

        if ( $response_code === 200 ){
    		$is_conn = true; //action when connected
      	} else {
       		$is_conn = false; //action in connection failure
      	}

	    return $is_conn;
	}

}

if( !function_exists('cp_get_edit_link') ) {
	function cp_get_edit_link( $style_id, $module, $theme ) {

		$url = '';

		$data   =  get_option( 'convert_plug_settings' );
		$esval  =  isset($data['cp-edit-style-link']) ? $data['cp-edit-style-link'] : 0;

		if( $esval ) {

			// get module styles
			$styles = get_option("smile_".$module."_styles");

			// get variant style for module
			$variant_styles = get_option( $module."_variant_tests" );

			$parent_style = false;
			$variant_style = false;
			$variant_style_id = '';

			if( is_array($styles) ) {
				foreach ($styles as $style) {

					// check if it is parent style
					if( $style['style_id'] === $style_id ) {
						$parent_style = true;
						break;
					}

					if( is_array($variant_styles) ) {
						if( isset( $variant_styles[$style['style_id']] ) ) {
							foreach ($variant_styles[$style['style_id']] as $child_style) {

								// check if it is child/ variant style
								if( $child_style['style_id'] === $style_id ) {
									$variant_style = true;
									$variant_style_id = $style['style_id'];
									break;
								}
							}
						}
					}
				}
			}

			if( $parent_style ) {
				$baseurl = "admin.php?page=smile-".$module."-designer&style-view=edit&style=".$style_id."&theme=".$theme;
				$url = admin_url($baseurl);
			} else {
				$baseurl = "admin.php?page=smile-".$module."-designer&style-view=variant&variant-test=edit&variant-style=".$style_id."&style=".$theme."&parent-style=".$theme."&style_id=".$variant_style_id."&theme=".$theme;
				$url = admin_url($baseurl);
			}
		}

		return $url;

	}
}

/**
 * Notify subscription to admin
 * @since 2.3.0
 */
if( !function_exists('cp_notify_sub_to_admin') ) {
	function cp_notify_sub_to_admin( $list_name, $subscriber_data, $sub_email, $email_sub, $email_body , $cp_page_url) {
		$email_name   = array();	
		$email_name   = explode(',', $sub_email);
		$to_arr       =	array();
		$body_content = $content = '';

		// prepare content for email
		$subject  = 'Congratulations! You have a New Subscriber!';
		$body     = "<p>You’ve got a new subscriber to the Campaign: ". $list_name ."</p>";
		$body 	 .= "<p>Here is the information :</p>";		
		$subject  = isset($email_sub) ? $email_sub : $subject ;		
		
		foreach ( $subscriber_data as $key => $value ) {
		 	if( $key !== 'user_id' ) {
		 		$body_content .= ucfirst($key) .' : '.	$value.'<br>' ;
		 	}
		}

		$body .= $body_content; 
		$body .= "<p>Congratulations! Wish you many more.<br>This e-mail was sent from Convert Plus on ". get_bloginfo('name') ." (". esc_url( site_url() ) . ")</p>";

		$current_url = $cp_page_url;
		$content = str_replace( "{{list_name}}",$list_name, $email_body );
		$content = str_replace( "{{content}}",$body_content, $content );
		$content = str_replace( "{{blog_name}}",get_bloginfo('name'), $content );
		$content = str_replace( "{{site_url}}",esc_url( site_url() ), $content );
		$content = str_replace( "{{page_url}}",$current_url, $content );
		$content = str_replace( "{{CP_PLUS_NAME}}",CP_PLUS_NAME, $content );

		$body = isset( $email_body ) ? do_shortcode( html_entity_decode( stripcslashes( htmlspecialchars($content)))) : $body ;	
		
		foreach ( $email_name as $key => $email ) {
			$to = sanitize_email( $email );
			array_push( $to_arr, $to );
		}
		// get subscriber email	
		//$to = sanitize_email( $sub_email );
		
		cp_send_mail( $to_arr, $subject, $body );
	}
}

function get_style_details( $style_id, $module ) {

	$styleType = '';
	$parent = '';
	$styles = get_option( 'smile_'.$module.'_styles' );
	$smile_variant_tests = get_option( $module.'_variant_tests' );

	if( is_array($styles) ) {
		foreach ( $styles as $key => $style ) {
			if( $style['style_id'] === $style_id ) {
				// main style
				$styleType = 'main';
			}
		}
	}

	if( $styleType === '' ) {
		if( is_array($smile_variant_tests) ) {
			foreach ( $smile_variant_tests as $key => $value ) {
				
				if( is_array($value) && !empty($value) ) {
					foreach ( $value as $variant ) {
			 			if( isset($variant['style_id']) ) {
							if( $variant['style_id'] === $style_id ) {
								// variant style
								$styleType = 'variant';
								$parent    = $key;							
							}	
						}
					}
				}
			}
		}
	}

	$style_details = array(
		"type"         => $styleType,
		"parent_style" => $parent
	);

	return $style_details;

}

/**
 * Sanitize all values from an array
 *
 * @since 2.3.2.1 
 */ 
function cp_sanitize_array( &$array ) {

	if( is_array( $array ) ) {
		foreach ( $array as &$value ) {	
			
			if( !is_array($value) )	
				
				// sanitize if value is not an array
				$value = sanitize_text_field( $value );
				
			else
			
				// go inside this function again
				cp_sanitize_array($value);
		
		}
	}
	return $array;
}

/**
  *
  *Get sites protocol
  *@since 2.3.3.1 
  */
if( !function_exists( "cp_get_protocol_settings_init" ) ) {
	function cp_get_protocol_settings_init($img) {
		$protocol = 'http://';    
		$replace_img = $img;                  
		
		if(isset($_SERVER['HTTPS'])){
		 $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https://" : "http://";
		}

		if( $protocol === 'https://' ){
			$replace_img = str_replace( 'http://', 'https://', $img );
		}	

		return $replace_img;
	}
}

add_filter( 'cp_get_protocol_settings', 'cp_get_protocol_settings_init');

/**
 * generateBoxShadow for module
 */
if( !function_exists( 'generateBoxShadow' )) {
	function generateBoxShadow($string){
		$pairs = explode( '|', $string );
		$result = array();
		foreach( $pairs as $pair ) {
			$pair = explode( ':', $pair );
			$result[$pair[0]] = $pair[1];
		}

		$res = '';
		if ( isset( $result['type'] ) && $result['type'] !== 'outset' )
			$res .= $result['type'] . ' ';

		$res .= $result['horizontal'] . 'px ';
		$res .= $result['vertical'] . 'px ';
		$res .= $result['blur'] . 'px ';
		$res .= $result['spread'] . 'px ';
		$res .= $result['color'];

		$style = 'box-shadow:'.$res.';';
		$style .= '-webkit-box-shadow:'.$res.';';
		$style .= '-moz-box-shadow:'.$res.';';

		if( $result['type'] === 'none' ) {
			$style = '';
		}

		return $style;
	}
}

/**
 *	= Enqueue Selected - Google Fonts
 *
 * @param string
 * @return string
 * @since 0.1.0
 *-----------------------------------------------------------*/
if( !function_exists( "cp_enqueue_google_fonts" ) ){
	function cp_enqueue_google_fonts( $fonts = '' ) {

		$pairs = $GFonts = $ar = '';

		$basicFonts = array(
			"Arial",
			"Arial Black",
			"Comic Sans MS",
			"Courier New",
			"Georgia",
			"Impact",
			"Lucida Sans Unicode",
			"Palatino Linotype",
			"Tahoma",
			"Times New Roman",
			"Trebuchet MS",
			"Verdana"
		);

		$default_google_fonts = array (
			"Lato",
			"Open Sans",
			"Libre Baskerville",
			"Montserrat",
			"Neuton",
			"Raleway",
			"Roboto",
			"Sacramento",
			"Varela Round",
			"Pacifico",
			"Bitter"
		);

		$allFonts = array_merge($default_google_fonts, $basicFonts);

		if (strpos($fonts, ',') !== FALSE)
			$pairs = explode(',', $fonts);

		//	Extract selected - Google Fonts
		if(!empty($pairs)) {
			foreach ($pairs as $key => $value) {
				if( isset($value) && !empty($value) ) {
					if( !in_array( $value, $basicFonts ) ) {
						$GFonts .= str_replace(' ', '+', $value) .'|';
					}
				}
			}

			$GFonts .= implode( "|", $default_google_fonts );

		} else {
			$GFonts = implode( "|", $default_google_fonts );
		}

		//	Check the google fonts is enabled from BackEnd.
		$data         = get_option( 'convert_plug_settings' );
		$is_GF_Enable = isset($data['cp-google-fonts']) ? $data['cp-google-fonts'] : 1;

		//	Register & Enqueue selected - Google Fonts
		if( !empty( $GFonts ) && $is_GF_Enable ) {
			echo "<link rel='stylesheet' type='text/css' id='cp-google-fonts' href='https://fonts.googleapis.com/css?family=".$GFonts."'>";
		}
	}
}

/**
 *	Check values are empty or not
 *
 * @since 0.1.5
 */
if( !function_exists( "cp_is_not_empty" ) ) {
	function cp_is_not_empty($vl) {
		if( isset( $vl ) && $vl != '' ) {
			return true;
		} else {
			return false;
		}
	}
}


/**
 * Generate CSS from dev input
 *
 * @param string 		- $prop
 * @param alphanumeric	- $val
 * @param string		- $suffix
 * @return string 		- Generate & return CSS (e.g. font-size: 16px;)
 * @since 0.1.5
 */
if( !function_exists( "cp_add_css" ) ) {
	function cp_add_css($prop, $val, $suffix = '') {		
		$op = '';
		if( $val != '') {
			if( $suffix != '' ) {
				$op = $prop. ':' .esc_attr( $val ) . $suffix. ';';
			} else {
				$op = $prop. ':' .esc_attr( $val ). ';';
			}
		}
		return $op;
	}
}

/**
 *	= Enqueue mobile detection js
 *
 * @param string
 * @return string
 * @since 0.1.0
 *-----------------------------------------------------------*/
/* if( !function_exists( "cp_enqueue_detect_device" ) ){
	function cp_enqueue_detect_device( $devices ) {
		 if (wp_script_is( 'cp-detect-device', 'enqueued' )) {
	       return;
	     } else {
			wp_enqueue_script('cp-detect-device' );
		}

	}
}
*/

/**
 *	Add Custom CSS for
 *
 * @since 0.1.5
 */
add_filter( 'cp_custom_css','cp_custom_css_filter', 99, 2);

if( !function_exists( "cp_custom_css_filter" ) ) {
	function cp_custom_css_filter($style_id, $css){
		if( $css !== "" ) {
			echo '<style type="text/css" id="custom-css-'.$style_id.'">'.$css.'</style>';
		}
	}
}

/**
 * generateBorderCss
 */
if( !function_exists( 'generateBorderCss' ) ){
	function generateBorderCss($string){
		$pairs = explode( '|', $string );
		$result = array();
		foreach( $pairs as $pair ){
			$pair = explode( ':', $pair );
			$result[ $pair[0] ] = $pair[1];
		}

		$cssCode1 = '';
		if( isset( $result['br_type'] ) && $result['br_type'] === '1' ) {
			$cssCode1 .= $result['br_tl'] . 'px ' . $result['br_tr'] . 'px ' . $result['br_br'] . 'px ';
			$cssCode1 .= $result['br_bl'] . 'px';
		} else {
			$cssCode1 .= $result['br_all'] . 'px';
		}

		$result['border_width'] = ' ';
		$text = '';
		$text .= 'border-radius: ' . $cssCode1 .';';
		$text .= '-moz-border-radius: ' . $cssCode1 .';';
		$text .= '-webkit-border-radius: ' . $cssCode1 .';';
		$text .= 'border-style: ' . $result['style'] . ';';
		$text .= 'border-color: ' . $result['color'] . ';';
		$text .= 'border-width: ' . $result['border_width'] . 'px;';

		if( isset( $result['bw_type'] ) && $result['bw_type'] === '1' ) {
			$text .= 'border-top-width:'. $result['bw_t'] .'px;';
		    $text .= 'border-left-width:'. $result['bw_l'] .'px;';
		    $text .= 'border-right-width:'. $result['bw_r'] .'px;';
		    $text .= 'border-bottom-width:'. $result['bw_b'] .'px;';
		} else {
			$text .= 'border-width:'. $result['bw_all'] .'px;';
		}

		return $text;
	}
}

/**
 *	Get WordPress attachment url
 *
 * @since 0.1.5
 */
if( !function_exists( "cp_get_wp_image_url_init" ) ) {
	function cp_get_wp_image_url_init( $wp_image = '') {
		if( cp_is_not_empty($wp_image) ){
			$wp_image = explode("|", $wp_image);
			$wp_image = wp_get_attachment_image_src($wp_image[0],$wp_image[1]);
			$wp_image = $wp_image[0];
			$wp_image = cp_get_protocol_settings_init($wp_image);
		}
		return $wp_image;
	}
}
add_filter( 'cp_get_wp_image_url', 'cp_get_wp_image_url_init' );

/**
 *	Set custom class for modal
 *
 * @since 0.1.5
 */
add_filter( 'cp_get_custom_class', 'cp_get_custom_class_init' );

if( !function_exists( "cp_get_custom_class_init" ) ) {
	function cp_get_custom_class_init( $enable_custom_class = 0, $custom_class, $style_id ) {		
		$custom_class = $custom_class;
		$custom_class  = str_replace( " ", "", trim( $custom_class ) );
		$custom_class  = str_replace( ",", " ", trim( $custom_class ) );
		$custom_class .= ' cp-'.$style_id;
		$custom_class = trim( $custom_class );
		return $custom_class;
	}
}


/**
 * Hide Image - On Mobile
 *
 * @since 0.1.5
 */
if( !function_exists( "cp_hide_image_on_mobile_init" ) ) {
	function cp_hide_image_on_mobile_init( $image_displayon_mobile, $image_resp_width ) {
		$hide_image = '';
		if( $image_displayon_mobile === '1' ) {
			$hide_image =' data-hide-img-on-mobile='.$image_resp_width;
		}
		return $hide_image;
	}
}
add_filter( 'cp_hide_image_on_mobile', 'cp_hide_image_on_mobile_init');

/**
 *	Check schedule of module
 *
 * @since 0.1.5
 */

if( !function_exists( "cp_is_module_scheduled" ) ) {
	function cp_is_module_scheduled($schedule, $live) {
		$op = '';
		if( is_array( $schedule ) && $live === '2' ) {
			$op = ' data-scheduled="true" data-start="'.$schedule['start'].'" data-end="'.$schedule['end'].'" ';
		} else {
			$op = ' data-scheduled="false" ';
		}
		return $op;
	}
}

/**
 * Find timezone offset
 * 
 */

if( !function_exists( "getOffsetByTimeZone" ) ) {
	function getOffsetByTimeZone($localTimeZone) {
		$time = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone($localTimeZone));
		$timezoneOffset = $time->format('P');
		return $timezoneOffset;
	}
}

/**
 *	Set custom class for modal
 *
 * @since 0.1.5
 */
add_filter( 'cp_get_scroll_class', 'cp_get_scroll_class_init' );

if( !function_exists( "cp_get_scroll_class_init" ) ) {
	function cp_get_scroll_class_init( $scroll_class) {
		$scroll_class = $scroll_class;
		$scroll_class  = str_replace( " ", "", trim( $scroll_class ) );
		$scroll_class  = str_replace( ",", " ", trim( $scroll_class ) );
		$scroll_class = trim( $scroll_class );
		return $scroll_class;
	}
}


/**
 * Check slidein has redirection
 *
 * @since 0.1.5 *
 * @param bullion - $on_success
 * @param string - 	$redirect_url
 * @param string -  $redirect_data
 * @return string - Data Attribute
 */
if( !function_exists( "cp_has_redirect_init" ) ){
	function cp_has_redirect_init($on_success, $redirect_url, $redirect_data , $on_redirect ,$download_url) {
		$op = '';
		if($on_success === 'redirect' && $redirect_url !== '' && $redirect_data === '1'){
			$op = ' data-redirect-lead-data="'.$redirect_data.'" ';
		}
		if( $on_success === 'redirect' && $redirect_url !== '' && $on_redirect !== '' ) {
			$op .= ' data-redirect-to ="'.$on_redirect.'" ';
		}
		return $op;
	}
}
add_filter( 'cp_has_redirect', 'cp_has_redirect_init' );

/**
 * Set value Enabled or Disabled. - Default 'enabled'
 *
 * @since 0.1.5
 */
if( !function_exists( 'cp_has_enabled_or_disabled_init' ) ) {
	function cp_has_enabled_or_disabled_init( $modal_exit_intent ) {
		$op = ( $modal_exit_intent !=='' && $modal_exit_intent !== '0' ) ? 'enabled' : 'disabled';
		return $op;
	}
}
add_filter( 'cp_has_enabled_or_disabled', 'cp_has_enabled_or_disabled_init' );

/**
 *	Get Modal Image URL
 *
 * @since 0.1.5
 */
if( !function_exists( 'cp_get_module_image_url_init' ) ) {
	function cp_get_module_image_url_init( $module_type = '', $module_img_custom_url = '', $module_img_src ='', $module_image = '') {
		
		$modal_new_image =  '';
		if( $module_img_src === '' ) {
			$module_img_custom_url = 'upload_img';
		}

		if( $module_img_src!=='' && $module_img_src === 'custom_url' ) {
			$modal_new_image = $module_img_custom_url;
		} else if( isset( $module_img_src ) && $module_img_src === 'upload_img' ) {
			if ( strpos($module_image,'http') !== false ) {
				$modal_new_image = explode( '|', $module_image );
				$modal_new_image = $modal_new_image[0];
			} else {
				$modal_new_image = apply_filters('cp_get_wp_image_url', $module_image );
		   	}
		   	$modal_new_image = cp_get_protocol_settings_init($modal_new_image);
		} else {
			$modal_new_image = '';
		}
	   	return $modal_new_image;
	}
}
add_filter( 'cp_get_module_image_url', 'cp_get_module_image_url_init' );


if( !function_exists( 'cp_get_module_image_alt_init' ) ) {
	function cp_get_module_image_alt_init( $module_type = '', $module_img_src ='', $module_image = '' ) {

		$alt = '';

		if( $module_img_src === '' ) {
			$module_img_src = 'upload_img';
		}
		
		if( isset( $module_img_src ) && $module_img_src === 'upload_img' ) {
			if ( strpos($module_image,'http') !== false ) {
			} else {
				$modal_image_alt = explode( '|', $module_image );
              	if( sizeof($modal_image_alt) >2 ){
				 $alt = "alt='".$modal_image_alt[2]."'";
				}
		   	}
		}
	   	return $alt;
	}
}
add_filter( 'cp_get_module_image_alt', 'cp_get_module_image_alt_init' );

//Gradient generator
if( !function_exists( 'generateBackGradient' ) ) {
	function generateBackGradient( $val ){		
		$grad_arr = explode( '|', $val );
		$first_color = $grad_arr[0];
		$sec_color   = $grad_arr[1];
		$first_deg   = $grad_arr[2];
		$sec_deg     = $grad_arr[3];
		$grad_type   = $grad_arr[4];
		$direction   = $grad_arr[5];
		$grad_name   = $grad_css = '';

		switch( $direction ){
            case 'center_left':
                $grad_name = 'left';
                break;
            case 'center_Right':                   
                $grad_name = 'right';
                break;

            case 'top_center':
                $grad_name = 'top';
                break;

            case 'top_left':
                $grad_name = 'top left';
                break;

            case 'top_right':
                $grad_name = 'top right';
                break;

            case 'bottom_center':
                $grad_name = 'bottom';
                break;

            case 'bottom_left':
                $grad_name = 'bottom left';
                break;

            case 'bottom_right':
                $grad_name = 'bottom right';
                break;

            case 'center_center':
                $grad_name = 'center';
                 if( $grad_type == 'linear'){
                   $grad_name = 'top left';
                 }                       
                break;

            case 'default':
				break;
        }

        if( $grad_type == 'linear'){
            $ie_css  = $grad_type.'-gradient(to '.$grad_name.', '.$first_color.' '.$first_deg .'%, '.$sec_color.' '.$sec_deg .'%)';
            $web_css = '-webkit-'.$grad_type.'-gradient('.$grad_name.', '.$first_color.' '.$first_deg .'%, '.$sec_color.' '.$sec_deg .'%)';
            $o_css   = '-o-'.$grad_type.'-gradient('.$grad_name.', '.$first_color.' '.$first_deg .'%, '.$sec_color.' '.$sec_deg .'%)';
            $mz_css  = '-moz-'.$grad_type.'-gradient('.$grad_name.', '.$first_color.' '.$first_deg .'%, '.$sec_color.' '.$sec_deg .'%)';
        }else{
            $ie_css  = $grad_type.'-gradient( ellipse farthest-corner at '.$grad_name.', '.$first_color.' '.$first_deg .'%, '.$sec_color.' '.$sec_deg .'%)';
            $web_css = '-webkit-'.$grad_type.'-gradient( ellipse farthest-corner at '.$grad_name.', '.$first_color.' '.$first_deg .'%, '.$sec_color.' '.$sec_deg .'%)';
            $o_css   = '-o-'.$grad_type.'-gradient( ellipse farthest-corner at '.$grad_name.', '.$first_color.' '.$first_deg .'%, '.$sec_color.' '.$sec_deg .'%)';
            $mz_css  = '-moz-'.$grad_type.'-gradient( ellipse farthest-corner at '.$grad_name.', '.$first_color.' '.$first_deg .'%, '.$sec_color.' '.$sec_deg .'%)';
        }

        $grad_css .= 'background:'.$web_css.';background:'.$o_css.';background:'.$mz_css.';background:'.$ie_css.';';

        return $grad_css;
	}
}

/**
 * Gives current device value
 *
 * @param string $device device value.
 * @return bool $is_current_device
 * @since 3.0.4
 */

if( !function_exists( 'cplus_is_current_device' ) ) {
	function cplus_is_current_device( $device ) {
		$is_current_device = true;
		$device_name = '';		
		
		if( cplus_is_desktop_device() ){
			$device_name = 'desktop';
		}else if( cplus_is_medium_device() ) {
			$device_name = 'tablet';
		}else if( wp_is_mobile() && ( !cplus_is_medium_device() ) ) {
			$device_name = 'mobile';
		}		

		if ( '' != $device ) {
			$device_array = explode( '|', $device );
			if ( ! empty( $device_array ) ) {
				if ( in_array( $device_name, $device_array ) ) {
					$is_current_device = false;
				}				
			}
		}

		return $is_current_device;
	}
}

/**
 * Check if current device is medium device
 *
 * @since 3.0.4
 * @return bool $is_medium
 */
if( !function_exists( 'cplus_is_medium_device' ) ) {
	function cplus_is_medium_device() {

		if ( empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$is_medium = false;
		} elseif ( strpos( $_SERVER['HTTP_USER_AGENT'], 'iPad' ) !== false ) {
			$is_medium = true;
		} else {
			$is_medium = false;
		}

		return $is_medium;
	}
}

/**
 * Check if current device is desktop device
 *
 * @since 3.0.4
 * @return bool $is_desktop
 */
if( !function_exists( 'cplus_is_desktop_device' ) ) {
	function cplus_is_desktop_device() {

		if ( empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$is_desktop = false;
		} elseif (
			strpos( $_SERVER['HTTP_USER_AGENT'], 'Macintosh' ) !== false
			|| strpos( $_SERVER['HTTP_USER_AGENT'], 'Windows' ) !== false
			) {
			$is_desktop = true;
		} else {
			$is_desktop = false;
		}

		return $is_desktop;
	}
}