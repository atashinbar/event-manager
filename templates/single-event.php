<?php get_header(); ?>
<div class="event-single">
	<img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'full'); ?>" alt="<?php the_title(); ?>" />
	<div class="event-details">
		<h1><?php the_title(); ?></h1>
		<p><?php the_content(); ?></p>
		<p><strong><?php _e('Event Date:', 'event-manager'); ?></strong> <?php echo get_post_meta(get_the_ID(), '_em_event_date', true); ?></p>
		<p><strong><?php _e('Location:', 'event-manager'); ?></strong> <?php echo get_post_meta(get_the_ID(), '_em_event_location', true); ?></p>
	</div>
</div>
<?php
get_footer();
