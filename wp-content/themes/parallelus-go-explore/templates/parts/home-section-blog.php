<?php
/**
 * Template part: Home Page Section - Blog Posts
 */

// The Featured Destinations set in theme options
$category = (array) get_options_data('home-page', 'home-section-5-source-categories');
$post_count = get_options_data('home-page', 'home-section-5-post-count');
$display_style = get_options_data('home-page', 'home-section-5-post-style');

// Section title
$section_title = get_options_data('home-page', 'home-section-5-title');
$section_more  = get_options_data('home-page', 'home-section-5-more-text');

// section styles
$styles = '';
$container_style['background-color'] = get_options_data('home-page', 'home-section-5-bg-color');
$container_style['background-image'] = get_options_data('home-page', 'home-section-5-bg-image');
foreach ($container_style as $attribute => $style) {
	if ( isset($style) && !empty($style) && $style !== '#') {
		if ($attribute == 'background-image') {
			$style = 'url('. $style .')';
		}
		$styles .= $attribute .':'. $style .';';
	}
}
$styles = (!empty($styles)) ? 'style="'.esc_attr($styles).'"' : '';

// blog style
$blog_class      = 'blog-posts';
$template_part   = '';
if (isset($display_style) && $display_style == 'alternate-1') {
	$blog_class      = 'blog-posts-alt';
	$template_part   = '-2';
}

// check the data
$categories = (is_array($category) && !empty($category)) ? $category : '';
$posts_per_page = (!empty($post_count)) ? (int) $post_count : 4;

// Category link
// $npl = explode('"',get_next_posts_link()); // get the next posts page URL (backup for no category selection)
// $npl_url = (isset($npl[1])) ? $npl[1] : '';
$category_link = (!empty($categories) && $categories[0] !== 'none') ? get_category_link( (int) $categories[0] ) : ''; // $npl_url;

// Blog Posts
// -------------------------------------------------

// The Query
$args['posts_per_page'] = $posts_per_page;
if (!empty($categories) && $categories[0] !== 'none') {
	$args['category__and'] = $categories;
}

$the_query = new WP_Query( $args );

// The Loop
if ( $the_query->have_posts() ) {

	?>
	<section class="regular <?php echo esc_attr($blog_class) ?>" <?php echo  $styles; // escaped above ?>>
		<div class="container">

			<div class="title-row">
			  <?php if (!empty($section_title)) : ?>
				<h3 class="title-entry"><?php esc_attr_e($section_title) ?></h3>
			  <?php endif; ?>
			  <?php if (!empty($category_link) && !empty($section_more)) : ?>
				<a href="<?php echo esc_url($category_link); ?>" class="btn btn-primary btn-xs"><?php esc_attr_e($section_more) ?> &nbsp; <i class="fa fa-angle-right"></i></a>
			  <?php endif; ?>
			</div>

			<div class="row">
				<?php
				while ( $the_query->have_posts() ) : $the_query->the_post();

					get_template_part( 'content-post'.$template_part, get_post_format() );

				endwhile;

				/* Restore original Post Data */
				wp_reset_postdata();

				?>
			</div> <!-- /.row -->
		</div> <!-- /.container -->
	</section>
	<?php

} // end have_posts()
