<?php

/**
 * Settings extension for additional controls not available in the base class
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Stripe_Checkout_Settings_Extended' ) ) {
	class Stripe_Checkout_Settings_Extended extends MM_Settings_Output {
		
		/**
		 * Class constructor
		 * 
		 * @param string $option This is the name of the option that will be used in the database
		 */
		public function __construct( $option ) {
			parent::__construct( $option );
		}
		
		/**
		 * The function used to create the toggle control
		 * 
		 * @param string $id ID of the control
		 * @param array $options The available options for the switch (needs exactly 2 options)
		 * @param string $classes The CSS classes for the control
		 */
		public function toggle_control( $id, $options, $classes = null ) {
			
			// If there are not exactly 2 options then we return an error
			if ( 2 != count( $options ) ) {
				echo __( 'You must include 2 options for a toggle switch!', 'sc' ) . '<br>';
				return;
			}
			
			// Default classes
			if( null === $classes ) {
				$classes = 'switch-light switch-candy switch-candy-blue';
			}

			$value = $this->get_setting_value( $id );

			$checked = ( ! empty( $value ) ? checked( 1, $value, false ) : '' );

			$html  = '<div class="' . esc_attr( $this->option ) . '-toggle-switch-wrap">';
			$html .= '<label class="' . esc_attr( $classes ) . '">';
			$html .= '<input type="checkbox" id="' . esc_attr( $this->get_setting_id( $id ) ) . '" name="' . esc_attr( $this->get_setting_id( $id ) ) . '" value="1" ' . $checked . '/>';
			$html .= '<span>';

			foreach ( $options as $o ) {
				$html .= '<span>' . esc_html( $o ) . '</span>';
			}

			$html .= '</span>';
			$html .= '<a></a>';
			$html .= '</label></div>';

			echo $html;
		}
		
		/*
		 * Function to display license fields for pro plugins
		 */
		public function license_field( $id, $product ) {
			
			$value = $this->get_setting_value( $id );

			$item = '';
			$html  = '<div class="sc-license-wrap">' . "\n";
			
			$html .= '<input type="text" class="sc-license-input regular-text" id="' . esc_attr( $this->get_setting_id( $id ) ) . '" ' .
					 'name="' . esc_attr( $this->get_setting_id( $id ) ) . '" value="' . trim( esc_attr( $value ) ) . '"/>' . "\n";

			$license = get_option( 'sc_license' );
			$license_action = '';
			$button_text = '';
			
			// Add button on side of input
			if ( ! empty( $license ) && $license == 'valid' && ! ( null === $value ) ) {
				$license_action = 'deactivate_license';
				$button_text = __( 'Deactivate', 'sc' );
			} else {
				$license_action = 'activate_license';
				$button_text = __( 'Activate', 'sc' );
			}
			
			$html .= '<button class="sc-license-action button" data-sc-action="' . $license_action . '" ' .
					 'data-sc-item="' . esc_attr( $product ) . '">' . $button_text . '</button>';
			
			if ( ! empty( $license ) && ! ( null === $value ) ) {
				$valid = Stripe_Checkout_Pro_Licenses::check_license( $value, $product );
				$license_class = '';
				$valid_message = '';

				if ( $valid == 'valid' ) {
					$license_class = 'sc-valid';
					$valid_message = __( 'License is valid and active.', 'sc' );
				} else if( $valid == 'notfound' ) {
					$license_class = 'sc-invalid';
					$valid_message = __( 'License service could not be found. Please contact support for additional help.', 'sc' );
				} else {
					$license_class = 'sc-inactive';
					$valid_message = __( 'License is inactive.', 'sc' );
				}
			} else {
				$license_class = 'sc-inactive';
				$valid_message = __( 'License is inactive.', 'sc' );
			}

			$html .= '<span class="sc-spinner spinner"></span>';
			$html .= '<span class="sc-license-message ' . $license_class . '">' . $valid_message . '</span>';

			$html .= '</div>';

			echo $html;
		}
	}
}