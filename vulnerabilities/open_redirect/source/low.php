<?php

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
	$target = $_GET['redirect'];
	// Only allow local (relative) targets: no scheme, no scheme-relative "//",
	// no backslashes, whitespace or control characters
	if (is_string ($target)
		&& !preg_match ('/[\x00-\x20\x7f\\\\]/', $target)
		&& substr ($target, 0, 2) !== "//"
		&& !preg_match ('/^[a-z][a-z0-9+.\-]*:/i', $target)) {
		header ("location: " . $target);
		exit;
	}
	http_response_code (400);
	?>
	<p>Only local redirect targets are allowed.</p>
	<?php
	exit;
}

http_response_code (500);
?>
<p>Missing redirect target.</p>
<?php
exit;
?>
