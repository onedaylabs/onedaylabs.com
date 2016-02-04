/**
 * Public facing JavaScript
 */

/* global jQuery, sc_script */

(function($) {
    'use strict';

    $(function() {

	    var $body = $( 'body' );
	
		$body.find( '.sc-checkout-form' ).each( function() {
		
            var scForm = $(this);
			
            // Get the "sc-id" ID of the current form as there may be multiple forms on the page.
            var formId = scForm.data('sc-id') || '';

			var interval = '', interval_count = 0, setupFee = 0;

			updateIntervalMessage();

			// Show correct interval for stripe_total
			// TODO update this for i18n somehow
			if ( scForm.find('.sc_sub_interval').length > 0 ) {

				var interval_count = scForm.find('.sc_sub_interval_count').val();
				interval = scForm.find('.sc_sub_interval').val();

				if ( interval_count > 1 ) {
					interval = ' every ' + interval_count + ' ' + interval + 's';
				} else {
					interval = '/' + interval;
				}
			}

			// Set the initial price based on the default subscription set
			if( scForm.has( '.sc_sub_amount' ).length > 0 ) {

				// Se3t initial value of setupFee to the form's default setup fee value.
				setupFee = sc_script[formId].setupFee;

				// All this forms radio buttons to look through
				var radio = scForm.find('input[type="radio"]');

				// If the default radio button is set we need to update the setup fee here if one exists
				// We loop through all the form radio buttons looking for the setup fee greater than 0 and if it is then we see if the option is checked
				// If the option is checked then we set the setupFee to this options setup fee data attribute
				if( radio.length > 0 ) {
					radio.each(function() {
						if( $(this).attr('data-sub-setup-fee') > 0 ) {
							if($(this).is(':checked') ) {
								setupFee = $(this).data('sub-setup-fee');

								// Update the setup fee
								scForm.find('.sc_sub_setup_fee').val( setupFee );
							}
						}
					});
				}
				
				sc_script[formId].amount = scForm.find('.sc_sub_amount').val();
				sc_script[formId].originalAmount = scForm.find('.sc_sub_amount').val();
				
				if( scForm.has( '.sc_sub_currency').length > 0 ) {
					// single plan
					sc_script[formId].currency = scForm.find( '.sc_sub_currency').val();
				} else {
					sc_script[formId].currency = scForm.find('.sc_sub_wrapper .sc-radio-group input[type="radio"]:checked').data('sub-currency');
				}
				
			}

			// Set the button amount if there is a setup fee involved
			
			// Update price and ID anytime a radio button changes
			scForm.find('.sc_sub_wrapper input[type="radio"]').on( 'change', function() {
				// We will update the hidden fields in case we need them somewhere else
				scForm.find('.sc_sub_id').val($(this).data('sub-id'));
				scForm.find('.sc_sub_amount').val($(this).data('sub-amount'));
				scForm.find('.sc_sub_interval').val($(this).data('sub-interval'));
				scForm.find('.sc_sub_interval_count').val($(this).data('sub-interval-count'));

				// Update plan
				updateIntervalMessage();

				// Change the setup fee if needed
				var setupFee = 0;

				// If the setup fee data attribute is bigger than 0 then we need to add it from this option
				// Otherwise we just add the default setup fee based on the form ID.
				if( parseInt( $(this).data('sub-setup-fee') ) > 0 ) {
					setupFee = $(this).data('sub-setup-fee');
				} else {
					setupFee = sc_script[formId].setupFee;
				}
				
				// Update the amount
				sc_script[formId].amount = scForm.find('.sc_sub_amount').val();
				sc_script[formId].originalAmount = scForm.find('.sc_sub_amount').val();

				// Update the setup fee
				scForm.find('.sc_sub_setup_fee').val( setupFee );
				
				// Update the currency
				sc_script[formId].currency = $(this).data('sub-currency');
				
				// Check for non-blank coupon code value.
		        var scCouponCode = scForm.find('.sc-coup-coupon-code').val();
				
				// If a coupon value exists then we need to update the price by calling the ajax function
		        if (scCouponCode) {
					// disable the buy button until the ajax completes
					scForm.find('.sc-payment-btn').prop('disabled',true);
					
					var oldMessage = scForm.find( '.sc-coup-success-message').html();
					
					// Change coupon message while calculating new price
					scForm.find('.sc-coup-success-message').html( 'Calculating...');
					scForm.find('.sc-coup-remove-coupon').hide();

					// AJAX POST params
			        var params = {
				        action: 'sc_coup_get_coupon',
				        coupon: scCouponCode,
				        // Amount already preset in basic [stripe] shortcode (or default of 50).
				        amount: sc_script[formId].amount
			        };
					
					// Send AJAX POST -- sc_sub.ajaxurl from localized JS.
					$.post(sc_sub.ajaxurl, params, function (response) { 
						
						if(response.success) {
							// Update the amount
							sc_script[formId].amount = response.message;
							scForm.find('.sc-total-amount').html(currencyFormattedAmount(sc_script[formId].amount, sc_script[formId].currency));
						}
						
						// Re-enable the buy button
						scForm.find('.sc-payment-btn').prop('disabled',false);
						
						// Put original coupon message back
						scForm.find('.sc-coup-success-message').html(oldMessage);
						scForm.find('.sc-coup-remove-coupon').show();
					}, 'json' );
				}

				if(scForm.find('.sc-recurring-total-amount').length > 0 ) {
					scForm.find('.sc-recurring-total-amount').html(currencyFormattedAmount(sc_script[formId].amount, sc_script[formId].currency) + interval);
				}

				sc_script[formId].amount = ( parseInt( scForm.find('.sc_sub_amount').val() ) + parseInt( setupFee ) ).toString();

				scForm.find('.sc-total-amount').html(currencyFormattedAmount(sc_script[formId].amount, sc_script[formId].currency));

				// We do this after setting the total amount because the total amount may be different than this update amount.
				//scForm.find('.sc-setup-fee-total').html(currencyFormattedAmount(sc_script[formId].amount, sc_script[formId].currency));
			});

			// We do this after setting the total amount because the total amount may be different than this update amount.
			if( scForm.find('.sc-recurring-total-amount').length > 0 ) {
				scForm.find('.sc-recurring-total-amount').html(currencyFormattedAmount(sc_script[formId].amount, sc_script[formId].currency) + interval);
			}

			// Only add the setup fee if this form has a subscription
			if ( scForm.find('.sc_sub_amount').length > 0 ) {
				sc_script[formId].amount = ( parseInt(scForm.find('.sc_sub_amount').val()) + parseInt(setupFee) ).toString();
			}

			scForm.find('.sc-total-amount').html(currencyFormattedAmount(sc_script[formId].amount, sc_script[formId].currency));

			// Function to update the message output for subscription plans.
			function updateIntervalMessage() {
				if ( scForm.find('.sc_sub_interval').length > 0 ) {

					interval_count = scForm.find('.sc_sub_interval_count').val();

					interval = scForm.find('.sc_sub_interval').val();

					if ( interval_count > 1 ) {
						interval = ' every ' + interval_count + ' ' + interval + 's';
					} else {
						interval = '/' + interval;
					}
				}
			}
		});


		
		// Zero-decimal currency check.
        // Just like sc_is_zero_decimal_currency() in PHP.
        // References sc_script['zero_decimal_currencies'] localized JS value.
        function isZeroDecimalCurrency(currency) {
            return ( $.inArray(currency.toUpperCase(), sc_script['zero_decimal_currencies']) > 1 );
        }

        // Use with coupon "amount" type and total amount.
        function currencyFormattedAmount(amount, currency) {
            // Just in case.
            currency = currency.toUpperCase();

            // Don't use decimals if zero-based currency.
            // Uses JS function in base plugin.
            if ( isZeroDecimalCurrency(currency) ) {
                amount = Math.round(amount);
            } else {
                amount = (amount / 100).toFixed(2);
            }

            var formattedAmount = '';

            // USD only: Show dollar sign on left of amount.
            if (currency === 'USD') {
                formattedAmount = '$' + amount;
            }
            else {
                // Non-USD: Show currency on right of amount.
                formattedAmount = amount + ' ' + currency;
            }

            return formattedAmount;
        }
    });

}(jQuery));
