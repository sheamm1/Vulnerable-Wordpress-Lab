<?php
	
// Tie into Wordpress
$wordpress = getcwd() . '/../../../../wp-load.php';
$wordpress = realpath($wordpress);

if(is_file($wordpress)) { 
	include($wordpress); // Get all that wonderfullness
} else {
	trigger_error("No Wordpress", E_USER_ERROR);
	exit;
}

if($eeSFLA) {

	$eeSFLA_Nonce = wp_create_nonce('eeSFLA');
	include(WP_PLUGIN_DIR . '/ee-simple-file-list-access/includes/eeSFLA_ViewManager.php');

}
	
?>