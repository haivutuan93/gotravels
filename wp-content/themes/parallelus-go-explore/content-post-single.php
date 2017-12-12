<?php
/**
 * Single post content
 */
?>

<div class="col-sm-8 col-md-6 col-md-push-3">

	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<header class="entry-header">
			<?php if ( rf_show_page_title() ) : ?>
				<h1 class="entry-title"><?php the_title(); ?></h1>
			<?php endif; ?>
		</header><!-- .entry-header -->

		<?php

		// Intro Text / Sub-title
		$summary = get_post_meta( get_the_ID(), 'theme_custom_sub_title_metabox_options_sub_title', true );
		if (!empty($summary)) {
			?>
			<div class="entry-summary">
				<p class="lead"><?php echo wp_kses_post($summary); ?></p>
			</div>
			<?php
		}
		?>

		<div class="entry-content">
			<?php the_content(); ?>
			<?php
				wp_link_pages( array(
					'before' => '<div class="page-links">' . __( 'Pages:', 'framework' ),
					'after'  => '</div>',
				) );
			?>
		</div><!-- .entry-content -->

		<?php
	
		
		// If comments are open or we have at least one comment, load up the comment template
		if ( comments_open() || '0' != get_comments_number() ) {
			comments_template();
		}
		?>

	</article><!-- #post-<?php the_ID(); ?> -->
</div>

<div class="col-xs-12 col-sm-4 col-md-3 col-md-pull-6 blog-details-column">

	<?php 
	// Thumbnail 
	if ( has_post_thumbnail() ) : 

		// Check for featured image in header
		$bg_setting = '';
		$header_size = '';
		$meta_options = get_post_custom();
		if ( isset($meta_options['theme_custom_layout_metabox_options_header_bg']) ) {
			$bg_setting = $meta_options['theme_custom_layout_metabox_options_header_bg'][0];
			$header_size = $meta_options['theme_custom_layout_metabox_options_header_size'][0];

		}

		if ( $bg_setting !== 'featured-image' || $header_size == 'none' ) {
			// check thumbnail orientation
			$post_thumbnailSize = (get_options_data('options-page', 'blog-image-orientation') == 'horizontal') ? 'blog-landscape' : 'blog';
			?>
			<figure class="entry-thumbnail card">
				<?php the_post_thumbnail( $post_thumbnailSize ); ?>
			</figure><!-- .entry-thumbnail -->
			<?php
		}

	endif; // has_post_thumbnail ?>

	<div class="entry-meta">

		<span class="icon-meta">
			<span class="posted-on">
				<i class="fa fa-calendar"></i><span class="meta-item"><?php echo esc_html(get_the_date()); ?></span>
			</span>
		</span>

		<div class="byline icon-meta">
			<i class="fa fa-user "></i><span class="author vcard meta-item"><?php the_author_posts_link(); ?></span>
		</div>

		<div class="comments-link icon-meta">
			<i class="fa fa-comments"></i><span class="meta-item"><?php comments_popup_link( '<span class="leave-reply">' . __( 'Comments', 'framework' ) . '</span>', __( '1 Comment', 'framework' ), __( '% Comments', 'framework' ) ); ?></span>
		</div>

		<?php
			// Categories
			$category_list = get_the_category_list( '&nbsp;&nbsp; ' );
			if ( $category_list != '' ) { 
				?>
				<div class="cat-links icon-meta">
					<i class="fa fa-folder"></i><?php echo wp_kses_post($category_list); ?>
				</div>
				<?php
			}

			// Tags
			$tag_list = get_the_tag_list( '', '&nbsp;&nbsp; ' );
			if ( $tag_list != '' ) { 
				?>
				<div class="tag-links icon-meta">
					<i class="fa fa-tag"></i><?php echo wp_kses_post($tag_list); ?>
				</div>
				<?php
			}
		?>

	</div>

	<?php 
	// Next/Previous post navigation
	if (function_exists( 'rf_next_prev_post_nav' )) {
		rf_next_prev_post_nav( 'nav-below' ); 
	}

	// Check for custom left sidebar from meta options
	$meta_options = get_post_custom();
	if ( isset($meta_options['theme_custom_sidebar_options_left']) ) {
		$theme_sidebar = $meta_options['theme_custom_sidebar_options_left'][0];

		// Determine the sidebar to use
		if ( $theme_sidebar !== 'default' ) {
			?>
			<div class="sidebar">	
				<?php get_sidebar('left'); ?>
			</div><!-- /.sidebar-left -->
			<?php
		}
	} // end sidebar left ?>

	<?php // edit_post_link( '<span class="glyphicon glyphicon-edit"></span> &nbsp;'.__( 'Edit', 'framework' ), '<p><span class="edit-link">', '</span></p>' ); ?>

</div><!-- /.blog-details-column -->

<div class="sidebar col-xs-12 col-sm-4 col-md-3">	

	<?php get_sidebar(); ?>

</div><!-- /.sidebar -->
