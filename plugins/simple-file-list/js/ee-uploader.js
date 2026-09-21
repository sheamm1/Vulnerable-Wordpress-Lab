// Simple File List Script: ee-uploader.js | Author: Mitchell Bennis | support@simplefilelist.com | Revised: 12.22.2019



console.log("ee-upload.js | ver 3.1");


var eeSFL_FileSet = new Array(); // Names
var eeSFL_FileObjects = new Array(); // File objects
var eeSFL_FileCount = 0; // How many to upload
var eeSFL_Uploaded = 0; // How many have uploaded
var eeSFL_Error = false; // Bad things have happened
var eeSFL_FormatsArray = eeSFL_FileFormats.split(","); // An array of the things.




// Receive files from input[type=file]
function eeSFL_FileInputHandler(eeEvent) {
	
	console.log("File Added via Input");
	
	var eeSFL_Files = document.getElementById("eeSFL_FileInput").files;
	
	eeSFL_ProcessFileInput(eeSFL_Files);

}

// Receive files from the drop zone
function eeSFL_DropHandler(eeEvent) {
  
  	console.log('File Added via Drop Zone');

	// Prevent default behavior (Prevent file from being opened)
	eeEvent.preventDefault();
	  
	eeSFL_ProcessFileInput(eeEvent.dataTransfer.files); // The file object
}

// Prevent file from being opened in the browser window
function eeSFL_DragOverHandler(eeEvent) {
	eeEvent.preventDefault();
}





	
// Check the files and prepare for upload
function eeSFL_ProcessFileInput(eeSFL_Files) { // The files object
    
    // Make sure it's not too many
    if(eeSFL_Files.length > eeSFL_FileLimit) { // If so, reset
	    
	    eeSFL_Error = false;
	    eeSFL_File = false;
	    jQuery("#eeSFL_FileInput").val("");
	    alert("Upload Limit: " + eeSFL_FileLimit);
	    return false;   
	}
    
    // Loop through file object
    for(var i = 0; i < eeSFL_Files.length; i++){
        
        var eeSFL_File =  eeSFL_Files[i];
        
        console.group("File # " + i);
	        
	        console.log("Name: " + eeSFL_File.name);
	        
	        // Validation
	        
	        // Size
	        console.log("Size: " + eeSFL_File.size);
	        
	        if(eeSFL_File.size > eeSFL_UploadMaxFileSize) {
		        eeSFL_Error = eeSFL_File.name + " is too large to upload.";
	        }
	        
	        // Type
	        var eeSFL_Extension = eeSFL_File.name.split(".").pop();
	        eeSFL_Extension = eeSFL_Extension.toLowerCase();
	        
	        if(eeSFL_FormatsArray.indexOf(eeSFL_Extension) == -1) {
		        eeSFL_Error = "This file type (" + eeSFL_Extension + ") is not allowed.";
	        }
	        
	        console.log("Extension: " + eeSFL_Extension);
	        console.log("Type: " + eeSFL_File.type);
	        
	        // Modified date
	        console.log("Date: " + eeSFL_File.lastModified);
        
        console.groupEnd();
        
        if(!eeSFL_Error) { // If no errors
        	
			eeSFL_FileObjects.push(eeSFL_File); // Add this object
			eeSFL_FileSet.push(eeSFL_File.name); // Add this name for the post-processor 
			
        } else {
	        
	        alert(eeSFL_Error); // Alert the user.
	        
	        eeSFL_Error = false;
	        eeSFL_File = false;
	        jQuery("#eeSFL_FileInput").val("");
	        return false;
        } 
    }
    
    
    eeSFL_FileCount = eeSFL_FileObjects.length; // Reset based on set
    var eeSFL_FileQstring = JSON.stringify(eeSFL_FileSet);
            
    jQuery("#eeSFL_FileList").val(eeSFL_FileQstring); // Set the hidden inputs
	jQuery("#eeSFL_FileCount").val(eeSFL_FileCount); // The number of files
	
	
	jQuery('#eeSFL_FileInput').hide();
	jQuery('#eeSFL_FilesDrug').text(eeSFL_FileCount + ' files selected.');
	jQuery('#eeSFL_FilesDrug').show();
    
    // Helpful information
    console.log("#eeSFL_FileList  Set: " + eeSFL_FileQstring);
	console.log("#eeSFL_FileCount Set: " + eeSFL_FileCount);
    console.log("Files: " + eeSFL_FileSet);
    console.log("Count: " + eeSFL_FileCount);
    
}




