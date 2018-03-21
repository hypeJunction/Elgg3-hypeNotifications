<?php

use hypeJunction\Notifications\Notification;

$notification = elgg_extract('item', $vars);
$full = elgg_extract('full_view', $vars, false);

if (!$notification instanceof Notification) {
	return;
}

$actor = $notification->getActor();
$object = $notification->getObject();

$size = elgg_extract('size', $vars, 'small');
$icon = elgg_view_entity_icon($actor, $size, [
	'use_hover' => false,
	'use_link' => false,
	'href' => false,
		]);

if (!$notification->isSeen()) {
	$notification->markAsSeen();
}

$class = ['notification-item'];
if (!$notification->isRead()) {
	$class[] = 'notification-new';
}

if ($full) {
	$content = elgg_view('output/longtext', [
		'value' => $notification->getBody(),
	]);
	if (!$notification->isRead()) {
		$notification->markAsRead();
	}
	$class[] = 'notification-full-listing';
} else {
	$content = elgg_view('output/longtext', [
		'value' => $notification->getSummary(),
	]);
	$class[] = 'notification-summary-listing';
}

$extras = elgg_format_element('span', [
	'class' => 'notification-time',
], elgg_view_friendly_time($notification->time_created));

$params = $vars;

$data = $notification->getData();
if (!empty($data['links']) && !$notification->isRead()) {
	foreach ($data['links'] as $name => $link) {
		$params['items'][] = [
			'name' => $name,
			'text' => elgg_echo("notifications:link:$name"),
			'href' => $link
		];
	}
}

$params['class'] = 'elgg-menu-hz';

$extras .= elgg_view_menu('notification', $params);

$content .= elgg_format_element('div', [
	'class' => 'notification-extras',
], $extras);

echo elgg_view_image_block($icon, $content, [
	'class' => $class,
	'data-id' => $notification->id,
]);