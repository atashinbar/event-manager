<?php

class Test_Register_Event_Post_Type extends WP_UnitTestCase
{
	// Test if the custom post type is registered
	public function test_event_post_type_registered()
	{
		$post_type = 'event';
		$this->assertTrue(post_type_exists($post_type), 'Custom post type "event" should be registered');
	}

	// Test if custom taxonomy is registered
	public function test_event_taxonomy_registered()
	{
		$taxonomy = 'event_type';
		$this->assertTrue(taxonomy_exists($taxonomy), 'Custom taxonomy "event_type" should be registered');
	}
}
