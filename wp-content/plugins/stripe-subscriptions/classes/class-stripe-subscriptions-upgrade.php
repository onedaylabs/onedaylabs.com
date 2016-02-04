<?php

/**
 * Upgrade class.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Stripe_Subscriptions_Upgrade' ) ) {
	class Stripe_Subscriptions_Upgrade {
		
		protected static $instance = null;
		
		private function __construct() {
			$this->run_all_upgrades();
		}
		
		public function run_all_upgrades() {
			global $sc_options;
			
			// If this option does not exist yet then this is upgrading from < 1.2.0
			$version = $sc_options->get_setting_value( 'sc_sub_version' );
			
			if ( null !== $version && null === $sc_options->get_setting_value( 'sub_upgrade_has_run' ) ) {
				$this->sc_v120_upgrade();
			}

			$new_version = Stripe_Subscriptions::get_plugin_version();
			$sc_options->add_setting( 'sc_sub_version', $new_version );
		}

		private function sc_v120_upgrade() {
	
			global $sc_options;

			// sc_settings_master holds a merge of all settings arrays tied to the Stripe plugin. This includes any settings that are implemented by users.
			$master = get_option( 'sc_settings_master' );
			
			if ( isset( $master['sc_sub_license_key'] ) ) {
				$sc_options->add_setting( 'sc_sub_license_key', $master['sc_sub_license_key'] );
			}
			
			$sc_options->add_setting( 'sub_had_upgrade', 1 );
			
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