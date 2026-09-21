<?php // Simple File List Script: ee-list-display-settings.php | Author: Mitchell Bennis | support@simplefilelist.com | Revised: 11.23.2019
	
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! wp_verify_nonce( $eeSFL_Nonce, 'eeInclude' ) ) exit('ERROR 98'); // Exit if nonce fails

$eeSFL_Log[] = 'Loading List Settings Page ...';

// Check for POST and Nonce
if(@$_POST['eePost'] AND check_admin_referer( 'ee-simple-file-list-settings', 'ee-simple-file-list-settings-nonce')) {
	
	// Get all the settings
	$eeSettings = get_option('eeSFL-Settings');

	// YES/NO Checkboxes
	$eeCheckboxes = array(
		'AllowFrontManage'
		,'ShowFileDescription'
		,'ShowFileActions'
		,'ShowHeader'
		,'ShowUploadLimits'
		,'ShowSubmitterInfo'
		,'GetUploaderInfo'
		,'PreserveSpaces'
		,'ShowFileExtension'
		,'AllowFrontSend'
	);
	foreach( $eeCheckboxes as $eeTerm){
		$eeSettings[$eeSFL_ID][$eeTerm] = eeSFL_ProcessCheckboxInput($eeTerm);
	}
	
	// Extension Processing
	if($eeSFLF) {
		$eeSFLF_Nonce = wp_create_nonce('eeSFLF_Include'); // Security
		include_once(WP_PLUGIN_DIR . '/ee-simple-file-list-folders/includes/eeSFLF_ListSettingsProcess.php');
	}
	
	if($eeSFLS) {
		$eeSFLS_Nonce = wp_create_nonce('eeSFLS_Include'); // Security
		include_once(WP_PLUGIN_DIR . '/ee-simple-file-list-search/includes/ee-pagination-settings-process.php');
	}
	
	
	// Update the array with new values
	$eeSFL_Config = $eeSettings[$eeSFL_ID];
	
	// Update DB
	update_option('eeSFL-Settings', $eeSettings );
	
	$eeSFL_Confirm = __('List Settings Saved', 'ee-simple-file-list');
}

// Settings Display =========================================
	
$eeOutput .= '<div class="eeSFL_Admin">';
	
if(@$eeSFL_Log['errors']) { 
	$eeOutput .=  eeSFL_ResultsDisplay($eeSFL_Log['errors'], 'notice-error');
} elseif(@$eeSFL_Confirm) { 
	$eeOutput .=  eeSFL_ResultsDisplay($eeSFL_Confirm, 'notice-success');
}

// Begin the Form	
$eeOutput .= '

