<?php // Simple File List Script: ee-list-display.php | Author: Mitchell Bennis | support@simplefilelist.com | Revised: 12.23.2019
	
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! wp_verify_nonce( $eeSFL_Nonce, 'eeInclude' ) ) exit('ERROR 98'); // Exit if nonce fails

// Check for an upload job, then run notification routine.
if(@$_POST['eeSFL_Upload']) {
	
	$eeSFL_Uploaded = TRUE;
	
	$eeSFL_ID = filter_var(@$_POST['eeListID'], FILTER_VALIDATE_INT);
	
	if($eeSFL_ID OR $eeSFL_ID === 0) {
		
		$eeOutput .= '<!-- Processing Upload -->';
		
		$eeOutput .= eeSFL_ProcessUpload($eeSFL_ID);
	}
	
	if($eeAdmin) {
		eeSFL_UploadCompletedAdmin(); // Action Hook: eeSFL_UploadCompletedAdmin  <-- Admin side
	} else {
		eeSFL_UploadCompleted(); // Action Hook: eeSFL_UploadCompleted <-- Front side
	}	
} else {
	$eeSFL_Uploaded = FALSE;
}


?>