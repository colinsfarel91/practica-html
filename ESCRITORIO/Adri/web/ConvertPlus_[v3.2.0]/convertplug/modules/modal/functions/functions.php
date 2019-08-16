<?php
/*
* Global functions for modal
*/

if( !function_exists( 'cp_generate_style_css' )){
	function cp_generate_style_css( $a ) {

		//custom css
		$styleID = "content-".$a['uid'];
		$style  = '';
		//custom height only for blank style
		if( isset( $a['cp_custom_height'] ) && isset( $a['cp_modal_height'] ) && $a['cp_custom_height'] === '1' ) {
			$style  .= '';
			$style  .=  "." . $styleID . " .cp-modal-body { "
					. "		min-height:".$a['cp_modal_height']."px;}";
		}
		// 	Append CSS code
		echo '<style type="text/css">'.$style.'</style>';

	}
}

/**
 * Check modal overlay settings
 *
 * @since 0.1.5
 */
if( !function_exists( 'cp_has_overaly_setting_init' ) ) {
	function cp_has_overaly_setting_init( $overlay_effect, $disable_overlay_effect, $hide_animation_width ) {
		$op = ' data-overlay-animation = "'.$overlay_effect.'" ';
		if($disable_overlay_effect === '1'){
			$op .= ' data-disable-animationwidth = "'.$hide_animation_width.'" ';
		}
		return $op;
	}
}
add_filter( 'cp_has_overaly_setting', 'cp_has_overaly_setting_init' );

/**
 * Affiliate - Link
 *
 * @since 0.1.5
 */

if( !function_exists( 'cp_get_affiliate_link_init' ) ) {
	function cp_get_affiliate_link_init( $affiliate_setting, $affiliate_username ) {
		$op = '';
		if($affiliate_setting === '1'){
			if($affiliate_username ===''){
				$affiliate_username = 'BrainstormForce';
				$op = "https://www.convertplug.com/buy?ref=BrainstormForce";
			} else {
				$op = "https://www.convertplug.com/buy?ref=".$affiliate_username."";
			}
			return $op;
		}
	}
}
add_filter( 'cp_get_affiliate_link', 'cp_get_affiliate_link_init');

/**
 * Affiliate - Class
 *
 * @since 0.1.5
 */
if( !function_exists( 'cp_get_affiliate_class_init' ) ) {
	function cp_get_affiliate_class_init( $affiliate_setting, $modal_size ) {
		$op = '';
		if($affiliate_setting === '1' &&  $modal_size === "cp-modal-custom-size" ){
			$op .= "cp-affilate";
		}
		return $op;
	}
}
add_filter( 'cp_get_affiliate_class', 'cp_get_affiliate_class_init');

/**
 * Affiliate - Setting
 *
 * @since 0.1.5
 */
if( !function_exists( 'cp_get_affiliate_setting_init' ) ) {
	function cp_get_affiliate_setting_init( $affiliate_setting ) {
		$op =  ( $affiliate_setting === '1' ) ? 'data-affiliate_setting='.$affiliate_setting : 'data-affiliate_setting ="0"' ;
		return $op;
	}
}
add_filter( 'cp_get_affiliate_setting', 'cp_get_affiliate_setting_init');

/**
 * Global Settings - Modal
 *
 * @since 0.1.5
 */
if( !function_exists( 'cp_modal_global_settings_init' ) ) {
	function cp_modal_global_settings_init( $closed_cookie, $conversion_cookie, $style_id, $style_details ) {

		$style_type = $style_details['type'];
		$parent_style = $style_details['parent_style'];

		$op  = ' data-closed-cookie-time="'.$closed_cookie.'"';
		$op .= ' data-conversion-cookie-time="'.$conversion_cookie.'" ';
		$op .= ' data-modal-id="'.$style_id.'" ';

		if( $parent_style !== '' ) {
			$op .= ' data-parent-style="'.$parent_style.'" ';
		}

		$op .= ' data-modal-style="'.$style_id.'" ';
		$op .= ' data-option="smile_modal_styles" ';
		return $op;
	}
}
add_filter( 'cp_modal_global_settings', 'cp_modal_global_settings_init');


/**
 * Modal Before
 *
 * @since 0.1.5
 */
