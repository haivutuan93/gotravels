<?php
/**
 * Destination Places / Archive
 *
 * Master destinations list.
 *
 */

get_header(); ?>

	<?php

	// Places (child destinations)
	// -------------------------------------------------

	// The Query
	$args = array(
		'post_type' => 'destination',
		'posts_per_page' => 24,
		'orderby' => array( 'menu_order' => 'ASC', 'parent' => 'ASC', 'title' => 'ASC' ),
	);
	$args = is_destination_archive( $args );
	$the_query = new WP_Query( $args );

	// The Loop
	if ( $the_query->have_posts() ) { ?>
		
		<section class="places">
			<div class="row">

			<?php
			// for each post...
			while ( $the_query->have_posts() ) : $the_query->the_post();
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
			rf_get_pagination( $the_query ); 
		endif;

	} 
	
	/* Restore original Post Data */
	wp_reset_postdata(); ?>

<?php get_footer(); ?>