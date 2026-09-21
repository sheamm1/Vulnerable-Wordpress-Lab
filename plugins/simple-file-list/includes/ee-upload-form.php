<?php  // Simple File List Script: ee-upload-form.php | Author: Mitchell Bennis | support@simplefilelist.com | Revised: 12.23.2019

// Security	
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! wp_verify_nonce( $eeSFL_Nonce, 'eeInclude' ) ) exit('ERROR 98'); // Exit if nonce fails

$eeSFL_UploadNonce = wp_create_nonce('ee-simple-file-list-upload'); // Checked in the upload engine.
global $eeSFL_ListRun;
$eeSFL_Log['Add Files'][] = 'Loaded: ee-uploader';

// Extension Check
if($eeSFLF) {
	if(@$_REQUEST['eeFolder'] AND $eeSFL_ListRun == 1) { // Adjust the path based on REQUEST arg
		$eeSFLF_ListFolder = filter_var(urldecode($_REQUEST['eeFolder']), FILTER_SANITIZE_STRING) . '/'; 
	} elseif( strlen(@$eeSFLF_ShortcodeFolder) ) {
		$eeSFLF_ListFolder = str_replace('&#34;', '', $eeSFLF_ShortcodeFolder) . '/'; // Fix for uploading to draft status page
	} else {
		$eeSFLF_ListFolder = FALSE;
	}
} else {
	$eeSFLF_ListFolder = FALSE;
}

$eeSFL_Log['Add Files'][] = 'Uploading to...';
$eeSFL_Log['Add Files'][] = $eeSFL_Config['FileListDir'] . $eeSFLF_ListFolder;

// File limit fallback
if(!$eeSFL_Config['UploadLimit']) { $eeSFL_Config['UploadLimit'] = $eeSFL->eeDefaultUploadLimit; }

// User Messaging	
if(@$eeSFL_Log['messages'] AND $eeSFL_ListRun == 1) { 
	$eeOutput .=  eeSFL_ResultsDisplay($eeSFL_Log['messages'], 'notice-success');
	$eeSFL_Log['messages'] = ''; // Clear
}	
if(@$eeSFL_Log['errors']) { 
	$eeOutput .=  eeSFL_ResultsDisplay($eeSFL_Log['errors'], 'notice-error');
	$eeSFL_Log['errors'] = ''; // Clear
}
	

