<?php

/**
 * Show notice if Pro is not the latest version.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<style>
	#sc-sub-pro-upgrade-notice .button-primary,
	#sc-sub-pro-upgrade-notice .button-secondary {
		margin-left: 15px;
	}
</style>

<div id="sc-sub-pro-upgrade-notice" class="error">
	<p>
		<?php 
			printf( __( 'Your version of %1$s is missing, disabled or out-of-date. Please activate and/or update it to avoid any incompatibilities with the Subscriptions add-on.', 'sc_sub' ),
					Stripe_Subscriptions::get_required_plugin_title() ); 
		?>
	</p>
</div>
