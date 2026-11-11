<?php

if( isset( $_POST[ 'Change' ] ) ) {
	// Hide the CAPTCHA form
	$hide_form = true;

	// Get input
	$pass_new  = isset( $_POST[ 'password_new' ] ) && is_string( $_POST[ 'password_new' ] ) ? $_POST[ 'password_new' ] : '';
	$pass_conf = isset( $_POST[ 'password_conf' ] ) && is_string( $_POST[ 'password_conf' ] ) ? $_POST[ 'password_conf' ] : '';
	$captcha   = isset( $_POST[ 'g-recaptcha-response' ] ) && is_string( $_POST[ 'g-recaptcha-response' ] ) ? $_POST[ 'g-recaptcha-response' ] : '';

	// Check Anti-CSRF token
	$user_token    = isset( $_POST[ 'user_token' ] ) ? $_POST[ 'user_token' ] : '';
	$session_token = isset( $_SESSION[ 'session_token' ] ) ? $_SESSION[ 'session_token' ] : '';
	$token_ok      = is_string( $user_token ) && is_string( $session_token ) && $session_token !== '' && hash_equals( $session_token, $user_token );

	// Check CAPTCHA from 3rd party only - no client-controlled back-door values
	// (fail closed when no key is configured or no answer given)
	$resp = false;
	if( $token_ok && $_DVWA[ 'recaptcha_private_key' ] != '' && $captcha != '' ) {
		$resp = recaptcha_check_answer(
			$_DVWA[ 'recaptcha_private_key' ],
			$captcha
		);
	}

	if( $resp ) {
		// CAPTCHA was correct. Do both new passwords match?
		if ($pass_new == $pass_conf) {
			$pass_new = md5( $pass_new );

			// Update database
			$current_user = dvwaCurrentUser();
			$data = $db->prepare( 'UPDATE users SET password = (:password) WHERE user = (:user) LIMIT 1;' );
			$data->bindParam( ':password', $pass_new, PDO::PARAM_STR );
			$data->bindParam( ':user', $current_user, PDO::PARAM_STR );
			$data->execute();

			// Feedback for user
			$html .= "<pre>Password Changed.</pre>";

		} else {
			// Ops. Password mismatch
			$html     .= "<pre>Both passwords must match.</pre>";
			$hide_form = false;
		}

	} else {
		// What happens when the CAPTCHA was entered incorrectly
		$html     .= "<pre><br />The CAPTCHA was incorrect. Please try again.</pre>";
		$hide_form = false;
	}
}

// Generate Anti-CSRF token
generateSessionToken();

?>
