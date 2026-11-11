<?php

if( isset( $_POST[ 'Upload' ] ) ) {
	// Where are we going to be writing to?
	$target_path  = DVWA_WEB_PAGE_TO_ROOT . "hackable/uploads/";

	// File information
	$uploaded_name = isset( $_FILES[ 'uploaded' ][ 'name' ] ) ? (string)$_FILES[ 'uploaded' ][ 'name' ] : '';
	$uploaded_ext  = strtolower( pathinfo( $uploaded_name, PATHINFO_EXTENSION ) );
	$uploaded_size = isset( $_FILES[ 'uploaded' ][ 'size' ] ) ? (int)$_FILES[ 'uploaded' ][ 'size' ] : 0;
	$uploaded_tmp  = isset( $_FILES[ 'uploaded' ][ 'tmp_name' ] ) ? (string)$_FILES[ 'uploaded' ][ 'tmp_name' ] : '';

	// Detect the real image type from the file contents; it must match the extension (blocks polyglots)
	$image_info = ( $uploaded_tmp !== '' && is_uploaded_file( $uploaded_tmp ) ) ? @getimagesize( $uploaded_tmp ) : false;
	$image_type = ( $image_info !== false ) ? $image_info[ 2 ] : 0;

	// Is it a real JPEG or PNG image with a matching extension?
	if( ( ( ( $uploaded_ext == 'jpg' || $uploaded_ext == 'jpeg' ) && $image_type === IMAGETYPE_JPEG ) ||
		( $uploaded_ext == 'png' && $image_type === IMAGETYPE_PNG ) ) &&
		( $uploaded_size > 0 ) && ( $uploaded_size < 100000 ) ) {

		// Re-encode the image (strips any embedded payload) and store it under a random name
		$target_file = bin2hex( random_bytes( 16 ) ) . '.' . $uploaded_ext;
		$img = ( $image_type === IMAGETYPE_JPEG ) ? @imagecreatefromjpeg( $uploaded_tmp ) : @imagecreatefrompng( $uploaded_tmp );
		$saved = false;
		if( $img !== false ) {
			if( $image_type === IMAGETYPE_JPEG ) {
				$saved = imagejpeg( $img, $target_path . $target_file, 100 );
			}
			else {
				$saved = imagepng( $img, $target_path . $target_file, 9 );
			}
			unset( $img );
		}

		if( !$saved ) {
			// No
			$html .= '<pre>Your image was not uploaded.</pre>';
		}
		else {
			// Yes!
			$html .= '<pre>' . htmlspecialchars( $target_path . $target_file ) . ' succesfully uploaded!</pre>';
		}
	}
	else {
		// Invalid file
		$html .= '<pre>Your image was not uploaded. We can only accept JPEG or PNG images.</pre>';
	}
}

?>
