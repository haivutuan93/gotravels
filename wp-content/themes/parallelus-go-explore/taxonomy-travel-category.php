<?php
/**
 * Destination Category 
 * 
 * For "travel-category" taxonomy, lists destinations in category.
 *
 */

// This template includes built-in layout containers.
add_filter('theme_template_has_layout', function(){ return true; });

$dest_ID = get_the_destination_ID();

get_header(); ?>

		<section class="main">
			<div class="container">

			<?php

			// The Loop
			if ( have_posts() ) { ?>
				
				<section class="places">
					<div class="row">


					<?php
					// for each post...
					while ( have_posts() ) : the_post();
					?>
					<div class="col-sm-4">
						<?php get_template_part( 'content', 'place' ); ?>
					</div>
					<?php
					endwhile;

					?>

					</div>
				</section>

				<?php

				// Paging function
				if (function_exists( 'rf_get_pagination' )) :
					rf_get_pagination(); 
				endif;

			} 
			
			/* Restore original Post Data */
			wp_reset_postdata();

			?>

			</div>
		</section>

<?php get_footer(); ?>
