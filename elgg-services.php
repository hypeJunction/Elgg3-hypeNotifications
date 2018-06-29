<?php

return [
	'email.transport' => \DI\object(\hypeJunction\Notifications\EmailTransport::class)
		->constructor(\DI\get('config'), \DI\get('hooks')),

	'db.notifications' => \DI\object(\hypeJunction\Notifications\SiteNotificationsTable::class)
		->constructor(\DI\get('db')),

	'db.digest' => \DI\object(\hypeJunction\Notifications\DigestTable::class)
		->constructor(\DI\get('db')),

	'notifications.site' => \DI\object(\hypeJunction\Notifications\SiteNotificationsService::class)
		->constructor(\DI\get('db.notifications')),

	'notifications.digest' => \DI\object(\hypeJunction\Notifications\DigestService::class)
		->constructor(\DI\get('db.digest')),
];