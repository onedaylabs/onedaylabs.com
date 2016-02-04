<?php

/**
 * Main Pro class
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Stripe_Checkout_Pro' ) ) {
	class Stripe_Checkout_Pro extends Stripe_Checkout {

		/**
		 * Plugin version, used for cache-busting of style and script file references.
		 *
		 * @since   2.0.0
		 *
		 * @var     string
		 */
		public $version = '2.3.7';

		/**
		 * Unique identifier for your plugin.
		 *
		 * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
		 * match the Text Domain file header in the main plugin file.
		 *
		 * @since    2.0.0
		 *
		 * @var      string
		 */
		public $plugin_slug = 'stripe-checkout-pro';

		/**
		 * Instance of this class.
		 *
		 * @since    2.0.0
		 *
		 * @var      object
		 */
		protected static $instance = null;

		/**
		 * Slug of the plugin screen.
		 *
		 * @since    2.0.0
		 *
		 * @var      string
		 */
		protected $plugin_screen_hook_suffix = null;

		

		/**
		 * Initialize the plugin by setting localization, filters, and administration functions.
		 *
		 * @since     2.0.0
		 */
		private function __construct() {
			
			$this->includes();

			add_action( 'init', array( $this, 'register_settings' ), 0 );

			add_action( 'init', array( $this, 'init' ), 1 );
		}
		
		public function register_settings() { 
			parent::register_settings();
		}
		
		/*
		 * Include necessary files
		 */
		public function includes() {
			
			parent::includes();
			
			require_once( SC_DIR_PATH_PRO . 'classes/class-stripe-checkout-pro-licenses.php' );
			require_once( SC_DIR_PATH_PRO . 'classes/class-shortcode-tracker.php' );
			require_once( SC_DIR_PATH_PRO . 'classes/class-stripe-checkout-pro-functions.php' );
			require_once( SC_DIR_PATH_PRO . 'classes/class-stripe-checkout-pro-admin.php' );
			require_once( SC_DIR_PATH_PRO . 'classes/class-stripe-checkout-system-status.php' );
			
		}
		
		/*
		 * Create instances for classes
		 */
		public function init() {
			Stripe_Checkout_Pro_Licenses::get_instance();
			Stripe_Checkout_Scripts::get_instance();
			Stripe_Checkout_Shortcodes::get_instance();
			
			if ( is_admin() ) {
				Stripe_Checkout_Admin::get_instance();
				Stripe_Checkout_Pro_Admin::get_instance();
				//Stripe_Checkout_Upgrade_Link::get_instance();
				Stripe_Checkout_Notices::get_instance();
				Stripe_Checkout_System_Status::get_instance();
			} else {
				Stripe_Checkout_Misc::get_instance();
			}
			
			// Need to leave outside of is_admin check or the AJAX will not work properly
			Stripe_Checkout_Pro_Functions::get_instance();
		}
		
		/**
		 * Return localized base plugin title.
		 *
		 * @since     1.0.0
		 *
		 * @return    string
		 */
		public static function get_plugin_title() {
			return __( 'WP Simple Pay Pro for Stripe', 'sc' );
		}

		public static function get_plugin_menu_title() {
			return __( 'Simple Pay Pro', 'sc' );
		}

		/**
		 * Return an instance of this class.
		 *
		 * @since     2.0.0
		 *
		 * @return    object    A single instance of this class.
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		/**
		 * Fired when the plugin is activated.
		 *
		 * @since    2.0.0
		 */
		public static function activate() {
			// Add value to indicate that we should show admin install notice.
			update_option( 'sc_show_admin_install_notice', 1 );

			if( ! function_exists( 'curl_version' ) ) {
				wp_die( sprintf( __( 'You must have the cURL extension enabled in order to run %s. Please enable cURL and try again. <a href="%s">Return to Plugins</a>.', 'sc' ), 
						self::get_plugin_title(), get_admin_url( '', 'plugins.php' ) ) );
			}
		}
	}
}
