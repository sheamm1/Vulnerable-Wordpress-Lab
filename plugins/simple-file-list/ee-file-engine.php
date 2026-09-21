<?php // Simple File List Script: ee-file-engine.php | Author: Mitchell Bennis | support@simplefilelist.com | Revised: 11.23.2019
	
// This script is accessed via AJAX by ee-list-display.php

// Write problems to error log file	
ini_set("log_errors", 1);
error_reporting (E_ALL);
ini_set ('display_errors', FALSE);
ini_set("error_log", "logs/ee-file-error.log");

// The List ID
if(@$_POST['eeSFL_ID']) { $eeThisListID = filter_var($_POST['eeSFL_ID'], FILTER_VALIDATE_INT); } else { $eeThisListID = FALSE; }

if(!$eeThisListID) { 
	$eeSFL_Error = "Missing ID";
	trigger_error($eeSFL_Error, E_USER_ERROR);
	exit($eeSFL_Error);
}

// The Action
if(@$_POST['eeFileAction']) { 
	$eeFileAction = filter_var($_POST['eeFileAction'], FILTER_SANITIZE_STRING); 
} else { 
	$eeSFL_Error = "Missing the Action";
	trigger_error($eeSFL_Error, E_USER_ERROR);
	exit($eeSFL_Error);
}

// Are we in a Folder?
if(@$_POST['eeListFolder']) { 
	$eeListFolder = urldecode( filter_var($_POST['eeListFolder'], FILTER_SANITIZE_STRING) ); 
} else {
	$eeListFolder = '';
}

// Tie into Wordpress
$wordpress = getcwd() . '/../../../wp-load.php'; // Starting at this plugin's home dir
$wordpress = realpath($wordpress);

if(is_file($wordpress)) { 
	include($wordpress); // Get all that wonderfullness
} else {
	trigger_error("No Wordpress", E_USER_ERROR);
	exit;
}


// WP Security
function eeSFL_CheckNonce() {
	
	if( !check_ajax_referer( 'eeSFL_ActionNonce', 'eeSecurity', FALSE ) ) {
		$eeSFL_Error = "WP AJAX Error";
		trigger_error($eeSFL_Error, E_USER_ERROR);
		exit;
	}	
}
add_action( 'plugins_loaded', 'eeSFL_CheckNonce' );


// Get the correct file list config if not main list
if($eeSFL_ID != $eeThisListID) {
	$eeSFL_Config = $eeSFL->eeSFL_Config($eeThisListID);
}


