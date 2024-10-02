<?php get_header(); ?>
<div class="event-archive">
	<?php
	$events = new WP_Query(array(
		'post_type' => 'event',
		'posts_per_page' => -1,
	));
	?>
	<div class="event-list-wrap">
		<?php
		while ($events->have_posts()) {
			$events->the_post();
		?>
			<div class="event-list">
				<div class="event-list-image">
					<img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'thumbnail'); ?>" alt="<?php the_title(); ?>" />
				</div>
				<div class="event-list-details">
					<a href="<?php echo get_permalink(get_the_ID()); ?>">
						<h2><?php the_title(); ?></h2>
					</a>
					<p><?php the_content(); ?></p>
					<p><?php echo esc_html__('Date', 'event-manager'); ?>: <?php echo get_post_meta(get_the_ID(), '_em_event_date', true); ?></p>
					<p><?php echo esc_html__('Location', 'event-manager'); ?>: <?php echo get_post_meta(get_the_ID(), '_em_event_location', true); ?></p>
				</div>
			</div>

		<?php
		}
		?>
	</div>
</div>
<?php
get_footer();
