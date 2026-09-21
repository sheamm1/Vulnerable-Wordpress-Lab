<?php // Simple File List Script: ee-support-functions.php | Author: Mitchell Bennis | support@simplefilelist.com | Revised: 11.23.2019
	
defined( 'ABSPATH' ) or die( 'No direct access is allowed' );

$eeSFL_Log[] = 'Loaded: ee-support-functions';

if(!function_exists('eeProcessSupportPost')) {

	function eeProcessSupportPost($eeArray) {
		     
	    global $eeContact_From;
	    
	    $eeContact_Body = '';
	     
	    $eeIgnore = array('eeSupportForm', 'ee-support-form-nonce', '_wp_http_referer');
	    
	    if(is_array($eeArray)) {
	     
		    foreach ($eeArray as $eeKey => $eeValue) {
		        
		        if(!in_array($eeKey, $eeIgnore) AND $eeValue) {
						
					$eeValue = filter_var($eeValue, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
					
					$eeKey = str_replace('eeContact_', '', $eeKey);
					
					$eeField = eeUnSlug($eeKey);
					
					$eeContact_Body .= $eeField . ': ' . $eeValue . PHP_EOL . PHP_EOL;
				}
		    }
		    
		    if(is_string($eeContact_Body)) {
			    
			    return $eeContact_Body;
		    
		    } else {
		    
		    	return FALSE; // Empty string...
		    	
		    }
	    
	    } else {
		    
		    return FALSE; // Bad POST array...
	    }
	}
}

if(!function_exists('eeMailError')) {
	
	function eeMailError($eeContact_wpError) {
		
		global $eeOutput, $eeContact_name, $eeContact_email, $eeContact_message, $eeContact_link;
		
		// Preserve input data
		$eeContact_name = stripslashes(@$_POST['eeContact_name']);
		$eeContact_email = @$_POST['eeContact_email'];
		$eeContact_message = stripslashes(@$_POST['eeContact_message']);
		$eeContact_link = @$_POST['eeContact_link'];
		
		$eeOutput .= '<div class="error"><p>' . __('The message failed to send.', 'ee-simple-file-list') . '</p>';
		
		// $eeOutput .= "<pre>" . print_r($eeContact_wpError, TRUE) . "</pre></div>";
		
		return error_log(print_r($eeContact_wpError, true));
	}
}

if(!function_exists('eeUnSlug')) {
	
	function eeUnSlug($eeSlug) {
	   
	   $eeSlug = str_replace('eeContact_', '', $eeSlug);
	   $eeSlug = strtolower($eeSlug);
	   $eeSlug = str_replace('-', ' ', $eeSlug);
	   $eeSlug = str_replace('_', ' ', $eeSlug);
	   $eeSlug = ucwords($eeSlug);
	   return $eeSlug;
	} 
}
	
?>