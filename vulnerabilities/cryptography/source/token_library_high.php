<?php

define ("KEY", "rainbowclimbinghigh");
define ("ALGO", "aes-128-cbc");

function encrypt ($plaintext, $iv) {
	# Default padding is PKCS#7 which is interchangeable with PKCS#5
	# https://en.wikipedia.org/wiki/Padding_%28cryptography%29#PKCS#5_and_PKCS#7

	if (strlen ($iv) != 16) {
		throw new Exception ("IV must be 16 bytes, " . strlen ($iv) . " passed");
	}
	$tag = "";
	$e = openssl_encrypt($plaintext, ALGO, KEY, OPENSSL_RAW_DATA, $iv, $tag);
	if ($e === false) {
		throw new Exception ("Encryption failed");
	}
	return $e;
}

function decrypt ($ciphertext, $iv) {
	if (strlen ($iv) != 16) {
		throw new Exception ("IV must be 16 bytes, " . strlen ($iv) . " passed");
	}
	$e = openssl_decrypt($ciphertext, ALGO, KEY, OPENSSL_RAW_DATA, $iv);
	if ($e === false) {
		throw new Exception ("Decryption failed");
	}
	return $e;
}

function create_token ($debug = false) {
	$token = "userid:2";

	// Generate a random IV per token so it cannot be predicted or reused
	$iv = random_bytes(16);

	if ($debug) {
		print "Clear text token: " . $token . "\n";
		print "Encryption key: " . KEY . "\n";
	}

	$e = encrypt ($token, $iv);
	// Prepend IV to ciphertext – IV is not secret but must not be client-controlled separately
	$data = array (
					"token" => base64_encode ($iv . $e)
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

		$token_decoded = base64_decode ($data_array['token']);
		if (strlen ($token_decoded) < 17) {
			$ret = array (
							"status" => 524,
							"message" => "Token too short to contain IV"
						);
			return json_encode ($ret);
		}

		// Extract IV from first 16 bytes – client cannot supply a separate IV
		$iv         = substr ($token_decoded, 0, 16);
		$ciphertext = substr ($token_decoded, 16);

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
