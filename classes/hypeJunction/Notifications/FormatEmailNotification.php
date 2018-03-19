<?php

namespace hypeJunction\Notifications;

use Elgg\Hook;

class FormatEmailNotification {

	/**
	 * Format email notification
	 * Wraps notification in an image block
	 *
	 * @elgg_plugin_hook format notification:email
	 *
	 * @param Hook $hook Hook
	 *
	 * @return \Elgg\Notifications\Notification|null
	 */
	public function __invoke(Hook $hook) {
		$notification = $hook->getValue();

		if (!$notification instanceof \Elgg\Notifications\Notification) {
			return null;
		}

		if (!elgg_get_plugin_setting('enable_html_emails', 'hypeNotifications')) {
			return null;
		}

		$body = elgg_view('notifications/wrapper/html/post', [
			'notification' => $notification,
		]);

		if ($body) {
			$notification->body = $body;
		}

		return $notification;
	}
}