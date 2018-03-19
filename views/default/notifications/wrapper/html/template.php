<?php
/**
 * Template borrowed from https://github.com/mailgun/transactional-email-templates
 */
$email = elgg_extract('email', $vars);
if (!$email instanceof \Elgg\Email) {
    return;
}

$title = $email->getSubject();
$body = $email->getBody();
$body = elgg_autop($body);

$header = elgg_view('notifications/wrapper/html/template/header', $vars);
$footer = elgg_view('notifications/wrapper/html/template/footer', $vars);

$allowed = ['a', 'b', 'blockquote', 'code', 'del', 'dd', 'dl', 'dt', 'em',
	'h1', 'h2', 'h3', 'h4', 'h5', 'i', 'img', 'kbd', 'li', 'ol', 'p', 'pre',
	's', 'sup', 'sub', 'strong', 'strike', 'ul', 'br', 'hr', 'table', 'thead', 'tbody',
	'th', 'td', 'tr'];

foreach ($allowed as &$tag) {
	$tag = "<$tag>";
}

$body = strip_tags($body, implode('', $allowed));
$header = strip_tags($header, implode('', $allowed));
$footer = strip_tags($footer, implode('', $allowed));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta name="viewport" content="width=device-width" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title><?php echo $title ?></title>
	</head>
	<body itemscope itemtype="http://schema.org/EmailMessage">
		<table class="body-wrap">
			<tr>
				<td></td>
				<td class="container" width="600">
					<div class="content">
						<div class="header">
							<table width="100%">
								<tr>
									<td class="aligncenter content-block">
										<?php echo $header ?>
									</td>
								</tr>
							</table>
						</div>
						<table class="main" width="100%" cellpadding="0" cellspacing="0">
							<tr>
								<td class="content-wrap">
									<?php echo $body ?>
								</td>
							</tr>
						</table>
						<div class="footer">
							<table width="100%">
								<tr>
									<td class="aligncenter content-block">
										<?php echo $footer ?>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</td>
				<td>
				</td>
			</tr>
		</table>
	</body>
</html>

