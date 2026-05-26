<?php

namespace hypeJunction\Notifications;

use Elgg\Email;
use Elgg\Email\Address;
use Elgg\Event;

/**
 * PrepareEmail class.
 */
class PrepareEmail {

	/**
	 * Prepare email
	 *
	 * @param Event $event Event
	 *
	 * @return bool|null
	 */
	public function __invoke(Event $event) {

		$email = $event->getValue();

		if (!$email instanceof Email) {
			return null;
		}

		if (\elgg_get_plugin_setting('mode', 'hypenotifications') == 'staging') {
			$to_address = $email->getTo()->getEmail();

			if (!EmailWhitelist::isWhitelisted($to_address)) {
				$catch_all = \elgg_get_plugin_setting('staging_catch_all', 'hypenotifications');
				if ($catch_all) {
					$email->setTo(new Address($catch_all));
				}
			}
		}

		$from_email = \elgg_get_plugin_setting('from_email', 'hypenotifications');
		if ($from_email) {
			$from = $email->getFrom();

			$params = $email->getParams();
			$params['original_from'] = $from;
			$email->setParams($params);

			$email->setFrom(new Address($from_email, $from->getName()));
		}

		return $email;
	}
}
