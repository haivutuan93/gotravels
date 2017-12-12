<?php
if ( ! function_exists( 'set_rating_settings' ) ) :
function set_rating_settings( $rating ) {

	$rating['settings']['star'] = array( 'class' => 'fa fa-star', 'color' => '#dd9933', 'class-menu' => 'fa fa-fw fa-star', 'class-menu-half' => 'fa fa-fw fa-star-half-o', 'class-menu-empty' => 'fa fa-fw fa-star-o');
	$rating['settings']['usd'] = array( 'class' => 'fa fa-usd', 'color' => '#6cbc3a', 'class-menu' => 'fa fa-fw fa-usd', 'class-menu-half' => 'fa fa-fw fa-usd', 'class-menu-empty' => 'fa fa-fw fa-usd', 'style' => 'opacity:.33');

	return $rating;
}
endif;
add_filter('rating_settings', 'set_rating_settings' );

if ( ! function_exists( 'get_pages_cpt' ) ) :
function get_pages_cpt( $post_id ) {
	$cpt = 'destination-page';
	return $cpt;
}
endif;

if ( ! function_exists( 'create_parent_dest_slug' ) ) :
function create_parent_dest_slug( $post, $is_slug = false ) {
	if( ! is_object( $post ) )
		return '';

	$parents = get_post_ancestors( $post->ID );
	$parents_sort = array_reverse( $parents );

	$slugs = array();
	foreach( $parents_sort as $parent_id ) {
		$parent_post = get_post( $parent_id );
		$slugs[] = $parent_post->post_name;
	}
	$slugs[] = $post->post_name;

	$separator = ( $is_slug ) ? '/' : '-';
	return implode( $separator, $slugs );
}
endif;

if ( ! function_exists( 'create_parent_slug' ) ) :
function create_parent_slug( $post, $is_slug = false, $dest_name = '' ) {
	if( ! is_object( $post ) )
		return '';

	$parents = get_post_ancestors( $post->ID );
	$parents_sort = array_reverse( $parents );

	$slugs = array();
	foreach( $parents_sort as $parent_id ) {
		$master_id = get_master_parent( $parent_id );
		if( $master_id ) {
			$master = get_post( $master_id );
			$slugs[] = $dest_name.'-'.$master->post_name;
		}
	}

	$separator = ( $is_slug ) ? '/' : '-';
	return implode( $separator, $slugs );
}
endif;

if ( ! function_exists( 'get_parent_slug' ) ) :
function get_parent_slug( $post, $is_slug = false ) {
	if( ! is_object( $post ) )
		return '';

	$parents = get_post_ancestors( $post->ID );
	$parents_sort = array_reverse( $parents );

	$slugs = array();
	foreach( $parents_sort as $parent_id ) {
		$parent_post = get_post( $parent_id );
		$slugs[] = $parent_post->post_name;
	}
//out($slugs);
	$separator = ( $is_slug ) ? '/' : '-';

	return implode( $separator, $slugs );
}
endif;

if ( ! function_exists( 'create_post_slug' ) ) :
function create_post_slug( $post ) {
	$master_id = get_master_parent( $post->ID );
	$master = get_post( $master_id );
	$slug = $master->post_name;

	return $slug;
}
endif;

if ( ! function_exists( 'create_parent_front_slug' ) ) :
function create_parent_front_slug( $post, $is_slug = false ) {
	$parents = get_post_ancestors( $post->ID );

	$parents_sort = array_reverse( $parents );

	$slugs = array();
	foreach( $parents_sort as $parent_id ) {
		$master_id = get_master_parent( $parent_id );
		$master = get_post( $master_id );
		$slugs[] = $master->post_name;
	}
	$master_id = get_master_parent( $post->ID );
	$master = get_post( $master_id );
	$slugs[] = $master->post_name;

	return implode( '-', $slugs );
}
endif;

if ( ! function_exists( 'create_parent_slug_nomaster' ) ) :
function create_parent_slug_nomaster( $post, $is_slug = false ) {
	if( ! is_object( $post ) )
		return '';
	$parents = get_post_ancestors( $post->ID );
	$parents_sort = array_reverse( $parents );

	$dest_id = get_guide_page_parent( $post->ID );
	$dest = get_post( $dest_id );
	$dest_name = create_parent_dest_slug( $dest );

	$slugs = array();
	foreach( $parents_sort as $parent_id ) {
		$parent_post = get_post( $parent_id );
		$slugs[] = $parent_post->post_name;
	}

	$separator = ( $is_slug ) ? '/' : '-';
	return implode( $separator, $slugs );
}
endif;

if ( ! function_exists( 'create_parent_front_slug_nomaster' ) ) :
function create_parent_front_slug_nomaster( $post, $is_slug = false ) {
	$parents = get_post_ancestors( $post->ID );
	$parents_sort = array_reverse( $parents );

	$dest_id = get_guide_page_parent( $post->ID );
	$dest = get_post( $dest_id );
	$dest_name = create_parent_dest_slug( $dest );

	$slugs = array();
	foreach( $parents_sort as $parent_id ) {
		$parent_post = get_post( $parent_id );
		$parent_post_name = $parent_post->post_name;
		$parent_post_name = apply_filters( 'wpml_ge_del_suffix', $parent_post_name );
		$slugs[] = preg_replace( '/'.$dest_name.'-/', '', $parent_post_name, 1 );
	}
	$post_name = $post->post_name;
	$post_name = apply_filters( 'wpml_ge_del_suffix', $post_name );

	$slugs[] = preg_replace( '/'.$dest_name.'-/', '', $post_name, 1 ); //$post->post_name;
	return implode( '-', $slugs );
}
endif;

if ( ! function_exists( 'destination_pages_list' ) ) :
function destination_pages_list( $id = 0, $args = array() ) {
	global $post;

	$id = ( $id == 0 ) ? $post->ID : $id;
	$query = array(
				'post_type' => get_pages_cpt( $id ),
				'posts_per_page' => -1,
			);
	$pages = get_posts( $query );
	$pages = get_page_hierarchy( $pages, $id );
	$start = count( get_post_ancestors( key( $pages ) ) );

	$output = "";

	$defaults = array(
		'mode' => 'list',     // or dropdown
		'size' => '4',        // min size for list
		'class' => 'postform'
	);

	$r = wp_parse_args( $args, $defaults );
	$mode = esc_attr( $r['mode'] );
	$size = esc_attr( $r['size'] );
	$class = esc_attr( $r['class'] );

	if ( ! empty( $pages ) ) {
		$output.= '<select class="'.$class.'"'. ( ( $mode == 'list' ) ? ' size="'.$size.'"' : "").' >';
		foreach ( $pages as $id => $item ) {
			$level = count( get_post_ancestors( $id ) );
			$output.= '<option value="' . esc_attr( $id ). '">' . esc_html( str_repeat( '&nbsp;&nbsp;&nbsp;&nbsp;', $level - $start ) . get_the_title( $id ) ) . '</option>';
		}
		$output.= '</select>';
	}

	echo $output;
}
endif;

if ( ! function_exists( 'destination_list' ) ) :
function destination_list( $id = 0, $args = array() ) {
	global $post;

	$id = ( $id == 0 )? $post->ID : $id;
	$query = array(
				'post_type' => 'destination',
				'posts_per_page' => -1,
			);
	$pages = get_posts( $query );
	$pages = get_page_hierarchy( $pages, $id );
	$start = count( get_post_ancestors( key( $pages ) ) );

	$output = "";

	$defaults = array(
		'mode'  => 'list',    // or dropdown
		'size'  => '4',       // min size for list
		'class' => 'postform'
	);

	$r = wp_parse_args( $args, $defaults );
	$mode = esc_attr( $r['mode'] );
	$size = esc_attr( $r['size'] );
	$class = esc_attr( $r['class'] );

	if ( ! empty( $pages ) ) {
		$output.= '<select class="'.$class.'"'. ( ( $mode == 'list' ) ? ' size="'.$size.'"' : "" ).' >';
		foreach ( $pages as $id => $item ) {
			$level = count( get_post_ancestors( $id ) );
			$output.= '<option value="' . esc_attr( $id ). '">' . esc_html( str_repeat( '&nbsp;&nbsp;&nbsp;&nbsp;', $level - $start ) . get_the_title( $id ) ) . '</option>';
		}
		$output.= '</select>';
	}

	echo $output;
}
endif;

if ( ! function_exists( 'get_guide_page_level' ) ) :
function get_guide_page_level( $post_id ) {
	$meta = get_post_meta( $post_id, 'guide_page_level' );
	$level = ( isset( $meta[0] ) && ! empty( $meta[0] ) )? $meta[0] : 0;

	return $level;
}
endif;

if ( ! function_exists( 'get_guide_page_parent' ) ) :
function get_guide_page_parent( $post_id ) {
	$meta = get_post_meta( $post_id, 'destination_parent_id' );
	$id = ( isset( $meta[0] ) && ! empty( $meta[0] ) )? $meta[0] : 0;

	return $id;
}
endif;

if ( ! function_exists( 'get_master_parent' ) ) :
function get_master_parent( $post_id ) {
	$meta = get_post_meta( $post_id, 'master_parent_id' );
	$id = ( isset( $meta[0] ) && ! empty( $meta[0] ) )? $meta[0] : 0;

	return $id;
}
endif;

if ( ! function_exists( 'get_guide_pages_slugs' ) ) :
function get_guide_pages_slugs( $post_id ) {
	$post = get_post( $post_id );
	$meta = get_post_meta( $post_id, 'pages_cpt_slug_' . $post->post_name );
	$slugs = ( isset( $meta[0] ) && ! empty( $meta[0] ) )? $meta[0] : '';

	return json_decode( $slugs );
}
endif;

