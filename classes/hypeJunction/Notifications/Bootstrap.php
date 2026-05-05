<?php

namespace hypeJunction\Notifications;

use Elgg\DefaultPluginBootstrap;

class Bootstrap extends DefaultPluginBootstrap {

	public function activate() {
		$sql_file = $this->getPlugin()->getPath() . 'install/mysql.sql';
		if (!file_exists($sql_file)) {
			return;
		}

		$prefix = \elgg()->db->prefix;
		$sql = file_get_contents($sql_file);
		$sql = str_replace('prefix_', $prefix, $sql);

		$connection = \elgg()->db->getConnection('write');
		foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
			$connection->executeStatement($stmt);
		}
	}

	public function init() {
		// Site notifications
		\elgg_register_notification_method('site');

		// Dynamic event handler registrations for object subtypes
		$subtypes = (array) \get_registered_entity_types('object');
		foreach ($subtypes as $subtype) {
			\elgg_register_event_handler('view', "object/$subtype", DismissObjectNotifications::class);
		}

		// Email transport is wired via elgg-services.php DI override (no call needed here)
	}
}
