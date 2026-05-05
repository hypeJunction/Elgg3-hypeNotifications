<?php

namespace hypeJunction\Notifications;

use Elgg\Event;
use Elgg\Notifications\NotificationEvent;

class SendSiteNotification {

	/**
	 * Deliver site notification
	 *
	 * @elgg_plugin_hook send notification:site
	 *
	 * @param Hook $hook Hook
	 *
	 * @return bool|null
	 */
	public function __invoke(Event $event) {

		$is_sent = $event->getValue();

		if ($is_sent === true) {
			// another handler sent the notification
			return true;
		}

		$notification = $event->getParam('notification');
		/* @var $notification \Elgg\Notifications\Notification */

		$event = $event->getParam('event');
		/* @var $event NotificationEvent */

		$site_notification = new Notification();
		$site_notification->setRecipient($notification->getRecipient());
		if ($event instanceof NotificationEvent) {
			$site_notification->setAction($event->getAction());
			$site_notification->setActor($event->getActor() ?: $notification->getSender());
			$site_notification->setObject($event->getObject() ?: null);
		} else {
			$site_notification->setActor($notification->getSender());
		}

		$site_notification->setData((array) $notification->toObject());
		if ($site_notification->save()) {
			return true;
		}
	}

}