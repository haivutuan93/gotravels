<?php
/*
Plugin Name: Travel Destinations
Plugin URI: http://para.llel.us
Description: Create travel guides, destinations and directories of related services.
Author: Parallelus
Author URI: http://para.llel.us
Version: 1.1.16
*/

if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
require_once( plugin_dir_path( __FILE__ ) . 'wp-list-tables.php' );
require_once( plugin_dir_path( __FILE__ ) . 'settings.php' );
require_once( plugin_dir_path( __FILE__ ) . 'directory.php' );
require_once( plugin_dir_path( __FILE__ ) . 'maps.php' );
require_once( plugin_dir_path( __FILE__ ) . 'common-functions.php' );

if( ! class_exists( 'Destination_CPT' ) ) {
	class Destination_CPT {

		private static $_this;

		public function __construct() {

		    self::$_this = $this;
			$this->init();
		}

		private function init() {

			$this->setup_constants();

			// Actions
			add_action( 'plugins_loaded', array( $this, 'load_languages' ), 11 );
			add_action( 'init', array( $this, 'init_settings' ), 99 );
			add_action( 'init', array( $this, 'register_post_type' ), 100 );
			add_action( 'init', array( $this, 'register_taxonomies' ), 101 );
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes_function' ) );
			add_action( 'pre_post_update', array( $this, 'pre_post_update_function' ), 10, 2 );
			add_action( 'save_post', array( $this, 'save_meta_box_data' ) );
			add_action( 'edit_form_after_title', array( $this, 'add_meta_box_after_title' ) );
			add_action( 'admin_menu', array( $this, 'change_destination_menu' ) );
			add_action( 'edit_form_top', array( $this, 'edit_form_top_func' ) );         // need to tabs output
			add_action( 'pre_get_posts', array( $this, 'sort_destinations_by_meta_value' ) );
			add_action( 'wp_footer', array( $this, 'language_switcher_fix' ) );
			add_action( 'wp_ajax_update_info_page_permalink', array( $this, 'update_nomaster_info_page_permalink' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_scripts' ) );

			// Filters
			add_filter( 'template_include', array( $this, 'para_destination_templates') );
			add_filter( 'request', array( $this, 'alter_the_query' ) );
			add_filter( 'wp_title', array( $this, 'page_name_wp_title' ), 10, 2 );
			add_filter( 'wp_link_query', array($this, 'wp_link_query_destination' ), 10, 2 );

			add_filter( 'oembed_discovery_links', array($this, 'oembed_discovery_links_rf' ), 10, 2 );
			add_filter( 'previous_post_rel_link', array( $this, 'previous_post_rel_link_rf' ) );
			add_filter( 'next_post_rel_link', array( $this, 'next_post_rel_link_rf' ) );

			add_filter( 'wp_get_nav_menu_items', array($this, 'fix_menu_url_info_pages'), 10, 3 );
			add_filter( 'preview_post_link', array($this, 'fix_preview_link' ) );
			// WP Init
			add_action( 'init', array( $this, 'load_scripts' ) );

			add_filter( 'post_type_link', array( $this, 'destination_page_link_filter' ), 10, 3 );

			add_filter( 'get_sample_permalink_html', array( $this, 'get_permalink_html' ), 10, 5 );

			// for Yoast SEO
			add_filter( 'wpseo_sitemap_post_type_archive_link', 'get_sitemap_post_type_archive_link', 100, 2 );

			// Settings
			$this->settings = get_destination_settings();

			// Create objects
			$this->master = new Travel_Master_Pages_CPT( $this->settings );
			$this->list = new Travel_Directory_CPT( $this->settings );
			$this->map = new Destination_Maps( $this->settings, false );

			// Compatibility
			$this->backward();

		}

		public function load_admin_scripts() {

			global $post;

			if ( is_object( $post ) && $post->post_type === 'destination-page' ) {
				$is_nomaster = get_master_parent( $post->ID ) ? false : true;

				wp_enqueue_script( 'destination-page-admin', TRAVEL_PLUGIN_URL . 'assets/js/destination-page-admin.js', array(
					'jquery'
				), '', true );
				wp_localize_script( 'destination-page-admin', 'destination_page_options', array(
					'is_nomaster' => $is_nomaster
				) );
			}

		}

		public function update_nomaster_info_page_permalink() {

			$post_id  = isset( $_REQUEST['post_id'] ) ? $_REQUEST['post_id'] : 0;
			$new_slug = isset( $_REQUEST['new_slug'] ) ? $_REQUEST['new_slug'] : '';

			if ( ! $post_id || strlen( str_replace( '-', '', $new_slug ) ) === 0 ) {
				echo get_sample_permalink_html( $post_id );
				wp_die();
			}

			$post    = get_post( $post_id );
			$dest_id = get_guide_page_parent( $post->ID );
			if ( ! $dest_id ) {
				echo get_sample_permalink_html( $post_id );
				wp_die();
			}

			$slugs       = get_guide_pages_slugs_new( $dest_id );
			$slugs_array = get_object_vars( $slugs );

			$dest      = get_post( $dest_id );
			$dest_slug = create_parent_dest_slug( $dest, true );
			$dest_name = create_parent_dest_slug( $dest, false );

			$post_slug   = $post->post_name;
			$parent_id   = $post->post_parent;
			$is_nomaster = get_master_parent( $post->ID ) ? false : true;

			if ( ! $is_nomaster ) {
				echo get_sample_permalink_html( $post_id );
				wp_die();
			}

			if ( $parent_id ) {
				// key
				$post_parent_slug = create_parent_slug_nomaster( $post, true );
				$key              = $post_parent_slug . '/' . $post_slug;
				$parent_key       = get_parent_slug( $post, true );
				// value
				$parts             = explode( '/', $slugs->$parent_key );
				$parent_front_slug = $parts[ count( $parts ) - 1 ];
				$post_front_slug   = $parent_front_slug . '-' . $new_slug;
			} else {
				// key
				$key = $post_slug;
				// value
				$post_front_slug = $new_slug;

				$icl_trid = get_trid_by_ID( $post->ID );
				if ( $icl_trid ) {
					$icl_parent_front_slug_for_new_post = get_icl_parent_front_slug_for_new_post( $icl_trid, $dest_name );
					if ( ! empty ( $icl_parent_front_slug_for_new_post ) ) {
						$post_front_slug = implode( '', explode( $icl_parent_front_slug_for_new_post . '-', $post_front_slug, 2 ) );
						$post_front_slug = $icl_parent_front_slug_for_new_post . '-' . $post_front_slug;
					}
				}
			}

			$slug = $dest_slug . '/' . $post_front_slug;
			//check if slug already exists
			$unallowed_slug = false;
			foreach ( $slugs_array as $slugs_array_key => $value ) {
				if ( $value === $slug ) {
					if ( $slugs_array_key !== $key ) {
						$unallowed_slug = true;
					}
				}
			}

			if ( ! $unallowed_slug ) {
				$old_slug    = $slugs->$key;
				$slugs->$key = $slug;

				// update children
				foreach ( $slugs_array as $slugs_array_key => $value ) {
					if ( strpos( $slugs_array_key, $key ) !== false ) {
						$slugs->$slugs_array_key = str_replace( $old_slug, $slug, $value );
					}
				}

				update_post_meta( $dest_id, 'pages_cpt_slug', json_encode( $slugs ) );
			}

			$permalink_html = get_sample_permalink_html( $post_id );

			if ( $unallowed_slug ) {
				$permalink_html .= '<div class="error-slug">' . sprintf( __( 'The slug <code>%s</code> already exists. Please try other value.', 'destinations' ), $new_slug ) . '</div>';
			}

			echo $permalink_html;

			wp_die();

		}

		public function get_permalink_html( $return, $post_id, $new_title, $new_slug, $post ) {

			if ( $post->post_type === 'destination-page' && $post->post_status === 'publish' ) {
				$no_master = ( is_object( $post ) ) ? get_guide_page_no_master( $post->ID ) : false;
				if ( $no_master ) {

					$dest_id   = get_guide_page_parent( $post->ID );
					$slugs     = get_guide_pages_slugs_new( $dest_id );
					$post_name = $post->post_name;
					$parent_id = $post->post_parent;

					if ( $parent_id ) {
						$parent_key = get_parent_slug( $post, true );

						if ( ! property_exists( $slugs, $parent_key ) ) {
							return $return;
						}
						$parts             = explode( '/', $slugs->$parent_key );
						$parent_front_slug = $parts[ count( $parts ) - 1 ] . '-';

						$complex_key     = $parent_key . '/' . $post_name;
						if ( ! property_exists( $slugs, $complex_key ) ) {
							return $return;
						}
						$parts           = explode( '/', $slugs->$complex_key );
						$post_front_slug = $parts[ count( $parts ) - 1 ];
						// replace first match
						$slug = implode('', explode($parent_front_slug, $post_front_slug, 2));
					} else {
						if ( ! property_exists( $slugs, $post_name ) ) {
							return $return;
						}
						$parts           = explode( '/', $slugs->$post_name );
						$post_front_slug = $parts[ count( $parts ) - 1 ];
						$slug            = $post_front_slug;
					}

					$view_link      = get_permalink( $post );
					$preview_target = '';

					list( $permalink, $post_name ) = get_sample_permalink( $post->ID, $new_title, $new_slug );

					$decoded_permalink = urldecode( $permalink );
					$post_name_html    = '<span id="editable-post-name">' . $slug . '</span>';
					$display_link      = str_replace( $slug, $post_name_html, $decoded_permalink );
					$return            = '<strong>' . __( 'Permalink:' ) . "</strong>\n";
					$return .= '<span id="sample-permalink"><a href="' . esc_url( $view_link ) . '"' . $preview_target . '>' . $display_link . "</a></span>\n";
					$return .= '&lrm;';
					$return .= '<span id="edit-slug-buttons"><button type="button" class="edit-slug button button-small hide-if-no-js" aria-label="' . __( 'Edit permalink' ) . '">' . __( 'Edit' ) . "</button></span>\n";
					$return .= '<span id="editable-post-name-full">' . $slug . "</span>\n";

				}
			}

			return $return;

		}

		public function destination_page_link_filter( $post_link, $post, $leavename = null, $sample = null ) {

			if ( $post->post_type === 'destination-page' && $post->post_status === 'publish' ) {
				static $in_process = 0;

				if ( ! $in_process ) {
					try {
						$in_process = $post->ID;
						$dest_ID    = get_guide_page_parent( $post->ID );
						if ( get_post_status( $dest_ID ) === false ) {
							// if destination doesn't exist
							$post_link = '';
						} else {
							$post_link = get_destination_page_link_by_dest_id( $post->ID );
						}
						$in_process = 0;
					} catch ( Exception $e ) {
						$in_process = 0;
					}
				}
			}

			return $post_link;

		}

		static function this() {
    		return self::$_this;
  		}

		function load_scripts() {

			// JS
			// wp_enqueue_script( 'some-scripts', TRAVEL_PLUGIN_URL . 'assets/js/some-file.js', array('jquery'), '', true);

			// CSS
			wp_enqueue_style( 'destinations-icons', TRAVEL_PLUGIN_URL . 'assets/css/destinations-font.css');
		}

		function backward() {
			global $wpdb;

			$query = "SELECT $wpdb->terms.term_id FROM $wpdb->terms WHERE $wpdb->terms.slug = 'places'";
			$oldterms = $wpdb->get_results( $query, OBJECT );
			foreach( $oldterms as $oldterm ) {
				$query = "UPDATE $wpdb->terms SET $wpdb->terms.name = 'Places' WHERE $wpdb->terms.term_id = ".$oldterm->term_id;
				$wpdb->query( $query );
			}
			$query = "SELECT $wpdb->terms.term_id FROM $wpdb->terms WHERE $wpdb->terms.slug = 'articles'";
			$oldterms = $wpdb->get_results( $query, OBJECT );
			foreach( $oldterms as $oldterm ) {
				$query = "UPDATE $wpdb->terms SET $wpdb->terms.name = 'Articles' WHERE $wpdb->terms.term_id = ".$oldterm->term_id;
				$wpdb->query( $query );
			}

			// fix destination pages
			if ( get_option( 'incorrect_destination_pages_fixed', '' ) == false ) {
				add_action( 'wp_loaded', array( $this, 'fix_destination_info_page_permalinks' ) );
			}
		}

		function fix_destination_info_page_permalinks() {
			set_time_limit(300);

			update_option( 'incorrect_destination_pages_fixed', true );

			$query = array(
				'post_type'      => 'destination-page',
				'post_parent' 	 => 0,
				'posts_per_page' => -1
			);

			$children_args = array(
				'numberposts' 	=> -1,
				'post_parent' 	=> get_the_ID(),
				'meta_key' 		=> 'guide_page_order',
				'orderby' 		=> 'meta_value_num',
				'order' 		=> 'ASC',
				'post_type' 	=> 'destination-page'
			);

			$guide_page_query = new WP_Query( $query );
			while ( $guide_page_query->have_posts() ) {
				global $post;
				$guide_page_query->the_post();

				fix_incorrect_info_page_permalinks( $post );

				$children = get_children( $children_args );
				if( count( $children ) > 0 ) {
					foreach( $children as $child ) {
						fix_incorrect_info_page_permalinks( $child );
					}
				}
			}
			wp_reset_postdata();
		}

		function sort_destinations_by_meta_value( $query ) {
			global $pagenow;

			if( ! is_admin() && is_search() ) {
				$meta_query = array(
					array(
						'key' => 'is_disabled_master_page',
						'compare' => 'NOT EXISTS'
					),
					array(
						'key' => 'is_disabled_guide_page',
						'compare' => 'NOT EXISTS'
					)
				);
				$query->set( 'meta_query', $meta_query );
			}

			if ( is_admin() && $pagenow == 'edit.php' && $query->query_vars['post_type'] == 'destination' ) {
				if( isset( $query->query_vars['post_status'] ) && ( $query->query_vars['post_status'] == 'draft' || $query->query_vars['post_status'] == 'trash') ) ;
				else {
					$query->set( 'meta_key', 'destination_order' );
					$query->set( 'orderby', 'meta_value_num' );
					$query->set( 'order', 'ASC' );
				}
			}
		}

		private function setup_constants() {

			// Plugin version
			if ( ! defined( 'TRAVEL_VERSION' ) ) {
				define( 'TRAVEL_VERSION', '1.0.0' );
			}

			// Plugin Folder Path
			if ( ! defined( 'TRAVEL_PLUGIN_DIR' ) ) {
				define( 'TRAVEL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}

			// Plugin Folder URL
			if ( ! defined( 'TRAVEL_PLUGIN_URL' ) ) {
				define( 'TRAVEL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			}

			// Plugin Root File
			if ( ! defined( 'TRAVEL_PLUGIN_FILE' ) ) {
				define( 'TRAVEL_PLUGIN_FILE', __FILE__ );
			}

			// Plugin Debug
			if ( ! defined( 'TRAVEL_PLUGIN_DEBUG' ) ) {
				define( 'TRAVEL_PLUGIN_DEBUG', 0 );
			}
		}

		/**
		 * Load our language files
		 *
		 * @access public
		 * @return void
		 */
		public function load_languages() {
			// Set unique textdomain string
			$textdomain = 'destinations';

			// The 'plugin_locale' filter is also used by default in load_plugin_textdomain()
			$locale = apply_filters( 'plugin_locale', get_locale(), $textdomain );

			// Set filter for WordPress languages directory
			$wp_lang_dir = apply_filters( 'destinations_wp_lang_dir', WP_LANG_DIR . '/destinations/' . $textdomain . '-' . $locale . '.mo' );

			// Translations: First, look in WordPress' "languages" folder
			load_textdomain( $textdomain, $wp_lang_dir );

			// Translations: Next, look in plugin's "lang" folder (default)
			$plugin_dir = basename( dirname( __FILE__ ) );
			$lang_dir = apply_filters( 'destinations_lang_dir', $plugin_dir . '/languages/' );
			load_plugin_textdomain( $textdomain, FALSE, $lang_dir );
		}

		function get_origin_page( $request ) {
			global $wpdb;

			$query = "SELECT $wpdb->postmeta.post_id,$wpdb->postmeta.meta_value FROM $wpdb->postmeta WHERE $wpdb->postmeta.meta_value LIKE '%" . $request . "%'";
			$posts_by_meta = $wpdb->get_results( $query, OBJECT );

			$idx = 0;
			if( defined( 'ICL_LANGUAGE_CODE' ) ) {
				global $sitepress;
				remove_filter( 'parse_query', array( $sitepress, 'parse_query' ) );

				foreach( $posts_by_meta as $key => $meta ) {
					$query = "SELECT language_code FROM $wpdb->prefix"."icl_translations WHERE element_id = " . $meta->post_id . " AND element_type = 'post_destination'";
					$post_lang = $wpdb->get_results( $query, OBJECT );

					if( isset( $post_lang[0] ) )
						$post_lang_code = $post_lang[0]->language_code;
					if( $post_lang_code == ICL_LANGUAGE_CODE ) {
						$idx = $key;
						break;
					}
				}
			}

			$meta_value = ( isset( $posts_by_meta[$idx] ) ) ? json_decode( $posts_by_meta[$idx]->meta_value, true ) : array();
			return array_search( $request, (array) $meta_value );
		}

		function alter_the_query( $request ) {
			global $paged_cust;

			if( is_admin() )
				return $request;
			if( count($request) == 1 && isset( $request['attachment'] ) ) {
				unset($request['attachment']);
				$request['error'] = 404;
			}

			if( isset( $request['destinations'] ) ) {
			}

			if( isset( $request['travel-dir-category'] ) )
				unset( $request['paged'] );
			if( ( isset($request['post_type'] ) && ( $request['post_type'] == 'destination' ) ) && isset( $request['paged'] ) ) { // destination archive
				$paged_cust = $request['paged'];
				unset( $request['paged'] );
			}

			if( isset( $request['post_type'] ) && $request['post_type'] == 'destination-page' ) {
				$dummy_query = new WP_Query();  // the query isn't run if we don't pass any query vars
				$dummy_query->parse_query( $request );
				if ( isset( $request['destination-page'] ) ) {
					$page_base = ( ! isset( $this->settings['page_base'] ) || empty( $this->settings['page_base'] ) ) ? '' : $this->settings['page_base'];
//					$request['destination-page'] = !empty($page_base)? str_replace($page_base.'/', '', $request['destination-page']) : $request['destination-page'];
//					$request['destination-page'] = $this->get_origin_page($request['destination-page']);
					$request1 = ! empty( $page_base ) ? str_replace( $page_base . '/', '', $request['destination-page'] ) : $request['destination-page'];
					$request1 = $this->get_origin_page( $request1 );
					$request['destination-page'] = $request1;
					$request['name'] = $request1;
				}
			}

			return $request;
		}

		public function init_settings() {
  			remove_action( 'wp_head', 'rel_canonical' );
			add_action( 'wp_head', array( $this, 'rel_canonical_rf' ) );	

			// Settings
			$this->settings = get_destination_settings();          // if use WPML set up settings for the current language

			$this->master->init_settings( $this->settings );
			$this->list->init_settings( $this->settings );

			if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
				global $sitepress;
				$seo_settings = $sitepress->get_setting( 'seo', array() );

				if ( array_key_exists( 'head_langs', $seo_settings ) && $seo_settings['head_langs'] ) {
					remove_action( 'wp_head', array( $sitepress, 'head_langs' ) );
					add_action( 'wp_head', array( $this, 'head_langs_rf' ) );
					add_filter( 'wpml_hreflangs_html', '__return_empty_string' ); // for WPML v~3.4
				}

				$langs = icl_get_languages( 'skip_missing=0' );
				if( is_array( $langs ) ) {
					foreach( $langs as $lang ) {
						$options = get_option( get_travel_guide_option_key( 'travel_guide_options', $lang['code'] ) );
						$this->settings_lang[$lang['code']] = $options ? json_decode( $options, true ) : array();
					}
				}
			}

		}

		public function register_post_type() {
			$rewrite_slug = ( isset( $this->settings['destinations_base'] ) && ! empty( $this->settings['destinations_base'] ) )? $this->settings['destinations_base'] : 'destination';

			$labels = array(
				'name' 				=> _x( 'Destinations', 'post type general name', 'destinations' ),
				'singular_name' 	=> _x( 'Destination', 'post type singular name', 'destinations' ),
				'add_new' 			=> __( 'Add New', 'destinations' ),
				'add_new_item' 		=> __( 'Add New Destination', 'destinations' ),
				'edit_item' 		=> __( 'Edit Destination', 'destinations' ),
				'new_item' 			=> __( 'New Destination', 'destinations' ),
				'all_items' 		=> __( 'All Destinations', 'destinations' ),
				'view_item' 		=> __( 'View Destination', 'destinations' ),
				'search_items' 		=> __( 'Search Destination', 'destinations' ),
				'not_found' 		=> __( 'No Destination found', 'destinations' ),
				'not_found_in_trash'=> __( 'No Destination found in Trash', 'destinations' ),
				'parent_item_colon' => '',
				'menu_name' 		=> __( 'Destinations', 'destinations' )
			);

			$args = array(
				'labels'              => $labels,
				'public'              => true,
				'has_archive'         => true,
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'query_var'           => true,//$rewrite_slug,
				'capability_type'     => 'post',
				'hierarchical'        => true,
				'menu_icon'           => 'dashicons-location-alt',
				'menu_position'       => null,
				'rewrite'             => array( 'slug' => $rewrite_slug /*, 'hierarchical' => false, 'with_front' => true*/ ),
				'supports'            => array( 'title', 'editor', 'thumbnail' )
			);

			register_post_type( 'destination', $args );
			$this->register_post_type_guide_page( 'destination-page' );

		}

		public function register_taxonomies() {

			$rewrite_slug = ( isset( $this->settings['places_articles_base'] ) && ! empty( $this->settings['places_articles_base'] ) )? $this->settings['places_articles_base'] : 'destinations';

			$labels = array();
			register_taxonomy(
				'destinations',
				'destination',
				array(
					'hierarchical' 	=> true,
					'show_ui' 		=> false,
					'query_var' 	=> $rewrite_slug,
					'rewrite' 		=> array( 'slug' => $rewrite_slug, 'hierarchical' => true, 'with_front'=> false )
				)
			);

			$this->add_destinations_terms();  // places and articles

			$labels = array(
				'name' 							=> __( 'Categories', 'destinations' ),
				'singular_name' 				=> __( 'Category', 'destinations' ),
				'search_items' 					=> __( 'Search Categories', 'destinations' ),
				'popular_items' 				=> __( 'Popular Categories', 'destinations' ),
				'all_items' 					=> __( 'All Categories', 'destinations' ),
				'edit_item' 					=> __( 'Edit Category', 'destinations' ),
				'update_item' 					=> __( 'Update Category', 'destinations' ),
				'add_new_item' 					=> __( 'Add New Category', 'destinations' ),
				'new_item_name' 				=> __( 'New Category Name', 'destinations' ),
				'separate_items_with_commas'	=> __( 'Separate categories with commas', 'destinations' ),
				'add_or_remove_items'			=> __( 'Add or remove categories', 'destinations' ),
				'choose_from_most_used' 		=> __( 'Choose from the most frequent Categories', 'destinations' ),
			);

			register_taxonomy(
				'travel-category',
				'destination',
				array(
					'hierarchical' 	=> true,
					'labels' 		=> $labels,
					'show_ui' 		=> true,
					'query_var' 	=> true,
					'rewrite' 		=> array( 'slug' => '','hierarchical' => true,  'with_front'=> true )
				)
			);
		}

		public function register_post_type_guide_page( $cpt ) {

			$title = __( 'Destination Pages', 'destinations' );

			$slug = ( isset( $this->settings['page_base'] ) && ! empty( $this->settings['page_base'] ) ) ? $this->settings['page_base'] : 'information';

			$labels = array(
				'name' 				=> _x( $title, 'post type general name', 'destinations' ),
				'singular_name' 	=> _x( $title, 'post type singular name', 'destinations' ),
				'add_new' 			=> __( 'Add New', 'destinations' ),
				'add_new_item' 		=> __( 'Add New', 'destinations' ).' '.$title,
				'edit_item' 		=> __( 'Edit', 'destinations' ).' '.$title,
				'new_item' 			=> __( 'New', 'destinations' ).' '.$title,
				'all_items' 		=> __( 'All', 'destinations' ).' '.$title,
				'view_item' 		=> __( 'View', 'destinations' ).' '.$title,
				'parent_item_colon' => '',
				'menu_name' 		=> __( $title, 'destinations' )
			);

			$args = array(
				'labels'              => $labels,
				'public'              => true,
				'has_archive'         => true,
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'query_var'           => true,
				'capability_type'     => 'post',
				'hierarchical'        => true,
				'menu_position'       => null,
				'show_in_admin_bar'   => false,
				// 'show_in_menu'        => 'edit.php?post_type=destination',
				//'rewrite'             => array('slug' => 'information', 'hierarchical' => false, 'with_front'=> false),
				'rewrite'             => array( 'slug' => $slug, 'hierarchical' => false, 'with_front'=> false ),
				'supports'            => array( 'title', 'editor', 'thumbnail' )
			);

			register_post_type( $cpt, $args );
		}

		public function add_destinations_terms() {
			if( ! term_exists( 'Places', 'destinations' ) ) 
				wp_insert_term( 'Places', 'destinations', $args = array() );

			if( ! term_exists( 'Articles', 'destinations' ) )
				wp_insert_term( 'Articles', 'destinations', $args = array() );


			if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
			 	$term_origin = get_term_by( 'slug', 'Places', 'destinations' );

			 	$wpml_element_type = apply_filters( 'wpml_element_type', 'destinations' );
				$default_language = apply_filters( 'wpml_default_language', NULL );

				$args = array( 'element_id' => $term_origin->term_taxonomy_id, 'element_type' => $wpml_element_type );
				$info_origin = apply_filters( 'wpml_element_language_details', null, $args );

				$langs = icl_get_languages( 'skip_missing=0' );
				if( is_array( $langs ) ) {
					foreach( $langs as $lang ) {
						if( $default_language == $lang['code'] )
							continue;
						if( ! term_exists( 'Places-'.$lang['code'], 'destinations' ) ) {
							$term = wp_insert_term( 'Places-'.$lang['code'], 'destinations', $args = array( 'slug' => 'places-'.$lang['code'] ) );
						  	$set_language_args = array(
						            'element_id'    		=> $term['term_taxonomy_id'],
						            'element_type'  		=> $wpml_element_type,
						            'trid'  				=> $info_origin->trid,
						            'language_code'   		=> $lang['code'],
						            'source_language_code' 	=> $info_origin->language_code
						        );
						  	do_action( 'wpml_set_element_language_details', $set_language_args );
						}
					}
				}
			}
		}

		function fix_menu_url_info_pages( $items, $menu, $args ) {
			foreach( $items as $key => $item ) {
				if( get_post_type( $item->object_id ) == 'destination-page' ) {
					$page_id = $item->object_id;
					$dest_ID = get_guide_page_parent( $page_id );
					if( defined( 'ICL_LANGUAGE_CODE' ) ) {
						$dest_ID = (int) apply_filters( 'wpml_object_id', $dest_ID, 'destination', false, ICL_LANGUAGE_CODE );
						$pages = get_destination_pages( $dest_ID, 'list', ICL_LANGUAGE_CODE );
					 	$trid = get_trid_by_ID( $item->object_id );
					 	$page_id = get_destination_page_id_by_trid( $trid, ICL_LANGUAGE_CODE );
					}
					if( isset( $pages[$page_id]['link'] ) )
						$items[$key]->url = $pages[$page_id]['link'];
				}
			}

			return $items;
		}

		function fix_preview_link( $link ) {
			global $post;

			if(is_admin() && isset($post) && $post->post_type == 'destination-page') {
				$dest_ID = get_guide_page_parent( $post->ID );
				$pages = get_destination_pages( $dest_ID );
				if( isset( $pages[$post->ID]['link'] ) )
					$link = $pages[$post->ID]['link'];
			}

			return $link;
		}

		function language_switcher_fix() {
			if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
				if( ! wp_script_is( 'jquery-ui-datepicker' ) )
					wp_enqueue_script( 'jquery-ui-datepicker' );

				if( is_array( $this->settings_lang ) ) {
					foreach( $this->settings_lang as $key => $val ) {
						echo '<input class="lang-alt-switcher-footer lang-alt-switcher-footer-'.$key.'" type="hidden" value="'.$key.'"/>';
					}
				}
			
				global $post, $sitepress;
				$default_language = apply_filters( 'wpml_default_language', NULL );
				$current_language = apply_filters( 'wpml_current_language', NULL );

				$dest_ID = get_the_destination_ID();
				$dest = get_post( $dest_ID );

				$langs = array();
				$langs[$current_language] = $this->settings_lang[$current_language];
				foreach( $this->settings_lang as $key => $val ) {
					if( $key != $current_language )
						$langs[$key] = $val;
				}

				foreach( $langs as $key => $val ) {
					//if($key != $current_language) {
						$lang = $key;

						$destID_alt_switcher = (int) apply_filters( 'wpml_object_id', $dest_ID, 'destination', false, $lang );
						$dest_alt_switcher = get_post( $destID_alt_switcher );

						if( is_single() && $post->post_type == 'destination' ) {
							$base_lang = empty($val['destinations_base'])? 'destination' : $val['destinations_base'];
							$base_current = empty($this->settings['destinations_base'])? 'destination' : $this->settings['destinations_base'];
							$dest_slug = create_parent_dest_slug( $dest_alt_switcher, true );
							$link_alt = get_final_permalink( 'destination', $dest_slug, $base_current, $base_lang, $lang );

							echo '<input class="lang-alt-switcher" id="lang-alt-switcher-'.$lang.'" data-alt="'.$lang.'" type="hidden" value="'.$link_alt.'"/>'; 
						}

 						if( is_tax( 'destinations' ) ) {
							remove_filter( 'get_term', array( $sitepress, 'get_term_adjust_id' ), 1 );
							$term_link = get_destination_taxonomy_term_links( 'places', $dest_alt_switcher->post_name, 'destinations', '', $lang );
							$term_link = apply_filters( 'wpml_permalink', $term_link, $lang );
							add_filter( 'get_term', array( $sitepress, 'get_term_adjust_id' ), 1, 1 );

							$need = false;
							if( $destID_alt_switcher ) {
								$args = array(
									'post_parent' => $destID_alt_switcher,
									'post_type'   => 'destination', 
									'numberposts' => -1,
									'post_status' => 'publish' 
								);
								$places = get_children( $args );
								$need = count( $places ) ? true : false;
							}

							if( ! $need )
								echo '<input type="hidden" class="lang-alt-switcher lang-alt-switcher-del" id="lang-alt-switcher-'.$lang.'" data-alt="'.$lang.'" />';
							else
								echo '<input class="lang-alt-switcher" id="lang-alt-switcher-'.$lang.'" data-alt="'.$lang.'" type="hidden" value="'.$term_link.'"/>';
 						}

						if( is_archive() && isset( $post->post_type ) && $post->post_type == 'destination' ) {
							$base_lang = empty( $val['destinations_base'] ) ? 'destination' : $val['destinations_base'];
							$base_current = empty( $this->settings['destinations_base'] ) ? 'destination' : $this->settings['destinations_base'];
							$link_alt = get_post_type_archive_link( 'destination' );
							$link_alt = str_replace( '/'.$base_current.'/', '/'.$base_lang.'/', $link_alt );
							$link_alt = apply_filters( 'wpml_permalink', $link_alt, $lang );

							echo '<input class="lang-alt-switcher" id="lang-alt-switcher-'.$lang.'" data-alt="'.$lang.'" type="hidden" value="'.$link_alt.'"/>'; 
						}

						if( is_single() && $post->post_type == 'destination-page' ) {
							$trid = get_trid_by_ID( $post->ID );  			// use if for Destination Page option Make 'Destination Pages' translatable is not checked
							$pageID_alt_switcher = get_destination_page_id_by_trid( $trid, $lang );

							if( ! $pageID_alt_switcher )
								continue;
							$destID_alt_switcher = (int) get_guide_page_parent( apply_filters( 'wpml_object_id', $pageID_alt_switcher, 'destination-page', false, $lang ) );
							$all_guide_pages = get_destination_pages( $destID_alt_switcher, 'list', $lang );

							$link_alt = isset( $all_guide_pages[$pageID_alt_switcher]['link'] ) ? $all_guide_pages[$pageID_alt_switcher]['link'] : '';

							if( is_guide_page_disabled( $pageID_alt_switcher ) ) {
								echo '<input type="hidden" class="lang-alt-switcher lang-alt-switcher-del" id="lang-alt-switcher-'.$lang.'" data-alt="'.$lang.'" />';
							}
							if( ! empty( $link_alt ) )
								echo '<input class="lang-alt-switcher lang-alt-switcher-new" id="lang-alt-switcher-'.$lang.'" data-alt="'.$lang.'" type="hidden" value="'.$link_alt.'" />';
						}

						if( is_single() && $post->post_type == 'travel-directory' ) {
							$base_lang = empty( $val['directory_item_base'] ) ? 'travel-directory' : $val['directory_item_base'];
							$base_current = empty( $this->settings['directory_item_base'] ) ? 'travel-directory' : $this->settings['directory_item_base'];

							$directory_item_id_lang = (int) apply_filters( 'wpml_object_id', $post->ID, 'travel-directory', false, $lang );
							$directory_item_lang = get_post( $directory_item_id_lang );

							$link = get_permalink( $directory_item_id_lang );
							$link = str_replace( '/'.$post->post_name.'/', '/'.$directory_item_lang->post_name.'/', $link );
							$link_alt = str_replace( '/'.$base_current.'/', '/'.$base_lang.'/', $link );
							$link_alt = apply_filters( 'wpml_permalink', $link_alt, $lang );

							echo '<input class="lang-alt-switcher" id="lang-alt-switcher-'.$lang.'" data-alt="'.$lang.'" type="hidden" value="'.$link_alt.'"/>';
						}

						if( is_tax( 'travel-dir-category' ) ) {
							$base_lang = empty( $val['guide_list_base'] ) ? 'listings' : $val['guide_list_base'];
							$base_current = empty( $this->settings['guide_list_base'] ) ? 'listings' : $this->settings['guide_list_base'];

							$term = get_guide_term_id();
							$term_id_lang = (int) apply_filters( 'wpml_object_id', $term->term_id, 'travel-dir-category', false, $lang );
							if( ! $term_id_lang ) {
								continue;
							}

							remove_filter( 'get_term', array( $sitepress, 'get_term_adjust_id' ), 1 );
		 					$term_lang = get_term_by( 'id', $term_id_lang, 'travel-dir-category' );

							if( ! $term_lang->count ) {
								echo '<input type="hidden" class="lang-alt-switcher lang-alt-switcher-del" id="lang-alt-switcher-'.$lang.'" data-alt="'.$lang.'" />';
								continue;
							}

							$term_link = get_destination_taxonomy_term_links( $term_lang->slug, $dest_alt_switcher->post_name, 'travel-dir-category', '', $lang );
							add_filter( 'get_term', array( $sitepress, 'get_term_adjust_id' ), 1, 1 );
							$term_link_alt = str_replace('/'.$base_current.'/', '/'.$base_lang.'/', $term_link);

							echo '<input class="lang-alt-switcher" id="lang-alt-switcher-'.$lang.'" data-alt="'.$lang.'" type="hidden" value="'.$term_link_alt.'"/>'; 
						}	
					//}
				}
			}
		}

		function para_destination_templates( $original_template ) {
			if( is_admin() )
				return $items;

			$post_id = get_the_ID();

			$cpt = get_post_type( $post_id );

			if ( defined( 'ICL_LANGUAGE_CODE' ) && is_single() ) {
				global $wp;
				$request = explode( '/', $wp->request );
				$lang_info = wpml_get_language_information( $post_id );
				$options = get_option( get_travel_guide_option_key( 'travel_guide_options', $lang_info['language_code'] ) );
				$settings_lang = $options ? json_decode( $options, true ) : array();
				if ( $cpt == 'destination' && ! is_preview() ) {
					if( ( $request[0] != 'destination' ) && ( $settings_lang['destinations_base'] != $request[0] ) ) {
						global $wp_query;
    					$wp_query->set_404();
						header( "HTTP/1.0 404 Not Found" );
						return get_404_template();
					}
				}
			}

			if ( $cpt == 'destination-page' ) {

				if ( is_archive() ) {
					header( "HTTP/1.0 404 Not Found" );
					return get_404_template();
				}

				if ( is_guide_page_disabled( $post_id ) ) {

					// return get_stylesheet_directory().'/404.php';
					return get_404_template();
				}
			}

			if( isset( $file_name ) ) {
				$plugin_template = TRAVEL_PLUGIN_DIR . 'templates/' . $file_name;
				$theme_template = locate_template(
					array(
						$file_name,
						'destinations/' . $file_name
					));
			}

			// Check for this CPT
			if ( in_array( $cpt, array() ) && is_single() ) {
				// Template file selection
				if ( $theme_template ) {
					// Custom theme provided template
					return $theme_template;
				} elseif ( file_exists( $plugin_template ) ) {
					// Default plugin template
					return $plugin_template;
				} else {
					// Fallback to WP if no template found
					return $original_template;
				}

			} else {
				return $original_template;
			}
		}

		function edit_form_top_func( $post ) {
			$screen = get_current_screen();
			if( 'destination' == $screen->post_type && $screen->action != 'add' ) {
				$tab = ( isset( $_REQUEST['tab'] ) && ! empty( $_REQUEST['tab'] ) ) ? $_REQUEST['tab'] : 'destination';
				$this->display_destination_tabs( $tab );
			}

			if( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'destination-page'/* && isset($_GET['parent']) && isset($_GET['gui'])*/) { ?>
				<script type="text/javascript">
				 jQuery(function($) {
					$('div#edit-slug-box').hide();
				 });
				</script> <?php
			}

			if( $post->post_type == 'destination-page') {
				$no_master = ( is_object( $post ) ) ? get_guide_page_no_master( $post->ID ) : false;
				if (! $no_master || $post->post_status !== 'publish' ) {
					?>
					<script type="text/javascript">
						jQuery(function ($) {

							$('div#edit-slug-box strong').first().hide();
							$('div#edit-slug-box span#sample-permalink').hide();
							$('div#edit-slug-box span#edit-slug-buttons').hide();

						});
					</script>
					<?php
				}
			}

		}

		function change_destination_menu() {
			global $menu, $submenu;

			foreach ( $menu as $mkey => $m ) {
				if( strstr($m[2], 'edit.php?post_type=destination-page') !== false || $m[2] == 'edit.php?post_type=master-pages' ) {
					remove_menu_page( $m[2] );
				}
			}
			add_submenu_page( 'edit.php?post_type=destination', __('Settings', 'destinations' ), __('Settings', 'destinations' ), 'manage_options', 'destination-settings', array( $this, 'settings_page') );
			add_submenu_page( 'edit.php?post_type=destination', 'Guide-Pages', 'Guide-Pages', 'edit_posts', 'guide-pages', array( $this, 'guide_pages') );
			add_submenu_page( 'edit.php?post_type=destination', 'Travel-Directory', 'Travel-Directory', 'manage_options', 'travel-directory', array( $this, 'travel_directory') );

			$key = array();
			foreach ( $submenu['edit.php?post_type=destination'] as $mkey => $m ) {
				if( $m[0] == 'Guide-Pages' )
				   $key[] = $mkey;
				if( $m[0] == 'Travel-Directory' )
				   $key[] = $mkey;
			}
			foreach( $key as $k )
				unset( $submenu['edit.php?post_type=destination'][$k] );
		}

		function get_children_pages( $children, $data_table, $level, $parent_id ) {
			if( count( $children ) > 0 ) {
				foreach( $children as $child ) {
					$dest_page = $this->get_exists_guide_page( $child->ID );
					if( ! $dest_page ) {
						if( defined( 'ICL_LANGUAGE_CODE' ) ) {
							if( apply_filters( 'wpml_current_language', NULL ) != apply_filters( 'wpml_default_language', NULL ) )
								continue;
						}						
						$guide_data = array(
						  'post_title'    => $child->post_title,
						  'post_status'   => 'publish',
						  'post_author'   => get_current_user_id(),
						  'post_type' 	  => $this->pages_cpt,
						  'post_parent'   => $parent_id,
						  'post_name'     => $child->post_name
						);
						$guide_page_id = wp_insert_post( $guide_data );
						$guide_page = get_post( $guide_page_id );
						$guide_page->post_name = set_guide_pages_slugs( $this->destination->ID, $guide_page, false ); // false = master
						wp_update_post( $guide_page );
					} else {
						$guide_page = $dest_page;
					}
					set_guide_page_level( $guide_page->ID, $this->level );
					set_guide_page_order( $guide_page->ID, $child->ID );
					set_guide_page_parent( $guide_page->ID, $this->destination->ID );
					set_master_parent( $guide_page->ID, $child->ID );
					set_guide_page_GUI( $guide_page->ID, $this->page_GUI );

					if( is_master_page_disabled( $child->ID ) )
						$this->set_guide_page_disabled( $guide_page->ID );

					$link = 'post.php?post='.$guide_page->ID.'&action=edit';
					$title_link = '<a href="'.$link.'" style="font-weight: bold; font-size:14px">' . str_repeat( '— ', $level ) . $guide_page->post_title . '</a>';
					$user = get_user_by( 'id', $guide_page->post_author );
					array_push( $data_table, array( 'ID' => $guide_page->ID, 'title' => $title_link, 'author' => $user->display_name, 'date' => date( 'F j, Y', strtotime( $guide_page->post_date ) ) ) );

					$args = array(
						'numberposts' => -1,
						'post_parent' => $child->ID,
						'meta_key' => 'master_order',
						'orderby' => 'meta_value_num',
						'order' => 'ASC',
						'post_type' => 'master-pages'
					);
					$children = get_children( $args );
					if( count( $children ) ) {
						$data_table = $this->get_children_pages( $children, $data_table, $level+1, $guide_page->ID );
					}
				}
			}
			return $data_table;
		}

		function get_children_nomaster_pages( $children, $data_table, $level, $parent_id ) {
			if( count( $children ) > 0 ) {
				foreach( $children as $child ) {
					$link = 'post.php?post='.$child->ID.'&action=edit';
					$title_link = '<a href="'.$link.'" style="font-weight: bold; font-size:14px">' . str_repeat( '— ', $level ) . $child->post_title . '</a>';
					$user = get_user_by( 'id', $child->post_author );
					array_push( $data_table, array( 'ID' => $child->ID, 'title' => $title_link, 'author' => $user->display_name, 'date' => date( 'F j, Y', strtotime( $child->post_date ) ) ) );

					$children = get_children ( $child->ID );
					if( count( $children ) ) {
						$data_table = $this->get_children_nomaster_pages( $children, $data_table, $level+1, $child->ID );
					}
				}
			}
			return $data_table;
		}

		function validate_guide_options() {
			$output = $this->master->set_messages();

			if ( $this->master->is_valid() )
				return true;
			else
				$error_msg  = '<strong>' .__( "Oops! There was an error saving. Please use a different permalink base.", 'destinations' ) .'</strong><br><br>';
				$error_msg .= __( "The following terms are already used by WordPress and could cause conflicts: ", 'destinations' ) .'<br>';
				$error_msg .= '<code>' .esc_attr($output). '</code>';
				return new WP_Error( 'use-reserve-word', $error_msg );
		}

		function save_guide_options() {
			if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'save-settings' ) {
				$this->settings = stripslashes_deep( $_POST );
				$this->settings['menu_item_child'] = isset( $this->settings['menu_item_child'] ) ? 'true' : 'false';
				$this->settings['menu_item_blogs'] = isset( $this->settings['menu_item_blogs'] ) ? 'true' : 'false';
				$this->settings['menu_title_child'] = isset( $this->settings['menu_title_child'] ) ? esc_attr($this->settings['menu_title_child']) : '';
				$this->settings['menu_title_blogs'] = isset( $this->settings['menu_title_blogs'] ) ? esc_attr($this->settings['menu_title_blogs']) : '';
				$this->settings['destinations_base'] = isset($this->settings['destinations_base'])? esc_attr($this->settings['destinations_base']) : '';
				$this->settings['page_base'] = isset( $this->settings['page_base'] ) ? esc_attr($this->settings['page_base']) : '';
				$this->settings['guide_list_base'] = isset( $this->settings['guide_list_base'] ) ? esc_attr( $this->settings['guide_list_base'] ) : '';
				$this->settings['directory_item_base'] = isset( $this->settings['directory_item_base'] ) ? esc_attr( $this->settings['directory_item_base'] ) : '';
				$this->settings['places_articles_base'] = isset( $this->settings['places_articles_base'] ) ? esc_attr( $this->settings['places_articles_base'] ) : '';
				$this->settings['show_map_for_directory_items'] = isset( $this->settings['show_map_for_directory_items'] ) ? 'true' : 'false';
				$this->settings['zoom_control'] = isset( $this->settings['zoom_control'] ) ? 'true' : 'false';
				$this->settings['zoom_scrollwheel'] = isset( $this->settings['zoom_scrollwheel'] ) ? 'true' : 'false';
				$this->settings['rewrite_flush_rules'] = 1;

				$option_key = get_travel_guide_option_key( 'travel_guide_options' );
				update_option( $option_key, json_encode( $this->settings ) );
				if( defined( 'ICL_LANGUAGE_CODE' ) ) {
					if( apply_filters( 'wpml_current_language', NULL ) == apply_filters( 'wpml_default_language', NULL ) )
						update_option( 'travel_guide_options', json_encode( $this->settings ) );
				}
				$link = admin_url() . 'edit.php?post_type=destination&page=destination-settings&tab=guide-options';
				echo '<script type="text/javascript">window.location = "'. esc_url_raw( $link ) .'";</script>';
			}
		}

		function display_destination_tabs( $tab ) {
			$post_id = ( isset( $_REQUEST['post'] ) && ! empty( $_REQUEST['post'] ) ) ? $_REQUEST['post'] : 0; ?>

			<h2 class="nav-tab-wrapper tab-controlls head-tabs" style="padding-top: 10px;">
				<a href="<?php echo admin_url(); ?>post.php?post=<?php echo $post_id; ?>&action=edit&tab=destination" class="nav-tab <?php echo ( ( $tab == 'destination' ) ? 'nav-tab-active' : '' ); ?>"><?php _e( 'Destination', 'destinations' ) ?></a>
				<a href="<?php echo admin_url(); ?>edit.php?post_type=destination&page=guide-pages&post=<?php echo $post_id; ?>&tab=pages" class="nav-tab <?php echo ( ( $tab == 'pages' ) ? 'nav-tab-active' : '' ); ?>"><?php _e( 'Pages', 'destinations' ) ; ?></a>
				<a href="<?php echo admin_url(); ?>edit.php?post_type=travel-directory" class="nav-tab <?php echo ( ( $tab == 'lists' ) ? 'nav-tab-active' : '' ); ?>"><?php _e( 'Directory Items', 'destinations' ); ?></a>
			</h2> <?php
		}

		function get_count( $m, $status = 'all' ) {
			$query = array(
						'post_type' 		=> 'destination-page',
						'posts_per_page' 	=> -1,
						'meta_query' 		=> array(
												array(
													'key' 	=> 'destination_parent_id',
													'value' => isset($this->destination->ID)? $this->destination->ID : -1
												),
						)
					);
			$query['post_status'] = ( $status == 'all' )? array( 'publish', 'draft' ) : $status;

			if( is_array( $status ) && in_array( 'trash', $status ) )
				$query['meta_query'][] = array(
											'key' 	=> 'no_master',
											'value' => 1
										 );

			if( $m != 0 ) {
					$query['m'] = $_GET['m'];
			}
			$pages = new WP_Query( $query );        // all pages

			return $pages->post_count;
		}

		function set_guide_page_disabled( $id ) {
			$children = get_children( $id );
			if( ! empty( $children ) ) {
				foreach( $children as $child ) {
					update_post_meta( $child->ID, 'is_disabled_guide_page', 'true' );
					$this->set_guide_page_disabled( $child->ID );
				}
			}
			update_post_meta( $id, 'is_disabled_guide_page', 'true' );
		}

		function set_guide_page_enabled( $id ) {
			$children = get_children( $id );
			if( ! empty( $children ) ) {
				foreach( $children as $child ) {
					delete_post_meta( $child->ID, 'is_disabled_guide_page', 'true' );
					$this->set_guide_page_enabled( $child->ID );
				}
			}
			delete_post_meta( $id, 'is_disabled_guide_page', 'true' );
		}

		function delete_guide_pages( $id, $force_delete ) {
			$args = array(
				'numberposts'	=> -1,
				'post_parent' 	=> $id,
				'post_type' 	=> 'destination-page',
			);
			$children = get_children( $args );

			if( ! empty( $children ) ) {
				foreach( $children as $child ) {
					if( $force_delete )
						wp_delete_post( $child->ID, $force_delete );
					else
						wp_trash_post( $child->ID );
				}
			}

			if( $force_delete )
				wp_delete_post( $id, $force_delete );
			else
				wp_trash_post( $id );
		}

		function untrash_guide_pages( $id ) {
			$args = array(
				'numberposts'	=> -1,
				'post_parent'	=> $id,
				'post_type' 	=> 'destination-page',
			);
			$children = get_children( $args );

			if( ! empty( $children ) ) {
				foreach( $children as $child ) {
					wp_publish_post( $child->ID );
				}
			}
			wp_publish_post( $id );
		}

		function get_posts_for_empty() {
			$query = array(
						'post_type' 		=> 'destination-page',
						'posts_per_page' 	=> -1,
						'post_status' 		=> 'trash',
						'meta_query' 		=> array(
													array(
														'key' 	=> 'destination_parent_id',
														'value' => $this->destination->ID
													),
													array(
														'key' 	=> 'no_master',
														'value' => 1
													)
						)
					);
			$posts_for_empty = new WP_Query( $query );

			return $posts_for_empty->posts;
		}

		function guide_page_actions() {

			$guide_page_id = ( isset( $_GET['post'] ) && ! empty( $_GET['post'] ) ) ? $_GET['post'] : 0;
			$item_id = ( isset( $_GET['item'] ) && ! empty( $_GET['item'] ) ) ? $_GET['item'] : 0;
			$post_status = isset( $_GET['post_status'] ) ? '&post_status='.$_GET['post_status'] : '';
			$paged = isset( $_GET['paged'] ) ? '&paged='.$_GET['paged'] : '';
			$url = admin_url( 'edit.php?post_type=destination&page=guide-pages&post='.$guide_page_id.'&tab=pages'.$post_status.$paged );

			if( isset( $_GET['action'] ) && ( $_GET['action'] == 'guide-page-disable' ) ) {
				if( $item_id )
					$this->set_guide_page_disabled( $item_id );
				echo( "<script>location.href = '".$url."';</script>" );
			}

			if( isset( $_GET['action'] ) && ( $_GET['action'] == 'guide-page-enable' ) ) {
				if( $item_id )
					$this->set_guide_page_enabled( $item_id );
				echo( "<script>location.href = '".$url."';</script>" );
			}

			if( isset( $_POST['action'] ) && $_POST['action'] == 'guide-disable' ) {
				if( is_array( $_POST['guide'] ) ) {
					foreach( $_POST['guide'] as $id ) {
						$this->set_guide_page_disabled( $id );
					}
				}
				echo( "<script>location.href = '".$url."';</script>" );
			}

			if( isset( $_POST['action'] ) && $_POST['action'] == 'guide-enable' ) {
				if( is_array($_POST['guide'] ) ) {
					foreach( $_POST['guide'] as $id ) {
						$this->set_guide_page_enabled( $id );
					}
				}
				echo( "<script>location.href = '".$url."';</script>" );
			}

			if( isset( $_GET['trashed'] ) && isset( $_GET['ids'] ) ) {
				$this->delete_guide_pages( $_GET['ids'], false );
				echo( "<script>location.href = '".$url."';</script>" );
			}

			if( isset( $_GET['untrashed'] ) ) {
				global $untrash_master_page_id;
				$this->untrash_guide_pages( $untrash_master_page_id );
				echo( "<script>location.href = '".$url."';</script>" );
			}

			if( isset( $_POST['action'] ) && $_POST['action'] == 'guide-untrash' ) {
				if( is_array( $_POST['guide'] ) ) {
					foreach( $_POST['guide'] as $id ) {
						$this->untrash_guide_pages( $id );
					}
				}
				echo( "<script>location.href = '".$url."';</script>" );
			}

			if( isset( $_POST['action'] ) && $_POST['action'] == 'guide-delete' ) {
				if( is_array( $_POST['guide'] ) ) {
					foreach( $_POST['guide'] as $id ) {
						$this->delete_guide_pages( $id, true );
					}
				}
				echo( "<script>location.href = '".$url."';</script>" );
			}

			if( isset( $_POST['empty_trash_page'] ) ) {
					$posts_for_empty = $this->get_posts_for_empty();
					foreach( $posts_for_empty as $post_for_empty ) {
						$this->delete_guide_pages( $post_for_empty->ID, true );
					}
				echo( "<script>location.href = '".$url."';</script>" );
			}
		}

		function get_exists_guide_page($master_id) {
			$args = array(
				'post_type' => 'destination-page',
				'posts_per_page' => -1,
				'meta_query' => array(
					array(
						'key'	 => 'destination_parent_id',
						'value'  => isset($this->destination->ID)? $this->destination->ID : -1
					),
					array(
						'key' 	=> 'master_parent_id',
						'value' => $master_id
					)
				)
			);
			$guide_pages = get_posts( $args );

			return (count($guide_pages))? $guide_pages[0] : 0;
		}

		function get_exists_guide_page_by_destination_id( $dest_id ) {
			$args = array(
				'post_type' 		=> 'destination-page',
				'posts_per_page' 	=> -1,
				'meta_query' 		=> array(
											array(
												'key' 	=> 'destination_parent_id',
												'value' => $dest_id
											),
				)
			);
			$guide_pages = get_posts( $args );

			return $guide_pages;
		}

		function display_guide_pages() {
			$this->guide_page_actions();

			$query = array(
				'post_type' 		=> 'master-pages',
				'posts_per_page' 	=> -1,
				'meta_key' 			=> 'master_order',
				'orderby' 			=> 'meta_value_num',
				'order' 			=> 'ASC',
			);

			if( isset( $_GET['filter_date'] ) && ! empty( $_GET['filter_date'] ) && $_GET['filter_date'] != 0 ) {
				$query['m'] = $_GET['filter_date'];
			}
			if( isset( $_GET['post_status'] ) && ! empty( $_GET['post_status'] ) ) {
				$query['post_status'] = ( $_GET['post_status'] == 'all' ) ? array( 'publish', 'draft' ) : array( $_GET['post_status'] );
			}

			$all_master_pages = new WP_Query( $query );

			$query['post_parent'] = 0;
			$master_pages = new WP_Query($query);  // Loop for all master pages
			$data_table = array();                 // gather data for WP List Table

			$this->page_GUI = get_GUI();

			if( ! isset( $_GET['post_status'] ) || ( isset( $_GET['post_status'] ) && $_GET['post_status'] != 'trash' ) ) {
				if ( $master_pages->have_posts() ) {
					while ( $master_pages->have_posts() ) {
						$master_pages->the_post();
						$args = array(
							'numberposts'	=> -1,
							'post_parent' 	=> $master_pages->post->ID,
							'meta_key' 		=> 'master_order',
							'orderby' 		=> 'meta_value_num',
							'order' 		=> 'ASC',
							'post_type' 	=> 'master-pages'
						);
						$children = get_children( $args );
						//$slgs = get_guide_pages_slugs_new($this->destination->ID);

						// for only parent
						//$post_name = (!empty($slgs))? array_search(create_parent_slug( $this->destination, true ) . '/' . create_parent_front_slug($master_pages->post), (array)$slgs) : 'nothing';
						//$post_name = empty($post_name)? 'nothing' : $post_name;

						//$guide_page_query = new WP_Query(array('post_type' => $this->pages_cpt, 'post_status' => array('publish'), 'name' => $post_name));

						$dest_page = $this->get_exists_guide_page( get_the_ID() );

						if( ! $dest_page ) {
							if( defined( 'ICL_LANGUAGE_CODE' )) {
								if( apply_filters( 'wpml_current_language', NULL ) != apply_filters( 'wpml_default_language', NULL ) )
									continue;
							}
							$post_name_new = get_GUI();
							$guide_data = array(
							  'post_title'    	=> $master_pages->post->post_title,
							  'post_status'   	=> 'publish',
							  'post_author'   	=> get_current_user_id(),
							  'post_type' 		=> $this->pages_cpt,
							  'post_parent' 	=> 0,
							  'post_name' 		=> $master_pages->post->post_name
							);
							$guide_page_id = wp_insert_post( $guide_data );
							$guide_page = get_post( $guide_page_id );
							$guide_page->post_name = set_guide_pages_slugs( $this->destination->ID, $guide_page, false ); // false = master
							wp_update_post( $guide_page );
						} else {
							$guide_page = $dest_page;
						}
						set_guide_page_level( $guide_page->ID, $this->level );
						set_guide_page_order( $guide_page->ID, $master_pages->post->ID );
						set_guide_page_parent( $guide_page->ID, $this->destination->ID );
						set_master_parent( $guide_page->ID, $master_pages->post->ID );
						set_guide_page_GUI( $guide_page->ID, $this->page_GUI );
						if( is_master_page_disabled( $master_pages->post->ID ) )
							$this->set_guide_page_disabled( $guide_page->ID );

						$link = 'post.php?post='.$guide_page->ID.'&action=edit';
						$title_link = '<a href="'.$link.'" style="font-weight: bold; font-size:14px">' . $guide_page->post_title . '</a>';
						$user = get_user_by( 'id', $guide_page->post_author );
						array_push( $data_table, array( 'ID' => $guide_page->ID, 'title' => $title_link, 'author' => $user->display_name, 'date' => date( 'F j, Y', strtotime( $guide_page->post_date ) ) ) );

						if( count( $children ) )
							$data_table = $this->get_children_pages( $children, $data_table, 1, $guide_page->ID );
					}

				} else {
					// no posts found
				}
			}

			// Additional pages that don't have master
			$query = array(
				'post_type' 		=> 'destination-page',
				'posts_per_page' 	=> -1,
				'post_parent' 		=> 0,
				'meta_query' 		=> array(
											array(
												'key' 	=> 'no_master',
												'value' => 1
											),
											array(
												'key' 	=> 'destination_parent_id',
												'value' => isset( $this->destination->ID ) ? $this->destination->ID : -1
											)
										)
			);
			if( isset( $_GET['filter_date'] ) && ! empty( $_GET['filter_date'] ) && $_GET['filter_date'] != 0 ) {
				$query['m'] = $_GET['filter_date'];
			}
			if( isset( $_GET['post_status'] ) && ! empty( $_GET['post_status'] ) ) {
				$query['post_status'] = array( $_GET['post_status'] );
			}

			$guide_page_query = new WP_Query( $query );
			while ( $guide_page_query->have_posts() ) {
				$guide_page_query->the_post();
				$link = 'post.php?post='.get_the_ID().'&action=edit';
				$title_link = '<a href="'.$link.'" style="font-weight: bold; font-size:14px">' . get_the_title() . '</a>';
				array_push( $data_table, array( 'ID' => get_the_ID(), 'title' => $title_link, 'author' => get_the_author_meta( 'display_name' ), 'date' => date( 'F j, Y', strtotime( get_the_date() ) ) ) );

				$args = array(
					'numberposts' 	=> -1,
					'post_parent' 	=> get_the_ID(),
					'meta_key' 		=> 'guide_page_order',
					'orderby' 		=> 'meta_value_num',
					'order' 		=> 'ASC',
					'post_type' 	=> 'destination-page'
				);
				$children = get_children( $args );
				if( count( $children ) ) {
					$data_table = $this->get_children_nomaster_pages( $children, $data_table, 1, get_the_ID() );
				}
			}

			if( ! isset( $_GET['post_status'] ) || ( isset( $_GET['post_status'] ) && ! in_array( $_GET['post_status'], array( 'trash', 'draft' ) ) ) ) {
				// Delete pages for selected destination if master page is deleted
				$query = array(
					'post_type' 		=> 'destination-page',
					'posts_per_page' 	=> -1,
					'meta_query' 		=> array(
												array(
													'key' 		=> 'destination_parent_id',
													'value' 	=> isset( $this->destination->ID ) ? $this->destination->ID : -1
												),
												array(
													'key' 		=> 'destination_master_GUI',
													'value' 	=> array( $this->page_GUI ),
													'compare' 	=> 'NOT IN'
												),
												array(
													'key' 		=> 'no_master',
													'compare' 	=> 'NOT EXISTS'
												)
					)
				);
				$deleted_pages = new WP_Query( $query );
				foreach( $deleted_pages->posts as $deleted ) {
					wp_delete_post( $deleted->ID, true );
				}
			}

			global $all_guide_pages_count, $publish_guide_pages_count, $draft_guide_pages_count, $trash_guide_pages_count;
			$m = ( isset( $_GET['filter_date'] ) && ! empty( $_GET['filter_date'] ) && $_GET['filter_date'] != 0 ) ? $_GET['filter_date'] : 0;
			$all_guide_pages_count = $this->get_count( $m );
			$publish_guide_pages_count = $this->get_count( $m, array( 'publish' ) );
			$draft_guide_pages_count = $this->get_count( $m, array( 'draft' ) );
			$trash_guide_pages_count = $this->get_count( $m, array( 'trash' ) );
			$url_new_page = add_query_arg( array( 'parent' => $this->destination->ID, 'gui' => $this->page_GUI ), admin_url().'post-new.php?post_type=destination-page' );
			?>

			<div class="wrap guide-pages">
				<h2><?php echo apply_filters( 'get_qtranslate_rw', $this->destination_title ) . __( ' Guide Pages', 'destinations' ); ?>&nbsp;
					<a href="<?php echo $url_new_page; ?>" class="add-new-h2"><?php _e( 'Add New', 'destinations' ); ?></a>
				</h2>

				<?php
					$table = new Guide_Pages_List_Table( $data_table ); ?>
					<form name="guide-bulk-apply" action="" method="post">
						<input type="hidden" id="guide-bulk-apply" value="guide-bulk-apply"> <?php
						$table->views();
						$table->prepare_items();
						$table->display(); ?>
					</form> <?php
				?>
			</div> <?php
		}

		function settings_page() {

			$tab = ( isset( $_REQUEST['tab'] ) && ! empty( $_REQUEST['tab'] ) ) ? $_REQUEST['tab'] : 'guide-options';

			switch ( $tab ) {
				case 'guide-options':
					$return = $this->validate_guide_options();
					if( ! is_wp_error( $return ) )
						$this->save_guide_options();
					else
						echo '<div id="message" class="error"><p>' . $return->get_error_message() . '</p></div>';

					$this->master->display_settings_tabs( $tab );
					$this->master->display_settings();
					break;

				case 'master-pages':
					$this->master->display_master_pages( $tab );
					break;
			}
		}

		function guide_pages() {

			$post_id = ( isset( $_REQUEST['post'] ) && ! empty( $_REQUEST['post'] ) ) ? $_REQUEST['post'] : 0;
			$post_id = defined( 'ICL_LANGUAGE_CODE' ) ? (int) apply_filters( 'wpml_object_id', $post_id, 'destination', false, ICL_LANGUAGE_CODE ) : $post_id;
			$this->destination = get_post( $post_id );
			$this->level = count( get_post_ancestors( $post_id ) );  	// level of the destination
			$this->destination_title = isset( $this->destination->post_title ) ? $this->destination->post_title : '';
			$this->pages_cpt = get_pages_cpt( $post_id );                  // get CPT name of the desination's guide pages

			$tab = ( isset( $_REQUEST['tab'] ) && ! empty( $_REQUEST['tab'] ) ) ? $_REQUEST['tab'] : 'destinations';
			$this->display_destination_tabs( $tab );

			$this->display_guide_pages();
		}

		function add_meta_box_after_title( $post_type ) {
			global $post, $wp_meta_boxes, $pagenow;

			do_meta_boxes( get_current_screen(), 'advanced', $post );
			unset( $wp_meta_boxes[get_post_type($post)]['advanced'] );
			$no_master = ( is_object( $post ) ) ? get_guide_page_no_master( $post->ID ) : false;

			$need_hide = ( $post->post_type == 'destination-page' && isset( $_GET['action'] ) && $_GET['action'] == 'edit' ) ? true : false;
			if( $post->post_type == 'destination-page' && defined( 'ICL_LANGUAGE_CODE' ) && isset( $_GET['trid'] ) && $pagenow == 'post-new.php' ) {
				$is_default_info_page_nomaster = get_guide_page_no_master( get_default_info_page_id( $_GET['trid'] ) );
				$no_master = $is_default_info_page_nomaster ? true : false; // set new attribute for no master page

				$master = $is_default_info_page_nomaster ? 0 : has_info_page_master( $_GET['trid'] );
				$dest = has_info_page_destination( $_GET['trid'] );
				$need_hide = true;
				if( $master && $dest ) { ?>
					<script type="text/javascript">
						jQuery(function($) {
							$('input[name=post_title]').val("<?php echo $master->post_title; ?>");
						});
					</script> <?php
					set_master_parent( $post->ID, $master->ID );
				} else {
					$hide_publish = false;
					if( ! $dest ) {
						echo '<div class="error"><p><b>'.__( 'Please translate a parent Destination', 'destinations' ).'</b></p></div>';
						$hide_publish = true;
					}
					if( ! $is_default_info_page_nomaster && ! $master ) {
						echo '<div class="error"><p><b>'.__( 'Please translate a parent Master Page', 'destinations' ).'</b></p></div>';
						$hide_publish = true;
					}
					if( $hide_publish ) {
						remove_post_type_support('destination-page', 'editor'); //hide editor
						?>
							<script type="text/javascript">
								jQuery(function($) {
									$('#publish').attr('disabled', 'disabled');
									$('#save-post').attr('disabled', 'disabled');
									$('a#post-preview').hide();
									$('#wpbody-content').find('input, textarea').attr('disabled', 'disabled');
								});
							</script> 
					<?php }
				}
			}

			if( $need_hide ) {
				$back_url = admin_url().'edit.php?post_type=destination&page=guide-pages&post='.get_guide_page_parent( $post->ID ).'&tab=pages';
				$back_url = defined( 'ICL_LANGUAGE_CODE' ) ? $back_url.'&lang='.ICL_LANGUAGE_CODE : $back_url;
				$pretty_url = ( $post->post_status == 'draft' ) ? $post->guid : get_destination_page_pretty_url( $post->ID ); ?>
				<input id="destination_pages_url_back" type="hidden" value="<?php echo $back_url; ?>">
				<input id="destination_page_pretty_url" type="hidden" value="<?php echo $pretty_url; ?>">
				<script type="text/javascript">
					jQuery(function($) {
						$('.page-title-action').text("<?php echo __('Back to destination pages', 'destinations'); ?>");
						$('.page-title-action').attr('href', $('#destination_pages_url_back').val());
						<?php if ( !$no_master ) : // only disable master pages title ?>
							$('input[name=post_title]').prop('readonly', true);
						<?php endif; ?>
						if($('#message.updated.notice.notice-success.is-dismissible').length) {
							$('#message.updated.notice.notice-success.is-dismissible a').attr('href', $('#destination_page_pretty_url').val());
						}
					});
				</script> <?php
			}
			if( $post->post_type == 'destination-page' && ( ! $no_master || $post->post_status !== 'publish' ) ) { ?>
				<script type="text/javascript">
					jQuery(function($) {
						$('div#edit-slug-box.hide-if-no-js strong').first().hide();
						$('div#edit-slug-box.hide-if-no-js span#sample-permalink').hide();
						$('div#edit-slug-box.hide-if-no-js span#edit-slug-buttons').hide();
					});
				</script> <?php
			}
		}

		public function add_meta_boxes_function() {
			global $post;

			add_meta_box(
				 'destination_intro',
				__( 'Introduction', 'destinations' ),
				array( $this, 'render_meta_box_intro' ),
				'destination',
				'advanced',
				'high'
			);

			add_meta_box(
				 'destination_attributes',
				__( 'Destination Attributes', 'destinations' ),
				array( $this, 'render_meta_box_attributes' ),
				'destination',
				'side',
				'core'
			);

			$no_master = get_guide_page_no_master( $post->ID );
			if( $no_master || isset( $_GET['parent'] ) && isset( $_GET['gui'] ) ) {
				add_meta_box(
					 'destination_attributes',
					__( 'Destination Attributes', 'destinations' ),
					array( $this, 'render_meta_box_page_attributes' ),
					'destination-page',
					'side',
					'core'
				);
			}

			add_meta_box(
				 'destination_options',
				__( 'Destination Options', 'destinations' ),
				array( $this, 'render_meta_box_options' ),
				'destination',
				'normal',
				'high'
			);

			add_meta_box(
				 'map_options',
				__( 'Map Options', 'destinations' ),
				array( $this, 'render_meta_box_map_options' ),
				'destination',
				'normal',
				'high'
			);

			add_meta_box(
				 'destination_page_intro',
				__( 'Introduction', 'destinations' ),
				array( $this, 'render_meta_box_intro' ),
				'destination-page',
				'advanced',
				'high'
			);

			if( isset( $_REQUEST['destination'] ) || isset( $_REQUEST['destination-page'] ) ) {
				add_meta_box(
					'parent_destination',
					__( 'Parent Destination', 'destinations' ),
					array( $this, 'render_meta_box_parent_guide_pages' ),
					$_REQUEST['post_type'],
					'normal',
					'high'
				);
			}
		}

		public function render_meta_box_intro() {
			global $post;

			$intro = get_destination_intro( $post->ID );
			echo '<h3 style="padding-left:0px;">' . __( 'Header Introduction Text', 'destinations' ) . '</h3>';
			echo '<textarea name="intro" class="settings-textarea widefat" rows=5>' . esc_textarea( $intro ) . '</textarea>';
		}

		public function render_meta_box_attributes() {
			global $post;

			$parents = get_posts(
				array(
					'post_type'   		=> 'destination',
					'meta_key' 			=> 'destination_order',
					'orderby' 			=> 'meta_value_num',
					'order' 			=> 'ASC',
					'numberposts' 		=> -1,
					'suppress_filters' 	=> defined( 'ICL_LANGUAGE_CODE' ) ? 0 : 1
				)
			);
			$parents = get_page_hierarchy( $parents );
			if( defined( 'ICL_LANGUAGE_CODE' ) ) {
				$trid = isset( $_GET['trid'] ) ? $_GET['trid'] : 0;
				if( $trid ) { 											// if new translated post
					$post_default = get_post( $trid );
					if( ! empty( $post_default ) ) {
						$parent_id = (int) apply_filters( 'wpml_object_id', $post_default->post_parent, 'destination', false, ICL_LANGUAGE_CODE );
					}
					$order = get_destination_order( $trid );
				} else {
					$parent_id = (int) apply_filters( 'wpml_object_id', $post->post_parent, 'destination', false, ICL_LANGUAGE_CODE );
					$order = get_destination_order( $post->ID );
				}
			} else {
				$parent_id = $post->post_parent;
				$order = get_destination_order( $post->ID );
			}

			echo '<label class="screen-reader-text" for="parent_id">'.__( 'Parent', 'destinations' ).'</label>';
			echo '<select name="parent_id" class="widefat">';
			printf( '<option value="0">' . __( '(no parent)', 'destinations' ) . '</option>');
			if ( ! empty( $parents ) ) {
				foreach ( $parents as $id => $parent ) {
					$level = count( get_post_ancestors( $id ) );
					printf( '<option value="%s"%s>%s</option>', esc_attr( $id ), selected( $id, $parent_id, false ), esc_html( str_repeat( '&nbsp;&nbsp;&nbsp;&nbsp;', $level ) . get_the_title( $id ) ) );
				}
			}
			echo '</select>';

			echo '<h3 style="padding-left:0px;">' . __( 'Order', 'destinations' ) . '</h3>';
			echo '<label class="screen-reader-text" for="order">'.__( 'Order', 'destinations' ).'</label>';
			echo '<input name="order" type="text" size="4" value="' . $order . '"></input>';
		}

		public function render_meta_box_page_attributes() {
			global $post;

			if( isset( $_GET['parent'] ) && isset( $_GET['gui'] ) ) {
				$no_master = get_guide_page_no_master( $post->ID );
				$value = $no_master ? get_guide_page_parent( $post->ID ) : $_GET['parent'];
				$parents = get_posts(
								array(
									'post_type'   		=> 'destination-page',
									'meta_key' 			=> 'guide_page_order',
									'orderby' 			=> 'meta_value_num',
									'order' 			=> 'ASC',
									'numberposts' 	 	=> -1 ,
									'meta_query'	 	=> array(
																array(
																	'key' 		=> 'destination_parent_id',
																	'value' 	=> $value
																),
																array(
																	'key' 		=> 'no_master',
																	'value' 	=> 1
																),
																array(
																	'key' 		=> 'is_disabled_guide_page',
																	'compare' 	=> 'NOT EXISTS'
																)
									)
								)
				);
				$parents = get_page_hierarchy( $parents );

				echo '<label class="screen-reader-text" for="parent_id">'.__( 'Parent', 'destinations' ).'</label>';
				echo '<select name="parent_id" class="widefat">';
				printf( '<option value="0">'.__( '(no parent)', 'destinations' ).'</option>');
				if ( ! empty( $parents ) ) {
					foreach ( $parents as $id => $parent ) {
						if( $id == $post->ID )
							continue;
						$level = count( get_post_ancestors( $id ) );
						printf( '<option value="%s"%s>%s</option>', esc_attr( $id ), selected( $id, $post->post_parent, false ), esc_html( str_repeat( '&nbsp;&nbsp;&nbsp;&nbsp;', $level ) . get_the_title( $id ) ) );
					}
				}
				echo '</select>';
			}

			$order = get_guide_page_order( $post->ID );
			echo '<h3 style="padding-left:0px;">' . __( 'Order', 'destinations' ) . '</h3>';
			echo '<label class="screen-reader-text" for="order">' . __( 'Order', 'destinations' ) . '</label>';
			echo '<input name="order" type="text" size="4" value="' . $order . '"></input>';
		}

		public function render_meta_box_options() {
			global $post, $current_screen;

			if( defined( 'ICL_LANGUAGE_CODE' ) ) {
				$trid = isset( $_GET['trid'] ) ? $_GET['trid'] : 0;
				if( $trid ) { 											// if new translated post
					$options = get_destination_options( $trid );
					$categories = get_categories();
					if( isset( $options['blog_categories'] ) && is_array( $options['blog_categories'] ) ) {
						foreach( $options['blog_categories'] as $k => $v ) {
							$options['blog_categories'][$k] = (int) apply_filters( 'wpml_object_id', $v, 'category', false, ICL_LANGUAGE_CODE );
						}
					}
				} else {
					$options = get_destination_options( $post->ID );
					$categories = get_categories();
				}
			} else {
				$trid = 0;
				$options = get_destination_options( $post->ID );
				$categories = get_categories();
			}

			// Things we do only when adding new entries (not when editing)
			if( $current_screen->action == 'add' ) {
				if( ! $trid ) {
					// Set defaults
					$include_posts_home = ' checked="checked"';
					$include_posts_child = ' checked="checked"';
					$guide_lists = ' checked="checked"';
				} else {
					$include_posts_home = ( isset( $options['include_posts_home'] ) && $options['include_posts_home'] == 'true' ) ? ' checked="checked"' : '';
					$include_posts_child = ( isset($options['include_posts_child'] ) && $options['include_posts_child'] == 'true' ) ? ' checked="checked"' : '';
					$guide_lists = ( isset( $options['guide_lists'] ) && $options['guide_lists'] == 'true' ) ? ' checked="checked"' : '';
				}
			} else {
				// This is and edit screen... get the values instead.
				$include_posts_home = ( isset( $options['include_posts_home'] ) && $options['include_posts_home'] == 'true' ) ? ' checked="checked"' : '';
				$include_posts_child = ( isset( $options['include_posts_child'] ) && $options['include_posts_child'] == 'true' ) ? ' checked="checked"' : '';
				$guide_lists = ( isset( $options['guide_lists'] ) && $options['guide_lists'] == 'true' ) ? ' checked="checked"' : '';
			}

			echo '<div class="wrap">';


			echo '<h3 style="padding-left:0px;">' . __( 'Blog Posts', 'destinations' ) . '</h3>';
			echo "<p style='margin-top:0;'>";
			echo '<input type="checkbox" name="include_posts_home"' . $include_posts_home . '/>' . __( 'Include posts on destination home', 'destinations' ) . '<br>';
			echo "</p>";
			echo "<p>";
			echo '<input type="checkbox" name="include_posts_child"' . $include_posts_child . '/>' . __( 'Include posts from child destinations', 'destinations' ) . '<br>';
			echo "</p>";

			$selected = isset( $options['blog_categories'] ) ? $options['blog_categories'] : array();
			echo '<h3 style="padding-left:0px;">' . __( 'Blog Categories', 'destinations' ) . '</h3>';
			echo "<p style='margin-top:0;'>";
				echo '<select multiple name="blog_categories[]" class="widefat" style="width: auto; min-width: 50%;">';
				foreach ( $categories as $category ) {
					echo '<option value="' . $category->term_id . '" ' . ( in_array( $category->term_id, $selected ) ? 'selected = "selected"' : '' ) . '>' . esc_html( $category->name ) . '</option>';
				}
				echo '</select>';
			echo "</p>";


			echo '<h3 style="padding-left:0px;">' . __( 'Directory Items', 'destinations' ) . '</h3>';
			echo "<p style='margin-top:0;'>";
			echo '<input type="checkbox" name="guide_lists"' . $guide_lists . '/>' . __( 'Include directory items from child destinations', 'destinations' ) . '<br>';
			echo "</p>";

			echo '</div>';
		}

		public function render_meta_box_map_options() {
			global $post, $current_screen;

			if( defined( 'ICL_LANGUAGE_CODE' ) ) {
				$trid = isset( $_GET['trid'] ) ? $_GET['trid'] : 0;
				if( $trid ) {
					$meta = get_post_meta( $trid, 'destination_options' ); 											// if new translated post
					$options = empty( $meta[0] ) ? '' : json_decode( $meta[0], true );
				} else {
					$meta = get_post_meta( $post->ID, 'destination_options' );
					$options = empty( $meta[0] ) ? '' : json_decode( $meta[0], true );				}
			} else {
				$trid = 0;
				$meta = get_post_meta( $post->ID, 'destination_options' );
				$options = empty( $meta[0] ) ? '' : json_decode( $meta[0], true );
			}

//			$meta = get_post_meta( $post->ID, 'destination_options' );
//			$options = empty($meta[0])? '' : json_decode($meta[0], true);


			echo '<div class="wrap">';

				// Allow injecting more content (before)
				do_action( 'before_meta_box_map_options', $options );

				// Get maps values saved
				$google_map = ( isset( $options['google_map'] ) && ! empty( $options['google_map'] ) ) ? $options['google_map'] : array();
				?>

				<h3 style="padding-left:0px;"><?php _e( 'Location', 'destinations' ) ?></h3>

				<p style="margin-top:0;">
					<?php render_geocoding_map_options( $google_map ); ?>
				</p>

				<p>
					<span style="display:inline-block; width:80px;"><label for="google_map_latitude"><?php _e( 'Latitude', 'destinations' ); ?></label></span>
					<input type="text" name="google_map_latitude" value="<?php echo ( isset( $google_map['latitude'] ) ? $google_map['latitude'] : '' ); ?>" size="30" />
				</p>

				<p>
					<span style="display:inline-block; width:80px;"><label for="google_map_longitude"><?php _e( 'Longitude', 'destinations' ); ?></label></span>
					<input type="text" name="google_map_longitude" value="<?php echo ( isset( $google_map['longitude'] ) ? $google_map['longitude'] : '' ); ?>" size="30" />
				</p>

				<p>
					<span style="display:inline-block; width:80px;"><label for="google_map_zoom"><?php _e( 'Zoom', 'destinations' ); ?></label></span>
					<select name="google_map_zoom" style="width: 6em;">
					<?php
					$map_zoom = ( isset( $google_map['zoom'] ) ) ? (int) $google_map['zoom'] : 11; // default 11
					for ($i = 1; $i <= 21; $i++) {
						echo '<option value="' . $i . '" ' . ( $map_zoom == $i ? 'selected = "selected"' : '' ) . '>' . $i . '</option>';
					} ?>
					</select>
				</p>

				<h3 style="padding-left:0px;"><?php _e( 'Pins', 'destinations' ) ?></h3>

				<p style='margin-top:0;'>
					<input type="checkbox" name="show_directory_pins" <?php echo ( (isset( $google_map['show_directory_pins'] ) && $google_map['show_directory_pins'] == 'true' ) ? 'checked' : '' ); ?> />
					<label for="show_directory_pins">
						<?php _e('Include directory items', 'destinations'); ?>
					</label>
				</p>
				<p>
					<input type="checkbox" name="show_child_pins" <?php echo ( ( isset( $google_map['show_child_pins'] ) && $google_map['show_child_pins'] == 'true' ) ? 'checked' : '' ); ?> />
					<label for="show_child_pins">
						<?php _e( 'Include child destinations', 'destinations' ); ?>
					</label>
				</p>
				<p>
					<input type="checkbox" name="show_current_pin" <?php echo ( ( isset( $google_map['show_current_pin'] ) && $google_map['show_current_pin'] == 'true' ) ? 'checked' : ''); ?> />
					<label for="show_current_pin">
						<?php _e( 'Show pin for current destination', 'destinations' ); ?>
					</label>
				</p>
				<?php

				// Allow injecting more content (after)
				do_action( 'after_meta_box_map_options', $options );

			echo '</div>';
		}

		public function render_meta_box_parent_guide_pages() {
			global $post;

			echo '<input name="parent_destination" type="text" value="'.$_REQUEST["destination"].'"" readonly></input>';

		}

		public function save_meta_box_data( $post_id ) {
			global $post, $sitepress;

			// Check the user's permissions.
			if ( isset( $_POST['post_type'] ) && ( ! is_object( $post ) || ! in_array( $post->post_type, array( 'destination', 'destination-page', 'post', 'page' ) ) ) )
				return;

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
					return;
				}

			if ( isset( $_POST['post_type'] ) && in_array( $post->post_type, array( 'destination', 'destination-page' ) ) ) {
				if( isset( $_POST['intro'] ) ) {
					$intro = wp_kses_post( $_POST['intro'] );
					update_post_meta( $post_id, 'destination_intro', $intro );
				}
				$options = array();
				$options['destinations_menu'] = isset( $_POST['destinations_menu'] ) ? 'true' : 'false';
				$options['include_posts_home'] = isset( $_POST['include_posts_home'] ) ? 'true' : 'false';
				$options['show_link_submenus'] = isset( $_POST['show_link_submenus'] ) ? 'true' : 'false';
				$options['include_posts_child'] = isset( $_POST['include_posts_child'] ) ? 'true' : 'false';
				$options['blog_categories'] = isset( $_POST['blog_categories'] ) ? $_POST['blog_categories'] : array();
				$options['guide_lists'] = isset( $_POST['guide_lists'] ) ? 'true' : 'false';
				$options['google_map']['address'] = isset( $_POST['google_map_address'] ) ? esc_attr( $_POST['google_map_address'] ) : '';
				$options['google_map']['longitude'] = isset( $_POST['google_map_longitude'] ) ? esc_attr( $_POST['google_map_longitude'] ) : '';
				$options['google_map']['latitude'] = isset( $_POST['google_map_latitude'] ) ? esc_attr( $_POST['google_map_latitude'] ) : '';
				$options['google_map']['zoom'] = isset( $_POST['google_map_zoom'] ) ? esc_attr( $_POST['google_map_zoom'] ) : '';
				$options['google_map']['show_directory_pins'] = isset( $_POST['show_directory_pins'] ) ? 'true' : 'false';
				$options['google_map']['show_child_pins'] = isset( $_POST['show_child_pins'] ) ? 'true' : 'false';
				$options['google_map']['show_current_pin'] = isset( $_POST['show_current_pin'] ) ? 'true' : 'false';

				// Filter the optins (for extending by add-ons)
				$options = apply_filters( 'destinations_save_meta_box_data', $options );
				// Do post meta update
				update_post_meta( $post_id, 'destination_options', json_encode( $options ) );

				$order = ( isset( $_POST['order'] ) && ! empty( $_POST['order'] ) ) ? $_POST['order'] : 0;
				if( $post->post_type =='destination' )
					update_post_meta( $post_id, 'destination_order', $order );
				// if($post->post_type =='destination-page' )
				// 	update_post_meta( $post_id, 'guide_page_order', $order );


				if( $post->post_type =='destination' ) {
					set_destinations_terms( $post->ID );

					// change destination slug
					if( $_POST['post_name'] <> $post->post_name ) {
						$guide_pages = $this->get_exists_guide_page_by_destination_id( $post->ID );
						if( isset( $guide_pages ) && ! empty( $guide_pages ) ) {
							remove_action( 'save_post', array( $this, 'save_meta_box_data' ) );
							foreach( $guide_pages as $guide_page ) {																		// current destination - all info pages
								$is_master = get_master_parent( $guide_page->ID ) ? true : false;
								$guide_page->post_name = set_guide_pages_slugs( $post->ID, $guide_page, ! $is_master, true );
								wp_update_post( $guide_page );
							}
							$children_dest = get_children( 'post_parent='.$post->ID.'&post_type=destination&post_status=publish' );		// children destinations - all info pages for each child destination
							foreach( $children_dest as $child_dest ) {
								$guide_pages = $this->get_exists_guide_page_by_destination_id( $child_dest->ID );
								foreach( $guide_pages as $guide_page ) {
									$is_master = get_master_parent( $guide_page->ID ) ? true : false;
									$guide_page->post_name = set_guide_pages_slugs( $child_dest->ID, $guide_page, !$is_master, true );
									wp_update_post( $guide_page );
								}
							}
							add_action( 'save_post', array( $this, 'save_meta_box_data' ) );
						}
					}
				}

				if( $post->post_type =='destination-page' ) {

					remove_action( 'save_post', array( $this, 'save_meta_box_data' ) );

					$url =  parse_url( $_REQUEST['_wp_http_referer'] );
					parse_str( $url['query'], $params );

					if( defined( 'ICL_LANGUAGE_CODE' ) && isset( $_POST['icl_trid'] ) && ! empty( $_POST['icl_trid'] ) ) { // new page for translation
						$params['parent'] = get_destination_id_by_trid( $_POST['icl_trid'] );
						if( ! get_guide_page_GUI( $post->ID ) )
							$params['gui'] = get_GUI();
					}

					$is_master = get_master_parent( $post->ID ) ? true : false;
					if( isset( $params['parent'] ) && isset( $params['gui'] ) ) {
						$level = count( get_post_ancestors( $post->ID ) );
						set_guide_page_level( $post->ID, $level );
						set_guide_page_order_nomaster( $post->ID, $order );
						set_guide_page_parent( $post->ID, $params['parent'] );
						set_guide_page_GUI( $post->ID, $params['gui'] );
						if( ! $is_master )
							set_guide_page_no_master( $post->ID );

						$new_page = get_post( $post->ID );
						if( $new_page->post_status != 'draft' ) {
							$new_page->post_name = set_guide_pages_slugs( $params['parent'], $new_page, !$is_master );  // for new nomaster page or firstly translated master/nomaster pages
							wp_update_post( $new_page );
						}
					} else {
						$dest_id = get_guide_page_parent( $post->ID );
						$post = get_post( $post->ID );
						if( $is_master ) {
							$post->post_name = set_guide_pages_slugs( $dest_id, $post, !$is_master, true );  // for update only
							if( get_post_status( $post->ID ) == false )
								wp_update_post( $post );
						} else {
							if( ! isset( $_POST['save'] ) && isset( $_POST['original_post_status'] ) && ( $_POST['original_post_status'] == 'draft') ) {
								$slugs = get_guide_pages_slugs_new( $dest_id );
								if ( ! property_exists( $slugs, $post->post_name ) ) {
									$post->post_name = set_guide_pages_slugs( $dest_id, $post, !$is_master );
									wp_update_post( $post );
								}
							}
							set_guide_page_no_master( $post->ID );
							update_post_meta( $post_id, 'guide_page_order', $order );
						}
					}

					add_action( 'save_post', array( $this, 'save_meta_box_data' ) );
				}
			}

		}

		public function pre_post_update_function( $post_id, $data ) {
			global $post;

			// Check the user's permissions.
			if ( isset( $_POST['post_type'] ) && in_array( $_POST['post_type'], array( 'destination', 'destination-page' ) ) ) {
				if ( ! current_user_can( 'edit_post', $post_id ) ) {  // edit_page !!!!!!!!!!!!!!
					return;
				}
			} else {

				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					return;
				}
			}

			if ( ! isset( $_POST['parent_id'] ) ) {
				return;
			}

			$post->post_parent = $_POST['parent_id'];
		}

		function page_name_wp_title( $title, $sep ) {
			global $post, $wp_query;

			if( ! $wp_query->post_count )
				return $title;

			$dest = get_post( get_the_destination_ID() );
			$post_title = apply_filters( 'get_qtranslate_rw', $post->post_title );
			$dest_title = isset( $dest ) ? apply_filters( 'get_qtranslate_rw', $dest->post_title ) : '';

			if( is_archive('destinations') ) {
				$title = str_replace( ' '.$sep.' ', '', $title );
				$title = rf__( $title, 'destinations' ) . ' '.$sep.' ';
			}
			if( is_tax( 'travel-category' ) ) {
				$title = str_replace( 'Categories', '', $title );
			}
			if( is_tax( 'destinations' ) ) {
				  $title = str_replace( 'Categories '.$sep, ' '.$sep.' '.$dest_title.' '.$sep, $title );
			}
			if( is_tax('travel-dir-category') ) {
				  $title = str_replace( 'Directory Types '.$sep, ' '.$sep.' '.$dest_title.' '.$sep, $title );
			}
			if( isset( $wp_query->query['travel-directory'] ) || isset( $wp_query->query['destination-page'] ) ) {
				$title = str_replace( substr( $title, 0, strpos( $title, $sep ) ), $post_title.' '.$sep.' '.$dest_title.' ', $title );
			}

			return $title;
		}

		function wp_link_query_destination( $results, $query ) {
			$sz = count( $results );
			for( $i=0; $i < $sz; $i++ ) {
				if( get_post_type( $results[$i]['ID'] ) == 'destination-page' ) {
					$dest_id = get_guide_page_parent( $results[$i]['ID'] );
					if( $dest_id ) {
						$dest_page = get_post( $results[$i]['ID'] );
						$post_slug = create_parent_slug( $dest_page, true );
						$slugs = get_guide_pages_slugs_new( $dest_id );
						$parts = explode( '/', $results[$i]['permalink'] );
						$key = array_search ( 'information' , $parts );
						$parts = array_slice( $parts, 0, $key + 1 );
						if( isset( $slugs->$post_slug ) ) {
							$new_link = $slugs->$post_slug;
							$results[$i]['permalink'] = implode( '/', $parts ).'/'.$new_link;
						}
					}
				}
			}

			return $results;
		}

		function rel_canonical_rf() {
			if ( ! is_singular() )
		    	return;

		  	global $wp_the_query;
		  	if ( !$id = $wp_the_query->get_queried_object_id() )
		    	return;
		 
			if( get_post_type( $id ) == 'destination-page' ) {
			  	$canonical = get_destination_page_link_by_dest_id( $id );

			  	if( $canonical ) {
			    	echo "<link rel='canonical' href='$canonical' />\n";
			    	return;
				}
		  	}
		 
		  	// original code
		  	$link = get_permalink( $id );
		  	if ( $page = get_query_var( 'cpage' ) )
		    	$link = get_comments_pagenum_link( $page );
		  	echo "<link rel='canonical' href='$link' />\n";
		}

		function oembed_discovery_links_rf() {
			global $post;
			$output = '';

			if ( is_singular() && ! is_front_page() ) {
			  if( get_post_type( $post->ID ) == 'destination-page' ) {
			  		$link = get_destination_page_link_by_dest_id( $post->ID );
					$output .= '<link rel="alternate" type="application/json+oembed" href="' . esc_url( get_oembed_endpoint_url( $link ) ) . '" />' . "\n";	  				
					if ( class_exists( 'SimpleXMLElement' ) ) {
						$output .= '<link rel="alternate" type="text/xml+oembed" href="' . esc_url( get_oembed_endpoint_url( $link, 'xml' ) ) . '" />' . "\n";
					}
					return $output;
				}

				$output .= '<link rel="alternate" type="application/json+oembed" href="' . esc_url( get_oembed_endpoint_url( get_permalink() ) ) . '" />' . "\n";

				if ( class_exists( 'SimpleXMLElement' ) ) {
					$output .= '<link rel="alternate" type="text/xml+oembed" href="' . esc_url( get_oembed_endpoint_url( get_permalink(), 'xml' ) ) . '" />' . "\n";
				}
			}
			return $output;
		}

		function previous_post_rel_link_rf() {
			$link = $this->get_adjacent_post_rel_link_rf( '%title', false, '', true ); //prev
			return $link;
		}

		function next_post_rel_link_rf() {
			$link = $this->get_adjacent_post_rel_link_rf( '%title', false, '', false ); //next
			return $link;
		}

		function get_adjacent_post_rel_link_rf( $title = '%title', $in_same_term = false, $excluded_terms = '', $previous = true, $taxonomy = 'category' ) {

			if ( $previous && is_attachment() && $post = get_post() )
				$post = get_post( $post->post_parent );
			else
				$post = get_adjacent_post( $in_same_term, $excluded_terms, $previous, $taxonomy );

			if ( empty( $post ) )
				return;

			$post_title = the_title_attribute( array( 'echo' => false, 'post' => $post ) );

			if ( empty( $post_title ) )
				$post_title = $previous ? __( 'Previous Post', 'destinations' ) : __( 'Next Post', 'destinations' );

			$date = mysql2date( get_option( 'date_format' ), $post->post_date );

			$title = str_replace( '%title', $post_title, $title );
			$title = str_replace( '%date', $date, $title );

			$link = $previous ? "<link rel='prev' title='" : "<link rel='next' title='";
			$link .= esc_attr( $title );

			if( $post->post_type == 'destination-page' ) {
				if( is_guide_page_disabled( $post->ID ) )
					return false;
				$link .= "' href='" . get_destination_page_link_by_dest_id( $post->ID ) . "' />\n";
			} else {
		 		$link .= "' href='" . get_permalink( $post ) . "' />\n";
		 	}

			return $link;
		}

		function head_langs_rf( $link ) {
			global $sitepress;
			$languages = $sitepress->get_ls_languages( array( 'skip_missing' => true ) );

			$the_post = get_post();
			$the_id   = $the_post ? $the_post->ID : false;

			$is_valid = count( $languages ) > 1 && ! is_paged() && ( ( ( is_single() || is_page() ) && $the_id && get_post_status( $the_id ) == 'publish' ) || ( is_home() || is_front_page() || is_archive() ) );

			if ( $is_valid ) {
				foreach ( $languages as $code => $lang ) {
					if ( get_post_type( $the_id ) == 'destination-page' ) {
						$dest_id     = get_post_meta( $the_id, 'destination_parent_id', true );
						$dest_alt_id = (int) apply_filters( 'wpml_object_id', $dest_id, 'destination', false, $code );
						$page_alt_id = (int) apply_filters( 'wpml_object_id', $the_id, 'destination-page', false, $code );

						if ( $dest_alt_id !== 0 ) {
							$pages              = $code ? get_destination_pages( $dest_alt_id, 'list', $code ) : get_destination_pages( $dest_alt_id );
							$alternate_hreflang = $pages[ $page_alt_id ]['link'];
						} else {
							$alternate_hreflang = '';
						}

					} else {
						$alternate_hreflang = apply_filters( 'wpml_alternate_hreflang', $lang['url'], $code );
					}

					if ( ! empty( $alternate_hreflang ) ) {
						printf( '<link rel="alternate" hreflang="%s" href="%s" />' . PHP_EOL,
							$sitepress->get_language_tag( $code ),
							str_replace( '&amp;', '&', $alternate_hreflang ) );
					}
				}
			}

		}

	}
}

// Main function to call Destinatin CPT class
function destinations_types_load() {
	$destination = new Destination_CPT();
}

// Go!
destinations_types_load();

