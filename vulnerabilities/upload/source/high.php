<?php

if( isset( $_POST[ 'Upload' ] ) ) {
	// File information
	$uploaded_name = $_FILES[ 'uploaded' ][ 'name' ];
	$uploaded_ext  = strtolower( substr( $uploaded_name, strrpos( $uploaded_name, '.' ) + 1 ) );
	$uploaded_size = $_FILES[ 'uploaded' ][ 'size' ];
	$uploaded_type = $_FILES[ 'uploaded' ][ 'type' ];
	$uploaded_tmp  = $_FILES[ 'uploaded' ][ 'tmp_name' ];

	// Randomize filename to prevent path traversal and double-extension attacks
	$safe_name    = md5( uniqid() ) . '.' . $uploaded_ext;
	$target_path  = DVWA_WEB_PAGE_TO_ROOT . "hackable/uploads/" . $safe_name;

	// Is it an image? (check extension, MIME type, size, and actual image content)
	if( ( $uploaded_ext == "jpg" || $uploaded_ext == "jpeg" || $uploaded_ext == "png" ) &&
		( $uploaded_size < 100000 ) &&
		( $uploaded_type == "image/jpeg" || $uploaded_type == "image/png" ) &&
		getimagesize( $uploaded_tmp ) ) {

		// Re-encode with GD to strip any embedded code (polyglot files)
		$upload_ok = false;
		if( $uploaded_ext == "png" ) {
			$img = @imagecreatefrompng( $uploaded_tmp );
			if( $img !== false ) {
				imagepng( $img, $target_path, 9 );
				imagedestroy( $img );
				$upload_ok = true;
			}
		} else {
			$img = @imagecreatefromjpeg( $uploaded_tmp );
			if( $img !== false ) {
				imagejpeg( $img, $target_path, 100 );
				imagedestroy( $img );
				$upload_ok = true;
			}
		}

		if( $upload_ok ) {
			$html .= "<pre>{$target_path} succesfully uploaded!</pre>";
		} else {
			$html .= '<pre>Your image was not uploaded. Invalid image data.</pre>';
		}
	}
	else {
		$html .= '<pre>Your image was not uploaded. We can only accept JPEG or PNG images.</pre>';
	}
}

?>
