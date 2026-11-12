<?php

if( isset( $_POST[ 'Upload' ] ) ) {
	$uploaded_name = $_FILES[ 'uploaded' ][ 'name' ];
	$uploaded_ext  = strtolower( substr( $uploaded_name, strrpos( $uploaded_name, '.' ) + 1 ) );
	$uploaded_size = $_FILES[ 'uploaded' ][ 'size' ];
	$uploaded_type = $_FILES[ 'uploaded' ][ 'type' ];
	$uploaded_tmp  = $_FILES[ 'uploaded' ][ 'tmp_name' ];

	if( ( $uploaded_ext == 'jpg' || $uploaded_ext == 'jpeg' || $uploaded_ext == 'png' ) &&
		( $uploaded_size < 100000 ) &&
		( $uploaded_type == 'image/jpeg' || $uploaded_type == 'image/png' ) &&
		getimagesize( $uploaded_tmp ) ) {

		$target_path  = DVWA_WEB_PAGE_TO_ROOT . "hackable/uploads/";
		$target_path .= basename( $uploaded_name );

		if( !move_uploaded_file( $uploaded_tmp, $target_path ) ) {
			$html .= '<pre>Your image was not uploaded.</pre>';
		}
		else {
			$html .= "<pre>{$target_path} succesfully uploaded!</pre>";
		}
	}
	else {
		$html .= '<pre>Your image was not uploaded. We can only accept JPEG or PNG images.</pre>';
	}
}

?>
