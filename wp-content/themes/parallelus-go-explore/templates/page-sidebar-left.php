<?php
/**
 * Template Name: Sidebar - Left
 * 
 * The template for displaying pages with a left sidebar.
 *
 */

get_header(); ?>

	<div class="row">

		<div class="col-sm-8 col-sm-push-4 col-lg-9 col-lg-push-3">

			<?php 

			while ( have_posts() ) : the_post(); 

				// Output content
				get_template_part( 'content', 'page' ); 

				// If comments are open or we have at least one comment, load up the comment template
				if ( comments_open() || '0' != get_comments_number() )
					comments_template();
				
			endwhile; // end of the loop. 
			?>

		</div>

		<div class="col-sm-4 col-sm-pull-8 col-lg-3 col-lg-pull-9">
			
			<?php get_sidebar('left'); ?>
		
		</div><!-- /sidebar -->

	</div><!-- /.row -->

<?php get_footer(); ?>
