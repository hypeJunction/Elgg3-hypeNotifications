<?php

namespace hypeJunction\Notifications;

use Elgg\Event;
use ElggEntity;
use ElggExtender;
use ElggRelationship;

class SyncEntityDelete {

	/**
	 * Remove rows from notification table when actor, recipient or object is deleted
	 *
	 * @elgg_event delete all
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
			$svc->getTable()->deleteByEntityGUID($object->guid);
		} else if ($object instanceof ElggExtender || $object instanceof ElggRelationship) {
			$svc->getTable()->deleteByExtenderID($object->id, $object->getType());
		}
	}
}