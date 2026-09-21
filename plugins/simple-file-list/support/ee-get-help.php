<?php // Simple File List Script: ee-get-help.php | Author: Mitchell Bennis | support@simplefilelist.com | Revised: 11.23.2019

defined( 'ABSPATH' ) or die( 'No direct access is allowed' );
if ( ! wp_verify_nonce( $eeSFL_Nonce, 'eeInclude' ) ) exit('ERROR 98'); // Exit if nonce fails

$eeSFL_Log[] = 'Loaded: ee-plugin-support';
	
// Config		
$eeContact_To = 'support@simplefilelist.com';

// Initialize
$eeContact_name = '';
$eeContact_email = '';
$eeContact_message = '';
$eeContact_link = '';
$eeContact_wpError = '';
$eeContact_Headers = array();
$eeContact_Install = WP_PLUGIN_URL;
		
include('ee-support-functions.php');

// Convert the current plugin slug to a nice name
$eeContact_PluginName = eeUnSlug(str_replace('ee-', '', filter_var($_GET['page'], FILTER_SANITIZE_STRING)));
	
// Form Processor ======================================

if(@$_POST['eeSupportForm']) {
	
	if(@$_POST['eeContact_email'] AND check_admin_referer( 'ee-support-form', 'ee-support-form-nonce')) {
		
		$eeContact_Body = eeProcessSupportPost($_POST); // Process the form
		
		$eeContact_Body .= PHP_EOL . PHP_EOL;
		
		// Add log file
		// $eeSFL_LogArray = get_option('eeSFL-Log');
		// $eeContact_Body .= print_r($eeSFL_LogArray, TRUE);
		
		$eeContact_Body .= 'Plugin Settings: ' . print_r($eeSFL_Config, TRUE) . PHP_EOL . PHP_EOL;
		unset($eeSFL_Env['FileLists']); // Don't want this
		$eeContact_Body .= 'Plugin Environment: ' . print_r($eeSFL_Env, TRUE);
		
		$eeContact_Name = stripslashes( filter_var($_POST['eeContact_name'], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES) );
		$eeContact_From = filter_var($_POST['eeContact_email'], FILTER_VALIDATE_EMAIL);
		
		$eeContact_Headers[] = 'From: ' . $eeContact_Name . ' <wordpress@' . $_SERVER['HTTP_HOST'] . ">";
		$eeContact_Headers[] = 'Reply-To: ' . $eeContact_From;
		
		if($eeContact_Body) {
			
			// show mail error if there is one.
			add_action( 'wp_mail_failed', 'eeMailError', 10, 1);
			
			// Uncomment to test email error
			// $eeContact_To = FALSE;
			
			$eeContact_Body = html_entity_decode($eeContact_Body);
			$eeContact_Body = strip_tags($eeContact_Body);
			$eeContact_Body = stripslashes($eeContact_Body);	// Make it all nice
			
			if(wp_mail($eeContact_To, $eeContact_PluginName . ' Support', $eeContact_Body, $eeContact_Headers)) {
				
				$eeSFL_Log[] = 'Message Sent';
				
				echo "<script>
				
				alert('The message was sent. Expect a reply soon.');
				
				window.location.replace('" . get_admin_url() . basename($_SERVER['PHP_SELF']) . '?page=' . $_GET['page'] . "');
				
				</script>";
				
			} else {
				
				$eeSFL_Log[] = 'Message FAILED';
			}
		}
	}
}


// Form Display ========================================
	
// Get this URL with all arguments
$eeContact_Location = $_SERVER['REQUEST_URI'];
	
// Nonce a field for security
$eeContact_Nonce = wp_nonce_field( 'ee-support-form', 'ee-support-form-nonce', TRUE, FALSE); // Return it

// The form's default link
// if(!$eeContact_link) { $eeContact_link = get_bloginfo('url'); }

