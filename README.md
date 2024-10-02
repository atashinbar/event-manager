# Event Manager Plugin

**Event Manager** is a WordPress plugin that allows users to create and manage events with custom post types and taxonomies, enhancing the event management experience.

## Features

-   **Custom Post Type**: Adds a custom post type for events.
-   **Custom Taxonomies**: Allows categorization of events with "Event Type"
-   **Admin Interface Enhancements**: Includes custom meta boxes for additional fields such as event date and location.
-   **Shortcode**: Enables embedding event listings in posts or pages with searching filter. [event_list]
-   **Archive**: Enables to view events on this URL : https://your-site.com/events
-   **Search and Filtering**: Provides functionality to filter events by date and taxonomy in shortcode.
-   **User Notifications**: Sends email notifications when events are published or updated.
-   **RSVP Functionality**: Allows users to confirm attendance at events via link in email content.
-   **REST API Integration**: Exposes event data through the WordPress REST API. wp-json ready and custom endpoint "em/v1/events"
-   **Localization**: Prepared for translation with appropriate internationalization functions. French file is ready.

## Installation

1. **Download the plugin**:

    - Download the plugin from the [GitHub repository](https://github.com/atashinbar/event-manager).

2. **Upload the plugin**:

    - Upload the `event-manager` directory to the `/wp-content/plugins/` directory.

3. **Activate the plugin**:
    - Go to the **Plugins** menu in WordPress and activate the **Event Manager** plugin.

## Usage

After activation, you can manage events by navigating to the **Events** menu in your WordPress admin.

1. To add a new event, click **Add New Event**.
2. Fill in the event details, including the event date and location.
3. Choose the event type from the right side wordpress menu.
4. Publish your event to make it visible on the front-end.

## Shortcodes

To display a list of events, you can use the following shortcode in any post or page:

```plaintext
[event_list]
```

## Custom Endpoint Usage

To retrieve events filtered by a specific date using the custom REST API endpoint:

```plaintext
https://your-site.com/wp-json/em/v1/events/?event_date=2024-10-02
```

## User Notifications

Now, every time an event is published or updated, an email notification will be sent to all users (or a specific group like subscribers or administrators). The email will contain a link that allows users to RSVP for the event. When users click the RSVP link, their email will be recorded in the RSVP list for that event, and this list will be visible in the event's admin page.

## Test Functions

```plaintext
Test_Event_Email
Test_Event_Meta
Test_Event_REST_API
Test_Event_Shortcode
Test_Register_Event_Post_Type
Test_Save_Event_Meta
```
