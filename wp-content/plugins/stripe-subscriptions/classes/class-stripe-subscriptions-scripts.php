<?php

/**
 * Subscriptions Scripts class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Stripe_Subscription_Scripts' ) ) {
	class Stripe_Subscriptions_Scripts {
		
		protected static $instance = null;

		private $min = null;
		
		private function __construct() {

			$this->min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

			// Include public scripts
			add_action( 'init', array( $this, 'register_public_scripts' ) );
			
			add_action( 'the_posts', array( $this, 'load_scripts' ) );
		}
		
		public function load_scripts( $posts ) {

			if ( empty($posts) ) {
				return $posts;
			}

			foreach ( $posts as $post ){
				if ( strpos( $post->post_content, '[stripe_subscription' ) !== false ){
					// Load JS
					wp_enqueue_script( Stripe_Subscriptions::get_plugin_slug() . '-public' );

					break;
				}
			}

			return $posts;
		}
		
		/**
		 * Register public scripts to use later
		 *
		 * @since     1.0.0
		 */
		public function register_public_scripts() {
		   //if( sc_sub_has_shortcode() ) {
			   wp_register_script( Stripe_Subscriptions::get_plugin_slug() . '-public',  SC_SUB_DIR_URL . 'assets/js/public-main' . $this->min . '.js', array( 'jquery' ), Stripe_Subscriptions::get_plugin_version(), true );
			   wp_localize_script( Stripe_Subscriptions::get_plugin_slug() . '-public', 'sc_sub', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ), Stripe_Subscriptions::get_plugin_version() );
		   //}
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
