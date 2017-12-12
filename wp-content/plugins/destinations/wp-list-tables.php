<?php
if ( ! class_exists( 'Master_Pages_List_Table' ) ) {
	class Master_Pages_List_Table extends WP_List_Table {

		public function __construct( $data ) {
			$this->items = $data;
			$this->screen = get_current_screen();
		}

		function get_columns() {
			$columns = array(
				'cb'     => '<input type="checkbox" />',
				'title'  => __( 'Title', 'destinations' ),
				'author' => __( 'Author', 'destinations' ),
				'date'   => __( 'Date', 'destinations' ),
				'order'  => __( 'Order', 'destinations' )
			);
			return $columns;
		}

		function prepare_items() {
			$columns = $this->get_columns();
			$hidden = array();
			$sortable = $this->get_sortable_columns();
			$this->_column_headers = array( $columns, $hidden, $sortable );

			$per_page     = 10;
			$current_page = $this->get_pagenum();
			$total_items  = count($this->items);
			$this->set_pagination_args( array (
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page )
			) );

			if( count($this->items) )
				$this->items = array_slice( $this->items, ( ( $current_page-1) * $per_page ), $per_page );
		}

		function column_default( $item, $column_name ) {
			switch( $column_name ) {
				case 'title':
				case 'author':
				case 'date':
					return $item[ $column_name ];
				case 'order':
					return isset( $item[ $column_name ] ) ? $item[ $column_name ] : 0;
				default:
					return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
		  }
		}

		function get_sortable_columns() {
			$sortable_columns = array(
				'title'  => array( 'title', false ),
				'author' => array( 'author', false ),
				'date'   => array( 'date', false )
			);

			return $sortable_columns;
		}

		function column_title( $item ) {
			$is_disabled = is_master_page_disabled( $item['ID'] );
			$action = ( $is_disabled ) ? 'master-page-enable' : 'master-page-disable';
			$title = ( $is_disabled ) ? __( 'Enable', 'destinations' ) : __( 'Disable', 'destinations' );
			$current = ( isset( $_GET['post_status'] ) && ! empty( $_GET['post_status'] ) )? $_GET['post_status'] : 'all';
			$status = get_post_status( $item['ID'] );
			$post_status = '&post_status='.$current;
			$paged = isset( $_GET['paged'] ) ? '&paged='.$_GET['paged'] : '';

			if( $current == 'trash' ) {
				$actions = array(
					'restore'  => sprintf( '<a href="post.php?post=%s&action=%s&_wpnonce=%s'.$post_status.$paged.'">'.__( 'Restore', 'destinations' ).'</a>', $item['ID'], 'untrash', wp_create_nonce( 'untrash-post_' . $item['ID'] ) ),
					'delete'  => sprintf( '<a href="post.php?post=%s&action=%s&_wpnonce=%s">'.__( 'Delete Permanently', 'destinations' ).'</a>', $item['ID'], 'delete-master', wp_create_nonce( 'delete-post_' . $item['ID'] ) ),
				);
			} else {
				$actions = array(
					'edit'      => sprintf( '<a href="post.php?post=%s&action=%s">'.__( 'Edit', 'destinations' ).'</a>', $item['ID'], 'edit'),
				);
				// Disable only for publish master pages
				if ( $status == 'publish' )
					$actions['disable'] = sprintf( '<a class="enable" href="?post_type=destination&page=destination-settings&tab=master-pages&post=%s&action=%s'.$post_status.$paged.'">%s</a>', $item['ID'], $action, $title );
				$actions['trash'] = sprintf( '<a class="enable" href="?post_type=destination&page=destination-settings&tab=master-pages&post=%s&action=%s'.$post_status.$paged.'">%s</a>', $item['ID'], 'confirm-master-delete', __( 'Delete', 'destinations' ) );
			}
			// Title (mark if disabled)
			$item['title'] = apply_filters( 'get_qtranslate_rw', $item['title'] );
			$item_title  = ( $current == 'all' && $status == 'draft' ) ? $item['title'].' - <strong><span class="post-state">'.__( 'Draft', 'destinations' ).'</span></strong>' : $item['title'];
			$item_title .= ( $is_disabled ) ? ' ('. __( 'Disabled', 'destinations' ) .')' : '';

			return sprintf( '%1$s %2$s', $item_title, $this->row_actions( $actions ) );
		}

		function get_bulk_actions() {
			$current = ( isset( $_GET['post_status'] ) && ! empty( $_GET['post_status'] ) )? $_GET['post_status'] : 'all';
			if($current == 'trash') {
				$actions = array(
					'master-untrash'  => __( 'Restore', 'destinations' ),
					'master-delete'  => __( 'Delete Permanently', 'destinations' ),
				);
			}
			else {
				$actions = array(
					'master-disable' => __( 'Disable', 'destinations' ),
					'master-enable'  => __( 'Enable', 'destinations' ),
					'confirm-master-delete'  => __( 'Delete', 'destinations' ),
				);
			}

			return $actions;
		}

		function single_row( $item ) {
			$is_disabled = is_master_page_disabled( $item['ID'] );
			$class = ( $is_disabled ) ? 'disabled' : '';

			echo '<tr class="'.$class.' unapproved">';
			$this->single_row_columns( $item );
			echo '</tr>';
		}

		function column_cb( $item ) {
			return sprintf(
				'<input type="checkbox" name="guide[]" value="%s" />', $item['ID']
			);
		}

		function get_views() {
			global $all_master_count, $publish_master_count, $draft_master_count, $trash_master_count;
			$views = array();

			$current = ( isset( $_GET['post_status'] ) && ! empty( $_GET['post_status'] ) ) ? $_GET['post_status'] : 'all';

			$class = ( $current == 'all' ? ' class="current"' :'' );
			$views['all'] = "<a href='edit.php?post_type=destination&page=destination-settings&tab=master-pages&post_status=all' {$class} >".__( 'All', 'destinations' )." <span class='count'>(" . $all_master_count . ")</span></a>";

			if( $publish_master_count ) {
				$class = ( $current == 'publish' ? ' class="current"' :'' );
				$views['publish'] = "<a href='edit.php?post_type=destination&page=destination-settings&tab=master-pages&post_status=publish' {$class} >".__( 'Published', 'destinations' )." <span class='count'>(" . $publish_master_count . ")</span></a>";
			}
			if( $draft_master_count ) {
				$class = ( $current == 'draft' ? ' class="current"' :'' );
				$views['draft'] = "<a href='edit.php?post_type=destination&page=destination-settings&tab=master-pages&post_status=draft' {$class} >".__( 'Draft', 'destinations' )." <span class='count'>(" . $draft_master_count . ")</span></a>";
			}
			if( $trash_master_count ) {
				$class = ( $current == 'trash' ? ' class="current"' :'' );
				$views['trash'] = "<a href='edit.php?post_type=destination&page=destination-settings&tab=master-pages&post_status=trash' {$class} >".__( 'Trash', 'destinations' )." <span class='count'>(" . $trash_master_count . ")</span></a>";
			}

			return $views;
		}

		function extra_tablenav( $which ) {	?>

			<div class="alignleft actions"> <?php
				$this->months_dropdown( 'master-pages' );
				submit_button( __( 'Filter' ), 'secondary', false, false, array( 'name' => 'filter_action', 'id' => 'custom-post-query-submit' ) );	?>
			</div>
			<div class="alignleft actions"> <?php
				$current = ( isset( $_GET['post_status'] ) && ! empty( $_GET['post_status'] ) )? $_GET['post_status'] : 'all';
				if ( $current == 'trash' )
					submit_button( __( 'Empty Trash', 'destinations' ), 'secondary', false, false, array( 'name' => 'empty_trash_master', 'id' => 'empty_trash' ) );
				?>
			</div> 	<?php
		}

	}
}

