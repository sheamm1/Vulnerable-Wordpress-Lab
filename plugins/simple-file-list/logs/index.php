<?php // Simple File List Support - support@simplefilelist.com - Rev 12.29.19 
	
// Accessed via http://website.com/wp-content/plugins/simple-file-list/logs/index.php?eePIN=2006

// This script emails me plugin configuration settings, errors and important environment information.
// No file information is included.
// The point of this script is to allow me, Simple File List Support, and no one else, to access basic info and error data.
// I believe in good service and support.

// Must have the proper PIN in order to access the page
$eePIN = filter_var(@$_GET['eePIN'], FILTER_VALIDATE_INT);

// Must also come from Me
$eeReferer = filter_var($_SERVER['HTTP_REFERER'], FILTER_SANITIZE_STRING);
$eeRefererMust = 'simplefilelist.com/support';

$eeTo = 'support@simplefilelist.com';

if($eePIN == 2006 AND strpos($eeReferer, $eeRefererMust) ) { // PIN and Referer must match
		
	// Attempt to turn on basic PHP logging...
	ini_set('display_errors', TRUE);
	error_reporting(E_ALL);
	
	// Tie into Wordpress
	$wordpress = getcwd() . '/../../../../wp-load.php'; // Starting at this plugin's home dir
	$wordpress = realpath($wordpress);
	
	if(is_file($wordpress)) { 
		include($wordpress); // Get all that wonderfullness
	} else {
		exit("No Wordpress");
	}
	
	// Get what's in this address bar
	$eeProtocol = strtolower($_SERVER['SERVER_PROTOCOL']);
	if(strpos($eeProtocol, 'ttps') == 1) { $eeProtocol = 'https'; } else { $eeProtocol = 'http'; }
	$eeHost = $_SERVER['HTTP_HOST'];
	
	// Add the rest...
	$eeThisWP = $eeProtocol . '://' . $eeHost;
	$eeThisURL = $eeThisWP . '/wp-content/plugins/simple-file-list/logs/';
	
	// My log file names - these are for the AJAX accessed files
	$eeFileLog = 'ee-file-error.log';
	$eeUploadLog = 'ee-upload-error.log';
	$eeEmailLog = 'ee-email-error.log';
	
	// Read My Error Log Files
	$eeFileLogContent = @file_get_contents($eeThisURL . $eeFileLog);
	$eeUploadLogContent = @file_get_contents($eeThisURL . $eeUploadLog);
	$eeEmailLogContent = @file_get_contents($eeThisURL . $eeEmailLog);
	
	// Tie into Wordpress
	define('WP_USE_THEMES', false);
	$wordpress = getcwd() . '/../../../../wp-blog-header.php';
	require($wordpress);
	
	// Get Database Log
	$eeLogFile = get_option('eeSFL-Log'); // Used below
	
	// PHP Log
	$eeLog = $_SERVER['DOCUMENT_ROOT'] . '/error_log';
	if(@filesize($eeLog) > 10) {
		$phpErrors = @file_get_contents($eeLog);
	} else {
		$phpErrors = FALSE;
	}
	
	// Wordpress Log
	$eeLog = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/debug.log';
	if(is_readable($eeLog)) {
		$wpErrors = @file_get_contents($eeLog);
	} else {
		$wpErrors = FALSE;
	}
	
	
	// Send Email
	$eeFrom = 'FROM: SFL Report <wordpress@' . $eeHost . '>';
	$eeSubject = 'Simple File List - Site Report';
	
	$eeBody = $eeSubject . ' - ' . $eeHost . PHP_EOL . PHP_EOL;
	
	if($eeFileLogContent) {
		$eeBody .= 'FILE ERRORS' . PHP_EOL . '------------------' . PHP_EOL . $eeFileLogContent . PHP_EOL . PHP_EOL . PHP_EOL;
	}
	
	if($eeUploadLogContent) {
		$eeBody .= 'UPLOAD ERRORS' . PHP_EOL . '------------------' . PHP_EOL . $eeUploadLogContent . PHP_EOL . PHP_EOL . PHP_EOL;
	}
	
	if($eeEmailLogContent) {
		$eeBody .= 'EMAIL ERRORS' . PHP_EOL . '------------------' . PHP_EOL . $eeEmailLogContent . PHP_EOL . PHP_EOL . PHP_EOL;
	}
	
	if($phpErrors) {
		$eeBody .= 'PHP ERRORS' . PHP_EOL . '------------------' . PHP_EOL . $phpErrors . PHP_EOL . PHP_EOL . PHP_EOL;
	}
	
	if($wpErrors) {
		$eeBody .= 'WORDPRESS ERRORS' . PHP_EOL . '------------------' . PHP_EOL . $wpErrors . PHP_EOL . PHP_EOL . PHP_EOL;
	}
	
	$eeBody .= 'RECENT LOG' . PHP_EOL . '------------------' . PHP_EOL . 
	
	$eeBody .= print_r($eeLogFile, TRUE);
	
	$eeBody .=  PHP_EOL . PHP_EOL;
	
	if( wp_mail($eeTo, $eeSubject, $eeBody, $eeFrom) ) {
		$eeMsg = ':-)';
	} else {
		$eeMsg = ':-(';
	}

	exit($eeMsg);
	
}
		
?>