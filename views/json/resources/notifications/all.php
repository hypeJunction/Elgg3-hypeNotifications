<?php

elgg_gatekeeper();

$user = elgg_get_logged_in_user_entity();

elgg_set_viewtype('default');

elgg_set_http_header('Content-Type: application/json');

$notifications = hypeapps_get_notifications([
	'limit' => 10,
]);

$count = hypeapps_count_notifications();

$list = elgg_view('notifications/list', [
	'items' => $notifications,
	'full_view' => false,
	'no_results' => elgg_echo('notifications:no_results'),
]);

$unseen = hypeapps_count_notifications([
	'status' => 'unseen',
]);

echo json_encode([
	'list' => $list,
	'new' => $unseen,
	'count' => $count,
]);

