<?php
// Generate a server-side random token so the algorithm is not exposed client-side
$_SESSION['js_challenge_token'] = bin2hex( random_bytes( 16 ) );
$server_token = $_SESSION['js_challenge_token'];

$page[ 'body' ] .= "
<script>
	function generate_token() {
		document.getElementById('token').value = '" . $server_token . "';
	}
	generate_token();
</script>
";
?>
