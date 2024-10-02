<?php

class Test_Event_Meta extends WP_UnitTestCase
{
	// Test if empty meta data is handled correctly
	public function test_empty_event_meta()
	{
		$post_id = $this->factory->post->create(array(
			'post_type' => 'event',
			'post_title' => 'Test Event with Empty Meta'
		));

		// No meta data is set, should return an empty value
		$meta_value = get_post_meta($post_id, '_em_event_date', true);
		$this->assertEmpty($meta_value, 'Meta value should be empty when no data is set');
	}
}
