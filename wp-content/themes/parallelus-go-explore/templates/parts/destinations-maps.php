<?php
/**
* Map in Hero (headers)
*/

// Check for "Show on Load"
$dest_meta = get_post_meta( get_the_ID(), 'destination_options');
$destination_options = (empty($dest_meta[0])) ? '' : json_decode($dest_meta[0], true);
	$show_on_load = ( isset($destination_options['google_map']['show_map_on_load']) ) ? trim($destination_options['google_map']['show_map_on_load']) : 'false';

	if( get_post_type() == 'travel-directory' ) {
		$show_on_load = show_directory_items_on_page_load( get_the_ID() );
	}

// Map Styles
$add_map_style = 'position: absolute; bottom: 0; left: 0; width: 100%; height: 100%; -webkit-transition:all 0s linear; -moz-transition:all 0s linear; transition:all 0s linear;';
if ($show_on_load == 'true') {
	$add_map_style .= ' z-index:0;';
} else {
	$add_map_style .= ' z-index:-1;';
}

?>

<div id="gmap_wrapper" style="<?php echo  $add_map_style; // no escaping needed (see above) ?>" >
	<div id="map-canvas"  style="width: 100%; height: 100%;"></div>
</div>
