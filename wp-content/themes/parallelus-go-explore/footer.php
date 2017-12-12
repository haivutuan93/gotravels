<?php
/**
 * The template for displaying the footer.
 */
?>

	<?php do_action('output_layout','end'); // Layout Manager - End Layout ?> 


	<?php if ( !is_page_template( 'templates/cover.php' ) ) : ?>

	<footer id="footer">
		<?php

		// The content source set in theme options
		$content_1    = get_options_data('options-page', 'footer-content-1');
		$content_2    = get_options_data('options-page', 'footer-content-2');
		$column_size  = get_options_data('options-page', 'footer-column-size');

		// Section styles
		$styles = '';
		$container_style['background-color'] = get_options_data('options-page', 'footer-bg-color');
		$container_style['background-image'] = get_options_data('options-page', 'footer-bg-image');
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
		$footer_content = (!empty($content_left) || !empty($content_right)) ? true : false;

		if ( $footer_content ) { ?>

			<section class="top-footer regular" <?php echo  $styles; // escaped above ?>>
				<div class="container">
					<div class="row">

						<?php if (!empty($content_left)) : ?>
						<div class="<?php echo esc_attr($class_left); ?>">
							<div class="footer-content-left">
								<?php the_static_block($content_left); ?>
							</div>
						</div>
						<?php endif; ?>

						<?php if (!empty($content_right)) : ?>
						<div class="<?php echo esc_attr($class_right); ?>">
							<div class="footer-content-right">
								<?php the_static_block($content_right); ?>
							</div>
						</div>
						<?php endif; ?>

					</div>
				</div>
			</section>

		<?php } // $footer_content



		// The content source set in theme options
		$sub_content  = get_options_data('options-page', 'sub-footer-content');

		// Section styles
		$sub_styles = '';
		$sub_container_style['background-color'] = get_options_data('options-page', 'sub-footer-bg-color');
		$sub_container_style['background-image'] = get_options_data('options-page', 'sub-footer-bg-image');
		foreach ($sub_container_style as $attribute => $style) {
			if ( isset($style) && !empty($style) && $style !== '#') {
				if ($attribute == 'background-image') {
					$style = 'url('. $style .')';
				}
				$sub_styles .= $attribute .':'. $style .';';
			}
		}
		$sub_styles = (!empty($sub_styles)) ? 'style="'. esc_attr($sub_styles) .'"' : '';

		// Content check
		$has_sub_content = (!empty($sub_content) && $sub_content !== 'disabled') ? true : false;

		if ( $has_sub_content ) {  ?>

			<section class="sub-footer" <?php echo $sub_styles; // escaped above ?>>
				<div class="container">	
					<div class="row">
						<div class="col-xs-12">
							<?php 
							if (!empty($sub_content)) :
								the_static_block($sub_content);
							endif; 
							?>
						</div>
					</div>
				</div>				
			</section>

		<?php } ?>

	</footer>

	<?php endif; // !is_page_template( 'templates/cover.php' ) ?>

<?php wp_footer(); ?>

</body>
</html>