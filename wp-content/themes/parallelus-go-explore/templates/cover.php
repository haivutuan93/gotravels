<?php
/**
 * Template Name: Cover
 * 
 * The template for displaying full background cover pages.
 *
 */

// Disable default content containers.
add_filter('theme_template_has_layout', function(){ return true; });

get_header();

get_footer();