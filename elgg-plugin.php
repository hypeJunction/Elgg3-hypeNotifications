<?php

return [
	'actions' => [
		'admin/notifications/methods' => [
			'access' => 'admin',
		],
		'admin/notifications/test_email', [
			'access' => 'admin',
		],
		'notifications/mark_all_read' => [],
		'notifications/mark_read' => [],
		'notifications/settings/digest' => [],
		'hypeNotifications/settings/save' => [
			'access' => 'admin',
		],
	],
	'routes' => [
		'collection:notification:owner' => [
			'path' => '/notifications/all/{username?}',
			'resource' => 'notifications/all',
		],
		'view:notification' => [
			'path' => '/notifications/view/{id}',
			'resource' => 'notifications/view',
		],
		'settings:notification:digest' => [
			'path' => '/notifications/settings/digest/{username?}',
			'resource' => 'notifications/settings/digest',
		],
		'ajax:notifications:ticker' => [
			'path' => '/notifications/ticker',
			'resource' => 'notifications/ticker',
		],
	],
	'upgrades' => [
		\hypeJunction\Notifications\MigrateNotifier::class,
	],
];