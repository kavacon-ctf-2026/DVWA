<?php
// Generate server-side token stored in session (prevents client-side reverse engineering)
$_SESSION['js_challenge_token_medium'] = bin2hex( random_bytes( 16 ) );
$server_token = $_SESSION['js_challenge_token_medium'];
$page['body'] .= "<script>function generate_token() { document.getElementById('token').value = '" . $server_token . "'; } generate_token();</script>";
?>
