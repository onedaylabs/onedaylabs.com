<?php

/**
 * Subscriptions Functions class.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Stripe_Subscriptions_Functions' ) ) {
	
	class Stripe_Subscriptions_Functions {
		
		protected static $instance = null;
		
		private function __construct() {

			// Since a trial does not actually create a charge we need to make a distinction between which kind of subscription is being charged and
			// load a success message accordingly.
			if ( isset( $_GET['charge'] ) ) {
				add_filter( 'sc_payment_details', array( $this, 'add_payment_details' ), 10, 2 );
			} elseif ( isset( $_GET['trial'] ) ) {
				add_filter( 'the_content', array( $this, 'trial_payment_details' ) );
			}


			$sub_toggle = null;
			
			if ( isset( $_POST['sc_sub_id'] ) && isset( $_POST['wp-simple-pay'] ) ) {

				if ( isset( $_POST['sc_form_field'] ) ) {
					foreach ($_POST['sc_form_field'] as $k => $v) {
						if ('sc_sub_toggle' == substr($k, 0, 13)) {

							$v = strtolower($v);

							if ($v === 'yes') {
								$sub_toggle = true;
							} elseif ($v === 'no') {
								$sub_toggle = false;
							}
						}
					}
				}

				// Checked
				if ( $sub_toggle === true ) {
					add_action( 'sc_do_charge', array( $this, 'do_charge' ) );
				} elseif ( $sub_toggle === false ) {
					// Exists but is not checked
					// Do nothing
				} else {
					// Standard procedure
					add_action( 'sc_do_charge', array( $this, 'do_charge' ) );
				}
			}
			
			add_filter( 'sc_before_payment_button', array( $this, 'validate_subscription' ) );
		}

		/**
		 * Add a success message for subscriptions with a trial.
		 */
		public function trial_payment_details( $content ) {

			if( isset( $_GET['trial'] ) ) {

				$cust_id   = $_GET['cust_id'];
				$sub_id    = $_GET['sub_id'];
				$test_mode = ( isset( $_GET['test_mode'] ) ? 'true' : 'false' );
				$is_above  = ( isset( $_GET['details_placement'] ) && $_GET['details_placement'] == 'below' ? false : true );

				Stripe_Checkout_Functions::set_key( $test_mode );

				$customer     = \Stripe\Customer::retrieve( $cust_id );
				$subscription = $customer->subscriptions->retrieve( $sub_id );


				$interval_count = $subscription->plan->interval_count;
				$interval       = $subscription->plan->interval;
				$amount         = $subscription->plan->amount;
				$currency       = $subscription->plan->currency;
				$product        = $subscription->metadata['product'];


				$html = '<div class="sc-payment-details-wrap">' . "\n";

				$html .= '<p>' . __( 'Congratulations, you have started your free trial!', 'sc_sub' ) . '</p>' . "\n";
				$html .= '<p>' . __( 'Your card will not be charged until your trial is over.', 'sc_sub' ) . '</p>' . "\n";

				$html .= '<p>' . sprintf( __( 'Your trial will end on: %1$s', 'sc_sub' ), date_i18n( get_option( 'date_format' ),  $subscription->trial_end ) ) .  '</p>' . "\n";
				$html .= '<p>' . "\n";

				if ( ! empty( $product ) ) {
					$html .= __( "Here's what you purchased:", 'sc_sub' ) . '<br/>' . "\n";
					$html .= stripslashes( $product ) . '<br/>' . "\n";
				}

				if ( isset( $_GET['store_name'] ) && ! empty( $_GET['store_name'] ) ) {
					$html .= __( 'From: ', 'sc_sub' ) . stripslashes( stripslashes( urldecode( $_GET['store_name'] ) ) ) . '<br/>' . "\n";
				}

				$html .= '<br/>' . "\n";

				$html .= '</p>' . "\n";

				$html .= '<p>' . __( 'You will be charged ', 'sc_sub' );

				$html .= '<strong>' . Stripe_Checkout_Misc::to_formatted_amount( $amount, $currency ) . ' ' . strtoupper( $currency );

				// For interval count of 1, use $1.00/month format.
				// For a count > 1, use $1.00 every 3 months format.
				if ( $interval_count == 1 ) {
					$html .= '/' . $interval;
				} else {
					$html .= ' ' . __( 'every', 'sc_sub' ) . ' ' . $interval_count . ' ' . $interval . 's';
				}

				$html .= '</strong>';

				$html .= __( ' when your trial is over.', 'sc_sub' );

				$html .= '</p>' . "\n";

				$html .= '<p>' . sprintf( __( 'Your customer ID is: %1$s', 'sc_sub' ), $cust_id ) . '</p>';

				$html .= '</div>';

				if ( ! $is_above ) {
					return $content . apply_filters( 'sc_trial_payment_details', $html, $subscription );
				} else {
					return apply_filters( 'sc_trial_payment_details', $html, $subscription ) . $content;
				}
			}

			return $content;
		}
		
		/**
		 * Check if the [stripe_subscription] shortcode exists on this page
		 * 
		 * @since 1.0.0
		 */
		public function has_shortcode() {
			global $post;

			// Currently ( 5/8/2014 ) the has_shortcode() function will not find a 
			// nested shortcode. This seems to do the trick currently, will switch if 
			// has_shortcode() gets updated. -NY
			if ( strpos( $post->post_content, '[stripe_subscription' ) !== false ) {
				return true;
			}

			return false;
		}

		/**
		 * Helper function to grab the subscription by ID and return the subscription object
		 * 
		 * @since 1.0.0
		 */
		public static function get_subscription_by_id( $id, $test_mode = 'false' ) {

			global $sc_options;

			$test_mode = ( isset( $_GET['test_mode'] ) ? 'true' : $test_mode );

			Stripe_Checkout_Functions::set_key( $test_mode );

			try {
				$return = \Stripe\Plan::retrieve( trim( $id ) );

			} catch( \Stripe\Error\Card $e ) {

				$body = $e->getJsonBody();

				$return = self::print_errors( $body['error'] );

			} catch ( \Stripe\Error\Authentication $e ) {
				// Authentication with Stripe's API failed
				// (maybe you changed API keys recently)

				$body = $e->getJsonBody();

				$return = self::print_errors( $body['error'] );

			} catch ( \Stripe\Error\ApiConnection $e ) {
				// Network communication with Stripe failed

				$body = $e->getJsonBody();

				$return = self::print_errors( $body['error'] );

			} catch ( \Stripe\Error\Base $e ) {

				$body = $e->getJsonBody();

				$return = self::print_errors( $body['error'] );

			} catch ( Exception $e ) {
				// Something else happened, completely unrelated to Stripe
				$body = $e->getJsonBody();

				$return = self::print_errors( $body['error'] );
			}

			return $return;
		}

		public function add_payment_details( $html, $details ) {

			//echo '<pre>' . print_r( $details, true ) . '</pre>';

			if( ! isset( $details->invoice ) ) {
				return $html;
			}

			$invoice = \Stripe\Invoice::retrieve( $details->invoice );
			
			$interval = $invoice->lines->data[0]->plan->interval;
			$interval_count = $invoice->lines->data[0]->plan->interval_count;

			$amount = $invoice->lines->data[0]->plan->amount;

			$starting_balance = $invoice->starting_balance;

			$html = '<div class="sc-payment-details-wrap">' . "\n";

			$html .= '<p>' . __( 'Congratulations. Your payment went through!', 'sc_sub' ) . '</p>' . "\n";
			$html .= '<p>' . "\n";

			if ( ! empty( $details->description ) ) {
				$html .= __( "Here's what you purchased:", 'sc_sub' ) . '<br/>' . "\n";
				$html .= stripslashes( $details->description ) . '<br/>' . "\n";
			}

			if ( isset( $_GET['store_name'] ) && ! empty( $_GET['store_name'] ) ) {
				$html .= __( 'From: ', 'sc_sub' ) . stripslashes( stripslashes( urldecode( $_GET['store_name'] ) ) ) . '<br/>' . "\n";
			}

			$html .= '<br/>' . "\n";
			$html .= '<strong>' . __( 'Total Paid: ', 'sc_sub' ) . Stripe_Checkout_Misc::to_formatted_amount( $details->amount, $details->currency ) . ' ' .
					 strtoupper( $details->currency ) . '</strong>' . "\n";

			$html .= '</p>' . "\n";


			$html .= '<p>';

			if ( $starting_balance > 0 ) {
				$html .= sprintf( __( 'You have been charged a one time fee of: %1$s %2$s', 'sc' ),
						Stripe_Checkout_Misc::to_formatted_amount( $starting_balance, $details->currency ), strtoupper( $details->currency ) ) . '<br>';
			}


			$html .= __( 'You will be charged ', 'sc_sub' );

			$html .= Stripe_Checkout_Misc::to_formatted_amount( $amount, $details->currency ) . ' ' . strtoupper( $details->currency );

			// For interval count of 1, use $1.00/month format.
			// For a count > 1, use $1.00 every 3 months format.
			if ( $interval_count == 1 ) {
				$html .= '/' . $interval;
			} else {
				$html .= ' ' . __( 'every', 'sc_sub' ) . ' ' . $interval_count . ' ' . $interval . 's';
			}

			$html .= '</p>' . "\n";

			$html .= '<p>' . sprintf( __( 'Your transaction ID is: %s', 'sc_sub' ), $details->id ) . '</p>';

			$html .= '</div>';

			return $html;

		}

		
		public static function print_errors( $err = array() ) {

			$message = '';

			if ( current_user_can( 'manage_options' ) ) {
				foreach ( $err as $k => $v ) {
					$message = '<h6>' . $k . ': ' . $v . '</h6>';
				}
			} else {
				$message = '<h6>' . __( 'An error has occurred. If the problem persists, please contact a site administrator.', 'sc_sub' ) . '</h6>';
			}

			return apply_filters( 'sc_error_message', $message );
		}


		public function do_charge() {

			if ( wp_verify_nonce( $_POST['wp-simple-pay-pro-nonce'], 'charge_card' ) ) {
				global $sc_options;

				$query_args = array();

				// Set redirect
				$redirect = $_POST['sc-redirect'];
				$fail_redirect = $_POST['sc-redirect-fail'];
				$failed = null;

				$message = '';

				// Get the credit card details submitted by the form
				$token = $_POST['stripeToken'];
				$amount = $_POST['sc-amount'];
				$description = $_POST['sc-description'];
				$store_name = $_POST['sc-name'];
				$currency = $_POST['sc-currency'];
				$details_placement = $_POST['sc-details-placement'];

				$charge = null;

				$sub = (isset($_POST['sc_sub_id']));
				$interval = (isset($_POST['sc_sub_interval']) ? $_POST['sc_sub_interval'] : 'month');
				$interval_count = (isset($_POST['sc_sub_interval_count']) ? $_POST['sc_sub_interval_count'] : 1);
				$statement_description = (isset($_POST['sc_sub_statement_description']) ? $_POST['sc_sub_statement_description'] : '');

				$setup_fee = ( isset( $_POST['sc_sub_setup_fee'] ) ? $_POST['sc_sub_setup_fee'] : 0 );

				$coupon = (isset($_POST['sc_coup_coupon_code']) ? $_POST['sc_coup_coupon_code'] : '');

				$test_mode = (isset($_POST['sc_test_mode']) ? $_POST['sc_test_mode'] : 'false');

				if ($sub) {
					$sub = (!empty($_POST['sc_sub_id']) ? $_POST['sc_sub_id'] : 'custom');
				}

				Stripe_Checkout_Functions::set_key($test_mode);

				$meta = array();

				if ( ! empty( $setup_fee ) ) {
					$meta['Setup Fee'] = Stripe_Checkout_Misc::to_formatted_amount( $setup_fee, $currency );
				}

				$meta = apply_filters('sc_meta_values', $meta);

				try {

					if ($sub == 'custom') {

						$timestamp = time();

						$plan_id = $_POST['stripeEmail'] . '_' . $amount . '_' . $timestamp;

						$name = __('Subscription:', 'sc_sub') . ' ' . Stripe_Checkout_Misc::to_formatted_amount($amount, $currency) . ' ' . strtoupper($currency) . '/' . $interval;

						// Create a plan
						$plan_args = array(
							'amount' => $amount,
							'interval' => $interval,
							'name' => $name,
							'currency' => $currency,
							'id' => $plan_id,
							'interval_count' => $interval_count
						);

						if (!empty($statement_description)) {
							$plan_args['statement_descriptor'] = $statement_description;
						}

						$new_plan = \Stripe\Plan::create($plan_args);

						// Create a customer and charge
						$new_customer = \Stripe\Customer::create(array(
							'email' => $_POST['stripeEmail'],
							'card' => $token,
							'plan' => $plan_id,
							'metadata' => $meta,
							'account_balance' => $setup_fee,
						));

					} else {

						// Create new customer
						$cust_args = array(
							'email' => $_POST['stripeEmail'],
							'card' => $token,
							'plan' => $sub,
							'metadata' => $meta,
							'account_balance' => $setup_fee,
						);

						if (!empty($coupon)) {
							$cust_args['coupon'] = $coupon;
						}

						$new_customer = \Stripe\Customer::create($cust_args);

						// Set currency based on sub
						$plan = \Stripe\Plan::retrieve($sub);

						//echo $subscription . '<Br>';
						$currency = strtoupper($plan->currency);

					}

					// We want to add the meta data and description to the actual charge so that users can still view the meta sent with a subscription + custom fields
					// the same way that they would normally view it without subscriptions installed.
					// We need the steps below to do this

					// First we get the latest invoice based on the customer ID
					$invoice = \Stripe\Invoice::all(array(
							'customer' => $new_customer->id,
							'limit' => 1)
					);

					// If this is a trial we need to skip this part since a charge is not made
					$trial = $invoice->data[0]->lines->data[0]->plan->trial_period_days;

					if (empty($trial) || ! empty( $setup_fee ) ) {
						// Now that we have the invoice object we can get the charge ID
						$inv_charge = $invoice->data[0]->charge;

						// Finally, with the charge ID we can update the specific charge and inject our meta data sent from Stripe Custom Fields
						$ch = \Stripe\Charge::retrieve($inv_charge);

						$charge = $ch;

						if (!empty($meta)) {
							$ch->metadata = $meta;
						}

						if (!empty($description)) {
							$ch->description = $description;
						}

						$ch->save();

						$query_args = array('charge' => $ch->id, 'store_name' => urlencode($store_name));

						$failed = false;
					} else {

						$sub_id = $invoice->data[0]->subscription;

						if ( ! empty( $description ) ) {
							$customer = \Stripe\Customer::retrieve($new_customer->id);
							$subscription = $customer->subscriptions->retrieve($sub_id);

							$subscription->metadata = array('product' => $description);
							$subscription->save();
						}

						$query_args = array('cust_id' => $new_customer->id, 'sub_id' => $sub_id, 'store_name' => urlencode($store_name));

						$failed = false;
					}

				} catch (Exception $e) {
					// Something else happened, completely unrelated to Stripe

					$redirect = $fail_redirect;

					$failed = true;

					$e = $e->getJsonBody();

					$query_args = array('sub' => true, 'error_code' => $e['error']['type'], 'charge_failed' => true);
				}

				unset($_POST['stripeToken']);

				do_action('sc_redirect_before');

				if ($test_mode == 'true') {
					$query_args['test_mode'] = 'true';
				}

				if ( 'below' == $details_placement ) {
					$query_args['details_placement'] = $details_placement;
				}

				if ( ! empty( $trial ) && empty( $setup_fee ) ) {
					$query_args['trial'] = 1;
				}

				wp_redirect( esc_url_raw( add_query_arg( apply_filters( 'sc_redirect_args', $query_args, $charge ), apply_filters( 'sc_redirect', $redirect, $failed ) ) ) );

				exit;
			}
		}

		public function validate_subscription( $html ) {

			$sub = Shortcode_Tracker::shortcode_exists_current( 'stripe_subscription' );
			$uea = Shortcode_Tracker::shortcode_exists_current( 'stripe_amount' );

			//$html = '';

			// Neither exist so we can just exit now
			if ( $sub === false && $uea === false ) {
				return $html;
			}

			$sub_id       = isset( $sub['attr']['id'] ) ? true : false;
			$sub_children = isset( $sub['children'] ) ? true : false;
			$use_amount   = ( isset( $sub['attr']['use_amount'] ) && $sub['attr']['use_amount'] == 'true' ) ? true : false;

			// Can't have both an ID and UEA
			if ( ( $sub_id || $sub_children ) && $uea ) {
				Shortcode_Tracker::update_error_count();

				if ( current_user_can( 'manage_options' ) ) {
					Shortcode_Tracker::add_error_message( '<h6>' . __( 'Subscriptions must specify a plan ID or include a user-entered amount field. You cannot include both or omit both.', 'sc_sub' ) . '</h6>' );
				}
			}

			if ( empty( $sub_id ) && ( $uea || $use_amount ) && $sub != false ) {

				$interval              = ( isset( $sub['attr']['interval'] ) ? $sub['attr']['interval'] : 'month' );
				$interval_count        = ( isset( $sub['attr']['interval_count'] ) ? $sub['attr']['interval_count'] : 1 );
				$statement_description = ( isset( $sub['attr']['statement_description'] ) ? $sub['attr']['statement_description'] : '' );

				$html .= '<input type="hidden" name="sc_sub_id" class="sc_sub_id" value="" />';
				$html .= '<input type="hidden" name="sc_sub_interval" class="sc_sub_interval" value="' . $interval . '" />';
				$html .= '<input type="hidden" name="sc_sub_interval_count" class="sc_sub_interval_count" value="' . $interval_count . '" />';
				$html .= '<input type="hidden" name="sc_sub_statement_description" class="sc_sub_statement_description" value="' . $statement_description . '" />';
			}

			if ( empty( $sub_id ) && ! $uea && empty( $sub_children ) && $use_amount === false ) {
				Shortcode_Tracker::update_error_count();

				if ( current_user_can( 'manage_options' ) ) {
					Shortcode_Tracker::add_error_message( '<h6>' . __( 'Subscriptions must specify a plan ID or include a user-entered amount field. You cannot include both or omit both.', 'sc_sub' ) . '</h6>' );
				}
			}

			return $html;
		}
		
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}
	}
}
