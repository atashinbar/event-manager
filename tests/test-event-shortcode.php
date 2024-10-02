<?php

class Test_Event_Shortcode extends WP_UnitTestCase
{
	// Test if the [events_list] shortcode outputs the expected content
	public function test_event_shortcode_output()
	{
		// Create a test event
		$post_id = $this->factory->post->create(array(
			'post_type' => 'event',
			'post_title' => 'Sample Event'
		));

		// Test the output of the [events_list] shortcode
		$shortcode_output = do_shortcode('[events_list]');
		$this->assertStringContainsString('Sample Event', $shortcode_output);
	}
}
