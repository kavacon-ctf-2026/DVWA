<?php
$headerCSP = "Content-Security-Policy: script-src 'self';";

header($headerCSP);

?>
<?php
// Do NOT echo user-supplied POST data as raw HTML – that creates a direct HTML injection vector
// even with CSP in place (attackers can inject tags that load scripts from the same origin).
$page[ 'body' ] .= '
<form name="csp" method="POST">
	<p>The page makes a call to ' . DVWA_WEB_PAGE_TO_ROOT . '/vulnerabilities/csp/source/jsonp.php to load some code. Modify that page to run your own code.</p>
	<p>1+2+3+4+5=<span id="answer"></span></p>
	<input type="button" id="solve" value="Solve the sum" />
</form>

<script src="source/high.js"></script>
';
