<?php 

	/**
	 * Represents the view for the License Keys admin tab.
	 */

	global $sc_options; 
?>

<!-- License Keys tab HTML -->
<div class="sc-admin-hidden" id="license-keys-settings-tab">
	<div>
		<?php $sc_options->description( __( 'These license keys are used for access to automatic upgrades and support.', 'sc' ) ); ?>
	</div>

	<div>
		<label for="<?php echo esc_attr( $sc_options->get_setting_id( 'pro_license_key' ) ); ?>">
			<?php printf( __( '%1$s License Key', 'sc' ), Stripe_Checkout_Pro::get_plugin_title() ); ?>
		</label>
		
		<?php $sc_options->license_field( 'sc_license_key', Stripe_Checkout_Pro::get_plugin_title() ); ?>
	</div>
	
	<?php do_action( 'sc_settings_tab_license' ); ?>
</div>
