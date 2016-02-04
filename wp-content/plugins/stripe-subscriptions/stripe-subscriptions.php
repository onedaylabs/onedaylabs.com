<?php
/**
 * Plugin Name: WP Simple Pay Pro for Stripe - Subscriptions Add-on
 * Plugin URI: https://wpsimplepay.com/
 * Description: Subscriptions add-on for WP Simple Pay Pro for Stripe.
 * Author: Moonstone Media
 * Author URI: http://moonstonemediagroup.com
 * Version: 1.2.7
 * Text Domain: sc_sub
 * Domain Path: /languages/
 *
 * Copyright 2014 Moonstone Media/Phil Derksen. All rights reserved.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define constants.
$sc_sub_constants = array(
	'SC_SUB_MAIN_FILE'  => __FILE__,
	'SC_SUB_DIR_PATH'   => plugin_dir_path( __FILE__ ),
	'SC_SUB_DIR_URL'    => plugin_dir_url( __FILE__ ) ,
);
foreach( $sc_sub_constants as $constant => $value ) {
	if ( ! defined( $constant ) ) {
		define( $constant, $value );
	}
}

require_once( SC_SUB_DIR_PATH . 'classes/class-stripe-subscriptions.php' );

$sc_version = get_option( 'sc_version' );

// Check if SC Version is the right version OR if SP Pro is missing and if so then we show an admin message to update the base plugin.
if ( version_compare( $sc_version, '2.3.0', '<' ) || ! class_exists( 'Stripe_Checkout_Pro' ) ) {

	require_once( SC_SUB_DIR_PATH . 'classes/class-stripe-subscriptions-admin.php' );
	add_action( 'admin_notices', array( 'Stripe_Subscriptions_Admin', 'update_stripe_pro_notice' ) );

} else {

	update_option( 'sc_sub_initialized', 1 );
	Stripe_Subscriptions::get_instance();

}
