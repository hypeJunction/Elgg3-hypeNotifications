<?php

elgg_import_esm('forms/admin/notifications/methods');

echo elgg_format_element('p', [
	'class' => 'elgg-text-help',
], elgg_echo('admin:notifications:methods:help'));

$methods = elgg_get_notification_methods();
$options = [];
foreach ($methods as $method) {
	$label = elgg_echo("notification:method:$method");
	$options[$label] = $method;
}

echo elgg_view_field([
	'#type' => 'checkboxes',
	'#label' => elgg_echo('admin:notifications:methods:personal'),
	'name' => 'personal',
	'default' => false,
	'options' => $options,
]);

echo elgg_view_field([
	'#type' => 'checkboxes',
	'#label' => elgg_echo('admin:notifications:methods:friends'),
	'name' => 'friends',
	'default' => false,
	'options' => $options,
]);

echo elgg_view_field([
	'#type' => 'checkboxes',
	'#label' => elgg_echo('admin:notifications:methods:groups'),
	'name' => 'groups',
	'default' => false,
	'options' => $options,
]);

echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'limit',
	'value' => 3,
]);

echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'offset',
	'value' => 0,
]);

$count = elgg_get_entities([
	'types' => 'user',
	'count' => true,
]);

echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'count',
	'value' => $count,
]);
?>

<div class="elgg-field">
	<div class="elgg-progressbar"></div>
</div>

<?php
$footer = elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('save'),
]);

elgg_set_form_footer($footer);
