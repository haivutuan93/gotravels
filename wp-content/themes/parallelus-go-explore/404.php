<?php
/**
 * The template for displaying 404 pages (Not Found).
 */

get_header(); ?>

	<section class="error-container error-404 not-found">

	<?php 
	// Check for custom 404 page content
	$error_page = (get_options_data('options-page', 'error-content')) ? get_options_data('options-page', 'error-content') : 'default';

	if ($error_page == 'default') {
		?>
		<header class="page-header">
			<h2 class="page-title"><?php _e( 'Whaaaaat??!?!!1', 'framework' ); ?></h2>
			<p class="lead"><?php _e( "It seems the page you're looking for isn't here.", 'framework' ); ?></p>
		</header><!-- .page-header -->

		<div class="404-search-box">
			<p><?php _e( 'Try looking somewhere else and you might get lucky!', 'framework' ); ?></p>
			<?php get_search_form(); ?>
			<br>
			<br>
		</div><!-- /.404-search-box -->

		<?php
	} else {
		// Get the custom error page
		$errorContent = get_post($error_page);
		if (isset($errorContent) && !empty($errorContent)) {
			echo apply_filters( 'the_content', $errorContent->post_content );
		}
	} ?>

	</section>

<?php get_footer(); ?>