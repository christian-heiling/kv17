<?php
namespace  Tribe\Extensions\EventsWizard;

$skip_url = add_query_arg( [ 'post_type' => 'tribe_events', 'tribe_wizard' => 'false' ], admin_url( 'post-new.php' ) );
?>
<footer class="tribe-events-wizard__footer">
	<a href="<?php echo esc_url( $skip_url ); ?>" class="tribe-events-wizard__skip-link">
		<?php esc_html_e( 'Skip Wizard', PLUGIN_TEXT_DOMAIN ); ?> &rarr;
	</a>
</footer>