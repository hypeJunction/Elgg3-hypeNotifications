<?php

$user = elgg_get_logged_in_user_entity();
$site = elgg_get_site_entity();

$recipient = get_input('recipient');
$subject = get_input('subject');
$body = get_input('body', '', false);

$attachments = [];
$uploads = elgg_get_uploaded_files('attachments');
if (!empty($uploads)) {
	foreach ($uploads as $upload) {
		if ($upload && $upload->isValid()) {
			$file = new ElggFile();
			$file->owner_guid = $user->guid;
			$file->access_id = ACCESS_PRIVATE;
			$file->acceptUploadedFile($upload);
			$attachments[] = $file;
		}
	}
}

$result = elgg_send_email(null, $recipient, $subject, $body, array(
	'attachments' => $attachments,
		), 'email');

foreach ($attachments as $attachment) {
	$attachment->delete();
}

if ($result) {
	return elgg_ok_response('', elgg_echo('admin:notifications:test_email:success'));
} else {
	return elgg_error_response(elgg_echo('admin:notifications:test_email:error'));
}