<form action="' . $_SERVER['PHP_SELF'] . '?page=' . $eeSFL->eePluginSlug . '&tab=settings&subtab=display_settings" method="post" id="eeSFL_Settings">
		
		<p class="eeSettingsRight"><a class="eeInstructionsLink" href="https://simplefilelist.com/" target="_blank">' . __('Instructions', 'ee-simple-file-list') . '</a>
		<input type="submit" name="submit" value="' . __('SAVE', 'ee-simple-file-list') . '" class="button eeSFL_Save" /></p>
		
		<h2>' . __('Front-Side Settings', 'ee-simple-file-list') . '</h2>
		
		<input type="hidden" name="eePost" value="TRUE" />
		<input type="hidden" name="eeListID" value="' . $eeSFL_ID . '" />';	
		
		$eeOutput .= wp_nonce_field( 'ee-simple-file-list-settings', 'ee-simple-file-list-settings-nonce', TRUE, FALSE);
		
		$eeOutput .= '
		
		<fieldset>';
		
		if($eeSFLF) {
			
			$eeSFLF_Nonce = wp_create_nonce('eeSFLF_Include'); // Security
			include_once(WP_PLUGIN_DIR . '/ee-simple-file-list-folders/includes/eeSFLF_ListSettings.php');
		}
		
		if($eeSFLS) {
			
			$eeSFLS_Nonce = wp_create_nonce('eeSFLS_Include'); // Security
			include_once(WP_PLUGIN_DIR . '/ee-simple-file-list-search/includes/ee-pagination-settings.php');
		}
			
		$eeOutput .= '<h3>' . __('Appearance', 'ee-simple-file-list') . '</h3>
		
		
		<label for="eePreserveSpaces">' . __('Preserve Spaces', 'ee-simple-file-list') . ':</label>
		<input type="checkbox" name="eePreserveSpaces" value="YES" id="eePreserveSpaces"';
		
		if( $eeSFL_Config['PreserveSpaces'] == 'YES') { $eeOutput .= ' checked="checked"'; }
		
		$eeOutput .= ' /> <p>' . __('File Name Spaces', 'ee-simple-file-list') . '</p>
		
		<div class="eeNote">' . __('Spaces in file names are replaced with hyphens in order to make the URL legal.', 'ee-simple-file-list') . '<br />' . 
			__('This setting will revert this action for display.', 'ee-simple-file-list') . '</div>
		
		<br class="eeClearFix" />	
		
		
		<label for="eeShowFileDescription">' . __('Show File Description', 'ee-simple-file-list') . ':</label>
		<input type="checkbox" name="eeShowFileDescription" value="YES" id="eeShowFileDescription"';
		
		if( $eeSFL_Config['ShowFileDescription'] == 'YES') { $eeOutput .= ' checked="checked"'; }
		
		$eeOutput .= ' /> <p>' . __('Description of the file', 'ee-simple-file-list') . '</p>
		
		<div class="eeNote">' . __('Display the file description below the file name.', 'ee-simple-file-list') . '</div>
		
		<br class="eeClearFix" />		
		
		
		<label for="eeShowFileActions">' . __('Show File Actions', 'ee-simple-file-list') . ':</label>
		<input type="checkbox" name="eeShowFileActions" value="YES" id="eeShowFileActions"';
		
		if( $eeSFL_Config['ShowFileActions'] == 'YES') { $eeOutput .= ' checked="checked"'; }
		
		$eeOutput .= ' /> <p>' . __('Open | Download', 'ee-simple-file-list') . '</p>
		
		<div class="eeNote">' . __('Show file action links below each file name on the front-side list', 'ee-simple-file-list') . '</div>
		
		<br class="eeClearFix" />
		
		
		<label for="eeShowFileExtension">' . __('Show Extension', 'ee-simple-file-list') . ':</label>
		<input type="checkbox" name="eeShowFileExtension" value="YES" id="eeShowFileExtension"';
		
		if( $eeSFL_Config['ShowFileExtension'] == 'YES') { $eeOutput .= ' checked="checked"'; }
		
		$eeOutput .= ' /> <p>' . __('File Type', 'ee-simple-file-list') . '</p>
		
		<div class="eeNote">' . __('Show or hide the file extension.', 'ee-simple-file-list') . '</div>
		
		<br class="eeClearFix" />		
		
		
		<label for="eeShowListHeader">' . __('Show Header', 'ee-simple-file-list') . ':</label>
		<input type="checkbox" name="eeShowHeader" value="YES" id="eeShowListHeader"';
		
		if( $eeSFL_Config['ShowHeader'] == 'YES') { $eeOutput .= ' checked="checked"'; }
		
		$eeOutput .= ' /> <p>' . __('Show the table header', 'ee-simple-file-list') . '</p>
		
		<div class="eeNote">' . __('Show the table header above the file list or not.', 'ee-simple-file-list') . '</div>
		
		<br class="eeClearFix" />		
		
		
		<label for="eeShowUploadLimits">' . __('Show Upload Limits', 'ee-simple-file-list') . ':</label>
		<input type="checkbox" name="eeShowUploadLimits" value="YES" id="eeShowUploadLimits"';
		
		if( $eeSFL_Config['ShowUploadLimits'] == 'YES') { $eeOutput .= ' checked="checked"'; }
		
		$eeOutput .= ' /> <p>' . __('Show the upload limitations', 'ee-simple-file-list') . '</p>
		
		<div class="eeNote">' . __('Show the user file size, number and file type restrictions.', 'ee-simple-file-list') . '</div>
		
		<br class="eeClearFix" />
		
		
		<h2>' . __('Functionality', 'ee-simple-file-list') . '</h2>
		
		<label for="eeGetUploaderInfo">' . __('Get Submitter Information', 'ee-simple-file-list') . ':</label>
		<input type="checkbox" name="eeGetUploaderInfo" value="YES" id="eeGetUploaderInfo"';
		
		if( $eeSFL_Config['GetUploaderInfo'] == 'YES') { $eeOutput .= ' checked="checked"'; }
		
		$eeOutput .= ' /> <p>' . __('Get name, email and description', 'ee-simple-file-list') . '</p>
		
		<div class="eeNote">' . __('Display a form which must be filled out before a file is uploaded.', 'ee-simple-file-list') . '<br />
			' . __('Submissions are included within the upload notification email and added to the file details.', 'ee-simple-file-list') . '</div>
		
		<br class="eeClearFix" />		
		
		
		<label for="eeShowSubmitterInfo">' . __('Show Submitter Info', 'ee-simple-file-list') . ':</label>
		<input type="checkbox" name="eeShowSubmitterInfo" value="YES" id="eeShowSubmitterInfo"';
		
		if( $eeSFL_Config['ShowSubmitterInfo'] == 'YES') { $eeOutput .= ' checked="checked"'; }
		
		$eeOutput .= ' /> <p>' . __('Show on Front-side', 'ee-simple-file-list') . '</p>
		
		<div class="eeNote">' . __('Show the file submitters information on the website.', 'ee-simple-file-list') . '</div>
		
		<br class="eeClearFix" />
		
		
		<label for="eeAllowFrontSend">' . __('Allow File Sending', 'ee-simple-file-list') . ':</label>
		<input type="checkbox" name="eeAllowFrontSend" value="YES" id="eeAllowFrontSend"';
		
		if( $eeSFL_Config['AllowFrontSend'] == 'YES') { $eeOutput .= ' checked="checked"'; }
		
		$eeOutput .= ' /> <p>' . __('Send via Email', 'ee-simple-file-list') . '</p>
		
		<div class="eeNote">' . __('Allow front-side users to email links to files.', 'ee-simple-file-list') . '</div>
		
		<br class="eeClearFix" />';
		
		if(!$eeSFLA) {
		
			$eeOutput .= '<label for="eeAllowFrontManage">' . __('Front-Side Manage', 'ee-simple-file-list') . ':</label>
			<input type="checkbox" name="eeAllowFrontManage" value="YES" id="eeAllowFrontManage"';
			
			if( $eeSFL_Config['AllowFrontManage'] == 'YES') { $eeOutput .= ' checked="checked"'; }
			
			$eeOutput .= ' /> <p>' . __('Use with Caution', 'ee-simple-file-list') . '</p>
							
			<div class="eeNote">' . __('Allow file editing and deletion on the front side of the site.', 'ee-simple-file-list') . '</div>
			
			<br class="eeClearFix" />';
		
		}
		
		$eeOutput .= '<input type="submit" name="submit" value="' . __('SAVE', 'ee-simple-file-list') . '" class="button eeSFL_Save" />
		
		</fieldset>
		
	</form>
	
</div>';
	
?>