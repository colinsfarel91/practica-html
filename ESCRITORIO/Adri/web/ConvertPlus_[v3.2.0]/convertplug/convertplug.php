<?php
/*
Plugin Name: Convert Plus
Plugin URI: https://www.convertplug.com/plus
Author: Brainstorm Force
Author URI: https://www.brainstormforce.com
Version: 3.2.0
Description: Welcome to Convert Plus - the easiest WordPress plugin to convert website traffic into leads. Convert Plus will help you build email lists, drive traffic, promote videos, offer coupons and much more!
Text Domain: smile
*/

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

if( !defined( 'CP_VERSION' ) ) {
	define( 'CP_VERSION', '3.2.0');
}

if( !defined( 'CP_BASE_DIR' ) ) {
	define( 'CP_BASE_DIR', plugin_dir_path( __FILE__ ));
}

if ( ! defined( '__CP_ROOT__' ) ) {
	define( '__CP_ROOT__', dirname( __FILE__ ) );
}

if( !defined( 'CP_BASE_URL' ) ) {
	define( 'CP_BASE_URL', plugin_dir_url( __FILE__ ));
}

if( !defined( 'CP_DIR_NAME' ) ){
	define( 'CP_DIR_NAME', plugin_basename( dirname( __FILE__ ) ) );
}
if( !defined( 'CP_DIR_FILE_NAME' ) ){
	define( 'CP_DIR_FILE_NAME', plugin_basename(__FILE__) );
}

if( !defined( 'CP_PLUS_NAME' ) ){
	define( 'CP_PLUS_NAME', 'Convert Plus' );
}

if( !defined( 'CP_PLUS_SLUG' ) ){
	define( 'CP_PLUS_SLUG', 'convert-plus' );
}

if( is_admin() ){
	register_activation_hook( __FILE__, 'on_cp_activate' );
}

if( !defined( 'CP_PLUGIN_URL' ) ){
	define( 'CP_PLUGIN_URL', plugins_url( '/', __FILE__ ) );
}

define( 'BSF_REMOVE_14058953_FROM_REGISTRATION_LISTING', true );

/**
* Function for activation hook/
*
* @since 1.0
*/
function on_cp_activate() {

	update_option( 'convert_plug_redirect', true );
	update_site_option( 'bsf_force_check_extensions', true );
	update_option( "dismiss-cp-update-notice", false );
	update_site_option( 'bsf_force_check_extensions', true );

	$cp_previous_version = get_option( 'cp_previous_version' );

	if( !$cp_previous_version ) {
		update_option( 'cp_is_new_user', true );
	} else {
		update_option( 'cp_is_new_user', false );
	}

	// save previous version of plugin in option
	update_option( "cp_previous_version", CP_VERSION );

	global $wp_version;
	$wp = '3.5';
	$php = '5.3.2';
    if ( version_compare( PHP_VERSION, $php, '<' ) )
        $flag = 'PHP';
    elseif
        ( version_compare( $wp_version, $wp, '<' ) )
        $flag = 'WordPress';
    else
        return;
    $version = 'PHP' == $flag ? $php : $wp;
    deactivate_plugins( basename( __FILE__ ) );
    wp_die('<p><strong>' . CP_PLUS_NAME . ' </strong> requires <strong>'.$flag.'</strong> version <strong>'.$version.'</strong> or greater. Please contact your host.</p>','Plugin Activation Error',  array( 'response'=>200, 'back_link'=> TRUE ) );

}

