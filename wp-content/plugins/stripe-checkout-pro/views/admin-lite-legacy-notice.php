<?php

/**
 * Show admin license key notice if Lite or the old legacy plugins are detected.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<style>
	#sc-lite-legacy-notice .button-primary,
	#sc-lite-legacy-notice .button-secondary {
		margin: 2px 0;
	}
</style>

<div id="sc-lite-legacy-notice" class="error">
	<p>
		<?php
			// Check for empty key first.
			echo '<strong>' . sprintf( __( 'Notice: You have %1$s or a legacy add-on installed. Please deactivate the plugin(s) to avoid conflicts with %2$s.', 'sc' ), 
										Stripe_Checkout::get_plugin_title(), Stripe_Checkout_Pro::get_plugin_title() ) . '</strong>' . "\n";
		?>
	</p>
</div>
