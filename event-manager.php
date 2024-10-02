<?php

/**
 * Plugin Name:     Event Manager
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          Ali Atashin Bar
 * Author URI:      YOUR SITE HERE
 * Text Domain:     event-manager
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         Event_Manager
 */


// don't load directly.
if (! defined('ABSPATH')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit;
}


class Event_Manager
{
	/**
	 * Instance of this class.
	 *
	 * @since   1.0.0
	 * @access  public
	 */
	public static $instance;


	/**
	 * Provides access to a single instance
	 *
	 * @since   1.0.0
	 * @return  object
	 */
	public static function instance()
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct()
	{
		self::set_hooks($this);
	}


	/**
	 * Set Hooks
	 *
	 */
	public static function set_hooks($This)
	{
		add_action('init', array($This, 'em_register_event_post_type'), 9);
		add_action('add_meta_boxes', array($This, 'em_add_event_meta_boxes'));
		add_action('save_post', array($This, 'em_save_event_meta'));

		// admin section
		add_filter('manage_event_posts_columns', array($This, 'em_set_custom_columns'));
		add_action('manage_event_posts_custom_column', array($This, 'em_custom_column'), 10, 2);

		add_shortcode('event_list', array($This, 'em_event_list_shortcode'));

		add_filter('template_include', array($This, 'myplugin_custom_templates'));

		// Search filters
		add_action('wp_ajax_em_event_search', array($This, 'em_event_search_ajax'));
		add_action('wp_ajax_nopriv_em_event_search', array($This, 'em_event_search_ajax'));
		add_action('wp_enqueue_scripts', array($This, 'em_enqueue_scripts'));
		add_filter('script_loader_tag', array($This, 'em_defer_scripts'), 10, 3);


		// Email Notification and RSVP
		add_action('save_post_event', array($This, 'em_send_event_notification'), 10, 3);
		add_action('template_redirect', array($This, 'em_handle_rsvp'));
		add_action('add_meta_boxes', array($This, 'em_add_rsvp_meta_box'));

		// Register Meta in Rest API
		add_action('rest_api_init', array($This, 'em_register_event_meta'));

		// Custom endpoint
		add_action('rest_api_init', array($This, 'em_register_custom_rest_routes'));

		// Clear the transient
		add_action('save_post', array($This, 'em_clear_event_cache'));
		add_action('delete_post', array($This, 'em_clear_event_cache'));
	}


	/**
	 * Load Plugin Text Domain
	 *
	 */
	public function load_plugin_textdomain(): void
	{

		load_plugin_textdomain(
			'event-manager',
			false,
			dirname(plugin_basename(__FILE__)) . '/languages'
		);
	}

	/**
	 * Register Custom Post Type "Event" and Taxonomy "Event Type"
	 *
	 */
	function em_register_event_post_type()
	{
		$labels = array(
			'name' => __('Events', 'event-manager'),
			'singular_name' => __('Event', 'event-manager'),
			'menu_name' => __('Events', 'event-manager'),
			'name_admin_bar' => __('Event', 'event-manager'),
			'add_new' => __('Add New Event', 'event-manager'),
			'add_new_item' => __('Add New Event', 'event-manager'),
			'new_item' => __('New Event', 'event-manager'),
			'edit_item' => __('Edit Event', 'event-manager'),
			'view_item' => __('View Event', 'event-manager'),
			'all_items' => __('All Events', 'event-manager'),
			'search_items' => __('Search Events', 'event-manager'),
			'not_found' => __('No Events found.', 'event-manager'),
			'not_found_in_trash' => __('No Events found in Trash.', 'event-manager'),
		);

		$args = array(
			'label' => __('Event', 'event-manager'),
			'description' => __('Event Post Type', 'event-manager'),
			'labels' => $labels,
			'supports' => array('title', 'editor', 'thumbnail', 'custom-fields', 'revisions'),
			'public' => true,
			'hierarchical' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_icon' => 'dashicons-calendar-alt',
			'show_in_rest' => true, // For REST API integration
			'has_archive' => true,
			'rewrite' => array('slug' => 'events'),
			'capability_type' => 'post',
			'menu_position' => 5,
		);
		register_post_type('event', $args);

		// Register Event Type Taxonomy
		$taxonomy_labels = array(
			'name' => __('Event Types', 'event-manager'),
			'singular_name' => __('Event Type', 'event-manager'),
		);

		$taxonomy_args = array(
			'labels' => $taxonomy_labels,
			'hierarchical' => true,
			'public' => true,
			'show_ui' => true,
			'show_in_rest' => true,
			'rewrite' => array('slug' => 'event-type'),
		);
		register_taxonomy('event_type', array('event'), $taxonomy_args);
	}