if( !function_exists( 'cp_modal_global_before_init' ) ) {
function cp_modal_global_before_init( $a ) {

	$autoclose_data         = $timezone 	= $referrer_data = $styleType  = '';
	$bg_repeat              = $bg_pos 		= $bg_size 		 = $bg_setting = $el_class = $module_bg_gradient = $module_bg_color_type = '';
	$modal_bg_image         = $customcss 	= $windowcss	 = $inset 	   = $css_style = '';
	$close_html             = $modal_size_style = $close_class = $close_inline = $inline_text = '';
	$close_img_class        = $close_img = $load_after_scroll = $font_family = '';
	$load_on_duration       = $close_btn_on_duration = $close_modal_on = '';
	$scroll_data            = $scroll_class = $inactive_data = $data_redirect = $overlay_effect = $hide_image = $placeholder_font 	= '';
	$cp_modal_content_class = $impression_disable_class = $cp_modal_content_class = $form_data_onsubmit ='';
	$style_id               = ( isset( $a['style_id'] ) ) ? $a['style_id'] : '';
	$a['image_resp_width']  = '768';			
	$convert_plug_settings  = get_option('convert_plug_settings');
	$style_details          = get_style_details( $style_id, 'modal' );	

	if ( !isset( $a['modal_size'] ) ) {
		$a['modal_size'] = 'cp-modal-custom-size';
	}

	//	Print CSS of the style
	cp_generate_style_css( $a );	

	// check referrer detection
	$referrer_check  = ( isset( $a['enable_referrer'] ) && (int)$a['enable_referrer'] ) ? 'display' : 'hide';
	$referrer_domain = ( $referrer_check === 'display' ) ? $a['display_to'] : $a['hide_from'];

	if( $referrer_check !== '' ){
		$referrer_data = 'data-referrer-domain="'.$referrer_domain.'"';
		$referrer_data .= ' data-referrer-check="'.$referrer_check.'"';
	}

	// check close after few second
	$autoclose_on_duration  = ( isset( $a['autoclose_on_duration'] ) && (int)$a['autoclose_on_duration'] ) ? $a['autoclose_on_duration'] : '';
	$close_module_duration = ( isset( $a['close_module_duration'] ) && (int)$a['close_module_duration'] ) ? $a['close_module_duration'] : '';
	$isInline = ( isset( $a['display'] ) && $a['display'] === "inline" ) ? true : false;
	
	if( $autoclose_on_duration !== '' && (!$isInline) && ( isset( $a['close_modal'] ) && $a['close_modal']!=='do_not_close' )){
		$autoclose_data = 'data-close-after = "'.$close_module_duration.'"';
	}
	//	Enqueue Google Fonts
	cp_enqueue_google_fonts( $a['cp_google_fonts'] );

	if( isset( $a['opt_bg'] ) && strpos( $a['opt_bg'], "|" ) !== false ){
	    $opt_bg      = explode( "|", $a['opt_bg'] );
	    $bg_repeat   = $opt_bg[0];
	    $bg_pos      = $opt_bg[1];
	    $bg_size     = $opt_bg[2];
        $bg_setting .= 'background-repeat: '.$bg_repeat.';';
        $bg_setting .= 'background-position: '.$bg_pos.';';
        $bg_setting .= 'background-size: '.$bg_size.';';
	}

	//	Time Zone		
	$timezone_name = !isset( $convert_plug_settings['cp-timezone'] ) ? 'wordpress' : $convert_plug_settings['cp-timezone'];

	if( $timezone_name !== '' && $timezone_name !== 'system' ){
		
		$timezone = get_option('timezone_string');
		if( $timezone === '' ){
			$toffset = get_option('gmt_offset');
			$timezone = "".$toffset."";
		}
	} else {
		$timezone = get_option('timezone_string');
		if( $timezone === '' ){
			$toffset = get_option('gmt_offset');
			$timezone = "".$toffset."";
		}
	}

	//	Modal - Padding
	
	if( isset( $a['content_padding'] ) && !empty( $a['content_padding'] ) ) {
		$el_class .= ' cp-no-padding ';
	}
	//check modal_back_type - gradient/simple/image
	$module_bg_color_type = ( isset( $a['module_bg_color_type'] ) ) ? $a['module_bg_color_type'] : '';
	$bg_type_set = false;
	$old_user = true;
	if( $module_bg_color_type !== ''){
		$module_bg_gradient = ( isset( $a['module_bg_gradient'] ) ) ? $a['module_bg_gradient'] : '';
		$bg_type_set = true;
		$old_user = false;
	}

	//	Modal - Background Image & Background Color
	
	$modal_bg_color = ( isset( $a['modal_bg_color'] ) ) ? $a['modal_bg_color'] : '';
	if( !isset( $a['modal_bg_image_src'] ) ) {
		$a['modal_bg_image_src']  = 'upload_img';
	}
	if( isset( $a['modal_bg_image_src'] ) && !empty( $a['modal_bg_image_src'] ) ) {

		if ( $a['modal_bg_image_src'] === 'custom_url' ) {
			$modal_bg_image = $a['modal_bg_image_custom_url'];
		} else if ( $a['modal_bg_image_src'] === 'upload_img' ) {
			if( isset( $a['modal_bg_image'] ) ) {
				if ( strpos($a['modal_bg_image'],'http') !== false ) {
					$modal_bg_image = explode( '|', $a['modal_bg_image'] );
					$modal_bg_image = $modal_bg_image[0];
				} else {
					$modal_bg_image = apply_filters( 'cp_get_wp_image_url', $a['modal_bg_image'] );
			   	}
			   	$modal_image = cp_get_protocol_settings_init($modal_bg_image);
			}
		} else {
			$modal_bg_image = '';
		}
	}

	if( $modal_bg_image !== '' ){
		if( $bg_type_set && $module_bg_color_type =='image' ){
			$customcss .= 'background-image:url(' . $modal_bg_image . ');' .$bg_setting .';';
			$windowcss .= 'background-image:url(' . $modal_bg_image . ');' .$bg_setting .';';
		}else if ($old_user){
			$customcss .= 'background-image:url(' . $modal_bg_image . ');' .$bg_setting .';';
			$windowcss .= 'background-image:url(' . $modal_bg_image . ');' .$bg_setting .';';
		}
	}

	$gradient_css = '';
	$modal_body_css = '';
	if( !$old_user && $module_bg_color_type == 'gradient' && $bg_type_set && $a['style'] !== 'countdown'){
		$modal_body_css= generateBackGradient($module_bg_gradient);
	}else{
		$modal_body_css = 'background-color:'.$modal_bg_color.';';
	}

	//	Modal - Box Shadow
	if( $a['box_shadow'] !== '' )  {
		$box_shadow_str = generateBoxShadow($a['box_shadow']);
		if ( strpos( $box_shadow_str,'inset' ) !== false ) {
			$inset 	.= $box_shadow_str.';';
			$inset 	.= "opacity:1";
		} else {
			$css_style 	.= $box_shadow_str;
		}
	}

	//	Check 'has_content_border' is set for that style and add border to modal content (optional)
	//	This option is style dependent - Developer will disable it by adding this variable
	if( !isset( $a['has_content_border'] ) || ( isset( $a['has_content_border'] ) && $a['has_content_border'] ) ) {
		if( isset( $a['border'] ) && $a['border'] !== '' ){
		 $css_style .= generateBorderCss($a['border']);
		}
	}
	if( $a['modal_size'] === "cp-modal-custom-size" ){
		$modal_size_style  = cp_add_css('width', '100', '%');
		$modal_ht = isset( $a['cp_modal_height'] ) ? $a['cp_modal_height'] : 'auto';
		$modal_size_style .= cp_add_css('height', $modal_ht );
		$modal_size_style .= cp_add_css('max-width', $a['cp_modal_width'], 'px');
		$windowcss = '';
	} else {
		$customcss = 'max-width: '.$a['cp_modal_width'].'px';
		$windowcss .= $box_shadow_str;
	}

	//	{START} - SAME FOR BEFORE & AFTER NEED TO CREATE FUNCTION IT's TEMP
	$close_img_prop  = cp_close_image_setup( $a );	
	$close_img       = $close_img_prop['close_img'];
	$close_img_class = $close_img_prop['close_img_class'];
	$close_alt       = $close_img_prop['close_alt'];

	if( $close_alt !== '' ){
		$close_alt = 'alt="'.$close_alt .'"';
	}else{
		$close_alt = 'close-link';
	}

	if( $a['close_modal'] === "close_txt") {
		if( isset($a['close_text_font']) && $a['close_text_font']!=='' ){
			$font_family = ' font-family:'.$a['close_text_font'];
		}
		$close_html = '<span style="color:'.$a['close_text_color'].';'.$font_family.'">'.$a['close_txt'].'</span>';
	} else if( $a['close_modal'] === "close_img" ) {
		$close_html = '<img class="'.$close_img_class.'" src="'.$close_img.'" '.$close_alt.' />';
	} else {
		$close_class = ' do_not_close ';
	}
	//	{END} - SAME FOR BEFORE & AFTER NEED TO CREATE FUNCTION IT's TEMP
	
	if( $a['autoload_on_scroll'] == '1') {
		$load_after_scroll = $a['load_after_scroll'];
	}
	
	if( $a['autoload_on_duration'] ) {
		$load_on_duration = $a['load_on_duration'];
	}
	
	if( isset( $a['display_close_on_duration'] ) && $a['display_close_on_duration'] && $a['close_modal'] !== 'do_not_close' ) {
		$close_btn_on_duration  .= 'data-close-btnonload-delay='.$a['close_btn_duration'].' ';
	}

	$dev_mode = 'disabled';
	if( !$a['developer_mode'] ){
		$a['closed_cookie'] = $a['conversion_cookie'] = 0;
		$dev_mode = 'enabled';
	}
	
	if( $a['close_modal_on'] )
		$close_modal_on = ' close_btn_nd_overlay';

	$user_inactivity = isset( $convert_plug_settings['user_inactivity'] ) ? $convert_plug_settings['user_inactivity'] : '60';
	
	if( $a['inactivity'] ) {
		$inactive_data = 'data-inactive-time="'.$user_inactivity.'"';
	}

	//scroll up to specific class
	$enable_custom_scroll = isset( $a['enable_custom_scroll'] ) ? $a['enable_custom_scroll'] : '';
	$enable_scroll_class = isset( $a['enable_scroll_class'] ) ? $a['enable_scroll_class'] : '';

	if($enable_custom_scroll){
		if( $enable_scroll_class !== '' ){
			$scroll_class 	= cp_get_scroll_class_init( $a['enable_scroll_class'] );
			$scroll_data 	= 'data-scroll-class="'.$scroll_class.'"';
		}
	}

	//	Variables
	$global_class 			= ' global_modal_container';
	$schedule               = isset( $a['schedule'] ) ? $a['schedule'] : '';
	$isScheduled 			= cp_is_module_scheduled( $schedule, $a['live'] );
	//	Filters & Actions
	
	if( isset($a['on_success']) && isset($a['redirect_url']) && isset($a['redirect_data']) && isset($a['on_redirect']) ) {
		$download_url ='';
		if(isset($a['download_url'])){
			$download_url = $a['download_url'];
		}
		$data_redirect	 	= cp_has_redirect_init( $a['on_success'], $a['redirect_url'], $a['redirect_data'] , $a['on_redirect'] ,$download_url);
	}
	
	if( isset($a['overlay_effect']) ) {
		$overlay_effect = $a['overlay_effect'];
	}
	
	if( isset( $a['image_displayon_mobile'] ) && isset( $a['image_resp_width'] ) ) {
		$hide_image 	 	= cp_hide_image_on_mobile_init( $a['image_displayon_mobile'], $a['image_resp_width'] );
	}

	$overaly_setting 		= cp_has_overaly_setting_init( $overlay_effect , $a['disable_overlay_effect'], $a['hide_animation_width'] );
	$afl_setting 	 		= apply_filters( 'cp_get_affiliate_setting', $a['affiliate_setting'] );
	$style_id 				= ( isset( $a['style_id'] ) ) ? $a['style_id'] : '';
	$style_class 			= ( isset( $a['style_class'] ) ) ? $a['style_class'] : '';

	//	Filters
	$custom_class 			= cp_get_custom_class_init( $a['enable_custom_class'], $a['custom_class'], $style_id );
	$modal_exit_intent 		= apply_filters( 'cp_has_enabled_or_disabled', $a['modal_exit_intent'] );
	$load_on_refresh 		= apply_filters( 'cp_has_enabled_or_disabled', $a['display_on_first_load'] );
	$load_on_count 			= '';
	if( $load_on_refresh === 'disabled'){
		$load_on_count 		= ( isset( $a['page_load_count'] ) ) ? $a['page_load_count'] : '';		
	}
	$global_modal_settings 	= cp_modal_global_settings_init( $a['closed_cookie'], $a['conversion_cookie'], $style_id, $style_details );

	$placeholder_color 		= ( isset( $a['placeholder_color'] ) ) ? $a['placeholder_color'] : '';
	$placeholder_font 		= ( isset( $a['placeholder_font'] ) && $a['placeholder_font'] !== '') ? $a['placeholder_color'] : 'inherit';
	
	$image_position			= ( isset( $a['image_position'] ) ) ? $a['image_position'] : '';
	$exit_animation			= isset( $a['exit_animation'] ) ? $a['exit_animation'] : 'cp-overlay-none';

	$schedular_tmz_offset = get_option('gmt_offset');
	if( $schedular_tmz_offset === '' ){
		$schedular_tmz_offset = getOffsetByTimeZone(get_option('timezone_string'));
	}
	$data_debug         			 = get_option( 'convert_plug_debug' );

	//  Container Classes
    if( isset( $a['mailer'] ) && ( $a['mailer'] === "custom-form" ) ) {
		$cp_modal_content_class .= ' cp-custom-form-container';
		//  Add - Contact Form 7 Styles
	    $is_cf7_styles_enable 	 = ( isset( $data_debug['cp-cf7-styles'] ) ) ? $data_debug['cp-cf7-styles'] : 1;
	    $cp_modal_content_class .= ( $is_cf7_styles_enable ) ? ' cp-default-cf7-style1' : '';
    }

	$impression_disable 	 = ( isset( $convert_plug_settings['cp-disable-impression'] ) ) ? $convert_plug_settings['cp-disable-impression'] : 0;
	if($impression_disable){
		$impression_disable_class = 'cp-disabled-impression';
	}

	 // check if modal should be triggered after post
	 $enable_after_post = (int) ( isset( $a['enable_after_post'] ) ? $a['enable_after_post'] : 0 );
	 if( $enable_after_post ) {
		 $custom_class .= ' cp-after-post';
	 }

	 // check if modal should be triggerd if items in the cart
	 $items_in_cart = (int) ( isset( $a['items_in_cart'] ) ? $a['items_in_cart'] : 0 );
	 if( $items_in_cart ) {
		 $custom_class .= ' cp-items-in-cart';
	 }

	// check if inline display is set

	$isInline = ( isset( $a['display'] ) && $a['display'] === "inline" ) ? true : false;	

	if( $isInline ){
		$custom_class .= " cp-open";
		$close_class = "do_not_close";
		$a['modal_overlay_bg_color'] = 'rgba( 255,255,255,0 );';
		$cp_close_inline = (int) ( isset( $timezone_settings['cp-close-inline'] ) ? $timezone_settings['cp-close-inline'] : 0 );
		$close_inline     =  ( $cp_close_inline ) ? 'cp-close-inline' : 'cp-do-not-close-inline' ;
		$inline_text 	  = 'cp-modal-inline '. $close_inline ;
	} else {
		$custom_class .= " cp-modal-global";
	}

	/**
	 * Contact Form - Layouts
	 *
	 */
	$form_layout          = ( isset( $a['form_layout'] ) ) ? $a['form_layout'] : '';
	$data_form_layout     = 'data-form-layout="'.$form_layout.'"';
	$after_content_scroll = isset( $data_debug['after_content_scroll'] ) ? $data_debug['after_content_scroll'] : '50';
	$after_content_data   = 'data-after-content-value="'. $after_content_scroll .'"';
	$cp_onload          = ( isset( $a['manual'] ) && $a['manual'] === 'true' ) ? '' : 'cp-onload cp-global-load ';

	$modal_bg_color = isset( $a['modal_bg_color'] ) ? $a['modal_bg_color'] : '';

	if( $a['modal_size'] ==='cp-modal-window-size'){
		$global_class .= ' cp-window-overlay';
	}

	//form display/hide after sucessfull submission
	
	$form_action_onsubmit = isset( $a['form_action_on_submit'] )? $a['form_action_on_submit'] :'';
	
	if( $form_action_onsubmit === 'reappear' ){
		$form_data_onsubmit = 'data-form-action = reappear';
		$form_data_onsubmit .= ' data-form-action-time = '.$a['form_reappear_time'].'';
	}else if( $form_action_onsubmit === 'disappears' ){
		$form_data_onsubmit = 'data-form-action = disappear';
		$form_data_onsubmit .= ' data-form-action-time ='.$a['form_disappears_time'].'';
	}

	$inline_test =  ( $isInline ) ?  $inline_text : "cp-overlay ";

	$content_uid = 'content-'.$a['uid'];
	$overlay_show_data = '';
	$overlay_show_data .= 'data-class-id="'.$content_uid.'" ';
	$overlay_show_data .= $referrer_data.' ';
	$overlay_show_data .= $after_content_data.' ';
	$overlay_show_data .= 'data-overlay-class = "overlay-zoomin" ';
	$overlay_show_data .= 'data-onload-delay = "'.esc_attr( $load_on_duration ).'"';
	$overlay_show_data .= 'data-onscroll-value = "'.esc_attr( $load_after_scroll ).'"';		
	$overlay_show_data .= 'data-exit-intent = "'.esc_attr( $modal_exit_intent ).'"';
	$overlay_show_data .= $global_modal_settings.' ';
	$overlay_show_data .= $inactive_data.' ';
	$overlay_show_data .= $scroll_data.' ';
	$overlay_show_data .= 'data-custom-class = "'.esc_attr( $custom_class ).'"';
	$overlay_show_data .= 'data-load-on-refresh = "'.esc_attr( $load_on_refresh ).'"';
	$overlay_show_data .= 'data-dev-mode = "'.esc_attr( $dev_mode ).'"';
	//$overlay_show_data .= 'data-onscroll-value = "'.esc_attr( $load_after_scroll ).'"';
		
	$onload_class = "";
	$onload_class .= 'overlay-show '.$cp_onload.' '.esc_attr( $custom_class );

	$overlay_class = '';
	$overlay_class .='cp-module cp-modal-popup-container '.' '.esc_attr( $style_id ).' '.$style_class. '-container';
	if( $isInline ){
		$overlay_class .= ' cp-inline-modal-container';		
    }

    //global container class and data	   
    $global_cont_data = '';
	$global_cont_data .= $isScheduled;
	$global_cont_data .= $global_modal_settings;
	$global_cont_data .= 'data-placeholder-font="'.$placeholder_font.'"';		
	$global_cont_data .= 'data-custom-class = "'.esc_attr( $custom_class ).'"';
	$global_cont_data .= 'data-class = "'.esc_attr( $content_uid ).'"';
	$global_cont_data .= 'data-load-on-refresh = "'.esc_attr( $load_on_refresh ).'"';		
	$global_cont_data .= 'data-load-on-count = "'.esc_attr( $load_on_count ).'"';
	$global_cont_data .= $hide_image .' ';
	$global_cont_data .= $afl_setting .' ';
	$global_cont_data .= $overaly_setting .' ';
	$global_cont_data .= $data_redirect .' ';
	$global_cont_data .= esc_attr( $close_btn_on_duration ) .' ';
	$global_cont_data .= $autoclose_data .' ';
	$global_cont_data .= esc_attr( $form_data_onsubmit ) .' ';		
	$global_cont_data .= ' data-tz-offset = "'.esc_attr( $schedular_tmz_offset ).'"';
	$global_cont_data .= 'data-image-position = "'.esc_attr( $image_position ).'"';
	$global_cont_data .= 'data-placeholder-color = "'.esc_attr( $placeholder_color ).'"';
	$global_cont_data .= 'data-timezonename = "'.esc_attr( $timezone_name ).'"';
	$global_cont_data .= 'data-timezone = "'.esc_attr( $timezone ).'"';
	
	$global_cont_class = "";
	$global_cont_class .= $content_uid.' ';
	$global_cont_class .= esc_attr( $inline_test ) .' ';
	$global_cont_class .= esc_attr( $close_modal_on ).' ';		
	$global_cont_class .= esc_attr( $overlay_effect ).' ';		
	$global_cont_class .= esc_attr( $global_class ).' ';				
	$global_cont_class .= esc_attr( $close_class ).' ';
	$global_cont_class .= esc_attr( $impression_disable_class ).' ';

	ob_start();

?>
<?php if( !$isInline ){ ?>
	<div <?php echo $overlay_show_data; ?> class="<?php echo $onload_class; ?>" data-module-type="modal" ></div>
<?php } ?>

	<div <?php echo $data_form_layout; ?> class="<?php echo esc_attr( $overlay_class ); ?> ">
		<div class="<?php echo ( $global_cont_class ) ; ?>" <?php echo  $global_cont_data;?> style=" <?php echo esc_attr( 'background:'.$a['modal_overlay_bg_color'] ); ?>" >
			<?php if( isset( $a['modal_size'] ) && $a['modal_size'] != "cp-modal-custom-size" ){ ?>
	      				<div class="cp-modal-body-overlay cp_fs_overlay" style="<?php echo esc_attr( $modal_body_css ); ?>;<?php echo esc_attr( $inset ); ?>;"></div>
	      			<?php } ?>
	    	<div class="cp-modal <?php echo esc_attr( $a['modal_size'] ); ?>" style="<?php echo esc_attr( $modal_size_style ); ?>">
	      		<div class="cp-animate-container" <?php echo $overaly_setting;?> data-exit-animation="<?php echo esc_attr( $exit_animation ); ?>">
	      			<div class="cp-modal-content <?php echo $cp_modal_content_class; ?>" style="<?php echo esc_attr( $css_style ); ?>;<?php echo esc_attr( $windowcss );?>">
					

	        		<div class="cp-modal-body <?php echo $style_class . ' ' . esc_attr( $el_class ); ?>" style="<?php echo esc_attr( $customcss );?>">
	          		 <?php if( $a['modal_size'] === "cp-modal-custom-size" ) { ?>
	      					<div class="cp-modal-body-overlay cp_cs_overlay" style="<?php echo esc_attr( $modal_body_css ); ?>;<?php echo esc_attr( $inset ); ?>;"></div>
	      				<?php } ?>
<?php
 }
}
add_filter( 'cp_modal_global_before', 'cp_modal_global_before_init' );


