<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up to the start of the content output.
 *
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?> <?php if (function_exists( 'rf_html_cover_class' )) : rf_html_cover_class(); endif; ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php wp_title( '|', true, 'right' ); ?></title>
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<link rel="shortcut icon" href="<?php echo esc_url(get_options_data('options-page', 'favorites-icon')); ?>">
	<link rel="apple-touch-icon-precomposed" href="<?php echo esc_url(get_options_data('options-page', 'mobile-bookmark')); ?>">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php do_action( 'before' ); ?>

	<div id="top"></div>

	<!-- Navigation (main menu)
	================================================== -->
	<div class="navbar-wrapper">
		<header class="navbar navbar-default navbar-fixed-top" id="MainMenu" role="navigation">
			<div class="navbar-extra-top clearfix">
				<div class="navbar container-fluid">
					<?php 
						if (class_exists('wp_bootstrap_navwalker')) {
							// Main navbar (left)
							wp_nav_menu( array(
								'menu'              => 'top-left',
								'theme_location'    => 'top-left',
								'container'         => false,
								'menu_class'        => 'nav navbar-nav navbar-left',
								'fallback_cb'       => 'wp_bootstrap_navwalker::fallback',
								'walker'            => new wp_bootstrap_navwalker()
							));
						} else {
							_e('Please make sure the Bootstrap Navigation extension is active. Go to "Runway > Extensions" to activate.', 'framework');
						}
					?>
					<div class="navbar-top-right">
						<?php
						$nav_right = get_options_data('options-page', 'nav-top-right-source');
						if ($nav_right !== 'none') { 
							?>
							<ul class="nav navbar-nav navbar-right">
								<?php
								for ($i = 1; $i <= 5; $i++) {
									$icon_sym = get_options_data('options-page', 'nav-top-right-icon-'.$i);
									$icon_url = get_options_data('options-page', 'nav-top-right-icon-'.$i.'-url');

									if ($icon_sym !== 'none') {
										echo '<li><a href="'.esc_url($icon_url).'" target="_blank"><i class="fa fa-'.esc_attr($icon_sym).' fa-fw"></i></a></li>';
									}
								} ?>
							</ul>
							<?php
						}

						// Search in Navigation
						$nav_search = get_options_data('options-page', 'nav-search');
						if ($nav_search !== 'hide') { ?>
							<form class="navbar-form navbar-right navbar-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
								<div class="form-group">
									<input type="text" class="form-control" placeholder="<?php echo esc_attr__( 'Search...', 'framework' ); ?>" value="" name="s" title="<?php _e( 'Search for:', 'framework' ); ?>">
								</div>
								<button type="submit" class="btn btn-default"><span class="fa fa-search"></span></button>
							</form>
							<?php
						} ?>
					</div>
				</div>
			</div>
			
			<div class="container-fluid collapse-md" id="navbar-main-container">
				<div class="navbar-header">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home" class="navbar-brand">
						<?php 
						$logo_image = get_options_data('options-page', 'logo', '');
						$has_logo = false;
						if (!empty($logo_image)) {
							echo '<img src="'.$logo_image.'" alt="'.esc_attr(get_bloginfo('name', 'display')).'">';
							$has_logo = true;
						}
						$brand_title = get_options_data('options-page', 'brand-title', '');
						if (!empty($brand_title)) {
							if ($has_logo) {
								echo ' &nbsp;';
							}
							echo apply_filters('get_qtranslate_rw', esc_attr($brand_title));
						}
						?>
					</a>
					<button class="navbar-toggle" type="button" data-toggle="collapse" data-target="#navbar-main">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
				</div>
				
				<nav class="navbar-collapse collapse" id="navbar-main">
					<?php 
						if (class_exists('wp_bootstrap_navwalker')) {				
							// Main navbar (right)
							wp_nav_menu( array(
								'menu'              => 'primary',
								'theme_location'    => 'primary',
								'container'         => false,
								'menu_class'        => 'nav navbar-nav navbar-right',
								'fallback_cb'       => 'wp_bootstrap_navwalker::fallback',
								'walker'            => new wp_bootstrap_navwalker()
							));
						} else {
							_e('Please make sure the Bootstrap Navigation extension is active. Go to "Runway > Extensions" to activate.', 'framework');
						}
					?>
				</nav>
			</div><!-- /.container-fluid -->
		</header>
	</div><!-- /.navbar-wrapper -->


	<?php 
	// Header / Hero Section
	// ----------------------------------------------------------------------
	if ( function_exists( 'rf_theme_header' ) ) {
		// Get the custom header content
		rf_theme_header();
	} else { 
		// Can be generic content to prevent errors ?>
		<section class="hero no-header-content"></section>
	<?php } ?>


	<?php

	// Layout Manager Support - start layout here...
	// ----------------------------------------------------------------------
	/**
	 * We're also using the output_layout action to add a theme specific HTML container
	 * for all template files that do not explicitly state they have pre-defined elements
	 * the applying content containers.
	 */
	do_action('output_layout','start');  

	?>