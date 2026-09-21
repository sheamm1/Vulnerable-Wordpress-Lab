<?php // Simple File List Script: ee-functions.php | Author: Mitchell Bennis | support@simplefilelist.com
	
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! wp_verify_nonce( $eeSFL_Nonce, 'eeSFL_Functions' ) ) exit('ERROR 98'); // Exit if nonce fails

$eeSFL_Log[] = 'Loaded: ee-functions';


// Detect upward path traversal
function eeSFL_DetectUpwardTraversal($eeFilePath) {

	global $eeSFL_Log, $eeSFL_Env;
	
	if($eeSFL_Env['eeOS'] == 'WINDOWS') {
		
		return TRUE; // For now
	
	} elseif($eeSFL_Env['eeOS'] == 'LINUX') {
	
		$eeUserPath = ABSPATH . dirname($eeFilePath);  // This could be problematic with things like ../
		$eeRealPath = realpath( ABSPATH . dirname($eeFilePath) ); // Expunge the badness and then compare...
		
		if ($eeUserPath != $eeRealPath) { // They must match
		    $eeSFL_Log['errors'] = 'Error 99'; // The infamous Error 99
		    wp_die('Error 99 :-('); // Bad guy found, bail out :-(
		}
		
		return TRUE;
	}
}




// Convert hyphens to spaces for display only
function eeSFL_PreserveSpaces($eeFileName) {
	
	$eeFileName = str_replace('-', ' ', $eeFileName);
	
	return $eeFileName;
}





// Add the correct URL argument operator, ? or &
function eeSFL_AppendProperUrlOp($eeURL) {
	
	if ( strpos($eeURL, '?') ) {
		$eeURL .= '&';
	} else {
		$eeURL .= '?';
	}
	
	return $eeURL;
}





// Check for the Upload Folder, Create if Needed
function eeSFL_FileListDirCheck($eeFileListDir) {
	
	if(!$eeFileListDir) { return FALSE; }
	
	global $eeSFL_Log, $eeSFL, $eeSFL_ID, $eeSFL_Env;
	
	// Check if FileListDir has a trailing slash...
	$eeLastChar = substr($eeFileListDir, -1);
	if($eeLastChar != '/') {  $eeFileListDir .= '/'; } // Trailing slash required
	
	// Set some standards
	if(strpos($eeFileListDir, '.') === 0 OR strpos($eeFileListDir, 'p-admin') OR strpos($eeFileListDir, 'p-includes') ) {
		$eeSFL_Log['errors'][] = 'This File List Location is Not Allowed: ' . $eeFileListDir;
		return FALSE;
	}
	
	$eeSFL_FileListDirCheck = get_transient('eeSFL-' . $eeSFL_ID . '-FileListDirCheck');
	
	// Check Transient First
	if( $eeFileListDir == $eeSFL_FileListDirCheck AND is_dir(ABSPATH . $eeFileListDir) ) {
		
		return TRUE; // OKAY, No Change
		
	} elseif( strlen($eeFileListDir) ) { // Transient Expired, Dir Changed or New Install
	
		$eeSFL_Log['DirCheck'][] = 'No Transient or Folder Change...';
		
		if( !is_writable( ABSPATH . $eeFileListDir ) ) {
			
			$eeSFL_Log['DirCheck'][] = 'No Directory Found';
			$eeSFL_Log['DirCheck'][] = 'Creating new file list directory ...';
			
			if ($eeSFL_Env['eeOS'] == 'WINDOWS') {
			    
			    if( !mkdir(ABSPATH . $eeFileListDir) ) { // Linux - Need to set permissions
				    
				    $eeSFL_Log['errors'][] = 'Cannot Create Windows Folder: ' . $eeFileListDir;
				}
			
			} elseif($eeSFL_Env['eeOS'] == 'LINUX') {
			    
			    if( !mkdir(ABSPATH . $eeFileListDir , 0755) ) { // Linux - Need to set permissions
				    
				    $eeSFL_Log['errors'][] = 'Cannot Create Linux Folder: ' . $eeFileListDir;
				}
			} else {
				$eeSFL_Log['errors'][] = 'ERROR: Could not detect operating system';
				return FALSE;
			}
			
			if(!is_writable( ABSPATH . $eeFileListDir )) {
				$eeSFL_Log['errors'][] = 'Cannot create the upload directory: ' . $eeFileListDir;
				$eeSFL_Log['errors'][] = 'Please check directory permissions';
				
				return FALSE;
			
			} else {
				
				$eeSFL_Log['DirCheck'][] = '"FileListDir" Has Been Created!';
				$eeSFL_Log['DirCheck'][] = $eeFileListDir;
			}
			
			
		
		} else {
			$eeSFL_Log[] = 'FileListDir Looks Good';
		}
		
		// Check index.html, create if needed.	
		if( strlen($eeFileListDir) >= 2 ) {	
			
			$eeFile = ABSPATH . $eeFileListDir . 'index.html'; // Disallow direct file indexing.
			
			if(!is_file($eeFile)) {
				
				if($handle = @fopen($eeFile, "a+")) {
					
					if(!@is_readable($eeFile)) {
					    
						$eeSFL_Log['errors'][] = 'ERROR: Could not write index.html';
						
						return FALSE;
						
					} else {
						
						// Write nice content to the file
						$eeString = file_get_contents( plugin_dir_path(__FILE__) . 'ee-index-template.html' );
						fwrite($handle, $eeString);
						fclose($handle);
					}
				}
			
			}
		}
		
		
		// Set Transient
		set_transient('eeSFL-' . $eeSFL_ID . '-FileListDirCheck', $eeFileListDir, 86400); // 1 Expires in Day
		
		return TRUE;
		
	} else {
		
		return FALSE;
	}
}