// The Upload Queue Processor
function eeUploadProcessor(eeSFL_FileObjects) {
	
	eeSFL_FileCount = eeSFL_FileObjects.length;
	
	if(eeSFL_FileCount) {
		
		// Remove button and replace with progress bar
	    jQuery("#eeSFL_UploadGo" ).hide();
	
		console.log("Uploading " + eeSFL_FileCount + " files...");
		
		for (var i = 0; i < eeSFL_FileCount; i++) { // Loop through and upload the files
			
			console.log("Processing File: " + eeSFL_FileObjects[i].name);
						            
            eeUploadFile(eeSFL_FileObjects[i]); // Upload the file using the function below...
		}
	}		
}



// File Upload AJAX Call
function eeUploadFile(eeSFL_File) { // Pass in file object
    
    var eeXhr = new XMLHttpRequest();
    
    if(eeXhr.upload) { // Upload progress
			
		jQuery('#eeSFL_UploadProgress').css('display', 'block');
	    
	    console.log('Upload in progress ...');
	    
	    eeXhr.upload.addEventListener("progress", function(e) {
		    
			var percent = parseInt((e.loaded / e.total * 100)); // Percent completed
			console.log('Upload Progress: ' + percent + '%' );
			
			// Progress Bar
			if(percent < 100) {
				jQuery('#eeSFL_UploadProgress').css('width', percent + '%'); // Width based on percent
			} else {
				jQuery('#eeSFL_UploadProgress').css('width', '100%');
			}
			
		}, false);
	}
	
	
	// Add our form data
	var eeFormData = new FormData();
    
    console.log("Uploading: " + eeSFL_File.name);
    console.log("Calling Engine: " + eeSFL_UploadEngineURL);
    
    eeXhr.open("POST", eeSFL_UploadEngineURL, true); // URL set in ee-upload-form.php
    
    eeXhr.onreadystatechange = function() {
        
        if (eeXhr.readyState == 4 && eeXhr.status != 500) { // && eeXhr.status == 200 <-- Windows returns 404?
	        
	        eeSFL_Uploaded ++;
            
            console.log("File Uploaded (" + eeSFL_Uploaded + " of " + eeSFL_FileCount + ")");
            
			// Every thing ok, file uploaded
            console.log("RESPONSE: " + eeXhr.responseText); // handle response.
            
            // Submit the Form
            if(eeSFL_Uploaded == eeSFL_FileCount) {
	            
	            if(eeXhr.responseText == "SUCCESS") {
				
					jQuery('#eeSFL_UploadProgress em').fadeIn(); // Show "Processing the Upload" message
	            
	            	console.log("--->>> SUBMITTING FORM ...");
	            	
	            	document.forms.eeSFL_UploadForm.submit(); // SUCCESS - Process the Form <<<----- FORM SUBMIT
					
		        } else {
			    	console.log("XHR Status: " + eeXhr.status);
			    	console.log("XHR State: " + eeXhr.readyState);
			    	
			    	var n = eeXhr.responseText.search("<"); // Error condition
			    	if(n === 0) {
				    	alert("Upload Error: " + eeSFL_File.name);
				    	jQuery( "#eeUploadingNow" ).fadeOut();
				    }
				    return false;
		        }
		        
	        } else { // Not the last file, so reset the progress bar
		        
		        jQuery('#eeSFL_UploadProgress').css('width', '0%'); // Reset the progress bar
	        }
        
        } else {
	    	console.log("XHR Status: " + eeXhr.status);
	    	console.log("XHR State: " + eeXhr.readyState);
	    	return false;
        }
    };
    
    // Pass to the Upload Engine...
    eeFormData.append("file", eeSFL_File);
    
    // These values are set in ee-upload-form.php
    eeFormData.append("eeSFL_ID", eeSFL_ListID);
    eeFormData.append("eeSFL_FileUploadDir", eeSFL_FileUploadDir);
    eeFormData.append("eeSFL_Timestamp", eeSFL_TimeStamp); 
    eeFormData.append("eeSFL_Token", eeSFL_TimeStampMD5);
        
    // Send the AJAX request...
    eeXhr.send(eeFormData);
}


console.log("Waiting for files...");

// Populate the action attribute in the form
var eeSFL_CurrentURL = document.location.href;
jQuery("#eeSFL_UploadForm").attr("action", eeSFL_CurrentURL);


