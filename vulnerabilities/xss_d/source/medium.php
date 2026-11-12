<?php

// Is there any input?
if ( array_key_exists( "default", $_GET ) && !is_null ($_GET[ 'default' ]) ) {
	$default = $_GET['default'];

	# Whitelist allowed languages only
	$allowed = array( 'English', 'French', 'Spanish', 'German' );
	if( !in_array( $default, $allowed ) ) {
		header( "location: ?default=English" );
		exit;
	}
}

?>
