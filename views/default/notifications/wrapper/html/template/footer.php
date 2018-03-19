<?php

$email = elgg_extract('email', $vars);

if (!$email instanceof \Elgg\Email) {
	return;
}

$to = $email->getTo()->getEmail();
$users = get_user_by_email($to);
if (!$users) {
	return;
}

$recipient = array_shift($users);

$site = elgg_get_site_entity();

$site_link = elgg_view('output/url', array(
	'href' => $site->getURL(),
	'text' => $site->name,
));

$settings_link = elgg_view('output/url', array(
	'href' => elgg_generate_url('settings:notification:personal', [
		'username' => $recipient->username,
	]),
	'text' => elgg_echo('notifications:footer:link'),
));

echo elgg_echo('notifications:footer', array(
	$site_link,
	$settings_link,
));