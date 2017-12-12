<?php
/**
 * Destination Blog Posts
 *
 */

// This template includes built-in layout containers.
add_filter('theme_template_has_layout', function(){ return true; });

// Check for content sections
$sub_nav_items = destination_sub_navigation( false ); // only return

get_header(); ?>

	<?php get_template_part( 'templates/parts/destinations-sub-nav' ); ?>

	<section class="main">
		<div class="container">
			<div class="row">
			
				<div class="col-sm-12 col-fixed-content">
					<section class="blog-posts-alt">

					<?php

					// The Query
					$the_query = $sub_nav_items['articles']; //new WP_Query( $args );

					// The Loop
					if ( $the_query->have_posts() ) {
					
						// for each post...
						while ( $the_query->have_posts() ) : $the_query->the_post();

							get_template_part( 'content-post-2', get_post_format() );

						endwhile;


						// Paging function
						if (function_exists( 'rf_get_pagination' )) :
							rf_get_pagination($the_query); 
						endif;

					} else {
						get_template_part( 'no-results', 'destination-blog' ); 
					}
					
					/* Restore original Post Data */
					wp_reset_postdata();

					?>
					</section>
				</div>

				<?php // Sidebar ?>
				<div class="col-sm-12 col-fixed-sidebar">
					<?php get_sidebar(); ?>
				</div><!-- / sidebar -->

			</div><!-- /.row -->
		</div>
	</section>

<?php get_footer(); ?>
