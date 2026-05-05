<?php

$id = get_input('id');
$notification = hypeapps_get_notification_by_id($id);

if (!$notification) {
	return elgg_error_response();
}

$recipient = $notification->getRecipient();
if (!$recipient || !$recipient->canEdit()) {
	return elgg_error_response();
}

if (!$notification->isSeen()) {
	$notification->markAsSeen();
}

if (!$notification->isRead()) {
	$notification->markAsRead();
}
