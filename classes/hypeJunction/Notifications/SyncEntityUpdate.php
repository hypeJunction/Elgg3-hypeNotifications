<?php

namespace hypeJunction\Notifications;

use Elgg\Event;
use ElggData;
use ElggEntity;

class SyncEntityUpdate {

	/**
	 * Update access levels
	 *
	 * @elgg_event update all
	 *
	 * @param Event $event Event
	 *
	 * @return void
	 * @throws \DatabaseException
	 */
	public function __invoke(Event $event) {
		$svc = elgg()->{'notifications.site'};
		/* @var $svc SiteNotificationsService */

		$object = $event->getObject();

		if ($object instanceof ElggEntity) {
			$attributes = $object->getOriginalAttributes();
			if (array_key_exists('access_id', $attributes)) {
				$svc->getTable()->updateAccess($object);
			}
		} else if ($object instanceof ElggData) {
			$svc->getTable()->updateAccess($object);
		}
	}
}