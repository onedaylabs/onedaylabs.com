<?php

/**
 * Show notice if Pro is not activated.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<style>
	#sc-sub-pro-deactivated-notice .button-primary,
	#sc-sub-pro-deactivated-notice .button-secondary {
		margin-left: 15px;
	}
</style>

<div id="sc-sub-pro-deactivated-notice" class="error">
	<p>
		<?php printf( __(  '%1$s requires %2$s to run properly. Please install %2$s to fully use this plugin.', 'sc_sub' ), 
							Stripe_Subscriptions::get_plugin_title(), Stripe_Subscriptions::get_required_plugin_title() ); ?>
	</p>
</div>