if ( ! function_exists( 'get_guide_pages_slugs_new' ) ) :
function get_guide_pages_slugs_new( $post_id ) {
	$post = get_post( $post_id );
	$meta = get_post_meta( $post_id, 'pages_cpt_slug' );
	if( isset( $meta[0] ) && ! empty( $meta[0] ) ) {
		$slugs = json_decode( $meta[0] );
	} else {
		$slugs = new stdClass();
	}

	return $slugs;
}
endif;

if ( ! function_exists( 'get_guide_page_no_master' ) ) :
function get_guide_page_no_master( $post_id ) {
	$meta = get_post_meta( $post_id, 'no_master' );
	$val = ( isset( $meta[0] ) && ! empty( $meta[0] ) )? $meta[0] : 0;

	return $val;
}
endif;

if ( ! function_exists( 'get_guide_page_GUI' ) ) :
function get_guide_page_GUI( $post_id ) {
	$meta = get_post_meta( $post_id, 'destination_master_GUI' );
	$val = ( isset( $meta[0] ) && ! empty( $meta[0] ) ) ? $meta[0] : 0;

	return $val;
}
endif;

if ( ! function_exists( 'get_destination_gmaps_options' ) ) :
function get_destination_gmaps_options( $post_id ) {
	$options = get_destination_options( $post_id );
	$intro = get_post_meta( $post_id, 'destination_intro' );

	$google_map = ( isset( $options['google_map'] ) && ! empty( $options['google_map'] ) ) ? $options['google_map'] : array();

	$attrs = array();
	$attrs['latitude'] = isset( $google_map['latitude'] ) ? $google_map['latitude'] : '';
	$attrs['longitude'] = isset( $google_map['longitude'] ) ? $google_map['longitude'] : '';
	$attrs['zoom'] = isset( $google_map['zoom'] ) ? $google_map['zoom'] : '';
	$attrs['show_directory_pins'] = ( isset( $google_map['show_directory_pins'] ) && $google_map['show_directory_pins'] == 'true' ) ? true : false;
	$attrs['show_child_pins'] = ( isset($google_map['show_child_pins'] ) && $google_map['show_child_pins'] == 'true' ) ? true : false;
	$attrs['show_current_pin'] = ( isset($google_map['show_current_pin'] ) && $google_map['show_current_pin'] == 'true' ) ? true : false;
	$attrs['title'] = get_the_title( $post_id );
	$attrs['intro'] = ( isset( $intro[0] ) && ! empty( $intro[0] ) ) ? $intro[0] : '';
	$attrs['link'] = get_the_permalink( $post_id );
	if( has_post_thumbnail( $post_id ) ) {
		$attachment_id = get_post_thumbnail_id( $post_id );
		$img = wp_get_attachment_image_src( $attachment_id, 'medium' );
		$attrs['image'] = '<img src="'. esc_url( $img[0] ).'" width="'.$img[1].'" height="'.$img[2].'">';
		$attrs['image_src'] = $img[0];
	} else {
		$attrs['image'] = '';
		$attrs['image_src'] = '';
	}

	return $attrs;
}
endif;

if ( ! function_exists( 'has_gmaps_location' ) ) :
function has_gmaps_location( $post_id ) {

	// $dest_id = get_the_destination_ID($post_id);
	// $options = get_destination_options( $dest_id );

	if ( get_post_type( $post_id ) == 'destination' ) {
		$options = get_destination_options( $post_id );
	// } elseif (get_post_type($post_id) == 'destination-page') {
		// $dest_id = get_the_destination_ID($post_id);
		// $options = get_destination_options( $dest_id );
	} elseif( get_post_type( $post_id ) == 'travel-directory' ) {
		$options = get_meta_guide_lists_details( $post_id );
	} else {
		// a generic check for postmeta...
	}
	return ( ! empty( $options['google_map']['longitude'] ) && ! empty( $options['google_map']['latitude'] ) ) ? true : false;
}
endif;

if ( ! function_exists( 'show_destination_map' ) ) :
function show_destination_map( $post_id ) {
	// If page doesn't have headers
	if ( ! rf_has_custom_header() )
		return false;

	// Current page
	if ( has_gmaps_location( $post_id ) )
		return $post_id;

	// Parent Destination
	$dest_id = get_the_destination_ID( $post_id );
	if ( has_gmaps_location( $dest_id ) )
		return $dest_id;

	// otherwise...
	return false;
}
endif;

if ( ! function_exists( 'get_directory_gmaps_options' ) ) :
function get_directory_gmaps_options( $post_id ) {
	$options = get_meta_guide_lists_details( $post_id );
	$intro = '';
	$intro_text = get_post_meta( $post_id, 'guide_lists_intro' );
	if ( is_array( $intro_text ) ) {
		foreach ( $intro_text as $text ) {
			if ( ! empty( $text ) ) {
				$intro = $text;
			}
		}
	}

	$google_map = ( isset( $options['google_map'] ) && ! empty( $options['google_map'] ) ) ? $options['google_map'] : array();
	$attrs = array();
	$attrs['latitude'] = isset( $google_map['latitude'] ) ? $google_map['latitude'] : '';
	$attrs['longitude'] = isset( $google_map['longitude'] ) ? $google_map['longitude'] : '';
	$attrs['zoom'] = isset( $google_map['zoom'] ) ? $google_map['zoom'] : '';
	$attrs['title'] = get_the_title( $post_id );
	$attrs['intro'] = $intro;
	$attrs['link'] = get_the_permalink( $post_id );
	if(has_post_thumbnail( $post_id )) {
		$attachment_id = get_post_thumbnail_id( $post_id );
		$img = wp_get_attachment_image_src( $attachment_id, 'medium' );
		$attrs['image'] = '<img src="'.esc_url( $img[0] ).'" width="'.$img[1].'" height="'.$img[2].'">';
		$attrs['image_src'] = $img[0];
	} else {
		$attrs['image'] = '';
		$attrs['image_src'] = '';
		$url = '';
	}

	$attrs['rating'] = '';
	$rating_data = get_guide_lists_rating( $post_id );
	$ratings = array();
	if ( isset( $rating_data['enabled'] ) && ! empty( $rating_data['enabled'] ) ) {
		foreach ( $rating_data['enabled'] as $type => $enabled ) {

			if ( $type == 'menu_order' || $enabled !== 'true' )
				continue;

			$key = str_replace( 'rating_types_', '', $type );
			if ( isset( $rating_data['settings'][$key] ) && isset( $rating_data[$type] ) ) {
				$ratings[$key] = $rating_data['settings'][$key];
				$ratings[$key]['value'] = $rating_data[$type];
			}
		}
	}
	if ( ! empty( $ratings )) {
		ob_start();
		foreach ( $ratings as $key => $data ) {
			?>
			<div class="rating-container">
				<span class="rating <?php echo 'rating-'. esc_attr( $key ); ?>">
					<div class="ratebox " data-id="<?php echo '-'. esc_attr( $key ); ?>" data-rating="<?php echo esc_attr( $data['value'] ); ?>" data-state="rated"></div>
					<input type="hidden" class="rate-class"  value="<?php echo esc_attr( $data['class'] ); ?>">
					<input type="hidden" class="rate-color"  value="<?php echo esc_attr( $data['color'] ); ?>">
					<input type="hidden" class="rating-is-front"  value="true">
					<span class="infobox-value-rating"><?php echo $data['value']; ?></span>
				</span>
			</div>
			<?php
		}
		$attrs['ratings'] = ob_get_clean();
	}

	return $attrs;
}
endif;

if ( ! function_exists( 'get_children_destination_gmaps_options' ) ) :
function get_children_destination_gmaps_options( $post_id, $pins ) {
	$children = get_children( $post_id );
	if( count( $children ) > 0 ) {
		foreach( $children as $child ) {
			$pins[$child->ID] = get_destination_gmaps_options( $child->ID );
			$pins = get_children_destination_gmaps_options( $child->ID, $pins );
		}
	}
	return $pins;
}
endif;

if ( ! function_exists( 'get_children_directory_gmaps_options' ) ) :
function get_children_directory_gmaps_options( $post_id, $pins ) {
	$children = get_children( $post_id );
	if( count( $children ) > 0 ) {
		foreach( $children as $child ) {
			$args = array(
				'post_type' => 'travel-directory',
				'posts_per_page' => -1,
				'meta_query' => array(
					array(
						'key' => 'destination_parent_id',
						'value' => $child->ID
					)
				)
			);
			$items = get_posts( $args );
			foreach( $items as $item ) {
				$pins[$item->ID] = get_directory_gmaps_options( $item->ID );
			}
			$pins = get_children_directory_gmaps_options( $child->ID, $pins );
		}
	}
	return $pins;
}
endif;

if ( ! function_exists( 'is_master_page_disabled' ) ) :
function is_master_page_disabled( $post_id ) {
	$meta = get_post_meta( $post_id, 'is_disabled_master_page' );
	$id = ( isset( $meta[0] ) && ! empty( $meta[0] ) ) ? $meta[0] : 0;

	return $id;
}
endif;

if ( ! function_exists( 'is_guide_page_disabled' ) ) :
function is_guide_page_disabled( $post_id ) {
	$meta = get_post_meta( $post_id, 'is_disabled_guide_page' );
	$id = ( isset( $meta[0] ) && ! empty( $meta[0] ) ) ? $meta[0] : 0;

	return $id;
}
endif;

if ( ! function_exists( 'get_children_nomaster_pages' ) ) :
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
endif;

if ( ! function_exists( 'clear_guide_page_slug' ) ) :
function clear_guide_page_slug( $slugs, $slug ) {
	if( ! empty( $slugs ) ) {
		foreach( $slugs as $key => $val ) {
			if( $val == $slug ) {
				$parts = explode( '/', $key );
				$post_name = $parts[count( $parts ) - 1];
				$id = get_id_by_post_name( $post_name );
				//if(! $id) {
					unset( $slugs->$key );
				//}
			}
		}
	}

	return $slugs;
}
endif;

