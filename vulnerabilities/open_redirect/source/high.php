<?php

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
	$target = $_GET['redirect'];
	// Only allow local (relative) targets whose path is the info page: no scheme,
	// no scheme-relative "//", no backslashes, whitespace or control characters
	$targetPath = is_string ($target) ? parse_url ($target, PHP_URL_PATH) : null;
	if (is_string ($target)
		&& !preg_match ('/[\x00-\x20\x7f\\\\]/', $target)
		&& substr ($target, 0, 2) !== "//"
		&& !preg_match ('/^[a-z][a-z0-9+.\-]*:/i', $target)
		&& is_string ($targetPath)
		&& basename ($targetPath) === "info.php") {
		header ("location: " . $target);
		exit;
	} else {
		http_response_code (500);
		?>
		<p>You can only redirect to the info page.</p>
		<?php
		exit;
	}
}

http_response_code (500);
?>
<p>Missing redirect target.</p>
<?php
exit;
?>
