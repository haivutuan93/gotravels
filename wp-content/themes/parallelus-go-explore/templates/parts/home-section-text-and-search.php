<?php
/**
 * Template part: Home Page Section - Text and Search
 */

// The content source set in theme options
$content_1    = get_options_data('home-page', 'home-section-2-content-1');
$content_2    = get_options_data('home-page', 'home-section-2-content-2');
$column_size  = get_options_data('home-page', 'home-section-2-column-size');
$search_field = get_options_data('home-page', 'home-section-2-search');

// Section styles
$styles = '';
$container_style['background-color'] = get_options_data('home-page', 'home-section-2-bg-color');
$container_style['background-image'] = get_options_data('home-page', 'home-section-2-bg-image');
foreach ($container_style as $attribute => $style) {
	if ( isset($style) && !empty($style) && $style !== '#') {
		if ($attribute == 'background-image') {
			$style = 'url('. $style .')';
		}
		$styles .= $attribute .':'. $style .';';
	}
}
$styles = (!empty($styles)) ? 'style="'.esc_attr($styles).'"' : '';

// Column widths
$column_size = (!empty($column_size)) ? explode(':', $column_size) : array('4','8');
$class_left = 'col-lg-'.$column_size[0];
$class_right = 'col-lg-'.$column_size[1];

// Content check
$content_left  = (!empty($content_1) && $content_1 !== 'disabled') ? $content_1 : '';
$content_right = (!empty($content_2) && $content_2 !== 'disabled') ? $content_2 : '';
$has_content   = (!empty($content_left) || !empty($content_right)) ? true : false;

// Search field
$search_html ='';
if (!empty($search_field) && $search_field !== 'disabled') {
	$search_html  = '<div class="col-sm-12 home-search-field">';
	$search_html .= '	<form class="big-search" role="search" method="get" action="'. esc_url( home_url( '/' ) ) .'">';
	$search_html .= '		<input type="text" name="s" placeholder="'. esc_attr__( 'Find Your Next Destination...', 'framework') .'" value="'. esc_attr( get_search_query() ) .'">';
	$search_html .= '		<button type="submit"><span class="glyphicon glyphicon-search"></span></button>';
	$search_html .= '	</form>';
	$search_html .= '</div>';
}

// Content Sections
// -------------------------------------------------

// Show content
if ( $has_content ) { 

	?>
	<section class="regular" <?php echo  $styles; // escaped above ?>>
		<div class="container">
			<div class="row">

			<?php if (!empty($content_left)) : ?>
			<div class="<?php echo esc_attr($class_left); ?>">
				<div class="home-content-left">
					<?php the_static_block($content_left); ?>
				</div>
				<?php 
				if (!empty($search_html) && $search_field == 'content-1') {
					echo  $search_html; // escaped above
				} 
				?>
			</div>
			<?php endif; ?>

			<?php if (!empty($content_right)) : ?>
			<div class="<?php echo esc_attr($class_right); ?>">
				<div class="home-content-right">
					<?php the_static_block($content_right); ?>
				</div>
				<?php 
				if (!empty($search_html) && $search_field == 'content-2') {
					echo  $search_html; // escaped above
				} 
				?>
			</div>
			<?php endif; ?>

			</div> <!-- /.row -->
		</div> <!-- /.container -->
	</section>
	<?php

} // End if has_content

