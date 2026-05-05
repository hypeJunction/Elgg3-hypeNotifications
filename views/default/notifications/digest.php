<?php

$notifications = elgg_extract('notifications', $vars);

if (empty($notifications)) {
	return;
}

$site = elgg_get_site_entity();
$intro = elgg_echo('notifications:digest:body_intro', [$site->name]);

if (elgg_get_plugin_setting('enable_html_emails', 'hypenotifications')) {
	echo elgg_format_element('p', [], $intro);

	$items = [];
	foreach ($notifications as $notification) {
		if (!$notification instanceof \hypeJunction\Notifications\DigestNotification) {
			continue;
		}

		$items[] = elgg_format_element('li', [
			'class' => 'elgg-item',
		], $notification->data['body']);
	}

	echo elgg_format_element('ul', [
		'class' => 'elgg-list',
	], implode('', $items));
} else {
	$hr = PHP_EOL . '----------------------------------------------' . PHP_EOL;

	echo $intro;

	echo $hr;

	foreach ($notifications as $notification) {
		if (!$notification instanceof \hypeJunction\Notifications\DigestNotification) {
			continue;
		}

		echo $notification->data['body'];
		echo $hr;
	}
}
