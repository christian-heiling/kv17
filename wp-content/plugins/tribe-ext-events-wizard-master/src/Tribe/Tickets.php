<?php
/**
 * Handles Event Tickets integration.
 *
 * @since 1.0.0
 *
 * @package Tribe\Extensions\EventsWizard
 */

namespace Tribe\Extensions\EventsWizard;

use Tribe__Tickets__Tickets as ET_Tickets;

/**
 * Class Tickets
 *
 * @since 1.0.0
 *
 * @package Tribe\Extensions\EventsWizard
 */
class Tickets {

	/**
	 * Add the tickets step to the wizard.
	 *
	 * @param array $steps The array containing wizard steps.
	 * @return array $steps
	 */
	public function wizard_step_tickets( $steps ) {
		$fields = [];

		$fields[] = [
			'id'      => 'tribe_event_tickets',
			'name'    => 'tribe_event_tickets',
			'type'    => 'checkbox',
			'label'   => __( 'I want to sell tickets for this event, create a sample ticket.', PLUGIN_TEXT_DOMAIN ),
			'classes' => 'tribe-events-wizard__field-sell-tickets',
		];

		$fields[] = [
			'id'              => 'tribe_event_ticket_title',
			'name'            => 'tribe_event_ticket_title',
			'type'            => 'text',
			'label'           => esc_html__( 'Your Ticket Title', PLUGIN_TEXT_DOMAIN ),
			'placeholder'     => esc_attr__( 'Your Ticket Title', PLUGIN_TEXT_DOMAIN ),
			'value'           => '',
			'classes'         => '',
			'dependency'      => [ 'depends' => 'tribe_event_tickets', 'condition' => 'is-checked' ],
			'required'        => true,
			'float_label'     => true,
			'data'            => [], // data attributes.
			'wrapper_classes' => 'tribe-dependent tribe-events-wizard__input--hidden',
		];

		$step = [
			'id'          => 'event-tickets',
			'step_title'  => esc_html__( 'Tickets', PLUGIN_TEXT_DOMAIN ),
			'title'       => esc_html__( 'Do you want to sell tickets?', PLUGIN_TEXT_DOMAIN ),
			'description' => esc_html__( 'You can have free events or create tickets if you want. You will need event tickets installed.', PLUGIN_TEXT_DOMAIN ),
			'fields'      => $fields,
			'classes'     => '',
		];

		$steps[] = $step;

		return $steps;
	}

	/**
	 * We hook to the AJAX save and add a ticket.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $event_id The ID of the event that was created.
	 * @param array $_post The $_post vars submitted in the form.
	 *
	 * @return void
	 */
	public function tickets_save_data( $event_id, $_post ) {

		if ( empty( $event_id ) ) {
			return;
		}

		$create_tickets = isset( $_post['tribe_event_tickets'] ) ? sanitize_text_field( $_post['tribe_event_tickets'] ) : '';

		if ( empty( $create_tickets ) ) {
			return;
		}

		$ticket_title = isset( $_post['tribe_event_ticket_title'] ) ? sanitize_text_field( $_post['tribe_event_ticket_title'] ) : esc_html__( 'Test ticket', PLUGIN_TEXT_DOMAIN );

		$provider       = ET_Tickets::get_event_ticket_provider( null );
		$provider_class = tribe( $provider );
		$ticket_data = [
			'ticket_name'             => $ticket_title,
			'ticket_description'      => "Test ticket description for {$ticket_title}",
			'ticket_show_description' => 1,
			'ticket_price'            => 10,
		];

		$ticket = $provider_class->ticket_add( $event_id, $ticket_data );

	}
}