/**
 * Modal After
 *
 * @since 0.1.5
 */
if( !function_exists( "cp_modal_global_after_init" ) ) {
function cp_modal_global_after_init( $a ) {

	$edit_link = '';
	if( is_user_logged_in() ) {
		// if user has access to CP_PLUS_SLUG, then only display edit style link
		if( current_user_can( 'access_cp' ) ) {
			if( isset( $a['style_id'] ) ) {
				$edit_link = cp_get_edit_link( $a['style_id'], 'modal', $a['style'] );
			}
		}
	}

	if ( !isset( $a['modal_size'] ) ) {
		$a['modal_size'] = 'cp-modal-custom-size';
	}

	$afilate_link  = cp_get_affiliate_link_init( $a['affiliate_setting'], $a['affiliate_username'] );
	$afilate_class = cp_get_affiliate_class_init( $a['affiliate_setting'], $a['modal_size'] );
	$style_id      = ( isset( $a['style_id'] ) ) ? $a['style_id'] : '';
	
	if( $a['close_modal'] !== 'close_txt' )
		$cp_close_image_width = $a['cp_close_image_width']."px";
	else
		$cp_close_image_width = 'auto';

	//	{START} - SAME FOR BEFORE & AFTER NEED TO CREATE FUNCTION IT's TEMP
	$close_img_class = $close_img = $close_alt  = '';
	$close_html      = $el_class = $modal_size_style = $close_class = '';
	$close_tooltip   = $close_tooltip_end = '';
	$close_img_prop  = cp_close_image_setup( $a );
	$close_img       = $close_img_prop['close_img'];
	$close_img_class = $close_img_prop['close_img_class'];
	$close_alt       = $close_img_prop['close_alt'];	
	$close_alt       = ( $close_alt !== '' ) ? 'alt="'.$close_alt .'"' : 'close-link';	
	
	if( isset( $a['content_padding'] ) && $a['content_padding'] ) {
		$el_class .= 'cp-no-padding ';
	}

	if( $a['close_modal'] === "close_txt" ) {
		$close_class .= 'cp-text-close';
		if( $a['close_modal_tooltip'] === '1' ) {
			$close_tooltip ='<span class="cp-close-tooltip cp-tooltip-icon has-tip cp-tipcontent-'.$a['style_id'].'data-classes="close-tip-content-'.$a['style_id'].'" data-position="left"  title="'. $a['tooltip_title'].'"  data-color="'.$a['tooltip_title_color'] .'" data-bgcolor="'.$a['tooltip_background'].'" data-closeid ="cp-tipcontent-'.$a['style_id'].'" data-font-family ="'.$a['tooltip_text_font'].'">';
			$close_tooltip_end ='</span>';
		}
		if( isset($a['close_text_font']) && $a['close_text_font']!='' ){
			$font_family = ' font-family:'.$a['close_text_font'];
		}

		$close_html = '<span style="color:'.$a['close_text_color'].';'.$font_family.'">'.$a['close_txt'].'</span>';
	} else if( $a['close_modal'] === "close_img" ) {
		$close_class .= 'cp-image-close';		
		$close_html   = '<img class="'.$close_img_class.'" src="'.$close_img.'" '.$close_alt.' />';
	} else {
		$close_class = 'do_not_close';
	}

	if( isset( $a['display_close_on_duration'] ) && $a['display_close_on_duration'] && $a['close_modal'] !== 'do_not_close' ) {
		$close_class  .= ' cp-hide-close';
	}

	//	{END} - SAME FOR BEFORE & AFTER NEED TO CREATE FUNCTION IT's TEMP

	/* -- tool tip ----- */
	$tooltip_position = 'left';
	if( $a['modal_size'] === "cp-modal-custom-size" ){
		$tooltip_position = 'top';
	} 

	$close_adjacent_position = ( isset( $a['adjacent_close_position'] ) ? $a['adjacent_close_position'] : 'cp-adjacent-right' );
	$close_position          = ( isset($a['close_position']) ? $a['close_position'] :'' );
 	if($close_adjacent_position!=''){
		switch( $close_adjacent_position ){
			case 'top_left':  $tooltip_position = 'right';
				break;
			case 'top_right': $tooltip_position = 'left';
				break;
		}
	}

	$tooltip_class = $tooltip_style = '';
	if( $a['close_modal_tooltip'] === '1' ) {
		$tooltip_class .= 'cp_closewith_tooltip';
		$tooltip_style .= 'color:'.$a['tooltip_title_color'].';background-color:'.$a['tooltip_background'].';border-top-color: '.$a['tooltip_background'].';';
	}
	$affiliate_fullsize = '';
	if( $a['modal_size'] !== "cp-modal-custom-size" ) {
		$affiliate_fullsize ='cp-affiliate-fullsize';
	}

	/// Generate border radius for form processing
	$pairs = explode( '|', $a['border'] );
	$result = array();
	foreach( $pairs as $pair ){
		$pair = explode( ':', $pair );
		$result[ $pair[0] ] = $pair[1];
	}

	$cssCode1 = '';
	if( !isset( $a['has_content_border'] ) || ( isset( $a['has_content_border'] ) && $a['has_content_border'] ) ) {
		$cssCode1 .= generateBorderCss($a['border']);
	}

	$result['border_width'] = ' ';
	$formProcessCss = '';
	$formProcessCss = $cssCode1 .';';
	$formProcessCss .= 'border-width: 0px;';
	$formProcessCss .= 'box-shadow: 0 0 3px 1px '.$a['modal_overlay_bg_color'].' inset;';

	// check if inline display is set
	$isInline = ( isset( $a['display'] ) && $a['display'] === "inline" ) ? true : false;
	if( $isInline ){
		$a['close_modal'] = "do_not_close";
	}
	
?>			
		<?php 
		//add nounce field to modal 
		$nonce = wp_create_nonce( 'cp-impress-nonce' );?>
		<input type="hidden" class="cp-impress-nonce" name="cp-impress-nonce" value="<?php echo $nonce; ?>">

		</div><!-- .cp-modal-body -->

		<?php
		if ( $edit_link !== '' ) {

			$edit_link_text = 'Edit With ' . CP_PLUS_NAME;

			$edit_link_txt = apply_filters( 'cp_style_edit_link_text', $edit_link_text );

		 	echo "<div class='cp_edit_link'><a target='_blank' href=".$edit_link." rel ='noopener'>".$edit_link_txt."</a></div>";
		}

		$msg_color = isset( $a['message_color'] ) ? $a['message_color'] : '';
		?>

			</div><!-- .cp-modal-content -->
			
            <?php if( isset($a['form_layout']) && $a['form_layout'] != 'cp-form-layout-4' ) { ?>
			<div class="cp-form-processing-wrap" style="<?php echo esc_attr($formProcessCss); ?>;">
				<div class="cp-form-after-submit">
            		<div class ="cp-form-processing" style="">
            			<div class="smile-absolute-loader" style="visibility: visible;">
					        <div class="smile-loader">
					            <div class="smile-loading-bar"></div>
					            <div class="smile-loading-bar"></div>
					            <div class="smile-loading-bar"></div>
					            <div class="smile-loading-bar"></div>
					        </div>
					    </div>
            		</div>
            		<div class ="cp-msg-on-submit" style="color:<?php echo esc_attr( $msg_color); ?>"></div>
            	</div>
            </div>
            <?php } ?>

    		<?php
    		    $close_adj_class = '';
    			$close_adjacent_position = ( isset( $a['adjacent_close_position'] ) ? $a['adjacent_close_position'] : 'cp-adjacent-right' );
	      			switch( $close_adjacent_position ){
						case 'top_left':  $close_adj_class .= ' cp-adjacent-left';
							break;
						case 'top_right': $close_adj_class .= ' cp-adjacent-right';
							break;
						case 'bottom_left': $close_adj_class .= ' cp-adjacent-bottom-left';
							break;
						case 'bottom_right': $close_adj_class .= ' cp-adjacent-bottom-right';
							break;
					}

    		if( $a['close_modal'] === 'close_img' && $a['close_position'] !== 'out_modal' ) { ?>

	      		<?php
	      		if( $a['close_position'] === 'adj_modal' ){
	      			$close_overlay_class = 'cp-adjacent-close';
	      		}else{
	      			$close_overlay_class = 'cp-inside-close';
	      		}
	      		   $close_overlay_class .= $close_adj_class;

	      		?>
		      	<div class="cp-overlay-close <?php echo esc_attr( $close_class ).' '.esc_attr( $close_overlay_class ); ?>" style="width: <?php echo esc_attr( $cp_close_image_width ); ?>">
					<?php if( $a['close_modal_tooltip'] === '1' ) { ?>
	      			<span class=" cp-tooltip-icon cp-inside-tip has-tip cp-tipcontent-<?php echo $a['style_id']; ?>" data-classes="close-tip-content-<?php echo $a['style_id']; ?>" data-offset="20"  data-position="<?php echo esc_attr( $tooltip_position );?>"  title="<?php echo html_entity_decode(stripslashes(esc_attr( $a['tooltip_title'] ) ));?>"  data-color="<?php echo esc_attr( $a['tooltip_title_color'] );?>" data-font-family="<?php echo esc_attr( $a['tooltip_text_font'] );?>" data-bgcolor="<?php echo esc_attr( $a['tooltip_background'] );?>" data-closeid ="cp-tipcontent-<?php echo $a['style_id']; ?>">
	      			<?php } ?>
					<?php echo $close_html; ?>
					<?php if($a['close_modal_tooltip'] === '1'){ ?></span><?php } ?>
		      	</div>

		    <?php } ?>
		</div><!-- .cp-animate-container -->

		<?php if( $isInline ) { ?>
			<span class="cp-modal-inline-end" data-style="<?php echo $style_id; ?>"></span>
		<?php } ?>

    </div><!-- .cp-modal -->

		<?php 
    
    if( $a['affiliate_setting'] === '1'  ) {?>
		        <div class ="cp-affilate-link cp-responsive">
		           <a href="<?php echo $afilate_link ?>" target= "_blank" rel="noopener"><?php echo do_shortcode( html_entity_decode( $a['affiliate_title'] ) ); ?></a>
		        </div>
      	<?php } ?><!-- .affiliate link for fullscreen -->

		<?php if( ( $a['close_position'] === 'out_modal' && $a['close_modal'] !== 'do_not_close') || $a['close_modal'] === 'close_txt' ) { ?>
		    <div class="cp-overlay-close cp-outside-close <?php echo esc_attr( $close_class ); ?> <?php echo $close_adj_class;?>"  style="width: <?php echo esc_attr( $cp_close_image_width ); ?>">
				 <?php if( $a['close_modal_tooltip'] === '1' ) { ?>
					<span class=" cp-close-tooltip cp-tooltip-icon  has-tip cp-tipcontent-<?php echo $a['style_id']; ?>" data-classes="close-tip-content-<?php echo $a['style_id']; ?>" data-position="<?php echo $tooltip_position;?>"  title="<?php echo html_entity_decode(stripslashes(esc_attr( $a['tooltip_title'] ) ));?>"  data-color="<?php echo esc_attr( $a['tooltip_title_color'] );?>"  data-font-family="<?php echo esc_attr( $a['tooltip_text_font'] );?>" data-bgcolor="<?php echo esc_attr( $a['tooltip_background'] );?>" data-closeid ="cp-tipcontent-<?php echo $a['style_id']; ?>" data-offset="20">
				<?php } ?>
				<?php echo $close_html; ?><?php if($a['close_modal_tooltip'] === '1'){ ?></span><?php } ?>
			 </div>
		<?php } ?>
	</div><!-- .cp-overlay -->
</div><!-- .cp-modal-popup-container -->
<?php
 }
}
add_filter( 'cp_modal_global_after', 'cp_modal_global_after_init' );

