<?php

class Test_Save_Event_Meta extends WP_UnitTestCase
{
	// Create a post with meta data and check if it's saved correctly
	public function test_save_event_meta()
	{
		$post_id = $this->factory->post->create(array(
			'post_type' => 'event',
			'post_title' => 'Test Event'
		));

		// Simulate meta field data
		update_post_meta($post_id, '_em_event_date', '2024-12-31');

		// Assert that meta field is saved correctly
		$this->assertEquals('2024-12-31', get_post_meta($post_id, '_em_event_date', true));
	}
}
