<?php
/**
 * Template Name: Full Width - Flush
 * 
 * The template for displaying pages with no sidebar and no content margins.
 *
 */

// Remove default content containers.
add_filter('theme_template_has_layout', function(){ return true; });

get_header(); ?>

	<?php while ( have_posts() ) : the_post(); ?>

		<?php get_template_part( 'content', 'page' ); ?>

		<?php
			// If comments are open or we have at least one comment, load up the comment template
			if ( comments_open() || '0' != get_comments_number() )
				comments_template();
		?>

	<?php endwhile; // end of the loop. ?>

<?php get_footer(); ?>
