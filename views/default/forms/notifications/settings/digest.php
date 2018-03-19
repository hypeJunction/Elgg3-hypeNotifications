<?php

use hypeJunction\Notifications\DigestService;

$user = elgg_extract('entity', $vars);

$notification_events = DigestService::getNotificationEvents();

$subscription_events = (array) elgg_extract('subscriptions', $notification_events);
$instant_events = (array) elgg_extract('instant', $notification_events);

echo elgg_format_element('div', [], elgg_echo('notifications:settings:digest:help'));

$frequency_options = [
	DigestService::NEVER => elgg_echo('notifications:frequency:never'),
	DigestService::INSTANT => elgg_echo('notifications:frequency:instantly'),
	DigestService::HOUR => elgg_echo('notifications:frequency:hourly'),
	DigestService::SIX_HOURS => elgg_echo('notifications:frequency:six_hours'),
	DigestService::TWELVE_HOURS => elgg_echo('notifications:frequency:twelve_hours'),
	DigestService::DAY => elgg_echo('notifications:frequency:daily'),
];

$subscriptions = '';
$subscriptions .= elgg_format_element('p', ['class' => 'margin-none'], elgg_echo('notifications:digest:subscriptions:title'));
foreach ($subscription_events as $entity_type => $entity_subtypes) {
	foreach ($entity_subtypes as $entity_subtype => $events) {
		foreach ($events as $event) {
			$setting = "subscriptions:{$event}:{$entity_type}:{$entity_subtype}";
			$value = elgg_get_plugin_user_setting($setting, $user->guid, 'hypeNotifications', DigestService::INSTANT);
			$subscriptions .= elgg_view_field([
				'#type' => 'select',
				'#label' => elgg_echo("notification:subscriptions:$event:$entity_type:$entity_subtype"),
				'name' => "params[$setting]",
				'value' => $value,
				'options_values' => $frequency_options,
			]);
		}
	}
}
if ($subscriptions) {
	echo elgg_view_module('info', elgg_echo('notifications:digest:subscriptions'), $subscriptions);
}

$instant = '';
$instant .= elgg_format_element('p', ['class' => 'margin-none'], elgg_echo('notifications:digest:instant:title'));
foreach ($instant_events as $entity_type => $entity_subtypes) {
	foreach ($entity_subtypes as $entity_subtype => $events) {
		foreach ($events as $event) {
			$setting = "instant:{$event}:{$entity_type}:{$entity_subtype}";
			$value = elgg_get_plugin_user_setting($setting, $user->guid, 'hypeNotifications', DigestService::INSTANT);
			$instant .= elgg_view_field([
				'#type' => 'select',
				'#label' => elgg_echo("notification:instant:$event:$entity_type:$entity_subtype"),
				'name' => "params[$setting]",
				'value' => $value,
				'options_values' => $frequency_options,
			]);
		}
	}
}
if ($instant) {
	echo elgg_view_module('info', elgg_echo('notifications:digest:instant'), $instant);
}

echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'guid',
	'value' => $user->guid,
]);

$footer = elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('save'),
]);

elgg_set_form_footer($footer);