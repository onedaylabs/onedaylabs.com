/**
 * Pro Admin JS
 */

/* global jQuery, sc_script */

(function($) {
	'use strict';
	
	$(function () {

		var $body = $( 'body' );

		$body.find( '.sc-license-wrap button' ).on( 'click', function( event ) {
			
			event.preventDefault();
			
			var button = $(this);
			var licenseWrap = button.closest( '.sc-license-wrap' );
			var licenseInput = licenseWrap.find( 'input.sc-license-input' );
			
			if( licenseInput.val().length < 1 ) {
				button.html( sc_strings.activate );
				button.data( 'sc-action', 'activate_license' );
				licenseWrap.find('.sc-license-message').html( sc_strings.inactive_msg ).removeClass('sc-valid sc-invalid').addClass( 'sc-inactive' );
			} else {
				// WP 4.2+ wants .is-active class added/removed for spinner.
				licenseWrap.find( '.spinner' ).addClass( 'is-active' );
				
				var data = {
					action: 'activate_license',
					license: button.parent().find('input[type="text"]').val(),
					item: button.data('sc-item'),
					sc_action: button.data('sc-action'),
					id: licenseInput.attr('id')
				};

				$.post( ajaxurl, data, function(response) {
					
					console.log( 'Response: ', response);
					
					// WP 4.2+ wants .is-active class added/removed for spinner.
					licenseWrap.find( '.spinner' ).removeClass( 'is-active' );
					
					if( response == 'valid' ) {
						button.html( sc_strings.deactivate );
						button.data('sc-action', 'deactivate_license');
						licenseWrap.find('.sc-license-message').html( sc_strings.valid_msg ).removeClass('sc-inactive sc-invalid').addClass( 'sc-valid' );
					} else if( response == 'deactivated' ) {
						button.html( sc_strings.activate );
						button.data( 'sc-action', 'activate_license' );
						licenseWrap.find('.sc-license-message').html( sc_strings.inactive_msg ).removeClass('sc-valid sc-invalid').addClass( 'sc-inactive' );
					} else if( response == 'invalid' ) {
						licenseWrap.find('.sc-license-message').html( sc_strings.invalid_msg + ' (' + response + ')' ).removeClass('sc-inactive sc-valid').addClass( 'sc-invalid' );
					} else if( response == 'notfound' ) {
						licenseWrap.find('.sc-license-message').html( sc_strings.notfound_msg + ' (' + response + ')' ).removeClass('sc-inactive sc-valid').addClass( 'sc-invalid' );
					} else if ( response == 'error' ) {
						licenseWrap.find('.sc-license-message').html( sc_strings.error_msg + ' (' + response + ')' ).removeClass('sc-inactive sc-valid').addClass( 'sc-invalid' );
					} else {
						licenseWrap.find('.sc-license-message').html( sc_strings.error_msg + ' (' + response + ')' ).removeClass('sc-inactive sc-valid').addClass( 'sc-invalid' );
					}
				});
			}
			
		});
	});
}(jQuery));
