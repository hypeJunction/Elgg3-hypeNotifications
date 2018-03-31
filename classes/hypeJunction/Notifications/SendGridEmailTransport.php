<?php

namespace hypeJunction\Notifications;

use Elgg\Email;
use SendGrid\Attachment;
use SendGrid\Content;
use Zend\Mail;
use Zend\Mail\Transport\TransportInterface;
use Zend\Mime\Mime;

class SendGridEmailTransport implements TransportInterface {

	/**
	 * @var string
	 */
	private $api_key;

	/**
	 * Constructor
	 *
	 * @param string $api_key API key
	 */
	public function __construct($api_key) {
		$this->api_key = $api_key;
	}

	/**
	 * Send a mail message
	 *
	 * @param \Zend\Mail\Message $message
	 *
	 * @return void
	 */
	public function send(Mail\Message $message) {

		$subject = $message->getSubject();

		$from = new \SendGrid\Email($message->getSender()->getName(), $message->getSender()->getEmail());

		$to = [];
		foreach ($message->getTo() as $recipient) {
			$to[] = new \SendGrid\Email($recipient->getName(), $recipient->getName());
		}

		$parts = $message->getBody()->getParts();

		$html = null;
		$text = null;
		$attachments = [];

		foreach ($parts as $part) {
			$type = $part->getType();
			switch ($type) {
				case Mime::TYPE_TEXT :
					$text = new Content($type, $part->getContent());
					break;

				case Mime::TYPE_HTML :
					$html = new Content($type, $part->getContent());
					break;

				default :
					$attachment = new Attachment();
					$attachment->setFilename($part->getFileName());
					$attachment->setType($part->getType());
					$attachment->setContent($part->getRawContent());

					$attachments[] = $attachment;
			}
		}

		$primary_to = array_shift($to);

		$mail = new \SendGrid\Mail($from, $subject, $primary_to, $text);
		$mail->addContent($html);
		foreach ($attachments as $attachment) {
			$mail->addAttachment($attachment);
		}

		$sg = new \SendGrid($this->api_key);

		try {
			$response = $sg->client->mail()->send()->post($mail);

			if ($response->statusCode() !== 200) {
				throw new Mail\Exception\RuntimeException($response->body(), $response->statusCode());
			}
		} catch (\Exception $ex) {
			elgg_log("SendGrid: " . $ex->getMessage(), 'ERROR');
			throw new Mail\Exception\RuntimeException($ex->getMessage(), $ex->getCode());
		}

	}
}