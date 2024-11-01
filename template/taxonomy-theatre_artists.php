<?php
/**
 */

get_header(); ?>
<!-- taxonomy-theatre_artists.php PLUGIN template -->

	<div id="primary" class="site-content">

		<?php if ( have_posts() ) : ?>
			<header class="archive-header">
				<h1 class="archive-title">Biography</h1>
			</header><!-- .archive-header -->
			<?php $term_slug = get_query_var( 'term' );
			$taxonomyName = get_query_var( 'taxonomy' );
			$current_term = get_term_by( 'slug', $term_slug, $taxonomyName );
			$term_id = $current_term->term_id;
			if (function_exists('get_tax_meta')) {
				$saved_data = get_tax_meta($term_id,'tcms_headshot',true);
				$artistbio = wp_kses_post(wptexturize(wpautop(stripslashes(get_tax_meta($term_id,'tcms_biography',true)))));
			}
			echo '<div class="tcmsArtistBio"><div class="tcmsArtistHeadshot">' . wp_get_attachment_image( $saved_data[0], 'medium') . '</div>'. $artistbio . '</div>';
			
			$args = array(
						'post_type' => 'tcms_production',
						'meta_key' => 'tcms_opening',
						'orderby' => 'meta_value',
						'order' => 'DESC',
						'posts_per_page' => -1,
							'tax_query' => array(
								array(
									'taxonomy' => 'theatre_artists',
									'field' => 'id',
									'terms' => $term_id
								)
							)	
					); 
			$artistproductions = get_posts( $args );

			$args = array(
						'post_type' => 'attachment',
						'order' => 'ASC',
						'posts_per_page' => 20,
							'tax_query' => array(
								array(
									'taxonomy' => 'theatre_artists',
									'field' => 'id',
									'terms' => $term_id
								)
							)	
					); 
			$photos = get_posts( $args );

			$args = array(
						'post_type' => 'post',
						'order' => 'DESC',
						'posts_per_page' => 20,
							'tax_query' => array(
								array(
									'taxonomy' => 'theatre_artists',
									'field' => 'id',
									'terms' => $term_id
								)
							)	
					); 
			$relatedposts = get_posts( $args );

			?>

		<?php else : ?>
			<?php get_template_part( 'content', 'none' ); ?>
		<?php endif; ?>
		
		<?php wp_enqueue_script('jquery-ui-tabs');  wp_enqueue_script('tcms-biotabs'); ?>
		<div id="bioTabs">
			<ul>			
				<li><a href="#productions">Productions</a></li>
				<?php if( $photos) { ?>
				<li><a href="#photos">Photos</a></li>
				<?php } ?>
				<?php if( $relatedposts) { ?>
				<li><a href="#posts">More</a></li>
				<?php } ?>
			</ul> <!-- tabs -->
			<div>	<!-- panes -->		
				<?php 						
				if (( $artistproductions ) and function_exists('tcms_show_production')) { 
					echo '<div id="productions" class="subpane tcmsBioProductions">';
					foreach($artistproductions as $related) : setup_postdata($related);
						$productionID = $related->ID;
						echo tcms_show_production ($productionID, 'excerpt', false); 
					endforeach;
				echo '</div>';
				}
				if( $photos ) { 
					echo '<div id="photos" class="subpane tcmsBioPhotos">';
					foreach($photos as $photo) : setup_postdata($photo);
						echo wp_get_attachment_link( $photo->ID, array(100,100) );
					endforeach;
					echo '</div>';
					}
				if( $relatedposts ) { 
					echo '<div id="posts" class="subpane tcmsBioPosts">';
					foreach($relatedposts as $thepost) : setup_postdata($thepost); ?>
						<h1 class="entry-title">
							<a href="<?php echo get_permalink( $thepost->ID ); ?>" title="<?php echo $thepost->post_title; ?>" rel="bookmark"><?php echo $thepost->post_title; ?></a>
						</h1>
						
					<?php endforeach;
					echo '</div>';
					}
				?>
				</div>	<!-- panes -->		
			</div>
		</div>
		<?php wp_reset_query(); ?>
 
<?php get_sidebar(); ?>
<?php get_footer(); ?>