<?php

if( isset( $_POST[ 'Submit' ] ) ) {
	// Get input
	$id = $_POST[ 'id' ];

	// Only accept a numeric user ID; escaping alone does not protect an unquoted numeric field
	$db_engine = $_DVWA['SQLI_DB'];
	if( !is_numeric( $id ) ) {
		$db_engine = '';
		$html .= '<pre>User ID must be a number.</pre>';
	}
	else {
		$id = intval( $id );
	}

	switch ($db_engine) {
		case MYSQL:
			// Check database using a prepared statement (parameterised query)
			$data = $db->prepare( 'SELECT first_name, last_name FROM users WHERE user_id = (:id);' );
			$data->bindParam( ':id', $id, PDO::PARAM_INT );
			$data->execute();

			// Get results
			while( $row = $data->fetch( PDO::FETCH_ASSOC ) ) {
				// Display values
				$first = $row["first_name"];
				$last  = $row["last_name"];

				// Feedback for end user
				$html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
			}
			break;
		case SQLITE:
			global $sqlite_db_connection;

			try {
				$stmt = $sqlite_db_connection->prepare( 'SELECT first_name, last_name FROM users WHERE user_id = :id;' );
				$stmt->bindValue( ':id', $id, SQLITE3_INTEGER );
				$results = $stmt->execute();
			} catch (Exception $e) {
				echo 'Caught exception: ' . $e->getMessage();
				exit();
			}

			if ($results) {
				while ($row = $results->fetchArray()) {
					// Get values
					$first = $row["first_name"];
					$last  = $row["last_name"];

					// Feedback for end user
					$html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
				}
			} else {
				echo "Error in fetch ".$sqlite_db->lastErrorMsg();
			}
			break;
	}
}

// This is used later on in the index.php page
// Setting it here so we can close the database connection in here like in the rest of the source scripts
$query  = "SELECT COUNT(*) FROM users;";
$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query ) or die( '<pre>' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . '</pre>' );
$number_of_rows = mysqli_fetch_row( $result )[0];

mysqli_close($GLOBALS["___mysqli_ston"]);
?>
