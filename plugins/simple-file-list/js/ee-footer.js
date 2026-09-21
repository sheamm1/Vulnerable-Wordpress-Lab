// Simple File List Script: ee-footer.js | Author: Mitchell Bennis | support@simplefilelist.com | Revised: 12.22.2019

// Used in front-side and back-side file list display

console.log('ee-footer.js Loaded');

// Upon page load completion...
jQuery(document).ready(function($) {	

	console.log('eeSFL Document Ready');
	
	window.addEventListener('touchstart', function() {
		eeSFL_isTouchscreen = true;
	});	

}); // END Ready Function





function eeSFL_EditFile(eeSFL_FileID) {
	
	event.preventDefault(); // Don't follow the link
	
	if( jQuery('#eeSFL_EditFileWrap_' + eeSFL_FileID).is(':visible') ) {
		
		jQuery('#eeSFL_EditFileWrap_' + eeSFL_FileID).slideUp();
		jQuery('#eeSFL_EditFile_' + eeSFL_FileID).text('Edit');
	
	} else {
		
		jQuery('#eeSFL_EditFileWrap_' + eeSFL_FileID).slideDown();
		jQuery('#eeSFL_EditFile_' + eeSFL_FileID).text('Cancel');
	}
}




function eeSFL_EditSave(eeSFL_FileID) {
	
	var eeRenaming = false;
	
	var eeName1 = jQuery('#eeSFL_RowID-' + eeSFL_FileID + ' p.eeSFL_FileLink').text(); // Current File Name
	var eeName2 = jQuery('#eeSFL_RowID-' + eeSFL_FileID + ' input.eeNewFileName').val(); // New File Name
	
	if(eeName1 != eeName2) { // If no match, we rename
		
		eeRenaming = true;
		eeSFL_FileAction(eeSFL_FileID, 'Rename');
	}
	
	var eeDesc1 = jQuery('#eeSFL_RowID-' + eeSFL_FileID + ' span.eeSFL_SavedDesc').text(); // Current Desc
	var eeDesc2 = jQuery('#eeSFL_RowID-' + eeSFL_FileID + ' input.eeSFL_NewFileDesc').val(); // New Desc
	
	if(eeDesc1 != eeDesc2) { // If no match, we update
		
		if(eeRenaming) { confirm('Update Description Too?'); }
		
		eeSFL_FileAction(eeSFL_FileID, 'UpdateDesc');
	}
	
	jQuery('#eeSFL_EditFileWrap_' + eeSFL_FileID).slideUp();
	jQuery('#eeSFL_EditFile_' + eeSFL_FileID).text('Edit');
}






// Triggered when you click the Delete link
function eeSFL_Delete(eeSFL_FileID) {
	
	event.preventDefault(); // Don't follow the link
	
	console.log('Deleting File ID #' + eeSFL_FileID);
	
	// Get the File Name
    var eeSFL_FileName = jQuery('#eeSFL_RowID-' + eeSFL_FileID + ' .eeSFL_RealFileName').text();
    
    console.log(eeSFL_FileName);
	
	if( confirm("Confirm Delete...\r\n" + eeSFL_FileName) ) {
	
		eeSFL_FileAction(eeSFL_FileID, 'Delete');
	
	}

}







function eeSFL_SendFile(eeSFL_FileID) {
	
	event.preventDefault(); // Don't follow the link
    var eeFileName = jQuery('#eeSFL_RowID-' + eeSFL_FileID + ' .eeSFL_RealFileName').text(); // Get the File Name
    jQuery('#eeSFL_SendTheseFilesList em').text(eeFileName); // Add it to the list view
    
    // if(eeSFL_ListFolder) { eeFileName = eeSFL_ListFolder + eeFileName; }
    
    console.log( 'Sending: ' + eeFileName + ' (ID: ' + eeSFL_FileID + ')' );
    
	jQuery('#eeSFL_SendMoreFiles input[type=checkbox]').prop("checked", false); // Uncheck all the boxes
	
	jQuery('.eeSFL_AddFileID_' + eeSFL_FileID + ' input[type=checkbox]').prop("checked", true); // Check the first file's box
	
	jQuery('#eeSFL_SendPop').fadeIn();
}

