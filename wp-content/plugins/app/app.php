<?php
/**
 * Plugin Name: App
 * Description: Plugin for custom code on this website
 * Version: 5.1.4
 * Author: Christian Heiling
 * Text Domain: app
 *
 */

add_action('init', function() {

    add_action( 'wp_enqueue_scripts', function() {
        wp_enqueue_style( 'app', plugin_dir_url(__FILE__) . 'css/app.css' );
    });
    
    add_shortcode('next_events', function($atts) {
        global $wp_embed;
        $atts = shortcode_atts([ 'max' => 3], $atts);
        	
        $events = tribe_get_events([
            'posts_per_page' => $atts['max'],
            'start_date'     => 'now',
        ]);
        
        $embeds = [];
        foreach($events as $e) {
            $link = get_permalink($e);
            
            $html = '<figure class="wp-block-embed-wordpress wp-block-embed is-type-wp-embed">';
            $html .= $wp_embed->run_shortcode('[embed]' . str_replace('https://', 'http://', $link) . '[/embed]');
            $html .= '</figure>';
            
            $embeds[] = $html;
        }
        
        $embeds = implode('', $embeds);
        return $embeds;
    });
});