if(!class_exists( 'Convert_Plug' )){
	// include Smile_Framework class
	require_once(  CP_BASE_DIR.'/framework/Smile_Framework.php' );

	class Convert_Plug extends Smile_Framework{
		public static $options = array();
		var $paths = array();
		static public $cp_dev_mode = false;
		static public $cp_editor_enable = false;

		function __construct(){

			//	Fall back support for multi fields
			add_action( 'wp_loaded', array( $this,'cp_access_capabilities' ), 1 );
			add_action( 'wp_loaded', array( $this,'cp_set_options' ), 1 );

			$this->paths = wp_upload_dir();
			$this->paths['fonts'] 	= 'smile_fonts';
			$this->paths['fonturl'] = set_url_scheme( trailingslashit( $this->paths['baseurl'] ).$this->paths['fonts'] );

			add_action( 'admin_menu', array( $this,'add_admin_menu' ), 99 );
			add_action( 'admin_menu', array( $this,'add_admin_menu_rename' ), 9999 );
			add_filter( 'custom_menu_order', array($this,'cp_submenu_order') );
			add_action( 'wp_enqueue_scripts', array( $this,'enqueue_front_scripts' ), 10);
			add_action( 'admin_print_scripts', array( $this, 'cp_admin_css' ) );
			add_action( 'admin_enqueue_scripts', array( $this,'cp_admin_scripts' ), 100);
			add_filter( 'bsf_core_style_screens', array( $this, 'cp_add_core_styles' ));
			add_action( 'admin_head', array( $this, 'cp_custom_css' ));
			add_action( 'admin_init', array($this,'cp_redirect_on_activation'));
			add_filter( 'plugin_action_links_' . CP_DIR_FILE_NAME, array( $this, 'cp_action_links' ), 10, 5);
			add_action( 'wp_ajax_cp_display_preview_modal', array( $this, 'cp_display_preview_modal' ) );
			add_action( 'wp_ajax_cp_display_preview_info_bar', array( $this, 'cp_display_preview_info_bar' ) );
			add_action( 'wp_ajax_cp_display_preview_slide_in', array( $this, 'cp_display_preview_slide_in' ) );
			add_action( 'plugins_loaded', array( $this, 'cp_load_textdomain' ) );
			add_filter( 'the_content', array( $this, 'cp_add_content' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'smile_frosty_scripts_from_core' ), 100);

			// de register scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'cp_dergister_scripts' ), 100 );

			require_once(  CP_BASE_DIR.'/admin/ajax-actions.php' );
			require_once( CP_BASE_DIR.'/framework/cp-widgets.php' );
			add_action( 'widgets_init', 'Load_Convertplug_Widget' );

			// minimum requirement for PHP version
			$php = '5.4';

			// If current version is less than minimum requirement, display admin notice
			if ( version_compare( PHP_VERSION, $php, '<' ) ) {
				add_action( 'admin_notices', 'cp_php_version_notice' );
			}

			// delete_option( 'cp_show_rebrand_notice' );

			$is_show_rebrand_notice = get_option( 'cp_show_rebrand_notice' );
			$cp_is_new_user = get_option( 'cp_is_new_user' );

			if( $is_show_rebrand_notice != 'no' && !$cp_is_new_user ) {
				add_action ( "admin_notices", 'cp_rebrand_notice' );
			}

			$data = get_option( 'convert_plug_debug' );

			$display_debug_info = isset($data['cp-display-debug-info']) ? $data['cp-display-debug-info'] : 0;

			if( $display_debug_info ) {
 				add_action( 'admin_footer', array( $this, 'cp_add_debug_info' ) );
 			}

 			//conflict due to imagify plugin
 			add_action( 'wp_print_scripts', array( $this, 'cp_dequeue_script_imagify' ), 999 );

 			add_filter( 'script_loader_tag', array( $this, 'cp_dequeue_script_amazon' ), 999, 3 );

			self::$cp_dev_mode = $data['cp-dev-mode'];

			//skip registration menu
			add_filter( 'bsf_skip_braisntorm_menu', array( $this, 'cp_skip_brainstorm_menu' ) );

			// Add popup license form on plugin list page.
			add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'cp_license_form_and_links' ) );
			add_action( 'network_admin_plugin_action_links_' . plugin_basename( __FILE__ ),  array( $this, 'cp_license_form_and_links' ) );

			//change registration page URL
			add_action( 'bsf_registration_page_url_14058953', array( $this, 'cp_get_registration_page_url' ) );
		}

		/**
		* Skip BSF menue from dashboard
		* @since 3.1.0
		*/
		function cp_skip_brainstorm_menu( $products ) {
			
			$products = array(
				14058953,
				'connects-contact-form-7',
				'connects-woocommerce',
				'connects-ontraport',
				'convertplug-vc',
				'connects-wp-registration-form',
				'connects-wp-comment-form',
				'connects-totalsend',
				'connects-sendreach',
			 );			

			return $products;
		}

		/**
		 * Show action links on the plugin screen.
		 *
		 * @param   mixed $links Plugin Action links.
		 * @return  array        Filtered plugin action links.
		 */
		function cp_license_form_and_links( $links = array() ) {

			if ( function_exists( 'get_bsf_inline_license_form' ) ) {
				$args = array(
					'product_id'         => 14058953,
					'popup_license_form' => true,
					'bsf_license_allow_email'   => true
				);
				return get_bsf_inline_license_form( $links, $args, 'envato' );
			}

			return $links;
		}

		/**
		 * Get registration page url for addon.
		 *
		 * @since  1.0.0
		 * @return String URL of the licnense registration page.
		 */
		public function cp_get_registration_page_url() {

			$url = admin_url( 'plugins.php?bsf-inline-license-form=14058953' );

			if ( is_multisite() ) {
			    $url = network_admin_url( 'plugins.php?bsf-inline-license-form=14058953' );
			}

			return $url;
		}

		/**
		* Set options on load of WordPress
		* @since 2.3.2
		*/
		function cp_set_options() {
			update_option( 'cp_is_displayed_debug_info', false );
		}

		/**
		* Add Convert Plus access capabilities to user roles
		* @since 2.2.0
		*/
		function cp_access_capabilities() {

			if ( is_user_logged_in() ) {
				if ( current_user_can( 'manage_options' ) ) {

					global $wp_roles;
	 				$wp_roles_data = $wp_roles->get_names();
	 				$roles = false;

					$cp_settings = get_option( 'convert_plug_settings' );

					if( isset($cp_settings['cp-access-role']) ) {
						$roles = explode( ",", $cp_settings['cp-access-role'] );
					}

	 				if(!$roles) {
	 					$roles = array();
	 				}

	 				// give access to administrator
	 				$roles[] = 'administrator';

	 				foreach ( $wp_roles_data as $key => $value ) {
	 					$role = get_role( $key );

	 					if ( in_array( $key, $roles ) ) {
	 						$role->add_cap( 'access_cp' );
	 					} else {
	 						$role->remove_cap( 'access_cp' );
	 					}
	 				}
 				}
			}
		}

		/**
		 * Add a class at the end of the post for after content trigger
		 *
		 * @since 1.0.3
		 */
		function cp_add_content( $content ) {
			if( is_single() || is_page() ){
				$content_str_array = cp_display_style_inline();
				$content .= '<span class="cp-load-after-post"></span>';
				$content = $content_str_array[0].$content;
				$content .= $content_str_array[1];
			}
			return $content;
		}

		/**
		 * Load plugin text domain.
		 *
		 * @since 1.0.0
		 */
		function cp_load_textdomain() {
		  load_plugin_textdomain( 'smile', false, CP_DIR_NAME . '/lang' );
		}

		/**
		 * Handle style preview ajax request for modal
		 *
		 * @since 1.0.0
		 */
		function cp_display_preview_modal(){
			require_once( CP_BASE_DIR.'/modules/modal/style-preview-ajax.php' );
			die();
		}

		/**
		 * Handle style preview ajax request for info bar
		 *
		 * @since 1.0.0
		 */
		function cp_display_preview_info_bar(){
			require_once( CP_BASE_DIR.'/modules/info_bar/style-preview-ajax.php' );
			die();
		}

		/**
		 * Ajax Callback for slide in style preview
		 *
		 * @since 1.0.0
		 */
		function cp_display_preview_slide_in(){
			require_once( CP_BASE_DIR.'/modules/slide_in/style-preview-ajax.php' );
			die();
		}

		/**
		 * Adds settings link in plugins action
		 * @param  array $actions
		 * @since 1.0
		 * @return array
		 */
		function cp_action_links( $actions, $plugin_file ) {
		    static $plugin;

			if ( !isset($plugin) )
				$plugin = CP_DIR_FILE_NAME;
			if ( $plugin === $plugin_file ) {
				$settings = array('settings' => '<a href="' . admin_url( 'admin.php?page='.CP_PLUS_SLUG.'&view=settings' ) . '">Settings</a>');
				$actions = array_merge($settings, $actions);
			}
			return $actions;
		}

		/**
		* Enqueue scripts and styles for insert shortcode popup
		* @since 1.0
		*/
		function cp_admin_scripts($hook) {

			//	Store all global CSS variables
			wp_enqueue_script( 'cp-css-generator', CP_PLUGIN_URL.'framework/assets/js/css-generator.js', array( 'jquery') );

			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_style( 'wp-color-picker' );

			$data  =  get_option( 'convert_plug_debug' );

			if ( strpos( $hook , CP_PLUS_SLUG ) !== false ) {
				wp_enqueue_style( 'cp-connects-icon', CP_PLUGIN_URL.'modules/assets/css/connects-icon.css' );
			}

			if( isset( $_GET['hidemenubar'] ) ) {

				//	Common File for Convert Plus
				wp_enqueue_script( 'cp-ckeditor', CP_PLUGIN_URL.'modules/assets/js/ckeditor/ckeditor.js' );
				wp_enqueue_script( 'cp-contact-form', CP_PLUGIN_URL.'modules/assets/js/convertplug.js', array( 'jquery', 'cp-ckeditor' ) );

				wp_enqueue_style( 'cp-perfect-scroll-style', CP_PLUGIN_URL.'admin/assets/css/perfect-scrollbar.min.css' );
				wp_enqueue_script( 'cp-perfect-scroll-js', CP_PLUGIN_URL.'admin/assets/js/perfect-scrollbar.jquery.js', array( "jquery" ) );
			}

			if( isset( $_GET['style-view'] ) && ( $_GET['style-view'] === "edit" || $_GET['style-view'] === 'variant' ) ) {

				wp_enqueue_script( 'cp-perfect-scroll-js', CP_PLUGIN_URL.'admin/assets/js/perfect-scrollbar.jquery.js', array( "jquery" ) );
				wp_enqueue_style( 'cp-perfect-scroll-style', CP_PLUGIN_URL.'admin/assets/css/perfect-scrollbar.min.css' );
				wp_enqueue_style( 'cp-animate', CP_PLUGIN_URL.'modules/assets/css/animate.css' );

				// ace editor files
				if( !isset( $_GET['hidemenubar'] ) ) {
					wp_enqueue_script( 'cp-ace', CP_PLUGIN_URL.'admin/assets/js/ace.js' , array( "jquery" ) );
					wp_enqueue_script( 'cp-ace-mode-css', CP_PLUGIN_URL.'admin/assets/js/mode-css.js' , array( "jquery" ) );
					wp_enqueue_script( 'cp-ace-mode-xml', CP_PLUGIN_URL.'admin/assets/js/mode-xml.js' , array( "jquery" ) );
					wp_enqueue_script( 'cp-ace-worker-css', CP_PLUGIN_URL.'admin/assets/js/worker-css.js' , array( "jquery" ) );
					wp_enqueue_script( 'cp-ace-worker-xml', CP_PLUGIN_URL.'admin/assets/js/worker-xml.js' , array( "jquery" ) );
				}
			}

			if( $hook === CP_PLUS_SLUG.'_page_contact-manager' ) {
				wp_enqueue_style( 'cp-contacts', CP_PLUGIN_URL.'admin/contacts/css/cp-contacts.css' );
				if( isset($_GET['view']) && $_GET['view'] === 'analytics' ) {

					wp_enqueue_script( 'bsf-charts-js', CP_PLUGIN_URL.'admin/assets/js/chart.js', false, false, true );
					wp_enqueue_script( 'bsf-charts-bar-js', CP_PLUGIN_URL.'admin/assets/js/chart.bar.js', false, false, true );
					wp_enqueue_script( 'bsf-charts-donut-js', CP_PLUGIN_URL.'admin/assets/js/chart.donuts.js', false, false, true );
					wp_enqueue_script( 'bsf-charts-line-js', CP_PLUGIN_URL.'admin/assets/js/Chart.Line.js', false, false, true );
					wp_enqueue_script( 'bsf-charts-polararea-js', CP_PLUGIN_URL.'admin/assets/js/Chart.PolarArea.js', false, false, true );
					wp_enqueue_script( 'bsf-charts-scripts', CP_PLUGIN_URL.'admin/contacts/js/connect-analytics.js', false, false, true );
				}

				wp_enqueue_style( 'css-select2', CP_PLUGIN_URL.'admin/assets/select2/select2.min.css' );
				wp_enqueue_script( 'convert-select2', CP_PLUGIN_URL.'admin/assets/select2/select2.min.js', false, '2.4.0.3', true );

				// sweet alert
				wp_enqueue_script( 'cp-swal-js', CP_PLUGIN_URL.'admin/assets/js/sweetalert.min.js', false, false, true );
				wp_enqueue_style( 'cp-swal-style', CP_PLUGIN_URL.'admin/assets/css/sweetalert.css' );
			}

			if( !isset( $_GET['hidemenubar'] ) && strpos( $hook , CP_PLUS_SLUG ) !== false ) {

				if( ( isset( $_GET['variant-test'] ) && $_GET['variant-test'] !== 'edit' )
					|| ( isset( $_GET['style-view'] ) && $_GET['style-view'] !== 'edit' )
					|| ( isset( $_GET['style-view'] ) && $_GET['style-view'] === 'edit' && isset( $_GET['theme'] ) && $_GET['theme'] === 'countdown' )
					|| !isset( $_GET['style-view'] ) )
				{

					wp_enqueue_style( 'smile-bootstrap-datetimepicker', CP_PLUGIN_URL.'modules/assets/css/bootstrap-datetimepicker.min.css' );

					wp_enqueue_script( 'smile-moment-with-locales', CP_PLUGIN_URL.'modules/assets/js/moment-with-locales.js', false, false, true );

					if( self::$cp_dev_mode === '1' ) {
						wp_enqueue_script( 'smile-bootstrap-datetimepicker', CP_PLUGIN_URL.'modules/assets/js/bootstrap-datetimepicker.js', false, false, true );

					} else {
						wp_enqueue_script( 'smile-bootstrap-datetimepicker', CP_PLUGIN_URL.'modules/assets/js/bootstrap-datetimepicker.min.js', false, false, true );
					}
				}

				// sweet alert
				wp_enqueue_script( 'cp-swal-js', CP_PLUGIN_URL.'admin/assets/js/sweetalert.min.js', false, false, true );
				wp_enqueue_style( 'cp-swal-style', CP_PLUGIN_URL.'admin/assets/css/sweetalert.css' );

			}

			// count down style scripts
			if( isset($_GET['theme']) && $_GET['theme'] === 'countdown' ) {
				wp_register_style( 'cp-countdown-style', CP_PLUGIN_URL.'modules/assets/css/jquery.countdown.css' );
				wp_register_script( 'cp-counter-plugin-js', CP_PLUGIN_URL. 'modules/assets/js/jquery.plugin.min.js', array( 'jquery' ), null, null, true );
				wp_register_script( 'cp-countdown-js', CP_PLUGIN_URL. 'modules/assets/js/jquery.countdown.js', array( 'jquery' ), null, null, true );
				wp_register_script( 'cp-countdown-script', CP_PLUGIN_URL. 'modules/assets/js/jquery.countdown.script.js', array( 'jquery' ), null, null, true );
			}

			if ( strpos( $hook , CP_PLUS_SLUG ) !== false ) {
				// developer mode
				if( self::$cp_dev_mode === '1' ) {
					wp_enqueue_style( 'convert-admin', CP_PLUGIN_URL.'admin/assets/css/admin.css' );
					wp_enqueue_style( 'convert-about', CP_PLUGIN_URL.'admin/assets/css/about.css' );
					wp_enqueue_style( 'convert-preview-style', CP_PLUGIN_URL.'admin/assets/css/preview-style.css' );
					wp_enqueue_style( 'jquery-ui-accordion', CP_PLUGIN_URL.'admin/assets/css/accordion.css' );
					wp_enqueue_style( 'css-select2', CP_PLUGIN_URL.'admin/assets/select2/select2.min.css' );
					wp_enqueue_style( 'cp-contacts', CP_PLUGIN_URL.'admin/contacts/css/cp-contacts.css' );
					wp_enqueue_style( 'cp-swal-style', CP_PLUGIN_URL.'admin/assets/css/sweetalert.css' );
				} else {
					wp_enqueue_style( 'convert-admin-css', CP_PLUGIN_URL.'admin/assets/css/admin.min.css');
				}
			}

			if ( strpos( $hook , CP_PLUS_SLUG ) !== false && self::$cp_dev_mode === '1' ) {
				if ( !wp_script_is( 'cp-frosty-script', 'enqueued' ) ) {
					wp_enqueue_script( 'cp-frosty-script', CP_PLUGIN_URL.'admin/assets/js/frosty.js', array( 'jquery' ), null, null, true );
				}
			}

		}

		/**
		* Enqueue font style
		* @since 1.0
		*/
		function cp_admin_css(){
			wp_enqueue_style( 'cp-admin-css', CP_PLUGIN_URL.'admin/assets/css/font.css' );
		}

		/**
		* Enqueue scripts and styles on frontend
		* @since 1.0
		*/
		function enqueue_front_scripts(){

			if( isset( $_GET['hidemenubar'] ) ) {

				//	Common File for Convert Plus
				wp_enqueue_script( 'cp-ckeditor', CP_PLUGIN_URL.'modules/assets/js/ckeditor/ckeditor.js' );
				wp_enqueue_script( 'cp-contact-form', CP_PLUGIN_URL.'modules/assets/js/convetplug.js', array( 'jquery', 'cp-ckeditor', 'smile-customizer-js' ) );

				if( !is_user_logged_in() || ( defined( "LOGGED_IN_COOKIE" ) && empty( $_COOKIE[LOGGED_IN_COOKIE] ) ) ){
					wp_clear_auth_cookie();
					wp_logout();
					auth_redirect();
				}

				wp_enqueue_script( 'cp-perfect-scroll-js', CP_PLUGIN_URL.'admin/assets/js/perfect-scrollbar.jquery.js', array( "jquery" ) );
			}
			//js for both perfect-scrollbar.jquery.js and idle-timer.min.js
			if( self::$cp_dev_mode === '1' ) { 
				wp_register_script( 'cp-perfect-scroll-js', CP_PLUGIN_URL.'admin/assets/js/perfect-scrollbar.jquery.js', array( "jquery" ) );

				wp_register_script( 'cp-ideal-timer-script', CP_PLUGIN_URL.'modules/assets/js/idle-timer.min.js', array( 'jquery' ), null, null, true );

				wp_register_style( 'cp-perfect-scroll-style', CP_PLUGIN_URL.'admin/assets/css/perfect-scrollbar.min.css' );
				
			}else{
				wp_register_script( 'cp-module-main-js', CP_PLUGIN_URL.'modules/assets/js/cp-module-main.js', array( "jquery" ) );
				wp_register_style( 'cp-module-main-style', CP_PLUGIN_URL.'modules/assets/css/cp-module-main.css' );
			}

		}

		/**
		* Add main manu for Convert Plus
		* @since 1.0
		*/
		function add_admin_menu(){
			$page = add_menu_page( CP_PLUS_NAME . ' Dashboard', CP_PLUS_NAME, 'access_cp', CP_PLUS_SLUG, array($this,'admin_dashboard'), 'div' );
			add_action( 'admin_print_scripts-' . $page, array($this,'convert_admin_scripts'));
			add_action( 'admin_footer-'. $page, array($this,'cp_admin_footer') );

			if(defined('BSF_MENU_POS'))
				$required_place = BSF_MENU_POS;
			else
				$required_place = 200;

			if(function_exists('bsf_get_free_menu_position'))
				$place = bsf_get_free_menu_position($required_place,1);
			else
				$place = null;

			if( !defined ( 'BSF_MENU_POS' ) ) {
				define('BSF_MENU_POS', $place);
			}
			global $menu;
			$menuExist = false;
			foreach($menu as $item) {
				if(strtolower($item[0]) === strtolower('Brainstorm')) {
					$menuExist = true;
				}
			}

			$contacts = add_submenu_page(
				CP_PLUS_SLUG,
				__("Connects","smile"),
				__("Connects","smile"),
				"access_cp",
				"contact-manager",
				array($this, 'contacts_manager')
			);
			add_action( 'admin_footer-'. $contacts, array($this,'cp_admin_footer') );

			$resources_page = add_submenu_page(
				CP_PLUS_SLUG,
				__("Resources","contacts_manager"),
				__("Resources","contacts_manager"),
				"access_cp",
				"cp-resources",
				array($this, 'cp_resources')
			);
			add_action( 'admin_footer-'. $resources_page, array($this,'cp_admin_footer') );

			$cust_page = add_submenu_page(
			        'contacts_manager',
			        'Hidden!',
			        'Hidden!',
			        'access_cp',
			        'cp_customizer',
			        array($this, 'cp_customizer_render_hidden_page')
			    );

			add_action( 'admin_footer-'. $cust_page, array($this,'cp_customizer_render_hidden_page') );

			// section wise menu
			global $bsf_section_menu;
			$section_menu = array(
				'menu' => 'cp-resources',
				'is_down_arrow' => true
			);
			$bsf_section_menu[] = $section_menu;

			$google_manager = add_submenu_page(
				CP_PLUS_SLUG,
				__("Google Font Manager","smile"),
				__("Google Fonts","smile"),
				"access_cp",
				"bsf-google-font-manager",
				array($this, 'cp_font_manager')
			);

			add_submenu_page(
				CP_PLUS_SLUG,
				__("Knowledge Base","smile"),
				__("Knowledge Base","smile"),
				"access_cp",
				"knowledge-base",
				array($this, 'cp_redirect_to_kb' )
			);

			$Ultimate_Google_Font_Manager = new Ultimate_Google_Font_Manager;
			add_action( 'admin_print_scripts-' . $google_manager, array($Ultimate_Google_Font_Manager,'admin_google_font_scripts'));
            add_action( 'admin_footer-'. $google_manager, array($this,'cp_admin_footer') );
		}

		function cp_customizer_render_hidden_page() {
			require_once( CP_BASE_DIR.'preview.php' );
		}

		function cp_font_manager() {
			$Ultimate_Google_Font_Manager = new Ultimate_Google_Font_Manager;
			$Ultimate_Google_Font_Manager->ultimate_font_manager_dashboard();
		}
		function add_admin_menu_rename(){
			global $menu, $submenu;
			if( isset( $submenu[CP_PLUS_SLUG][0][0] ) ) {
			    $submenu[CP_PLUS_SLUG][0][0] = 'Dashboard';
			}
		}

		function cp_resources() {
			$icon_manager = false;
			require_once(CP_BASE_DIR.'admin/resources.php');
		}

		function cp_submenu_order($menu_ord) {
			global $submenu;

		    if(!isset($submenu[CP_PLUS_SLUG]))
		    	return false;

		    $temp_resource = $temp_connect = $temp_google_font_manager = $temp_font_icon_manager = $temp_in_sync = $temp_knowledge_base = array();
		    foreach ($submenu[CP_PLUS_SLUG] as $key => $cp_submenu) {
		    	if($cp_submenu[2] === 'cp-resources') {
		    		$temp_resource = $submenu[CP_PLUS_SLUG][$key];
		    		unset($submenu[CP_PLUS_SLUG][$key]);
		    	}
		    	if($cp_submenu[2] === 'contact-manager') {
		    		$temp_connect = $submenu[CP_PLUS_SLUG][$key];
		    		unset($submenu[CP_PLUS_SLUG][$key]);
		    	}
		    	if($cp_submenu[2] === 'bsf-font-icon-manager') {
		    		$temp_font_icon_manager = $submenu[CP_PLUS_SLUG][$key];
		    		unset($submenu[CP_PLUS_SLUG][$key]);
		    	}
		    	if($cp_submenu[2] === 'bsf-extensions-14058953') {
		    		$temp_addons = $submenu[CP_PLUS_SLUG][$key];
		    		unset($submenu[CP_PLUS_SLUG][$key]);
		    	}
		    	if($cp_submenu[2] === 'bsf-google-font-manager') {
		    		$temp_google_font_manager = $submenu[CP_PLUS_SLUG][$key];
		    		unset($submenu[CP_PLUS_SLUG][$key]);
		    	}
		    	if($cp_submenu[2] === 'knowledge-base') {
		    		$temp_knowledge_base= $submenu[CP_PLUS_SLUG][$key];
		    		unset($submenu[CP_PLUS_SLUG][$key]);
		    	}
		    	if($cp_submenu[2] === 'cp-wp-comment-form') {
		    		$temp_wp_comment_form = $submenu[CP_PLUS_SLUG][$key];
		    		unset($submenu[CP_PLUS_SLUG][$key]);
		    	}
		    	if($cp_submenu[2] === 'cp-wp-registration-form') {
		    		$temp_wp_registration_form = $submenu[CP_PLUS_SLUG][$key];
		    		unset($submenu[CP_PLUS_SLUG][$key]);
		    	}
		    	if($cp_submenu[2] === 'cp-woocheckout-form') {
		    		$temp_woocheckout_form = $submenu[CP_PLUS_SLUG][$key];
		    		unset($submenu[CP_PLUS_SLUG][$key]);
		    	}
		    	if($cp_submenu[2] === 'cp-contact-form7') {
		    		$temp_contact_form7 = $submenu[CP_PLUS_SLUG][$key];
		    		unset($submenu[CP_PLUS_SLUG][$key]);
		    	}

		    }

		    array_filter($submenu[CP_PLUS_SLUG]);

	    	if(!empty($temp_resource)) {
	    		array_push($submenu[CP_PLUS_SLUG], $temp_resource);
	    	}
	    	if(!empty($temp_connect)) {
	    		array_push($submenu[CP_PLUS_SLUG], $temp_connect);
	    	}
	    	if(!empty($temp_addons)) {
	    		array_push($submenu[CP_PLUS_SLUG], $temp_addons);
	    	}
	    	if(!empty($temp_google_font_manager)) {
	    		array_push($submenu[CP_PLUS_SLUG], $temp_google_font_manager);
	    	}
	    	if(!empty($temp_knowledge_base)) {
	    		array_push($submenu[CP_PLUS_SLUG], $temp_knowledge_base);
	    	}
	    	if(!empty($temp_font_icon_manager)) {
	    		array_push($submenu[CP_PLUS_SLUG], $temp_font_icon_manager);
	    	}
	    	if(!empty($temp_wp_comment_form)) {
	    		array_push($submenu[CP_PLUS_SLUG], $temp_wp_comment_form);
	    	}
	    	if(!empty($temp_wp_registration_form)) {
	    		array_push($submenu[CP_PLUS_SLUG], $temp_wp_registration_form);
	    	}
	    	if(!empty($temp_woocheckout_form)) {
	    		array_push($submenu[CP_PLUS_SLUG], $temp_woocheckout_form);
	    	}
	    	if(!empty($temp_contact_form7)) {
	    		array_push($submenu[CP_PLUS_SLUG], $temp_contact_form7);
	    	}

		    return $menu_ord;
		}

		/**
		* Load scripts and styles on admin area of Convert Plus
		* @since 1.0
		*/
		function convert_admin_scripts() {

			wp_enqueue_script( 'jQuery' );
			wp_enqueue_style( 'thickbox' );

			$data  =  get_option( 'convert_plug_debug' );

			// developer mode
			if( self::$cp_dev_mode === '1' ) {

				// accordion
				wp_enqueue_script( 'convert-accordion-widget', CP_PLUGIN_URL.'admin/assets/js/jquery.widget.min.js' );
				wp_enqueue_script( 'convert-accordion', CP_PLUGIN_URL.'admin/assets/js/accordion.js');
				wp_enqueue_script( 'cp-frosty-script', CP_PLUGIN_URL.'admin/assets/js/frosty.js', array( 'jquery' ), null, null, true );
				wp_enqueue_script( 'convert-admin', CP_PLUGIN_URL.'admin/assets/js/admin.js',array('cp-frosty-script'), null, null, true );

				// shuffle js scripts
				wp_enqueue_script( 'smile-jquery-modernizer', CP_PLUGIN_URL.'modules/assets/js/jquery.shuffle.modernizr.js','','',true);
				wp_enqueue_script( 'smile-jquery-shuffle', CP_PLUGIN_URL.'modules/assets/js/jquery.shuffle.min.js','','',true);
				wp_enqueue_script( 'smile-jquery-shuffle-custom', CP_PLUGIN_URL.'modules/assets/js/shuffle-script.js','','',true);

				// sweet alert
				wp_enqueue_script( 'cp-swal-js', CP_PLUGIN_URL.'admin/assets/js/sweetalert.min.js', false, false, true );

			} else {
				wp_enqueue_script( 'cp-frosty-script', CP_PLUGIN_URL.'admin/assets/js/frosty.js', array( 'jquery' ), null, null, true );
				wp_enqueue_script( 'convert-admin', CP_PLUGIN_URL.'admin/assets/js/admin.min.js','','',true);
			}

			wp_localize_script( 'convert-admin', 'cplus_vars', array(
				'delete_notice'  	 => __( 'You will not be able to recover this selected', 'smile' ),
				'confirm_delete' 	 => __( 'Yes, delete it!', 'smile' ),
				'cancel_delete'  	 => __( 'No, cancel it!', 'smile' ),
				'delete_conf_notice' => __( 'Style you have selected has been deleted.', 'smile' ),
				)
			);

			if( ( isset( $_GET['style-view'] ) && ( $_GET['style-view'] === "edit" || $_GET['style-view'] === "variant" ) ) || !isset( $_GET['style-view'] ) ) {

				wp_enqueue_script( 'convert-select2', CP_PLUGIN_URL.'admin/assets/select2/select2.min.js', false, '2.4.0.1');

			}

			// REMOVE WP EMOJI
			remove_action('wp_head', 'print_emoji_detection_script', 7);
			remove_action('wp_print_styles', 'print_emoji_styles');

			remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
			remove_action( 'admin_print_styles', 'print_emoji_styles' );

		}

		/**
		 * Add footer link for dashboar
		 * @since 1.0.1
		*/
		function cp_admin_footer() {
			echo'<div id="wpfooter" role="contentinfo" class="cp_admin_footer">
				        <p id="footer-left" class="alignleft">
				        <span id="footer-thankyou">Thank you for using <a href="https://www.convertplug.com/plus" target="_blank" rel="noopener" >' . CP_PLUS_NAME . '</a>.</span>   </p>
				    <p id="footer-upgrade" class="alignright">';
				       _e( "Version", "smile" ); echo ' '.CP_VERSION;
				        ;echo  '</p>
				    <div class="clear"></div>
				</div>';
		}

		/**
		* Load convertPlug dashboard
		* @since 1.0
		*/
		function admin_dashboard(){
			require_once(CP_BASE_DIR.'/admin/admin.php');
		}

		/**
		* Load convertPlug contacts manager
		* @since 1.0
		*/
		function contacts_manager(){
			require_once(CP_BASE_DIR.'/admin/contacts/admin.php');
		}

		function cp_add_core_styles($hooks) {

		    $contactsPage_hook = CP_PLUS_SLUG.'_page_contact-manager';
		    $cpmainPage_hook = 'toplevel_page_'.CP_PLUS_SLUG;
		    array_push($hooks,$contactsPage_hook,$cpmainPage_hook);
		    return $hooks;
		}


		/**
		* Redirects to the premium version of MailChimp for WordPress (uses JS)
		*/
		function cp_redirect_to_kb() {

			?><script type="text/javascript">window.location.replace('<?php echo admin_url(); ?>admin.php?page=<?php echo CP_PLUS_SLUG;?>&view=knowledge_base'); </script><?php
		}

		/**
		* Load frosty scripts from bsf core
		* @since 2.1.0
		*/
		//function load_frosty_scripts_from_core($hooks) {
		function smile_frosty_scripts_from_core($hook) {
		
			// page hooks array where we need frosty scripts to load
			$array = array(
				'toplevel_page_'.CP_PLUS_SLUG,
				CP_PLUS_SLUG.'_page_smile-modal-designer',
				CP_PLUS_SLUG.'_page_smile-info_bar-designer',
				CP_PLUS_SLUG.'_page_smile-slide_in-designer',
				CP_PLUS_SLUG.'_page_contact-manager',
				CP_PLUS_SLUG.'_page_role-manager',
				'admin_page_cp_customizer',
				CP_PLUS_SLUG.'_page_cp-wp-registration-form'
			);
			
			if(strpos( $hook , CP_PLUS_SLUG ) !== false){
				//array_push($hooks, $hook);
				if ( !wp_script_is( 'cp-frosty-script', 'enqueued' ) ) {
					wp_enqueue_script( 'cp-frosty-script', CP_PLUGIN_URL.'admin/assets/js/frosty.js', array( 'jquery' ), null, null, true );
				}
				if ( !wp_style_is( 'cp-frosty-style', 'enqueued' ) ) {
					wp_enqueue_style( 'cp-frosty-style', CP_PLUGIN_URL.'admin/assets/css/frosty.css' );
				}
			}
			//return $hooks;
		}
		

		/**
		* Retrieve and store modules into the static variable $modules
		* @accepts    ->  array of modules in form of "Module Name" => "Module Main File"
		* @since 1.0
		*/
		public static function convert_plug_store_module($modules_array){
			$result = false;
			if(!empty($modules_array)){
				self::$modules = $modules_array;
				$result = true;
			}
			return $result;
		}

		/**
		 * Created default campaign on activation
		 *
		 * @since 1.0
		*/
		function create_default_campaign(){

			// create default campaign
			$smile_lists = get_option('smile_lists');
			if(!$smile_lists) {
				$data = array();
				$list = array(
					"date"           => date("d-m-Y"),
					"list-name"      => "First",
					"list-provider"  => "Convert Plug",
					"list"           => "",
					"provider_list"  => ""
					);

				$data[] = $list;
				update_option('smile_lists',$data);
			}
		}

		/**
		* Redirect on activation hook
		*
		* @since 1.0
		*/
		function cp_redirect_on_activation(){

			if( get_option('convert_plug_redirect') == true ) {
				update_option('convert_plug_redirect',false);
				$this->create_default_campaign();
				if(!is_multisite()) :
					wp_redirect(admin_url('admin.php?page='.CP_PLUS_SLUG));
				endif;
			}
		}

		/**
		 * Add custom css for customizer admin page
		 *
		 * @since 2.0.1
		*/
		function cp_custom_css($hook) {
			if( isset( $_GET['page'] ) && $_GET['page'] === 'cp_customizer' ) {

			  echo '<style>
			    #adminmenuwrap,
			    #adminmenuback,
			    #wpadminbar,
			    #wpfooter,
			    .media-upload-form .notice,
			    .media-upload-form div.error,
			    .update-nag,
			    .updated,
			    .wrap .notice,
			    .wrap div.error,
			    .wrap div.updated,
			    .notice-warning,
			    #wpbody-content .error,
			    #wpbody-content .notice {
			  		display: none !important;
				}
			  </style>';

			   //Remove WooCommerce's annoying update message
			   remove_action( 'admin_notices', 'woothemes_updater_notice' );

			   //Remove admin notices
			   remove_action( 'admin_notices', 'update_nag', 3 );
			}
		}

		/**
		* Deregister scripts on customizer page
		*
		* @since 2.3.2
		*/
		function cp_dergister_scripts($hook) {
			$data    =  get_option( 'convert_plug_settings' );
			$psval   = isset( $data['cp-plugin-support'] ) ? $data['cp-plugin-support'] : 1;

			if( $psval ) {

	 			$page_hooks = array(
					CP_PLUS_SLUG.'_page_smile-modal-designer',
					CP_PLUS_SLUG.'_page_smile-info_bar-designer',
					CP_PLUS_SLUG.'_page_smile-slide_in-designer',
					'admin_page_cp_customizer'
				);

				if( in_array( $hook, $page_hooks ) ) {

					if( ( isset( $_GET['style-view'] ) && ( $_GET['style-view'] === 'edit' || $_GET['style-view'] === 'variant'  ) )  || isset( $_GET['hidemenubar'] ) )  {

						global $wp_scripts;
			        	$scripts = $wp_scripts->registered;
			        	$deregistered_scripts = array();

			        	if( is_array($scripts) ) {
				        	foreach ($scripts as $key => $script) {

				        		$source = $script->src;

				        		// if script is registered by plugin other than ConvertPlg OR by Theme
				        		if( ( strpos( $source, "wp-content/plugins" ) && !strpos( $source, "wp-content/plugins/". CP_DIR_NAME ) ) || strpos( $source, "wp-content/themes" ) ) {
				        			//var_dump($script->handle);
				        			if( isset( $script->handle ) ) {
					        			$handle = $script->handle;
					        			$source = $script->src;

					        			$deregistered_scripts[$source] = $handle;

					        			// deregister script handle
					        			wp_deregister_script( $handle );
					        		}
				        		}

				        	}
				        }

				        if( !empty($deregistered_scripts) ) {
				        	update_option( 'cp_scripts_debug_info', $deregistered_scripts );
				        }

				    }
				}
			}
		}

		function cp_dequeue_script_imagify($hook) {
			if( isset( $_GET['page'] ) ){
				$page_name =  esc_attr( $_GET['page'] );

				$page_hooks = array(
					'smile-modal-designer',
					'smile-info_bar-designer',
					'smile-slide_in-designer',
					'admin_page_cp_customizer',
					'contact-manager',
					CP_PLUS_SLUG,
				);				
				
				if( in_array( $page_name, $page_hooks ) ) {	

					wp_dequeue_script( 'chartjs' );
					wp_dequeue_script( 'bsf-core-frosty' );
					wp_dequeue_style( 'bsf-core-frosty-style' );
					wp_dequeue_style( 'imagify-css-sweetalert' );
					wp_dequeue_script( 'imagify-js-admin' );
					wp_dequeue_script( 'imagify-js-sweetalert' );

					if( function_exists( 'wpjobster_admin_stylesheet' ) ){
						remove_action( 'admin_head', 'wpjobster_admin_stylesheet' );
					}
				}
			}
		}

		/**
		* Exclude js from  amazone_link plugin
		*
		* @since 3.1.1
		*/
		function cp_dequeue_script_amazon( $tag, $handle, $src){

			$page_name =  isset($_GET['page']) ? esc_attr( $_GET['page'] ): '';

				$page_hooks = array(
					'smile-modal-designer',
					'smile-info_bar-designer',
					'smile-slide_in-designer',
					'admin_page_cp_customizer',
					'contact-manager',
					CP_PLUS_SLUG,
				);				
				
				if( in_array( $page_name, $page_hooks ) ) {	
					if ( 'jquery_ui' == $handle || 'aalb_admin_js' == $handle || 'handlebars_js' == $handle || 'aalb_sha2_js' == $handle  ){						
						$tag = '';			
					}	
				}	  
			return  $tag;
		}


		/**
		* Display debug info for excluded scripts
		*
		* @since 2.3.2
		*/
		function cp_add_debug_info() {

			$is_displayed_info = get_option( 'cp_is_displayed_debug_info' );

			// if debug info is not already displayed
			if( !$is_displayed_info ) {

				$screen = get_current_screen();

				$current_page_hook = $screen->base;

				$page_hooks = array(
					CP_PLUS_SLUG.'_page_smile-modal-designer',
					CP_PLUS_SLUG.'_page_smile-info_bar-designer',
					CP_PLUS_SLUG.'_page_smile-slide_in-designer'
				);

				if( in_array( $current_page_hook, $page_hooks ) && !isset($_GET['hidemenubar']) ) {

					update_option( "cp_is_displayed_debug_info", true );

					$debug_info = get_option( 'cp_scripts_debug_info' );

					$debug_info_html = "<!-- CP Debug Information - List of the JS disabled on customizer screen ----------- \n";

					if( is_array($debug_info) ) {
						foreach ($debug_info as $src => $handle) {
							$string = $handle . " :- " . $src;
							$debug_info_html .= $string ."\n";
						}
					}

					$debug_info_html .= "<!-- End - CP Debug Information -->";

					echo $debug_info_html;
				}
			}
		}

	}


	/**
	* Public Function to search style from multidimentional array
	* @accepts		-> array of styles and style name to be searched
	* @return		-> array key if style is found in the given array
	* @since 1.0
	*/
	function search_style($array, $style)
	{
		if( is_array($array) ) {
			foreach ( $array as $key => $data )
			{
				$data_style = isset($data['style_id']) ? $data['style_id'] : '';
				if ($data_style === $style)
					return $key;
			}
		}
	}

	/**
	* Public function for accepting requests for adding new module in the convert plug
	* @accepts    ->  array of modules in form of "Module Name" => "Module Main File"
	* @since 1.0
	*/
	function convert_plug_add_module($modules_array){
		return Convert_Plug::convert_plug_store_module($modules_array);
	}

	/*function cp_editor_styles() {
    	add_editor_style( plugins_url('admin/assets/css/cp-editor.css',__FILE__) );
	}*/

	// load modules
	require_once(CP_BASE_DIR.'/modules/config.php');

}
new Smile_Framework;
new Convert_Plug;

