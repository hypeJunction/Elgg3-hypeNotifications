<?php

namespace hypeJunction\Notifications;

use Elgg\Config;
use Elgg\PluginHooksService;
use hypeJunction\Embed\File;
use Zend\Mail\Transport\FileOptions;
use Zend\Mail\Transport\Sendmail;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mail\Transport\TransportInterface;

class EmailTransport {

	/**
	 * @var Config
	 */
	protected $config;

	/**
	 * @var PluginHooksService
	 */
	protected $hooks;

	/**
	 * Constructor
	 *
	 * @param Config             $config Config
	 * @param PluginHooksService $hooks  Hook
	 */
	public function __construct(Config $config, PluginHooksService $hooks) {
		$this->config = $config;
		$this->hooks = $hooks;
	}

	/**
	 * Returns email transport
	 *
	 * @return TransportInterface
	 */
	public function build() {

		$name = $this->config->{'email.transport'};

		switch ($name) {
			default :
				$transport = new Sendmail();
				break;

			case 'file' :
				$dirname = $this->config->dataroot . 'notifications_log/zend/';
				if (!is_dir($dirname)) {
					mkdir($dirname, 0700, true);
				}
				$options = [
					'path' => $dirname,
					'callback' => function () {
						return 'Message_' . microtime(true) . '_' . mt_rand() . '.txt';
					},
				];
				$transport = new File(new FileOptions($options));
				break;

			case 'smtp' :
				$options = array_filter([
					'name' => $this->config->{'email.smtp_host_name'},
					'host' => $this->config->{'email.smtp_host'},
					'port' => $this->config->{'email.smtp_port'},
					'connection_class' => $this->config->{'email.smtp_connection'},
					'connection_config' => array_filter([
						'username' => $this->config->{'email.smtp_username'},
						'password' => $this->config->{'email.smtp_password'},
						'ssl' => $this->config->{'email.smtp_ssl'},
					]),
				]);
				$transport = new Smtp(new SmtpOptions($options));
				break;

			case 'sparkpost' :
				$transport = new SparkPostEmailTransport($this->config->{'email.sparkpost_apikey'});
				break;

			case 'mailgun' :
				$transport = new MailgunEmailTransport($this->config->{'email.mailgun_apikey'}, $this->config->{'email.mailgun_domain'});
				break;

			case 'sendgrid' :
				$transport = new SendGridEmailTransport($this->config->{'email.sendgrid_apikey'});
				break;
		}

		return $this->hooks->trigger('email:transport', 'system', null, $transport);
	}
}