if ( ! function_exists( 'wpml_ge_add_suffix_rw' ) ) :
function wpml_ge_add_suffix_rw( $name, $lang = '' ) {
	if( defined( 'ICL_LANGUAGE_CODE' ) ) {
		$lang = empty($lang)? ICL_LANGUAGE_CODE : $lang;
		if( substr( $name, strlen( $name )-6 ) != '-'.$lang.'-ge' ) {
				$name = $name . '-'.$lang.'-ge';
		}
	}
	return $name;
}
add_filter( 'wpml_ge_add_suffix', 'wpml_ge_add_suffix_rw', 10, 2 );
endif;

if ( ! function_exists( 'wpml_ge_del_suffix_rw' ) ) :
function wpml_ge_del_suffix_rw( $name ) {
	if( defined( 'ICL_LANGUAGE_CODE' ) && substr( $name, strlen( $name )-6 ) == '-'.ICL_LANGUAGE_CODE.'-ge' ) {
		$name = substr( $name, 0, strlen( $name )-6 );
	}
	return $name;
}
add_filter( 'wpml_ge_del_suffix', 'wpml_ge_del_suffix_rw' );
endif;

if ( ! function_exists( 'get_icl_parent_front_slug_for_new_post' ) ) :
function get_icl_parent_front_slug_for_new_post( $icl_trid, $dest_name ) {
	$id_default = get_destination_page_id_by_trid($icl_trid, apply_filters( 'wpml_default_language', NULL ) );
	$id_default_master = get_master_parent( $id_default );
	$id_master_translated = (int)apply_filters( 'wpml_object_id', $id_default_master, get_post_type( $id_default_master ), false, ICL_LANGUAGE_CODE );
	$id_parent_default = wp_get_post_parent_id( $id_default );
 	$parents_sort = array();
 	
 	$id_parent = (int)apply_filters( 'wpml_object_id', $id_parent_default, get_post_type( $id_parent_default ), false, ICL_LANGUAGE_CODE );
 	if( $id_parent ) {
 	   $parents = get_post_ancestors( $id_parent );
 	   array_unshift( $parents, $id_parent );
 	   $parents_sort = array_reverse( $parents );
 	}

	$slugs = array();
	foreach( $parents_sort as $parent_id ) {
		$master_id = get_master_parent( $parent_id );
		$master = get_post( $master_id );
		$slugs[] = $master->post_name;
	}

	return implode('/', $slugs);
}
endif;

if ( ! function_exists( 'get_icl_parents_nomaster' ) ) :
function get_icl_parents_nomaster( $icl_trid ) {
	$id_default = get_destination_page_id_by_trid($icl_trid, apply_filters( 'wpml_default_language', NULL ) );
	$id_parent_default = wp_get_post_parent_id( $id_default );
	$id_parent = (int)apply_filters( 'wpml_object_id', $id_parent_default, get_post_type( $id_parent_default ), false, ICL_LANGUAGE_CODE );
	$parents_sort = get_parents_common( $id_parent );

	return $parents_sort;
}
endif;

if ( ! function_exists( 'get_parents_common' ) ) :
function get_parents_common( $post_id ) {
	$parents = get_post_ancestors( $post_id );
	if( $post_id )
		array_unshift( $parents, $post_id );
	$parents_sort = array_reverse( $parents );

	return $parents_sort;
}
endif;

if ( ! function_exists( 'get_icl_parent_slug_for_new_post' ) ) :
function get_icl_parent_slug_for_new_post( $icl_trid, $dest_name, $slugs ) {
	$parents_sort = get_icl_parents_nomaster( $icl_trid );
	$item_slugs = array();
	foreach( $parents_sort as $parent_id ) {
		$parent = get_post( $parent_id );
		$item_slugs[] = $parent->post_name;
	}

	return implode('/', $item_slugs);
}
endif;

if ( ! function_exists( 'get_icl_parent_front_slug_for_new_post_nomaster' ) ) :
function get_icl_parent_front_slug_for_new_post_nomaster( $icl_trid, $dest_slug, $slugs ) {
	$parents_sort = get_icl_parents_nomaster( $icl_trid );
	$front_slug = '';
	foreach( $parents_sort as $parent_id ) {
		$parent = get_post( $parent_id );
		$parent_slug = create_parent_slug_nomaster( $parent, true );
		$current_slug = empty( $parent_slug ) ? $parent->post_name : $parent_slug . '/'. $parent->post_name;
		$front_slug = str_replace($dest_slug.'/', '', $slugs->$current_slug);
	}

	return $front_slug;
}
endif;

if ( ! function_exists( 'fix_incorrect_info_page_permalinks' ) ) :
function fix_incorrect_info_page_permalinks( $post ) {

	$dest_id = get_guide_page_parent( $post->ID );
	if ( ! $dest_id ) {
		return;
	}

	$slugs       = get_guide_pages_slugs_new( $dest_id );
	$slugs_array = get_object_vars( $slugs );

	$dest      = get_post( $dest_id );
	$dest_slug = create_parent_dest_slug( $dest, true );
	$dest_name = create_parent_dest_slug( $dest, false );

	$post_slug   = $post->post_name;
	$parent_id   = $post->post_parent;
	$post_name   = sanitize_title( $post->post_title );
	$is_nomaster = get_master_parent( $post->ID ) ? false : true;

	if ( $parent_id ) {
		$parent_key = get_parent_slug( $post, true );
		if ( ! property_exists( $slugs, $parent_key ) ) {
			return;
		}

		if ( $is_nomaster ) {
			$parts             = explode( '/', $slugs->$parent_key );
			$parent_front_slug = $parts[ count( $parts ) - 1 ];
			$post_front_slug   = $parent_front_slug . '-' . $post_name;
			$post_parent_slug  = create_parent_slug_nomaster( $post, true );
		} else {
			$post_front_slug  = str_replace( $dest_slug . '/', '', $slugs->$parent_key ) . '-' . $post_name;
			$post_parent_slug = get_parent_slug( $post, true );
		}
	} else {
		$icl_trid = get_trid_by_ID( $post->ID );
		if ( $is_nomaster ) {
			if ( $icl_trid ) {
				$icl_front_slug_for_new_post = get_icl_parent_front_slug_for_new_post_nomaster( $icl_trid, $dest_slug, $slugs );
				$post_front_slug             = ! empty( $icl_front_slug_for_new_post ) ? $icl_front_slug_for_new_post . '-' . $post_name : $post_name;
			} else {
				$post_front_slug = $post_name;
			}
		} else {
			//check if master parent exists
			$parents = get_post_ancestors( $post->ID );
			$parents_sort = array_reverse( $parents );
			foreach( $parents_sort as $parent_id ) {
				$master_id = get_master_parent( $parent_id );
				if ( get_post_status( $master_id ) === false ) {
					return;
				}
			}
			$master_id = get_master_parent( $post->ID );
			if ( get_post_status( $master_id ) === false ) {
				return;
			}
			// end check

			if ( $icl_trid ) {
				$icl_parent_front_slug_for_new_post = get_icl_parent_front_slug_for_new_post( $icl_trid, $dest_name );
				$post_front_slug                    = ! empty( $icl_parent_front_slug_for_new_post ) ? $icl_parent_front_slug_for_new_post . '-' . $post_name : $post_name;
			} else {
				$post_front_slug = create_parent_front_slug( $post );// front slug (new master)
			}
		}

		$post_parent_slug = '';
	}

	if ( empty( $post_parent_slug ) ) {
		$key = $post_slug;
	} else {
		$key = $post_parent_slug . '/' . $post_slug;
	}

	$slug = $dest_slug . '/' . $post_front_slug;

	if ( ! array_key_exists( $key, $slugs_array ) ) {
		// save changes
		if ( get_post_meta( $dest_id, 'pages_cpt_slug_reserve', true ) == false ) {
			update_post_meta( $dest_id, 'pages_cpt_slug_reserve', json_encode( $slugs ) );
		}

		// needed to add new slug
		if ( $is_nomaster ) {
			$slug = sanitize_guide_page_slug( $slugs_array, $slug );
		} else {
			if ( $exist_key = array_search( $slug, $slugs_array ) ) {
				//rename existing slug if it is no_master
				$existing_page = get_posts( array(
					'name'           => $exist_key,
					'post_type'      => 'destination-page',
					'posts_per_page' => 1
				) );

				$existing_page = ( count( $existing_page ) > 0 ) ? $existing_page[0] : null;

				if ( ! is_null( $existing_page ) && get_master_parent( $existing_page->ID ) ) {
					// existing page is master-page
					// do nothing
					return;
				}

				// mod existing page slug
				$slugs->$exist_key = sanitize_guide_page_slug( $slugs_array, $slug );
			}
		}

		$slugs = (object) array_merge( (array)$slugs, array( $key => $slug ) );

		update_post_meta( $dest_id, 'pages_cpt_slug', json_encode( $slugs ) );
	}

}
endif;

