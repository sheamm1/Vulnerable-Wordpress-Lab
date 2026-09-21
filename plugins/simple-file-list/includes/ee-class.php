<?php // Simple File List Script: ee-class.php | Author: Mitchell Bennis | support@simplefilelist.com | Revised: 12.12.2019
	
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! wp_verify_nonce( $eeSFL_Nonce, 'eeSFL_Class' ) ) exit('ERROR 98'); // Exit if nonce fails

$eeSFL_Log[] = 'Loaded: ee-class';

class eeSFL_MainClass {
			
	// Basics
	public $eePluginName = 'Simple File List';
	public $eePluginNameSlug = 'simple-file-list';
	public $eePluginSlug = 'ee-simple-file-list';
	public $eePluginMenuTitle = 'File List';
	public $eePluginWebPage = 'http://simplefilelist.com';
	public $eeAddOnsURL = 'https://get.simplefilelist.com/index.php';
	// public $eeListID = 1;
	public $eeAllFilesSorted = array();
	public $eeDefaultUploadLimit = 99;
	public $eeFileThumbSize = 64;
    
    // File Types
    public $eeDynamicImageThumbFormats = array('gif', 'jpg', 'jpeg', 'png');
    public $eeDynamicVideoThumbFormats = array('avi', 'flv', 'm4v', 'mov', 'mp4', 'wmv');
    public $eeDefaultThumbFormats = array('3gp', 'ai', 'aif', 'aiff', 'apk', 'avi', 'bmp', 'cr2', 'dmg', 'doc', 'docx', 
    	'eps', 'flv', 'gz', 'indd', 'iso', 'jpeg', 'jpg', 'm4v', 'mov', 'mp3', 'mp4', 'mpeg', 'mpg', 'pdf', 'png', 
		'pps', 'ppsx', 'ppt', 'pptx', 'psd', 'tar', 'tgz', 'tif', 'tiff', 'txt', 'wav', 'wma', 'wmv', 'xls', 'xlsx', 'zip', 'folder');
	public $eeOpenableFileFormats = array('aif', 'aiff', 'avi', 'bmp', 'flv', 'jpeg', 'jpg', 'm4v', 'mov', 'mp3', 'mp4', 'mpeg', 'mpg', 'pdf', 'png', 
		'txt', 'wav', 'wma', 'wmv', 'folder', 'htm', 'html');
    
    public $eeExcludedFileNames = array('error_log', 'index.html');
    private $eeForbiddenTypes = array('.php', '.exe', '.js', '.com', '.wsh', '.vbs');
    
    private $eeExcludedFiles = array('index.html');
    
    public $eeNotifyMessageDefault = 'Greetings,' . PHP_EOL . PHP_EOL . 
    	'You should know that a file has been uploaded to your website.' . PHP_EOL . PHP_EOL . 
    		'[file-list]' . PHP_EOL . PHP_EOL;
    
    
    // The Default List Definition
    public $DefaultListSettings = array( // An array of file list settings arrays
		
		1 => array(
			
			// List Settings
			'ListTitle' => 'Simple File List', // List Title (Not currently used)
			'FileListDir' => 'wp-content/uploads/simple-file-list', // List Directory Name (relative to ABSPATH)
			'ExpireTime' => 0, // Hours before next re-scan
			'ShowList' => 'YES', // Show the File List (YES, ADMIN, USER, NO)
			'AdminRole' => 2, // Who can access settings, based on WP role (5 = Admin ... 1 = Subscriber)
			'ShowFileThumb' => 'YES', // Display the File Thumbnail Column (YES or NO)
			'ShowFileDate' => 'YES', // Display the File Date Column (YES or NO)
			'ShowFileSize' => 'YES', // Display the File Size Column (YES or NO)
			'LabelThumb' => 'Thumbnail', // Label for the thumbnail column
			'LabelName' => 'Name', // Label for the file name column
			'LabelDate' => 'Date', // Label for the file date column
			'LabelSize' => 'Size', // Label for the file size column
			'SortBy' => 'Date', // Sort By (Name, Date, Size, Random)
			'SortOrder' => 'Descending', // Descending or Ascending
			
			// Upload Settings
			'AllowUploads' => 'YES', // Allow File Uploads (YES, ADMIN, USER, NO)
			'UploadLimit' => 10, // Limit Files Per Upload Job (Quantity)
			'UploadMaxFileSize' => 1, // Maximum Size per File (MB)
			'FileFormats' => 'gif, jpg, jpeg, png, tif, pdf, wav, wmv, wma, avi, mov, mp4, m4v, mp3, zip', // Allowed Formats
			'AllowOverwrite' => 'NO', // Number new files with same name, or just overwrite.
			
			// Display Settings
			'PreserveSpaces' => 'NO', // Replace ugly hyphens with spaces
			'ShowFileDescription' => 'YES', // Display the File Description (YES or NO)
			'ShowFileActions' => 'YES', // Display the File Action Links Section (below each file name) (YES or NO)
			'ShowFileExtension' => 'YES', // Show the file extension, or not.
			'ShowHeader' => 'YES', // Show the File List's Table Header (YES or NO)
			'ShowUploadLimits' => 'YES', // Show the upload limitations text.
			'GetUploaderInfo' => 'NO', // Show the Info Form
			'ShowSubmitterInfo' => 'NO', // Show who uploaded the file (name linked to their email)
			'AllowFrontSend' => 'NO', // Allow users to email file links (YES or NO)
			'AllowFrontManage' => 'NO', // Allow front-side users to manage files (YES or NO)
			
			// Notifications
			'Notify' => 'YES', // Send Notifications (YES or NO)
			'NotifyTo' => '', // Send Notification Email Here (Defaults to WP Admin Email)
			'NotifyCc' => '', // Send Copies of Notification Emails Here
			'NotifyBcc' => '', // Send Blind Copies of Notification Emails Here
			'NotifyFrom' => '', // The sender email (reply-to) (Defaults to WP Admin Email)
			'NotifyFromName' => '', // The nice name of the sender
			'NotifySubject' => '', // The subject line
			'NotifyMessage' => '', // The notice message's body
			
			// Extensions will add to this as needed
		)
	);
    
    
    // Get Environment
    public function eeSFL_GetEnv() {
	    
	    $eeSFL_Env = array();
	    
	    // Detect OS
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
		    $eeSFL_Env['eeOS'] = 'WINDOWS';
		} else {
		    $eeSFL_Env['eeOS'] = 'LINUX';
		}
		
