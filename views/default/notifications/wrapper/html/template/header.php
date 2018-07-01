<?php

$site = elgg_get_site_entity();

$text = $site->getDisplayName();

foreach (['png', 'gif', 'jpg'] as $ext) {
	if (elgg_view_exists("theme/logo.$ext")) {
		$text = elgg_format_element('img', [
			'src' => elgg_get_simplecache_url("theme/logo.$ext"),
			'alt' => 'Logo',
		]);
	}
}

echo elgg_format_element('div', [
	'class' => 'branding'
], elgg_view('output/url', [
	'text' => $text,
	'href' => $site->getURL(),
	'class' => 'logo'
]));