<?php 

	/**
	 * Represents the view for the additional pro only Default Settings tab.
	 */

	global $sc_options; 
?>

<div>
	<label for="<?php echo esc_attr( $sc_options->get_setting_id( 'shipping' ) ); ?>"><?php _e( 'Enable Shipping Address', 'sc' ); ?></label>
	<?php $sc_options->checkbox( 'shipping' ); ?>
	<span><?php _e( 'Require the user to enter their shipping address during checkout.', 'sc' ); ?></span>
	<?php $sc_options->description( 'When a shipping address is required, the customer is also required to enter a billing address.' ); ?>
</div>

<div>
	<label><?php _e( 'Payment Button Style', 'sc' ); ?></label>
	<?php $sc_options->radio_button( 'none', 'None', 'none', 'payment_button_style' ); ?>
	<?php $sc_options->radio_button( 'stripe', 'Stripe', 'stripe', 'payment_button_style' ); ?>
	<?php $sc_options->description( __( 'Enable Stripe styles for the main payment button. Base button CSS class: <code>sc-payment-btn</code>.', 'sc' ) ); ?>
</div>

<div>
	<label for="<?php echo esc_attr( $sc_options->get_setting_id( 'coup_label' ) ); ?>"><?php _e( 'Coupon Input Label', 'sc' ); ?></label>
	<?php $sc_options->textbox( 'coup_label', 'regular-text' ); ?>
	<?php $sc_options->description( __( 'Label to show before the coupon code input.', 'sc' ) ); ?>
</div>

<div>
	<label><?php _e( 'Apply Button Style', 'sc' ); ?></label>
	<?php $sc_options->radio_button( 'none', 'None', 'none', 'sc_coup_apply_button_style' ); ?>
	<?php $sc_options->radio_button( 'stripe', 'Stripe', 'stripe', 'sc_coup_apply_button_style' ); ?>
	<?php $sc_options->description( __( 'Optionally enable Stripe styles for the coupon "Apply" button. Base button CSS class: <code>sc-coup-apply-btn</code>.', 'sc' ) ); ?>
</div>

<div>
	<label for="<?php echo esc_attr( $sc_options->get_setting_id( 'stripe_total_label' ) ); ?>"><?php _e( 'Stripe Total Label', 'sc' ); ?></label>
	<?php $sc_options->textbox( 'stripe_total_label', 'regular-text' ); ?>
	<?php $sc_options->description( __( 'The default label for the stripe_total shortcode.', 'sc' ) ); ?>
</div>

<div>
	<label for="<?php echo esc_attr( $sc_options->get_setting_id( 'sc_uea_label' ) ); ?>"><?php _e( 'Amount Input Label', 'sc' ); ?></label>
	<?php $sc_options->textbox( 'sc_uea_label', 'regular-text' ); ?>
	<?php $sc_options->description( __( 'Label to show before the amount input.', 'sc' ) ); ?>
</div>