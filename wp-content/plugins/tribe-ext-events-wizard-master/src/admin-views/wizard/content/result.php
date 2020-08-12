<?php
namespace  Tribe\Extensions\EventsWizard;

?>
<div class="tribe-events-wizard__result">
	<div class="tribe-events-wizard__result-success">
		<h1>🎉 <?php esc_html_e( 'Your event has been created!', PLUGIN_TEXT_DOMAIN ); ?></h1>
		<p><?php esc_html_e( "Congratulations! You're being redirected to the editor.", PLUGIN_TEXT_DOMAIN ); ?></p>
		<p><a class="tribe-events-wizard__result-success-link" href="#"><?php esc_html_e( 'Take me there', PLUGIN_TEXT_DOMAIN ); ?> &rarr;</a></p>
	</div>
	<div class="tribe-events-wizard__result-error">
		<h1>😞 <?php esc_html_e( 'There was an error!', PLUGIN_TEXT_DOMAIN ); ?></h1>
		<p><?php esc_html_e( 'Please try reloading this page.', PLUGIN_TEXT_DOMAIN ); ?></p>
	</div>
</div>