if(@$eeSFL_Config['FileListDir']) {
	
	$eeOutput .= '
	
	<!-- Simple File List Uploader -->
			
		<form action="' . eeSFL_GetThisURL() . '" method="POST" enctype="multipart/form-data" name="eeSFL_UploadForm" id="eeSFL_UploadForm">
		
			<input type="hidden" name="MAX_FILE_SIZE" value="' .(($eeSFL_Config['UploadMaxFileSize']*1024)*1024) . '" />
			<input type="hidden" name="eeSFL_Upload" value="TRUE" />
			<input type="hidden" name="eeListID" value="' . $eeSFL_ID . '" />
			<input type="hidden" name="eeSFL_FileCount" value="" id="eeSFL_FileCount" />
			<input type="hidden" name="eeSFL_FileList" value="" id="eeSFL_FileList" />';
		
		if($eeSFL_Env['wpUserID'] > 0) { $eeOutput .= '<input type="hidden" name="eeSFL_FileOwner" value="' . $eeSFL_Env['wpUserID'] . '" id="eeSFL_FileOwner" />'; }
			
		if($eeSFLF AND $eeSFLF_ListFolder) { $eeOutput .= '
			<input type="hidden" name="eeSFLF_UploadFolder" value="' . urlencode($eeSFLF_ListFolder) . '" id="eeSFLF_UploadFolder" />
			';
		}
		$eeOutput .= wp_nonce_field( 'ee-simple-file-list-upload', 'ee-simple-file-list-upload-nonce', TRUE, FALSE);
	
		$eeOutput .= '
		
		<h2 class="eeSFL_UploadFilesTitle">' . __('Upload Files', 'ee-simple-file-list') . '</h2>
		
		<div id="eeSFL_FileDropZone" ondrop="eeSFL_DropHandler(event);" ondragover="eeSFL_DragOverHandler(event);">';
		
		if($eeSFL_Config['GetUploaderInfo'] == 'YES' AND !$eeAdmin) { $eeOutput .= $eeSFL->eeSFL_UploadInfoForm(); }
		
		$eeSFL_FileFormats = str_replace(' ' , '', $eeSFL_Config['FileFormats']); // Strip spaces
		
		// Security
		$eeSFL_Timestamp = time();
		$eeSFL_TimestampMD5 = md5('unique_salt' . $eeSFL_Timestamp);
	    
		$eeOutput .= '<input type="file" name="eeSFL_FileInput" id="eeSFL_FileInput" onchange="eeSFL_FileInputHandler(event)" multiple />
		
		<p id="eeSFL_FilesDrug" class="eeHide"></p>
		
		<br class="eeClearFix" />
		
		<script>
		
			var eeSFL_ListID = ' . $eeSFL_ID . ';
			var eeSFL_FileUploadDir = "' . urlencode($eeSFL_Config['FileListDir'] . $eeSFLF_ListFolder) . '";
			var eeSFL_FileLimit = ' . $eeSFL_Config['UploadLimit'] . '; // Maximum number of files allowed
			var eeSFL_UploadMaxFileSize = ' . (($eeSFL_Config['UploadMaxFileSize']*1024)*1024) . ';
			var eeSFL_FileFormats = "' . str_replace(' ' , '', $eeSFL_Config['FileFormats']) . '"; // Allowed file extensions
			var eeSFL_TimeStamp = "' . $eeSFL_Timestamp . '"; // Security
			var eeSFL_TimeStampMD5 = "' . $eeSFL_TimestampMD5 . '"; // Security
			var eeSFL_UploadEngineURL = "' . $eeSFL_Env['pluginURL'] . 'ee-upload-engine.php' . '";
			
		</script>
		
		<script src="' . $eeSFL_Env['pluginURL'] . '/js/ee-uploader.js?v=' . eeSFL_Cache_Version . '"></script>
		
		<span id="eeSFL_UploadProgress"><em>' . __('Processing the Upload', 'ee-simple-file-list') . '</em></span>
		
		<button type="button" class="button eeButton" name="eeSFL_UploadGo" id="eeSFL_UploadGo" onclick="eeUploadProcessor(eeSFL_FileObjects);">' . __('Upload', 'ee-simple-file-list') . '</button>';
		
		if($eeSFL_Config['ShowUploadLimits'] == 'YES' OR $eeAdmin) {
		
			$eeOutput .= '<p class="sfl_instuctions">' . __('File Limit', 'ee-simple-file-list') . ': ' . $eeSFL_Config['UploadLimit'] . ' ' . __('files', 'ee-simple-file-list') . '<br />
			
			' . __('Size Limit', 'ee-simple-file-list') . ': ' . $eeSFL_Config['UploadMaxFileSize'] . ' MB
			
			' . __('per file', 'ee-simple-file-list') . '.<br />
			
			' . __('Types Allowed', 'ee-simple-file-list') . ': ' . str_replace(',', ', ', $eeSFL_Config['FileFormats'])  . '<br />
			
			' . __('Drag-and-drop files here or use the Browse button.', 'ee-simple-file-list') . '</p>';
		
		} else {
			
			$eeOutput .= '
		
			<br class="eeClearFix" />';
		}
		
		$eeOutput .= '
		
		</div>
	
	</form>';
	
	
} else {
	$eeOutput .= __('No upload directory configured.', 'ee-simple-file-list');
	$eeSFL_Log['errors'] = 'No upload directory configured.';
}

?>