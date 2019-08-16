<?php

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

// action to add templates
function cp_add_slide_in_template( $args, $preset, $module ) {

	if( $module == 'slide_in' ) {

		$modal_temp_array = array (

			"fashion" =>
				array (
					"optin", // theme slug for template
					"Fashion", // template name
					CP_PLUGIN_URL.'modules/slide_in/assets/demos/optin/optin.html', // HTML file for template
					"http://downloads.brainstormforce.com/convertplug/presets/screenshot_fashion.png", // screen shot
					CP_PLUGIN_URL.'modules/slide_in/assets/demos/optin/customizer.js', // customizer js for template
					"All,Offers", // categories
					"Shortcode,Canvas,HTML,Custom", // tags
					"fashion" // template unique slug
			),
			"free_checklist" =>
				array (
					"optin_widget", // theme slug for template
					"Free Checklist", // template name
					CP_PLUGIN_URL.'modules/slide_in/assets/demos/optin_widget/optin_widget.html', // HTML file for template
					"http://downloads.brainstormforce.com/convertplug/presets/screenshot_checklist.png", // screen shot
					CP_PLUGIN_URL.'modules/slide_in/assets/demos/optin_widget/customizer.js', // customizer js for template
					"All,slide in", // categories
					"Shortcode,Canvas,HTML,Custom", // tags
					"free_checklist" // template unique slug
			),
			"free_audit" =>
				array (
					"optin", // theme slug for template
					"Free Audit", // template name
					CP_PLUGIN_URL.'modules/slide_in/assets/demos/optin/optin.html', // HTML file for template
					"http://downloads.brainstormforce.com/convertplug/presets/screenshot_audit.png", // screen shot
					CP_PLUGIN_URL.'modules/slide_in/assets/demos/optin/customizer.js', // customizer js for template
					"All,slide in", // categories
					"Shortcode,Canvas,HTML,Custom", // tags
					"free_audit" // template unique slug
			),
			"upcoming_event_in_new_york!" =>
				array (
					"optin", // theme slug for template
					"Upcoming Event In New York", // template name
					CP_PLUGIN_URL.'modules/slide_in/assets/demos/optin/optin.html', // HTML file for template
					"http://downloads.brainstormforce.com/convertplug/presets/screenshot_events.png", // screen shot
					CP_PLUGIN_URL.'modules/slide_in/assets/demos/optin/customizer.js', // customizer js for template
					"All,slide in", // categories
					"Shortcode,Canvas,HTML,Custom", // tags
					"upcoming_event_in_new_york!" // template unique slug
			),

			"apartment_finder" =>
				array (
					"optin_widget", // theme slug for template
					"Apartment Finder", // template name
					CP_PLUGIN_URL.'modules/slide_in/assets/demos/optin_widget/optin_widget.html', // HTML file for template
					"http://downloads.brainstormforce.com/convertplug/presets/screenshot_apartement.png", // screen shot
					CP_PLUGIN_URL.'modules/slide_in/assets/demos/optin_widget/customizer.js', // customizer js for template
					"All,slide in", // categories
					"Shortcode,Canvas,HTML,Custom", // tags
					"apartment_finder" // template unique slug
			),
			"slide_in_social_left" =>
				array (
					"social_fly_in", // theme slug for template
					"Slide In Social Left", // template name
					CP_PLUGIN_URL.'modules/slide_in/assets/demos/social_fly_in/social_fly_in.html', // HTML file for template
					"http://downloads.brainstormforce.com/convertplug/presets/screenshot_slide_in_social_left.png", // screen shot
					CP_PLUGIN_URL.'modules/slide_in/assets/demos/social_fly_in/customizer.js', // customizer js for template
					"All,slide in", // categories
					"Shortcode,Canvas,HTML,Social", // tags
					"slide_in_social_left" // template unique slug
			),
			"slide_in_social_right" =>
				array (
					"social_widget_box", // theme slug for template
					"Slide In Social Right", // template name
					CP_PLUGIN_URL.'modules/slide_in/assets/demos/social_widget_box/social_widget_box.html', // HTML file for template
					"http://downloads.brainstormforce.com/convertplug/presets/screenshot_slidein_widget.png", // screen shot
					CP_PLUGIN_URL.'modules/slide_in/assets/demos/social_widget_box/customizer.js', // customizer js for template
					"All,slide in", // categories
					"Shortcode,Canvas,HTML,Social", // tags
					"slide_in_social_right" // template unique slug
			),
			"floating_bar_1" =>
				array (
					"floating_social_bar", // theme slug for template
					"Floating bar 1", // template name
					CP_PLUGIN_URL.'modules/slide_in/assets/demos/floating_social_bar/floating_social_bar.html', // HTML file for template
					"http://downloads.brainstormforce.com/convertplug/presets/screenshot_floating_bar_1.png", // screen shot
					CP_PLUGIN_URL.'modules/slide_in/assets/demos/floating_social_bar/customizer.js', // customizer js for template
					"All,slide in", // categories
					"Shortcode,Canvas,HTML,Social", // tags
					"floating_bar_1" // template unique slug
			),
			"pro_health" =>
				array (
					"optin", // theme slug for template
					"Pro Health", // template name
					CP_PLUGIN_URL.'modules/slide_in/assets/demos/optin/optin.html', // HTML file for template
					"http://downloads.brainstormforce.com/convertplug/presets/screenshot_pro_health.png", // screen shot
					CP_PLUGIN_URL.'modules/slide_in/assets/demos/optin/customizer.js', // customizer js for template
					"All,slide in", // categories
					"Shortcode,Canvas,HTML,Health", // tags
					"pro_health" // template unique slug
			),
			"get_insurance" =>
				array (
					"optin", // theme slug for template
					"Get Insurance", // template name
					CP_PLUGIN_URL.'modules/slide_in/assets/demos/optin/optin.html', // HTML file for template
					"http://downloads.brainstormforce.com/convertplug/presets/screenshot_insurence.png", // screen shot
					CP_PLUGIN_URL.'modules/slide_in/assets/demos/optin/customizer.js', // customizer js for template
					"All,slide in", // categories
					"Shortcode,Canvas,HTML,Insurance", // tags
					"get_insurance" // template unique slug
			),
			"tech_blogging_ideas" =>
				array (
					"optin", // theme slug for template
					"Tech Blogging Ideas", // template name
					CP_PLUGIN_URL.'modules/slide_in/assets/demos/optin/optin.html', // HTML file for template
					"http://downloads.brainstormforce.com/convertplug/presets/screenshot_teching_blog.png", // screen shot
					CP_PLUGIN_URL.'modules/slide_in/assets/demos/optin/customizer.js', // customizer js for template
					"All,slide in", // categories
					"Shortcode,Canvas,HTML,Tech,Blogg,Idea", // tags
					"tech_blogging_ideas" // template unique slug
			),
			"fashion_tips" =>
				array (
					"optin_widget", // theme slug for template
					"Fashion Tips", // template name
					CP_PLUGIN_URL.'modules/slide_in/assets/demos/optin_widget/optin_widget.html', // HTML file for template
					"http://downloads.brainstormforce.com/convertplug/presets/screenshot_exclusive_fashion_preset.png", // screen shot
					CP_PLUGIN_URL.'modules/slide_in/assets/demos/optin_widget/customizer.js', // customizer js for template
					"All,slide in", // categories
					"Shortcode,Canvas,HTML,Tip,Fashion,Idea", // tags
					"fashion_tips" // template unique slug
			),
		);

		if( $preset  !== '' ) {
			$temp_arr = $modal_temp_array[$preset];
			$modal_temp_array = array();
			$modal_temp_array[$preset] = $temp_arr;
			$args = array_merge( $args, $modal_temp_array );
		} else {
			$args = array_merge( $args, $modal_temp_array );
		}
	}

	return $args;
}
