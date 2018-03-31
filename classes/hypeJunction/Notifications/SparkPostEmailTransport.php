<?php

namespace hypeJunction\Notifications;

use Zend\Mail;
use Zend\Mail\Transport\TransportInterface;
use SparkPost\SparkPost;
use GuzzleHttp\Client;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use Zend\Mime\Mime;

class SparkPostEmailTransport implements TransportInterface {

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

		$httpClient = new GuzzleAdapter(new Client());
		$sparky = new SparkPost($httpClient, ['key' => $this->api_key]);

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
						'name' => $part->getFileName(),
						'type' => $part->getType(),
						'data' => base64_encode($part->getRawContent()),
					];
			}
		}

		$recipients = [];

		foreach ($message->getTo() as $recipient) {
			$recipients[] = [
				'address' => [
					'name' => $recipient->getName(),
					'email' => $recipient->getEmail(),
				]
			];
		}

		$sender_name = $message->getSender()->getName();
		$sender_email = $message->getSender()->getEmail();

		$options = [];

		if (elgg_get_plugin_setting('mode', 'hypeNotifications') == 'staging') {
			$options['sandbox'] = true;
		}

		$promise = $sparky->transmissions->post([
			'content' => [
				'from' => [
					'name' => $sender_name,
					'email' => $sender_email,
				],
				'subject' => $message->getSubject(),
				'html' => $html,
				'text' => $text,
				'attachments' => $attachments,
			],
			'recipients' => $recipients,
			'options' => $options,
		], $message->getHeaders()->toArray());

		try {
			$response = $promise->wait();
			$code = $response->getStatusCode();
			if ($code !== 200) {
				throw new Mail\Exception\RuntimeException($response->getBody(), $code);
			}
		} catch (\Exception $e) {
			elgg_log("SparkPost: " . $e->getMessage(), 'ERROR');
			throw new Mail\Exception\RuntimeException($e->getMessage(), $e->getCode());
		}

	}
}