<?php
// Generate server-side session token – the client-side SHA256 approach in high.js can be
// reverse-engineered; a random server-issued token cannot.
$_SESSION['js_challenge_token_high'] = bin2hex( random_bytes( 16 ) );
$server_token = $_SESSION['js_challenge_token_high'];
$page['body'] .= "<script>function generate_token() { document.getElementById('token').value = '" . $server_token . "'; } generate_token();</script>";
?>
