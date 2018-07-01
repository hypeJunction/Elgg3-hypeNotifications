<?php

use Pelago\Emogrifier;

$view = elgg_view('notifications/wrapper/html/template', $vars);

$css = elgg_view('notifications/wrapper/html/template.css');
$css .= elgg_view('elements/components.css');
$css .= elgg_view('elements/buttons.css');
$css .= elgg_view('elements/typography.css');

$css = _elgg_services()->cssCompiler->compile($css);

$emogrifier = new Emogrifier($view, $css);
$emogrifier->disableStyleBlocksParsing();
$emogrifier->disableInvisibleNodeRemoval();
$emogrifier->addExcludedSelector('html');
$emogrifier->addExcludedSelector('head');
$emogrifier->addExcludedSelector('meta');
$emogrifier->addExcludedSelector('style');
$emogrifier->addExcludedSelector('title');

$content = $emogrifier->emogrify();

echo $content;