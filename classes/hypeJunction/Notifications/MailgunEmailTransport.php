<?php

namespace hypeJunction\Notifications;

use Mailgun\Exception\HttpClientException;
use Mailgun\Mailgun;
use Zend\Mail;
use Zend\Mail\Transport\TransportInterface;
use Zend\Mime\Mime;

class MailgunEmailTransport implements TransportInterface {

	/**
	 * @var string
	 */
	private $api_key;

	/**
	 * @var string
	 */
	private $domain;

	/**
	 * Constructor
	 *
	 * @param string $api_key API key
	 */
	public function __construct($api_key, $domain) {
		$this->api_key = $api_key;
		$this->domain = $domain;
	}

	/**
	 * Send a mail message
	 *
	 * @param \Zend\Mail\Message $message
	 *
	 * @return void
	 */
	public function send(Mail\Message $message) {

		$mailgun = Mailgun::create($this->api_key);

		$parts = $message->getBody()->getParts();

		$html = null;
		$text = null;
		$attachments = [];

		foreach ($parts as $part) {
			$type = $part->getType();
			switch ($type) {
				case Mime::TYPE_TEXT :
					$text = $part->getContent();
					break;

				case Mime::TYPE_HTML :
					$html = $part->getContent();
					break;

				default :
					$attachments[] = [
						'filename' => $part->getFileName(),
						'mimetype' => $part->getType(),
						'fileContent' => $part->getRawContent(),
					];
			}
		}

		$recipients = [];

		foreach ($message->getTo() as $recipient) {
			$recipients[] = $recipient->getEmail();
		}

		try {
			$mailgun->messages()->send($this->domain, [
				'from' => $message->getSender()->getEmail(),
				'to' => implode(',', $recipients),
				'subject' => $message->getSubject(),
				'text' => $text,
				'html' => $html,
				'attachment' => $attachments,
			]);
		} catch (HttpClientException $ex) {
			$body = $ex->getResponseBody();
			elgg_log("Mailgun: " . $ex->getMessage() . ' ' .json_encode($body), 'ERROR');

			throw new Mail\Exception\RuntimeException($ex->getMessage() . ' ' .json_encode($body), $ex->getCode());
		}

	}
}