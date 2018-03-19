<?php

namespace hypeJunction\Notifications;

use Elgg\Hook;

class SetClientConfig {

	/**
	 * Set client-side data
	 *
	 * @elgg_plugin_hook elgg.data site
	 *
	 * @param Hook $hook Hook
	 *
	 * @return array
	 */
	public function __invoke(Hook $hook) {
		$return = $hook->getValue();

		$return['notifications']['ticker'] = (int) elgg_get_plugin_setting('ticker', 'hypeNotifications', 60);

		return $return;
	}
}