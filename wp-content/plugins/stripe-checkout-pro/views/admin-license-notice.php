<?php

/**
 * Show admin license key notice if blank or invalid license saved.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<style>
	#sc-license-notice .button-primary,
	#sc-license-notice .button-secondary {
		margin: 2px 0;
	}
</style>

<div id="sc-license-notice" class="error">
	<p>
		<?php

		// Check for empty key first.
		echo '<strong>' . __( 'Notice: You must enter your plugin license key(s) to receive automatic updates and support.', 'sc' ) . '</strong><br />' . "\n";

		// Show "below" message on Support tab.
		if ( !( isset( $_GET['tab'] ) && ( 'licenses' == $_GET['tab'] ) ) ) {
			// Render link to Support tab on other plugin tabs.
			echo '<a href="' . esc_url( add_query_arg( array ( 'page' => SC_PLUGIN_SLUG, 'tab' => 'licenses' ), admin_url( 'admin.php' ) ) ) . '">' .
					__( 'Go to the Licenses tab to enter your license key(s)', 'sc' ) . '</a>.<br />' . "\n";
		}

		// In all cases show message and link to purchase a key.
		?>

		<?php _e( 'If you would like to purchase a license ', 'sc' ); ?>
		<a href="<?php echo sc_ga_campaign_url( SC_WEBSITE_BASE_URL, 'stripe_checkout_pro', 'license_notice', 'license_notice' ); ?>"
		   target="_blank"><?php _e( 'please visit our store', 'sc' ); ?></a>.
	</p>
</div>
