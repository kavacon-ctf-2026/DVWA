<?php

header ("X-XSS-Protection: 0");

// Is there any input?
if( array_key_exists( "name", $_GET ) && $_GET[ 'name' ] != NULL ) {
	// htmlspecialchars neutralises all HTML injection – preg_replace("script") can be bypassed
	$name = htmlspecialchars( $_GET[ 'name' ] );

	$html .= "<pre>Hello {$name}</pre>";
}

?>
