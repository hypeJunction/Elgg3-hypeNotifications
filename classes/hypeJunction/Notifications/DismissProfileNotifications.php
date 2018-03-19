<?php

namespace hypeJunction\Notifications;

use Elgg\Hook;

class DismissProfileNotifications {

	/**
	 * Dismiss user/group notifications when their profile is viewed
	 *
	 * @elgg_plugin_hook view <view_name>
	 *
	 * @param Hook $hook Hook
	 *
	 * @return void
	 * @throws \DatabaseException
	 */
	public function __invoke(Hook $hook) {

		$return = $hook->getValue();

		if (empty($return)) {
			return;
		}

		if (elgg_in_context('action')) {
			return;
		}

		$vars = $hook->getParam('vars');

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