	/**
	 * Add Meta Boxes
	 *
	 */
	public function em_add_event_meta_boxes()
	{
		add_meta_box(
			'em_event_details',
			__('Event Details', 'event-manager'),
			array($this, 'em_render_event_meta_box'),
			'event',
			'normal',
			'high'
		);
	}

	/**
	 * Render Meta Box Content
	 *
	 */
	public function em_render_event_meta_box($post)
	{
		wp_nonce_field('em_save_event_meta', 'em_event_nonce');
		$event_date = get_post_meta($post->ID, '_em_event_date', true);
		$event_location = get_post_meta($post->ID, '_em_event_location', true);

		echo '<label for="em_event_date">' . __('Event Date', 'event-manager') . '</label>';
		echo '<input type="date" id="em_event_date" name="em_event_date" value="' . esc_attr($event_date) . '" /><br/><br/>';

		echo '<label for="em_event_location">' . __('Event Location', 'event-manager') . '</label>';
		echo '<input type="text" id="em_event_location" name="em_event_location" value="' . esc_attr($event_location) . '" />';
	}

	/**
	 * Save Meta Box Data
	 *
	 */
	public function em_save_event_meta($post_id)
	{
		if (!isset($_POST['em_event_nonce']) || !wp_verify_nonce($_POST['em_event_nonce'], 'em_save_event_meta')) {
			return;
		}

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		if (isset($_POST['em_event_date'])) {
			update_post_meta($post_id, '_em_event_date', sanitize_text_field($_POST['em_event_date']));
		}

		if (isset($_POST['em_event_location'])) {
			update_post_meta($post_id, '_em_event_location', sanitize_text_field($_POST['em_event_location']));
		}
	}

	/**
	 * Customize Admin Columns
	 *
	 */
	public function em_set_custom_columns($columns)
	{
		$columns['event_date'] = __('Event Date', 'event-manager');
		$columns['event_location'] = __('Event Location', 'event-manager');
		return $columns;
	}

	/**
	 * Populate Admin Columns
	 *
	 */
	public function em_custom_column($column, $post_id)
	{
		switch ($column) {
			case 'event_date':
				$event_date = get_post_meta($post_id, '_em_event_date', true);
				echo esc_html($event_date);
				break;

			case 'event_location':
				$event_location = get_post_meta($post_id, '_em_event_location', true);
				echo esc_html($event_location);
				break;
		}
	}

