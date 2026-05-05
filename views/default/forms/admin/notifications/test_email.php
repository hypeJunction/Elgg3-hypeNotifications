<?php

$user = elgg_get_logged_in_user_entity();
$site = elgg_get_site_entity();

echo elgg_view_field([
	'#type' => 'email',
	'#label' => elgg_echo('admin:notifications:test_email:recipient'),
	'name' => 'recipient',
	'value' => $user->email,
	'required' => true,
]);

echo elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo('admin:notifications:test_email:subject'),
	'name' => 'subject',
	'value' => "Test email from $site->name",
	'required' => true,
]);

echo elgg_view_field([
	'#type' => 'longtext',
	'#label' => elgg_echo('admin:notifications:test_email:body'),
	'name' => 'body',
	'value' => elgg_view('forms/admin/notifications/test_email.html'),
	'required' => true,
]);

echo elgg_view_field([
	'#type' => 'file',
	'#label' => elgg_echo('admin:notifications:test_email:attachments'),
	'multiple' => true,
	'name' => 'attachments[]',
]);

$footer = elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('send'),
]);

elgg_set_form_footer($footer);
