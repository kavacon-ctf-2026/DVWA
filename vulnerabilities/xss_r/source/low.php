<?php

header ("X-XSS-Protection: 0");

// Is there any input?
if( array_key_exists( "name", $_GET ) && $_GET[ 'name' ] != NULL ) {
	// Feedback for end user (output-encoded to prevent XSS)
	$html .= '<pre>Hello ' . htmlspecialchars( (string) $_GET[ 'name' ], ENT_QUOTES, 'UTF-8' ) . '</pre>';
}

?>
