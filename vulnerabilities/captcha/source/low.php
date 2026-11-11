<?php

if( isset( $_POST[ 'Change' ] ) && isset( $_POST[ 'step' ] ) && ( $_POST[ 'step' ] == '1' ) ) {
	// Hide the CAPTCHA form
	$hide_form = true;

	// Get input
	$pass_new  = isset( $_POST[ 'password_new' ] ) && is_string( $_POST[ 'password_new' ] ) ? $_POST[ 'password_new' ] : '';
	$pass_conf = isset( $_POST[ 'password_conf' ] ) && is_string( $_POST[ 'password_conf' ] ) ? $_POST[ 'password_conf' ] : '';
	$captcha   = isset( $_POST[ 'g-recaptcha-response' ] ) && is_string( $_POST[ 'g-recaptcha-response' ] ) ? $_POST[ 'g-recaptcha-response' ] : '';

	// Forget any previous CAPTCHA result
	unset( $_SESSION[ 'captcha_passed' ] );

	// Check CAPTCHA from 3rd party (fail closed when no key is configured or no answer given)
	$resp = false;
	if( $_DVWA[ 'recaptcha_private_key' ] != '' && $captcha != '' ) {
		$resp = recaptcha_check_answer(
			$_DVWA[ 'recaptcha_private_key'],
			$captcha
		);
	}

	// Did the CAPTCHA fail?
	if( !$resp ) {
		// What happens when the CAPTCHA was entered incorrectly
		$html     .= "<pre><br />The CAPTCHA was incorrect. Please try again.</pre>";
		$hide_form = false;
		return;
	}
	else {
		// CAPTCHA was correct. Do both new passwords match?
		if( $pass_new == $pass_conf ) {
			// Remember server-side that the CAPTCHA was solved, bound to this new password
			$_SESSION[ 'captcha_passed' ] = hash( 'sha256', $pass_new );

			// Show next stage for the user
			$html .= "
				<pre><br />You passed the CAPTCHA! Click the button to confirm your changes.<br /></pre>
				<form action=\"#\" method=\"POST\">
					<input type=\"hidden\" name=\"step\" value=\"2\" />
					<input type=\"hidden\" name=\"password_new\" value=\"" . htmlspecialchars( $pass_new, ENT_QUOTES ) . "\" />
					<input type=\"hidden\" name=\"password_conf\" value=\"" . htmlspecialchars( $pass_conf, ENT_QUOTES ) . "\" />
					<input type=\"submit\" name=\"Change\" value=\"Change\" />
				</form>";
		}
		else {
			// Both new passwords do not match.
			$html     .= "<pre>Both passwords must match.</pre>";
			$hide_form = false;
		}
	}
}

if( isset( $_POST[ 'Change' ] ) && isset( $_POST[ 'step' ] ) && ( $_POST[ 'step' ] == '2' ) ) {
	// Hide the CAPTCHA form
	$hide_form = true;

	// Get input
	$pass_new  = isset( $_POST[ 'password_new' ] ) && is_string( $_POST[ 'password_new' ] ) ? $_POST[ 'password_new' ] : '';
	$pass_conf = isset( $_POST[ 'password_conf' ] ) && is_string( $_POST[ 'password_conf' ] ) ? $_POST[ 'password_conf' ] : '';

	// Step 2 is only allowed if step 1 (the CAPTCHA) was really passed in this session,
	// for this exact password. The result is single-use.
	$passed_captcha = isset( $_SESSION[ 'captcha_passed' ] ) && is_string( $_SESSION[ 'captcha_passed' ] )
		&& hash_equals( $_SESSION[ 'captcha_passed' ], hash( 'sha256', $pass_new ) );
	unset( $_SESSION[ 'captcha_passed' ] );

	if( !$passed_captcha ) {
		$html     .= "<pre><br />You have not passed the CAPTCHA.</pre>";
		$hide_form = false;
		return;
	}

	// Check to see if both password match
	if( $pass_new == $pass_conf ) {
		// They do!
		$pass_new = md5( $pass_new );

		// Update database
		$current_user = dvwaCurrentUser();
		$data = $db->prepare( 'UPDATE users SET password = (:password) WHERE user = (:user);' );
		$data->bindParam( ':password', $pass_new, PDO::PARAM_STR );
		$data->bindParam( ':user', $current_user, PDO::PARAM_STR );
		$data->execute();

		// Feedback for the end user
		$html .= "<pre>Password Changed.</pre>";
	}
	else {
		// Issue with the passwords matching
		$html .= "<pre>Passwords did not match.</pre>";
		$hide_form = false;
	}
}

?>