// Post-process an upload job
function eeSFL_ProcessUpload($eeSFL_ID) {
	
	global $eeSFL, $eeSFL_Config, $eeSFL_Env, $eeSFL_Log;
	
	$eeOutput = FALSE;
	
	$eeFileCount = filter_var(@$_POST['eeSFL_FileCount'], FILTER_VALIDATE_INT);
	$eeSFLF_UploadFolder = urldecode(filter_var(@$_POST['eeSFLF_UploadFolder'], FILTER_SANITIZE_STRING));
	
	if($eeFileCount) {
		
		// Re-index the File List
		$eeFiles = $eeSFL->eeSFL_UpdateFileListArray($eeSFL_ID);
	
		$eeSFL_Log['Add Files'][] = 'Post-processinging Upload Job ...';
		$eeSFL_Log['Add Files'][] = $eeFileCount . ' Files';
		
		$eeFileList = stripslashes($_POST['eeSFL_FileList']);
		
		// Check for Nonce
		if(check_admin_referer( 'ee-simple-file-list-upload', 'ee-simple-file-list-upload-nonce')) {
			
			$eeArray = json_decode($eeFileList);
			$eeArray = array_map('eeSFL_SanitizeFileName', $eeArray);
			
			$eeNewArray = array(); // For our lowered-case extensions
			
			// Drop file extensions to lowercase
			foreach( $eeArray as $eeFile) {  // We need to do this here and in ee-upload-engine.php
				
				$eeArray = explode('.', $eeFile);
				$eeNewArray[] = $eeSFLF_UploadFolder . $eeArray[0] . '.' . strtolower($eeArray[1]);
			}
			$eeArray = $eeNewArray;
			
			$eeSFL_Env['UploadedFiles'] = $eeArray;
			
			
			// Notification
			if( count($eeArray) ) {
				
				$eeUploadJob = ''; // This will be what happened
				
				// Semantics
				if($eeFileCount > 1) { 
					$eeUploadJob .= $eeFileCount . ' ' . __('Files uploaded', 'ee-simple-file-list');	
				} else {
					$eeUploadJob .= __('File uploaded', 'ee-simple-file-list');
				}
				$eeUploadJob .= ":" . PHP_EOL . PHP_EOL;
				
				// Loop through the uploaded files
				if(count($eeArray)) {
					
					foreach($eeArray as $eeFile) { 
						
						$eeFile = eeSFL_SanitizeFileName($eeFile);
						
						// Notification
						$eeUploadJob .=  $eeSFLF_UploadFolder . $eeFile . PHP_EOL . 
							$eeSFL_Config['FileListURL'] . $eeSFLF_UploadFolder . $eeFile . PHP_EOL . 
								"(" . eeSFL_GetFileSize( $eeSFL_Config['FileListDir'] . $eeSFLF_UploadFolder . $eeFile ) . ")" . PHP_EOL . PHP_EOL;
					
						// Is uploader person logged-in?
						if( is_numeric(@$_POST['eeSFL_FileOwner']) ) {
							$eeSFL->eeSFL_UpdateFileDetail($eeSFL_ID, $eeFile, 'FileOwner', $_POST['eeSFL_FileOwner']);
						}
								
						// Add submitter data to file list array
						if($eeSFL_Config['GetUploaderInfo'] == 'YES') {
						
							$eeSFL_Log['Add Files'][] = 'Adding submitter info...';
							
							foreach( $eeFiles as $eeKey => $eeArray2) {
								
								// $eeSFL_Log[] = 'Does ' . $eeArray2['FilePath'] . ' == ' . $eeSFLF_UploadFolder . $eeFile;
								
								if( $eeArray2['FilePath'] ==  $eeSFLF_UploadFolder . $eeFile) {
									
									if(@$_POST['eeSFL_Comments']) {
										$eeSFL->eeSFL_UpdateFileDetail($eeSFL_ID, $eeFile, 'SubmitterName', filter_var($_POST['eeSFL_Name'], FILTER_SANITIZE_STRING));
										$eeSFL->eeSFL_UpdateFileDetail($eeSFL_ID, $eeFile, 'SubmitterEmail', filter_var($_POST['eeSFL_Email'], FILTER_VALIDATE_EMAIL));
										$eeSFL->eeSFL_UpdateFileDetail($eeSFL_ID, $eeFile, 'SubmitterComments', filter_var($_POST['eeSFL_Comments'], FILTER_SANITIZE_STRING));
									}
								}
							}
						}
					}
						
					$eeSFL_Log['messages'][] = __('File Upload Complete', 'ee-simple-file-list');
					
					if( is_admin() ) {
						$eeOutput = TRUE;
					
					} else  {
						
						// Send Email Notice
						if($eeSFL_Config['Notify'] == 'YES') {
							
							// Retuns the <script> output for sending the email
							return $eeSFL->eeSFL_AjaxEmail( $eeUploadJob, $eeSFL_ID ); 
						
						} else {
							return TRUE; // No notice wanted
						}
					}
					
				} else {
					$eeSFL_Log['errors'][] = 'Bad File Array';
					$eeSFL_Log['errors'][] = $eeUploadJob;
					return FALSE;
				}
			}
			
		} else {
			wp_die('ERROR 98');
		}
	
	} else {
		$eeSFL_Log['errors'][] = 'No ID or Files';
		return FALSE;
	}
}






