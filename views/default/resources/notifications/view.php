<?php

elgg_gatekeeper();

$id = elgg_extract('id', $vars);

$notification = hypeapps_get_notification_by_id($id);

if (!$notification instanceof \hypeJunction\Notifications\Notification) {
	forward('', '404');
}

$user = $notification->getRecipient();
if (!$user || !$user->canEdit()) {
	forward('', '404');
}

elgg_push_breadcrumb(elgg_echo('notifications'), 'notifications/all');
elgg_push_breadcrumb(elgg_echo('notification'));

elgg_set_page_owner_guid($user->guid);

elgg_set_context('settings');

$title = elgg_echo('notifications');

if (!$notification->isRead()) {
	$notification->markAsRead();
}

$target = $notification->getTargetURL();
if ($target) {
	forward($target);
}

$content = elgg_view('notifications/notification', [
	'item' => $notification,
	'full_view' => true,
		]);

if (elgg_is_xhr()) {
	echo $content;
	return;
}

$layout = elgg_view_layout('content', [
	'title' => $title,
	'content' => $content,
	'filter' => '',
		]);

echo elgg_view_page($title, $layout);
