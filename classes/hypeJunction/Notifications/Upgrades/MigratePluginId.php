<?php

namespace hypeJunction\Notifications\Upgrades;

use Elgg\Upgrade\AsynchronousUpgrade;
use Elgg\Upgrade\Result;

/**
 * Migrates plugin settings from the Elgg 3.x plugin entity (title='hypeNotifications')
 * to the Elgg 4.x entity (title='hypenotifications').
 *
 * In Elgg 3.x the manifest <id> was 'hypeNotifications' (camelCase). Elgg 4.x
 * derives the plugin ID from the directory name ('hypenotifications', lowercase).
 * Because Elgg matches plugin entities by title, the 3.x entity is orphaned on
 * upgrade and all admin-configured settings (ticker, mode, staging_emails, etc.)
 * become inaccessible.
 */
class MigratePluginId extends AsynchronousUpgrade {

	const OLD_ID = 'hypeNotifications';
	const NEW_ID = 'hypenotifications';

	public function getVersion(): int {
		return 2026041701;
	}

	public function shouldBeSkipped(): bool {
		return !$this->getOldPluginEntity() instanceof \ElggPlugin;
	}

	public function needsIncrementOffset(): bool {
		return false;
	}

	public function countItems(): int {
		return AsynchronousUpgrade::UNKNOWN_COUNT;
	}

	public function run(Result $result, $offset): Result {
		$old = $this->getOldPluginEntity();
		if (!$old instanceof \ElggPlugin) {
			$result->markComplete();
			return $result;
		}

		$new = elgg_get_plugin_from_id(self::NEW_ID);
		if (!$new instanceof \ElggPlugin) {
			$result->addError(self::NEW_ID . ' plugin entity not found; cannot migrate settings');
			$result->markComplete();
			return $result;
		}

		$settings = $old->getAllPrivateSettings();
		foreach ($settings as $name => $value) {
			if ($name === \ElggPlugin::PRIORITY_SETTING_NAME) {
				continue;
			}

			if ($new->getSetting($name) === null) {
				if ($new->setSetting($name, $value)) {
					$result->addSuccesses();
				} else {
					$result->addError("hypenotifications: failed to migrate setting '{$name}'");
					$result->addFailures();
				}
			} else {
				$result->addSuccesses();
			}
		}

		$result->markComplete();
		return $result;
	}

	private function getOldPluginEntity(): ?\ElggPlugin {
		$entities = elgg_get_entities([
			'type' => 'object',
			'subtype' => 'plugin',
			'metadata_name_value_pairs' => [
				['name' => 'title', 'value' => self::OLD_ID],
			],
			'limit' => 1,
			'ignore_access' => true,
		]);

		return $entities[0] instanceof \ElggPlugin ? $entities[0] : null;
	}
}
