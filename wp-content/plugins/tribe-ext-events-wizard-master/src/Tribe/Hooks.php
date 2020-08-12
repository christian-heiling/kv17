<?php
/**
 * Handles hooking all the actions and filters used by the module.
 *
 * To remove a filter:
 * remove_filter( 'some_filter', [ tribe( Tribe\Extensions\EventsWizard\Hooks::class ), 'some_filtering_method' ] );
 * remove_filter( 'some_filter', [ tribe( 'events-wizard.hooks' ), 'some_filtering_method' ] );
 *
 * To remove an action:
 * remove_action( 'some_action', [ tribe( Tribe\Extensions\EventsWizard\Hooks::class ), 'some_method' ] );
 * remove_action( 'some_action', [ tribe( 'events-wizard.hooks' ), 'some_method' ] );
 *
 * @since 1.0.0
 *
 * @package Tribe\Extensions\EventsWizard
 */

namespace Tribe\Extensions\EventsWizard;

/**
 * Class Hooks
 *
 * @since 1.0.0
 *
 * @package Tribe\Extensions\EventsWizard
 */
class Hooks extends \tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * Adds the actions required by the extension.
	 *
	 * @since 1.0.0
	 */
	protected function add_actions() {
		add_action( 'admin_init', [ $this, 'action_admin_redirect' ] );
		add_action( 'admin_menu', [ $this, 'action_admin_menu' ] );
		add_action( 'wp_ajax_tribe_ext_events_wizard_create', [ $this, 'action_ajax_create_event' ] );
		add_action( 'wp_ajax_nopriv_tribe_ext_events_wizard_create', [ $this, 'action_ajax_create_event' ] );

		if ( class_exists( 'Tribe__Tickets__Main' ) ) {
			add_filter( 'tribe_ext_events_wizard_event_add', [ $this, 'action_tickets_save_data' ], 10, 2 );
		}
	}

	/**
	 * Adds the actions required by the extension.
	 *
	 * @since 1.0.0
	 */
	protected function add_filters() {
		if ( class_exists( 'Tribe__Tickets__Main' ) ) {
			add_filter( 'tribe_ext_events_wizard_data', [ $this, 'filter_tickets_wizard_data' ] );
		}
	}

	/**
	 * Adds the redirect of new posts to the wizard.
	 *
	 * @since 1.0.0
	 */
	public function action_admin_redirect() {
		$this->container->make( Wizard::class )->page_redirect();
	}

	/**
	 * Adds the Wizard page.
	 *
	 * @since 1.0.0
	 */
	public function action_admin_menu() {
		$this->container->make( Wizard::class )->add_wizard_page();
	}

	/**
	 * Hook the AJAX functionality.
	 *
	 * @since 1.0.0
	 */
	public function action_ajax_create_event() {
		$this->container->make( Wizard::class )->ajax_create_event();
	}

	/**
	 * Add the ET wizard step data.
	 *
	 * @since 1.0.0
	 */
	public function filter_tickets_wizard_data( $steps ) {
		return $this->container->make( Tickets::class )->wizard_step_tickets( $steps );
	}

	/**
	 * Handle ET saving
	 *
	 * @since 1.0.0
	 */
	public function action_tickets_save_data( $event_id, $_post ) {
		$this->container->make( Tickets::class )->tickets_save_data( $event_id, $_post );
	}
}