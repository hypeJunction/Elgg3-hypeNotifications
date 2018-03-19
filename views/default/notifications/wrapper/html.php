<?php

use Pelago\Emogrifier;

$view = elgg_view('notifications/wrapper/html/template', $vars);

$css = elgg_view('notifications/wrapper/html/template.css');
$css .= elgg_view('elements/components.css');
$css .= elgg_view('elements/buttons.css');

$css = _elgg_services()->cssCompiler->compile($css);

$emogrifier = new Emogrifier($view, $css);
echo $emogrifier->emogrify();
