/**
 * Public JS
 */

/* global jQuery, sc_script */

(function($) {
    'use strict';

    $(function() {

	    var $body = $( 'body' );

        var scFormList = $body.find('.sc-checkout-form');
		
		// Run Pikaday Datepicker method on each date field in each Stripe checkout form.
        // Requires Moment JS, Pikaday and Pikaday jQuery plugin.
        var scDateFields = scFormList.find('.sc-cf-date');

		scDateFields.pikaday({
            format: 'M/D/YYYY'
        });

        // Make sure each checkbox change sets the appropriate hidden value (Yes/No) to record
        // to Stripe payment records.
        var scCheckboxFields = scFormList.find('.sc-cf-checkbox');

        scCheckboxFields.change(function() {

            var checkbox = $(this);

            var checkboxId = checkbox.prop('id');

            // Hidden ID field is simply "_hidden" appended to checkbox ID field.
            var hiddenField = $body.find('#' + checkboxId + '_hidden');

            // Change to "Yes" or "No" dending on checked or not.
            hiddenField.val( checkbox.is(':checked') ? 'Yes' : 'No' );
        });
		
		//Process the form(s)
        scFormList.each(function() {
            var scForm = $(this);

			// Make sure the payment button is actually located within the form and if not then output a message and disable the button
			if ( scForm.has('.sc-payment-btn').length != 1 ) {

				// Check if is_admin and output the message if so
				if ( sc_script[1].is_admin == true ) {
					// We just do 1 here because all the arrays have the same string set so we are guaranteed to grab one no matter the form ID.
					scForm.before( '<p>' + sc_script[1].invalid_html_string + '</p>');
				}

				scForm.find( '.sc-payment-btn').prop( 'disabled', true );
			}

	        // Get the "sc-id" ID of the current form as there may be multiple forms on the page.
	        var formId = scForm.data('sc-id') || '';

			var scAmountDropdown = scForm.find('.sc-cf-amount');
						
			if(scAmountDropdown.length > 0) {
				scAmountDropdown.on( 'change', function() {
							if(scAmountDropdown.is('input[type="radio"]')){
								var newAmount = scForm.find('.sc-cf-amount:checked').data('sc-price');
							} else {
								var newAmount = scForm.find('.sc-cf-amount option:selected').data('sc-price');
							}
							
							 // Trigger coupon remove link click.
							var oldCoupon = scForm.find('.sc-coup-coupon-code').val();
							scForm.find('.sc-coup-remove-coupon').click();
							
							sc_script[formId].originalAmount = newAmount;
							sc_script[formId].amount = newAmount;
							scForm.find('.sc-total-amount').text(currencyFormattedAmount(newAmount, sc_script[formId].currency));
							
							scForm.find('.sc-coup-coupon').val( oldCoupon );
							scForm.find('.sc-coup-apply-btn').click();
						});
			}
			
			// Store original amount in case coupon code removed later.
	        sc_script[formId].originalAmount = sc_script[formId].amount;

	        // Parsley JS prevents form submit by default. Stripe also suggests using a button click event
	        // (not submit) to open the overlay in their custom implementation.
	        // https://stripe.com/docs/checkout#integration-custom
	        // So we need to explicitly call .validate() instead of auto-binding forms with data-parsley-form.
	        // http://parsleyjs.org/doc/index.html#psly-usage-form

	        // Update 5/20/2015: Fire off form/Parsley JS validation with button click.
	        // Needed for some mobile browsers like Chrome iOS.
	        // https://stripe.com/docs/checkout#integration-more-runloop
	        // Using event subscription method won't work for them (i.e. .subscribe('parsley:form:validate'... ).

	        // Set form's ParsleyJS validation error container.
	        scForm.parsley({
		        errorsContainer: function (el) {
			        return el.closest('.sc-form-group');
		        }
	        });

	        scForm.find( '.sc-payment-btn' ).on( 'click.scpPaymentBtn', function( event ) {

		        if ( scForm.parsley().validate() ) {
			        // Amount already preset in basic [stripe] shortcode (or default of 50).
			        var finalAmount = sc_script[formId].amount;

			        // If user-entered amount found in form, use it's amount instead of preset/default.
			        var scUeaAmount = scForm.find('.sc-uea-custom-amount').val();

			        if (scUeaAmount) {

				        // Multiply amount to what Stripe needs unless zero-decimal currency used.
				        // Always round so there's no decimal. Stripe hates that.
				        if ( isZeroDecimalCurrency(sc_script[formId].currency) ) {
					        finalAmount = Math.round(scUeaAmount);
				        } else {
					        finalAmount = Math.round( parseFloat(scUeaAmount * 100) );
				        }
			        }

			        var scQuantity = scForm.find('.sc-cf-quantity');

			        if(scQuantity.length > 0) {
				        // First we need to set the value
				        // We need to check if it is a radio button so we can grab the selected value
				        if(scQuantity.is('input[type="radio"]')){
					        scQuantity = scForm.find('.sc-cf-quantity:checked').val();
				        } else {
					        scQuantity = scForm.find('.sc-cf-quantity').val();
				        }

				        if( scQuantity > 0 ) {
					        finalAmount = parseInt(scQuantity) * finalAmount;
				        }
			        }

			        // Now pass to the Stripe Checkout handler.
			        // StripeCheckout object from Stripe's checkout.js.
			        // sc_script from localized script values from PHP.
			        // Reference https://stripe.com/docs/checkout#integration-custom for help.

			        var handler = StripeCheckout.configure({
				        key: sc_script[formId].key,
				        image: ( sc_script[formId].image != -1 ? sc_script[formId].image : '' ),
				        token: function(token, args) {

					        // At this point the Stripe checkout overlay is validated and submitted.

					        // Set the values on our hidden elements to pass via POST when submitting the form for payment.
					        scForm.find('.sc_stripeToken').val( token.id );
					        scForm.find('.sc_stripeEmail').val( token.email );
					        scForm.find('.sc_amount').val( finalAmount );

					        // Add shipping fields values if the shipping information is filled
					        if( ! $.isEmptyObject( args ) ) {
						        scForm.find('.sc-shipping-name').val(args.shipping_name);
						        scForm.find('.sc-shipping-country').val(args.shipping_address_country);
						        scForm.find('.sc-shipping-zip').val(args.shipping_address_zip);
						        scForm.find('.sc-shipping-state').val(args.shipping_address_state);
						        scForm.find('.sc-shipping-address').val(args.shipping_address_line1);
						        scForm.find('.sc-shipping-city').val(args.shipping_address_city);
					        }

					        //Unbind original form submit trigger before calling again to "reset" it and submit normally.
					        scForm.unbind('submit');
					        scForm.submit();

					        //Disable original payment button and change text for UI feedback while POST-ing to Stripe.
					        scForm.find('.sc-payment-btn')
						        .prop('disabled', true)
						        .find('span')
						        .text('Please wait...');
				        },
				        alipay: ( sc_script[formId].alipay == 1 || sc_script[formId].alipay == 'true' ? true : false ),
				        alipayReusable: ( sc_script[formId].alipay_reusable == 1 || sc_script[formId].alipay_reusable == 'true' ? true : false ),
				        locale: ( sc_script[formId].locale != -1 ? sc_script[formId].locale : '' )
			        });
					
			        var stripeParams = {
				        name: ( sc_script[formId].name != -1 ? sc_script[formId].name : '' ),
				        description: ( sc_script[formId].description != -1 ? sc_script[formId].description : '' ),
				        amount: finalAmount,
				        currency: ( sc_script[formId].currency != -1 ? sc_script[formId].currency : 'USD' ),
				        panelLabel: ( sc_script[formId].panelLabel != -1 ? sc_script[formId].panelLabel : 'Pay {{amount}}' ),
				        billingAddress: ( sc_script[formId].billingAddress == 'true' || sc_script[formId].billingAddress == 1 ? true : false ),
				        shippingAddress: ( sc_script[formId].shippingAddress == 'true' || sc_script[formId].shippingAddress == 1 ? true : false ),
				        allowRememberMe: ( sc_script[formId].allowRememberMe == 1 || sc_script[formId].allowRememberMe == 'true' ?  true : false ),
				        bitcoin: ( sc_script[formId].bitcoin == 1 || sc_script[formId].bitcoin == 'true' ?  true : false ),
				        zipCode: ( sc_script[formId].zipCode == 1 || sc_script[formId].zipCode == 'true' ?  true : false )
			        };

			        // When using do_shortcode() the prefill_email option not being set causes some errors and issues and this fixes it
			        // by not including the 'email=""' if prefill_email is not set. Having it set to blank is what causes the issue.
			        if ( sc_script[formId].email != -1 ) {
				        stripeParams.email = sc_script[formId].email;
			        }

			        handler.open( stripeParams );
		        }

		        event.preventDefault();
	        });

	        // Hook up Apply button for coupon codes.
	        scForm.find( '.sc-coup-apply-btn' ).on( 'click.scpCouponApply', function( event ) {
				
		        event.preventDefault();
				
		        // Trigger coupon remove link click.
				scForm.find('.sc-coup-remove-coupon').click();

		        // Clear out any previous validation message.
		        scForm.find('.sc-coup-validation-message').empty();

		        // Check for non-blank coupon code value.
		        var scCouponCode = scForm.find('.sc-coup-coupon').val();

		        if (scCouponCode) {

			        // Show loading indicator while checking code validity.
			        scForm.find('.sc-coup-loading').show();
					
					// We need to find out here if we are in test mode or not so we can pass it to the AJAX callback
					var scTestMode = scForm.find('input[name="sc_test_mode"]');
					
					if(scTestMode.length > 0 ) {
						scTestMode = 'true';
					} else {
						scTestMode = 'false';
					}
					
			        // AJAX POST params
			        var params = {
				        action: 'sc_coup_get_coupon',
				        coupon: scCouponCode,
				        // Amount already preset in basic [stripe] shortcode (or default of 50).
				        amount: sc_script[formId].amount,
						test_mode: scTestMode
			        };

			        // Send AJAX POST -- sc_coup.ajaxurl from localized JS.
			        $.post(sc_coup.ajaxurl, params, function (response) {

				        if (response.success) {

					        // Coupon code success! Do the work...
												
					        // response.message is actually the raw amount.
					        // response.coupon has the other properties.
					        var finalAmount = response.message;
					        var coupon = response.coupon;

					        // Set localized JS var to amount that Stripe handler will use.
					        sc_script[formId].amount = finalAmount;

					        // If a valid coupon code, set hidden field value to new code.
					        // Even if it replaces an already valid code.
					        // Only one coupon code per transaction allowed.
					        scForm.find('.sc-coup-coupon-code').val(coupon.code);

					        // Also clear visible coupon code input value.
					        scForm.find('.sc-coup-coupon').val('');

					        // Clear out any previous coupon success message and hide removal link.
					        scForm.find('.sc-coup-success-message').empty();
					        scForm.find('.sc-coup-remove-coupon').hide();

					        // Start coupon code message with successful used code itself.
					        // Format: [code] - [#/% off] [x]
					        var couponCodeMsg = coupon.code + ': ';

					        // Change "amount off" part of coupon message depending on coupon type.
					        if (coupon.type == 'percent') {
						        couponCodeMsg += coupon.amountOff + '% off';
					        }
					        else if (coupon.type == 'amount') {
						        couponCodeMsg += currencyFormattedAmount(coupon.amountOff, sc_script[formId].currency) + ' off';
					        }

					        // Finally display coupon code success message.
					        scForm.find('.sc-coup-success-message').text(couponCodeMsg);

					        // Show coupon removal link.
					        scForm.find('.sc-coup-remove-coupon').show();

					        // Update total amount shown.
					        scForm.find('.sc-total-amount').text(currencyFormattedAmount(finalAmount, sc_script[formId].currency));

				        } else {

					        // Invalid coupon code or other error from server.
					        scForm.find('.sc-coup-validation-message').text(response.message);
				        }

				        // Hide loading indicator.
				        scForm.find('.sc-coup-loading').hide();

			        }, 'json');
		        }
	        });

	        // Hook up coupon code "remove" links.
	        scForm.find( '.sc-coup-remove-coupon' ).on( 'click.scpCouponRemove' , function( event ) {

		        event.preventDefault();
				
		        // Reset localized JS var that Stripe handler will use.
		        sc_script[formId].amount = sc_script[formId].originalAmount;

		        // Blank out coupon code hidden field value.
		        scForm.find('.sc-coup-coupon-code').val('');

		        // Clear out any previous coupon success message and hide removal link.
		        scForm.find('.sc-coup-success-message').empty();
		        scForm.find('.sc-coup-remove-coupon').hide();

		        // Update total amount shown.
		        // Amount already preset in basic [stripe] shortcode (or default of 50).
		        scForm.find('.sc-total-amount').text(currencyFormattedAmount(sc_script[formId].amount, sc_script[formId].currency));
	        });
			
			scForm.find('.sc-uea-custom-amount').on( 'keyup', function() { 
				if(sc_script[formId].currency == 'USD') {
					scForm.find('.sc-total-amount').html('$' + $(this).val());
				} else {
					scForm.find('.sc-total-amount').html($(this).val() + ' ' + sc_script[formId].currency);
				}
			});
        });
		
		// Disable comma (,) for custom amount field
	    $body.find( '.sc-uea-custom-amount' ).on( 'keypress', function( event ) {
			if ( event.which == 44 ) {
				event.preventDefault();
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
