<?php

namespace hypeJunction\Notifications;

use Elgg\Hook;
use ElggMenuItem;

class TopbarMenu {

	/**
	 * Setup topbar menu
	 *
	 * @param Hook $hook Hook
	 *
	 * @return ElggMenuItem[]|null
	 * @throws \DatabaseException
	 */
	public function __invoke(Hook $hook) {

		$user = elgg_get_logged_in_user_entity();
		if (!$user) {
			return null;
		}

		$menu = $hook->getValue();

		$counter = '';

		$count = hypeapps_count_notifications([
			'status' => 'unseen',
			'recipient_guid' => $user->guid,
		]);

		if ($count) {
			if ($count > 99) {
				$count = '99+';
			}

			$counter = elgg_format_element('span', [
				'id' => 'notifications-new',
				'class' => $count ? 'notifications-unread-count messages-new' : 'notifications-unread-count messages-new hidden',
			], $count);
		}

		$menu[] = ElggMenuItem::factory([
			'name' => 'notifications',
			'href' => 'notifications/all#notifications-popup',
			'text' => '',
			'icon' => 'bell',
			'badge' => $counter,
			'priority' => 600,
			'tooltip' => elgg_echo('notifications:thread:unread', [$count]),
			'rel' => 'popup',
			'id' => 'notifications-popup-link',
			'data-position' => json_encode([
				'my' => 'center top',
				'at' => 'center bottom',
				'of' => '#notifications-popup-link',
				'collision' => 'fit fit',
			]),
		]);

		return $menu;
	}
}