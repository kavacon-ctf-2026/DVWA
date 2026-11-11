<?php

// The page we wish to display
$file = $_GET[ 'page' ];

// Input validation: exact-match allowlist (fnmatch("file*") also matched file:// URIs)
$allowedPages = array( 'include.php', 'file1.php', 'file2.php', 'file3.php' );
if( isset( $file ) && !in_array( $file, $allowedPages, true ) ) {
	// This isn't the page we want! Fall back to the default page instead of including it.
	$page[ 'body' ] .= "<div class=\"warning\">ERROR: File not found!</div>";
	$file = 'include.php';
}

?>
