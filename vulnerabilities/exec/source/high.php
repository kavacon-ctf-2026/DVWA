<?php

if( isset( $_POST[ 'Submit' ]  ) ) {
	// Get input
	$target = trim($_REQUEST[ 'ip' ]);

	// Validate that input is a proper IPv4 address
	$octet = explode( ".", $target );
	if( count($octet) == 4 &&
		is_numeric($octet[0]) && is_numeric($octet[1]) &&
		is_numeric($octet[2]) && is_numeric($octet[3]) ) {

		$target = $octet[0].'.'.$octet[1].'.'.$octet[2].'.'.$octet[3];

		// Determine OS and execute the ping command.
		if( stristr( php_uname( 's' ), 'Windows NT' ) ) {
			$cmd = shell_exec( 'ping  ' . $target );
		}
		else {
			$cmd = shell_exec( 'ping  -c 4 ' . $target );
		}

		$html .= "<pre>{$cmd}</pre>";
	}
	else {
		$html .= '<pre>ERROR: You have entered an invalid IP.</pre>';
	}
}

?>
