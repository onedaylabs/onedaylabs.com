<?php

/**
 * Sidebar portion of the administration dashboard view.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- Use some built-in WP admin theme styles. -->

<div class="sidebar-container metabox-holder">
	<div class="postbox">
		<h3 class="wp-ui-primary"><span><?php _e( 'Quick Links', 'sc' ); ?></span></h3>
		<div class="inside">
			<ul>
				<li>
					<div class="dashicons dashicons-arrow-right-alt2"></div>
					<a href="<?php echo Stripe_Checkout_Admin::ga_campaign_url( SC_WEBSITE_BASE_URL . 'docs/', 'stripe-checkout-pro', 'sidebar-link', 'docs' ); ?>" target="_blank">
						<?php _e( 'Support & Documentation', 'sc' ); ?></a>
				</li>

				<li>
					<div class="dashicons dashicons-arrow-right-alt2"></div>
					<a href="https://dashboard.stripe.com/" target="_blank">
						<?php _e( 'Stripe Dashboard', 'sc' ); ?></a>
				</li>
				<li>
					<div class="dashicons dashicons-arrow-right-alt2"></div>
					<a href="<?php echo Stripe_Checkout_Admin::ga_campaign_url( SC_WEBSITE_BASE_URL . 'feature-requests/', 'stripe-checkout-pro', 'sidebar-link', 'feature-requests' ); ?>" target="_blank">
						<?php _e( 'Feature Requests', 'sc' ); ?></a>
				</li>
			</ul>
		</div>
	</div>
</div>

<div class="sidebar-container metabox-holder">
	<div class="postbox-nobg">
		<div class="inside centered">
			<a href="https://stripe.com/" target="_blank">
				<img src="<?php echo SC_DIR_URL; ?>assets/img/powered-by-stripe.png" />
			</a>
		</div>
	</div>
</div>
