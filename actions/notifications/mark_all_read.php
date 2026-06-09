<?php

$guid = (int) get_input('guid');
$user = $guid ? get_entity($guid) : null;

if (!$user || !$user->canEdit()) {
	return elgg_error_response(elgg_echo('actionunauthorized'));
}

hypeapps_mark_all_notifications_read($user->guid);