// load google fonts class
if( is_admin() ){
 require_once(CP_BASE_DIR.'/framework/Ultimate_Font_Manager.php');
}

/// set global variables
global $cp_analytics_start_time,$cp_analytics_end_time,$colorPallet,$cp_default_dateformat;

$colorPallet = array (
	'rgba(26, 188, 156,1.0)',
	'rgba(46, 204, 113,1.0)',
	'rgba(52, 152, 219,1.0)',
	'rgba(155, 89, 182,1.0)',
	'rgba(52, 73, 94,1.0)',
	'rgba(241, 196, 15,1.0)',
	'rgba(230, 126, 34,1.0)',
	'rgba(231, 76, 60,1.0)',
	'rgba(236, 240, 241,1.0)',
	'rgba(149, 165, 166,1.0)'
);

$cp_analytics_end_time = current_time( 'd-m-Y');
$date = date_create($cp_analytics_end_time);
date_sub($date, date_interval_create_from_date_string('9 days'));
$cp_analytics_start_time = date_format($date, 'd-m-Y');

if ( get_magic_quotes_gpc() ) {
    $_POST      = array_map( 'stripslashes_deep', $_POST );
    $_GET       = array_map( 'stripslashes_deep', $_GET );
    $_COOKIE    = array_map( 'stripslashes_deep', $_COOKIE );
    $_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
}
// bsf core
$bsf_core_version_file = realpath(dirname(__FILE__).'/admin/bsf-core/version.yml');
if(is_file($bsf_core_version_file)) {
	global $bsf_core_version, $bsf_core_path;
	$bsf_core_dir = realpath(dirname(__FILE__).'/admin/bsf-core/');
	$version = file_get_contents($bsf_core_version_file);
	if(version_compare($version, $bsf_core_version, '>')) {
		$bsf_core_version = $version;
		$bsf_core_path = $bsf_core_dir;
	}
}
add_action('init', 'bsf_core_load', 999);
if(!function_exists('bsf_core_load')) {
	function bsf_core_load() {
		global $bsf_core_version, $bsf_core_path;
		if(is_file(realpath($bsf_core_path.'/index.php'))) {
			if ( is_admin() ) {
				include_once realpath($bsf_core_path.'/index.php');
			}
		}
	}
}
add_filter('bsf_core_style_screens', 'cp_bsf_core_style_hooks');
function cp_bsf_core_style_hooks($hooks) {
	$resources_page_hook = CP_PLUS_SLUG.'_page_cp-resources';
	array_push($hooks, $resources_page_hook);
	return $hooks;
}

