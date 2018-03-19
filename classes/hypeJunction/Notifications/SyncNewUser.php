<?php

namespace hypeJunction\Notifications;

use Elgg\Event;

class SyncNewUser {

	/**
	 * Enable site notifications for new users
	 *
	 * @elgg_event create user
	 *
	 * @param Event $event Event
	 * @return void
	 */
	public function __invoke(Event $event) {

		$user = $event->getObject();

		if (!$user instanceof \ElggUser) {
			return;
		}

		$user->setNotificationSetting('site', true);

		$metaname = 'collections_notifications_preferences_site';
		$user->$metaname = -1; // enable for new friends
	}

}