<?php
/**
 * The template for displaying search forms
 */
?>
<form role="search" method="get" class="search-form form-inline" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<div class="form-group">
		<input type="search" class="search-field form-control" placeholder="<?php echo esc_attr__( 'Search...', 'framework' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" title="<?php echo esc_attr__( 'Search...', 'framework' ); ?>">
		<input type="submit" class="search-submit btn btn-default" value="<?php echo esc_attr__( 'Search', 'framework' ); ?>">
	</div>
</form>
