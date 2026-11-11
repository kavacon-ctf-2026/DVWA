<?php

if( isset( $_POST[ 'Submit' ]  ) ) {
	// Get input
	$id = $_POST[ 'id' ];
	$exists = false;

	// Only accept a numeric user ID; escaping alone does not protect an unquoted numeric field
	$db_engine = $_DVWA['SQLI_DB'];
	if( !is_numeric( $id ) ) {
		$db_engine = '';
	}
	else {
		$id = intval( $id );
	}

	switch ($db_engine) {
		case MYSQL:
			// Check database using a prepared statement (parameterised query)
			try {
				$data = $db->prepare( 'SELECT first_name, last_name FROM users WHERE user_id = (:id) LIMIT 1;' );
				$data->bindParam( ':id', $id, PDO::PARAM_INT );
				$data->execute();
				$exists = ( $data->fetch() !== false );
			} catch (Exception $e) {
				$exists = false;
			}

			break;
		case SQLITE:
			global $sqlite_db_connection;

			try {
				$stmt = $sqlite_db_connection->prepare( 'SELECT first_name, last_name FROM users WHERE user_id = :id LIMIT 1;' );
				$stmt->bindValue( ':id', $id, SQLITE3_INTEGER );
				$results = $stmt->execute();
				$row = $results->fetchArray();
				$exists = $row !== false;
			} catch(Exception $e) {
				$exists = false;
			}
			break;
	}

	if ($exists) {
		// Feedback for end user
		$html .= '<pre>User ID exists in the database.</pre>';
	} else {
		// Feedback for end user
		$html .= '<pre>User ID is MISSING from the database.</pre>';
	}
}

?>
