<?php
/**
 * Default post content
 *
 * Called from: index.php
 */

$postClass = (has_post_thumbnail()) ? '' : 'no-thumbnail';

if (get_options_data('options-page', 'blog-image-orientation') == 'horizontal') {
	$post_thumbnailSize = 'blog-landscape';
	$placeholder = '<img width="800" height="600" title="" alt="" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAgAAAAGCAMAAADJ2y/JAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMDE0IDc5LjE1Njc5NywgMjAxNC8wOC8yMC0wOTo1MzowMiAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTQgKFdpbmRvd3MpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjVFRTMxMTRDRjJGRTExRTRBNTA0QzczRUQ4OEI3MkI5IiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjVFRTMxMTRERjJGRTExRTRBNTA0QzczRUQ4OEI3MkI5Ij4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6NUVFMzExNEFGMkZFMTFFNEE1MDRDNzNFRDg4QjcyQjkiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6NUVFMzExNEJGMkZFMTFFNEE1MDRDNzNFRDg4QjcyQjkiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz7Wn6+0AAAABlBMVEXp6usAAAD3NurCAAAADklEQVR42mJgIAcABBgAADYAATYL3RUAAAAASUVORK5CYII=" />';
} else {
	$post_thumbnailSize = 'blog';
	$placeholder = '<img width="600" height="800" title="" alt="" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAYAAAAICAMAAADtGH4KAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMDE0IDc5LjE1Njc5NywgMjAxNC8wOC8yMC0wOTo1MzowMiAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTQgKFdpbmRvd3MpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjMxQjk3NTA3RjJGRDExRTRCNjk0Rjg0QjlEODkzNDkxIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjMxQjk3NTA4RjJGRDExRTRCNjk0Rjg0QjlEODkzNDkxIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6MzFCOTc1MDVGMkZEMTFFNEI2OTRGODRCOUQ4OTM0OTEiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6MzFCOTc1MDZGMkZEMTFFNEI2OTRGODRCOUQ4OTM0OTEiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz6NsDE8AAAABlBMVEXp6usAAAD3NurCAAAADklEQVR42mJgIA8ABBgAADgAAYdNUgcAAAAASUVORK5CYII=">';
}
$col_class = (is_front_page()) ? 'col-lg-3 col-sm-6' : 'col-lg-3 col-md-4 col-sm-6';
?>
<div class="<?php echo esc_attr($col_class) ?>">
	<article id="post-<?php the_ID(); ?>" <?php post_class( $postClass ); ?>>
		<div class="card">
			<header class="entry-header">
				<a href="<?php the_permalink(); ?>" rel="bookmark">

					<?php 
					if ( has_post_thumbnail() ) :

						// Background image
						$image_ID = get_post_thumbnail_id( $post->ID );
						$image = wp_get_attachment_image_src( $image_ID, $post_thumbnailSize );
						$style = 'background-image: url('. esc_url($image[0]) .')'; 
						
						?>
						<div class="entry-thumbnail" style="<?php echo esc_attr($style) ?>">
							<?php 
							// the_post_thumbnail( $post_thumbnailSize ); 
							echo  $placeholder; // escaped static image  // hidden, but applies correct ratio to container ?>
						</div><!-- .entry-meta -->
						<?php
					else :
						?>
						<div class="entry-thumbnail" >
							<?php echo  $placeholder; // escaped static image ?>
						</div><!-- .entry-meta -->
						<?php
					endif; ?>
				
					<h1 class="entry-title"><?php the_title(); ?></h1>

				</a>
			</header>

			<footer class="entry-meta clearfix">
				<span class="byline"><i class="fa fa-user"></i> <span class="author vcard"><?php the_author_posts_link(); ?></span></span>
				<span class="posted-on"><?php echo esc_html(get_the_date()); ?></span>
			</footer> <!-- .entry-meta -->
		</div>

		<?php // Excerpt text
		if ( is_search() ) : // Only display Excerpts for Search and Archive Pages ?>
		<div class="entry-summary">
			<?php the_excerpt(); ?>
		</div><!-- .entry-summary -->
		<?php endif; ?>

	</article><!-- #post-<?php the_ID(); ?> -->
</div>