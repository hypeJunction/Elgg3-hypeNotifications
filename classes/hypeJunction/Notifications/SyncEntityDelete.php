<?php

namespace hypeJunction\Notifications;

use Elgg\Event;
use ElggEntity;
use ElggExtender;
use ElggRelationship;

/**
 * SyncEntityDelete class.
 */
class SyncEntityDelete {

	/**
	 * Remove rows from notification table when actor, recipient or object is deleted
	 *
	 * @elgg_event delete all
	 *
	 * @param Event $event Event
	 *
	 * @return void
	 */
	public function __invoke(Event $event) {

		$svc = elgg()->{'notifications.site'};
		/* @var $svc SiteNotificationsService */

		$object = $event->getObject();

		try {
			if ($object instanceof ElggEntity) {
				$svc->getTable()->deleteByEntityGUID($object->guid);
			} else if ($object instanceof ElggExtender || $object instanceof ElggRelationship) {
				$svc->getTable()->deleteByExtenderID($object->id, $object->getType());
			}
		} catch (\Elgg\Exceptions\DatabaseException $e) {
			// Table may not exist yet if plugin is not yet activated
		}
	}
}
