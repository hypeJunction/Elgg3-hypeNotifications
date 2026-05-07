<?php

namespace hypeJunction\Notifications;

use Elgg\DefaultPluginBootstrap;

/**
 * Bootstrap class.
 */
class Bootstrap extends DefaultPluginBootstrap {

	/**
	 * {@inheritdoc}
	 */
	public function activate() {
		$sql_file = $this->plugin->getPath() . 'install/mysql.sql';
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

	/**
	 * {@inheritdoc}
	 */
	public function init() {
		// Site notifications
		\elgg_register_notification_method('site');

		// Email transport is wired via elgg-services.php DI override (no call needed here)
		// View-based dismiss handlers are registered via elgg-plugin.php events section
	}
}
