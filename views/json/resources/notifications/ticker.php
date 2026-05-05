<?php

elgg_gatekeeper();

$unseen = hypeapps_count_notifications([
	'status' => 'unseen',
]);

echo json_encode([
	'new' => $unseen,
]);
