<?php

if ( array_key_exists( "default", $_GET ) && !is_null( $_GET[ 'default' ] ) ) {
	$allowed = array( 'English', 'French', 'Spanish', 'German' );
	if( !in_array( $_GET['default'], $allowed ) ) {
		header( "location: ?default=English" );
		exit;
	}
}

?>
