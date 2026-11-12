<?php

if( isset( $_POST[ 'Submit' ]  ) ) {
	// Get input
	$target = $_REQUEST[ 'ip' ];

	// Validate that input is a valid IPv4 address (4 numeric octets)
	$octet = explode( ".", $target );
	if( (is_numeric( $octet[0] )) && (is_numeric( $octet[1] )) && (is_numeric( $octet[2] )) && (is_numeric( $octet[3] )) && ( sizeof( $octet ) == 4 ) ) {
		$target = $octet[0].'.'.$octet[1].'.'.$octet[2].'.'.$octet[3];

		// Determine OS and execute the ping command.
		if( stristr( php_uname( 's' ), 'Windows NT' ) ) {
			$cmd = shell_exec( 'ping  ' . $target );
		} else {
			$cmd = shell_exec( 'ping  -c 4 ' . $target );
		}

		// Feedback for the end user
		$html .= "<pre>{$cmd}</pre>";
	}
	else {
		$html .= '<pre>ERROR: You have entered an invalid IP.</pre>';
	}
}

?>
