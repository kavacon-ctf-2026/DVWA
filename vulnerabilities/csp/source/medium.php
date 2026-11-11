<?php

// Fresh, unpredictable nonce for every response and no 'unsafe-inline'
$cspNonce = base64_encode( random_bytes( 16 ) );
$headerCSP = "Content-Security-Policy: script-src 'self' 'nonce-" . $cspNonce . "'; object-src 'none'; base-uri 'self';";

header($headerCSP);

?>
<?php
if (isset ($_POST['include'])) {
// User input is output-encoded so it can never inject markup or script
$page[ 'body' ] .= "
	" . htmlspecialchars( (string) $_POST['include'], ENT_QUOTES, 'UTF-8' ) . "
";
}
$page[ 'body' ] .= '
<form name="csp" method="POST">
	<p>Whatever you enter here gets dropped directly into the page, see if you can get an alert box to pop up.</p>
	<input size="50" type="text" name="include" value="" id="include" />
	<input type="submit" value="Include" />
</form>
';
