<?php if ( __FILE__ == $_SERVER['SCRIPT_FILENAME'] ) { die(); }


// Execute hooks before framework loads
do_action( 'functions_before' );


#-----------------------------------------------------------------
# Load framework
#-----------------------------------------------------------------
include_once get_template_directory() . '/framework/load.php';



// Execute hooks after framework loads
do_action( 'functions_after' ); ?><?php
/**
 * Theme registration and WP connections
 *
 */

/**
 * Toggle template directory and URI for Runway child/standalone themes
 *
 * These functions can be used to replace the defaults in WordPress so the correct path is
 * generated for both child themes and standalone. It will ensure the paths being referenced 
 * to your themes folder are always correct. 
 */
if (!function_exists('rf_get_template_directory_uri')) :
	function rf_get_template_directory_uri() {
		return (IS_CHILD) ? get_stylesheet_directory_uri() : get_template_directory_uri();
	}
endif;
if (!function_exists('rf_get_template_directory')) :
	function rf_get_template_directory() {
		return (IS_CHILD) ? get_stylesheet_directory() : get_template_directory();
	}
endif;


/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) )
	$content_width = 1240; /* pixels */


if ( ! function_exists( 'rf_theme_setup' ) ) :
/**
 * Set up theme defaults and register support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which runs
 * before the init hook. The init hook is too late for some features, such as indicating
 * support post thumbnails.
 */
function rf_theme_setup() {

	if ( function_exists( 'add_theme_support' ) ) {
	
		// WP Stuff
		add_editor_style(); // Admin editor styles
		add_theme_support( 'automatic-feed-links' ); // RSS feeds	
		// add_theme_support( 'post-formats', array( 'image', 'video' ) ); // Post formats. Unused: quote, link
		register_nav_menu( 'primary', __( 'Primary Menu', 'framework' ) ); // Main menu

		// Post thumbnails
		add_theme_support( 'post-thumbnails' );
		set_post_thumbnail_size( 1200, 9999 );

		// Additional image sizes
		add_image_size( 'blog', 600, 800, true ); // Blog: vertical 3:4 ratio, hard crop 
		add_image_size( 'blog-landscape', 800, 600, true ); // Blog: horizontal 4:3 ratio, hard crop 
		add_image_size( 'header', 1920, 1080 ); // Header background: 16:9 ratio
		add_image_size( 'place', 960, 540, true ); // Places in destinations: 16:9 ratio, hard crop

		// WooCommerce
		add_theme_support( 'woocommerce' );
		if(function_exists('is_woocommerce')) {
			remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
			remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
			add_action('woocommerce_before_main_content', 'theme_woocommerce_wrapper_start', 10);
			add_action('woocommerce_after_main_content', 'theme_woocommerce_wrapper_end', 10);
			add_action('woocommerce_add_to_cart_message', 'theme_woocommerce_add_to_cart_message', 10);
		}
	}

	// Translation
	load_theme_textdomain( 'framework', rf_get_template_directory() . '/languages' );

	// Navigation menus
	register_nav_menus( array(
		'primary'  => __( 'Main Menu', 'framework' ),
		'top-left'  => __( 'Top - Left', 'framework' ),
	) );

}
endif; // rf_theme_setup
add_action( 'after_setup_theme', 'rf_theme_setup' );

if(function_exists('is_woocommerce')):
	function theme_woocommerce_wrapper_start() {
	}		
	function theme_woocommerce_wrapper_end() {
	}
endif;

/**
 * Enqueue scripts and styles
 */
function rf_enqueue_scripts() {
	global $wp_scripts;

	// Load CSS
	wp_enqueue_style( 'owl-carousel', rf_get_template_directory_uri() . '/assets/css/owl-carousel.css', '2.0.0-beta.2.4' ); // carousel base CSS
	wp_enqueue_style( 'theme-bootstrap', rf_get_template_directory_uri() . '/assets/css/bootstrap.min.css' ); // can be changed to 'bootstrap.css' for testing.
	wp_enqueue_style( 'theme-style', get_stylesheet_uri() );

	// Load scripts
	wp_enqueue_script( 'theme-js', rf_get_template_directory_uri().'/assets/js/theme-scripts.js', array('jquery'), '1.0', true );
	wp_enqueue_script( 'theme-bootstrapjs', rf_get_template_directory_uri().'/assets/js/bootstrap.min.js', array('jquery'), '1.0', true );
	wp_enqueue_script( 'owl-carousel', rf_get_template_directory_uri().'/assets/js/owl.carousel.min.js', array('jquery'), '2.0.0-beta.2.4', true );
	wp_enqueue_script( 'fitvids', '//cdnjs.cloudflare.com/ajax/libs/fitvids/1.1.0/jquery.fitvids.min.js', array('jquery'), '1.1.0', true );

	// IE only JS
	wp_enqueue_script( 'theme-html5shiv', '//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.2/html5shiv-printshiv.min.js', '3.7.2' ); // Source: https://cdnjs.com/libraries/html5shiv
	$wp_scripts->add_data( 'theme-html5shiv', 'conditional', 'lt IE 9' );
	wp_enqueue_script( 'theme-respondjs', '//cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.min.js', '1.4.2' ); // Source: https://cdnjs.com/libraries/respond.js
	$wp_scripts->add_data( 'theme-respondjs', 'conditional', 'lt IE 9' );
    
    // IE10 viewport hack for Surface/desktop Windows 8 bug -->
	wp_enqueue_script( 'theme-ie10-viewport-bug', rf_get_template_directory_uri().'/assets/js/ie10-viewport-bug-workaround.js', '1.0.0', true );

	// Load comment reply ajax
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	// Load keyboard navigation for image template
	if ( is_singular() && wp_attachment_is_image() ) {
		wp_enqueue_script( 'theme-keyboard-image-nav', rf_get_template_directory_uri() . '/assets/js/keyboard-image-nav.js', array( 'jquery' ), '1.0.0' );
	}

	// Google Fonts
	$gFont = array();
	if (get_options_data('options-page', 'font-body') == 'google') {
		$gFont[] = get_options_data('options-page', 'font-body-google');
	}
	if (get_options_data('options-page', 'font-heading') == 'google') {
		$gFont[] = get_options_data('options-page', 'font-heading-google');
	}
	$gFontQuery = rf_google_fonts_query( $gFont );
	
	// Load Google Fonts
	if (!empty($gFontQuery)) {
		wp_enqueue_style( 'google-font', $gFontQuery, array(), null );
	}

}
add_action( 'wp_enqueue_scripts', 'rf_enqueue_scripts' );

// Override default google maps language
if(!function_exists('set_google_maps_lang')) {
	function set_google_maps_lang( $locale ) {
		//$locale = 'de_DE';
	    return $locale;
	}
}
add_filter('goexplore_google_maps_lang', 'set_google_maps_lang' );