// Return the size of a file in a nice format.
// Accepts a path or filesize in bytes
function eeSFL_GetFileSize($eeSFL_File) {  
    
    if( is_numeric($eeSFL_File) ) {
		$bytes = $eeSFL_File;
	} elseif(is_file(ABSPATH . $eeSFL_File)) {
		$bytes = filesize(ABSPATH . $eeSFL_File);	
	} else {
		return FALSE;
	}
	    
    $kilobyte = 1024;
    $megabyte = $kilobyte * 1024;
    $gigabyte = $megabyte * 1024;
    $terabyte = $gigabyte * 1024;
    $precision = 2;
   
    if (($bytes >= 0) && ($bytes < $kilobyte)) {
        return $bytes . ' B';
 
    } elseif (($bytes >= $kilobyte) && ($bytes < $megabyte)) {
        return round($bytes / $kilobyte, $precision) . ' KB';
 
    } elseif (($bytes >= $megabyte) && ($bytes < $gigabyte)) {
        return round($bytes / $megabyte, $precision) . ' MB';
 
    } elseif (($bytes >= $gigabyte) && ($bytes < $terabyte)) {
        return round($bytes / $gigabyte, $precision) . ' GB';
 
    } elseif ($bytes >= $terabyte) {
        return round($bytes / $terabyte, $precision) . ' TB';
    } else {
        return $bytes . ' B';
    }
}




