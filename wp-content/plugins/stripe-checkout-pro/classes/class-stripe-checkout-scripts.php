<?php

/**
 * Scripts class file
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Stripe_Checkout_Scripts_Pro' ) ) {
	
	class Stripe_Checkout_Scripts {
		
		// class instance variable
		public static $instance = null;
		
		private $min = null;
		
		/*
		 * Class constructor
		 */
		private function __construct() {
			
			$this->min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
			
			// Load scripts when posts load so we know if we need to include them or not
			add_filter( 'the_posts', array( $this, 'load_scripts' ) );
			
			// Add public CSS
			add_action( 'init', array( $this, 'enqueue_public_styles' ) );
			
			// Enqueue admin styles.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
			
			// Add public styles.
			add_action( 'init', array( $this, 'enqueue_public_scripts' ) );
			
			// Enqueue admin scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
			
			// Add public JS
			add_action( 'init', array( $this, 'enqueue_public_scripts' ) );
			
			// Localize the script for shortcodes
			add_action( 'wp_footer', array( $this, 'localize_shortcode_script' ) );
		}
		
		/**
		 * Function that will actually determine if the scripts should be used or not
		 * 
		 * @since 1.0.0
		 */
		public function load_scripts( $posts ){

			global $sc_options, $base_class;

			if ( empty( $posts ) ) {
				return $posts;
			}

			foreach ( $posts as $post ) {
				if ( ( false !== strpos( $post->post_content, '[stripe' ) ) || ( null !== $sc_options->get_setting_value( 'always_enqueue' ) ) ) {
					// Load CSS
					wp_enqueue_style( $base_class->plugin_slug . '-public' );
					
					// Load JS
					wp_enqueue_script( $base_class->plugin_slug . '-public' );

					break;
				}
			}

			return $posts;
		}
		
		/**
		 * Load public facing CSS
		 * 
		 * @since 1.0.0
		 */
		public function enqueue_public_styles() {
			
			global $sc_options, $base_class;

			wp_register_style( 'stripe-checkout-button', 'https://checkout.stripe.com/v3/checkout/button.css', array(), $base_class->version );

			wp_register_style( 'pikaday', SC_DIR_URL . 'assets/css/vendor/pikaday' . $this->min . '.css', array(), $base_class->version );
			
			if ( null === $sc_options->get_setting_value( 'disable_css' ) ) {
				wp_register_style( $base_class->plugin_slug . '-public', SC_DIR_URL . 'assets/css/public-pro' . $this->min . '.css', array( 'stripe-checkout-button', 'pikaday' ), $base_class->version );
			}
		}

		/**
		 * Enqueue admin-specific style sheets for this plugin's admin pages only.
		 *
		 * @since     1.0.0
		 */
		public function enqueue_admin_styles() {
			
			global $base_class;
			
			if ( Stripe_Checkout_Admin::get_instance()->viewing_this_plugin() ) {
				wp_enqueue_style( $base_class->plugin_slug .'-admin-styles', SC_DIR_URL . 'assets/css/admin-main' . $this->min . '.css', array(), $base_class->version );
				wp_enqueue_style( $base_class->plugin_slug .'-admin-styles-pro', SC_DIR_URL . 'assets/css/admin-pro' . $this->min . '.css', array(), $base_class->version );
				wp_enqueue_style( $base_class->plugin_slug .'-toggle-switch', SC_DIR_URL . 'assets/css/vendor/toggle-switch' . $this->min . '.css', array(), $base_class->version );
			}
		}
		
		/*
		 * Enqueue public facing scripts
		 */
		public function enqueue_public_scripts() {
			
			global $base_class;
			
			// Register Parsley JS validation library.
			wp_register_script( 'parsley', SC_DIR_URL . 'assets/js/vendor/parsley' . $this->min . '.js', array( 'jquery' ), $base_class->version, true );

			wp_register_script( 'stripe-checkout', 'https://checkout.stripe.com/checkout.js', array(), null, true );

			wp_register_script( 'moment', SC_DIR_URL . 'assets/js/vendor/moment' . $this->min . '.js', array(), $base_class->version, true );
			wp_register_script( 'pikaday', SC_DIR_URL . 'assets/js/vendor/pikaday' . $this->min . '.js', array( 'moment' ), $base_class->version, true );
			wp_register_script( 'pikaday-jquery', SC_DIR_URL . 'assets/js/vendor/pikaday-jquery' . $this->min . '.js', array( 'jquery', 'pikaday' ), $base_class->version, true );
			
			wp_register_script( $base_class->plugin_slug . '-public', SC_DIR_URL . 'assets/js/public-main' . $this->min . '.js', array( 'jquery', 'stripe-checkout', 'parsley', 'moment', 'pikaday', 'pikaday-jquery' ), $base_class->version, true );

			wp_localize_script( $base_class->plugin_slug . '-public', 'sc_coup', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
		}
		
		/*
		 * Enqueue admin facing scripts
		 */
		public function enqueue_admin_scripts() {
			
			global $base_class;
			
			if ( Stripe_Checkout_Admin::get_instance()->viewing_this_plugin() ) {

				wp_enqueue_script( $base_class->plugin_slug . '-admin', SC_DIR_URL . 'assets/js/admin-main' . $this->min . '.js', array( 'jquery' ), $base_class->version, true );

				wp_enqueue_script( $base_class->plugin_slug . '-admin-pro', SC_DIR_URL . 'assets/js/admin-pro' . $this->min . '.js', array( 'jquery' ), $base_class->version, true );

				wp_localize_script( $base_class->plugin_slug . '-admin-pro', 'sc_strings', array(
						'activate'     => __( 'Activate', 'sc' ),
						'deactivate'   => __( 'Deactivate', 'sc' ),
						'valid_msg'    => __( 'License is valid and active.', 'sc' ),
						'inactive_msg' => __( 'License is inactive.', 'sc' ),
						'invalid_msg'  => __( 'Sorry, but this license key is invalid.', 'sc' ),
						'notfound_msg' => __( 'License service could not be found. Please contact support for assistance.', 'sc' ),
						'error_msg'    => __( 'An error has occurred, please try again.', 'sc' )
					)
				);
			}
		}
		
		/**
		 * Function to localize the script variables being sent from the shortcodes
		 * 
		 * @since 2.0.0
		 */
		public function localize_shortcode_script() {
			global $script_vars, $base_class;

			wp_localize_script( $base_class->plugin_slug . '-public', 'sc_script', $script_vars );

			// clear it out after we use it
			$script_vars = array();
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