		// Detect Web Server
		if(!function_exists('apache_get_version')) {
		    $eeSFL_Env['eeWebServer'] = $_SERVER["SERVER_SOFTWARE"];
		} else {
			$eeSFL_Env['eeWebServer'] = 'Apache';
		}
		
		$eeSFL_Env['wpSiteURL'] = get_site_url() . '/'; // This Wordpress Website
		$eeSFL_Env['wpPluginsURL'] = plugins_url() . '/'; // The Wordpress Plugins Location
		
		$eeSFL_Env['pluginURL'] = plugins_url() . '/' . $this->eePluginNameSlug . '/';
		$eeSFL_Env['pluginDir'] = WP_PLUGIN_DIR . '/' . $this->eePluginNameSlug . '/';
		
		$wpUploadArray = wp_upload_dir();
		$wpUploadDir = $wpUploadArray['basedir'];
		$eeSFL_Env['wpUploadDir'] = $wpUploadDir . '/'; // The Wordpress Uploads Location
		$eeSFL_Env['wpUploadURL'] = $wpUploadArray['baseurl'] . '/';

		$eeSFL_Env['FileListDefaultDir'] = str_replace(ABSPATH, '', $eeSFL_Env['wpUploadDir'] . 'simple-file-list/'); // The default file list location
		
		$eeSFL_Env['upload_max_upload_size'] = substr(ini_get('upload_max_filesize'), 0, -1); // PHP Limit (Strip off the "M")
		$eeSFL_Env['post_max_size'] = substr(ini_get('post_max_size'), 0, -1); // PHP Limit (Strip off the "M")
		
		// Check which is smaller, upload size or post size.
		if ($eeSFL_Env['upload_max_upload_size'] <= $eeSFL_Env['post_max_size']) { 
			$eeSFL_Env['the_max_upload_size'] = $eeSFL_Env['upload_max_upload_size'];
		} else {
			$eeSFL_Env['the_max_upload_size'] = $eeSFL_Env['post_max_size'];
		}
		
		$eeSFL_Env['supported'] = get_option('eeSFL_Supported'); // Server technologies available (i.e. FFMPEG)
		
		$eeSFL_Env['wpUserID'] = get_current_user_id();
		
