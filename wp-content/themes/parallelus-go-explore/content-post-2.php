<?php
/**
 * Alternate post content: Post Layout Style 2
 *
 * Called from: index.php
 */

$postClass = '';
$postClass_col_2 = 'col-sm-8';
$post_thumbnailSize = (get_options_data('options-page', 'blog-image-orientation') == 'horizontal') ? 'blog-landscape' : 'blog';

if ( !has_post_thumbnail() ) {
	$postClass = 'no-thumbnail';
	$postClass_col_2 = 'col-sm-12';
}

?>
<article id="post-<?php the_ID(); ?>" <?php post_class( $postClass ); ?>>
	<div class="row">
		<?php if ( has_post_thumbnail() ) { ?>
		<div class="col-sm-4">
			<a href="<?php the_permalink(); ?>" rel="bookmark">
				<?php if ( has_post_thumbnail() ) : ?>
				<div class="entry-thumbnail card">
					<?php the_post_thumbnail( $post_thumbnailSize ); ?>
				</div><!-- .entry-meta -->
				<?php endif; ?>
			</a>
		</div>
		<?php } // end if has_post_thumbanil() ?>
		<div class="<?php echo esc_attr($postClass_col_2) ?>">
			<header class="entry-header">
				<a href="<?php the_permalink(); ?>" rel="bookmark">
					<h1 class="entry-title"><?php the_title(); ?></h1>
				</a>
				<div class="entry-meta">
					<span class="icon-meta">
						<span class="byline">
							<i class="fa fa-user"></i>
							<span class="author vcard"><?php the_author_posts_link(); ?></span>
						</span>
					</span>
					&nbsp;
					<span class="icon-meta">
						<span class="posted-on">
							<i class="fa fa-calendar"></i>
							<span class="meta-item"><?php echo esc_html(get_the_date()); ?></span>
						</span>
					</span>
				</div>
			</header>
			<div class="entry-content">
				<?php the_excerpt(); ?>
				<a href="<?php the_permalink(); ?>" class="more-link btn btn-sm btn-primary"><?php _e('Continue reading', 'framework') ?> <span class="meta-nav"> &nbsp; <i class="fa fa-arrow-right"></i></span></a>
			</div>
			<?php // edit_post_link( __( 'Edit', 'framework' ), '<span class="edit-link">', '</span>' ); ?>
		</div>
	</div>
</article>