if( !function_exists('cp_close_image_setup') ) {

	function cp_close_image_setup( $a ) {
		$close_img = $close_img_class = $close_alt = '';
		$close_alt = 'close-link';
		if ( !isset( $a['close_image_src'] ) ) {
			$a['close_image_src'] = 'upload_img';
		}

		if ( $a['close_image_src'] === 'upload_img' ) {

			if( isset($a['close_img'] ) && !empty($a['close_img']) ) {
				if ( strpos($a['close_img'],'http') !== false ) {
				    $close_img = $a['close_img'];
				    if ( strpos($close_img, '|') !== FALSE ) {
						$close_img = explode( '|', $close_img );
						$close_img = $close_img[0];
						$close_img = cp_get_protocol_settings_init($close_img);
					}
				    $close_img_class = 'cp-default-close';
				} else {
					$close_img = apply_filters('cp_get_wp_image_url', $a['close_img'] );
					$close_img = cp_get_protocol_settings_init($close_img);
					
					$close_img_alt =  explode( '|', $a['close_img'] );
					if( sizeof($close_img_alt) > 2 ){
						$close_alt = $close_img_alt[2];
					}
				}
			}
		} else if ( $a['close_image_src'] === 'custom_url' ) {
			$close_img = $a['modal_close_img_custom_url'];
		} else if( $a['close_image_src'] === 'pre_icons' ) {
			$icon_url = CP_PLUGIN_URL."modules/assets/images/" .$a['close_icon']. ".png";
			$close_img = $icon_url;
			$close_img = cp_get_protocol_settings_init($close_img);			
		}

		$close_img_prop = array (
			"close_img" => $close_img,
			"close_img_class" => $close_img_class,
			"close_alt" => $close_alt,
			);
		return $close_img_prop;

	}
}

