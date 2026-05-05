<?php

namespace hypeJunction\Notifications;

use Elgg\Event;
use Laminas\Mail\Message;
use Laminas\Mime\Mime;
use Laminas\Mime\Part;

class AddHtmlEmailPart {

	/**
	 * Add HTML email part
	 *
	 * @param Hook $hook Hook
	 * @return Message
	 */
	public function __invoke(Event $event) {

		$message = $event->getValue();
		/* @var $message Message */

		if (elgg_get_plugin_setting('enable_html_emails', 'hypenotifications')) {

$html_body = elgg_view('notifications/wrapper/html', [
				'email' => $event->getParam('email'),
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