<?php
/**
 * The template used for displaying page content in page.php
 *
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php if ( rf_show_page_title() ) : ?>
		<header class="page-header">
			<h1 class="page-title"><?php the_title(); ?></h1>
		</header><!-- .entry-header -->
		<?php 

	endif;

	// Intro Text / Sub-title
	if ( rf_show_page_title('meta-value') !== 'in-header' ) : 
		$summary = get_post_meta( get_the_ID(), 'theme_custom_sub_title_metabox_options_sub_title', true );
		if (!empty($summary)) {
			?>
			<div class="entry-summary">
				<p class="lead"><?php echo wp_kses_post($summary); ?></p>
			</div>
			<?php
		}
	endif;
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
	<?php edit_post_link( __( 'Edit', 'framework' ), '<footer class="entry-meta"><span class="edit-link">', '</span></footer>' ); ?>
</article><!-- #post-## -->
