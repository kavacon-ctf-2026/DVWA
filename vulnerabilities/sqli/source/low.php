<?php

if( isset( $_REQUEST[ 'Submit' ] ) ) {
	// Get input
	$id = $_REQUEST[ 'id' ];

	// Only accept a numeric user ID; anything else is rejected before touching the database
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
				// Get values
				$first = $row["first_name"];
				$last  = $row["last_name"];

				// Feedback for end user
				$html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
			}

			mysqli_close($GLOBALS["___mysqli_ston"]);
			break;
		case SQLITE:
			global $sqlite_db_connection;

			#$sqlite_db_connection = new SQLite3($_DVWA['SQLITE_DB']);
			#$sqlite_db_connection->enableExceptions(true);

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

?>