/**
 * Register Convertplug Addons installer menu
 */
if ( ! function_exists( 'cp_bsf_extensions_menu' ) ) {

	function cp_bsf_extensions_menu( $reg_menu ) {

		$reg_menu = get_site_option( 'bsf_installer_menu', $reg_menu );
		$_dir = CP_BASE_DIR;

		if( '' == $reg_menu ) {	
			$reg_menu = array();
		}

		$bsf_cp_id = bsf_extract_product_id( $_dir );

		$reg_menu['ConvertPlus'] = array(
			'parent_slug'	=>	CP_PLUS_SLUG,
			'page_title'	=>	__('Addons','smile'),
			'menu_title' 	=>	__('Addons','smile'),
			'product_id' 	=>	$bsf_cp_id,
		);

		update_site_option( 'bsf_installer_menu', $reg_menu );
		return $reg_menu;
	}

}

add_filter( 'bsf_installer_menu', 'cp_bsf_extensions_menu' );
if ( is_multisite() ) {
	add_action( 'admin_head', 'cp_bsf_extensions_menu' );
}

/**
 * Multisite Extension menue for ConvertPlus
 */
function cp_register_options_page() {

  $page = add_menu_page( 'Convert Plus Add-ons',__( 'Convert Plus Add-ons','smile'), 'access_cp', 'bsf-extensions-14058953', '', 'div' );
}

