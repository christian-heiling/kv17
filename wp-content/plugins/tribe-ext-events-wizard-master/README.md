## 🚀 Events wizard

This is an extension to add a wizard for the event creation. Ideally, this will help and make the event creation as easy as possible.

How does it work:

* It catches the event creation link ( `post-new.php` for `tribe_events` ) and redirects the user to an intermediate step, the wizard.
	* The wizard can be skipped by adding the `tribe_wizard=false` get param.
* Then the wizard page is created from an array that is created in the `Wizard` class.
* The different steps are displayed and if there are required fields, you'll need to fill them to get to the next step.
* Once you're done and completed the wizard, the event creation is triggered with all the information the wizard has collected.
* If the event was created successfully, then you're redirected to the WordPress editor (blocks or classic, depending on your TEC configuration).
* The events are created as drafts, so you can add more info and edit your event as you want before publishing.

## Extending

You can add more steps from your plugin or your theme `functions.php` file:

```php
<?php

add_filter( 'tribe_ext_events_wizard_data', 'custom_last_step' );

function custom_last_step( $steps ) {
	$fields = [];

	$fields[] = [
		'id'          => 'my_confirmation_field',
		'name'        => 'my_confirmation_field',
		'type'        => 'checkbox',
		'label'       => __( 'Just confirm what I want' ),
		'classes'     => 'my-own__css-class',
	];

	$step = [
		'id'          => 'my-confirmation-step',
		'step_title'  => esc_html__( 'Last' ),
		'title'       => esc_html__( 'I need your confirmation' ),
		'description' => esc_html__( 'And here I am explaining why, because I am a description' ),
		'fields'      => $fields,
		'classes'     => '',
	];

	$steps[] = $step;

	return $steps;
}

```

And to intercept the saving and do whatever you want with this information you can do the following:

```php
<?php

add_action( 'tribe_ext_events_wizard_event_add', 'custom_last_step_save' );

function custom_last_step_save( $event_id, $_post ) {
	if ( empty( $event_id ) ) {
		return;
	}

	$my_confirmation_value = isset( $_post['my_confirmation_field'] ) ? sanitize_text_field( $_post['my_confirmation_field'] ) : '';

	update_post_meta( $event_id, '_my_confirmation', $my_confirmation_value );

}
```

You can also filter the event creation args by hooking to the following filter: `tribe_ext_events_wizard_event_args`. For example if you want to publish the events directly, instead of setting them as drafts, you can do the following:

```php
<?php

add_filter( 'tribe_ext_events_wizard_event_args', 'events_wizard_set_new_event_published' );

function events_wizard_set_new_event_published( $args ) {

	$args['post_status'] = 'publish';

	return $args;

}
```

## Supported fields

* `text` - Text.
* `checkbox` - Checkbox.
* More TBD

## Documentation coming soon

* How to add fields for steps.
	* How to add dependencies on fields. i.e.: If one field is checked, then other one is displayed.


## Libraries

* jQuery steps.
* jQuery validate.