// Make sure the file name is acceptable
function eeSFL_SanitizeFileName($eeSFL_FileName) {
	
	// Make sure file has an extension
	$eeSFL_PathParts = pathinfo($eeSFL_FileName);
	$eeSFL_FileNameAlone = str_replace('.', '_', $eeSFL_PathParts['filename']); // Get rid of dots
	$eeSFL_Extension = @strtolower($eeSFL_PathParts['extension']);
	$eeSFL_FileName = sanitize_file_name( $eeSFL_FileNameAlone . '.' . $eeSFL_Extension );
    
    return $eeSFL_FileName;
}



// Yes or No Settings Checkboxes
function eeSFL_ProcessCheckboxInput($eeTerm) {
	
	$eeValue = filter_var(@$_POST['ee' . $eeTerm], FILTER_SANITIZE_STRING);
	
	if($eeValue == 'YES') { return 'YES'; } else { return 'NO'; }
}



// Settings Text Inputs 
function eeSFL_ProcessTextInput($eeTerm, $eeType = 'text') {
	
	$eeValue = '';
	
	if($eeType == 'email') {
		
		$eeValue = filter_var(@$_POST['ee' . $eeTerm], FILTER_VALIDATE_EMAIL);
	
	} elseif($eeType == 'textarea') {
		
		// Currently not used
		return;
	
	} else {
		
		$eeValue = filter_var(@$_POST['ee' . $eeTerm], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
	}
	
	return $eeValue;
}




// Check if a file already exists, then number it so file will not be over-written.
function eeSFL_CheckForDuplicateFile($eeSFL_FilePathAdded, $eeSFL_ID = 1) { // Full path from WP root
	
	global $eeSFL, $eeSFL_Log;
	
	$eeSFL_Config = $eeSFL->eeSFL_Config($eeSFL_ID);
	
	if(@$eeSFL_Config['AllowOverwrite'] == 'YES') { // Overwriting files allowed
		return $eeSFL_FilePathAdded;
	}
	
	$eeCopyLimit = 1000; // File copies limit
	$eeDir = dirname($eeSFL_FilePathAdded) . '/';
		
	$eePathParts = pathinfo($eeSFL_FilePathAdded);
	$eeNameOnly = $eePathParts['filename'];
	$eeExtension = strtolower($eePathParts['extension']);
	
	$eeSFL_Files = get_option('eeSFL-FileList-' . $eeSFL_ID); // Our array of file info
	
	if($eeSFL_Files) {
	
		foreach($eeSFL_Files as $eeArray) { // Loop through file array and look for a match.
			
			$eeFilePath = $eeArray['FilePath']; // Get the /folder/name.ext
			
			// Check if duplicate
			if( $eeSFL_FilePathAdded == $eeSFL_Config['FileListDir'] . $eeFilePath ) { // Duplicate found
			
				$eeSFL_Log['Add Files'][] = 'Duplicate Item Found: ' . $eeSFL_FilePathAdded;
				
				if( is_file(ABSPATH . $eeSFL_FilePathAdded) ) { // Confirm the file is really there
					
					for ($i = 1; $i <= $eeCopyLimit; $i++) { // Look for existing copies
						
						$eeSFL_FilePathAdded = $eeDir . $eeNameOnly . '_(' . $i . ').' . $eeExtension; // Indicate the copy number
						
						if(!is_file(ABSPATH . $eeSFL_FilePathAdded)) { break; } // If no copy is there, we're done.
					}							
				}
			}
		}
	
	}
		
	return 	$eeSFL_FilePathAdded; // Return the new file name and path
}




// Return the general size of a file in a nice format.
function eeSFL_FormatFileSize($eeFileSizeBytes) {  
    
    $bytes = $eeFileSizeBytes;
    $kilobyte = 1024;
    $megabyte = $kilobyte * 1024;
    $gigabyte = $megabyte * 1024;
    $terabyte = $gigabyte * 1024;
    $precision = 2;
   
    if (($bytes >= 0) && ($bytes < $kilobyte)) {
        return $bytes . ' B';
 
    } elseif (($bytes >= $kilobyte) && ($bytes < $megabyte)) {
        return round($bytes / $kilobyte, $precision) . ' KB';
 
    } elseif (($bytes >= $megabyte) && ($bytes < $gigabyte)) {
        return round($bytes / $megabyte, $precision) . ' MB';
 
    } elseif (($bytes >= $gigabyte) && ($bytes < $terabyte)) {
        return round($bytes / $gigabyte, $precision) . ' GB';
 
    } elseif ($bytes >= $terabyte) {
        return round($bytes / $terabyte, $precision) . ' TB';
    } else {
        return $bytes . ' B';
    }
}




// The form submission results bar at the top of the admin pages
function eeSFL_ResultsDisplay($eeSFL_Results, $eeResultType) { // error, updated, etc...
	
	$eeReturn = '<div class="notice ';
	
	if(is_admin()) {
		$eeReturn .= $eeResultType;
	} else {
		$eeReturn .= 'eeResult';
	}
	
	$eeReturn .= ' is-dismissible"><p>';
	$eeReturn .= eeSFL_MessageDisplay($eeSFL_Results); // Parse the message array
	$eeReturn .= '</p></div>';
	
	return $eeReturn;
}




// Problem Display / Error reporting
function eeSFL_MessageDisplay($eeSFL_Message) {
	
	$eeReturn = '';
	
	$eeAdmin = is_admin();
	
	if(is_array($eeSFL_Message)) {
		
		if(!$eeAdmin) { $eeReturn .= '<div id="eeMessageDisplay">' . PHP_EOL; }
		
		$eeReturn .= '<ul>' . PHP_EOL; // Loop through $eeSFL_Messages array
		foreach($eeSFL_Message as $key => $value) { 
			if(is_array($value)) {
				foreach ($value as $value2) {
					$eeReturn .= "<li>$value2</li>" . PHP_EOL;
				}
			} else {
				$eeReturn .= "<li>$value</li>" . PHP_EOL;
			}
		}
		$eeReturn .= "</ul>" . PHP_EOL;
		
		if(!$eeAdmin) { $eeReturn .= '</div>' . PHP_EOL; }
		
		return $eeReturn;
		
	} else {
		return $eeSFL_Message;
	}
}



// Return a formatted header string
function eeSFL_ReturnHeaderString($eeFrom, $eeCc = FALSE, $eeBcc = FALSE) {
	
	$eeAdminEmail = get_option('admin_email');
	
	$eeHeaders = 'From: ' . get_option('blogname') . ' < ' . $eeAdminEmail . ' >'  . PHP_EOL;
	
	if($eeCc) { $eeHeaders .= "CC: " . $eeCc . PHP_EOL; }
	
	if($eeBcc) { $eeHeaders .= "BCC: " . $eeBcc . PHP_EOL; }
	
	if( !filter_var($eeFrom, FILTER_VALIDATE_EMAIL) ) {
		$eeFrom = $eeAdminEmail;
	}
	
	$eeHeaders .= "Return-Path: " . $eeAdminEmail . PHP_EOL . 
		"Reply-To: " . $eeFrom . PHP_EOL;
	
	return $eeHeaders;

}




// Process a raw input of email addresses
// Can be a single address or a comma sep list
function eeSFL_ProcessEmailString($eeString) {
	
	$eeString = filter_var($eeString, FILTER_SANITIZE_STRING);
	
	if( strpos($eeString, ',') ) { // More than one address?
		
		$eeArray = explode(',', $eeString);
		
		$eeAddresses = ''; // Reset
		
		foreach( $eeArray as $eeEmail) {
			
			$eeEmail = filter_var($eeEmail, FILTER_VALIDATE_EMAIL);
			
			if($eeEmail) {
				
				$eeAddresses .= $eeEmail . ','; // Reassemble validated addresses
			}
		}
		
		$eeAddresses = substr($eeAddresses, 0, -1); // Strip the last comma
	
	} else {
		
		$eeAddresses = filter_var($eeString, FILTER_VALIDATE_EMAIL);
	}
	
	if( strpos($eeAddresses, '@') ) {
		
		return $eeAddresses;
		
	} else {
		
		return FALSE;
	}
}





// Get what's in the address bar
function eeSFL_GetThisURL() {
	
	$thisUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	 
	return $thisUrl;
}

?>