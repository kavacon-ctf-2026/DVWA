<?php

// The page we wish to display
$file = $_GET[ 'page' ];

$allowed = array( 'include.php', 'file1.php', 'file2.php', 'file3.php', 'file4.php' );

if( !in_array( $file, $allowed ) ) {
	$html .= '<pre>ERROR: File not found!</pre>';
	$file = 'include.php';
}

?>