	/**
	 * Shortcode to Display Event Listings and Search
	 *
	 */
	public function em_event_list_shortcode($atts)
	{
		ob_start();
?>
		<!-- Search Form -->
		<form id="event-search-form" method="post">
			<input type="text" name="s" placeholder="<?php echo esc_html__('Search Events', 'event-manager'); ?>" />

			<!-- Event Type Dropdown -->
			<select name="event_type">
				<option value=""><?php echo esc_html__('All Event Types', 'event-manager'); ?></option>
				<?php
				$terms = get_terms('event_type');
				foreach ($terms as $term) {
					echo '<option value="' . esc_attr($term->slug) . '">' . esc_html($term->name) . '</option>';
				}
				?>
			</select>

			<!-- Date Input -->
			<input type="date" name="event_date" placeholder="<?php echo esc_html__('Event Date', 'event-manager'); ?>" />

			<!-- Location Input -->
			<input type="text" name="event_location" placeholder="<?php echo esc_html__('Location', 'event-manager'); ?>" />

			<input type="submit" value="<?php echo esc_html__('Search', 'event-manager'); ?>" />
		</form>

		<!-- Event Results -->
		<div id="event-list-wrap">
			<?php
			// Check if cached events exist
			$cached_events = get_transient('em_cached_events');
			if (false === $cached_events) {
				// No cache, run the query
				// Display events by default
				$args = array(
					'post_type' => 'event',
					'posts_per_page' => -1
				);
				$cached_events = new WP_Query($args);
				// Cache for 12 hours
				set_transient('em_cached_events', $cached_events, 12 * HOUR_IN_SECONDS);
			}
			if ($cached_events->have_posts()) {
				while ($cached_events->have_posts()) {
					$cached_events->the_post();
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
			} else {
				echo '<p>' . esc_html__('No events found', 'event-manager') . '</p>';
			}

			wp_reset_postdata();
			?>
		</div>

		<!-- AJAX Script -->
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				$('#event-search-form').on('submit', function(e) {
					e.preventDefault();

					var formData = $(this).serialize();

					$.ajax({
						url: '<?php echo admin_url("admin-ajax.php"); ?>',
						method: 'POST',
						data: {
							action: 'em_event_search',
							formData: formData
						},
						success: function(response) {
							$('#event-list-wrap').html(response);
						}
					});
				});
			});
		</script>
		<?php
		return ob_get_clean();
	}

	/**
	 * Search Event
	 *
	 */
	public function em_event_search_ajax()
	{
		// Parse the search data sent from the form
		parse_str($_POST['formData'], $search_data);

		$meta_query = array();
		$tax_query = array();

		// Keyword search
		$args = array(
			'post_type' => 'event',
			'posts_per_page' => -1,
			's' => isset($search_data['s']) ? sanitize_text_field($search_data['s']) : '',
		);

		// Event Type filter
		if (isset($search_data['event_type']) && $search_data['event_type'] != '') {
			$tax_query[] = array(
				'taxonomy' => 'event_type',
				'field'    => 'slug',
				'terms'    => sanitize_text_field($search_data['event_type']),
			);
		}

		// Event Date filter
		if (isset($search_data['event_date']) && $search_data['event_date'] != '') {
			$meta_query[] = array(
				'key'     => '_em_event_date',
				'value'   => sanitize_text_field($search_data['event_date']),
				'compare' => '='
			);
		}

		// Event Location filter
		if (isset($search_data['event_location']) && $search_data['event_location'] != '') {
			$meta_query[] = array(
				'key'     => '_em_event_location',
				'value'   => sanitize_text_field($search_data['event_location']),
				'compare' => 'LIKE'
			);
		}

		// Apply meta and tax queries if necessary
		if (! empty($meta_query)) {
			$args['meta_query'] = $meta_query;
		}
		if (! empty($tax_query)) {
			$args['tax_query'] = $tax_query;
		}


		$query = new WP_Query($args);

		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();
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
		} else {
			echo '<p>' . esc_html__('No events found', 'event-manager') . '</p>';
		}

		wp_die();
	}

	/**
	 * Register style ans script
	 *
	 */
	public function em_enqueue_scripts()
	{
		wp_enqueue_script('jquery');

		// if (is_post_type_archive('event') || is_singular('event')) { // you can enable this line if you do not need to shortcode
		wp_enqueue_style('em-styles', plugin_dir_url(__FILE__) . 'assets/css/event-style.css');
		wp_enqueue_script('em-scripts', plugin_dir_url(__FILE__) . 'assets/js/event-scripts.js', array('jquery'), '1.0', true);
		// }
	}

	/**
	 * Defer Non-Critical JavaScript
	 *
	 */
	public function em_defer_scripts($tag, $handle, $src)
	{
		if ('em-scripts' !== $handle) {
			return $tag;
		}
		return '<script src="' . $src . '" defer="defer"></script>';
	}

	/**
	 * Single and Archive Template
	 *
	 */
	public function myplugin_custom_templates($template)
	{
		if (is_singular('event')) {
			// Load the single template for custom post type 'book'
			$plugin_template = plugin_dir_path(__FILE__) . 'templates/single-event.php';
			if (file_exists($plugin_template)) {
				return $plugin_template;
			}
		}

		if (is_post_type_archive('event')) {
			// Load the archive template for custom post type 'book'
			$plugin_template = plugin_dir_path(__FILE__) . 'templates/archive-event.php';
			if (file_exists($plugin_template)) {
				return $plugin_template;
			}
		}

		return $template;
	}

	/**
	 * Hook into the save post action for events
	 *
	 */
	public function em_send_event_notification($post_id, $post, $update)
	{
		// Check if the post type is 'event'
		if ($post->post_type != 'event' || wp_is_post_revision($post_id)) {
			return;
		}

		// Define the recipients (you can modify this to suit your requirement)
		$recipients = get_users(array('role__in' => array('subscriber', 'administrator')));

		// Prepare the subject and message
		$subject = $update ? 'Event Updated: ' . $post->post_title : 'New Event Published: ' . $post->post_title;

		// Generate the RSVP link
		$rsvp_link = add_query_arg(array(
			'rsvp' => 'yes',
			'event_id' => $post_id,
			'user_email' => '%s' // This will be replaced by the user's email
		), get_permalink($post_id));

		// Prepare the email content
		$message = sprintf(
			"Hello,\n\nA new event has been %s: %s.\n\nEvent Details:\nTitle: %s\nDate: %s\nLocation: %s\n\nTo RSVP, please click the link below:\n%s\n\n",
			$update ? 'updated' : 'published',
			$post->post_title,
			$post->post_title,
			get_post_meta($post_id, '_em_event_date', true),
			get_post_meta($post_id, '_em_event_location', true),
			'[RSVP LINK]'
		);

		// Loop through the recipients and send the email
		foreach ($recipients as $user) {
			$user_rsvp_link = sprintf($rsvp_link, urlencode($user->user_email));
			$user_message = str_replace('[RSVP LINK]', $user_rsvp_link, $message);

			wp_mail($user->user_email, $subject, $user_message);
		}
	}

	/**
	 * Handle RSVP confirmations
	 *
	 */
	public function em_handle_rsvp()
	{
		if (isset($_GET['rsvp']) && isset($_GET['event_id']) && isset($_GET['user_email'])) {
			$event_id = intval($_GET['event_id']);
			$user_email = sanitize_email($_GET['user_email']);

			// Check if it's a valid event and user
			if (get_post_type($event_id) === 'event' && is_email($user_email)) {
				// Get existing RSVPs
				$rsvp_list = get_post_meta($event_id, '_em_rsvp_list', true);
				if (! is_array($rsvp_list)) {
					$rsvp_list = array();
				}

				// Add user to RSVP list if not already confirmed
				if (! in_array($user_email, $rsvp_list)) {
					$rsvp_list[] = $user_email;
					update_post_meta($event_id, '_em_rsvp_list', $rsvp_list);
				}

				// Display thank you or confirmation message
				echo '<p>' . esc_html__('Thank you for confirming your attendance at the event!', 'event-manager') . '</p>';
				exit; // Exit to stop other WordPress processes
			}
		}
	}

	/**
	 * Add meta box to display RSVP list
	 *
	 */
	public function em_add_rsvp_meta_box()
	{
		add_meta_box(
			'em_rsvp_meta_box',
			__('RSVP List', 'event-manager'),
			array($this, 'em_display_rsvp_meta_box'),
			'event',
			'normal',
			'default'
		);
	}

	/**
	 * Display RSVP meta box
	 *
	 */
	public function em_display_rsvp_meta_box($post)
	{
		// Get the list of RSVPs
		$rsvp_list = get_post_meta($post->ID, '_em_rsvp_list', true);

		if (! empty($rsvp_list)) {
			echo '<ul>';
			foreach ($rsvp_list as $rsvp) {
				echo '<li>' . esc_html($rsvp) . '</li>';
			}
			echo '</ul>';
		} else {
			echo '<p>' . esc_html__('No RSVPs yet.', 'event-manager') . '</p>';
		}
	}

	/**
	 * Expose Event Meta Fields to REST API
	 *
	 */
	public function em_register_event_meta()
	{
		// Register event date meta field
		register_post_meta('event', '_em_event_date', array(
			'show_in_rest' => true, // Make this meta field accessible via REST API
			'type'         => 'string',
			'single'       => true,
		));

		// Register event location meta field
		register_post_meta('event', '_em_event_location', array(
			'show_in_rest' => true,
			'type'         => 'string',
			'single'       => true,
		));
	}

	/**
	 * Register custom rest route
	 *
	 */
	public function em_register_custom_rest_routes()
	{
		register_rest_route('em/v1', '/events/', array(
			'methods'  => 'GET',
			'callback' => array($this, 'em_get_filtered_events'),
			'permission_callback' => '__return_true',
		));
	}

	/**
	 * Custom REST API Endpoints
	 *
	 */
	public function em_get_filtered_events($data)
	{
		$args = array(
			'post_type' => 'event',
			'meta_query' => array()
		);

		if (isset($data['event_date'])) {
			$args['meta_query'][] = array(
				'key'     => '_em_event_date',
				'value'   => $data['event_date'],
				'compare' => '='
			);
		}

		// Check if cached events exist
		$cached_events = get_transient('em_cached_events');
		if (false === $cached_events) {
			// No cache, run the query
			$cached_events = new WP_Query($args);
			// Cache for 12 hours
			set_transient('em_cached_events', $cached_events, 12 * HOUR_IN_SECONDS);
		}

		$events = array();

		if ($cached_events->have_posts()) {
			while ($cached_events->have_posts()) {
				$cached_events->the_post();

				$events[] = array(
					'id'       => get_the_ID(),
					'title'    => get_the_title(),
					'content'  => get_the_content(),
					'event_date' => get_post_meta(get_the_ID(), '_em_event_date', true),
					'location'   => get_post_meta(get_the_ID(), '_em_event_location', true),
				);
			}
			wp_reset_postdata();
		}

		return new WP_REST_Response($events, 200);
	}

	/**
	 * Clear the transient when a new event is published or update
	 *
	 */
	public function em_clear_event_cache($post_id)
	{
		if (get_post_type($post_id) === 'event') {
			delete_transient('em_cached_events');
		}
	}

	/**
	 * init
	 *
	 */
	public function init()
	{
		$this->load_plugin_textdomain();
	}
}

Event_Manager::instance()->init();
