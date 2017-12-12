<?php
/**
 * Template part: Home Page Section - Slideshow
 */
// The content source set in theme options
$content_format       = get_options_data('home-page', 'home-section-4-active');
$content_category     = get_options_data('home-page', 'home-section-4-destination-category');
$content_slideshow    = get_options_data('home-page', 'home-section-4-simple-slideshow-source');
$content_rev_slider   = get_options_data('home-page', 'home-section-4-rev-slider-source');
$content_layer_slider = get_options_data('home-page', 'home-section-4-layer-slider-source');
$content = array();
$has_content =  false;

// Content check
switch ($content_format) {
	case 'destinations':

		// The Query
		$args = array(
			'post_type' => 'destination',
			'posts_per_page' => -1, // set a max here, -1 for all
			'suppress_filters' => defined('ICL_LANGUAGE_CODE')? 0 : 1,
			'tax_query' => array(
				array(
					'taxonomy' => 'travel-category',
					'field'    => 'id',
					'terms'    => array($content_category),
				),
			)
		);
		$the_query = new WP_Query( $args );

		if ( $the_query->have_posts() ) { 

			// content exists and which output method to use
			$has_content = 'default';

			while ( $the_query->have_posts() ) : $the_query->the_post(); 

				// Title
				$content[$post->ID]['title'] = get_the_title();

				// Description
				$content[$post->ID]['description'] = get_destination_intro( $post->ID );
				if (function_exists('get_destination_intro')) {
					$content[$post->ID]['description'] = dest_get_words( $content[$post->ID]['description'], 25).'...';
				}
				// Image
				$content[$post->ID]['image'] = '';
				if ( has_post_thumbnail() ) : 
					$image_ID = get_post_thumbnail_id( $post->ID );
					$image = wp_get_attachment_image_src( $image_ID, 'header' );
					// the image with CSS
					$content[$post->ID]['image'] = 'background-image: url('.$image[0].')'; 
				endif; 

				// Link
				$content[$post->ID]['link'] = get_permalink();
			endwhile;
		}
		break;
	
	case 'simple':

		$id = $content_slideshow;
		if (function_exists('sts_get_slider') && !empty($id)) {

			$simple_slider = sts_get_slider( $id );
			if ( is_array($simple_slider) && !empty($simple_slider) ) { 

				// content exists and which output method to use
				$has_content = 'default';

				foreach ($simple_slider as $index => $slide) {


					// Title
					$content[$index]['title'] = (isset($slide['title'])) ? $slide['title'] : '';

					// Description
					$content[$index]['description'] = (isset($slide['description'])) ? $slide['description'] : '';

					// Image
					$content[$index]['image'] = (isset($slide['source'])) ? $slide['source'] : '';
					if ( !empty($content[$index]['image']) ) : 
						// the image with CSS
						$content[$index]['image'] = 'background-image: url('.$content[$index]['image'].')'; 
					endif; 

					// Link
					$content[$index]['link']   = (isset($slide['slide-link'])) ? $slide['slide-link'] : '';
					$content[$index]['target'] = (isset($slide['open-new-window']) && $slide['open-new-window'] == 'checked') ? '_blank' : '';
				}
			}
		}
		break;

	case 'rev-slider':
		// content exists and which output method to use
		$has_content = 'rev-slider';
		$alias = $content_rev_slider;
		break;
	
	case 'layer-slider':
		// content exists and which output method to use
		// $has_content = 'custom';
		$id = $content_layer_slider;
		break;

	default:
		# ???
		break;
}

// Slideshow
// -------------------------------------------------

// We have some content!
if ( $has_content ) :

	// Use the default HTML structure of the basic slide show
	// -------------------------------------------------------
	if ($has_content == 'default') {
		?>
		<section class="featured-slider">
			<div class="featured-carousel">
				
			<?php
			foreach ($content as $key => $value) {

				// check for a link target (like a new window)
				$target = '';
				if (isset($value['target']) && !empty($value['target'])) {
					$target = 'target="'. esc_attr($value['target']) .'"';
				}
				?>
				<div class="item">
					<div class="bg-img" style="<?php echo esc_attr($value['image']) ?>"></div>
					<div class="color-hue"></div>
					<div class="container">
						<div class="row">
							<div class="col-sm-12 col-md-6 col-md-offset-6">
								<article>
									<h3><?php echo wp_kses_post($value['title']) ?></h3>
									<p class="lead"><?php echo wp_kses_post($value['description']) ?></p>
									<a href="<?php echo esc_url($value['link']) ?>" <?php echo  $target; // escaped above ?> class="btn btn-primary"><?php _e('Read More', 'framework') ?> &nbsp; <i class="fa fa-angle-right"></i></a>
								</article>
							</div>
						</div>
					</div>
				</div>
				<?php
			} ?>
			</div>
		</section>
		<?php

	// Use the custom structure of another slide show
	// -------------------------------------------------------
	} elseif ($has_content == 'rev-slider') {
		// Revolution Slider
		putRevSlider( $alias ); 

	} elseif ($has_content == 'layer-slider') {
		// Layer Slider (coming soon)
	
	}

endif;
