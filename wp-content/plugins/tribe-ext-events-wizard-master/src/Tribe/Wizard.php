<?php
namespace Tribe\Extensions\EventsWizard;

use Tribe__Events__Main as Events_Plugin;
use Tribe__Utils__Array as Arr;

/**
 * Class Wizard
 *
 * @since   1.0.0
 *
 * @package Tribe\Extensions\EventsWizard
 */
class Wizard {

	/**
	 * ID for the Wizard in WP.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $page_id = 'tribe-events-wizard';

	/**
	 * Manages the admin page redirect.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function page_redirect() {
		global $pagenow;

		// Allow to set tribe_wizard get variable to avoid the wizard if needed.
		if ( isset( $_GET['tribe_wizard'] ) && ! tribe_is_truthy( $_GET['tribe_wizard'] ) ) {
			return;
		}

		/**
		 * Add the option to prevent redirects to the wizard by using this filter.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $should_redirect If the admin page should redirect.
		 */
		$should_redirect = apply_filters( 'tribe_ext_events_wizard_admin_redirect', $should_redirect = true );

		// Bail if we don't want to handle redirects.
		if ( empty( $should_redirect ) ) {
			return;
		}

		if (
			'post-new.php' === $pagenow
			&& isset( $_GET['post_type'] ) && $_GET['post_type'] === Events_Plugin::POSTTYPE
		) {
			wp_safe_redirect( admin_url( '/options-general.php?page=' . $this->page_id ) );
			exit;
		}
	}

	/**
	 * Fetches the Wizard title.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Wizard', PLUGIN_TEXT_DOMAIN );
	}

	/**
	 * Add the wizard page to WordPress admin.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function add_wizard_page() {

		add_submenu_page(
			'options-general.php', // @todo: check if we can move it under `Events`
			$this->get_title(),
			$this->get_title(),
			'manage_options',
			sanitize_key( $this->page_id ),
			[ $this, 'wizard_page' ]
		);
	}

	/**
	 * Check if it's on the Wizard page.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_on_page() {
		return ! empty( $_GET['page'] ) && $this->page_id === $_GET['page'];
	}

	/**
	 * Wizard page.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function wizard_page() {
		// Do not proceed, if we're not on the right page.
		if ( ! $this->is_on_page() ) {
			return;
		}

		// Include assets.
		tribe_asset_enqueue_group( Assets::$group_key );
		tribe_asset_enqueue_group( 'events-admin' );

		/**
		 * Start the actual page content.
		 */
		$this->wizard_page_header();
		?>

		<div class="tribe-events-wizard__wrapper">

			<div class="tribe-events-wizard__content" style="opacity: 0;">

				<?php $this->wizard_page_content(); ?>

			</div>

		</div>

		<?php $this->wizard_page_footer(); ?>

		<?php
	}

	public function ajax_create_event() {

		if ( ! check_ajax_referer( 'tribe_ext_events_wizard_nonce', 'nonce' ) ) {
			exit( '🤔mmm, naughty!' );
		}

		// Get and sanitize data.
		$event_title      = isset( $_POST['tribe_event_title'] ) ? sanitize_text_field( $_POST['tribe_event_title'] ) : 'The event title';
		$event_start_date = isset( $_POST['tribe_event_start_date'] ) ? sanitize_text_field( $_POST['tribe_event_start_date'] ) : gmdate( 'Y-m-d' );
		$event_end_date   = isset( $_POST['tribe_event_end_date'] ) ? sanitize_text_field( $_POST['tribe_event_end_date'] ) : gmdate( 'Y-m-d' );
		$event_all_day    = isset( $_POST['tribe_event_all_day'] ) ? sanitize_text_field( $_POST['tribe_event_all_day'] ) : false;
		$event_start_time = isset( $_POST['tribe_event_start_time'] ) ? sanitize_text_field( $_POST['tribe_event_start_time'] ) : '13:00';
		$event_end_time   = isset( $_POST['tribe_event_end_time'] ) ? sanitize_text_field( $_POST['tribe_event_end_time'] ) : '17:00';
		$event_url        = isset( $_POST['tribe_event_link'] ) ? sanitize_text_field( $_POST['tribe_event_link'] ) : '';

		list( $event_start_hour, $event_start_minute ) = explode( ':', $event_start_time );
		list( $event_end_hour, $event_end_minute )     = explode( ':', $event_end_time );

		// Prepare the args for the event creation.
		$args = [
			'post_author'      => get_current_user_id(),
			'post_title'       => $event_title,
			'post_content'     => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', PLUGIN_TEXT_DOMAIN ),
			'post_status'      => 'draft',
			'EventStartDate'   => $event_start_date,
			'EventEndDate'     => $event_end_date,
			'EventAllDay'      => ! empty( $event_all_day ),
			'EventStartHour'   => $event_start_hour,
			'EventStartMinute' => $event_start_minute,
			'EventEndHour'     => $event_end_hour,
			'EventEndMinute'   => $event_end_minute,
			'EventCost'        => '',
			'EventURL'         => $event_url,
		];

		/**
		 * Add the ability to filter the args for the event creation.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $args The args sent for the event creation.
		 */
		$args = apply_filters( 'tribe_ext_events_wizard_event_args', $args );

		// Attempt to create event.
		$event_id = tribe_create_event( $args );

		// Init AJAX response.
		$response['success'] = false;

		// Populate AJAX response with info if the event creation succeeds.
		if ( ! empty( $event_id ) ) {
			$redirect_url = get_edit_post_link( $event_id );

			$response = [
				'success'      => true,
				'event_id'     => $event_id,
				'redirect_url' => $redirect_url,
			];
		}

		/**
		 * Trigger action after event creation, so others can hook in here.
		 *
		 * @since 1.0.0
		 *
		 * @param int $event_id The id of the event that was just created.
		 */
		do_action( 'tribe_ext_events_wizard_event_add', $event_id, $_POST );

		/**
		 * Add the ability to filter the JSON response.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $response The response we're sending to the JS
		 */
		$response = apply_filters( 'tribe_ext_events_wizard_event_args', $response, $event_id );

		// Print AJAX response.
		header( 'Content-Type: application/json' );
		echo json_encode( $response );
		die();
	}

	/**
	 * Wizard page header.
	 *
	 * @return void
	 */
	public function wizard_page_header() {
		require_once( Main::PATH . '/src/admin-views/wizard/header.php' );
	}

	/**
	 * Wizard page content.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function wizard_page_content() {

		require_once( Main::PATH . '/src/admin-views/wizard/content/loader.php' );

		require_once( Main::PATH . '/src/admin-views/wizard/content/result.php' );

		$this->wizard_page_form();

	}

	/**
	 * Wizard page form.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function wizard_page_form() {
		?>
		<form id="tribe-events-wizard__form" action="#">
			<?php
			// Create a nonce.
			wp_nonce_field( 'tribe_ext_events_wizard_nonce' );

			// Get steps and iterate.
			$steps = $this->get_wizard_steps();

			foreach ( $steps as $step ) : ?>

				<h3><?php echo $step['step_title']; ?></h3>

				<fieldset class="tribe-events-wizard__step">

					<legend class="tribe-events-wizard__step-title">
						<?php echo $step['title']; ?>
					</legend>

					<div class="tribe-events-wizard__step-description">
						<?php echo wpautop( $step['description'] ); ?>
					</div>

					<?php if ( isset( $step['fields'] ) ) : ?>
						<?php
						$input_group_classes = [ 'tribe-events-wizard__input-group' ];
						if ( ! empty( $step['inputs_wrapper'] ) ) {
							$input_group_classes[] = $step['inputs_wrapper'];
						}
						?>
						<div <?php tribe_classes( $input_group_classes ); ?>>

							<?php foreach ( $step['fields'] as $field ) : ?>
								<?php $this->wizard_page_form_field( $field ); ?>
							<?php endforeach; ?>

						</div>
					<?php endif; ?>

				</fieldset>

			<?php endforeach; ?>

		</form>
		<?php
	}

	/**
	 * Do the form field.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $field The field data.
	 */
	public function wizard_page_form_field( $field ) {
		if ( empty( $field['type'] ) ) {
			return;
		}

		// We include the admin field template according to the field type.
		$field_path = Main::PATH . '/src/admin-views/wizard/content/form/input-' . $field['type'] . '.php';

		// Bail if there's no field for that type.
		if ( ! file_exists( $field_path ) ) {
			return;
		}

		require( $field_path );
	}

	/**
	 * Wizard page footer.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function wizard_page_footer() {
		require_once( Main::PATH . '/src/admin-views/wizard/footer.php' );
	}

	/**
	 * Wizard data.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function get_wizard_steps() {

		$steps = [];

		// Set the main steps for the Wizard.
		$steps[] = $this->get_wizard_step_one();
		$steps[] = $this->get_wizard_step_two();
		$steps[] = $this->get_wizard_step_three();

		/**
		 * Add the ability to filter the wizard steps data.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $steps The wizard steps
		 */
		$steps = apply_filters( 'tribe_ext_events_wizard_data', $steps );

		return $steps;
	}

	/**
	 * Step one data.
	 *
	 * @since 1.0.0
	 *
	 * @return array $step The array containing the step data.
	 */
	private function get_wizard_step_one() {
		$fields = [];

		$fields[] = [
			'id'          => 'tribe_event_title',
			'name'        => 'tribe_event_title',
			'type'        => 'text',
			'label'       => esc_html__( 'Your Event Title', PLUGIN_TEXT_DOMAIN ),
			'placeholder' => esc_attr__( "What's the event title?", PLUGIN_TEXT_DOMAIN ),
			'value'       => '',
			'classes'     => '',
			'dependency'  => [],
			'required'    => true,
			'float_label' => true,
			'data'        => [], // data attributes.
		];

		$step = [
			'id'          => 'event-title',
			'step_title'  => esc_html__( 'What', PLUGIN_TEXT_DOMAIN ),
			'title'       => esc_html__( 'Start creating your event, set the title.', PLUGIN_TEXT_DOMAIN ),
			'description' => esc_html__( 'Creating an event should be simple. This wizard will guide you, and ask you for the most basic information you need to create an event. Let\'s start with something central, please insert the event title.', PLUGIN_TEXT_DOMAIN ),
			'fields'      => $fields,
			'classes'     => '',
		];

		return $step;
	}

	/**
	 * Step two data.
	 *
	 * @since 1.0.0
	 *
	 * @return array $step The array containing the step data.
	 */
	private function get_wizard_step_two() {

		$fields = [];

		$start_timepicker_step = tribe( 'tec.admin.event-meta-box' )->get_timepicker_step( 'start' );
		$end_timepicker_step   = tribe( 'tec.admin.event-meta-box' )->get_timepicker_step( 'end' );
		$timepicker_round      = tribe( 'tec.admin.event-meta-box' )->get_timepicker_round();

		$fields[] = [
			'id'          => 'EventStartDate', // We rely on the TEC admin fields and JS.
			'name'        => 'tribe_event_start_date',
			'type'        => 'text',
			'label'       => __( 'Start date', PLUGIN_TEXT_DOMAIN ),
			'placeholder' => __( 'Start date', PLUGIN_TEXT_DOMAIN ),
			'value'       => date( 'm-d-Y' ),
			'classes'     => 'tribe-datepicker tribe-events-wizard__field-start-date',
			'dependency'  => [ 'depends' => '', 'condition' => '' ],
			'required'    => true,
			'float_label' => true,
		];

		$fields[] = [
			'id'          => 'EventStartTime', // We rely on the TEC admin fields and JS.
			'name'        => 'tribe_event_start_time',
			'type'        => 'text',
			'placeholder' => __( 'Start time', PLUGIN_TEXT_DOMAIN ),
			'value'       => '08:00:00',
			'classes'     => 'tribe-timepicker tribe-events-wizard__field-start-time',
			'dependency'  => [],
			'required'    => false,
			'float_label' => true,
			'data-attr'   => [ 'format' => 'H:i', 'step' => $start_timepicker_step, 'round' => $timepicker_round ], // data attributes.
		];

		$fields[] = [
			'id'          => 'EventEndDate', // We rely on the TEC admin fields and JS.
			'name'        => 'tribe_event_end_date',
			'type'        => 'text',
			'label'       => __( 'End date', PLUGIN_TEXT_DOMAIN ),
			'placeholder' => __( 'End date', PLUGIN_TEXT_DOMAIN ),
			'value'       => date( 'm-d-Y' ),
			'classes'     => 'tribe-datepicker tribe-events-wizard__field-end-date',
			'dependency'  => [],
			'required'    => true,
			'float_label' => true,
		];

		$fields[] = [
			'id'          => 'EventEndTime', // We rely on the TEC admin fields and JS.
			'name'        => 'tribe_event_end_time',
			'type'        => 'text',
			'placeholder' => __( 'End time', PLUGIN_TEXT_DOMAIN ),
			'value'       => '17:00:00',
			'classes'     => 'tribe-timepicker tribe-events-wizard__field-end-time',
			'dependency'  => [],
			'required'    => false,
			'data-attr'   => [ 'format' => 'H:i', 'step' => $end_timepicker_step, 'round' => $timepicker_round ], // data attributes.
		];

		$fields[] = [
			'id'          => 'allDayCheckbox', // We rely on the TEC admin fields and JS.
			'name'        => 'tribe_event_all_day',
			'type'        => 'checkbox',
			'label'       => __( 'Is it an all day event?', PLUGIN_TEXT_DOMAIN ),
			'classes'     => 'tribe-events-wizard__field-all-day',
			'dependency'  => [],
			'data-attr'   => [],
			'wrapper_classes' => 'tribe-allday tribe-events-wizard__all-day-wrapper',
		];

		$step = [
			'id'          => 'event-time',
			'step_title'  => esc_html__( 'When?', PLUGIN_TEXT_DOMAIN ),
			'title'       => esc_html__( 'When is it happening?', PLUGIN_TEXT_DOMAIN ),
			'description' => esc_html__( "By definition, all events happen at a certain date and time. So, let's add the dates for the event.", PLUGIN_TEXT_DOMAIN ),
			'fields'      => $fields,
			'classes'     => '',
			'inputs_wrapper' => 'tribe-datetime-block',
		];

		return $step;
	}

	/**
	 * Step three data.
	 *
	 * @since 1.0.0
	 *
	 * @return array $step The array containing the step data.
	 */
	private function get_wizard_step_three() {
		$fields = [];

		$fields[] = [
			'id'          => 'tribe_event_online',
			'name'        => 'tribe_event_online',
			'type'        => 'checkbox',
			'label'       => __( 'This is an online event', PLUGIN_TEXT_DOMAIN ),
			'classes'     => 'tribe-events-wizard__field-online-event',
			'dependency'  => [],
			'data-attr'   => [],
		];

		$fields[] = [
			'id'          => 'tribe_event_link',
			'name'        => 'tribe_event_link',
			'type'        => 'text',
			'label'       => esc_html__( 'Your Event Link', PLUGIN_TEXT_DOMAIN ),
			'placeholder' => esc_attr__( "http://zoom.us/my-awesome-event", PLUGIN_TEXT_DOMAIN ),
			'value'       => '',
			'classes'     => '',
			'dependency'  => [ 'depends' => 'tribe_event_online', 'condition' => 'is-checked' ],
			'required'    => true,
			'float_label' => true,
			'data'        => [], // data attributes.
			'wrapper_classes' => 'tribe-dependent tribe-events-wizard__input--hidden',
		];

		$step = [
			'id'          => 'event-location',
			'step_title'  => esc_html__( 'Where', PLUGIN_TEXT_DOMAIN ),
			'title'       => esc_html__( 'Where is it happening?', PLUGIN_TEXT_DOMAIN ),
			'description' => esc_html__( 'The event location will let users know where is that they should go when attending. If you have the virtual events extension you can setup your event as virtual (online).', PLUGIN_TEXT_DOMAIN ),
			'fields'      => $fields,
			'classes'     => '',
		];

		return $step;
	}

}