// Close the Overlay
function eeSFL_Send_Cancel() {
	event.preventDefault();
	document.getElementById("eeSFL_SendFileForm").reset();
	jQuery('#eeSFL_SendPop').fadeOut();
}

// Open the File List
function eeSFL_Send_AddMoreFiles() {
	event.preventDefault();
	jQuery('#eeSFL_SendMoreFiles').show();
	jQuery('#eeSFL_SendInfo').slideUp(); // Make room for the list in the overlay
}

// Cancel the File List
function eeSFL_Send_AddMoreCancel() {
	event.preventDefault();
	jQuery('eeSFL_SendMoreFiles input[type=checkbox]').prop("checked", false); // Uncheck all the boxes
	jQuery('.eeSFL_AddFileID_' + eeSFL_FileID + ' input[type=checkbox]').prop("checked", true); // Check the first file's box
	jQuery('#eeSFL_SendInfo').show();
	jQuery('#eeSFL_SendMoreFiles').slideUp();
}

// Approve Added Files
function eeSFL_Send_AddTheseFiles() {
	
	event.preventDefault();
	var eeArray = new Array;
	
	jQuery('#eeSFL_SendInfo').show();
	jQuery('#eeSFL_SendMoreFiles').slideUp();
	
	// Add each to the list display
	jQuery('#eeSFL_SendTheseFilesList em').text(''); // Reset
	
	jQuery('#eeSFL_SendMoreFiles input[type=checkbox]').each(function() { 
		
		if( jQuery(this).is(':checked') ) {
			
			var eeFileName = decodeURIComponent( jQuery(this).val() ); // Decode
			
			if(eeSFL_ListFolder.length >= 1 ) {
				
				if( eeFileName.indexOf(eeSFL_ListFolder) === 0 ) {
					
					eeFileName = eeFileName.replace(eeSFL_ListFolder, '');
				}
			}
			
			eeArray.push(eeFileName);
		}
	});
	
	var eeArrayLength = eeArray.length;
	var eeSendingThese = '';
	
	for(var i = 0; i < eeArrayLength; i++) {
		if(eeArray[i]) {
			eeSendingThese = eeSendingThese + eeArray[i] + ', ';
		}
	}
	
	// Strip last ,	
	eeSendingThese = eeSendingThese.substring(0, eeSendingThese.length - 2);
	
	jQuery('#eeSFL_SendTheseFilesList em').text(eeSendingThese);
}




