<?php
/**
 * The main service provider for Events Wizard.
 *
 * @since   1.0.0
 * @package Tribe\Extensions\EventsWizard
 */

namespace Tribe\Extensions\EventsWizard;

/**
 * Class Service_Provider
 * @since   1.0.0
 * @package Tribe\Extensions\EventsWizard
 */
class Service_Provider extends \tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		$wizard = new Wizard( $this->container );
		$this->container->singleton( Wizard::class, $wizard );
		$this->container->singleton( 'events-wizard.wizard', $wizard );

		if ( class_exists( 'Tribe__Tickets__Main' ) ) {
			$tickets = new Tickets( $this->container );
			$this->container->singleton( Tickets::class, $tickets );
			$this->container->singleton( 'events-wizard.tickets', $tickets );
		}

		$this->register_hooks();
		$this->register_assets();

		// Register the SP on the container
		$this->container->singleton( 'events-wizard.provider', $this );
		$this->container->singleton( static::class, $this );
	}

	/**
	 * Registers the provider handling assets
	 *
	 * @since 1.0.0
	 */
	protected function register_assets() {
		$assets = new Assets( $this->container );
		$assets->register();

		$this->container->singleton( Assets::class, $assets );
	}

	/**
	 * Registers the provider handling all the 1st level filters and actions for the extension
	 *
	 * @since 1.0.0
	 */
	protected function register_hooks() {
		$hooks = new Hooks( $this->container );
		$hooks->register();

		// Allow Hooks to be removed, by having the them registered to the container.
		$this->container->singleton( Hooks::class, $hooks );
		$this->container->singleton( 'events-wizard.hooks', $hooks );
	}
}
