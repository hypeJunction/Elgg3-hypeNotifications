<?php

namespace hypeJunction\Notifications;

use Elgg\Hook;
use Elgg\Notifications\InstantNotificationEvent;
use Elgg\Notifications\NotificationEvent;
use ElggEntity;
use ElggObject;

class ScheduleDigest {

	/**
	 * Respect user notification event preferences
	 *
	 * @elgg_plugin_hook send all
	 * @param Hook $hook Hook
	 *
	 * @return bool|null
	 */
	public function __invoke(Hook $hook) {

		$type = $hook->getType();

		list($prefix, $method) = explode(':', $type);

		if ($prefix !== 'notification') {
			return null;
		}

		$event = $hook->getParam('event');

		if (!$event instanceof NotificationEvent) {
			return null;
		}

		$notification = $hook->getParam('notification');

		if (!$notification instanceof Notification) {
			return null;
		}

		$action = $event->getAction();
		$object = $event->getObject();
		$recipient_guid = $notification->getRecipientGUID();

		if ($object instanceof ElggEntity && !$object instanceof ElggObject) {
			$entity_type = $object->getType();
			$entity_subtype = 'default';
		} else if ($object instanceof \ElggData) {
			$entity_type = $object->getType();
			$entity_subtype = $object->getSubtype();
		}

		$event_type = $event instanceof InstantNotificationEvent ? 'instant' : 'subscriptions';

		$setting_name = "$event_type:$action:$entity_type:$entity_subtype";
		$setting_value = elgg_get_plugin_user_setting($setting_name, $recipient_guid, 'hypeNotifications', DigestService::INSTANT);

		if ($setting_value == DigestService::NEVER) {
			// set notification as sent
			return true;
		}

		if ($method != 'email' || $setting_value == DigestService::INSTANT || !$setting_value) {
			// let the handler deliver it
			return null;
		}

		// Store in the database
		$time_schedule = elgg()->{'notifications.digest'}->getNextDeliveryTime($setting_value);

		$digest_notification = new DigestNotification();
		$digest_notification->setRecipient($notification->getRecipient());
		$digest_notification->setData((array) $notification->toObject());
		$digest_notification->setTimeScheduled($time_schedule);

		if ($digest_notification->save()) {
			return true;
		}
	}

}