if ( ! function_exists( 'set_guide_pages_slugs' ) ) :
function set_guide_pages_slugs( $dest_id, $post, $is_nomaster = false, $is_update = false ) {

	$slugs = get_guide_pages_slugs_new( $dest_id );

	$dest = get_post( $dest_id );
	$dest_slug = create_parent_dest_slug( $dest, true );
	$dest_name = create_parent_dest_slug( $dest, false );
	$parent_id = $post->post_parent;

	$post_name = $is_update ? str_replace($dest_name.'-', '', $post->post_name) : $post->post_name;
	if( $parent_id ) {
		$parent_key = get_parent_slug( $post, true );
		if( $is_nomaster ) {
			$parts = explode( '/', $slugs->$parent_key);
			$parent_front_slug = $parts[count($parts) - 1];
			if( $is_update ) {
				$complex_key = $parent_key.'/'.$post_name;
				$parts = explode( '/', $slugs->$complex_key);
				$post_front_slug = $parts[count($parts) - 1];												// front slug (update nomaster) - child
			} else {
				$post_front_slug = $parent_front_slug.'-'.$post_name;										// front slug (new nomaster) - child
			}
			$post_parent_slug = create_parent_slug_nomaster( $post, true ); 								// parent slug (nomaster) - child
		} else {
			if( $is_update ) {
				$post_front_slug = create_parent_front_slug( $post );										// front slug (update master) - child
			} else {
				$post_front_slug = str_replace($dest_slug.'/', '', $slugs->$parent_key) .'-'. $post_name; 	// front slug (new master) - child
			}
			$post_parent_slug = get_parent_slug( $post, true );
		}
	} else {
		if( $is_nomaster ) {
			if( $is_update ) {
				$parts = explode( '/', $slugs->$post_name);
				$post_front_slug = $parts[count($parts) - 1];
			} else {
				if( ! $parent_id && isset( $_POST['icl_trid'] ) && ! empty( $_POST['icl_trid'] ) ) {
					$icl_trid = isset( $_POST['icl_trid'] ) ? $_POST['icl_trid'] : 0;
					if( $icl_trid ) {
						$post_parent_slug = get_icl_parent_slug_for_new_post($icl_trid, $dest_name, $slugs);
						$icl_front_slug_for_new_post = get_icl_parent_front_slug_for_new_post_nomaster($icl_trid, $dest_slug, $slugs);
																											// front slug (new translated nomaster)
						$post_front_slug = ! empty( $icl_front_slug_for_new_post ) ? $icl_front_slug_for_new_post.'-'.$post_name : $post_name;
					} else {
						$post_front_slug = create_parent_front_slug( $post );								// front slug (new nomaster)
						$post_parent_slug = '';																// parent slug (new nomaster)
					}
				} else {
					$post_front_slug = $post_name;															// front slug (new nomaster)
					$post_parent_slug = '';																	// parent slug (new nomaster)
				}
			}
		} else {
			if( $is_update ) {
				$post_front_slug = create_parent_front_slug( $post );										// front slug (update master)
				$post_parent_slug = '';																		// parent slug (update master)
			} else {
				if( ! $parent_id && isset( $_POST['icl_trid'] ) && ! empty( $_POST['icl_trid'] ) ) {
					$icl_trid = isset( $_POST['icl_trid'] ) ? $_POST['icl_trid'] : 0;
					if( $icl_trid ) {
						$post_parent_slug = get_icl_parent_slug_for_new_post($icl_trid, $dest_name, $slugs);// parent slug (new translated master)
						$icl_parent_front_slug_for_new_post = get_icl_parent_front_slug_for_new_post($icl_trid, $dest_name);
																											// front slug (new translated master)
						$post_front_slug = ! empty( $icl_parent_front_slug_for_new_post ) ? $icl_parent_front_slug_for_new_post.'-'.$post_name : $post_name;
					} else {
						$post_front_slug = create_parent_front_slug( $post );								// front slug (new master)
						$post_parent_slug = '';																// parent slug (new master)
					}
				} else {
						$post_front_slug = create_parent_front_slug( $post );								// front slug (new master)
						$post_parent_slug = '';																// parent slug (new master)
				}
			}
		}
	}
	$post_slug = $is_update ? $post->post_name : get_GUI();
	$new_post_name = $post_slug;

	if( empty( $post_parent_slug ) ) {																		// key for array of slags
		$key = $new_post_name;
	} else {
		$key = $post_parent_slug.'/'.$post_slug;
	}

	$slug = $dest_slug.'/'.$post_front_slug;																// value for array of slags
	$slugs_array = get_object_vars( $slugs );

	// check if slug already exists and construct new slug if it's needed
	if ( ! $is_update ) {
		if ( $is_nomaster ) {
			$slug = sanitize_guide_page_slug( $slugs_array, $slug );
		} else {
			if ( $exist_key = array_search( $slug, $slugs_array) ) {
				//rename existing slug if it is no_master
				$existing_page = get_posts( array(
					'name' => $exist_key,
					'post_type' => 'destination-page',
					'posts_per_page' => 1
				) );

				$existing_page = ( count( $existing_page ) > 0 ) ? $existing_page[0] : null;

				if ( is_null( $existing_page ) || ! get_master_parent( $existing_page->ID ) ) {
					// mod existing no_master page slug
					$slugs->$exist_key = sanitize_guide_page_slug( $slugs_array, $slug );
				}
			}
		}
	}

	$slugs = clear_guide_page_slug( $slugs, $slug );
	$slugs->$key = $slug;

	update_post_meta( $dest_id, 'pages_cpt_slug', json_encode( $slugs ) );
	return $new_post_name;
}
endif;

if ( ! function_exists( 'sanitize_guide_page_slug' ) ) :
function sanitize_guide_page_slug( $slugs_array, $slug ) {
	$new_slug = $slug;
	$suffix   = 1;

	while ( in_array( $new_slug, $slugs_array ) ) {
		$new_slug = $slug . "-$suffix";
		$suffix++;
	}

	return $new_slug;
}
endif;

if ( ! function_exists( 'update_guide_pages_slugs' ) ) :
function update_guide_pages_slugs( $post, $is_nomaster = false ) {
	$dest_id = get_guide_page_parent( $post->ID );
	set_guide_pages_slugs( $dest_id, $post, $is_nomaster, true );
}
endif;

if ( ! function_exists( 'set_guide_page_level' ) ) :
function set_guide_page_level( $post_id, $level = 0 ) {
	update_post_meta( $post_id, 'guide_page_level', $level );
}
endif;

if ( ! function_exists( 'set_guide_page_order' ) ) :
function set_guide_page_order( $post_id, $master_post_id ) {
	$meta_master_order = get_post_meta( $master_post_id, 'master_order' );
	$master_order = ( isset($meta_master_order[0] ) && ! empty( $meta_master_order[0] ) )? $meta_master_order[0] : 0;
	update_post_meta( $post_id, 'guide_page_order', $master_order );
}
endif;

if ( ! function_exists( 'set_guide_page_order_nomaster' ) ) :
function set_guide_page_order_nomaster( $post_id, $order = 0 ) {
	update_post_meta( $post_id, 'guide_page_order', $order );
}
endif;

if ( ! function_exists( 'set_guide_page_parent' ) ) :
function set_guide_page_parent( $post_id, $parent_id = 0 ) {
	update_post_meta( $post_id, 'destination_parent_id', $parent_id );
}
endif;

if ( ! function_exists( 'set_master_parent' ) ) :
function set_master_parent( $post_id, $parent_id = 0 ) {
	update_post_meta( $post_id, 'master_parent_id', $parent_id );
}
endif;

if ( ! function_exists( 'set_guide_page_GUI' ) ) :
function set_guide_page_GUI( $post_id, $value ) {
	update_post_meta( $post_id, 'destination_master_GUI', $value );
}
endif;

if ( ! function_exists( 'set_guide_page_no_master' ) ) :
function set_guide_page_no_master( $post_id ) {
	update_post_meta( $post_id, 'no_master', 1 );
}
endif;

if ( ! function_exists( 'need_show_articles' ) ) :
function need_show_articles( $id = 0 ) {
	global $post;

	$id = ( $id == 0 ) ? $post->ID : $id;
	$options = get_destination_options( $id );

	if( $options['include_posts_home'] == 'false' )
		return false;
	else
		return true;
}
endif;

if ( ! function_exists( 'blog_posts_query' ) ) :
function blog_posts_query( $id = 0 ) {
	global $post;

	$id = ( $id == 0 ) ? $post->ID : $id;
	$options = get_destination_options( $id );

	$include_child_posts = ( isset( $options['include_posts_child'] ) && $options['include_posts_child'] == 'true' ) ? true : false;

	$categories = isset( $options['blog_categories'] ) ? $options['blog_categories'] : array();

	if( $include_child_posts ) {
		$children = get_children( $id );
		foreach( $children as $child ) {
			$options = get_destination_options( $child->ID );
			$categories_ext = isset( $options['blog_categories'] ) ? $options['blog_categories'] : array();
			$categories = array_merge( $categories, $categories_ext );
		}
	}

	if( ! empty( $categories ) ) {
		$query = array(
			'post_type' => 'post',
			'posts_per_page' => -1,
		);

		if( ! empty( $categories ) ) {
			$query['tax_query'] = array(
				array(
					'taxonomy' => 'category',
					'field'    => 'id',
					'terms' => $categories
				)
			);
		}

		return new WP_Query( $query );
	} else {
		return array();
	}
}
endif;

if ( ! function_exists( 'get_destinations' ) ) :
function get_destinations( $parent_id = 0 ) {
	$args = array(
		'post_type' => 'destination',
		'post_parent' => $parent_id,
		'posts_per_page' => -1,
		'orderby' => 'title',
		'order' => 'ASC',
	);
	$destinations = ( $parent_id == 0 ) ? get_posts( $args ) : get_children( $args );

	return $destinations;
}
endif;

if ( ! function_exists( 'get_default_info_page_id' ) ) :
function get_default_info_page_id( $trid ) {
	global $wpdb;

	$query = "SELECT element_id FROM $wpdb->prefix"."icl_translations WHERE trid = ".$trid." AND element_type = 'post_destination-page' AND isnull(source_language_code)";//" = '".ICL_LANGUAGE_CODE."'";
	$res = $wpdb->get_results( $query, OBJECT );
	$elm_src_id = $res[0]->element_id;

	return $elm_src_id;
}
endif;

if ( ! function_exists( 'has_info_page_master' ) ) :
function has_info_page_master( $trid ) {
	$elm_src_id = get_default_info_page_id( $trid );
	$src_master_page_id = get_master_parent( $elm_src_id );
	$master_page_id = (int)apply_filters( 'wpml_object_id', $src_master_page_id, 'master-pages', false, ICL_LANGUAGE_CODE );
	if( ! $master_page_id )
		return 0;
	$master_page = get_post( $master_page_id );

	return $master_page;
}
endif;