if ( is_multisite() ) {
	add_action( 'network_admin_menu','cp_register_options_page', 9 );
}


/**
 * Heading for the extensions installer screen
 *
 * @return String: Heading to which will appear on Extensions installer page
 */
function cp_bsf_extensioninstaller_heading() {
	return CP_PLUS_NAME . ' Addons';
}

add_filter( 'bsf_extinstaller_heading_14058953', 'cp_bsf_extensioninstaller_heading' );

/**
 * Sub Heading for the extensions installer screen
 *
 * @return String: Sub Heading to which will appear on Extensions installer page
 */
function cp_bsf_extensioninstaller_subheading() {
	return 'Add-ons extend the functionality of ' . CP_PLUS_NAME . '. With these addons, you can connect with third party softwares, integrate new features and make ' . CP_PLUS_NAME . ' even more powerful.';
}

add_filter( 'bsf_extinstaller_subheading_14058953', 'cp_bsf_extensioninstaller_subheading' );
/**
 * Heading for the extensions installer screen
 *
 * @return String: Heading to which will appear on Extensions installer page
 */
function cp_extensioninstaller_heading() {
	return CP_PLUS_NAME . ' Addons';
}

add_filter( 'bsf_extinstaller_heading_14058953', 'cp_extensioninstaller_heading' );

