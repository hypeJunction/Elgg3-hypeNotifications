<?php

namespace hypeJunction\Notifications;

use Elgg\Hook;
use Elgg\TimeUsing;

class SendDigest {

	use TimeUsing;

	/**
	 * Send digests when cron runs
	 *
	 * @elgg_plugin_hook cron hourly
	 *
	 * @param Hook $hook Hook
	 * @return void
	 * @throws \DatabaseException
	 * @throws \NotificationException
	 */
	public function __invoke(Hook $hook) {


		$time = $this->getCurrentTime()->getTimestamp();

		$svc = elgg()->{'notifications.digest'};
		/* @var $svc DigestService */

		$recipients = $svc->getTable()->getRecipients([
			'time_scheduled' => $time,
		]);

		if (empty($recipients)) {
			return;
		}

		foreach ($recipients as $recipient) {
			$notifications = $svc->getTable()->getAll([
				'recipient_guid' => $recipient,
				'time_scheduled' => $time,
			]);

			if (empty($notifications)) {
				return;
			}

			$subject = elgg_echo('notifications:digest:subject');
			$message = elgg_view('notifications/digest', [
				'notifications' => $notifications,
			]);

			$sent = notify_user($recipient, 0, $subject, $message, [], 'email');
			if ($sent) {
				foreach ($notifications as $notification) {
					$notification->delete();
				}
			}
		}
	}
}