if ( ! function_exists( 'has_info_page_destination' ) ) :
function has_info_page_destination( $trid ) {
	$elm_src_id = get_default_info_page_id( $trid );
	$src_dest_id = get_guide_page_parent( $elm_src_id );
	$dest_id = (int)apply_filters( 'wpml_object_id', $src_dest_id, 'destination', false, ICL_LANGUAGE_CODE );
	if( ! $dest_id )
		return 0;
	$dest = get_post( $dest_id );

	return $dest;
}
endif;

if ( ! function_exists( 'get_trid_by_ID' ) ) :
function get_trid_by_ID( $id ) {
	$wpml_element_type = apply_filters( 'wpml_element_type', get_post_type( $id ) );
	$args = array( 'element_id' => $id, 'element_type' => $wpml_element_type );
	$origin = apply_filters( 'wpml_element_language_details', null, $args );
	$origin_trid = ( is_object( $origin ) && property_exists( $origin, 'trid' ) &&  $origin->trid )  ? $origin->trid : 0;

	return $origin_trid;
}
endif;

if ( ! function_exists( 'get_all_ids_by_trid' ) ) :
function get_all_ids_by_trid( $trid ) {
	global $wpdb;

	$query = "SELECT element_id, language_code FROM $wpdb->prefix"."icl_translations WHERE trid = ".$trid;
	$elms = $wpdb->get_results( $query, OBJECT );

	$ids = array();
	foreach( $elms as $elm ) {
		$ids[$elm->language_code] = $elm->element_id;
	}

	return $ids;
}
endif;

if ( ! function_exists( 'get_destination_id_by_trid' ) ) :
function get_destination_id_by_trid( $trid ) {
	global $wpdb;

	$query = "SELECT element_id FROM $wpdb->prefix"."icl_translations WHERE trid = ".$trid;
	$elms = $wpdb->get_results( $query, OBJECT );

	$ids = array();
	foreach( $elms as $elm ) {
		$ids[] = $elm->element_id;
	}

	$id = (int)apply_filters( 'wpml_object_id', get_guide_page_parent( min( $ids ), 'destination', false, ICL_LANGUAGE_CODE ) );
	return $id;
}
endif;

if ( ! function_exists( 'get_destination_page_id_by_trid' ) ) :
function get_destination_page_id_by_trid( $trid, $lang ) {
	global $wpdb;

	$query = "SELECT element_id FROM $wpdb->prefix"."icl_translations WHERE trid = ".$trid." AND element_type = 'post_destination-page' AND language_code = '".$lang."'";
	$elms = $wpdb->get_results( $query, OBJECT );
	$id = 0;
	foreach( $elms as $elm ) {
		$id = $elm->element_id;
	}
	return $id;
}
endif;

if ( ! function_exists( 'get_destination_page_link_by_dest_id' ) ) :
function get_destination_page_link_by_dest_id( $post_id = 0, $lang = false ) {
	$dest_id = get_post_meta( $post_id, 'destination_parent_id', TRUE );
	$link = '';
	if( ! empty( $dest_id ) ) {
		$pages = $lang ? get_destination_pages( $dest_id, 'list', $lang ) : get_destination_pages( $dest_id );
		$link = isset( $pages[$post_id] ) ? $pages[$post_id]['link'] : $link;
	}

	return $link;
}
endif;

if ( ! function_exists( 'get_destination_pages' ) ) :
function get_destination_pages( $post_id = 0, $return = 'list', $lang = false ) {
	$options  = get_option( get_travel_guide_option_key( 'travel_guide_options' ) );
	$settings = $options ? json_decode( $options, true ) : array();
	//$page_base = '';
	if( $lang ) {
		$options = get_option( get_travel_guide_option_key( 'travel_guide_options', $lang ) );
		$settings_lang = $options ? json_decode( $options, true ) : array();
	}

	$args = array(
		'post_type' => get_pages_cpt( $post_id ),
		'posts_per_page' => -1,
		'meta_key' => 'guide_page_order',
		'orderby' => 'meta_value_num',
		'order' => 'ASC',
		'suppress_filters' => $lang? 1 : 0,
		'meta_query' => array(
			array(
				'key' => 'is_disabled_master_page',
				'compare' => 'NOT EXISTS'
			),
			array(
				'key' => 'is_disabled_guide_page',
				'compare' => 'NOT EXISTS'
			),
			array(
				'key' => 'destination_parent_id',
				'value' => $lang? (int)apply_filters( 'wpml_object_id', $post_id, 'destination', false, $lang ) : $post_id
			)
		)
	);

	if ($return == 'query')
		return $args;

	// Get the posts
	$guide_pages = get_posts( $args );
	if ($return == 'posts')
		return $guide_pages;

	$items = get_page_hierarchy( $guide_pages );
	$dest = get_post( $post_id );
	$dest_name = create_parent_dest_slug( $dest, false );
	$pages_slugs = get_guide_pages_slugs_new( $post_id );

	$info = array();
	foreach( $items as $key => $item ) {
		$level = count( get_post_ancestors( $key ) );
		$info[$key]['id'] = $key;
		$info[$key]['title'] = str_repeat( '&nbsp;&nbsp;&nbsp;&nbsp;', $level ) . get_the_title( $key );
		$info[$key]['link'] = get_permalink( $key ); // '';

		if( $level ) {
			$item_post = get_post( $key );
			$is_master = get_master_parent( $key )? true : false;
			$parent_item = get_parent_slug( $item_post, true );
			$item = $parent_item . '/'. $item;
		}

		if ( isset( $pages_slugs->$item ) ) {
			$settings_page_base_lang = isset( $settings_lang['page_base'] ) ? $settings_lang['page_base'] : '';
			$settings_page_base = isset( $settings['page_base'] ) ? $settings['page_base'] : '';
			$info[$key]['link'] = get_final_permalink( 'destination-page', $pages_slugs->$item, $settings_page_base, $settings_page_base_lang, $lang );
		}
	}

	return $info;
}
endif;

if ( ! function_exists( 'get_final_permalink' ) ) :
function get_final_permalink( $post_type, $slug = '', $current_base, $lang_base, $lang ) { // slug = post_name (for all posts except info pages(CPT destination-page)
	$link = get_post_type_archive_link( $post_type );
	if(defined('ICL_LANGUAGE_CODE')) {
		$link = ( $pos = strpos( $link, '?lang=' ) ) ? substr( $link, 0, $pos ) : $link;
	}
	$link = $link . $slug;
//	$link = str_replace('/'.$dest_slug, '/'.$dest_slug, $link);
	if( defined( 'ICL_LANGUAGE_CODE' ) ) {
		$link = apply_filters( 'wpml_permalink', $link, $lang );
		$link = ( $lang ) ? str_replace( '/'.$current_base.'/', '/'.$lang_base.'/', $link ) : $link;
	}

	return trailingslashit($link);
}
endif;

if ( ! function_exists( 'get_destination_page_pretty_url' ) ) :
function get_destination_page_pretty_url( $post_ID ) {
	$dest_ID = get_the_destination_ID( $post_ID );
	$all_guide_pages = get_destination_pages( $dest_ID, 'list' );
	$link = array_key_exists($post_ID, $all_guide_pages) ? $all_guide_pages[$post_ID]['link'] : '';

	return trailingslashit($link);
}
endif;

if ( ! function_exists( 'get_destination_page_pretty_url_sitemap' ) ) :
function get_destination_page_pretty_url_sitemap( $post_ID, $lang = false ) {
	$dest_ID = get_guide_page_parent( $post_ID );
	if( ! $dest_ID ) 
		return '';
	$all_guide_pages = get_destination_pages( $dest_ID, 'list', $lang );
	$link = $all_guide_pages[$post_ID]['link'];

	return ! empty( $link ) ? trailingslashit($link) : '';
}
endif;

if ( ! function_exists( 'get_hero_data' ) ) :
function get_hero_data( $id = 0 ) {
	global $post;

	$id = ( $id == 0 )? $post->ID : $id;

	$item = get_post( $id );

	$hero['name'] = $item->post_title;
	$hero['breadcrumb'] = array();
	$parents = array_reverse( get_post_ancestors( $item->ID ) );

	foreach( $parents as $key => $item ) {
		$item = ( defined( 'ICL_LANGUAGE_CODE' ) )? (int)apply_filters( 'wpml_object_id', $item, 'destination', true, ICL_LANGUAGE_CODE ) : $item;
		$hero['breadcrumb'][$key]['title'] = get_the_title( $item );
		$hero['breadcrumb'][$key]['link'] = trailingslashit(get_permalink( $item ));
	}
	return $hero;
}
endif;

if ( ! function_exists( 'get_all_children' ) ) :
function get_all_children( $post_id = 0, $all_destinations ) {
	$children = get_children( $post_id );
	if( count( $children ) ) {
		foreach( $children as $key => $child ) {
			$all_destinations[] = $key;
			$all_destinations = get_all_children( $child->ID, $all_destinations );
		}
	}

	return $all_destinations;
}
endif;

if ( ! function_exists( 'sort_directory_terms' ) ) :
function sort_directory_terms( $terms_unsorted = array() ) {
	$terms_tmp = array();
	$terms_sorted = array();
	$terms_excl = array();

	foreach( $terms_unsorted as $key => $term ) {
		$term_data = get_option( 'taxonomy_'.$term->term_id );
		$term_data['menu_order'] = ! empty( $term_data['menu_order'] ) ? $term_data['menu_order'] : 0;
		$new_keys[] = array( 'key' => $key, 'order' => $term_data['menu_order'] );
		if ( array_key_exists( $term_data['menu_order'], $terms_tmp ) ) {
			$terms_excl[] = array( 'term' => $term, 'menu_order' => $term_data['menu_order'] );
		} else
			$terms_tmp[$term_data['menu_order']] = $term;
	}
	ksort( $terms_tmp );

	foreach( $terms_tmp as $key => $term ) {
		$terms_sorted[] = $term;
		foreach( $terms_excl as $term_excl ) {
			if( $key == $term_excl['menu_order'] )
				$terms_sorted[] = $term_excl['term'];
		}
	}

	return $terms_sorted;
}
endif;

