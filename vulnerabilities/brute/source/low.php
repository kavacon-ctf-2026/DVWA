<?php

if( isset( $_GET[ 'Login' ] ) ) {
	// Get username
	$user = $_GET[ 'username' ];

	// Get password
	$pass = $_GET[ 'password' ];
	$pass = md5( $pass );

	// Account lockout check
	$total_failed_login = 3;
	$lockout_time       = 15;
	$account_locked     = false;

	$data = $db->prepare( 'SELECT failed_login, last_login FROM users WHERE user = (:user) LIMIT 1;' );
	$data->bindParam( ':user', $user, PDO::PARAM_STR );
	$data->execute();
	$row = $data->fetch();
	if( $row !== false ) {
		if( (int)$row[ 'failed_login' ] >= $total_failed_login ) {
			$last_login = strtotime( $row[ 'last_login' ] );
			$timeout    = $last_login + ($lockout_time * 60);
			if( time() < $timeout ) {
				$account_locked = true;
			}
		}
	}

	// Check the database
	$data = $db->prepare( 'SELECT * FROM users WHERE user = (:user) AND password = (:password) LIMIT 1;' );
	$data->bindParam( ':user', $user, PDO::PARAM_STR );
	$data->bindParam( ':password', $pass, PDO::PARAM_STR );
	$data->execute();
	$row = $data->fetch();

	if( $row !== false && !$account_locked ) {
		// Get users details
		$avatar = $row[ 'avatar' ];

		// Login successful
		$html .= "<p>Welcome to the password protected area {$user}</p>";
		$html .= "<img src=\"{$avatar}\" />";

		// Reset failed login counter
		$data = $db->prepare( 'UPDATE users SET failed_login = "0" WHERE user = (:user) LIMIT 1;' );
		$data->bindParam( ':user', $user, PDO::PARAM_STR );
		$data->execute();
	}
	else {
		// Login failed
		$html .= "<pre><br />Username and/or password incorrect.</pre>";

		// Update failed login count
		$data = $db->prepare( 'UPDATE users SET failed_login = (failed_login + 1), last_login = now() WHERE user = (:user) LIMIT 1;' );
		$data->bindParam( ':user', $user, PDO::PARAM_STR );
		$data->execute();
	}
}

?>
