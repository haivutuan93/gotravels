<?php
/**
 * Destination Home Page
 *
 */

// This template includes built-in layout containers.
add_filter('theme_template_has_layout', function(){ return true; });

// Helpers
$dest = $post;
$settings = get_destination_settings();

// Check for content sections
$sub_nav_items = destination_sub_navigation( false ); // only return

get_header(); 

		get_template_part( 'templates/parts/destinations-sub-nav' ); ?>

		<!-- Main Section
		================================================== -->
		<section class="main">
			<div class="container">
				<div class="row">

					<div class="col-sm-12 col-fixed-content">
					<?php 


					// Start the WP loop
					while ( have_posts() ) : the_post(); ?>

						<div class="intro">
							<p class="lead"><?php echo get_destination_intro(); ?></p>
							<div class="entry-content"><?php the_content(); ?></div>
						</div>


						<?php 

						// Places (child destinations)
						// -------------------------------------------------

						// The Query
						$args = array(
							'post_type' => 'destination',
							'post_parent' => $dest->ID, // $dest_ID,
							'posts_per_page' => isset($settings['number_posts_child'])? $settings['number_posts_child'] : 2,
							'meta_key' => 'destination_order',
							'orderby' => array( 'meta_value_num' => 'ASC', 'title' => 'ASC' ),
						);
						$places_query = new WP_Query( $args );

						// The Loop
						if ( $places_query->have_posts() ) { ?>
							
						<section class="narrow places">

							<!-- Section Title -->
							<div class="title-row">
								<h3 class="title-entry"><?php _e('Places in', 'framework') ?> <?php the_title(); ?></h3>
								<a href="<?php echo esc_url(get_destination_taxonomy_term_links( 'places', $dest->post_name )) ?>" class="btn btn-primary btn-xs"><?php _e('Find More', 'framework'); ?> &nbsp; <i class="fa fa-angle-right"></i></a>
							</div>

							<div class="row">


								<?php
								// for each post...
								while ( $places_query->have_posts() ) : $places_query->the_post();
								?>
								<div class="col-sm-6">
									<?php get_template_part( 'content', 'place' ); ?>
								</div>
								<?php
								endwhile;

								?>

							</div> <!-- /.row -->

						</section>

							<?php
						} 
						
						/* Restore original Post Data */
						wp_reset_postdata();



						// Pages (information)
						// -------------------------------------------------
						
						if( count($sub_nav_items['information']) ):  ?>
						<section class="narrow page-info">

							<!-- Section Title -->
							<div class="title-row">
								<h3 class="title-entry"><?php _e('Information', 'framework') ?></h3>
								<?php $more_info_link = reset($sub_nav_items['information']); // the more link ?>
								<a href="<?php echo esc_url($more_info_link['link']) ?>" class="btn btn-primary btn-xs"><?php _e('Find More', 'framework'); ?> &nbsp; <i class="fa fa-angle-right"></i></a>
							</div>

							<div class="row">
								<?php
								// The Query
								$info_args = get_destination_pages($post->ID, 'query'); 
								$info_args['posts_per_page'] = 5; 
								$info_query = new WP_Query( $info_args );

								// The Loop
								if ( $info_query->have_posts() ) {

									// for each post...
									$i = 0; $limit = isset($settings['number_posts_information'])? $settings['number_posts_information'] : 5;
									while ( $info_query->have_posts() ) : $info_query->the_post();

										if($i < $limit): 
											$sm = ($i == 0)? 12 : 6;
											$lg = ($i == 0)? 8 : 4; 

											?>
											<div class="col-sm-<?php echo esc_attr($sm); ?> col-lg-<?php echo esc_attr($lg); ?>">
												<a href="<?php echo esc_url($sub_nav_items['information'][$post->ID]['link']); ?>" class="page-box-link">
													<article class="page-box">
														<h3 class="entry-title"><?php the_title(); ?></h3>
														<?php // $info = get_post($key); ?>
														<p class="entry-excerpt"><?php echo get_the_excerpt(); ?></p>
														<p class="more-link"><?php _e('Read more', 'framework') ?></p>
														<div class="page-box-destination">
															<span><i class="fa fa-map-marker"></i> <?php echo esc_attr($dest->post_title); ?></span>
														</div>
													</article>
												</a>
											</div>
											<?php 

											$i++;
										endif;
									endwhile;

								}
								
								// Restore original Post Data
								wp_reset_postdata();
								
								?>
						</section>
						<?php endif; ?>

						<?php 

						// Directory (index)
						// -------------------------------------------------

						if( count($sub_nav_items['directory']) ): ?>						
						<section class="narrow directory">
							
							<!-- Section Title -->
							<div class="title-row">
								<h3 class="title-entry"><?php _e('Directory', 'framework') ?></h3>
								<?php $more_info_link = reset($sub_nav_items['directory']); // the more link ?>
								<a href="<?php echo esc_url($more_info_link['link']) ?>" class="btn btn-primary btn-xs"><?php _e('Find More', 'framework'); ?> &nbsp; <i class="fa fa-angle-right"></i></a>
							</div>

							<div class="row">
								<?php 
								$limit = isset($settings['number_posts_directory'])? $settings['number_posts_directory'] : 6;
								$placeholder = "<img width='960' height='540' src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAJCAMAAAAM9FwAAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAAZQTFRF////AAAAVcLTfgAAAAF0Uk5TAEDm2GYAAAAOSURBVHjaYmAYpAAgwAAAmQABh704YAAAAABJRU5ErkJggg=='>";												
								foreach($sub_nav_items['directory'] as $directory): ?>
									<div class="col-sm-6 col-lg-4">
										<article class="place-box card">
											<a href="<?php echo esc_url($directory['link']); ?>" class="place-link">
												<header>
													<h3 class="entry-title"><i class="fa fa-folder"></i><?php echo esc_attr($directory['name']); ?></h3>
												</header>
												<?php if(isset($directory['image'])): ?>
														<div class="entry-thumbnail">
															<?php echo get_the_post_thumbnail($directory['post_ID'], 'place'); ?>
														</div>
												<?php else:	
														echo $placeholder;
													  endif; 
												?>											
											</a>
										</article>
									</div>
									<?php 

									$limit--;
									if (!$limit)
										break;
								endforeach; ?>
							</div> <!-- /.row -->
						</section>
						<?php endif; ?>

						<?php 

						// Articles (blog)
						// -------------------------------------------------

						if( /*isset($settings['menu_item_blogs']) && $settings['menu_item_blogs'] == 'true' &&*/ need_show_articles() && count($sub_nav_items['articles']) && count($sub_nav_items['articles']->posts) ): ?>
						<section class="narrow blog-posts-alt">
							
							<!-- Section Title -->
							<div class="title-row">
								<h3 class="title-entry"><?php _e('Articles', 'framework') ?></h3>
								<a href="<?php echo esc_url(get_destination_taxonomy_term_links( 'articles', $dest->post_name )) ?>" class="btn btn-primary btn-xs"><?php _e('Find More', 'framework'); ?> &nbsp; <i class="fa fa-angle-right"></i></a>
							</div>

							<div class="row">
								<div class="col-sm-12">
									<?php
												
									// The Query
									$the_query = $sub_nav_items['articles']; //new WP_Query( $args );

									// The Loop
									if ( $the_query->have_posts() ) {
									
										// for each post...
										$limit = isset($settings['number_posts_blogs'])? $settings['number_posts_blogs'] : 3;
										while ( $the_query->have_posts() ) : $the_query->the_post();

											get_template_part( 'content-post-2', get_post_format() );

											$limit--;
											if (!$limit)
												break;
										endwhile;

									} else {
										get_template_part( 'no-results', 'destination-blog' ); 
									}
									
									/* Restore original Post Data */
									wp_reset_postdata();
												
									?>									
								</div>
						</section>
						<?php endif;

					endwhile; // end of the loop. ?>

					</div>

					<div class="col-sm-12 col-fixed-sidebar">
						<?php get_sidebar(); ?>
					</div><!-- /sidebar -->

				</div><!-- /.row -->
			</div>
		</section>

<?php get_footer(); ?>