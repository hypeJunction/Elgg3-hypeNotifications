<?php

namespace hypeJunction\Notifications;

use Elgg\Email;
use Elgg\Hook;

class ValidateEmail {

	/**
	 * Validate whitelisted email
	 *
	 * @param Hook $hook Hook
	 * @return bool|null
	 */
	public function __invoke(Hook $hook) {

		$email = $hook->getParam('email');

		if (!$email instanceof Email) {
			return null;
		}

		if (elgg_get_plugin_setting('mode', 'hypeNotifications') == 'staging') {
			$to_address = $email->getTo()->getEmail();

			if (!EmailWhitelist::isWhitelisted($to_address)) {
				return false;
			}
		}
	}
}