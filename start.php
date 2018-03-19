<?php

/**
 * hypeNotifications
 *
 * Enhanced on-site and off-site notifications
 *
 * @author    Ismayil Khayredinov <info@hypejunction.com>
 * @copyright Copyright (c) 2017-2018, Ismayil Khayredinov
 */
require_once __DIR__ . '/autoloader.php';

return function () {
	elgg_register_event_handler('init', 'system', function () {

		// Digests
		elgg_register_plugin_hook_handler('send', 'all', \hypeJunction\Notifications\ScheduleDigest::class, 100);
		elgg_register_plugin_hook_handler('cron', 'hourly', \hypeJunction\Notifications\SendDigest::class);

		// Site notifications
		elgg_register_notification_method('site');
		elgg_register_plugin_hook_handler('send', 'notification:site', \hypeJunction\Notifications\SendSiteNotification::class, 400);

		elgg_register_event_handler('update', 'all', \hypeJunction\Notifications\SyncEntityUpdate::class, 999);
		elgg_register_event_handler('delete', 'all', \hypeJunction\Notifications\SyncEntityDelete::class, 999);
		elgg_register_event_handler('create', 'user', \hypeJunction\Notifications\SyncNewUser::class);
		elgg_register_event_handler('create', 'relationship', \hypeJunction\Notifications\SyncNewMember::class);

		elgg_register_plugin_hook_handler('view', 'profile/details', \hypeJunction\Notifications\DismissProfileNotifications::class);
		elgg_register_plugin_hook_handler('view', 'groups/profile/layout', \hypeJunction\Notifications\DismissProfileNotifications::class);

		elgg_register_plugin_hook_handler('view', 'object/default', \hypeJunction\Notifications\DismissObjectNotifications::class);
		elgg_register_plugin_hook_handler('view', 'post/elements/full', \hypeJunction\Notifications\DismissObjectNotifications::class);

		$subtypes = (array) get_registered_entity_types('object');
		foreach ($subtypes as $subtype) {
			elgg_register_plugin_hook_handler('view', "object/$subtype", \hypeJunction\Notifications\DismissObjectNotifications::class);
		}

		elgg_register_plugin_hook_handler('elgg.data', 'site', \hypeJunction\Notifications\SetClientConfig::class);

		// Email notifications and transport
		elgg_set_email_transport(elgg()->{'email.transport'}->build());
		elgg_register_plugin_hook_handler('format', 'notification:email', \hypeJunction\Notifications\FormatEmailNotification::class, 999);
		elgg_register_plugin_hook_handler('prepare', 'system:email', \hypeJunction\Notifications\PrepareEmail::class, 999);
		elgg_register_plugin_hook_handler('validate', 'system:email', \hypeJunction\Notifications\ValidateEmail::class);
		elgg_register_plugin_hook_handler('zend:message', 'system:email', \hypeJunction\Notifications\AddHtmlEmailPart::class);

		// Menus
		elgg_register_plugin_hook_handler('register', 'menu:topbar', \hypeJunction\Notifications\TopbarMenu::class);
		elgg_register_plugin_hook_handler('register', 'menu:page', \hypeJunction\Notifications\PageMenu::class);

		// Views
		elgg_extend_view('page/elements/topbar', 'notifications/popup');

		elgg_extend_view('elgg.css', 'notifications/notifications.css');
		elgg_extend_view('admin.css', 'notifications/notifications.css');

	});
};