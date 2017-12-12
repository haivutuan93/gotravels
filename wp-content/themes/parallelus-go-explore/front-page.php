<?php
/**
 * The home page file. 
 */

$page_on_front = get_option('page_on_front');
if (get_option('show_on_front') == 'posts' || $page_on_front == '0' || empty($page_on_front)) {

	// Regular home page with theme styling
	
	add_filter('theme_template_has_layout', function(){ return true; }); // the template has layout containers
	get_header();
		// Load the home page template
		get_template_part('templates/parts/home', 'layout-1');
	get_footer();

} else {

	// User selected a page, so show it instead.
	include( get_page_template() );
}