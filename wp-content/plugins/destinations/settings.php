<?php
if( ! class_exists( 'Travel_Master_Pages_CPT' ) ) {
	class Travel_Master_Pages_CPT {

		public function __construct( $settings ) {
			$this->settings = $settings;
			$this->init();
		}

		private function init() {

			add_action( 'init', array( $this, 'register_post_type' ), 100 );
			add_action( 'add_meta_boxes', array( $this, 'add_meta_box_attributes' ) );
			add_action( 'pre_post_update', array( $this, 'pre_post_update_function' ), 10, 2 );
			add_action( 'save_post', array( $this, 'save_meta_box_data' ) );
			add_action( 'manage_master-pages_posts_columns', array( $this, 'manage_masters_posts_columns') );
			add_action( 'manage_master-pages_posts_custom_column', array( $this, 'manage_masters_custom_column') ,10,2);
			add_action( 'edit_form_after_title', array( $this, 'add_meta_box_after_title' ) );			

			add_filter( 'parse_query', array( $this, 'sort_masters_by_meta_value' ) );
		}

		function init_settings( $settings ) {
			$this->settings = $settings;
		}

		public function register_post_type() {

			$labels = array(
				'name' 				=> _x( 'Master Pages', 'post type general name', 'destinations' ),
				'singular_name' 	=> _x( 'Master Page', 'post type singular name', 'destinations' ),
				'add_new' 			=> __( 'Add New', 'destinations' ),
				'add_new_item' 		=> __( 'Add New Master Page', 'destinations' ),
				'edit_item' 		=> __( 'Edit Master Page', 'destinations' ),
				'new_item' 			=> __( 'New Master Page', 'destinations' ),
				'all_items' 		=> __( 'All Master Page', 'destinations' ),
				'view_item' 		=> __( 'View Master Page', 'destinations' ),
				'search_items' 		=> __( 'Search Master Page', 'destinations' ),
				'not_found' 		=> __( 'No Master Pages found', 'destinations' ),
				'not_found_in_trash'=> __( 'No Master Pages found in Trash', 'destinations' ),
				'parent_item_colon' => '',
				'menu_name' 		=> __( 'Master Pages', 'destinations' )
			);

			$args = array(
				'labels'              => $labels,
				'public'              => true,
				'exclude_from_search' => true,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'query_var'           => true,
				'capability_type'     => 'post',
				'hierarchical'        => true,
				'supports'            => array( 'title' ),
				'menu_position'       => null,
				'show_in_admin_bar'   => false,
				// 'show_in_menu'        => 'edit.php?post_type=destination',
			);

			register_post_type( 'master-pages', $args );
		}

		function add_meta_box_after_title( $post_type ) {
			global $post, $wp_meta_boxes, $pagenow;

				if( $post->post_type != 'master-pages' )
					return;

				$back_url = admin_url().'edit.php?post_type=destination&page=destination-settings&tab=master-pages';
				$back_url = defined( 'ICL_LANGUAGE_CODE' ) ? $back_url.'&lang='.ICL_LANGUAGE_CODE : $back_url; ?>
				<input id="master_pages_url_back" type="hidden" value="<?php echo $back_url; ?>">
				<script type="text/javascript">
					jQuery(function($) {
						$('.page-title-action').after('<a href="'+$('#master_pages_url_back').val()+'" class="page-title-action"><?php echo __( 'Back to master pages', 'destinations' ); ?></a>');
					});
				</script> <?php
		}

		public function add_meta_box_attributes() {
			add_meta_box(
				 'post_parent',
				__( 'Master Page Attributes', 'destinations' ),
				array( &$this, 'render_meta_box_attributes' ),
				'master-pages',
				'side',
				'low'
			);

			add_meta_box(
				'destination_master_intro',
				__( 'About Master Pages', 'destinations' ),
				array( $this, 'render_meta_box_help_info' ),
				'master-pages',
				'advanced',
				'high'
			);
		}

		public function render_meta_box_help_info() {
			global $post;

			$meta = get_post_meta( $post->ID, 'destination_master_intro' );
			$intro = empty( $meta[0] ) ? '' : $meta[0];
			echo '<p>' . __( 'A Master Page is a static index of pages available to all destinations. They act as placeholders for content specific to a destination. You can disable individual Master Pages from each destination. You can also add custom pages to only a one destination from the pages tab of a destination.', 'destinations' ) . '</p>';

		}

		public function render_meta_box_intro() {
			global $post;

			$meta = get_post_meta( $post->ID, 'destination_master_intro' );
			$intro = empty( $meta[0] ) ? '' : $meta[0];
			echo '<h3 style="padding-left:0px;">' . __( 'Header Introduction Text', 'destinations' ) . '</h3>';
			echo '<textarea name="intro" class="settings-textarea widefat" rows=5>' . $intro . '</textarea>';

		}

		public function render_meta_box_attributes() {
			global $post;

			$parents = get_posts(
				array(
					'post_type'   => 'master-pages',
					'meta_key' => 'master_order',
					'orderby' => 'meta_value_num',
					'order' => 'ASC',
					'numberposts' => -1,
					'suppress_filters' => defined( 'ICL_LANGUAGE_CODE' ) ? 0 : 1
				)
			);
			$parents = get_page_hierarchy( $parents );

			echo '<label class="screen-reader-text" for="parent_id">'.__( 'Parent', 'destinations' ).'</label>';
			echo '<select name="parent_id" class="widefat">';
			printf( '<option value="0">'.__( '(no parent)', 'destinations' ).'</option>' );
			if ( ! empty( $parents ) ) {
				foreach ( $parents as $id => $parent ) {
					$level = count( get_post_ancestors( $id ) );
					printf( '<option value="%s"%s>%s</option>', esc_attr( $id ), selected( $id, $post->post_parent, false ), esc_html( str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level ) . get_the_title( $id ) ) );
				}
			}
			echo '</select>';

			$meta = get_post_meta( $post->ID, 'master_order' );
			$order = ( isset( $meta[0] ) && ! empty( $meta[0] ) ) ? $meta[0] : 0;
			echo '<h3 style="padding-left:0px;">' . __( 'Order', 'destinations' ) . '</h3>';
			echo '<label class="screen-reader-text" for="order">' . __( 'Order', 'destinations' ) . '</label>';
			echo '<input name="order" type="text" size="4" value="' . $order . '"></input>';
		}

		public function update_all_child_guide_pages( $post_id ) {
			global $post;

			$children_guide_pages = $this->get_children_guide_pages( $post_id );//out($post_id);
			foreach( $children_guide_pages as $child ) {//out($child);
				set_guide_page_order( $child, $post_id );
				$child_page = get_post( $child );//out($child_page);
				//$child_page->post_title = esc_attr( stripcslashes( $_POST['post_title'] ) );
				$child_page->post_title = esc_attr( stripcslashes( get_the_title( $post_id ) ) );

				$dest_id = get_guide_page_parent( $child_page->ID );
				$child_page->post_name = set_guide_pages_slugs( $dest_id, $child_page, false, true );
//out($child_page);
				wp_update_post( $child_page, true );
			}
		}

		public function save_meta_box_data( $post_id ) {
			global $post, $wp_rewrite;

			if( isset( $post->post_type ) && $post->post_type == 'master-pages' ) {
				remove_action( 'save_post', array( $this, 'save_meta_box_data' ) );
				remove_action( 'save_post', array( Destination_CPT::this(), 'save_meta_box_data' ) );

				if ( isset( $_POST['intro'] ) ) {
					$intro = sanitize_text_field( $_POST['intro'] );
					update_post_meta( $post_id, 'destination_master_intro', $intro );
				}

				$order = ( isset( $_POST['order'] ) && ! empty( $_POST['order'] ) ) ? $_POST['order'] : 0;
				update_post_meta( $post_id, 'master_order', $order );
				$this->update_all_child_guide_pages( $post_id );
				$children_master_pages = get_children( 'post_parent='.$post_id.'&post_type=master-pages&post_status=publish' );//out($children_master_pages);
				foreach( $children_master_pages as $child ) {
					$this->update_all_child_guide_pages( $child->ID );
				}

				add_action( 'save_post', array( Destination_CPT::this(), 'save_meta_box_data' ) );
				add_action( 'save_post', array( $this, 'save_meta_box_data' ) );

				// in case the slugs were changed
				$wp_rewrite->flush_rules();
			}
		}

		public function pre_post_update_function( $post_id, $data ) {
			global $post;

			// Check the user's permissions.
			if ( isset( $_POST['post_type'] ) && 'master-pages' == $_POST['post_type'] ) {
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
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

		function manage_masters_posts_columns( $post_columns ) {
			$post_columns = array(
				'cb' => $post_columns['cb'],
				'title' => __( 'Title', 'destinations' ),
				'author' => __( 'Author', 'destinations' ),
				'date' => __( 'Date', 'destinations' ),
				'order' => __( 'Order', 'destinations' )
				);
			return $post_columns;
		}

		function manage_masters_custom_column( $column_key, $post_id ) {
			global $pagenow;
			$post = get_post( $post_id );
			if ( $post->post_type == 'master-pages' && is_admin() && $pagenow == 'edit.php' )  {

				if ( $column_key == 'order' ) {
					$order = get_post_meta( $post_id, 'master_order', true );
					echo $order;
				}
			}
		}

		function sort_masters_by_meta_value( $query ) {
			global $pagenow;
			if ( is_admin() && $pagenow == 'edit.php' &&
				isset( $_GET['post_type'] ) && $_GET['post_type'] == 'master-pages' ) {
				$query->query_vars['orderby'] = 'order';
			}
		}

		function display_settings_tabs( $tab ) { ?>
			<h2 class="nav-tab-wrapper tab-controlls head-tabs" style="padding-top: 10px;">
				<a href="<?php echo admin_url(); ?>edit.php?post_type=destination&page=destination-settings&tab=guide-options" class="nav-tab <?php echo ( ( $tab == 'guide-options' ) ? 'nav-tab-active' : '' ); ?>"><?php _e( 'Destination Options', 'destinations' ); ?></a>
				<a href="<?php echo admin_url(); ?>edit.php?post_type=destination&page=destination-settings&tab=master-pages" class="nav-tab <?php echo ( ( $tab == 'master-pages' ) ? 'nav-tab-active' : '' ); ?>"><?php _e( 'Master Pages', 'destinations' ); ?></a>
			</h2>
			<?php
		}

		function is_valid() {
			return ( isset( $this->messages ) && ! empty( $this->messages ) ) ? false : true;
		}

		function set_messages() {
			$exclude = array( 'destinations', 'listings', 'blog', 'category' );
			foreach( get_post_types() as $item )
				$exclude[] = $item;
			$output = implode( ', ', $exclude );

			$this->messages = array();
			if( isset( $_REQUEST['destinations_base'] ) && in_array( $_REQUEST['destinations_base'], $exclude ) )
				$this->messages['destinations_base'] = '"'. esc_attr( $_REQUEST['destinations_base'] ) .'"'. __( ' is a reserved word', 'destinations' );
			if( isset( $_REQUEST['page_base'] ) && in_array( $_REQUEST['page_base'], $exclude ) )
				$this->messages['page_base'] = '"'. esc_attr( $_REQUEST['page_base'] ) .'"'. __( ' is a reserved word', 'destinations' );
			if( isset( $_REQUEST['guide_list_base'] ) && in_array( $_REQUEST['guide_list_base'], $exclude ) )
				$this->messages['guide_list_base'] = '"'. esc_attr( $_REQUEST['guide_list_base'] ) .'"'. __( ' is a reserved word', 'destinations' );
			if( isset( $_REQUEST['directory_item_base'] ) && in_array( $_REQUEST['directory_item_base'], $exclude ) )
				$this->messages['directory_item_base'] = '"'. esc_attr( $_REQUEST['directory_item_base'] ) .'"'. __( ' is a reserved word', 'destinations' );
			if( isset( $_REQUEST['places_articles_base'] ) && in_array( $_REQUEST['places_articles_base'], $exclude ) )
				$this->messages['places_articles_base'] = '"'. esc_attr( $_REQUEST['places_articles_base'] ) .'"'. __( ' is a reserved word', 'destinations' );

			return $output;
		}

		function display_settings() {
			$menus = get_registered_nav_menus();
			$locations = get_nav_menu_locations();

			$menu_selected = ( isset( $this->settings['destinations_menu'] ) && ! empty( $this->settings['destinations_menu'] ) ) ? $this->settings['destinations_menu'] : 0;	?>

			<form action="<?php echo admin_url(); ?>edit.php?post_type=destination&page=destination-settings&action=save-settings&tab=guide-options" method="post">
				<!--<h3><?php _e( 'Child Destinations', 'destinations' ); ?></h3>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row" valign="top"><?php _e('Menu Item', 'destinations'); ?></th>
							<td>
								<select name="destinations_menu">
									<option value="0" <?php echo (($menu_selected == 0)? 'selected' : ''); ?>><?php _e("Manually create menu"); ?></option>
									<?php if( isset( $locations ) && !empty( $locations ) ) : ?>
										<optgroup label="<?php _e('--- Select a Menu ---'); ?>">
											<?php foreach( $locations as $key => $val ):
												if( isset($menus[$key]) ): ?>
													<option value="<?php echo $key; ?>" <?php echo (($menu_selected === $key)? 'selected' : ''); ?>><?php echo $menus[$key]; ?></option>
												<?php endif; ?>
											<?php endforeach; ?>
										</optgroup>
									<?php endif; ?>
								</select>
								<p class="description"><?php _e('Automatically add destinations to the selected theme menu location') ?></p>
							</td>
						</tr>
					</tbody>
				</table> -->

				<h3><?php _e( 'Child Destinations', 'destinations' ); ?></h3>
				<table class="form-table">
					<tbody>
<!-- 						<tr>
							<th scope="row" valign="top"><?php _e('Menu Item'); ?></th>
							<td>
								<input type="checkbox" name="menu_item_child" value="" <?php echo (!$this->is_valid())? (isset($_REQUEST['menu_item_child'])? 'checked' : '') : ((isset($this->settings['menu_item_child']) && $this->settings['menu_item_child'] == 'true')? ' checked' : '') ?>><?php _e('Enable', 'destinations'); ?>
								<p class="description"><?php _e('Show a link in destination sub-menus to an index page of child destination', 'destinations') ?></p>
							</td>
						</tr> -->
						<tr>
							<th scope="row" valign="top"><?php _e( 'Menu Title', 'destinations' ); ?></th>
							<td>
								<input type="text" name="menu_title_child" value="<?php echo ( ! $this->is_valid() ) ? $_REQUEST['menu_title_child'] : ( isset( $this->settings['menu_title_child'] ) ? $this->settings['menu_title_child'] : '' ) ?>">
								<p class="description"><?php _e( 'The title of the blog link in destination sub-menus', 'destinations' ) ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row" valign="top"><?php _e( 'Menu Order', 'destinations' ); ?></th>
							<td>
								<input type="text" name="menu_order_child" value="<?php echo ( ! $this->is_valid() ) ? $_REQUEST['menu_order_child'] : ( isset( $this->settings['menu_order_child'] ) ? $this->settings['menu_order_child'] : '' ) ?>">
								<p class="description"><?php _e( 'Menu order for position in destination sub-menus', 'destinations' ) ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row" valign="top"><?php _e( 'Number of Posts', 'destinations' ); ?></th>
							<td>
								<input type="text" name="number_posts_child" value="<?php echo ( ! $this->is_valid() ) ? $_REQUEST['number_posts_child'] : ( isset( $this->settings['number_posts_child'] ) ? $this->settings['number_posts_child'] : '' ) ?>">
								<p class="description"><?php _e( 'Number of the posts in Places section', 'destinations' ) ?></p>
							</td>
						</tr>
					</tbody>
				</table>

				<h3><?php _e( 'Destination Blogs', 'destinations' ); ?></h3>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row" valign="top"><?php _e( 'Menu Item', 'destinations' ); ?></th>
							<td>
								<input type="checkbox" name="menu_item_blogs" value="" <?php echo ( ! $this->is_valid() ) ? ( isset( $_REQUEST['menu_item_blogs'] ) ? 'checked' : '' ) : ( ( isset( $this->settings['menu_item_blogs'] ) && $this->settings['menu_item_blogs'] == 'true' ) ? ' checked' : '') ?>><?php _e( 'Enable', 'destinations' ); ?>
								<p class="description"><?php _e( 'Show blog link in destination sub-menus', 'destinations' ) ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row" valign="top"><?php _e( 'Menu Title', 'destinations' ); ?></th>
							<td>
								<input type="text" name="menu_title_blogs" value="<?php echo ( ! $this->is_valid() ) ? $_REQUEST['menu_title_blogs'] : ( isset( $this->settings['menu_title_blogs'] ) ? $this->settings['menu_title_blogs'] : '' ) ?>">
								<p class="description"><?php _e(' The title of the blog link in destination sub-menus', 'destinations' ) ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row" valign="top"><?php _e( 'Menu Order', 'destinations' ); ?></th>
							<td>
								<input type="text" name="menu_order_blogs" value="<?php echo ( !$this->is_valid() ) ? $_REQUEST['menu_order_blogs'] : ( isset( $this->settings['menu_order_blogs'] ) ? $this->settings['menu_order_blogs'] : '' ) ?>">
								<p class="description"><?php _e('Menu order for position in destination sub-menus', 'destinations') ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row" valign="top"><?php _e( 'Number of Posts', 'destinations '); ?></th>
							<td>
								<input type="text" name="number_posts_blogs" value="<?php echo ( ! $this->is_valid() ) ? $_REQUEST['number_posts_blogs'] : ( isset( $this->settings['number_posts_blogs'] ) ? $this->settings['number_posts_blogs'] : '' ) ?>">
								<p class="description"><?php _e('Number of the posts in Articles section', 'destinations') ?></p>
							</td>
						</tr>
					</tbody>
				</table>

				<h3><?php _e( 'Information', 'destinations' ); ?></h3>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row" valign="top"><?php _e( 'Number of Posts', 'destinations' ); ?></th>
							<td>
								<input type="text" name="number_posts_information" value="<?php echo ( ! $this->is_valid() ) ? $_REQUEST['number_posts_information'] : ( isset( $this->settings['number_posts_information'] ) ? $this->settings['number_posts_information'] : '' ) ?>">
								<p class="description"><?php _e( 'Number of the posts in Information section', 'destinations' ) ?></p>
							</td>
						</tr>
<!--						<tr>-->
<!--							<th scope="row" valign="top">--><?php //_e( 'Sitemap', 'destinations' ); ?><!--</th>-->
<!--							<td>-->
<!--								<input class="sitemap-url" type="text" size="60" name="sitemap_url" value="--><?php //echo ( ! $this->is_valid() ) ? $_REQUEST['sitemap_url'] : ( isset( $this->settings['sitemap_url'] ) ? $this->settings['sitemap_url'] : '' ) ?><!--">-->
<!--								<p class="description">--><?php //_e( 'Add the URL to your Google sitemap and any encoded information page slugs<br> in that sitemap will be replaced with our prettified ones.', 'destinations' ) ?><!--</p>-->
<!--							</td>-->
<!--						</tr>-->
					</tbody>
				</table>

				<h3><?php _e( 'Directory', 'destinations' ); ?></h3>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row" valign="top"><?php _e( 'Number of Posts', 'destinations' ); ?></th>
							<td>
								<input type="text" name="number_posts_directory" value="<?php echo ( ! $this->is_valid() ) ? $_REQUEST['number_posts_directory'] : ( isset( $this->settings['number_posts_directory'] ) ? $this->settings['number_posts_directory'] : '' ) ?>">
								<p class="description"><?php _e('Number of the posts in Directory section', 'destinations') ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row" valign="top"><?php _e( 'Show map on page load', 'destinations' ); ?></th>
							<td>
								<input type="checkbox" name="show_map_for_directory_items" value="" <?php echo ( ! $this->is_valid() ) ? ( isset( $_REQUEST['show_map_for_directory_items'] ) ? 'checked' : '' ) : ( ( isset( $this->settings['show_map_for_directory_items'] ) && $this->settings['show_map_for_directory_items'] == 'true' ) ? ' checked' : '') ?>><?php _e( 'Enable', 'destinations' ); ?>
								<p class="description"><?php _e( 'Show map for Directory Items', 'destinations' ) ?></p>
							</td>
						</tr>
					</tbody>
				</table>

				<h3><?php _e( 'Permalinks', 'destinations' ); ?></h3>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row" valign="top"><?php _e( 'Destinations Base', 'destinations' ); ?></th>
							<td>
								<input type="text" name="destinations_base" value="<?php echo ( ! $this->is_valid() ) ? $_REQUEST['destinations_base'] : ( isset( $this->settings['destinations_base'] ) ? $this->settings['destinations_base'] : '' ) ?>">
								<span class="error-message"><?php echo isset($this->messages['destinations_base']) ? $this->messages['destinations_base'] : ''; ?></span>
								<p class="description">
									<?php echo sprintf( __( 'Customize the URL structure. If you leave these blank the defaults will be used. %s For example, using %s%s%s as your base would appear:', 'destinations' ), '<br>', '<code>', __( 'location', 'destinations' ), '</code>' ); ?>
									<br><strong><?php echo site_url() ?>/location/europe/</strong>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row" valign="top"><?php _e( 'Page Base', 'destinations' ); ?></th>
							<td>
								<input type="text" name="page_base" value="<?php echo ( ! $this->is_valid() ) ? $_REQUEST['page_base'] : ( isset( $this->settings['page_base'] ) ? $this->settings['page_base'] : '' ) ?>">
								<span class="error-message"><?php echo isset( $this->messages['page_base'] ) ? $this->messages['page_base'] : ''; ?></span>
								<p class="description">
									<?php echo sprintf( __( 'Customize the URL structure. If you leave these blank the defaults will be used. %s For example, using %s%s%s as your base would appear:', 'destinations' ), '<br>', '<code>', __( 'info', 'destinations' ), '</code>' ); ?>
									<br><strong><?php echo site_url() ?>/info/europe/about/</strong>
								</p>
							</td>
						</tr>						
						<tr>
							<th scope="row" valign="top"><?php _e( 'Directory Base', 'destinations' ); ?></th>
							<td>
								<input type="text" name="guide_list_base" value="<?php echo ( ! $this->is_valid() ) ? $_REQUEST['guide_list_base'] : ( isset( $this->settings['guide_list_base'] ) ? $this->settings['guide_list_base'] : '' ) ?>">
								<span class="error-message"><?php echo isset( $this->messages['guide_list_base'] ) ? $this->messages['guide_list_base'] : ''; ?></span>
								<p class="description">
									<?php echo sprintf( __( 'Customize the URL structure. If you leave these blank the defaults will be used. %s For example, using %s%s%s as your base would appear:', 'destinations' ), '<br>', '<code>', __( 'guide', 'destinations' ), '</code>' ); ?>
									<br><strong><?php echo site_url() ?>/guide/europe/hotels/</strong>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row" valign="top"><?php _e( 'Directory Item Base', 'destinations' ); ?></th>
							<td>
								<input type="text" name="directory_item_base" value="<?php echo ( ! $this->is_valid() ) ? $_REQUEST['directory_item_base'] : ( isset( $this->settings['directory_item_base'] ) ? $this->settings['directory_item_base'] : '' ) ?>">
								<span class="error-message"><?php echo isset( $this->messages['directory_item_base'] ) ? $this->messages['directory_item_base'] : ''; ?></span>
								<p class="description">
									<?php echo sprintf( __( 'Customize the URL structure. If you leave these blank the defaults will be used. %s For example, using %s%s%s as your base would appear:', 'destinations' ), '<br>', '<code>', __( 'directory', 'destinations' ), '</code>' ); ?>
									<br><strong><?php echo site_url() ?>/directory/tasty-treats/</strong>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row" valign="top"><?php _e( 'Places/Articles Base', 'destinations' ); ?></th>
							<td>
								<input type="text" name="places_articles_base" value="<?php echo ( ! $this->is_valid() ) ? $_REQUEST['places_articles_base'] : ( isset( $this->settings['places_articles_base'] ) ? $this->settings['places_articles_base'] : '' ) ?>">
								<span class="error-message"><?php echo isset( $this->messages['places_articles_base'] ) ? $this->messages['places_articles_base'] : ''; ?></span>
								<p class="description">
									<?php echo sprintf( __( 'Customize the URL structure. If you leave these blank the defaults will be used. %s For example, using %s%s%s as your base would appear:', 'destinations' ), '<br>', '<code>', __( 'locations', 'destinations' ), '</code>' ); ?>
									<br><strong><?php echo site_url() ?>/locations/europe/places/</strong> and
									<br><strong><?php echo site_url() ?>/locations/europe/articles/</strong>
								</p>
							</td>
						</tr>	
					</tbody>
				</table>

				<h3><?php _e( 'Maps', 'destinations' ); ?></h3>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row" valign="top"><?php _e( 'Zoom Controls', 'destinations' ); ?></th>
							<td>
								<input type="checkbox" name="zoom_control" value="" <?php echo ( ! $this->is_valid() ) ? ( isset( $_REQUEST['zoom_control'] ) ? 'checked' : '' ) : ( ( isset( $this->settings['zoom_control'] ) && $this->settings['zoom_control'] == 'true' ) ? ' checked' : '') ?>><?php _e( 'Enable', 'destinations' ); ?>
								<p class="description"><?php _e( 'Enable map zoom controls', 'destinations' ) ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row" valign="top"><?php _e( 'Zoom Scroll Wheel', 'destinations' ); ?></th>
							<td>
								<input type="checkbox" name="zoom_scrollwheel" value="" <?php echo ( ! $this->is_valid() ) ? ( isset( $_REQUEST['zoom_scrollwheel'] ) ? 'checked' : '' ) : ( ( isset( $this->settings['zoom_scrollwheel'] ) && $this->settings['zoom_scrollwheel'] == 'true' ) ? ' checked' : '') ?>><?php _e( 'Enable', 'destinations' ); ?>
								<p class="description"><?php _e( 'Enable map zoom with mouse scroll wheel', 'destinations' ) ?></p>
							</td>
						</tr>
					</tbody>
				</table>

				<input class="button-primary" type="submit" value="<?php _e( 'Save Settings', 'destinations' ) ?>">
			</form>	<?php
		}

		function get_children_pages( $children, $data_table, $level, $parent_id ) {
			if( count( $children ) > 0 ) {
				foreach( $children as $child ) {
					$guide_page = $child;

					$link = 'post.php?post='.$guide_page->ID.'&action=edit';
					$title_link = '<a href="'.$link.'" style="font-weight: bold; font-size:14px">' . str_repeat( '— ', $level ) . $guide_page->post_title . '</a>';
					$user = get_user_by( 'id', $guide_page->post_author );
					$meta = get_post_meta( $guide_page->ID, 'master_order' );
					$order = ( isset( $meta[0] ) && ! empty( $meta[0] ) ) ? $meta[0] : 0;
					array_push( $data_table, array( 'ID' => $guide_page->ID, 'title' => $title_link, 'author' => $user->display_name, 'date' => date( 'F j, Y', strtotime( $guide_page->post_date ) ), 'order' => $order ) );

					$children = get_children ( array('post_type' => 'master-pages', 'post_parent' => $child->ID) );
					if( count( $children ) ) {
						$data_table = $this->get_children_pages( $children, $data_table, $level+1, $guide_page->ID );
					}
				}
			}
			return $data_table;
		}

		function get_count( $m, $status = 'all' ) {
			$query = array(
					'post_type' => 'master-pages',
					'posts_per_page' => -1,
					);
			$query['post_status'] = ( $status == 'all' ) ? array( 'publish', 'draft' ) : $status;

			if( $m != 0 ) {
					$query['m'] = $_GET['m'];
			}
			$pages = new WP_Query( $query );        // all pages

			return $pages->post_count;
		}

		function get_children_guide_pages( $post_id, $trash = false ) {

			$query = array(
				'post_type' => 'destination-page',
				'posts_per_page' => -1,
				'meta_query' => array(
					array(
						'key' => 'master_parent_id',
						'value' => $post_id
					)
				)
			);

			$children = new WP_Query($query);

			$ids = array();
			while ( $children->have_posts() ) {
				$children->the_post();
				$ids[] = get_the_ID();
			}
			return empty( $ids ) ? array() : $ids;
		}

		function update_children_guide_pages_post_meta( $id, $key ) {
			$children = $this->get_children_guide_pages( $id );
			foreach( $children as $child )
				update_post_meta( $child, $key, 'true' );
		}

		function delete_children_guide_pages_post_meta( $id, $key ) {
			$children = $this->get_children_guide_pages( $id );
			foreach( $children as $child )
				delete_post_meta( $child, $key, 'true' );
		}

		function delete_children_guide_pages( $id, $force_delete ) {
			$children = $this->get_children_guide_pages( $id, $force_delete );
			foreach( $children as $child ) {
				if( $force_delete )
					wp_delete_post( $child, $force_delete );
				else
					wp_trash_post( $child );
			}
		}

		function untrash_children_guide_pages( $id ) {
			$children = $this->get_children_guide_pages( $id, true );
			foreach( $children as $child ) {
				$restore_post = get_post( $child );
				wp_publish_post( $child );
			}
		}

		function set_master_page_disabled( $id ) {
			$args = array(
				'numberposts' => -1,
				'post_parent' => $id,
				'post_type' => 'master-pages',
			);
			$children = get_children( $args );
			if( ! empty( $children ) ) {
				foreach( $children as $child ) {
					update_post_meta( $child->ID, 'is_disabled_master_page', 'true' );
					$this->update_children_guide_pages_post_meta( $child->ID, 'is_disabled_guide_page' );
					$this->set_master_page_disabled( $child->ID );
				}
			}
			update_post_meta( $id, 'is_disabled_master_page', 'true' );
			$this->update_children_guide_pages_post_meta( $id, 'is_disabled_guide_page' );
		}

		function set_master_page_enabled( $id ) {
			$args = array(
				'numberposts' => -1,
				'post_parent' => $id,
				'post_type' => 'master-pages',
			);
			$children = get_children( $args );
			if( ! empty( $children ) ) {
				foreach( $children as $child ) {
					delete_post_meta( $child->ID, 'is_disabled_master_page', 'true' );
					$this->delete_children_guide_pages_post_meta( $child->ID, 'is_disabled_guide_page' );
					$this->set_master_page_enabled( $child->ID );
				}
			}
			delete_post_meta( $id, 'is_disabled_master_page', 'true' );
			$this->delete_children_guide_pages_post_meta( $id, 'is_disabled_guide_page' );
		}

		function delete_master_guide_pages( $id, $force_delete = false ) {
			$args = array(
				'numberposts' => -1,
				'post_parent' => $id,
				'post_type' => 'master-pages',
			);
			$children = get_children( $args );

			if( ! empty( $children ) ) {
				foreach( $children as $child ) {
					$this->delete_children_guide_pages( $child->ID, $force_delete );
					$this->delete_master_guide_pages( $child->ID, $force_delete );
					if( $force_delete )
						wp_delete_post( $child->ID, $force_delete );
					else
						wp_trash_post( $child->ID );
				}
			}
			$this->delete_children_guide_pages( $id, $force_delete );
			if( $force_delete )
				wp_delete_post( $id, $force_delete );
			else
				wp_trash_post( $id );
		}

		function master_page_actions() {

			$master_id = ( isset( $_GET['post'] ) && !empty( $_GET['post'] ) ) ? $_GET['post'] : 0;
			$post_status = isset( $_GET['post_status'] ) ? '&post_status='.$_GET['post_status'] : '';
			$paged = isset( $_GET['paged'] ) ? '&paged='.$_GET['paged'] : '';
			$url = admin_url( 'edit.php?post_type=destination&page=destination-settings&tab=master-pages'.$post_status.$paged );

			if( isset( $_GET['action'] ) && ( $_GET['action'] == 'master-page-disable' ) ) {
				if( $master_id )
					$this->set_master_page_disabled( $master_id );
				echo("<script>location.href = '".$url."';</script>");
			}

			if( isset( $_GET['action'] ) && ( $_GET['action'] == 'master-page-enable' ) ) {
				if( $master_id ) {
					$this->set_master_page_enabled( $master_id );
				}
				echo("<script>location.href = '".$url."';</script>");
			}

			if( isset( $_POST['action'] ) && $_POST['action'] == 'master-disable' ) {
				if( is_array( $_POST['guide'] ) ) {
					foreach( $_POST['guide'] as $id ) {
						$this->set_master_page_disabled( $id );
					}
				}
				echo("<script>location.href = '".$url."';</script>");
			}

			if( isset( $_POST['action'] ) && $_POST['action'] == 'master-enable' ) {
				if( is_array( $_POST['guide'] ) ) {
					foreach( $_POST['guide'] as $id ) {
						$this->set_master_page_enabled( $id );
					}
				}
				echo( "<script>location.href = '".$url."';</script>" );
			}

			if( isset( $_GET['action'] ) && ( $_GET['action'] == 'confirm-master-delete' ) ) {
				$msg_confirm = __( 'Deleting a master page will also delete all destination information Pages associated with that master page.<br>', 'destinations' );
				$item_confirm = __( 'master-pages', 'destinations' );
				$item_title = get_the_title( $_GET['post'] );
				$action_url_yes = admin_url( 'edit.php?post_type=destination&page=destination-settings&tab=master-pages&post='.$_GET['post'].'&action=master-delete&post_status='.$_GET['post_status'] );
				$action_url_no = admin_url( 'edit.php?post_type=destination&page=destination-settings&tab=master-pages' );

				require_once( get_template_directory().'/framework/templates/delete-confirmation.php' );
				exit;
			}

			if( isset( $_GET['action'] ) && isset( $_GET['post'] ) && ( $_GET['action'] == 'master-delete' ) ) {
				if( isset( $_GET['bulk'] ) && $_GET['bulk'] == 1 ) {
					$posts_for_delete = explode( ',', $_GET['post'] );
					foreach( $posts_for_delete as $id ) {
						$this->delete_master_guide_pages( $id, true );
					}
				} else {
					$this->delete_master_guide_pages( $_GET['post'], true );
				}
				echo( "<script>location.href = '".$url."';</script>" );
			}

			if( isset( $_POST['action'] ) && ( $_POST['action'] == 'confirm-master-delete' ) ) {
				$msg_confirm = __( 'Removing of master page will lead to the removing of all destination pages, associated with it.<br>', 'destinations' );
				$item_confirm = __( 'master-pages', 'destinations' );
				if( is_array( $_POST['guide'] ) ) {
					$posts_for_delete = implode( ',', $_POST['guide'] );
					foreach( $_POST['guide'] as $id ) {
						$item_title[] = get_the_title( $id );
					}
				}
				$action_url_yes = admin_url( 'edit.php?post_type=destination&page=destination-settings&tab=master-pages&bulk=1&post='.$posts_for_delete.'&action=master-delete' );
				$action_url_no = admin_url( 'edit.php?post_type=destination&page=destination-settings&tab=master-pages' );

				require_once( get_template_directory().'/framework/templates/delete-confirmation.php' );
				exit;
			}
		}

		public function display_master_pages( $tab ) {
			global $all_master_count, $publish_master_count, $draft_master_count, $trash_master_count;

			$m = ( isset( $_GET['m'] ) && ! empty( $_GET['m'] ) && $_GET['m'] != 0 ) ? $_GET['m'] : 0;   // filter for date
			$this->master_page_actions();

			$all_master_count = $this->get_count( $m );
			$publish_master_count = $this->get_count( $m, array( 'publish' ) );
			$draft_master_count = $this->get_count( $m, array( 'draft' ) );
			$trash_master_count = $this->get_count( $m, array( 'trash' ) );

			$query = array(
					'post_type' => 'master-pages',
					'posts_per_page' => -1,
					'meta_key' => 'master_order',
					'orderby' => 'meta_value_num',
					'order' => 'ASC',
				);

			if( isset( $_GET['filter_date'] ) && ! empty( $_GET['filter_date'] ) && $_GET['filter_date'] != 0 ) {
					$query['m'] = $_GET['filter_date'];
			}
			if( isset( $_GET['post_status'] ) && ! empty( $_GET['post_status'] ) ) {
					$query['post_status'] = ( $_GET['post_status'] == 'all' ) ? array( 'publish', 'draft' ) : array( $_GET['post_status'] );
					if( $_GET['post_status'] != 'trash' )
						$query['post_parent'] = 0;
			} else
					$query['post_parent'] = 0;

			$data_table = array();               	 // gather data for WP List Table
			$master_pages = new WP_Query( $query );  // Loop for all master pages

			if ( $master_pages->have_posts() ) {

				while ( $master_pages->have_posts() ) {

					$master_pages->the_post();
					$args = array(
						'numberposts' => -1,
						'post_parent' => $master_pages->post->ID,
						'meta_key' => 'master_order',
						'orderby' => 'meta_value_num',
						'order' => 'ASC',
						'post_type' => 'master-pages',
						'post_status' => array( 'publish' )
					);
					if( isset( $_GET['post_status'] ) && ! empty( $_GET['post_status'] ) ) {
						$args['post_status'] = ( $_GET['post_status'] == 'all' ) ? array( 'publish', 'draft' ) : array( $_GET['post_status'] );
					}

					$children = get_children( $args );

					$link = 'post.php?post='.$master_pages->post->ID.'&action=edit';
					$title_link = '<a href="'.$link.'" style="font-weight: bold; font-size:14px">' . $master_pages->post->post_title . '</a>';
					$user = get_user_by( 'id', $master_pages->post->post_author );
					$meta = get_post_meta( $master_pages->post->ID, 'master_order' );
					$order = ( isset( $meta[0] ) && ! empty( $meta[0] ) ) ? $meta[0] : 0;
					array_push( $data_table, array( 'ID' => $master_pages->post->ID, 'title' => $title_link, 'author' => $user->display_name, 'date' => date( 'F j, Y', strtotime( $master_pages->post->post_date ) ), 'order' => $order ) );

					if( count( $children ) )
						$data_table = $this->get_children_pages( $children, $data_table, 1, $master_pages->post->ID );
				}

			} else {
				// no posts found
			} ?>

			<div class="wrap master-pages">
				<?php $this->display_settings_tabs( $tab ); ?>
				<h2><?php _e( 'Guide Master Pages', 'destinations' ); ?> <a href="post-new.php?post_type=master-pages" class="add-new-h2"><?php _e( 'Add New', 'destinations '); ?></a> </h2>
				<p>
					<?php _e( 'These pages are placeholders for every destination to have unique content. Think of them as pre-defined structures for consistancy and updats across all destinations. After creating a Master Page it will show in the pages sections of every destination. You can open the page and add content specific to the destination or hide the page if it is not needed.', 'destinations' ); ?>
				</p>
				<?php
					$table = new Master_Pages_List_Table( $data_table ); ?>
					<form name="master-bulk-apply" action="" method="post">
						<input type="hidden" id="master-bulk-apply" value="master-bulk-apply"> <?php
						$table->views();
						$table->prepare_items();
						$table->display(); ?>
					</form>
			</div> <?php
		}
	}
}
