<?php

/**
 * Show notice if SC Sub is not the latest version.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="sc-pro-sub-upgrade-notice" class="error">
	<p>
		<?php sprintf( __( 'There was an issue reactivating Stripe Subscriptions, please visit the <a href="%s">plugins page</a> to activate it manually.', 'sc' ), get_admin_url( '', 'plugins.php' ) ); ?>
	</p>
</div>
