<?php

namespace hypeJunction\Notifications;

use Elgg\Email;
use Elgg\Email\Address;
use Elgg\Hook;

class PrepareEmail {

	/**
	 * Prepare email
	 *
	 * @param Hook $hook Hook
	 *
	 * @return bool|null
	 */
	public function __invoke(Hook $hook) {

		$email = $hook->getValue();

		if (!$email instanceof Email) {
			return null;
		}

		if (elgg_get_plugin_setting('mode', 'hypeNotifications') == 'staging') {
			$to_address = $email->getTo()->getEmail();

			if (!EmailWhitelist::isWhitelisted($to_address)) {
				$catch_all = elgg_get_plugin_setting('staging_catch_all', 'hypeNotifications');
				if ($catch_all) {
					$email->setTo(new Address($catch_all));
				}
			}
		}

		if ($from_email = elgg_get_plugin_setting('from_email', 'hypeNotifications')) {
			$from = $email->getFrom();

			$params = $email->getParams();
			$params['original_from'] = $from;
			$email->setParams($params);

			$email->setFrom(new Address($from_email, $from->getName()));
		}

		return $email;
	}
}