<?php

return [
	'plugin' => [
		'version' => '6.0.0',
	],
	'bootstrap' => \hypeJunction\Notifications\Bootstrap::class,
	'actions' => [
		'admin/notifications/methods' => [
			'access' => 'admin',
		],
		'admin/notifications/test_email' => [
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
		\hypeJunction\Notifications\Upgrades\MigratePluginId::class,
	],
	'events' => [
		'send' => [
			'all' => [
				\hypeJunction\Notifications\ScheduleDigest::class => ['priority' => 100],
			],
			'notification:site' => [
				\hypeJunction\Notifications\SendSiteNotification::class => ['priority' => 400],
			],
		],
		'cron' => [
			'hourly' => [
				\hypeJunction\Notifications\SendDigest::class => [],
			],
		],
		'view' => [
			'profile/details' => [
				\hypeJunction\Notifications\DismissProfileNotifications::class => [],
			],
			'groups/profile/layout' => [
				\hypeJunction\Notifications\DismissProfileNotifications::class => [],
			],
			'object/default' => [
				\hypeJunction\Notifications\DismissObjectNotifications::class => [],
			],
			'post/elements/full' => [
				\hypeJunction\Notifications\DismissObjectNotifications::class => [],
			],
		],
		'elgg.data' => [
			'page' => [
				\hypeJunction\Notifications\SetClientConfig::class => [],
			],
		],
		'format' => [
			'notification:email' => [
				\hypeJunction\Notifications\FormatEmailNotification::class => ['priority' => 999],
			],
		],
		'prepare' => [
			'system:email' => [
				\hypeJunction\Notifications\PrepareEmail::class => ['priority' => 999],
			],
		],
		'validate' => [
			'system:email' => [
				\hypeJunction\Notifications\ValidateEmail::class => [],
			],
		],
		'zend:message' => [
			'system:email' => [
				\hypeJunction\Notifications\AddHtmlEmailPart::class => [],
			],
		],
		'register' => [
			'menu:topbar' => [
				\hypeJunction\Notifications\TopbarMenu::class => [],
			],
			'menu:page' => [
				\hypeJunction\Notifications\PageMenu::class => [],
			],
		],
		'update' => [
			'all' => [
				\hypeJunction\Notifications\SyncEntityUpdate::class => ['priority' => 999],
			],
		],
		'delete' => [
			'all' => [
				\hypeJunction\Notifications\SyncEntityDelete::class => ['priority' => 999],
			],
		],
		'create' => [
			'user' => [
				\hypeJunction\Notifications\SyncNewUser::class => [],
			],
			'relationship' => [
				\hypeJunction\Notifications\SyncNewMember::class => [],
			],
		],
	],
	'view_extensions' => [
		'page/elements/topbar' => [
			'notifications/popup' => [],
		],
		'elgg.css' => [
			'notifications/notifications.css' => [],
		],
		'admin.css' => [
			'notifications/notifications.css' => [],
		],
	],
];
