<?php

namespace hypeJunction\Notifications;

use Elgg\Event;
use Elgg\Traits\TimeUsing;

/**
 * SendDigest class.
 */
class SendDigest {

	use TimeUsing;

	/**
	 * Send digests when cron runs
	 *
	 * @elgg_plugin_hook cron hourly
	 *
	 * @param Event $event Event
	 * @return void
	 * @throws \Elgg\Exceptions\DatabaseException
	 * @throws \NotificationException
	 */
	public function __invoke(Event $event) {


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
			$recipient_entity = $recipient ? get_entity((int) $recipient) : null;
			if (!$recipient_entity instanceof \ElggUser) {
				continue;
			}

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

			$email = \Elgg\Email::factory([
				'to' => $recipient_entity,
				'subject' => $subject,
				'body' => $message,
			]);

			$sent = elgg_send_email($email);
			if ($sent) {
				foreach ($notifications as $notification) {
					$notification->delete();
				}
			}
		}
	}
}
