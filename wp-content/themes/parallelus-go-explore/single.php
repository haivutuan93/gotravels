<?php
/**
 * The template for displaying all single posts.
 *
 */

// Probably this could all be done in functions.php with a condition to test if 'is_single'
$show_title = 'show'; // we will get this later

if ($show_title != 'in-header') {
	add_filter('theme_header_title', function() { return ''; });   // set header title blank
	add_filter('theme_header_content', function() { return ''; }); // set sub-title blank
} else {
	add_filter('theme_header_content', function() { 
		$sub_title = 'This is just static text added in single.php';
		return wpautop($sub_title); 
	});
}


// now show the page
get_header(); ?>
	<div id="content" class="row">
		
		<?php 
		while ( have_posts() ) : the_post(); 

			// Output the content
			get_template_part( 'content-post', 'single' );
			
		endwhile; // end of the loop. ?>

	</div><!-- / #content -->

<?php get_footer(); ?>