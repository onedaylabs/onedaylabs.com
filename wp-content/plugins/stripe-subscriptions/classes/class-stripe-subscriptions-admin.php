<?php

/**
 * Subscriptions Admin class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Stripe_Subscriptions_Admin' ) ) {
	class Stripe_Subscriptions_Admin {
		
		protected static $instance = null;
		
		private function __construct() {
			
			add_action( 'init', array( $this, 'upgrade_plugin' ) , 3 );
			
		}
		
		public function upgrade_plugin() {
			global $sc_options;
			
			if ( null === $sc_options->get_setting_value( 'sub_upgrade_has_run' ) ) {
				include_once( SC_SUB_DIR_PATH . 'classes/class-stripe-subscriptions-upgrade.php' );
				Stripe_Subscriptions_Upgrade::get_instance();
			}
			
			$sc_options->add_setting( 'sub_upgrade_has_run', 1 );
		}
		
		public static function update_stripe_pro_notice() {
			require_once( SC_SUB_DIR_PATH . 'views/admin-notice-stripe-pro.php' );
		}
		
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}
	}
}