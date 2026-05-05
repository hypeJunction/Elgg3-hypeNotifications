<?php

$user = elgg_extract('entity', $vars);
if (!$user) {
	return;
}

$limit = get_input('limit', 20);
$offset = get_input('offset', 0);

$notifications = hypeapps_get_notifications([
	'recipient_guid' => $user->guid,
	'limit' => $limit,
	'offset' => $offset,
]);

$count = hypeapps_count_notifications([
	'recipient_guid' => $user->guid,
]);

echo elgg_view('notifications/list', [
	'items' => $notifications,
	'count' => $count,
	'pagination' => true,
	'offset' => $offset,
	'limit' => $limit,
	'full_view' => false,
]);
