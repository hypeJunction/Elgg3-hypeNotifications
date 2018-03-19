<?php

namespace hypeJunction\Notifications;

use Elgg\Hook;
use ElggMenuItem;
use ElggUser;

class PageMenu {

	/**
	 * Setup page menu
	 *
	 * @param Hook $hook Hook
	 * @return ElggMenuItem[]
	 */
	public function __invoke(Hook $hook) {

		$menu = $hook->getValue();

		if (elgg_in_context('settings')) {
			$page_owner = elgg_get_page_owner_entity();
			if ($page_owner instanceof ElggUser) {
				$menu[] = ElggMenuItem::factory([
					'name' => 'notifications:digest',
					'text' => elgg_echo('notifications:settings:digest'),
					'href' => elgg_generate_url('settings:notification:digest', [
						'username' => $page_owner->username,
					]),
					'section' => 'notifications',
				]);
			}
		}

		$menu[] = ElggMenuItem::factory([
			'name' => 'notifications',
			'text' => elgg_echo('admin:notifications'),
			'href' => '#',
			'section' => 'configure',
			'context' => ['admin'],
		]);

		$menu[] = ElggMenuItem::factory([
			'name' => 'notifications:settings',
			'text' => elgg_echo('settings'),
			'href' => 'admin/plugin_settings/hypeNotifications',
			'section' => 'configure',
			'parent_name' => 'notifications',
			'context' => ['admin'],
		]);

		$menu[] = ElggMenuItem::factory([
			'name' => 'notifications:methods',
			'text' => elgg_echo('admin:notifications:methods'),
			'href' => 'admin/notifications/methods',
			'section' => 'configure',
			'parent_name' => 'notifications',
			'context' => ['admin'],
		]);

		$menu[] = ElggMenuItem::factory([
			'name' => 'notifications:test_email',
			'text' => elgg_echo('admin:notifications:test_email'),
			'href' => 'admin/notifications/test_email',
			'section' => 'configure',
			'parent_name' => 'notifications',
			'context' => ['admin'],
		]);

		return $menu;
	}
}