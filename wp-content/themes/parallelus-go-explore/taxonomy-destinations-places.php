<?php
/**
 * Destination Places
 *
 * Child destinations list.
 *
 */

// This template includes built-in layout containers.
add_filter('theme_template_has_layout', function(){ return true; });

$dest_ID = get_the_destination_ID();

get_header();

		// Sub-nav
		get_template_part( 'templates/parts/destinations-sub-nav' );

		?>

		<section class="main">
			<div class="container">

			<div class="title-row page-header">
				<h3 class="title-entry"><?php echo __('Places in', 'framework') .' '. get_the_title($dest_ID); ?></h3>
			</div>

			<?php

			// Places (child destinations)
			// -------------------------------------------------

			// The Query
			$args = array(
				'post_type' => 'destination',
				'post_parent' => $dest_ID,
				'posts_per_page' => 9,
				'meta_key' => 'destination_order',
				'orderby' => array( 'meta_value_num' => 'ASC', 'title' => 'ASC' ),
				'paged' => (isset($paged)) ? $paged : 0
			);
			$the_query = new WP_Query( $args );

			// The Loop
			if ( $the_query->have_posts() ) { ?>
				
				<section class="places">
					<div class="row">


					<?php
					// for each post...
					while ( $the_query->have_posts() ) : $the_query->the_post();
					?>
					<div class="col-sm-6 col-md-4">
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
					rf_get_pagination($the_query); 
				endif;

			} 
			
			/* Restore original Post Data */
			wp_reset_postdata();

			?>

			</div>
		</section>

<?php get_footer(); ?>
