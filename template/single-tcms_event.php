<?php get_header(); ?>

<!-- single-tcms_production.php PLUGIN -->

	<div id="primary" class="site-content">
		
			<?php while ( have_posts() ) : the_post(); ?>
			
				<?php echo tcms_get_event ($post->ID, 'eventlist', true);  ?>

			<?php endwhile; // end of the loop. ?>

	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
<?php } ?>