<?php

namespace hypeJunction\Notifications;

use Elgg\Hook;

class DismissObjectNotifications {

	/**
	 * Log an object listing view
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

		$vars = $hook->getParam('vars');

		$entity = elgg_extract('entity', $vars);
		if (!$entity instanceof \ElggEntity) {
			return;
		}

		$full_view = elgg_extract('full_view', $vars, false);
		if (!$full_view) {
			return;
		}

		$svc = elgg()->{'notifications.site'};
		/* @var $svc SiteNotificationsService */

		$svc->getTable()->markReadByEntityGUID($entity->guid);
	}

}