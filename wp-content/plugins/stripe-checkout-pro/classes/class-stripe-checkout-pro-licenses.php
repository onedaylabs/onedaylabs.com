<?php

/**
 * License class
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Stripe_Checkout_Pro_Licenses' ) ) {
	class Stripe_Checkout_Pro_Licenses {
		
		protected static $instance = null;

		/**
		 * Product Name for EDD SL Updater
		 *
		 * @since    2.0.0
		 *
		 * @var      string
		 */
		protected static $sc_edd_sl_item_name = 'WP Simple Pay Pro for Stripe';

		/**
		 * Author Name for EDD SL Updater
		 *
		 * @since    2.0.0
		 *
		 * @var      string
		 */
		protected static $sc_edd_sl_author = 'Moonstone Media';
		
		private function __construct() {
			
			if ( ! defined( 'SC_EDD_STORE_URL' ) ) {
				define( 'SC_EDD_STORE_URL', 'https://wpsimplepay.com/' );
			}
			
			// Add our uption to use for EDD
			add_action( 'admin_init', array( $this, 'setup_edd_option' ), 0 );
			
			// Run EDD software licensing plugin updater.
			add_action( 'admin_init', array( $this, 'edd_sl_updater' ) );
			
			add_action( 'wp_ajax_activate_license', array( $this, 'activate_license' ) );
		}
		
		public function setup_edd_option() {
			
			global $base_class;
			
			$sc_edd_licenses = get_option( 'sc_edd_licenses' );
			
			$sc_edd_licenses[ $base_class->plugin_slug ] = array( 
				'item_name' => self::$sc_edd_sl_item_name,
				'version'   => $base_class->version,
				'file'      => SC_MAIN_FILE,
			);
			
			update_option( 'sc_edd_licenses', $sc_edd_licenses );
		}
		
		/**
		 * Easy Digital Download Plugin Updater Code.
		 *
		 * @since     2.0.0
		 */
		public function edd_sl_updater() {
			global $sc_options, $base_class;

			if( ! class_exists( 'SP_EDD_SL_Plugin_Updater' ) ) {
				// load our custom updater
				require_once( SC_DIR_PATH_PRO . 'libraries/SP_EDD_SL_Plugin_Updater.php' );
			}
			
			$sc_edd_licenses = get_option( 'sc_edd_licenses' );
			if ( null !== $sc_options->get_setting_value( 'sc_license_key' ) ) {
			
				$license_key = $sc_options->get_setting_value( 'sc_license_key' );

				if ( ! ( false === $sc_edd_licenses ) ) {

					if ( is_array( $sc_edd_licenses ) ) {

						foreach( $sc_edd_licenses as $plugin ) {

							// setup the updater
							$edd_updater[] = new SP_EDD_SL_Plugin_Updater( SC_EDD_STORE_URL, $plugin['file'], array(
									'version'   => $plugin['version'], // current plugin version number
									'license'   => $license_key, // license key (used get_option above to retrieve from DB)
									'item_name' => $plugin['item_name'], // name of this plugin
									'author'    => self::$sc_edd_sl_author, // author of this plugin
								)
							);
						}
					}
				}
			}
		}
		
		/*
		 * AJAX function to activate a license with EDD
		 */
		public function activate_license() {
			
			global $sc_options;
			
			$sc_license = '';

			$current_license = $_POST['license'];
			$item            = $_POST['item'];
			$action          = $_POST['sc_action'];
			$id              = $_POST['id'];

			// Need to trim the id of the excess stuff so we can update our option later
			$length = strpos( $id, ']' ) - strpos( $id, '[' );
			$id     = substr( $id, strpos( $id, '[' ) + 1, $length - 1 );

			// Do activation
			$activate_params = array(
				'edd_action' => $action,
				'license'    => $current_license,
				'item_name'  => urlencode( $item ),
				'url'        => home_url()
			);

			$response = wp_remote_post( SC_EDD_STORE_URL, array( 'timeout' => 15, 'body' => $activate_params, 'sslverify' => false ) );
			
			//echo '<pre>' . print_r( $response, true ) . '</pre>';
			//die(); exit;

			if( is_wp_error( $response ) )
			{

				echo 'ERROR';

				die();
			}

			$activate_data = json_decode( wp_remote_retrieve_body( $response ) );
			
			//echo '<pre>' . print_r( $activate_data, true ) . '</pre>';
			//die(); exit;

			if ( $activate_data->license == 'valid' ) {
				$sc_license = 'valid';
				$sc_options->add_setting( $id, $current_license );
			} else if( $activate_data->license == 'deactivated' ) {
				$sc_license = 'deactivated';
			} else {
				$sc_license = $activate_data->error;
			}

			update_option( 'sc_license', $sc_license );
			
			//echo $activate_data->license;
			
			echo $sc_license;

			die();
		}
		
		/*
		 * AJAX function to check a license with EDD
		 */
		public static function check_license( $license, $item ) {
			
			$check_params = array(
				'edd_action' => 'check_license',
				'license'    => $license,
				'item_name'  => urlencode( $item ),
				'url'        => home_url()
			);

			$response = wp_remote_post( SC_EDD_STORE_URL, array( 'timeout' => 15, 'body' => $check_params, 'sslverify' => false ) );
			
			if( is_wp_error( $response ) )
			{
				return 'error';
			}

			$is_valid = json_decode( wp_remote_retrieve_body( $response ) );
			
			if( ! empty( $is_valid ) ) {
				return json_decode( wp_remote_retrieve_body( $response ) )->license;
			} else {
				return 'notfound';
			}
		}
		
		/**
		 * Return an instance of this class.
		 *
		 * @since     1.0.0
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
	}
}

