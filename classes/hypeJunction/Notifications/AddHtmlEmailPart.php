<?php

namespace hypeJunction\Notifications;

use Elgg\Hook;
use Zend\Mail\Message;
use Zend\Mime\Mime;
use Zend\Mime\Part;

class AddHtmlEmailPart {

	/**
	 * Add HTML email part
	 *
	 * @param Hook $hook Hook
	 * @return Message
	 */
	public function __invoke(Hook $hook) {

		$message = $hook->getValue();
		/* @var $message Message */

		if (elgg_get_plugin_setting('enable_html_emails', 'hypeNotifications')) {

			$html_body = elgg_view('notifications/wrapper/html', [
				'email' => $hook->getParam('email'),
			]);

			if ($html_body) {
				$html_part = new Part($html_body);
				$html_part->setCharset('UTF-8');
				$html_part->setType(Mime::TYPE_HTML);

				$message->getBody()->addPart($html_part);
			}
		}

		return $message;

	}
}