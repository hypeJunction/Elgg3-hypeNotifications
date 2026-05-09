<?php

if (!elgg_is_logged_in()) {
	return;
}

elgg_import_esm('notifications/popup');

$list = elgg_format_element('div', [
	'id' => 'notifications-messages'
]);

$footer = elgg_view('output/url', [
	'href' => elgg_normalize_url('notifications/all'),
	'text' => elgg_echo('notifications:all'),
	'is_trusted' => true,
]);

$footer = elgg_format_element('div', ['class' => 'elgg-foot'], $footer);
$body = $list . $footer;

echo elgg_format_element('div', [
	'class' => 'elgg-module elgg-module-popup elgg-notifications-popup hidden',
	'id' => 'notifications-popup'
], $body);
