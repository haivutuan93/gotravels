<?php
/**
 * The template part for the default home page (layout 1)
 */

// Featured Destinations Section
$featuredSection = get_options_data('home-page', 'home-section-1-active', 'show');
if ($featuredSection != 'hide') {
	get_template_part('templates/parts/home-section', 'featured');
}

// Text and Search Section
$textAndSearchSection = get_options_data('home-page', 'home-section-2-active', 'show');
if ($textAndSearchSection != 'hide') {
	get_template_part('templates/parts/home-section', 'text-and-search');
}

// Accordion Section
$accordionSection = get_options_data('home-page', 'home-section-3-active', 'show');
if ($accordionSection != 'hide') {
	get_template_part('templates/parts/home-section', 'accordion');
}

// Slideshow Section
$slideshowSection = get_options_data('home-page', 'home-section-4-active', 'show');
if ($slideshowSection != 'hide') {
	get_template_part('templates/parts/home-section', 'slideshow');
}

// Blog Posts/Page Section
$blogSection = get_options_data('home-page', 'home-section-5-active', 'show');
if ($blogSection != 'hide') {

	// Custom styled home page posts
	get_template_part('templates/parts/home-section', 'blog');

} else {

	// WordPress Default Blog Posts
	if (get_option('show_on_front') == 'posts') { ?>
	
	<section class="regular blog-posts">
		<div class="container">
			<?php 

			$postStyle = get_options_data('options-page', 'blog-layout-style'); // update to check theme option
			$postStyle = ( !empty($postStyle) ) ? '-'.$postStyle : ''; // set to layout style #
			
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
		</div> <!-- /.container -->
	</section>
	<?php
	}
}
