<?php
/**
 * Destination Sub Page
 *
 * The template for displaying destination pages.
 *
 */

// This template includes built-in layout containers.
add_filter('theme_template_has_layout', function(){ return true; });

// Destination ID
$dest_ID = get_the_destination_ID();

get_header(); ?>

		<?php get_template_part( 'templates/parts/destinations-sub-nav' ); ?>

		<section class="main">
			<div class="container">
				<div class="row">
				<?php

				// Start the WP loop
				while ( have_posts() ) : the_post(); ?>

					<div class="col-sm-12 col-fixed-content">
						<div class="row">

							<div class="col-md-3 col-sm-4 page-navigation">
								<ul class="nav nav-stacked">
									<?php
									$info_pages = get_destination_pages( $dest_ID );
									if( count($info_pages) ):
										foreach($info_pages as $info_page): ?>
											<li <?php echo ($post->ID == $info_page['id'])? 'class="active"' : ''; ?>><a href="<?php echo esc_url($info_page['link']); ?>"><?php echo esc_attr($info_page['title']); ?></a></li>
											<?php 
										endforeach;
									endif; ?>
								</ul>
							</div><!-- /.page-navigation -->

							<div class="col-md-9 col-sm-8">
								<header class="page-header">
									<h1 class="page-title"><?php the_title() ?></h1>
									<?php 
									$intro = get_destination_intro();
									if ( !empty($intro) ) {
										?>
										<p class="lead"><?php echo wp_kses_post($intro); ?></p>
										<?php
									} ?>
								</header>

								<?php 
								// Thumbnail 
								if ( has_post_thumbnail() ) : ?>
									<p class="entry-thumbnail">
										<?php the_post_thumbnail(); ?>
									</p><!-- .entry-thumbnail -->
									<?php
								endif; // has_post_thumbnail ?>
								<div class="entry-content"><?php the_content(); ?></div>
							</div><!-- /.page-content -->

						</div>
					</div>

					<?php // Sidebar ?>
					<div class="col-sm-12 col-fixed-sidebar">
						<?php get_sidebar(); ?>
					</div><!-- / sidebar -->

				<?php endwhile; // end of the loop. ?>

				</div><!-- /.row -->
			</div>
		</section>

<?php get_footer(); ?>