/**
 * Sub Heading for the extensions installer screen
 *
 * @return String: Sub Heading to which will appear on Extensions installer page
 */
function cp_extensioninstaller_subheading() {
	return 'Add-ons extend the functionality of ' . CP_PLUS_NAME . '. With these addons, you can connect with third party softwares, integrate new features and make ' . CP_PLUS_NAME . ' even more powerful.';
}

add_filter( 'bsf_extinstaller_subheading_14058953', 'cp_extensioninstaller_subheading' );


// BSF CORE commom functions
if(!function_exists('bsf_get_option')) {
	function bsf_get_option($request = false) {
		$bsf_options = get_option('bsf_options');
		if(!$request)
			return $bsf_options;
		else
			return (isset($bsf_options[$request])) ? $bsf_options[$request] : false;
	}
}
if(!function_exists('bsf_update_option')) {
	function bsf_update_option($request, $value) {
		$bsf_options = get_option('bsf_options');
		$bsf_options[$request] = $value;
		return update_option('bsf_options', $bsf_options);
	}
}
add_action( 'wp_ajax_bsf_dismiss_notice', 'bsf_dismiss_notice');
if(!function_exists('bsf_dismiss_notice')) {
	function bsf_dismiss_notice() {
		$notice = $_POST['notice'];
		$x = bsf_update_option($notice, true);
		echo ($x) ? true : false;
		die();
	}
}

