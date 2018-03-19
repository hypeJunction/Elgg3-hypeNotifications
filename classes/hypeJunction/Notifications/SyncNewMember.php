<?php

namespace hypeJunction\Notifications;

use Elgg\Event;

class SyncNewMember {

	/**
	 * Enable site notifications for new group members
	 *
	 * @elgg_event create relationship
	 *
	 * @param Event $event Event
	 *
	 * @return void
	 */
	public function __invoke(Event $event) {

		$relationship = $event->getObject();
		if (!$relationship instanceof \ElggRelationship) {
			return;
		}

		if ($relationship->relationship != 'member') {
			return;
		}

		elgg_add_subscription($relationship->guid_one, 'notifysite', $relationship->guid_two);
	}
}