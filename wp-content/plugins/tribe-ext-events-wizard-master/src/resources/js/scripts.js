/**
 * @type   {PlainObject}
 */
var tribe = tribe || {};

/**
 * @type   {PlainObject}
 */
tribe.events = tribe.events || {};

/**
 *
 * @type  {PlainObject}
 */
tribe.events.wizard = {};

/**
 *
 * @param  {PlainObject} $   jQuery
 * @param  {PlainObject} obj tribe.events.wizard
 *
 * @return {void}
 */
( function( $, obj ) {
	'use strict';
	var $document = $( document );

	/**
	 * Selectors used for configuration and setup
	 *
	 * @since 4.9.4
	 *
	 * @type {PlainObject}
	 */
	obj.selectors = {
		form: '#tribe-events-wizard__form',
		wrapper: '.tribe-events-wizard__content',
		loader: '.tribe-events-wizard__loader',
		result: '.tribe-events-wizard__result',
		resultSuccess: '.tribe-events-wizard__result-success',
		resultError: '.tribe-events-wizard__result-error',
		redirectLink: '.tribe-events-wizard__result-success-link',
		footer: '.tribe-events-wizard__footer',
	};

	/**
	 * Handles the initialization of the wizard
	 *
	 * @since 1.0.0
	 *
	 * @return {void}
	 */
	obj.ready = function() {
		var $form        = $( obj.selectors.form );

		// Show the wrapper with an effect.
		$( obj.selectors.wrapper ).addClass( 'animated fadeIn delay-1s' );

		$form.steps( {
			headerTag: 'h3',
			bodyTag: 'fieldset',
			transitionEffect: 'slideLeft',
			onStepChanging: function ( event, currentIndex, newIndex ) {
				// Always allow previous action even if the current form is not valid!
				if ( currentIndex > newIndex ) {
					return true;
				}

				// Needed in some cases if the user went back (clean up)
				if ( currentIndex < newIndex ) {
					// To remove error styles
					$form.find( '.body:eq(' + newIndex + ') label.error').remove();
					$form.find( '.body:eq(' + newIndex + ') .error' ).removeClass( 'error' );
				}
				$form.validate().settings.ignore = ':disabled,:hidden';
				return $form.valid();
			},
			onStepChanged: function ( event, currentIndex, priorIndex ) {

				// Used to skip the "Warning" step if the user is old enough and wants to the previous step.
				if ( currentIndex === 2 && priorIndex === 3 ) {
					$form.steps( 'previous' );
				}
			},
			onFinishing: function ( event, currentIndex ) {
				$form.validate().settings.ignore = ":disabled";
				return $form.valid();
			},
			onFinished: function ( event, currentIndex ) {
				var params = $form.serialize() + '&action=tribe_ext_events_wizard_create';

				// Hide the form.
				$( obj.selectors.footer ).addClass( 'animated fadeOut fast' );
				$form.addClass( 'animated fadeOutUp fast' );
				setTimeout( function() {
					$( obj.selectors.loader ).css( 'display', 'flex' );
					$( obj.selectors.form ).css( 'display', 'none' );
				}, 200 );

				// Show the loader.
				$( obj.selectors.loader ).addClass( 'animated fadeInUp fast' );

				$.post(
					TribeEventsWizard.ajaxurl,
					params,
					function( response ) {

						setTimeout( function() {
							$( obj.selectors.loader ).removeClass( 'fadeInUp' );
							$( obj.selectors.loader ).addClass( 'delay-2s bounceOut fast' );
							setTimeout( function() {
								$( obj.selectors.result ).css( 'display', 'flex' );
								$( obj.selectors.loader ).css( 'display', 'none' );
								$( obj.selectors.result ).addClass( 'bounceIn' );
								if ( response.success ) {
									var redirectURL = response.redirect_url.replace( '&amp;','&' );
									// Set redirect link.
									$( obj.selectors.redirectLink ).attr( 'href', redirectURL );

									// we do what we want with the response
									window.location.replace( redirectURL );
								} else {
									// Hide success message and show the error one.
									$( obj.selectors.resultSuccess ).hide();
									$( obj.selectors.resultError ).show();
								}
							}, 2000 );

						}, 4000 );
					}
				);


			}
		} ).validate( {
			errorPlacement: function errorPlacement( error, element ) {
				element.before( error );
			},
				rules: {}
		});

	};

	// Configure on document ready
	$document.ready( obj.ready );

} )( jQuery, tribe.events.wizard );
