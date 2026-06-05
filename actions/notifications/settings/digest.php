<?php

$guid = get_input('guid');
$user = get_entity($guid);

if (!$user instanceof ElggUser || !$user->canEdit()) {
	return elgg_error_response(elgg_echo('actionunauthorized'));
}

$params = get_input('params');

foreach ($params as $key => $value) {
	$user->setPluginSetting('hypenotifications', $key, $value);
}

elgg_ok_response('', elgg_echo('notifications:settings:digest:success'));