if ( ! function_exists( 'get_destination_options' ) ) :
function get_destination_options( $post_id = 0 ) {
	$meta = get_post_meta( $post_id, 'destination_options' );
	$options = empty( $meta[0] ) ? '' : json_decode( $meta[0], true );

	return $options;
}
endif;

if ( ! function_exists( 'get_guide_lists_directory' ) ) :
function get_guide_lists_directory( $post_id = 0 ) {
	global $post;

	$post_id = ( $post_id == 0 )? $post->ID : $post_id;
	$dest = get_post( $post_id );
	$options = get_destination_options( $post_id );
	$include_child_guide_lists = ( isset( $options['guide_lists'] ) && $options['guide_lists'] == 'true' ) ? true : false;

	$all_child_destinations[] = $post_id;
	if( $include_child_guide_lists ) {
		$all_child_destinations = get_all_children( $post_id, $all_child_destinations );
	}

	$args = array(
		'post_type' => 'travel-directory',
		'posts_per_page' => -1,
		'orderby' => 'title',
		'order' => 'ASC',
		'post_status' => array( 'publish' ),
		'suppress_filters' => defined( 'ICL_LANGUAGE_CODE' )? 0 : 1,
		'meta_query' => array(
			array(
				'key' => 'destination_parent_id',
				'value' => $all_child_destinations,
				'compare' => 'IN'
			)
		)
	);
	$guide_lists = get_posts( $args );

	$directory = array();
	$terms_unsorted = array();
	$terms = array();
	$images = array();
	foreach( $guide_lists as $list ) {
		$guides_terms = get_the_terms( $list->ID, 'travel-dir-category' );
		if( $guides_terms ) {
			$category = current( $guides_terms );
			if( in_array( $category->term_id, $terms ) === false ) {
				if( in_array( $category->term_id, $images ) === false && has_post_thumbnail( $list->ID ) )
					$images[] = $category->term_id;
				$terms_unsorted[] = $category;
				$first_post_id[] = $list->ID;
				$terms[] = $category->term_id;
			}
		}
	}

	$terms_sorted = sort_directory_terms( $terms_unsorted );
	foreach( $terms_sorted as $key => $term ) {
		$directory[$term->term_id]['post_ID'] = $term->object_id;
		$directory[$term->term_id]['name'] = $term->name;
		$directory[$term->term_id]['link'] = trailingslashit(get_destination_taxonomy_term_links( $term->slug, $dest->post_name, 'travel-dir-category' ));
		if( in_array( $term->term_id, $images ) ) {
			$directory[$term->term_id]['image'] = get_post_thumbnail_id( $term->object_id );
		}
	}
	return $directory;
}
endif;

if ( ! function_exists( 'get_meta_guide_lists_details' ) ) :
function get_meta_guide_lists_details( $post_id ) {
	$meta = get_post_meta( $post_id, 'guide_lists_details' );
	$details_meta = empty( $meta[0] ) ? '' : json_decode( stripslashes( $meta[0] ), true );

	return $details_meta;
}
endif;

if ( ! function_exists( 'get_guide_lists_details' ) ) :
function get_guide_lists_details( $post_id ) {
	$details_meta = get_meta_guide_lists_details( $post_id );
	$details = array();
	if( isset( $details_meta['address'] ) && ! empty( $details_meta['address'] ) )
		$details[__( 'Address', 'destinations' )] = nl2br( esc_attr( str_replace( "&lt;br /&gt;", "\r\n", $details_meta['address'] ) ) );
	if( isset( $details_meta['contact_name_main'] ) && ! empty( $details_meta['contact_name_main'] ) )
		$details[$details_meta['contact_name_main']] = $details_meta['contact_value_main'];
	foreach( $details_meta['contacts'] as $detail ) {
		$details[key( $detail )] = $detail[key( $detail )];
	}
	if( isset( $details_meta['other_name_main'] ) && ! empty( $details_meta['other_name_main'] ) )
		$details[$details_meta['other_name_main']] = $details_meta['other_value_main'];
	foreach( $details_meta['other'] as $detail ) {
		$details[key( $detail )] = $detail[key( $detail )];
	}

	return $details;
}
endif;

if ( ! function_exists( 'get_meta_rating' ) ) :
function get_meta_rating( $post_id ) {
	$meta = get_post_meta( $post_id, 'guide_list_rating' );
	$rating = empty( $meta[0] ) ? '' : json_decode( $meta[0], true );

	return $rating;
}
endif;

if ( ! function_exists( 'get_guide_lists_rating' ) ) :
function get_guide_lists_rating( $post_id ) {
	$rating = get_meta_rating( $post_id );
	$rating['star'] = ( isset( $rating['rating_types_star'] ) && ! empty( $rating['rating_types_star'] ) ) ? $rating['rating_types_star'] : '';
	$rating['usd'] = ( isset( $rating['rating_types_usd'] ) && ! empty( $rating['rating_types_usd'] ) ) ? $rating['rating_types_usd'] : '';

	$rating = apply_filters( 'rating_settings', $rating );
	unset( $rating['settings'][0] );
	unset( $rating['settings'][1] );

	$term = wp_get_post_terms( $post_id, 'travel-dir-category' );
	if( empty( $term ) ) {
		$rating['enabled']['rating_types_star'] = 'true';
		$rating['enabled']['rating_types_usd'] = 'true';
	} else
		$rating['enabled'] = get_option( 'taxonomy_'.$term[0]->term_id );

	return $rating;
}
endif;

if ( ! function_exists( 'get_guide_lists_taxonomy' ) ) :
function get_guide_lists_taxonomy( $post_id, $dest_slug ) {
	$taxonomy = wp_get_post_terms( $post_id, 'travel-dir-category', array( "fields" => "all" ) );

	$terms = array( 'name'=>'', 'link'=>'' );
	if ( isset( $taxonomy[0] ) ) {
		$terms['name'] = $taxonomy[0]->name;
		$terms['link'] = trailingslashit(get_destination_taxonomy_term_links( $taxonomy[0]->slug, $dest_slug, 'travel-dir-category' ));
	}

	return $terms;
}
endif;

if ( ! function_exists( 'get_guide_lists_by_category' ) ) :
function get_guide_lists_by_category( $destination_id = 0, $category_id = 0, $return = 'posts' ) {

	$options = get_destination_options( $destination_id );
	$include_child_guide_lists = ( isset( $options['guide_lists'] ) && $options['guide_lists'] == 'true' )? true : false;

	$all_child_destinations[] = $destination_id;
	if( $include_child_guide_lists ) {
		$all_child_destinations = get_all_children( $destination_id, $all_child_destinations );
	}

	$args = array(
				'post_type' => 'travel-directory',
				'posts_per_page' => -1,
				'post_status' => array( 'publish' ),
				'orderby' => 'title',
				'order' => 'ASC',
				'meta_query' => array(
					array(
						'key' => 'destination_parent_id',
						'value' => $all_child_destinations,
						'compare' => 'IN'
					)
				),
				'tax_query' => array(
					array(
						'taxonomy' => 'travel-dir-category',
						'field'    => 'id',
						'terms' => $category_id
					)
				)
			);

	$lists = get_posts( $args );

	$cat = isset( $_GET['cat'] ) ? $_GET['cat'] : 'star';
	$order = isset( $_GET['order'] ) ? $_GET['order'] : 'desc';
	$list = array();
	foreach( $lists as $item ) {
			$rating = get_meta_rating( $item->ID );
			$list[$item->ID] = isset( $rating['rating_types_'.$cat] ) ? $rating['rating_types_'.$cat] : 0;
	}

	if( $order == 'desc' )
		arsort( $list );
	if( $order == 'asc' )
		asort( $list );

	if ( $return == 'Sorted IDs' ) {
		return array_keys( $list );
	}

	$posts_sorted = array();
	foreach($list as $key => $item) {
		$posts_sorted[] = get_post( $key );
	}

	/* Restore original Post Data */
	wp_reset_postdata();

	return $posts_sorted;
}
endif;

if ( ! function_exists( 'get_destination_intro' ) ) :
function get_destination_intro( $post_ID = 0 ) {
	global $post;

	switch ( $post->post_type ) {
		case 'destination':
			$meta_name = 'destination_intro';
			break;

		case 'destination-page':
			$meta_name = 'destination_intro';
			break;

		case 'travel-directory':
			$meta_name = 'guide_lists_intro';
			break;

		default:
			# code...
			break;
	}

	$id = ( $post_ID ) ? $post_ID : $post->ID;
	$meta = get_post_meta( $id, $meta_name );
	$intro = ( isset( $meta[0] ) && ! empty( $meta[0] ) ) ? $meta[0] : '';

	return $intro;
}
endif;

if ( ! function_exists( 'get_destination_order' ) ) :
function get_destination_order( $post_id = 0 ) {
	$meta = get_post_meta( $post_id, 'destination_order' );
	$order = ( isset($meta[0] ) && ! empty( $meta[0] ) )? $meta[0] : 0;

	return $order;
}
endif;

if ( ! function_exists( 'get_guide_page_order' ) ) :
function get_guide_page_order( $post_id = 0 ) {
	$meta = get_post_meta( $post_id, 'guide_page_order' );
	$order = ( isset( $meta[0] ) && ! empty( $meta[0] ) ) ? $meta[0] : 0;

	return $order;
}
endif;

if ( ! function_exists( 'get_id_by_post_name' ) ) :
function get_id_by_post_name( $post_name ) {
	global $wpdb;
	$id = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_name = '".$post_name."' AND post_status = 'publish'" );
	return $id;
}
endif;

if ( ! function_exists( 'get_destination_post' ) ) :
function get_destination_post( $post_id = 0 ) {

	if( $post_id == 0 ) {
		global $wp;
		$url = explode( '/', home_url( add_query_arg( array(), $wp->request) ) );
		$name = $url[count($url) - 2];
		$dest_id = get_id_by_post_name( $name );
	} else {
		$dest_id = get_guide_page_parent( $post_id );
	}

	$dest = get_post( $dest_id );
	return $dest;
}
endif;