		return $eeSFL_Env;
    }
    
    

    // Get Settings for Specified List
    public function eeSFL_Config($eeSFL_ID = 1) {
	    
	    global $eeSFL_Log, $eeSFL_Env;
	    
	    $eeSFL_Config = array();
	    
	    // Getting the settings array
	    $eeArray = get_option('eeSFL-Settings');
	    
	    $eeSFL_Env['FileLists'] = $eeArray;
	    
	    if(is_array($eeArray)) {
	    
			// Get sub-array for this list ID
			$eeSFL_Config = $eeArray[$eeSFL_ID];
			
			// Check Environment
			if($eeSFL_Env['the_max_upload_size'] < $eeSFL_Config['UploadMaxFileSize']) {
				$eeSFL_Config['UploadMaxFileSize'] = $eeSFL_Env['the_max_upload_size']; // If Env is lower, set to that.
			}
			
			$eeSFL_Config['FileListBaseDir'] = $eeSFL_Config['FileListDir']; // Before folders are added
			$eeSFL_Config['FileListURL'] = $eeSFL_Env['wpSiteURL'] . $eeSFL_Config['FileListDir']; // The Full URL
			
			// The whole point...	
			return $eeSFL_Config; // Associative array
		
		} else {
			return $this->DefaultListSettings;
		}
	}
	    
    
    // Default File List Definition
    public $eeSFL_Files = array(
	    
		0 => array( // The File ID (We copy this to the array on-the-fly when sorting)
			'FileList' => 1, // The ID of the File List, contained in the above array.
		    'FilePath' => '', // Path to file, relative to the list root
		    'FileExt' => '', // The file extension
			'FileSize' => '', // The size of the file
			'FileDateAdded' => '', // Date the file was added to the list
			'FileDateChanged' => '', // Last date the file was renamed or otherwise changed
			'FileDescription' => '', // A short description of the file
			
			'SubmitterName' => '', // Who uploaded the file
			'SubmitterEmail' => '', // Their email
			'SubmitterComments' => '', // What they said
			
			'FileUserGroup' => '', // (Coming Later)
			'FileOwner' => '' // (Coming Later)
		)
    );
    
    
    
    public function eeSFL_UpdateFileDetail($eeSFL_ID, $eeFile, $eeDetail, $eeValue = FALSE) {
	    
	    if($eeValue) {
	    
		    // Get the current file array
			$eeFileArray = get_option('eeSFL-FileList-' . $eeSFL_ID);
			
			foreach( $eeFileArray as $eeKey => $eeThisFileArray ) {
		
				if($eeFile == $eeThisFileArray['FilePath']) { // Look for this file
					
					$eeFileArray[$eeKey][$eeDetail] = $eeValue;
				}
			}
			
			// Save the updated array
			$eeFileArray = update_option('eeSFL-FileList-' . $eeSFL_ID, $eeFileArray);
			
			return $eeFileArray;
		
		} else {
			return FALSE;
		}
	}

    
    
    // Scan the real files and create or update array as needed.
    public function eeSFL_UpdateFileListArray($eeSFL_ID = 1) {
	    
	    global $eeSFL_Log, $eeSFL_Config, $eeSFL_Env, $eeSFLF;
	    
	    $eeSFL_Log['File List'][] = 'Scanning File List...';
	    
	    $eeFilesArray = get_option('eeSFL-FileList-' . $eeSFL_ID); // Get the File List Array
	    
	    $eeFilePathsArray = $this->eeSFL_IndexFileListDir($eeSFL_Config['FileListDir']); // Get the real files
	    
	    if ( !is_array($eeFilesArray) OR !@count($eeFilesArray) ) { // Creating New
			
			$eeSFL_Log['File List'][] = 'No List Found! Creating from scratch...';
			
			$eeFilesArray = array();
			
			foreach( $eeFilePathsArray as $eeKey => $eeFile) {
				
				$eeFilesArray[$eeKey]['FileList'] = $eeSFL_ID;
				$eeFilesArray[$eeKey]['FilePath'] = $eeFile;
				$eePathParts = pathinfo($eeFile);
				$eeFileNameAlone = $eePathParts['filename'];
				$eeExtension = strtolower(@$eePathParts['extension']);
				
				if(!$eeExtension) { 
					
					$eeFilesArray[$eeKey]['FileExt'] = 'folder';
					$eeFilesArray[$eeKey]['FileSize'] = 0;
				
				} else { 
					
					$eeFilesArray[$eeKey]['FileExt'] = $eeExtension;
					$eeFilesArray[$eeKey]['FileSize'] = @filesize(ABSPATH . $eeSFL_Config['FileListDir'] . $eeFile);
				}
				
				$eeFilesArray[$eeKey]['FileDateAdded'] = date("Y-m-d H:i:s", filemtime(ABSPATH . $eeSFL_Config['FileListDir'] . $eeFile));
				$eeFilesArray[$eeKey]['FileDateChanged'] = $eeFilesArray[$eeKey]['FileDateAdded'];
				
			}
		
		} else { // Update file info
			
			$eeSFL_Log['File List'][] = 'Updating existing list...';
			
			if(!$eeFilesArray) { return FALSE; } // No files found
			
			$eeFileArrayNew = $eeFilesArray;
			
			// Check to be sure files are there...
			foreach( $eeFilesArray as $eeKey => $eeFileSet) {
				
				// Check if file is there
				$eeFile = ABSPATH . $eeSFL_Config['FileListDir'] . $eeFileSet['FilePath'];
				
				if( is_file($eeFile) ) { // Update file particulars
					
					// Update file size
					$eeFileArrayNew[$eeKey]['FileSize'] = @filesize($eeFile);
					
					// Update modification date
					$eeFileArrayNew[$eeKey]['FileDateChanged'] = date("Y-m-d H:i:s", @filemtime($eeFile));
					
				} elseif( is_dir($eeFile) ) {
					
					if($eeSFLF) {
						if($eeSFL_Config['ShowFolderSize'] == 'YES') { // How Big?
							$eeFileArrayNew[$eeKey]['FileSize'] = $eeSFLF->eeSFLF_GetFolderSize( $eeSFL_Config['FileListDir'] . $eeFileSet['FilePath'] );
						}
					}
				} else { // Get rid of it
					
					$eeSFL_Log['File List'][] = 'Removing: ' . $eeFile;
					
					unset($eeFileArrayNew[$eeKey]);
				}
				
				// If no file path
				if(strlen($eeFileSet['FilePath']) === 0) {
					unset($eeFileArrayNew[$eeKey]);
				}
			}

			
			// Check if any new files have been added
			foreach( $eeFilePathsArray as $eeKey => $eeFile) {
				
				$eeMatch = FALSE;
				
				// Look for this file in our array
				foreach( $eeFilesArray as $eeKey2 => $eeArray ) {
					
					if($eeFile == $eeArray['FilePath']) {
						
						$eeMatch = TRUE;
						
						break; // Matched this file
					}
				}
				
				if($eeMatch === FALSE) { // New File Found
					
					$eeSFL_Log['File List'][] = 'Added: ' . $eeFile;
					
					$eePathParts = pathinfo($eeFile);
					$eeDirName = $eePathParts['dirname'];
					$eeFileName = $eePathParts['basename'];
					$eeFileNameAlone = $eePathParts['filename'];
					$eeExtension = strtolower(@$eePathParts['extension']); // Throws a notice on folders
					$eeKey .= '_new'; // Make sure the key is unique
					
					$eeFileArrayNew[$eeKey]['FileList'] = $eeSFL_ID;
					$eeFileArrayNew[$eeKey]['FilePath'] = $eeFile;
					
					if(!$eeExtension) { 
				
						$eeFileArrayNew[$eeKey]['FileExt'] = 'folder';
						$eeFileArrayNew[$eeKey]['FileSize'] = 0;
					
					} else { 
						
						$eeFileArrayNew[$eeKey]['FileExt'] = $eeExtension;
						$eeFileArrayNew[$eeKey]['FileSize'] = @filesize(ABSPATH . $eeSFL_Config['FileListDir'] . $eeFile);
					}
					
					$eeFileArrayNew[$eeKey]['FileDateAdded'] = date("Y-m-d H:i:s", @filemtime(ABSPATH . $eeSFL_Config['FileListDir'] . $eeFile));
					$eeFileArrayNew[$eeKey]['FileDateChanged'] = $eeFileArrayNew[$eeKey]['FileDateAdded'];
				}
			}
			
			$eeFilesArray = $eeFileArrayNew;
		}
		
		
		if( count($eeFilesArray) ) {
	    
		    $eeFilesArray = array_values($eeFilesArray); // Reset the Keys
		    
		    // Update the DB
		    update_option('eeSFL-FileList-' . $eeSFL_ID, $eeFilesArray);
		    
		    foreach($eeFilesArray as $eeKey => $eeFile) {
		    	
		    	// Check and create thumbnail if needed...
				$eeExt = $eeFile['FileExt'];
				if( in_array($eeExt, $this->eeDynamicImageThumbFormats) OR in_array($eeExt, $this->eeDynamicVideoThumbFormats) ) {
					$this->eeSFL_CheckThumbnail($eeFile['FilePath']);
				}
		    }
		    
		    // Set the transient
		    if(@$eeSFL_Config['ExpireTime'] >= 1) {
			
				$eeExpiresIn = $eeSFL_Config['ExpireTime'] * HOUR_IN_SECONDS;
				
				$eeSFL_Log[] = 'Setting transient to expire in ' . $eeSFL_Config['ExpireTime'] . ' hours.';
				
				set_transient('eeSFL_FileList-' . $eeSFL_ID, 'Good', $eeExpiresIn);
			}
		

		    // Check for FFmpeg here
			if(trim(@shell_exec('type -P ffmpeg'))) {
				update_option('eeSFL_Supported', 'ffMpeg');
			} else {
				$eeSFL_Log[] = 'FFMPEG is not supported';
			}
			
			return $eeFilesArray;
		}
	    
	    return FALSE;
    }
	
	
	
	
	// Get All the Files
	private function eeSFL_IndexFileListDir($eeFileListDir) {
	    
	    global $eeSFLF, $eeSFL_Log;
	    
	    $eeFilesArray = array();
	    
	    if(!is_dir(ABSPATH . $eeFileListDir)) {
		    
		    $eeSFL_Log['errors'][] = 'The File List is Gone. Re-Creating...';
		    
		    eeSFL_FileListDirCheck($eeFileListDir);
		    return $eeFilesArray;
	    }
	    
	    if($eeSFLF) {
		    
		    $eeSFL_Log['File List'][] = 'Getting files and folders...';
		    
		    $eeSFLF->eeSFLF_IndexCompleteFileListDirectory($eeFileListDir);
		    $eeFilesArray = $eeSFLF->eeSFLF_FileListArray;
		    
		    $eeFilesArray = str_replace($eeFileListDir . '/', '', $eeFilesArray); // Strip the FileListDir
		    
	    } else {
		    
		    $eeSFL_Log['File List'][] = 'Getting files from: ' . $eeFileListDir; 
		    
		    $eeFileNameOnlyArray = scandir(ABSPATH . $eeFileListDir);
		    
		    foreach($eeFileNameOnlyArray as $eeValue) {
		    	
		    	if(strpos($eeValue, '.') !== 0 ) { // Not hidden
			    	
			    	if(is_file(ABSPATH . $eeFileListDir . $eeValue)) { // Is a regular file
			    	
				    	if(!in_array($eeValue, $this->eeExcludedFiles) )  { // Not excluded
					    	
					    	// Catch and correct spaces in items found
					    	if( strpos($eeValue, ' ') AND strpos($eeValue, ' ') !== 0 ) {
				        
						        $eeNewItem = str_replace(' ', '-', $eeValue);
						        
						        if(rename(ABSPATH . $eeFileListDir . $eeValue, ABSPATH . $eeFileListDir . $eeNewItem)) {
							        $eeValue = $eeNewItem;
						        }
						    }
					    	
					    	$eeFilesArray[] = $eeValue; // Add the path
				    	}
			    	}
		    	}
		    }
	    }
	    
	    
	    if(!count($eeFilesArray)) {
		    // $eeSFL_Log['errors'][] = 'No Files Found';
		    $eeSFL_Log['File List'][] = 'No Files Found';
	    }

		return $eeFilesArray;
	}
	
	
	
	// Check thumbnail
	public function eeSFL_CheckThumbnail($eeFilePath) { // File Path relative to FileListDir
		
		global $eeSFL_Log, $eeSFL_Config, $eeSFL_Env;
		
		$eeExt = FALSE;
		$eeScreenshot = FALSE;
		$eeThumbURL = FALSE;
		
		$eePathParts = pathinfo($eeFilePath);
		$eeDir = $eePathParts['dirname']; // In a folder ?
		if($eeDir == '.') { $eeDir = FALSE; } else { $eeDir .= '/'; } // For home folder
		
		$eeBaseName = $eePathParts['basename'];
		$eeFileNameOnly = $eePathParts['filename'];
		$eeExt = strtolower(@$eePathParts['extension']);
		
		$eeFileFullPath = ABSPATH . $eeSFL_Config['FileListDir'] . $eeFilePath;
		$eeFileFullPath = str_replace('//', '/', $eeFileFullPath);
		
		$eeThumbsPATH = ABSPATH . $eeSFL_Config['FileListDir'] . $eeDir . '.thumbnails/';
		
		// Is there already a thumb?
		if(is_file($eeThumbsPATH . 'thumb_' . $eeFileNameOnly . '.jpg')) { return TRUE; } // Checked Okay
		
		// Else...
		
		// Video Files
		if(in_array($eeExt, $this->eeDynamicVideoThumbFormats)) { // Check for FFMPEG
			
			if($eeSFL_Env['supported']) {
				
				// FFmpeg won't create the thumbs dir, so we need to do it here if needed.
				if(!is_dir($eeThumbsPATH)) { mkdir($eeThumbsPATH); }
				
				$eeExt = 'png'; // Set the extension
				$eeScreenshot = $eeThumbsPATH . 'eeScreenshot_' . $eeFileNameOnly . '.' . $eeExt; // Create a temporary file
				
				// Create a full-sized image at the one-second mark
				$eeCommand = 'ffmpeg -i ' . $eeFileFullPath . ' -ss 00:00:01.000 -vframes 1 ' . $eeScreenshot;
				
				$eeFFmpeg = trim(shell_exec($eeCommand));
				
				$eeSFL_Log['FFmpeg'][] = $eeCommand;
				
				if(is_file($eeScreenshot)) { // <<<------------------------ TO DO - Resize down to $this->eeFileThumbSize
					
					$this->eeSFL_CreateThumbnailImage($eeScreenshot);
					
					unlink($eeScreenshot); // Delete the screeshot file
					
					return TRUE;
				
				} else {
					
					// FFmpeg FAILED !!!
					$eeSFL_Log[] = 'FFmpeg could not read ' . $eeFileNameOnly . '. Using the default thumbnail.';
					
					// Create a default thumb, as if there was no FFmpeg <<<--------- TO DO - Try to improve the command above / How can we do this on the fly in the list?
					$eeFrom = $eeSFL_Env['pluginDir'] . 'images/thumbnails/!default_video.jpg';
					$eeTo = $eeThumbsPATH . 'thumb_' . $eeFileNameOnly . '.jpg';
					
					if(!copy($eeFrom, $eeTo)) {
						$eeSFL_Log[] = 'FFmpeg could create the default thumbnail.';
						return FALSE;
					}
					
					return TRUE;
				}
					
			} else {
				$eeSFL_Log[] = 'FFmpeg Not Installed';
			}
		}
		
		// Image Files
		if(in_array($eeExt, $this->eeDynamicImageThumbFormats)) { // Just for known image files... 
		
			// Generate an Image Thumbnail
			if(!$eeExt OR strpos($eeExt, '.') === 0) { // It's a Folder or Hidden
			
				$eeExt = 'folder';
			
			} else {
		
				$this->eeSFL_CreateThumbnailImage($eeFileFullPath);
				
				return TRUE;
			}
		}
	}
	
	
	
	
	// Create thumbnail image
	private function eeSFL_CreateThumbnailImage($eeFileFullPath) { // Requires full path 
		
		global $eeSFL_Log, $eeSFL_Config, $eeSFL_Env;
		
		$eeFileSize = filesize($eeFileFullPath);
		
		$eeFilePath = str_replace(ABSPATH . $eeSFL_Config['FileListDir'], '', $eeFileFullPath); // Strip path thru FileListDir
		
		$eePathParts = pathinfo($eeFilePath);
		
		if( !is_array($eePathParts) ) { 
	        $eeSFL_Log['errors'][] = 'No Path Parts';
	        $eeSFL_Log['errors'][] = $eeFileFullPath;
	        return FALSE;
	    }
		
		$eeFileNameOnly = $eePathParts['filename'];
		$eeFileExt = $eePathParts['extension'];
		$eeDir = $eePathParts['dirname']; // Get this folder location
		if($eeDir) { $eeDir .= '/'; } // Add the slash
		$eeDir = str_replace('.thumbnails/', '', $eeDir); // Remove if .thumbnails/ is part of the path (video thumbs)

		// Dynamicly created thumbnails are here
		$eeThumbsURL = $eeSFL_Env['wpSiteURL'] . '/' . $eeSFL_Config['FileListURL'] . $eeDir . '.thumbnails/'; 
		$eeThumbsPATH = ABSPATH . $eeSFL_Config['FileListDir'] . $eeDir . '.thumbnails/'; // Path to them
        
        $eeSizeCheck = @getimagesize($eeFileFullPath);
        
        // $eeSizeCheck['filesize'] = eeSFL_FormatFileSize($eeFileSize);
        // $eeSizeCheck['pixels'] = $eeSizeCheck[0] * $eeSizeCheck[1];
        // $eeSizeCheck['pixels-per-byte'] = $eeSizeCheck['pixels'] / $eeFileSize;
        
        $eeSizeCheck['memory-limit'] = preg_replace("/[^0-9]/", "", ini_get('memory_limit') ) * 1048576;
        $eeSizeCheck['memory-usage'] = memory_get_usage();
        
        $eeImageMemoryNeeded = ($eeSizeCheck[0] * $eeSizeCheck[1] * $eeSizeCheck['bits']) / 8;
        
        // $eeSizeCheck['image-memory'] = eeSFL_FormatFileSize($eeImageMemoryNeeded);
        
        $eeImageSizeLimit = ( $eeSizeCheck['memory-limit'] - $eeSizeCheck['memory-usage'] ) * .2;
        
        // $eeSizeCheck['image-size-limit'] = eeSFL_FormatFileSize($eeImageSizeLimit);
        
        // echo '<pre>' . $eeFileNameOnly; print_r($eeSizeCheck); echo '</pre>'; exit;
        
        if($eeImageMemoryNeeded >  $eeImageSizeLimit) { // It's too big for Wordpress
			
			$eeDefaultThumbIcon = $eeSFL_Env['pluginDir'] . 'images/thumbnails/!default_image.jpg';
			$eeNewThumb = $eeThumbsPATH . 'thumb_' . $eeFileNameOnly . '.jpg';
		
			copy($eeDefaultThumbIcon, $eeNewThumb); // Use our default image file icon
		
		} else { // Create thumbnail
			
			// Thank Wordpress for this easyness.
			$eeFileImage = wp_get_image_editor($eeFileFullPath); // Try to open the file
	        
	        if (!is_wp_error($eeFileImage)) { // Image File Opened
	            
	            $eeFileImage->resize($this->eeFileThumbSize, $this->eeFileThumbSize, TRUE); // Create the thumbnail
	            
	            $eeFileNameOnly = str_replace('eeScreenshot_', '', $eeFileNameOnly); // Strip the temp term from video screenshots
			
				$eeSFL_Log['creating thumb'][] = $eeThumbsPATH . 'thumb_' . $eeFileNameOnly . '.jpg'; 
	            
	            $eeFileImage->save($eeThumbsPATH . 'thumb_' . $eeFileNameOnly . '.jpg'); // Save the file
	        
	        } else { // Cannot open
		        
		        $eeSFL_Log['errors'][] = 'File Not Compatible: ' . basename($eeFileFullPath);   
	        }
		}
	}
	
	
	
	
	// Move the sort item to the array key and then sort. Preserve the key (File ID) in a new element
	public function eeSFL_SortFiles($eeFiles, $eeSortBy, $eeSortOrder) {
		
		global $eeSFL_Log;
		
		if(is_array($eeFiles)) {
			if( count($eeFiles) <= 1 ) { return $eeFiles; } // No point if none or one
		} else {
			return FALSE;
		}
		
		$eeSFL_Log['File List'][] = 'Sorting by...';
		$eeSFL_Log['File List'][] = $eeSortBy . ' > ' . $eeSortOrder;
		
		$eeFilesSorted = array();
			
		if($eeSortBy == 'Date') { // Files by Date
			
			foreach($eeFiles as $eeKey => $eeFileArray) {
				
				if(@$eeFileArray['FileDateChanged']) {
					
					$eeFilesSorted[ $eeFileArray['FileDateChanged'] . ' ' . $eeKey ] = $eeFileArray; // Add the file key to preserve files with same date or size.
					$eeFilesSorted[$eeFileArray['FileDateChanged'] . ' ' . $eeKey]['FileID'] = $eeKey; // Save the ID in new element
				
				} elseif($eeFileArray['FileDateAdded']) {
					
					$eeFilesSorted[ $eeFileArray['FileDateAdded'] . ' ' . $eeKey ] = $eeFileArray;
					$eeFilesSorted[$eeFileArray['FileDateAdded'] . ' ' . $eeKey]['FileID'] = $eeKey;
				}
			}
			
		} elseif($eeSortBy == 'Size') { // Files by Size
			
			foreach($eeFiles as $eeKey => $eeFileArray) {
				
				// Add the file key to preserve files with same size.
				$eeFilesSorted[ $eeFileArray['FileSize'] . '.' . $eeKey ] = $eeFileArray;
				$eeFilesSorted[$eeFileArray['FileSize'] . '.' . $eeKey]['FileID'] = $eeKey;
			}
	
		} elseif($eeSortBy == 'Name') { // Alpha
			
			foreach($eeFiles as $eeKey => $eeFileArray) {
				
				$eeFilesSorted[ $eeFileArray['FilePath'] ] = $eeFileArray; // These keys shall always be unique
				$eeFilesSorted[ $eeFileArray['FilePath'] ]['FileID'] = $eeKey;
			}
		
		} else { // Random
			
			foreach($eeFiles as $eeKey => $eeFileArray) {
				
				$eeFilesSorted[$eeKey]['FileID'] = $eeKey;
			}
			
			$eeFilesSorted = shuffle($eeFilesSorted);
			
			return $eeFilesSorted;
		}
		
		ksort($eeFilesSorted); // Sort by the key
		
		// If Descending
		if($eeSortOrder == 'Descending') {
			$eeFilesSorted = array_reverse($eeFilesSorted);
		}
		
		$eeFilesSorted = array_values($eeFilesSorted); // Reindex the array keys
		
		return $eeFilesSorted;
	}
	
	
	
	public function eeSFL_SendFilesEmail($eePOST) {
		
		global $eeSFL_Config, $eeSFL_Env;
		$eeFiles = '';
		
		// Process a raw input of email addresses
		$eeFrom = filter_var(@$eePOST['eeSFL_SendFrom'], FILTER_VALIDATE_EMAIL); // 1 Required
		if(!$eeFrom) { return FALSE; }
		
		$eeTo = eeSFL_ProcessEmailString(@$eePOST['eeSFL_SendTo']); // Required
		if(!$eeTo) { return FALSE; }
		
		// Optional Inputs
		$eeCc = eeSFL_ProcessEmailString(@$eePOST['eeSFL_SendCc']);
		
		// Email Headers
		$eeHeaders = eeSFL_ReturnHeaderString($eeFrom, $eeCc);
		
		// Subject
		$eeSubject = filter_var(@$eePOST['eeSFL_SendSubject'], FILTER_SANITIZE_STRING);
		if(!$eeSubject) { $eeSubject = __('File Notification', 'ee-simple-file-list'); }
		
		// Message
		$eeMessage = filter_var(@$eePOST['eeSFL_SendMessage'], FILTER_SANITIZE_STRING);
		
		if( is_array($eePOST['eeSFL_SendTheseFiles']) ) { // The files array checkboxes
			
			foreach( $eePOST['eeSFL_SendTheseFiles'] as $eeFile) {
				$eeFiles .= '-> ' . $eeSFL_Config['FileListURL'] . urldecode($eeFile) . PHP_EOL;
			}
		}
		
		// Footer
		if(@$eeSFL_Config['SendFooter']) {
			$eeFooter = $eeSFL_Config['SendFooter'];
		} else {
			if( is_admin() ) {
				$eeURL = $eeSFL_Env['wpSiteURL']; // The main URL
			} else {
				$eeURL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; // The exact page
			}
			if($eeURL) {
				$eeFooter = 'Sent from the file list at ' . $eeURL;
			} else {
				$eeFooter = 'Powered by Simple File List';
			}
			
		}
		
		// The Body
		$eeMessage .= PHP_EOL .  PHP_EOL . $eeFiles . PHP_EOL .  PHP_EOL . $eeFooter; // with Custom Footer
		
		// TO DO -- Allow files to be attached if less than X MB
		$eeAttached = array();
		
		// Send the Message
		wp_mail($eeTo, $eeSubject, $eeMessage, $eeHeaders, $eeAttached);
	}
	
	
	
	
	// Send a system notification email, via ee-email-engine.php 
	public function eeSFL_AjaxEmail($eeSFL_UploadJob, $eeSFL_ID = 1) {
		
		global $eeSFL_Log, $eeSFL_Config, $eeSFL_Env;
		
		// Email Notifications
		$eeSFL_Message = '';
		$eeSFL_Body = '';
		
		// Notifications via AJAX Engine
		if($eeSFL_UploadJob) {
		
			$eeSFL_Log['notice'][] = 'Sending Notification Email';
			
			// Create nonce to be checked on the other side
			$eeSFL_EmailNonce = wp_create_nonce('ee-simple-file-list-email');
			
			// Build the Message Body
			$eeSFL_Body = $eeSFL_Config['NotifyMessage'];
			$eeSFL_Body = str_replace('[file-list]', $eeSFL_UploadJob, $eeSFL_Body); // Add files
			$eeSFL_Body = str_replace('[web-page]', get_permalink(), $eeSFL_Body); // Add location
			
			// Get Form Input?
			if(@$_POST['eeSFL_Email']) {
				
				$eeSFL_Body .= PHP_EOL . PHP_EOL . __('Uploader Information', 'ee-simple-file-list') . PHP_EOL;
				
				$eeSFL_Name = substr(filter_var(@$_POST['eeSFL_Name'], FILTER_SANITIZE_STRING), 0, 64);
				$eeSFL_Name = strip_tags($eeSFL_Name);
				if($eeSFL_Name) { 
					$eeSFL_Body .= __('Uploaded By', 'ee-simple-file-list') . ': ' . ucwords($eeSFL_Name) . " - ";
				}
				
				$eeSFL_Email = substr(filter_var(@$_POST['eeSFL_Email'], FILTER_VALIDATE_EMAIL), 0, 128);
				$eeSFL_Body .= strtolower($eeSFL_Email) . PHP_EOL;
				$eeSFL_ReplyTo = $eeSFL_Name . ' <' . $eeSFL_Email . '>';
				
				$eeSFL_Comments = substr(filter_var(@$_POST['eeSFL_Comments'], FILTER_SANITIZE_STRING), 0, 5012);
				$eeSFL_Comments = strip_tags($eeSFL_Comments);
				if($eeSFL_Comments) {
					$eeSFL_Body .= $eeSFL_Comments . PHP_EOL . PHP_EOL;
				}
			}
			

			
			// Show if no extensions installed
			if( @count($eeSFL_Env['installed']) < 1 OR strlen($eeSFL_Body) < 3) { // Or if no content
				
				$eeSFL_Body .= PHP_EOL . PHP_EOL . "----------------------------------"  . 
				PHP_EOL . "Powered by Simple File List - simplefilelist.com";
			}
			
			
			// Send the message to the Email Engine via Ajax
			$eeOutput = '<script type="text/javascript">
				
				console.log("Simple File List - Upload Notification");
				
				function eeSFL_Notification() {
				
					var eeSFL_Url = "' . $eeSFL_Env['pluginURL'] . 'ee-email-engine.php' . '";
					var eeSFL_Xhr = new XMLHttpRequest();
					var eeSFL_FormData = new FormData();
					    
					console.log("Calling Email Engine: " + eeSFL_Url);
					    
					eeSFL_Xhr.open("POST", eeSFL_Url, true);
					    
				    eeSFL_Xhr.onreadystatechange = function() {
				        
				        if (eeSFL_Xhr.readyState == 4) {
			            
			            	// Every thing ok
				            console.log("RESPONSE: " + eeSFL_Xhr.responseText);
				            
				            if(eeSFL_Xhr.responseText == "SENT") {
					            
				            	console.log("Message Sent");
								
					        } else {
						    	
						    	console.log("XHR Status: " + eeSFL_Xhr.status);
						    	console.log("XHR State: " + eeSFL_Xhr.readyState);
						    	
						    	var n = eeSFL_Xhr.responseText.search("<"); // Error condition
						    	
						    	if(n === 0) {
							    	console.log("Error Returned: " + eeSFL_Xhr.responseText);
							    }
							    return false;
					        }
				        
				        } else {
					    	console.log("XHR Status: " + eeSFL_Xhr.status);
					    	console.log("XHR State: " + eeSFL_Xhr.readyState);
					    	return false;
				        }
				    };';
				    
				    // Security First
				    $eeSFL_Timestamp = time();
				    $eeSFL_TimestampMD5 = md5('eeSFL_' . $eeSFL_Timestamp);
				    
				    $eeSFL_Body = json_encode($eeSFL_Body);
				    
				    $eeOutput .= '
				    
				    eeSFL_FormData.append("eeSFL_Timestamp", "' . $eeSFL_Timestamp . '");
				     
				    eeSFL_FormData.append("eeSFL_Token", "' . $eeSFL_TimestampMD5 . '");
				    
				    eeSFL_FormData.append("eeSFL_ID", "' . $eeSFL_ID . '");
				    
	 			    eeSFL_FormData.append("eeSFL_Body", ' . $eeSFL_Body . ');
				        
				    eeSFL_Xhr.send(eeSFL_FormData);
				    
				}
				
				';
				
				$eeOutput .= 'eeSFL_Notification();'; // Run the above function right now
				
				$eeOutput .= '
				
			</script>';
			
			return $eeOutput;
		}
	}
	
	
	
	
	// Sanitize Email Addresses
	public function eeSFL_SanitizeEmailString($eeAddresses) { // Can be one or more addresses, comma deliniated
		
		global $eeSFL_Log;
		$eeAddressSanitized = '';
		
		if(strpos($eeAddresses, ',')) { // Multiple Addresses
		
			$eeSFL_Addresses = explode(',', $eeAddresses);
			
			$eeSFL_AddressesString = '';
			
			foreach($eeSFL_Addresses as $add){
				
				$add = trim($add);
				
				if(filter_var($add, FILTER_VALIDATE_EMAIL)) {
			
					$eeSFL_AddressesString .= $add . ',';
					
				} else {
					$eeSFL_Log['errors'][] = $add . ' - ' . __('This is not a valid email address.', 'ee-simple-file-list');
				}
			}
			
			$eeAddressSanitized = substr($eeSFL_AddressesString, 0, -1); // Remove last comma
			
		
		} elseif(filter_var($eeAddresses, FILTER_SANITIZE_EMAIL)) { // Only one address
			
			$add = $eeAddresses;
			
			if(filter_var($add, FILTER_VALIDATE_EMAIL)) {
				
				$eeAddressSanitized = $add;
				
			} else {
				
				$eeSFL_Log['errors'][] = $add . ' - ' . __('This is not a valid email address.', 'ee-simple-file-list');
			}
			
		} else {
			
			$eeAddressSanitized = ''; // Anything but a good email gets null.
		}
		
		return $eeAddressSanitized;
	}
	
	
	
	
	
	
	// Upload Info Form Display
	public function eeSFL_UploadInfoForm() {
		
		$eeName = '';
		$eeEmail = '';
		
		$wpUserObj = wp_get_current_user();
		
		if($wpUserObj) {
			$eeName = $wpUserObj->first_name . ' ' . $wpUserObj->last_name;
			$eeEmail = $wpUserObj->user_email;
		}
		
		$eeOutput = '<div id="eeUploadInfoForm">';
		
			if(!$eeEmail) {
				
				$eeOutput .= '<label for="eeSFL_Name">' . __('Name', 'ee-simple-file-list') . ':</label>
			
				<input required="required" type="text" name="eeSFL_Name" value="" id="eeSFL_Name" size="64" maxlength="64" /> 
			
				<label for="eeSFL_Email">' . __('Email', 'ee-simple-file-list') . ':</label>
				<input required="required" type="email" name="eeSFL_Email" value="" id="eeSFL_Email" size="64" maxlength="128" />';
			
			} else {
				
				$eeOutput .= '<input type="hidden" name="eeSFL_Name" value="' . $eeName . '" />
								<input type="hidden" name="eeSFL_Email" value="' . $eeEmail . '" />';
				
			}
			
			$eeOutput .= '<label for="eeSFL_Comments">' . __('Description', 'ee-simple-file-list') . ':</label>
			<textarea required="required" name="eeSFL_Comments" id="eeSFL_Comments" rows="5" cols="64" maxlength="5012"></textarea></div>';
			
		return $eeOutput;
	
	}
		
	
	
	public function eeSFL_WriteLogData($eeSFL_ThisLog) {
		
		$eeDate = date('Y-m-d:h:m');
		
		$eeLogNow = get_option('eeSFL-Log'); // Stored as an array
		
		// Log Size Management
		$eeSizeCheck = serialize($eeLogNow);
		if(strlen($eeSizeCheck) > 65535) { // Using TEXT Limit, even tho options are LONGTEXT.
			$eeLogNow = array(); // Clear
		}
		
		$eeLogNow[$eeDate][] = $eeSFL_ThisLog;
		
		update_option('eeSFL-Log', $eeLogNow, FALSE);
		
		return TRUE;
	}

	
} // END Class 

?>