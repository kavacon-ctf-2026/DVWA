<?php

# Authenticated encryption (AES-256-GCM) replaces AES-128-CBC: tokens can no longer be
# forged or bit-flipped, and there is no padding oracle. The key is a random secret kept
# server-side (never in the source) and every token gets a fresh random IV.
define ("ALGO", "aes-256-gcm");
define ("IV_LENGTH", 12);
define ("TAG_LENGTH", 16);

function get_secret_key () {
	static $key = null;
	if (!is_null ($key)) {
		return $key;
	}

	// Prefer a key supplied by the deployment.
	$env = getenv ("DVWA_CRYPTO_HIGH_KEY");
	if (is_string ($env) && strlen ($env) >= 32) {
		$key = hash ("sha256", $env, true);
		return $key;
	}

	// Otherwise use a random key generated once and stored outside the web root.
	$path = sys_get_temp_dir () . DIRECTORY_SEPARATOR . "dvwa_crypto_high.key";
	if (!is_file ($path)) {
		$tmp = $path . "." . bin2hex (random_bytes (8));
		if (@file_put_contents ($tmp, bin2hex (random_bytes (32))) !== false) {
			@chmod ($tmp, 0600);
			// link() is atomic and never overwrites, so concurrent requests agree on one key.
			if (!@link ($tmp, $path) && !is_file ($path)) {
				@rename ($tmp, $path);
			}
			@unlink ($tmp);
		}
	}

	$hex = @file_get_contents ($path);
	if (!is_string ($hex) || strlen ($hex) != 64 || !ctype_xdigit ($hex)) {
		throw new Exception ("Secret key unavailable");
	}
	$key = hex2bin ($hex);
	return $key;
}

function encrypt ($plaintext, $iv) {
	if (strlen ($iv) != IV_LENGTH) {
		throw new Exception ("IV must be " . IV_LENGTH . " bytes, " . strlen ($iv) . " passed");
	}
	$tag = "";
	$e = openssl_encrypt($plaintext, ALGO, get_secret_key (), OPENSSL_RAW_DATA, $iv, $tag, "", TAG_LENGTH);
	if ($e === false) {
		throw new Exception ("Encryption failed");
	}
	return $e . $tag;
}

function decrypt ($ciphertext, $iv) {
	if (strlen ($iv) != IV_LENGTH) {
		throw new Exception ("IV must be " . IV_LENGTH . " bytes, " . strlen ($iv) . " passed");
	}
	if (strlen ($ciphertext) <= TAG_LENGTH) {
		throw new Exception ("Decryption failed");
	}
	$tag  = substr ($ciphertext, -TAG_LENGTH);
	$text = substr ($ciphertext, 0, -TAG_LENGTH);
	$e = openssl_decrypt($text, ALGO, get_secret_key (), OPENSSL_RAW_DATA, $iv, $tag);
	if ($e === false) {
		throw new Exception ("Decryption failed");
	}
	return $e;
}

// Added the debug flag so that when calling from the script
// the function can print the data used to create the token

function create_token ($debug = false) {
	$token = "userid:2";
	$iv = random_bytes (IV_LENGTH);

	if ($debug) {
		# Never print the key.
		print "Clear text token: " . $token . "\n";
	}

	$e = encrypt ($token, $iv);
	$data = array (
					"token" => base64_encode ($e),
					"iv" => base64_encode ($iv)
				);
	return json_encode($data);
}

function check_token ($data) {
	$users = array ();
	$users[1] = array ("name" => "Geoffery", "level" => "admin");
	$users[2] = array ("name" => "Bungle", "level" => "user");
	$users[3] = array ("name" => "Zippy", "level" => "user");
	$users[4] = array ("name" => "George", "level" => "user");

	$data_array = false;
	try {
		$data_array = json_decode ($data, true);
	} catch (TypeError $exp) {
		$ret = array (
						"status" => 521,
						"message" => "Data not in JSON format",
						"extra" => $exp->getMessage()
					);
	}

	if (is_null ($data_array)) {
		$ret = array (
						"status" => 522,
						"message" => "Data in wrong format"
					);
	} else {
		if (!array_key_exists ("token", $data_array)) {
			$ret = array (
							"status" => 523,
							"message" => "Missing token"
						);
			return json_encode ($ret);
		}
		if (!array_key_exists ("iv", $data_array)) {
			$ret = array (
							"status" => 524,
							"message" => "Missing IV"
						);
			return json_encode ($ret);
		}
			
		$ciphertext = base64_decode ($data_array['token']);
		$iv = base64_decode ($data_array['iv']);

		# Assume failure
		$ret = array (
						"status" => 500,
						"message" => "Unknown error"
					);
		try {
			$d = decrypt ($ciphertext, $iv); 
			if (preg_match ("/^userid:(\d+)$/", $d, $matches)) {
				$id = $matches[1];
				if (array_key_exists ($id, $users)) {
					$user = $users[$id];
					$ret = array (
									"status" => 200,
									"user" => $user["name"],
									"level" => $user['level']
								);
				} else {
					$ret = array (
									"status" => 525,
									"message" => "User not found"
								);
				}
			} else {
				$ret = array (
								"status" => 527,
								"message" => "No user specified"
							);
			}
		} catch (Exception $exp) {
			$ret = array (
							"status" => 526,
							"message" => "Unable to decrypt token",
							"extra" => $exp->getMessage()
						);
		}
	}
	return json_encode ($ret);
}