// AJAX Post to File Engine
function eeSFL_FileAction(eeSFL_FileID, eeSFL_Action) {
	
	event.preventDefault(); // Don't follow link
	
	console.log(eeSFL_Action + ' -> ' + eeSFL_FileID);
	
	// The File Action Engine
	var eeActionEngine = eeSFL_PluginURL + 'ee-file-engine.php';
	
	
	// AJAX --------------
	
	// Get the Nonce
	var eeSFL_ActionNonce = jQuery('#eeSFL_ActionNonce').text();
	
	if(eeSFL_Action == 'Rename') {
		
		var eeSFL_OldFileName = jQuery('#eeSFL_RowID-' + eeSFL_FileID + ' span.eeSFL_RealFileName').text(); // Current File Name
		var eeSFL_NewFileName = jQuery('#eeSFL_RowID-' + eeSFL_FileID + ' input.eeNewFileName').val();
		
		// Sanitize to match reality
		eeSFL_NewFileName = eeSFL_NewFileName.replace(/ /g, '-'); // Deal with spaces
		eeSFL_NewFileName = eeSFL_NewFileName.replace(/--/g, '-'); // Deal with double dash
		
		if(eeSFL_OldFileName.indexOf('.') == -1) { // It's a folder
			
			eeSFL_NewFileName = eeSFL_NewFileName.replace(/\./g, '_'); // Replace dots
		
		} else { 
			
			if(eeSFL_NewFileName.indexOf('.') == -1) { // Disallow removing extension
				return false;
			}
			
			// Remove dots from name-part
			var eeArray = eeSFL_NewFileName.split('.');
			
			if(eeArray.length > 2) {
				
				console.log('Problem Filename: ' + eeSFL_NewFileName);
				
				var eeExt = eeArray.pop();
				var eeNewFileName = '';
				
				for(i = 0; i < eeArray.length; i++) {
					eeNewFileName += eeArray[i] + '_';
				}
				eeNewFileName = eeNewFileName.substring(0, eeNewFileName.length - 1); // Strip last dash
				
				eeSFL_NewFileName = eeNewFileName + '.' + eeExt;
			}
		}
	
		var eeFormData = {
			'eeSFL_ID': eeSFL_ListID,
			'eeFileOld': eeSFL_OldFileName,
			'eeListFolder': eeSFL_ListFolder,
			'eeFileAction': eeSFL_Action + '|' + eeSFL_NewFileName,
			'eeSecurity': eeSFL_ActionNonce
		};
	
	} else if(eeSFL_Action == 'Delete') {
		
		// Get the File Name
		var eeSFL_FileName = jQuery('#eeSFL_RowID-' + eeSFL_FileID + ' .eeSFL_RealFileName').text();
		
		var eeFormData = {
			'eeSFL_ID': eeSFL_ListID,
			'eeListFolder': eeSFL_ListFolder,
			'eeFileName': eeSFL_FileName,
			'eeFileAction': eeSFL_Action,
			'eeSecurity': eeSFL_ActionNonce
		};
	
	} else if(eeSFL_Action == 'UpdateDesc') {
		
		var eeSFL_NewFileDesc = jQuery('#eeSFL_RowID-' + eeSFL_FileID + ' input.eeSFL_NewFileDesc').val(); // Desc
		
		// alert(eeSFL_FileID + ' -> ' + eeSFL_NewFileDesc);
		
		// return;
		
		var eeFormData = {
			'eeSFL_ID': eeSFL_ListID,
			'eeFileAction': eeSFL_Action,
			'eeFileID': eeSFL_FileID,
			'eeFileDesc': eeSFL_NewFileDesc,
			'eeSecurity': eeSFL_ActionNonce
		};	
	}
	
	if(eeSFL_Action && eeFormData) {

		console.log('Calling: ' + eeActionEngine);

		jQuery.post(eeActionEngine, eeFormData, function(response) {
			
			if(response == 'SUCCESS') {
				
				if(eeSFL_Action == 'Rename') {
					
					jQuery('#eeSFL_RowID-' + eeSFL_FileID + ' .eeNewFileName').val(eeSFL_NewFileName);
					
					jQuery('div.eeSFL_FileRenameEntry').hide();
					
					jQuery('#eeSFL_RowID-' + eeSFL_FileID + ' span.eeSFL_RealFileName').text(eeSFL_NewFileName);
					
					// Make a New Link
					var eeNewLink = '<a class="eeSFL_FileName" href="/' + eeSFL_FileListDir + eeSFL_NewFileName + '">' + eeSFL_NewFileName + '</a>';
					jQuery('#eeSFL_RowID-' + eeSFL_FileID + ' p.eeSFL_FileLink').html(eeNewLink);
					
					// Update the Open link
					jQuery('#eeSFL_RowID-' + eeSFL_FileID + ' a.eeSFL_FileOpen').attr('href', '/' + eeSFL_FileListDir + eeSFL_NewFileName);
					jQuery('#eeSFL_RowID-' + eeSFL_FileID + ' a.eeSFL_FileDownload').attr('href', '/' + eeSFL_FileListDir + eeSFL_NewFileName);
					
				} else if (eeSFL_Action == 'Delete') {
					
					jQuery('#eeSFL_RowID-' + eeSFL_FileID).hide('slow');
					
				} else if(eeSFL_Action == 'UpdateDesc') {
					
					jQuery('#eeSFL_RowID-' + eeSFL_FileID + ' p.eeSFL_FileDesc').show();
					jQuery('#eeSFL_RowID-' + eeSFL_FileID + ' p.eeSFL_FileDesc').html(eeSFL_NewFileDesc);
					
				}
			
			} else {
				alert(response);
			}
		});
	}
}
