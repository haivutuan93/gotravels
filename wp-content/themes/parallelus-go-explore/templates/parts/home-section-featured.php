<?php
/**
 * Template part: Home Page Section - Featured Destinations
 */

// The Featured Destinations set in theme options
$destinations = (array) get_options_data('home-page', 'home-section-1-source-destinations');
if (!isset($destinations[0]) || $destinations[0] == 'all' || $destinations[0] == 'random' ) {
	$destinations = '';
}

// Section title
$section_title = get_options_data('home-page', 'home-section-1-title');
$section_more  = get_options_data('home-page', 'home-section-1-more-text');

// Items to show
$item_count = get_options_data('home-page', 'home-section-1-destination-count');
$item_count = ( $item_count == 'auto' ) ? -1 : $item_count; // default

// Use random
$random = get_options_data('home-page', 'home-section-1-source-random'); 
$random = ( $random == 'true' ) ? true : false;

// section styles
$styles = '';
$container_style['background-color'] = get_options_data('home-page', 'home-section-1-bg-color');
$container_style['background-image'] = get_options_data('home-page', 'home-section-1-bg-image');
foreach ($container_style as $attribute => $style) {
	if ( isset($style) && !empty($style) && $style !== '#') {
		if ($attribute == 'background-image') {
			$style = 'url('. $style .')';
		}
		$styles .= $attribute .':'. $style .';';
	}
}
$styles = (!empty($styles)) ? 'style="'.esc_attr($styles).'"' : '';


// Destinations
// -------------------------------------------------

// The Query
$args = array(
	'post_type' => 'destination',
	'posts_per_page' => (!empty($item_count)) ? $item_count : 4, // could set a max here if needed
);
if (!empty($destinations)) {
	// $args['posts_per_page'] = -1;
	$args['post__in'] = $destinations;
	// $item_count = count($destinations);
}
if ($random) {
	// remove_all_filters('posts_orderby');
	$args['orderby'] = 'rand';
}
$the_query = new WP_Query( $args );

// The Loop
if ( $the_query->have_posts() ) { 


?>

<section class="featured-destinations" <?php echo  $styles; // escaped above ?>>
	<div class="container">
		<div class="cards overlap">

			<!-- Section Title -->
			<div class="title-row">
				<h3 class="title-entry"><?php esc_attr_e($section_title) ?></h3>
			  <?php if (!empty($section_more)) : ?>
				<a href="<?php echo esc_url(get_post_type_archive_link( 'destination' )); ?>" class="btn btn-primary btn-xs"><?php esc_attr_e($section_more) ?> &nbsp; <i class="fa fa-angle-right"></i></a>
			  <?php endif; ?>
			</div>

			<!-- Cards Row -->
			<div class="row">

			<?php

			// Specify the container column class to use based on # of destinations
			$colClass = '';
			switch ($item_count) {
				case 1:
					$colClass = 'col-sm-8 col-sm-push-2 col-md-6 col-md-push-3';
					break;
				case 2:
					$colClass = 'col-lg-4 col-lg-push-2 col-sm-6'; 
					break;
				case 3:
				case 6:
				case 9:
					$colClass = 'col-sm-4'; 
					break;
				case 5:
					$colClass = 'col-sm-2 col-sm-push-1'; 
					break;
				case 8:
				case 12:
				case 16:
				default:
					$colClass = 'col-md-3 col-sm-6'; // default (4)
					break;
			}

			// for each post...
			while ( $the_query->have_posts() ) : $the_query->the_post();

				?>
				<div class="<?php echo esc_attr($colClass) ?>">
					<article class="card">
						<?php 
						// image CSS string
						$image_style = '';

						// Get the image
						if ( has_post_thumbnail() ) : ?>
							<?php 
							$image_ID = get_post_thumbnail_id( $post->ID );
							$image = wp_get_attachment_image_src( $image_ID, 'blog-landscape' );
							$image_style = 'background-image: url('.$image[0].')'; // the URL
						endif; ?>
						<a href="<?php the_permalink(); ?>" class="featured-image" style="<?php echo esc_attr($image_style) ?>"><div class="featured-img-inner"></div></a>
						<div class="card-details">
							<h4 class="card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
							<div class="meta-details clearfix">
								<?php /*
								<div class="rating rating-star">
									<i class="fa fa-star icon highlighted"></i>
									<i class="fa fa-star icon highlighted"></i>
									<i class="fa fa-star icon highlighted"></i>
									<i class="fa fa-star icon highlighted"></i>
									<i class="fa fa-star icon"></i>
								</div>
								*/ ?>
								<ul class="hierarchy">
									<li class="symbol"><i class="fa fa-map-marker"></i></li>
									<?php 
									// Get breadcrumb trail
									$hierarchy = get_post_ancestors($post);
									if (!empty($hierarchy)) {
										$path = array_reverse($hierarchy);
										for ($x = 0; $x < count($path) && $x < 2; $x++) {
											$id = $path[$x];
											echo '<li><a href="'.esc_url(get_permalink($id)).'">'.get_the_title($id).'</a></li>'; 
										}
									} else {
										// Use current destination if no parents
										echo '<li><a href="'.esc_url(get_permalink()).'">'.get_the_title().'</a></li>';
									}
									?>
								</ul>
							</div>
						</div>
					</article>
				</div>
				
			<?php endwhile; ?>

			</div> <!-- /.row -->
		</div>
	</div>
</section>

<?php
} // End if have_posts()

/* Restore original Post Data */
wp_reset_postdata();

?>			
