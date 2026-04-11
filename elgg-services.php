<?php

return [
	'email.transport' => \DI\create(\hypeJunction\Notifications\EmailTransport::class)
		->constructor(\DI\get('config'), \DI\get('hooks')),

	'db.notifications' => \DI\create(\hypeJunction\Notifications\SiteNotificationsTable::class)
		->constructor(\DI\get('db')),

	'db.digest' => \DI\create(\hypeJunction\Notifications\DigestTable::class)
		->constructor(\DI\get('db')),

	'notifications.site' => \DI\create(\hypeJunction\Notifications\SiteNotificationsService::class)
		->constructor(\DI\get('db.notifications')),

	'notifications.digest' => \DI\create(\hypeJunction\Notifications\DigestService::class)
		->constructor(\DI\get('db.digest')),
];