if( strpos($eeFileAction, 'Rename') === 0 ) {

	// If Renaming
	if( strpos($eeFileAction, '|') ) {
		$eeArray = explode('|', $eeFileAction);
		$eeFileAction = $eeArray[0];
		$eeNewFileName = urldecode( $eeArray[1] );
		$eeNewFileName = eeSFL_SanitizeFileName($eeNewFileName);
	}
		
	// The File Name
	if(@$_POST['eeFileOld']) { 
		$eeOldFileName = filter_var( $_POST['eeFileOld'], FILTER_SANITIZE_STRING ); 
	} else { 
		$eeSFL_Error = "Missing the Current File Name";
		trigger_error($eeSFL_Error, E_USER_ERROR);
		exit;
	}
	
	if($eeNewFileName) {
		
		if(strpos($eeOldFileName, '.') === FALSE) { // Folder
			$eeNewFileName = str_replace('.', '_', $eeNewFileName); // Prevent adding an extension
		}
		
		eeSFL_DetectUpwardTraversal($eeSFL_Config['FileListDir'] . $eeNewFileName); // Die if foolishness
		
		$eeFullPath = ABSPATH . $eeSFL_Config['FileListDir'] . $eeListFolder;
		$eeOldFilePath = $eeFullPath . $eeOldFileName;
		$eeNewFilePath = $eeFullPath . $eeNewFileName;
	
		$eeString = 'Renaming: ' . $eeListFolder . $eeOldFileName . ' to ' . $eeListFolder . $eeNewFileName;
		
		if( !rename($eeOldFilePath, $eeNewFilePath) ) {
			
			$eeSFL_Log['errors'][] = 'Could Not Rename ' . $eeOldFileName . ' to ' . $eeNewFileName;
			
			$eeSFL_Error = "Cannot Rename the File";
			trigger_error($eeSFL_Error, E_USER_ERROR);
			exit($eeSFL_Error);
		
		}
	
	} else { 
		$eeSFL_Error = "Missing the New File Name";
		trigger_error($eeSFL_Error, E_USER_ERROR);
		exit($eeSFL_Error);
	}
	
} elseif($eeFileAction == 'Delete') {
	
	// The File Path
	if(@$_POST['eeFileName']) { 
		$eeFileName = filter_var( $_POST['eeFileName'], FILTER_SANITIZE_STRING ); 
	} else { 
		$eeSFL_Error = "Missing the File to Delete";
		trigger_error($eeSFL_Error, E_USER_ERROR);
		exit($eeSFL_Error);
	}
	
	eeSFL_DetectUpwardTraversal($eeSFL_Config['FileListDir'] . $eeListFolder . $eeFileName); // Die if foolishness
	
	$eeFilePath = ABSPATH . $eeSFL_Config['FileListDir'] . $eeListFolder . $eeFileName;
	
	if( strpos($eeFileName, '.') ) { // Gotta be a File - Looking for the dot rather than using is_file() for better speed
		
		if(unlink($eeFilePath)) {
			
			$eeSFL_Msg = __('Deleted the File', 'ee-simple-file-list') . ' &rarr; ' . $eeListFolder . $eeFileName;
			$eeSFL_Log[] = $eeSFL_Msg;
			$eeSFL_Log['messages'][] = $eeSFL_Msg;
			
		} else {
			$eeSFL_Msg = __('File Delete Failed', 'ee-simple-file-list') . ':' . $eeListFolder . $eeFileName;
			$eeSFL_Log['errors'] = $eeSFL_Msg;
			$eeSFL_Log['messages'][] = $eeSFL_Msg;
		}
	} else {
		
		// Delete Folder
		if($eeSFLF) {
			if( !$eeSFLF->eeSFLF_DeleteFolder($eeFilePath) ) {
				$eeSFL_Msg = __('Folder Delete Failed', 'ee-simple-file-list') . ':' . $eeListFolder . $eeFileName;
				$eeSFL_Log['errors'] = $eeSFL_Msg;
				$eeSFL_Log['messages'][] = $eeSFL_Msg;
			}
		}
	}

} elseif($eeFileAction == 'UpdateDesc') {
	
	// The Description
	if(filter_var($_POST['eeFileID'], FILTER_VALIDATE_INT) !== FALSE) { // Might be a zero
		$eeFileID = $_POST['eeFileID'];
	} else { 
		$eeFileID = 0;
	}
	
	// The Description
	if(@$_POST['eeFileDesc']) { 
		$eeFileDesc = filter_var($_POST['eeFileDesc'], FILTER_SANITIZE_STRING); 
	} else { 
		$eeFileDesc = '';
	}
	
	$eeSFL->eeSFL_UpdateFileDetail($eeThisListID, 'FileDescription', $eeFileDesc);
	
	// Get the file array
	$eeFileArray = get_option('eeSFL-FileList-' . $eeThisListID);
	
	foreach( $eeFileArray as $eeKey => $eeThisFileArray ) {
		
		if($eeKey == $eeFileID) {
				
			$eeFileArray[$eeFileID]['FileDescription'] = $eeFileDesc;
		}
	}
		
	// Save the updated array
	$eeFileArray = update_option('eeSFL-FileList-' . $eeThisListID, $eeFileArray);
	
	
} else {
	
	exit; // Nothing to do
	
}

// Timer
$eeSFL_Time = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
$eeSFL_Log[] = 'Execution Time: ' . round($eeSFL_Time,3);

// Re-index the File List
$eeSFL_Files = $eeSFL->eeSFL_UpdateFileListArray($eeThisListID);

// Write to the log file to the Database
$eeSFL->eeSFL_WriteLogData($eeSFL_Log); 


echo 'SUCCESS';

?>