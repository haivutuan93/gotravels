<?php
/**
 * The main template file. (loads blog posts)
 */

$postStyle = get_options_data('options-page', 'blog-layout-style'); // update to check theme option
$postStyle = ( !empty($postStyle) ) ? '-'.$postStyle : ''; // set to layout style #

get_header(); ?>

	<div class="row blog-posts<?php echo esc_html($postStyle) ?>">
		<div id="content" class="col-lg-12">

		<?php 

		if ( have_posts() ) : ?>

			<div class="row">

			<?php /* Start the Loop */ 
			while ( have_posts() ) : the_post(); 

				/* Include the Post-Format-specific template for the content.
				 * If you want to overload this in a child theme then include a file
				 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
				 */
				get_template_part( 'content-post'. $postStyle, get_post_format() );
			
			endwhile; 
			?>

			</div><!-- /.row -->

			<?php
			
			// Paging function
			if (function_exists( 'rf_get_pagination' )) :
				rf_get_pagination(); 
			endif;
			
		else :

			get_template_part( 'no-results', 'index' ); 

		endif; // end of loop. ?>

		</div><!-- / #content -->
	</div><!-- /.row -->

<?php get_footer(); ?>