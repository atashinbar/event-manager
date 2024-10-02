<?php

class Test_Event_REST_API extends WP_UnitTestCase
{

	// Test if the REST API endpoint for the event returns the expected data
	public function test_rest_api_event_endpoint()
	{
		// Create a test event
		$post_id = $this->factory->post->create(array(
			'post_type' => 'event',
			'post_title' => 'REST API Test Event'
		));

		// Simulate making a GET request to the REST API endpoint
		$response = rest_do_request(new WP_REST_Request('GET', '/wp/v2/event/' . $post_id));

		// Assert the response status is 200 OK
		$this->assertEquals(200, $response->get_status());

		// Assert the returned data contains the event title
		$this->assertArrayHasKey('title', $response->get_data());
		$this->assertEquals('REST API Test Event', $response->get_data()['title']['rendered']);
	}
}
