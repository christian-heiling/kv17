<?php
/**
 * Handles registering all Assets for the Events Happening Now
 *
 * To remove a Assets:
 * tribe( 'assets' )->remove( 'asset-name' );
 *
 * @since 1.0.0
 *
 * @package Tribe\Extensions\EventsWizard
 */
namespace Tribe\Extensions\EventsWizard;

use Tribe__Events__Main as Events_Plugin;
/**
 * Register
 *
 * @since 1.0.0
 *
 * @package Tribe\Extensions\EventsWizard
 */
class Assets extends \tad_DI52_ServiceProvider {

	/**
	 * Key for this group of assets.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public static $group_key = 'events-wizard';

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		$plugin = tribe( Main::class );

		tribe_asset(
			$plugin,
			'tribe-ext-events-wizard-styles',
			'style.css',
			[],
			'wp_enqueue_scripts',
			[
				'groups'       => [ static::$group_key ],
				'conditionals' => [ $this, 'should_enqueue_admin' ],
			]
		);

		tribe_asset(
			$plugin,
			'tribe-ext-events-wizard-jquery-validate',
			'jquery.validate.js',
			[ 'jquery' ],
			'admin_enqueue_scripts',
			[
				'groups'       => [ static::$group_key ],
				'conditionals' => [ $this, 'should_enqueue_admin' ],
			]
		);

		tribe_asset(
			$plugin,
			'tribe-ext-events-wizard-jquery-steps',
			'jquery.steps.js',
			[ 'jquery', 'tribe-ext-events-wizard-jquery-validate' ],
			'admin_enqueue_scripts',
			[
				'groups'       => [ static::$group_key ],
				'conditionals' => [ $this, 'should_enqueue_admin' ],
			]
		);


		tribe_asset(
			$plugin,
			'tribe-ext-events-wizard-scripts',
			'scripts.js',
			[],
			'admin_enqueue_scripts',
			[
				'groups'       => [ static::$group_key ],
				'conditionals' => [ $this, 'should_enqueue_admin' ],
				'in_footer'    => false,
				'localize'     => [
					'name' => 'TribeEventsWizard',
					'data' => [ $this, 'get_ajax_url_data' ],
				],
			]
		);
	}

	/**
	 * Checks if we are on the correct admin pages to enqueue admin
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function should_enqueue_admin() {
		$should_enqueue = is_admin() && tribe( 'events-wizard.wizard' )->is_on_page();

		/**
		 * Allow filtering of where the base Admin Assets will be loaded
		 *
		 * @since 1.0.0
		 *
		 * @param bool $should_enqueue
		 */
		return apply_filters( 'tribe_ext_events_wizard_assets_should_enqueue', $should_enqueue );
	}

	/**
	 * Gets the Localize variable for admin JS
	 *
	 * @since  1.0.0
	 *
	 * @return array
	 */
	public function get_ajax_url_data() {

		$data = array(
			'ajaxurl'   => esc_url_raw( admin_url( 'admin-ajax.php', ( is_ssl() || FORCE_SSL_ADMIN ? 'https' : 'http' ) ) ),
			'post_type' => Events_Plugin::POSTTYPE,
		);

		/**
		 * Makes the localize variable for TEC admin JS filterable.
		 *
		 * @since 1.0.0
		 *
		 * @param array $data {
		 *     These items exist on the TEC object in admin JS.
		 *
		 *     @type string ajaxurl The default URL to wp-admin's AJAX endpoint.
		 *     @type string post_type The Event post type.
		 * }
		 */
		return apply_filters( 'tribe_ext_events_wizard_admin_js_ajax_url_data', $data );
	}
}
