<?php

// Login attempts are only accepted via POST (credentials never travel in the URL),
// must carry a valid single-use anti-CSRF token, and are throttled per session.
if( $_SERVER[ 'REQUEST_METHOD' ] == 'POST' && isset( $_POST[ 'Login' ] ) && isset( $_POST[ 'username' ] ) && isset( $_POST[ 'password' ] ) ) {
	// Default values
	$total_failed_login = 3;
	$lockout_time       = 15; // minutes

	// Per-session failed-attempt tracking (kept in the session rather than the users
	// table, so failed guesses here can never lock anyone out of the main login page)
	if( !isset( $_SESSION[ 'brute_failed_login' ] ) || !isset( $_SESSION[ 'brute_last_failed' ] ) ) {
		$_SESSION[ 'brute_failed_login' ] = 0;
		$_SESSION[ 'brute_last_failed' ]  = 0;
	}
	if( ( $_SESSION[ 'brute_failed_login' ] >= $total_failed_login ) && ( time() >= ( $_SESSION[ 'brute_last_failed' ] + ( $lockout_time * 60 ) ) ) ) {
		// Lockout period has expired
		$_SESSION[ 'brute_failed_login' ] = 0;
	}
	$account_locked = ( $_SESSION[ 'brute_failed_login' ] >= $total_failed_login );

	// Check Anti-CSRF token
	$user_token    = isset( $_POST[ 'user_token' ] ) ? $_POST[ 'user_token' ] : '';
	$session_token = isset( $_SESSION[ 'session_token' ] ) ? $_SESSION[ 'session_token' ] : '';

	if( !is_string( $user_token ) || !is_string( $session_token ) || $session_token === '' || !hash_equals( $session_token, $user_token ) ) {
		$html .= "<pre><br />CSRF token is incorrect.</pre>";
	}
	elseif( !is_string( $_POST[ 'username' ] ) || !is_string( $_POST[ 'password' ] ) ) {
		$html .= "<pre><br />Username and/or password incorrect.</pre>";
	}
	elseif( $account_locked ) {
		$html .= "<pre><br />Too many failed login attempts. Please try again in {$lockout_time} minutes.</pre>";
	}
	else {
		// Get input
		$user = $_POST[ 'username' ];
		$pass = md5( $_POST[ 'password' ] );

		// Check the database (prepared statement)
		$data = $db->prepare( 'SELECT avatar FROM users WHERE user = (:user) AND password = (:password) LIMIT 1;' );
		$data->bindParam( ':user', $user, PDO::PARAM_STR );
		$data->bindParam( ':password', $pass, PDO::PARAM_STR );
		$data->execute();
		$row = $data->fetch();

		if( $row !== false ) {
			// Login successful
			$_SESSION[ 'brute_failed_login' ] = 0;
			$avatar = htmlspecialchars( $row[ 'avatar' ], ENT_QUOTES );
			$html .= "<p>Welcome to the password protected area " . htmlspecialchars( $user, ENT_QUOTES ) . "</p>";
			$html .= "<img src=\"{$avatar}\" />";
		}
		else {
			// Login failed
			sleep( rand( 2, 4 ) );
			$_SESSION[ 'brute_failed_login' ]++;
			$_SESSION[ 'brute_last_failed' ] = time();
			$html .= "<pre><br />Username and/or password incorrect.</pre>";
		}
	}
}

// Generate a fresh Anti-CSRF token for every request
generateSessionToken();

?>
