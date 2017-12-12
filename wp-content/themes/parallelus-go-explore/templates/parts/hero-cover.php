<?php
/**
 * The template part for the cover (full screen) background hero image in the header
 */
?>

<!-- Cover element -->
<section id="header" <?php if (is_page_template( 'templates/cover-with-page.php' )) { echo 'class="cover-with-page"'; } ?>>
	<?php
	if ( has_post_thumbnail() ) {
		$bg_image = wp_get_attachment_url( get_post_thumbnail_id($post->ID) );
	} ?>

	<div class="cover-wrapper" style="background-image: url(<?php echo esc_url($bg_image) ?>)">
		<div class="cover-container overlay">
			<div class="cover-inner">
				<div class="container">
					<h1 class="page-title"><?php echo apply_filters('theme_header_title', get_the_title()); ?></h1>
					<?php while ( have_posts() ) : the_post(); ?>

						<?php if (has_excerpt()) : ?>
							<div class="lead"><?php echo get_the_excerpt(); ?></div>
						<?php else : ?>
							<div class="lead"><?php the_content(); ?></div>
						<?php endif; ?>

					<?php endwhile; // end of the loop. ?>
				</div><!-- /.container -->
			</div><!-- /.cover-inner -->
		</div><!-- /.cover-container -->
	</div><!-- /.cover-wrapper -->

</section><!-- /#header -->