<?php
/**
 * The home page file. ( show_on_front = 'page' )
 */

if ( get_option('show_on_front') == 'page' && (int) get_option('page_for_posts') === get_queried_object_ID() ) {
	// The blog page, as specified in "Front page displays"
	get_template_part('index');
} else {
	// The home page of the site
	get_template_part('front-page');
}

