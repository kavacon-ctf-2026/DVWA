<?php

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
	$target = $_GET['redirect'];
	// Only allow local (relative) targets: no scheme, no scheme-relative "//",
	// no backslashes, whitespace or control characters
	if (!is_string ($target)
		|| preg_match ('/[\x00-\x20\x7f\\\\]/', $target)
		|| substr ($target, 0, 2) === "//"
		|| preg_match ('/^[a-z][a-z0-9+.\-]*:/i', $target)) {
		http_response_code (400);
		?>
		<p>Absolute URLs not allowed.</p>
		<?php
		exit;
	} else {
		header ("location: " . $target);
		exit;
	}
}

http_response_code (500);
?>
<p>Missing redirect target.</p>
<?php
exit;
?>