// Build the Support Form
$eeOutput .= '<article class="eeSupp">

		<a class="button eeRight" href="https://simplefilelist.com/docs/" target="_blank">' . __('Plugin Documentation', 'ee-simple-file-list') . '</a>
		
		<h2>' . __('Support Request', 'ee-simple-file-list') . '</h2>
		
		<p>' . __('Do you need help or have a question? Send a message and I will reply promptly.', 'ee-simple-file-list') . '</p>

		<form action="' . $eeContact_Location . '" method="post" id="eeSupportForm">
			  
			<fieldset>
				
				<input type="hidden" name="eeContact_plugin" value="' . $eeSFL->eePluginName . ' (' . eeSFL_Version . ' | ' . eeSFL_DB_Version . ' | ' . eeSFL_Cache_Version . ')" />';
				
				if(@defined('eeSFLF_Version')) {
					$eeOutput .= '<input type="hidden" name="eeContact_folder-ext" value="' . eeSFLF_Version . '" />';
				}
				if(@defined('eeSFLS_Version')) {
					$eeOutput .= '<input type="hidden" name="eeContact_search-ext" value="' . eeSFLS_Version . '" />';
				}
				
				
				$eeOutput .= '<label for="eeContact_name">' . __('Your Name', 'ee-simple-file-list') . ':</label>
				<input type="text" name="eeContact_name" id="eeContact_name" value="' . $eeContact_name . '" required />
				
				<label for="eeContact_email">' . __('Your Email', 'ee-simple-file-list') . ':</label>
				<input type="email" name="eeContact_email" id="eeContact_email" value="' . $eeContact_email . '" required />
				
				<label for="eeContact_page">' . __('Page with Shortcode', 'ee-simple-file-list') . ':</label>
				<input type="url" name="eeContact_Problem-Page" value="' . $eeContact_link . '" id="eeContact_page">

				<label for="eeContact_message">' . __('Your Message', 'ee-simple-file-list') . ':</label>
				<textarea required name="eeContact_message" id="eeContact_message" cols="60" rows="6">' . $eeContact_message . '</textarea>
				
				<input type="hidden" name="eeSupportForm" value="TRUE" /> 
				' .$eeContact_Nonce . '
				<input type="hidden" name="eeContact_Site-Name" value="' . get_bloginfo('name') . '" />
				<input type="hidden" name="eeContact_Site" value="' . get_bloginfo('url') . '" />
				<input type="hidden" name="eeContact_Install" value="' . $eeContact_Install . '" />
				<input type="hidden" name="eeContact_WP-Version" value="' . get_bloginfo('version') . '" />
				<input type="hidden" name="eeContact_Time-Zone" value="' . get_option('timezone_string') . '" />
				<input type="hidden" name="eeContact_Language" value="' . get_bloginfo('language') . '" />
				<input type="hidden" name="eeContact_Content-Type" value="' . get_bloginfo('html_type') . '" />
				<input type="hidden" name="eeContact_Agent" value="' . @$_SERVER['HTTP_USER_AGENT'] . '" />
				
				<br class="eeClearFix" />
				
				<span id="eeSupportFormSubmitMessage">
				Sending Your Message<br />
				<img style="padding:3px;background:white;border:1px #666 solid;border-radius:12px;margin-top:10px;" src="' . plugin_dir_url(__FILE__) . 'images/sending.gif" width="32" height="32" alt="Sending Icon" /></span>
				
				<br class="eeClearFix" />
				
				<input type="submit" name="submit" value="' . __('SEND', 'ee-simple-file-list') . '" class="button eeSFL_Save eeRight" />
				
				<br class="eeClearFix" />
				
				<p><i>' . __('Basic plugin environment details will be sent along with your message to:', 'ee-simple-file-list') . '</i> 
				<a href="mailto:' . $eeContact_To . '" title="Mitchell Bennis" >' . $eeContact_To . '</a>.<br /><br />' . __('To help me further, please include this information', 'ee-simple-file-list') . '...<br />
				
				<a target="_blank" href="/wp-admin/site-health.php?tab=debug">' . __('Click here', 'ee-simple-file-list') . '</a> &rarr; ' . __('Click the "Copy site info to clipboard button"', 'ee-simple-file-list') . ' &rarr; ' . __('Paste the result into the message above.', 'ee-simple-file-list') . '</p>
			
			</fieldset>
			
		</form></article>
			
	<script>

	jQuery( "#eeSupportFormSubmitMessage" ).hide();
	
	jQuery( "#eeSupportForm" ).submit(function() {
		jQuery( "#eeSupportFormSubmit" ).hide();
		jQuery( "#eeSupportFormSubmitMessage" ).fadeIn();
	});

	</script>';

?>