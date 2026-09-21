<?php // Simple File List Script: ee-email-engine.php | Author: Mitchell Bennis | support@simplefilelist.com | Revised: 12.23.2019
	
// This script is accessed via AJAX from eeSFL_AjaxEmail() on ee-class.php
	
// Write problems to error log file
ini_set("log_errors", 1);
error_reporting (E_ALL);
ini_set ('display_errors', FALSE);
ini_set("error_log", "logs/ee-email-error.log");

if(@$_POST['eeSFL_Body']) { 
	
	$eeSFL_Body = $_POST['eeSFL_Body'];
	
	// $eeSFL_Body = json_decode($eeSFL_Body);
	$eeSFL_Body = filter_var($eeSFL_Body, FILTER_SANITIZE_STRING);
	$eeSFL_Body = strip_tags($eeSFL_Body);
	$eeSFL_Body = htmlspecialchars_decode($eeSFL_Body, ENT_QUOTES);
	$eeSFL_Body = strip_tags($eeSFL_Body);
	$eeSFL_Body = stripslashes($eeSFL_Body);
	
	if(!$eeSFL_Body) {
		trigger_error("eeSFL_Body is NULL", E_USER_ERROR);
		exit;
	}
	
	// List ID
	$eeSFL_ID = filter_var($_POST['eeSFL_ID'], FILTER_VALIDATE_INT);

} else {  
	
	trigger_error("Missing eeSFL_Body", E_USER_ERROR);
	exit;
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
	
	if( !check_ajax_referer( 'ee-simple-file-list-email', 'ee-simple-file-list-email-nonce', FALSE ) ) {
		trigger_error("WP AJAX Error", E_USER_ERROR);
		exit;
	}	
}
add_action( 'plugins_loaded', 'eeSFL_CheckNonce' );

$verifyToken = md5('eeSFL_' . $_POST['eeSFL_Timestamp']);
	
if($_POST['eeSFL_Token'] == $verifyToken) { // Security

	if($eeSFL_Config['NotifyFrom']) {
		$eeSFL_NotifyFrom = $eeSFL_Config['NotifyFrom'];
	} else {
		$eeSFL_NotifyFrom = get_option('admin_email');
	}
	
	if($eeSFL_Config['NotifyFromName']) {
		$eeSFL_AdminName = $eeSFL_Config['NotifyFromName'];
	} else {
		$eeSFL_AdminName = $eeSFL->eePluginName;
	}
	
	$eeTo = $eeSFL_Config['NotifyTo'];
	
	$eeSFL_Headers = "From: " . stripslashes( $eeSFL_Config['NotifyFromName'] ) . " <$eeSFL_NotifyFrom>" . PHP_EOL . 
		"Return-Path: $eeSFL_NotifyFrom" . PHP_EOL . "Reply-To: $eeSFL_NotifyFrom";
	
	if($eeSFL_Config['NotifyCc']) {
		$eeSFL_Headers .= PHP_EOL . "CC:" . $eeSFL_Config['NotifyCc'];
	}
		
	if($eeSFL_Config['NotifyBcc']) {
		$eeSFL_Headers .= PHP_EOL . "BCC:" . $eeSFL_Config['NotifyBcc'];
	}
	
	if($eeSFL_Config['NotifySubject']) {
		
		$eeSFL_Subject = stripslashes( $eeSFL_Config['NotifySubject'] );
	} else {
		$eeSFL_Subject = __('File Upload Notice', 'ee-simple-file-list');
	}
		
	if(strpos($eeTo, '@') ) {
		if(!wp_mail($eeTo, $eeSFL_Subject, $eeSFL_Body, $eeSFL_Headers)) { // Email Notice
			trigger_error('Mail Did Not Send to ' . $eeEmail, E_USER_ERROR);
			exit;
		}
	} else {
		trigger_error("Missing eeSFL_Config['NotifyTo']", E_USER_ERROR);
		exit;
	}
	
	exit('SENT');
	
} else {
	trigger_error('Token Failure', E_USER_ERROR);
}
	
?>