add_action('admin_init', 'bsf_core_check',10);
if(!function_exists('bsf_core_check')) {
	function bsf_core_check() {
		if(!defined('BSF_CORE')) {
			if(!bsf_get_option('hide-bsf-core-notice'))
				add_action( 'admin_notices', 'bsf_core_admin_notice' );
		}
	}
}

	

add_action('admin_init', 'cp_bsf_update_bg_type',10);
if(!function_exists('cp_bsf_update_bg_type')) {
	function cp_bsf_update_bg_type() {
		update_option( 'cp_new_bg_type', false );
		$cp_bg_type = get_option( 'cp_new_bg_type' );
		if( !$cp_bg_type ) {
			update_option( 'cp_new_bg_type', true );
		} else {
			update_option( 'cp_new_bg_type', false );
		}				
	}
}

if(!function_exists('bsf_core_admin_notice')) {
	function bsf_core_admin_notice() {
		?>
		<script type="text/javascript">
		(function($){
			$(document).ready(function(){
				$(document).on( "click", ".bsf-notice", function() {
					var bsf_notice_name = $(this).attr("data-bsf-notice");
				    $.ajax({
				        url: ajaxurl,
				        method: 'POST',
				        data: {
				            action: "bsf_dismiss_notice",
				            notice: bsf_notice_name
				        },
				        success: function(response) {
				        	console.log(response);
				        }
				    })
				})
			});
		})(jQuery);
		</script>
		<div class="bsf-notice update-nag notice is-dismissible" data-bsf-notice="hide-bsf-core-notice">
            <p><?php _e( 'License registration and extensions are not part of plugin/theme anymore. Kindly download and install "BSF CORE" plugin to manage your licenses and extensins.', 'bsf' ); ?></p>
        </div>
		<?php
	}
}

