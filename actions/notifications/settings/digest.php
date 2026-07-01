<?php

$guid = (int) get_input('guid');
$user = $guid ? get_entity($guid) : null;

if (!$user instanceof ElggUser || !$user->canEdit()) {
	return elgg_error_response(elgg_echo('actionunauthorized'));
}

$params = get_input('params');

foreach ($params as $key => $value) {
	$user->setPluginSetting('hypenotifications', $key, $value);
}

elgg_ok_response('', elgg_echo('notifications:settings:digest:success'));
