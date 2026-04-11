<?php

namespace hypeJunction\Notifications;

use Elgg\DefaultPluginBootstrap;

class Bootstrap extends DefaultPluginBootstrap {

	public function init() {
		// Site notifications
		\elgg_register_notification_method('site');

		// Dynamic view hook registrations for object subtypes
		$subtypes = (array) \get_registered_entity_types('object');
		foreach ($subtypes as $subtype) {
			\elgg_register_plugin_hook_handler('view', "object/$subtype", DismissObjectNotifications::class);
		}

		// Email notifications and transport
		\elgg_set_email_transport(\elgg()->{'email.transport'}->build());
	}
}