if(isset($_GET['hide-bsf-core-notice']) && $_GET['hide-bsf-core-notice'] === 're-enable') {
	$x = bsf_update_option('hide-bsf-core-notice', false);
}

add_action( 'wp_ajax_cp_dismiss_notice', 'cp_dismiss_notice');
if(!function_exists('cp_dismiss_notice')) {
	function cp_dismiss_notice() {
		$notice = $_POST['notice'];
		$x = update_option($notice, true);
		echo ($x) ? true : false;
		die();
	}
}

/*
 * Function to display admin notice for outdated php version
*/
if( !function_exists( 'cp_php_version_notice' ) ) {
	function cp_php_version_notice() {
	    ?>
	    <div class="notice notice-warning cp-php-warning is-dismissible">
		        <p><?php _e( "Your server seems to be running outdated, unsupported and vulnerable version of PHP. You are advised to contact your host and upgrade to PHP 5.6 or greater.", 'smile' ); ?></p>
	    </div>
	    <?php
	}
}

/*
 * Function to display admin notice for plugin rebranding
*/
if( !function_exists( 'cp_rebrand_notice' ) ) {
	function cp_rebrand_notice() {
	    ?>

	    <script type="text/javascript">
		(function($){
			$(document).ready(function(){
				$(document).on( "click", ".cp-rebranding-warning", function() {
				    $.ajax({
				        url: ajaxurl,
				        method: 'POST',
				        data: {
				            action: "cp_dismiss_rebrand_notice"
				        },
				        success: function(response) {
				        	console.log(response);
				        }
				    })
				})
			});
		})(jQuery);
		</script>

	    <div class="notice notice-warning cp-rebranding-warning is-dismissible">
	    	<?php $link = 'https://convertplug.com/plus/convertplug-is-now-convertplus/'; ?>

		    <p><?php echo sprintf( __( 'Howdy! ConvertPlug is now rebranded as '.CP_PLUS_SLUG.'. Please read the announcement <a rel="noopener" target="_blank" href="%s" rel="noopener">here</a>.', 'smile' ), $link ); ?>
		    </p>
	    </div>
	    <?php
	}
}

// end of common functions
