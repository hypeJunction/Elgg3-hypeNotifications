<?php

$limit = get_input('limit');
$offset = get_input('offset');

$personal_methods = (array) get_input('personal', []);
$friends_methods = (array) get_input('friends', []);
$groups_methods = (array) get_input('groups', []);

$users = elgg_get_entities([
	'types' => 'user',
	'limit' => $limit,
	'offset' => $offset,
	'batch' => true,
		]);

$i = 0;

foreach ($users as $user) {
	/* @var $user ElggUser */

	foreach ($personal_methods as $method) {
		$user->setNotificationSetting($method, true);
	}

	if (!empty($friends_methods)) {
		$metaname = 'collections_notifications_preferences_' . $method;
		$user->$metaname = -1; // enable for new friends
		
		$friends = elgg_get_entities([
			'types' => 'user',
			'relationship' => 'friend',
			'relationship_guid' => $user->guid,
			'limit' => 0,
			'callback' => false,
			'batch' => true,
		]);

		foreach ($friends as $friend) {
			foreach ($friends_methods as $method) {
				elgg_add_subscription($user->guid, $method, $friend->guid);
			}
		}
	}

	if (!empty($groups_methods)) {
		$groups = elgg_get_entities([
			'types' => 'group',
			'relationship' => 'member',
			'relationship_guid' => $user->guid,
			'inverse_relationship' => true,
			'limit' => 0,
			'callback' => false,
			'batch' => true,
		]);

		foreach ($groups as $group) {
			foreach ($groups_methods as $method) {
				elgg_add_subscription($user->guid, $method, $group->guid);
			}
		}
	}

	$i++;
}

return elgg_ok_response();

