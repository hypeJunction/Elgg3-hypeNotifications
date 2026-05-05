<?php

namespace hypeJunction\Notifications;

use Elgg\Event;

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
	public function __invoke(Event $event) {
		$return = $event->getValue();

		$return['notifications']['ticker'] = (int) elgg_get_plugin_setting('ticker', 'hypenotifications', 60);

		return $return;
	}
}