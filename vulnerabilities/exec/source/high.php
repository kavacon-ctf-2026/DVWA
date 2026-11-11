<?php

if( isset( $_POST[ 'Submit' ]  ) ) {
	// Get input
	$target = trim( (string)$_REQUEST[ 'ip' ] );

	// Allow only a well-formed IP address (allowlist instead of a bypassable blacklist)
	if( filter_var( $target, FILTER_VALIDATE_IP ) !== false ) {
		$target = escapeshellarg( $target );

		// Determine OS and execute the ping command.
		if( stristr( php_uname( 's' ), 'Windows NT' ) ) {
			// Windows
			$cmd = shell_exec( 'ping  ' . $target );
		}
		else {
			// *nix
			$cmd = shell_exec( 'ping  -c 4 ' . $target );
		}

		// Feedback for the end user
		$html .= '<pre>' . htmlspecialchars( (string)$cmd ) . '</pre>';
	}
	else {
		$html .= '<pre>ERROR: You have entered an invalid IP.</pre>';
	}
}

?>
