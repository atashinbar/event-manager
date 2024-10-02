<?php

class Test_Event_Email extends WP_UnitTestCase
{

	// Test the RSVP email send functionality
	public function test_rsvp_email_send()
	{
		// Create a mock user email and event
		$user_email = 'testuser@example.com';
		$event_id = $this->factory->post->create(array(
			'post_type' => 'event',
			'post_title' => 'Test Event'
		));

		// Mock wp_mail() to capture email data
		add_filter('wp_mail', function ($args) use ($user_email, $event_id) {
			$this->assertEquals('RSVP Confirmation', $args['subject'], 'Email subject should be RSVP Confirmation');
			$this->assertStringContainsString('Click the link below to RSVP for the event', $args['message'], 'Email message should contain RSVP text');
			$this->assertContains($user_email, $args['to'], 'Email should be sent to the correct user');
			return $args;
		});

		// Trigger RSVP email sending function (which should internally call wp_mail)
		em_send_rsvp_email($user_email, $event_id);
	}
}