if ( ! function_exists( 'is_destination_paged' ) ) :
function is_destination_paged( $args = array() ) {
	global $paged;
	if( $paged )
		$args['paged'] = $paged;
	return $args;
}
endif;

if ( ! function_exists( 'is_destination_archive' ) ) :
function is_destination_archive( $args = array() ) {
	global $paged, $paged_cust;
	$paged = $paged_cust;
	if( $paged )
		$args['paged'] = $paged;
	return $args;
}
endif;

if ( ! function_exists( 'get_the_destination_ID' ) ) :
function get_the_destination_ID() {
	global $post, $paged;

	$dest_id = 0;

	if( is_tax( 'destinations' ) || is_tax( 'travel-dir-category' ) ) { // for taxonomy: destinations
		global $wp;

		$options  = get_option( get_travel_guide_option_key( 'travel_guide_options' ) );
		$settings = $options ? json_decode( $options, true ) : array();
		//$directory_base = (!isset($settings['guide_list_base']) || empty($settings['guide_list_base'])) ? '' : $settings['guide_list_base'];
		$directory_base = '';

		$request = ! empty( $directory_base ) ? str_replace( $directory_base.'/', '', $wp->request ) : $wp->request;
		$url = explode( '/', home_url( add_query_arg( array(), $request) ) );

		$page_index = array_search( 'page', $url );
		$name_index = ( $page_index ) ? count( $url ) - ( count( $url ) - $page_index) - 2 : count( $url ) - 2;
		$paged = ( $page_index ) ? intval( $url[$page_index + 1] ) : 0;

		$name = $url[$name_index];
		$dest_id = get_id_by_post_name( $name );
	} elseif ( is_object( $post ) ) {
		$dest_id = get_guide_page_parent( $post->ID ); // for post types: destination-page, travel-directory
	}

	if ( ! $dest_id && is_singular( 'destination' ) ) {
		$dest_id = $post->ID; // for CPT destination single (destination home)
	}

	$dest_id = ( defined( 'ICL_LANGUAGE_CODE' ) ) ? (int)apply_filters( 'wpml_object_id', $dest_id, 'destination', true, ICL_LANGUAGE_CODE ) : $dest_id;
	return $dest_id;
}
endif;

if ( ! function_exists( 'get_the_destination_post' ) ) :
function get_the_destination_post() {
	$dest_id = get_the_destination_ID();
	$dest = get_post( $dest_id );

	return $dest;
}
endif;

if ( ! function_exists( 'get_guide_term_id' ) ) :
function get_guide_term_id() {
	global $wp, $paged;

	$url = explode( '/', home_url( add_query_arg( array(), $wp->request) ) );

	$page_index = array_search( 'page', $url );
	$name_index = ( $page_index ) ? count( $url ) - ( count( $url ) - $page_index ) - 1 : count( $url ) - 1;
	$paged = ( $page_index )? intval( $url[$page_index + 1] ) : 0;//$page_index? $url[$page_index + 1] : 10;
	$name = ( $pos = strpos( $url[$name_index], '?lang=' ) )? substr( $url[$name_index], 0, $pos ) : $url[$name_index];

	$term = get_term_by( 'slug', $name, 'travel-dir-category' );

	return $term;
}
endif;

if ( ! function_exists( 'set_destinations_terms' ) ) :
function set_destinations_terms( $id ) {
	$post_terms = get_the_terms( $id, 'destinations' );
	$post_terms_id = array();
	if ( is_array( $post_terms ) && count( $post_terms ) && ! is_wp_error( $post_terms )) {
		foreach( $post_terms as $post_term ) {
			$post_terms_id[] = $post_term->term_id;
		}
	}

	$args = array(
		'orderby'    => 'name',
		'order'      => 'ASC',
		'hide_empty' => false,
	);
	$terms = get_terms( 'destinations', $args );
	if ( count( $terms ) ) {
		foreach( $terms as $term ) {
			if( ! in_array($term->term_id, $post_terms_id) )
				$post_terms_id[] = $term->term_id;
		}
	}

	$post_terms_id = array_map( 'intval', $post_terms_id );
	$term_taxonomy_ids = wp_set_object_terms( $id, $post_terms_id, 'destinations' );
}
endif;

if ( ! function_exists( 'dest_get_words' ) ) :
function dest_get_words( $text, $num_words = 10, $more = '&hellip; ') {
	$trimmed = wp_trim_words( $text, $num_words, $more );
	return $trimmed;
}
endif;

if ( ! function_exists( 'dest_get_characters' ) ) :
function dest_get_characters( $text, $count = 60, $more = '&hellip;' ) {
	if ( strlen( $text ) <= $count ){
		return $text;
	}

	$trimmed = substr( $text, 0, strrpos( substr( $text, 0, $count), ' ' ) );
	if ( ! empty( $more ) ) {
		$trimmed .= $more;
	}
	return $trimmed;
}
endif;

if ( ! function_exists( 'get_destination_settings' ) ) :
function get_destination_settings() {

	// Retrieve settings
	$options  = get_option( get_travel_guide_option_key( 'travel_guide_options' ) );
	$settings = $options ? json_decode( $options, true ) : array();

	/**
	 * Test some defaults
	 */

	// Child destinations on parent Destinations front page
	if ( ! isset($settings['number_posts_child'] ) || empty( $settings['number_posts_child'] ) ) {
		$settings['number_posts_child'] = 2;
	}
	// "Pages" on parent Destinations front page
	if ( ! isset( $settings['number_posts_information'] ) || empty( $settings['number_posts_information'] ) ) {
		$settings['number_posts_information'] = 5;
	}
	// Directory item categories on parent Destinations front page
	if ( ! isset($settings['number_posts_directory'] ) || empty( $settings['number_posts_directory'] ) ) {
		$settings['number_posts_directory'] = 6;
	}
	// Blog posts on parent Destinations front page
	if ( ! isset($settings['number_posts_blogs'] ) || empty( $settings['number_posts_blogs'] ) ) {
		$settings['number_posts_blogs'] = 3;
	}

	return apply_filters( 'get_destination_settings', $settings );
}
endif;

if ( ! function_exists( 'show_directory_items_on_page_load' ) ) :
function show_directory_items_on_page_load( $id ) {
		$show_on_load = 'false';
		$settings = get_destination_settings();
		if( is_single( $id )
		    && array_key_exists( 'show_map_for_directory_items', $settings )
		    && $settings['show_map_for_directory_items'] == 'true'
		) {
			$guide_lists_meta = get_post_meta( $id, 'guide_lists_details');
			$guide_lists_details = (empty($guide_lists_meta[0])) ? '' : json_decode($guide_lists_meta[0], true);
			$show_on_load = ( ! empty( $guide_lists_details['google_map']['longitude'] ) && ! empty( $guide_lists_details['google_map']['latitude'] ) ) ? 'true' : 'false';
		}

		return $show_on_load;
}
endif;

if ( ! function_exists( 'get_sub_nav_links' ) ) :
function get_sub_nav_links() {
	$settings = get_destination_settings();
	$links = array( 1 => 'information', 2 => 'directory' );

	$settings['menu_order_child'] = isset( $settings['menu_order_child'] ) ? $settings['menu_order_child'] : '';
	$settings['menu_order_blogs'] = isset( $settings['menu_order_blogs'] ) ? $settings['menu_order_blogs'] : '';

	$sub_nav_links[$settings['menu_order_child']] = 'places';
	if( $settings['menu_order_child'] == $settings['menu_order_blogs'] ) {
		$sub_nav_links[] = 'articles';
	} else
		$sub_nav_links[$settings['menu_order_blogs']] = 'articles';

	$max = max( array_keys( $sub_nav_links ) );
	for( $i = 1, $j = 1; $i <= $max; $i++ ) {
		if( !isset( $sub_nav_links[$i] ) && $j <= 2 )
			$sub_nav_links[$i] = $links[$j++];
	}

	if( ! in_array( 'information', $sub_nav_links ) )
		$sub_nav_links[$max+1] = 'information';
	if( ! in_array( 'directory', $sub_nav_links ) )
		$sub_nav_links[$max+2] = 'directory';

	ksort( $sub_nav_links );

	return $sub_nav_links;
}
endif;

if ( ! function_exists( 'get_destination_taxonomy_term_links' ) ) :
function get_destination_taxonomy_term_links( $term = false, $post_name = '', $taxonomy = 'destinations', $slug = '', $lang = false ) {
	$options  = get_option( get_travel_guide_option_key( 'travel_guide_options' ) );
	$settings = $options ? json_decode( $options, true ) : array();
	if( $lang ) {
		$options = get_option( get_travel_guide_option_key( 'travel_guide_options', $lang ) );
		$settings_lang = $options ? json_decode( $options, true ) : array();
	}
	$directory_base = '';
	// if($term == 'places' || $term == 'articles')
	// 	$directory_base = '';
	// else
	// 	$directory_base = (!isset($settings['guide_list_base']) || empty($settings['guide_list_base'])) ? '' : $settings['guide_list_base'] . '/';

	// Build the URL
	if ( $term ) {
		// Add the slug
		$term_slug = $term.$slug;
		$term_link = get_term_link( $term_slug, $taxonomy );
		if ( !is_wp_error( $term_link ) ) {
			if( defined( 'ICL_LANGUAGE_CODE' ) ) {
				$current_lang = apply_filters( 'wpml_current_language', NULL );
				$default_lang = apply_filters( 'wpml_default_language', NULL );
				if( $current_lang != $default_lang )
					$term_link = str_replace( 'places-'.$current_lang, $post_name, $term_link );
				$term_link = ( $lang ) ? str_replace( '/'.$settings['guide_list_base'].'/','/'.$settings_lang['guide_list_base'].'/', $term_link ) : $term_link;
				$term_link = ( $pos = strpos( $term_link, '?lang=' ) )? substr( $term_link, 0, $pos ) : $term_link;
				$term_link = ( substr( $term_link, -1 ) == '/') ? $term_link : $term_link . '/';
			}
			$find    = '/'.$term_slug.'/';
			$replace = '/'.$post_name.'/';
			$pos     = strrpos( $term_link, $find ); // find last occurance
			$url     = $term_link;                 // make sure we have a URL (fallback)
			// replace last occurance of the "term slug"
			if( $pos !== false ) {
				$url = substr_replace( $term_link, $replace, $pos, strlen( $find ) );
			}
			// add the term at the end
			$url .= $directory_base . $term;
			if( defined( 'ICL_LANGUAGE_CODE' ) ) {
				$lang = ( ! $lang) ? $current_lang : $lang;
				$url = ( $lang ) ? apply_filters( 'wpml_permalink', $url, $lang ) : $url;
			}

			return trailingslashit($url);
		}
	}

	return false;
}
endif;

