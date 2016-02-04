<?php
/**
 * Main class
 */


class Stripe_Subscriptions {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */

	protected $version = '1.2.7';
	
	/**
	 * Required version of SC Pro
	 *
	 * @since   1.0.1
	 *
	 * @var     string
	 */

	protected $sc_required = '2.3.7';

	/**
	 * Unique identifier
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'stripe-subscriptions';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;
	
	/**
	 * Product Name for EDD SL Updater
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $sc_sub_edd_sl_item_name = 'WP Simple Pay Pro for Stripe - Subscriptions Add-on';
	
	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {
		// Define plugin wide variables
		$this->setup_constants();
		
		// Load plugin text domain
		add_action( 'plugins_loaded', array( $this, 'plugin_textdomain' ) );
		
		// Check for base plugin
		add_action( 'admin_init', array( $this, 'base_inactive_notice' ) );
		
		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		
		// Include our needed files
		add_action( 'init', array( $this, 'includes' ), 0 );
		
		add_action( 'init', array( $this, 'init' ), 1 );
		
		add_action( 'admin_init', array( $this, 'setup_edd_option' ), 0 );
		
		// Run check for SC Pro version
		add_action( 'admin_notices', array( $this, 'check_sc_pro_required' ) );
	}
	
	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function plugin_textdomain() {
		load_plugin_textdomain(
			'sc_sub',
			false,
			SC_SUB_DIR_PATH . 'languages/'
		);

	}
	
	public function setup_edd_option() {
			
			$sc_edd_licenses = get_option( 'sc_edd_licenses' );
			
			$sc_edd_licenses[ $this->plugin_slug ] = array( 
				'item_name' => $this->sc_sub_edd_sl_item_name,
				'version'   => $this->version,
				'file'      => SC_SUB_MAIN_FILE,
			);
			
			update_option( 'sc_edd_licenses', $sc_edd_licenses );
	}
	
	
	public function check_sc_pro_required() {
		if( class_exists( 'Stripe_Checkout_Pro' ) ) {
			
			$sc_version = get_option( 'sc_version' );
			
			if( version_compare( $sc_version, $this->sc_required, '<' ) ) {
				include_once( SC_SUB_DIR_PATH . 'views/admin-notice-stripe-pro.php' );
			}
		}
	}

	/**
	 * Check for existence for base plugin (SC Pro)
	 *
	 * @since     1.0.0
	 */
	public function base_inactive_notice() {
		
		if ( ! class_exists( 'Stripe_Checkout_Pro' ) ) {
			include_once( 'views/admin-sc-pro-deactivated-notice.php' );
		}
	}
	
	/**
	 * Define any plugin wide constants we need
	 * 
	 * @since 1.0.0
	 */
	public function setup_constants() {
		if( ! defined( 'SC_SUB_PLUGIN_SLUG' ) ) {
			define( 'SC_SUB_PLUGIN_SLUG', $this->plugin_slug );
		}
	}
	
	/**
	 * Include necessary files
	 *
	 * @since     1.0.0
	 */
	public function includes() {
		
		include_once( SC_SUB_DIR_PATH . 'classes/class-stripe-subscriptions-admin.php' );
		include_once( SC_SUB_DIR_PATH . 'classes/class-stripe-subscriptions-scripts.php' );
		include_once( SC_SUB_DIR_PATH . 'classes/class-stripe-subscriptions-functions.php' );
		include_once( SC_SUB_DIR_PATH . 'classes/class-stripe-subscriptions-shortcodes.php' );
		
		if( ! is_admin() ) {
			if( ! class_exists( 'Shortcode_Tracker' ) ) {
				include_once( 'includes/class-shortcode-tracker.php' );
			}
		}
	}
	
	public function init() {
		Stripe_Subscriptions_Admin::get_instance();
		Stripe_Subscriptions_Scripts::get_instance();
		Stripe_Subscriptions_Functions::get_instance();
		Stripe_Subscriptions_Shortcodes::get_instance();
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'sc_sub',
			false,
			dirname( plugin_basename( SC_SUB_MAIN_FILE ) ) . '/languages/'
		);
	}
	
	/**
	 * Return the title of the plugin
	 * 
	 * @since 1.0.0
	 */
	public static function get_plugin_title() {
		return __( 'WP Simple Pay Pro for Stripe - Subscriptions Add-on', 'sc_sub' );
	}
	
	public static function get_required_plugin_title() {
		return __( 'WP Simple Pay Pro for Stripe', 'sc_sub' );
	}
	
	public static function get_plugin_slug() {
		return self::get_instance()->plugin_slug;
	}
	
	public static function get_plugin_version() {
		return self::get_instance()->version;
	}
}
