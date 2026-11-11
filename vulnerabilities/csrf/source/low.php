<?php

// State-changing request: only accepted via POST, with a valid single-use
// anti-CSRF token and the user's current password (re-authentication).
if( $_SERVER[ 'REQUEST_METHOD' ] == 'POST' && isset( $_POST[ 'Change' ] ) ) {
	// Get input
	$user_token    = isset( $_POST[ 'user_token' ] ) ? $_POST[ 'user_token' ] : '';
	$session_token = isset( $_SESSION[ 'session_token' ] ) ? $_SESSION[ 'session_token' ] : '';
	$pass_curr     = isset( $_POST[ 'password_current' ] ) && is_string( $_POST[ 'password_current' ] ) ? $_POST[ 'password_current' ] : '';
	$pass_new      = isset( $_POST[ 'password_new' ] ) && is_string( $_POST[ 'password_new' ] ) ? $_POST[ 'password_new' ] : '';
	$pass_conf     = isset( $_POST[ 'password_conf' ] ) && is_string( $_POST[ 'password_conf' ] ) ? $_POST[ 'password_conf' ] : '';

	// Check Anti-CSRF token
	if( !is_string( $user_token ) || !is_string( $session_token ) || $session_token === '' || !hash_equals( $session_token, $user_token ) ) {
		$html .= "<pre>CSRF token is incorrect.</pre>";
	}
	else {
		// Check that the current password is correct
		$current_user = dvwaCurrentUser();
		$pass_curr    = md5( $pass_curr );
		$data = $db->prepare( 'SELECT password FROM users WHERE user = (:user) AND password = (:password) LIMIT 1;' );
		$data->bindParam( ':user', $current_user, PDO::PARAM_STR );
		$data->bindParam( ':password', $pass_curr, PDO::PARAM_STR );
		$data->execute();

		// Do both new passwords match and does the current password match the user?
		if( ( $pass_new == $pass_conf ) && ( $data->rowCount() == 1 ) ) {
			// They do!
			$pass_new = md5( $pass_new );

			// Update the database
			$data = $db->prepare( 'UPDATE users SET password = (:password) WHERE user = (:user);' );
			$data->bindParam( ':password', $pass_new, PDO::PARAM_STR );
			$data->bindParam( ':user', $current_user, PDO::PARAM_STR );
			$data->execute();

			// Feedback for the user
			$html .= "<pre>Password Changed.</pre>";
		}
		else {
			// Issue with passwords matching
			$html .= "<pre>Passwords did not match or current password incorrect.</pre>";
		}
	}
}

// Generate a fresh Anti-CSRF token for every request
generateSessionToken();

?>