if ( ! class_exists( 'Guide_Pages_List_Table' ) ) {
	class Guide_Pages_List_Table extends WP_List_Table {

		public function __construct( $data ) {
			$this->items = $data;
			$this->screen = get_current_screen();
		}

		function get_columns() {
			$columns = array(
				'cb'        => '<input type="checkbox" />',
				'title'     => __( 'Title', 'destinations' ),
				'author'    => __( 'Author', 'destinations' ),
				'date'      => __( 'Date', 'destinations' ),
				'order'     => __( 'Order', 'destinations' ),
			);
			return $columns;
		}

		function prepare_items() {
			$columns = $this->get_columns();
			$hidden = array();
			$sortable = $this->get_sortable_columns();
			$this->_column_headers = array( $columns, $hidden, $sortable );

			$per_page     = 10;
			$current_page = $this->get_pagenum();
			$total_items  = count( $this->items );
			$this->set_pagination_args( array (
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page )
			) );

			if( count($this->items) )
				$this->items = array_slice( $this->items, ( ( $current_page-1 ) * $per_page ), $per_page );
		}

		function column_default( $item, $column_name ) {
			switch( $column_name ) {
				case 'title':
				case 'author':
				case 'date':
					return $item[ $column_name ];
				case 'order':
					return get_guide_page_order( $item['ID'] );
				default:
					return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
		  }
		}

		function get_sortable_columns() {
			$sortable_columns = array(
				'title'  => array( 'title', false ),
				'author' => array( 'author', false ),
				'date'   => array( 'date', false )
			);

			return $sortable_columns;
		}

		function column_title( $item ) {
			global $post;

			if( $_GET['post'] )
				$post_id = $_GET['post'];
			else
				return;

			$is_disabled = is_guide_page_disabled( $item['ID'] );
			$action = ( $is_disabled ) ? 'guide-page-enable' : 'guide-page-disable';
			$title = ( $is_disabled ) ? __( 'Enable', 'destinations' ) : __( 'Disable', 'destinations' );
			$is_single_page = get_guide_page_no_master( $item['ID'] );
			$current = ( isset( $_GET['post_status'] ) && ! empty( $_GET['post_status'] ) ) ? $_GET['post_status'] : 'all';
			$status = get_post_status( $item['ID'] );
			$post_status = '&post_status='.$current;
			$paged = isset( $_GET['paged'] ) ? '&paged='.$_GET['paged'] : '';

			if ( $current == 'trash' ) {
				$actions = array(
					'restore'  => sprintf( '<a href="post.php?post=%s&action=%s&_wpnonce=%s'.$post_status.$paged.'">'.__( 'Restore', 'destinations' ).'</a>', $item['ID'], 'untrash', wp_create_nonce( 'untrash-post_' . $item['ID'] ) ),
					'delete'  => sprintf( '<a href="post.php?post=%s&action=%s&_wpnonce=%s">'.__( 'Delete Permanently', 'destinations' ).'</a>', $item['ID'], 'delete', wp_create_nonce( 'delete-post_' . $item['ID'] ) ),
				);
			} else {
				$actions = array(
					'edit'      => sprintf( '<a href="post.php?post=%s&action=%s">'.__( 'Edit', 'destinations' ).'</a>', $item['ID'], 'edit' ),
				);
			}
			// Disable only for publish pages
			if ( $status == 'publish' )
				$actions['disable'] = sprintf( '<a class="enable" href="?post_type=destination&page=guide-pages&tab=pages&post=%s&action=%s&item=%s'.$post_status.$paged.'">%s</a>', $post_id, $action, $item['ID'], $title );

			// Delete action for non-master pages
			if ( $is_single_page && $current != 'trash' )
				$actions['trash'] = sprintf( '<a href="post.php?post=%s&action=%s&_wpnonce=%s">'.__( 'Delete', 'destinations' ).'</a>', $item['ID'], 'trash', wp_create_nonce( 'trash-post_' . $item['ID'] ) );

			// Title (mark if disabled)
			$item['title'] = apply_filters( 'get_qtranslate_rw', $item['title'] );
			$item_title  = ( $current == 'all' && $status == 'draft' ) ? $item['title'].' - <strong><span class="post-state">'.__( 'Draft', 'destinations' ).'</span></strong>' : $item['title'];
			$item_title .= ( $is_disabled ) ? ' ('. __(' Disabled', 'destinations' ) .')' : '';

			return sprintf( '%1$s %2$s', $item_title, $this->row_actions( $actions ) );
		}

		function get_bulk_actions() {
			$current = (  isset( $_GET['post_status'] ) && ! empty( $_GET['post_status'] ) )? $_GET['post_status'] : 'all';

			if($current == 'trash') {
				$actions = array(
					'guide-untrash'  => __( 'Restore', 'destinations' ),
					'guide-delete'  => __( 'Delete Permanently', 'destinations' ),
				);
			}
			else {
				$actions = array(
					'guide-disable' => __( 'Disable', 'destinations' ),
					'guide-enable'  => __( 'Enable', 'destinations' ),
				);
			}

			return $actions;
		}

		function single_row( $item ) {
			$is_disabled = is_guide_page_disabled( $item['ID'] );
			$class = ( $is_disabled ) ? 'disabled' : '';

			echo '<tr class="'.$class.' unapproved">';
			$this->single_row_columns( $item );
			echo '</tr>';
		}

		function column_cb( $item ) {
			return sprintf(
				'<input type="checkbox" name="guide[]" value="%s" />', $item['ID']
			);
		}

		function get_views() {
			global $all_guide_pages_count, $publish_guide_pages_count, $draft_guide_pages_count, $trash_guide_pages_count;
			$views = array();

			$current = ( isset( $_GET['post_status'] ) && ! empty( $_GET['post_status'] ) ) ? $_GET['post_status'] : 'all';

			$class = ( $current == 'all' ? ' class="current"' :'' );
			$views['all'] = "<a href='edit.php?post_type=destination&page=guide-pages&post=".$_GET['post']."&tab=pages&post_status=all' {$class} >".__( 'All', 'destinations' )." <span class='count'>(" . $all_guide_pages_count . ")</span></a>";

			if( $publish_guide_pages_count ) {
				$class = ( $current == 'publish' ? ' class="current"' :'' );
				$views['publish'] = "<a href='edit.php?post_type=destination&page=guide-pages&post=".$_GET['post']."&tab=pages&post_status=publish' {$class} >".__( 'Published', 'destinations' )." <span class='count'>(" . $publish_guide_pages_count . ")</span></a>";
			}
			if( $draft_guide_pages_count ) {
				$class = ( $current == 'draft' ? ' class="current"' :'' );
				$views['draft'] = "<a href='edit.php?post_type=destination&page=guide-pages&post=".$_GET['post']."&tab=pages&post_status=draft' {$class} >".__( 'Draft', 'destinations' )." <span class='count'>(" . $draft_guide_pages_count . ")</span></a>";
			}
			if( $trash_guide_pages_count ) {
				$class = ( $current == 'trash' ? ' class="current"' :'' );
				$views['trash'] = "<a href='edit.php?post_type=destination&page=guide-pages&post=".$_GET['post']."&tab=pages&post_status=trash' {$class} >".__( 'Trash', 'destinations' )." <span class='count'>(" . $trash_guide_pages_count . ")</span></a>";
			}

		   return $views;
		}

		function extra_tablenav( $which ) {	?>

			<div class="alignleft actions"> <?php
				$this->months_dropdown( 'master-pages' );
				submit_button( __( 'Filter', 'destinations' ), 'secondary', false, false, array( 'name' => 'filter_action', 'id' => 'custom-post-query-submit' ) );	?>
			</div>
			<div class="alignleft actions"> <?php
				$current = ( isset( $_GET['post_status'] ) && ! empty( $_GET['post_status'] ) )? $_GET['post_status'] : 'all';
				if ( $current == 'trash' )
					submit_button( __( 'Empty Trash', 'destinations' ), 'secondary', false, false, array( 'name' => 'empty_trash_page', 'id' => 'empty_trash' ) );
				?>
			</div> 	<?php
		}

	}
}
