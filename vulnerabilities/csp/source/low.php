<?php

// Only allow scripts from this origin; no third-party hosts that serve user-controlled content
$headerCSP = "Content-Security-Policy: script-src 'self'; object-src 'none'; base-uri 'self';";

header($headerCSP);

?>
<?php
if (isset ($_POST['include'])) {
// Never turn user input into a <script src>; just echo it back as encoded text
$page[ 'body' ] .= "
	<p>Including scripts from user-supplied URLs is not allowed: " . htmlspecialchars( (string) $_POST['include'], ENT_QUOTES, 'UTF-8' ) . "</p>
";
}
$page[ 'body' ] .= '
<form name="csp" method="POST">
	<p>You can include scripts from external sources, examine the Content Security Policy and enter a URL to include here:</p>
	<input size="50" type="text" name="include" value="" id="include" />
	<input type="submit" value="Include" />
</form>
<p>
	You will probably need to do some reading up on what some of the domains allowed by the CSP do and how they can be used.
</p>
';
