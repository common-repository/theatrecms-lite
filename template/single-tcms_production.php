<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */

get_header(); ?>

<!-- single-tcms_production.php PLUGIN -->

	<div id="primary" class="site-content">
		
			<?php while ( have_posts() ) : the_post(); ?>
			
				<?php $filtered = tcms_show_production ($post->ID, 'full', true); $shortcoded = do_shortcode($filtered); echo $shortcoded; ?>
<?php the_terms( $post->ID, 'theatre_artists', 'Artists: ', ' <span>/</span> ', '' ); ?>
				<?php comments_template( '', true ); ?>

			<?php endwhile; // end of the loop. ?>

	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>