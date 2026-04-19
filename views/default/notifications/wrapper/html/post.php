<?php
$notification = elgg_extract('notification', $vars);

if (!$notification instanceof \Elgg\Notifications\Notification) {
	return;
}

$actor = $notification->getSender();
if (!$actor instanceof ElggUser) {
	return;
}

$file = $actor->getIcon('small');
if (!$file->exists()) {
	return;
}

$icon = elgg_view('output/url', [
	'href' => $actor->getURL(),
'text' => elgg_view('output/img', [
		'src' => elgg_get_inline_url($file, false),
		'alt' => $actor->getDisplayName(),
	]),
	'class' => 'user-icon',
		]);

$body = elgg_autop($notification->body);
?>
<table class="image-block">
	<tr>
		<td>
			<?php echo $icon ?>
		</td>
		<td>
			<?php echo $body ?>
		</td>
	</tr>
</table>
