<?php

namespace hypeJunction\Notifications;

use Elgg\Event;

class DismissProfileNotifications {

	/**
	 * Dismiss user/group notifications when their profile is viewed
	 *
	 * @elgg_plugin_hook view <view_name>
	 *
	 * @param Hook $hook Hook
	 *
	 * @return void
	 * @throws \Elgg\Exceptions\DatabaseException
	 */
	public function __invoke(Event $event) {

		$return = $event->getValue();

		if (empty($return)) {
			return;
		}

		if (elgg_in_context('action')) {
			return;
		}

		$vars = $event->getParam('vars');

		$entity = elgg_extract('entity', $vars);
		if (!$entity) {
			$entity = elgg_get_page_owner_entity();
		}

		if (!$entity instanceof \ElggEntity) {
			return;
		}

		$svc = elgg()->{'notifications.site'};
		/* @var $svc SiteNotificationsService */

		$svc->getTable()->markReadByEntityGUID($entity->guid);
	}
}