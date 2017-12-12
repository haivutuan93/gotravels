<?php
/**
 * The template for displaying Search Results pages.
 */

get_header(); ?>

	<div class="row">
		<div class="col-sm-12 col-md-8">

			<div class="row">
				<div class="col-sm-12 home-search-field">
					<form class="big-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ) ?>">
						<input type="text" name="s" placeholder="<?php echo esc_attr__( 'Search...', 'framework') ?>" value="<?php echo esc_attr( get_search_query() ) ?>">
						<button type="submit"><span class="glyphicon glyphicon-search"></span></button>
					</form>
				</div>
			</div>

		<?php if ( have_posts() ) : ?>

			<?php if ( function_exists('rf_has_custom_header') && !rf_has_custom_header() ) : ?>
				<header class="page-header">
					<h2 class="page-title"><?php printf( __( 'Search Results for: %s', 'framework' ), '<span>' . get_search_query() . '</span>' ); ?></h2>
				</header> <!-- .page-header -->
			<?php endif; ?>

			<?php 

			while ( have_posts() ) : the_post(); 

				// Output the content
				get_template_part( 'templates/content', 'search' ); 

			endwhile; 

			// Paging function
			if (function_exists( 'rf_get_pagination' )) :
				rf_get_pagination(); 
			endif;
		
		else : 

			get_template_part( 'no-results', 'search' ); 

		endif; // end of loop. ?>

		</div>

		<div class="col-sm-12 col-md-4 col-lg-3 col-lg-offset-1">
			
			<?php get_sidebar('search'); ?>
		
		</div><!-- /sidebar -->
	</div><!-- /.row -->

<?php get_footer(); ?>