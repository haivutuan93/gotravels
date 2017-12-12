<?php
/**
 * Template part: Home Page Section - Accordion
 */

// The content source set in theme options
$content_1    = get_options_data('home-page', 'home-accordion-1-category');
$content_2    = get_options_data('home-page', 'home-accordion-2-category');
$content_3    = get_options_data('home-page', 'home-accordion-3-category');

// Section styles
$styles = '';
$container_style['background-color'] = get_options_data('home-page', 'home-section-3-bg-color');
$container_style['background-image'] = get_options_data('home-page', 'home-section-3-bg-image');
foreach ($container_style as $attribute => $style) {
	if ( isset($style) && !empty($style) && $style !== '#') {
		if ($attribute == 'background-image') {
			$style = 'url('. $style .')';
		}
		$styles .= $attribute .':'. $style .';';
	}
}
$styles = (!empty($styles)) ? 'style="'.esc_attr($styles).'"' : '';

// Content check
$accordions = array();
if ((!empty($content_1) && $content_1 !== 'disabled')) {
	$accordions[$content_1]  = array(
		'title' => get_options_data('home-page', 'home-accordion-1-title'),
		'description' => get_options_data('home-page', 'home-accordion-1-description'),
		'link' => get_term_link( (int) $content_1, 'travel-category' )
	);
}
if ((!empty($content_2) && $content_2 !== 'disabled')){
	$accordions[$content_2]  = array(
		'title' => get_options_data('home-page', 'home-accordion-2-title'),
		'description' => get_options_data('home-page', 'home-accordion-2-description'),
		'link' => get_term_link( (int) $content_2, 'travel-category' )
	);
}
if ((!empty($content_3) && $content_3 !== 'disabled')){
	$accordions[$content_3]  = array(
		'title' => get_options_data('home-page', 'home-accordion-3-title'),
		'description' => get_options_data('home-page', 'home-accordion-3-description'),
		'link' => get_term_link( (int) $content_3, 'travel-category' )
	);
}
$has_content   = (!empty($accordions)) ? true : false;


// Content Sections
// -------------------------------------------------

// Show content
if ( $has_content ) { 

	?>

	<section class="regular background" <?php echo  $styles; // escaped above ?>>
		<div class="container">
			<div class="row">

			<?php 

			// Specify the container column class to use based on # of accordions
			$colClass = '';
			switch (count($accordions)) {
				case 1:
					$colClass = 'col-md-8 col-md-push-2 col-lg-6 col-lg-push-3';
					break;
				case 2:
					$colClass = 'col-md-6 col-lg-5 col-lg-push-1'; 
					break;
				case 3:
				default:
					$colClass = 'col-md-6 col-lg-4';
					$colLastClass = 'col-md-8 col-md-push-2 col-lg-4 col-lg-push-0';
					break;
			}

			// track the loops
			$count = 1;

			foreach ($accordions as $accordion => $options) : 

				// Get the category link
				$category_link = '';
				if ( !empty( $options['link'] ) && !is_wp_error( $options['link'] ) ) {
					$category_link = '<a href="'. esc_url($options['link']) .'">'. __('Find More', 'framework') .' &nbsp; <i class="fa fa-arrow-right"></i></a>';
				}
				?>

				<div class="<?php if ($count == 3) : echo esc_attr($colLastClass); else: echo esc_attr($colClass); endif; ?>">
					<article class="card accordion-card">
						<header>
							<h3 class="section-title"><?php esc_html_e(stripslashes($options['title'])) ?></h3>
							<p><?php esc_html_e(stripslashes($options['description'])) ?></p>
						</header>
						<div class="accordion-panel">
							<div class="panel-group" id="accordion-<?php echo esc_attr($accordion) ?>" role="tablist" aria-multiselectable="true">
							<?php

							// The Query
							$args = array(
								'post_type' => 'destination',
								'posts_per_page' => 3, // set a max here
								'tax_query' => array(
									array(
										'taxonomy' => 'travel-category',
										'field'    => 'term_id',
										'terms'    => $accordion,
									),
								)
							);
							$the_query = new WP_Query( $args );

							// The Loop
							if ( $the_query->have_posts() ) { 

								$expanded = 'true';

								// for each post...
								while ( $the_query->have_posts() ) : $the_query->the_post(); 

									// image CSS string
									$image_style = '';

									// Get the image
									if ( has_post_thumbnail() ) : ?>
										<?php 
										$image_ID = get_post_thumbnail_id( $post->ID );
										$image = wp_get_attachment_image_src( $image_ID, 'blog-landscape' );
										$image_style = 'background-image: url('.$image[0].')'; // the URL
									endif; 
									
									?>
									<!-- Guide Panel -->
									<div class="panel panel-default" style="<?php echo esc_attr($image_style); ?>">
										<div id="collapse-<?php echo esc_attr($accordion) ?>-<?php echo esc_attr($post->ID) ?>" class="panel-collapse collapse <?php if ($expanded == 'true') : ?>in<?php endif; ?>" role="tabpanel" aria-labelledby="heading-<?php echo esc_attr($accordion) ?>-<?php echo esc_attr($post->ID) ?>">
											<div class="panel-body">
												<div class="read-more"><?php _e('Details', 'framework') ?> <i class="fa fa-arrow-right"></i></div>
												<a href="<?php echo esc_url(get_permalink()) ?>"><div class="spacer"></div></a>
											</div>
										</div>
										<a data-toggle="collapse" data-parent="#accordion-<?php echo esc_attr($accordion) ?>" href="#collapse-<?php echo esc_attr($accordion) ?>-<?php echo esc_attr($post->ID) ?>" aria-expanded="<?php echo esc_attr($expanded) ?>" aria-controls="collapse-<?php echo esc_attr($accordion) ?>-<?php echo esc_attr($post->ID) ?>">
											<div class="panel-heading" role="tab" id="heading-<?php echo esc_attr($accordion) ?>-<?php echo esc_attr($post->ID) ?>">
												<div class="panel-icon">
													<i class="fa fa-map-marker"></i>
												</div>
												<h4 class="panel-title"><?php the_title() ?></h4>
												<ul class="hierarchy">
													<?php 
													// Get breadcrumb trail
													$hierarchy = get_post_ancestors($post);
													if (!empty($hierarchy)) {
														$path = array_reverse($hierarchy);
														for ($x = 0; $x < count($path) && $x < 2; $x++) {
															$id = $path[$x];
															echo '<li>'.get_the_title($id).'</li>'; 
														}
													} else {
														// Use current destination if no parents
														echo '<li>'.get_the_title().'</li>';
													}
													?>
												</ul>
											</div>
										</a>
									</div>

									<?php 

									// after first loop so set string "false"
									$expanded = 'false';
								endwhile;
							
							} // End if have_posts()

							/* Restore original Post Data */
							wp_reset_postdata();

							?>		

							</div>
						</div> <!-- /.accordion-panel -->
						<footer><?php echo  $category_link; // escaped above ?></footer>
					</article> <!-- /.accordion-card -->
				</div>
				
				<?php 
				// increment
				$count++; 

			endforeach; // end the loop ?>

		    </div> <!-- /.row -->
	    </div>
	</section>

<?php

} // End if has_content

