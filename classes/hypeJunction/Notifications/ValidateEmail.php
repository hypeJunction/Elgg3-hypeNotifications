<?php

namespace hypeJunction\Notifications;

use Elgg\Email;
use Elgg\Event;

/**
 * ValidateEmail class.
 */
class ValidateEmail {

	/**
	 * Validate whitelisted email
	 *
	 * @param Event $event Event
	 * @return bool|null
	 */
	public function __invoke(Event $event) {

		$email = $event->getParam('email');

		if (!$email instanceof Email) {
			return null;
		}

		if (\elgg_get_plugin_setting('mode', 'hypenotifications') == 'staging') {
			$to_address = $email->getTo()->getEmail();

			if (!EmailWhitelist::isWhitelisted($to_address)) {
				return false;
			}
		}
	}
}