if ( ! function_exists( 'output_sub_menu_item' ) ) :
function output_sub_menu_item( $id, $item, $echo = true ) {
	$dest = get_post( $id );
	$settings = get_destination_settings();

	ob_start();
	switch ( $item ) {
		case 'places':
			$places = get_destinations( $dest->ID );
			if( count( $places ) && $echo ):
				// Link URL
				$places_url = get_destination_taxonomy_term_links( 'places', $dest->post_name );
				$places_title = ( isset( $settings['menu_title_child'] ) && !empty( $settings['menu_title_child'] ) )? $settings['menu_title_child'] : __( 'Places', 'destinations' );
				// List Item ?>
				<li><a href="<?php echo esc_url( trailingslashit($places_url) ); ?>"><?php echo $places_title; ?></a></li>
			<?php endif;
			$items = $places;
			break;

		case 'information':
			$info_pages = get_destination_pages( $dest->ID );
			if( count( $info_pages ) && $echo ): ?>
				<li class="dropdown show-on-hover">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php _e( 'Information', 'destinations' ); ?> <span class="caret"></span></a>
					<ul class="dropdown-menu" role="menu">
						<?php foreach( $info_pages as $info_page ): ?>
								<li><a href="<?php echo esc_url( trailingslashit($info_page['link']) ); ?>"><?php echo $info_page['title']; ?></a></li>
						<?php endforeach; ?>
					</ul>
				</li>
			<?php endif;
			$items = $info_pages;
			break;

		case 'directory':
			$directories = get_guide_lists_directory( $dest->ID );
			if( count( $directories ) && $echo ):

				// Temp method to get first value as main link.
				$first = reset( $directories );
				$directory_url = ( isset($first['link']) ) ? esc_url( $first['link'] ) : '#'; // '#';
				?>
				<li class="dropdown show-on-hover">
					<a href="<?php echo esc_url($directory_url); ?>" class="dropdown-toggle" data-toggle="dropdown"><?php _e( 'Directory', 'destinations' ); ?> <span class="caret"></span></a>
					<ul class="dropdown-menu" role="menu">
						<?php foreach($directories as $key => $directory):
							?>
							<li><a href="<?php echo esc_url( trailingslashit($directory['link']) ); ?>"><?php echo $directory['name']; ?></a></li>
						<?php endforeach; ?>
					</ul>
				</li>
			<?php endif;
			$items = $directories;
			break;

		case 'articles':
			$articles = blog_posts_query( $dest->ID );
			if( isset( $settings['menu_item_blogs'] ) && $settings['menu_item_blogs'] == 'true' && is_object( $articles ) && isset( $articles->posts ) && count( $articles->posts ) && $echo ):
				$articles_url = get_destination_taxonomy_term_links( 'articles', $dest->post_name );
				$articles_title = ( isset( $settings['menu_title_blogs'] ) && !empty( $settings['menu_title_blogs'] ) )? $settings['menu_title_blogs'] : __( 'Blog', 'destinations' );
				?>
				<li><a href="<?php echo esc_url( trailingslashit($articles_url) ); ?>"><?php echo $articles_title; ?></a></li>
			<?php endif;
			$items = $articles;
			break;
	}

	if ( $echo ) {
		echo ob_get_clean();
	}

	return $items;
}
endif;

if ( ! function_exists( 'get_travel_guide_option_key' ) ) :
function get_travel_guide_option_key( $option_key, $lang = '' ) {
	if( defined( 'ICL_LANGUAGE_CODE' ) ) {
		$what_lang = empty( $lang )? ICL_LANGUAGE_CODE : $lang;
		$option_key = $what_lang . '_' . $option_key;
	}
	return $option_key;
}
endif;

#-----------------------------------------------------------------
# Filters wp_title to translate text for Places and Articles.
#-----------------------------------------------------------------

if ( ! function_exists( 'destinations_taxonomy_wp_title' ) ) :
function destinations_taxonomy_wp_title( $title, $sep ) {

	// Places in title
	if ( is_tax( 'destinations', 'places' ) ) {
		$places_title = __( 'Places', 'destinations' );
		$title = preg_replace( '/Places/', $places_title, $title, 1 );
	}

	// Articles in title
	if ( is_tax( 'destinations', 'articles' ) ) {
		$articles_title = __( 'Articles', 'destinations' );
		$title = preg_replace( '/Articles/', $articles_title, $title, 1 );
	}

	return $title;
}
endif; // destinations_taxonomy_wp_title
add_filter( 'wp_title', 'destinations_taxonomy_wp_title', 10, 2 );

if ( ! function_exists( 'filter_permalink_after_search' ) ) :
function filter_permalink_after_search( $url ) {
	global $post;

	if(!isset($_GET['s']))
		return $url;

	if( $dest_id = get_guide_page_parent( $post->ID ) ) {
		$dest = get_post( $dest_id );
		$dest_name = create_parent_dest_slug( $dest, false );
		$slugs = get_guide_pages_slugs_new( $dest_id );
		$item = create_parent_slug( $post, true, $dest_name );

		if ( isset( $item ) && !empty( $item ) && isset( $slugs->$item ) ) {
			$url = get_post_type_archive_link( get_pages_cpt( $post->ID ) ) . $slugs->$item;
		}
	}

	return trailingslashit($url);
}
endif;
add_filter( 'the_permalink', 'filter_permalink_after_search' );

if ( ! function_exists( 'unescaped_json') ) :
function unescaped_json( $arr ) {
	return preg_replace_callback(
						'/\\\\u([0-9a-f]{4})/i',
						function ( $matches ) {
							$sym = mb_convert_encoding(
									pack( 'H*', $matches[1] ),
									'UTF-8',
									'UTF-16'
									);
							return $sym;
						},
						json_encode( $arr )
			);
}
endif;

#-----------------------------------------------------------------
# Filter for plugin: qtranslate X
#-----------------------------------------------------------------
if ( ! function_exists( 'get_qtranslate_rw' ) ) :
function get_qtranslate_rw( $text ) {
	if ( function_exists( 'qtranxf_use' ) ) {
		global $q_config;
		$text = qtranxf_use( $q_config['language'], $text );
	}

	return $text;
}
add_filter( 'get_qtranslate_rw', 'get_qtranslate_rw' );
endif;

if ( ! function_exists( 'get_GUI' ) ) :
function get_GUI( $length = 0 ) {

	$id = '';
	$chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$length = ($length > 0)? $length : 12; // used specified length, or default 12
	for ($i = $length; $i > 0; --$i) {
		$id .= $chars[intval(round((mt_rand() / mt_getrandmax()) * (strlen($chars) - 1)))];
	}

	return strtolower($id);
}
endif;

if ( ! function_exists( 'render_geocoding_map_options' ) ) :
function render_geocoding_map_options( $google_map = array() ) {
	$api_key = get_options_data( 'options-page', 'google-api-key' );

	if ( ! empty( $api_key ) ) {
		?>

		<span style="display:inline-block; width:80px;">
			<label for="google_map_address"><?php _e( 'Address', 'destinations' ); ?></label>
		</span>
		<input type="text" name="google_map_address"
		       value="<?php echo( isset( $google_map['google_map_address'] ) ? $google_map['google_map_address'] : '' ); ?>"
		       size="30"/>
		<a id="geocode" class="button button-secondary" href="#"><?php _e( 'Geocode', 'destinations' ); ?></a>
		<div id="geocode_message"></div>
		<div id="map" style="height: 300px;"></div>

		<?php
		$maps_lang = apply_filters( 'goexplore_google_maps_lang', get_option( 'WPLANG' ) );
		wp_enqueue_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js?v=3&key=' . $api_key . '&language=' . $maps_lang, array( 'jquery' ) );

		wp_enqueue_script( 'maps-admin', TRAVEL_PLUGIN_URL . 'assets/js/maps-admin.js', array(
			'jquery',
			'google-maps'
		), '', true );
		wp_localize_script( 'maps-admin', 'destination_options', array(
			'address'    => isset( $google_map['address'] ) ? $google_map['address'] : '',
			'lat'        => isset( $google_map['latitude'] ) ? $google_map['latitude'] : '',
			'lng'        => isset( $google_map['longitude'] ) ? $google_map['longitude'] : '',
			'zoom'       => isset( $google_map['zoom'] ) ? $google_map['zoom'] : '',
			'error_text' => __( 'Geocode was not successful for the following reason: ', 'destinations' ),
		) );
	} else {
		_e( 'Enter a Google API key in Theme Options to enable <a href="https://developers.google.com/maps/documentation/geocoding/start">Google geocoding</a>.', 'destinations' );
	}
}
endif;

if ( ! function_exists( 'get_sitemap_post_type_archive_link' ) ) :
function get_sitemap_post_type_archive_link( $archive_url, $post_type ) {
	if ( $post_type === 'destination-page' ) {
		$archive_url = 0;
	}

	return $archive_url;
}
endif;
