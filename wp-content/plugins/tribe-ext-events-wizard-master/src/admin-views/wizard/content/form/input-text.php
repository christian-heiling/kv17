<?php
namespace  Tribe\Extensions\EventsWizard;

$field_name    = isset( $field['name'] ) ? $field['name'] : '';
$field_id      = isset( $field['id'] ) ? $field['id'] : '';

if ( empty( $field_id ) && empty( $field_name ) ) {
	return;
}

$placeholder   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
$field_label   = isset( $field['label'] ) ? $field['label'] : '';
$field_value   = isset( $field['value'] ) ? $field['value'] : '';
$field_classes = [];
$wrapper_classes = [ 'tribe-events-wizard__input-float-label' ];
$data_attributes = isset( $field['data-attr'] ) ? $field['data-attr'] : [];

if ( ! empty ( $field['classes'] ) ) {
	$field_classes[] = $field['classes'];
}

if ( ! empty ( $field['required'] ) ) {
	$field_classes[] = 'required';
}

if ( ! empty( $field['wrapper_classes'] ) ) {
	$wrapper_classes[] = $field['wrapper_classes'];
}

$dependencies = '';
if ( ! empty( $field['dependency'] ) ) {
	if ( ! empty( $field['dependency']['depends'] ) ) {
		$dependencies .= ' data-depends="#' . esc_attr( $field['dependency']['depends'] ) . '" ';
	}
	if ( ! empty( $field['dependency']['condition'] ) ) {
		$dependencies .= ' data-condition-' . esc_html( $field['dependency']['condition'] ) . ' ';
	}
}

?>
<div <?php tribe_classes( $wrapper_classes ); echo $dependencies; ?>>
	<input
		type="text"
		autocomplete="off"
		<?php if ( ! empty( $field_id ) ) : ?>
			id="<?php echo esc_attr( $field_id ); ?>"
		<?php endif; ?>
		<?php if ( ! empty( $field_name ) ) : ?>
			name="<?php echo esc_attr( $field_name ); ?>"
		<?php endif; ?>
		<?php if ( ! empty( $placeholder ) ) : ?>
			placeholder="<?php echo esc_attr( $placeholder ); ?>"
		<?php endif; ?>
		<?php if ( ! empty( $field_value ) ) : ?>
			value="<?php echo esc_attr( $field_value ); ?>"
		<?php endif; ?>
		<?php tribe_classes( $field_classes ) ?>
		<?php foreach ( $data_attributes as $d => $v ) { echo 'data-' . esc_html( $d ) . '="' . esc_attr( $v ) . '"'; } ?>
	/>
	<?php if ( ! empty( $field_label ) ) : ?>
		<label
		<?php if ( ! empty( $field_id ) ) : ?>
			for="<?php echo esc_attr( $field_id ); ?>"
		<?php endif; ?>
		><?php echo $field_label; ?></label>
	<?php endif; ?>
</div>
