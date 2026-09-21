<?php // Simple File List Script: ee-plugin-author.php | Author: Mitchell Bennis | support@simplefilelist.com | Revised: 11.23.2019
	
defined( 'ABSPATH' ) or die( 'No direct access is allowed' );
if ( ! wp_verify_nonce( $eeSFL_Nonce, 'eeInclude' ) ) exit('ERROR 98'); // Exit if nonce fails

$eeSFL_Log[] = 'Loaded: ee-plugin-author';
	
// Plugin Contributors Array - Format: Name|URL|DESCRIPTION Example: Thnaks to <a href="URL">NAME</a> DESCRIPTION
// Values here are inserted below
$eeContributors = array('dmhendricks|https://github.com/dmhendricks/file-icon-vectors| for the awesome file type icons');  // else it's FALSE;

$eePageSlug = $_GET['page'];
	
// The Content
$eeOutput .= '<article class="eeSupp">

	<a href="http://simplefilelist.com/donations/simple-file-list-project/" title="' . esc_attr__('Show Your Support', 'ee-simple-file-list') . '" target="_blank">
	
		<img id="mitchellbennisHeadshot" src="' . plugin_dir_url( __FILE__ ) . '/images/Mitchell-Bennis-Head-Shot.jpg" alt="Mitchell Bennis" />
	
	</a>

	<h2>' . __('Thank You', 'ee-simple-file-list') . '</h2>
	
	<p>' . __('Thank you for using my plugin. I am proud of this work and am committed to supporting it.', 'ee-simple-file-list') . ' ' . __('The goal is to keep it simple, yet make it do what you need it to do.', 'ee-simple-file-list') . ' ' . __('Tell me about the features that you want.', 'ee-simple-file-list') . ' </p>

	
	<p><a href="http://mitchellbennis.com/" target="_blank">Mitchell Bennis</a><br />
	Cokato, Minnesota, USA</p>'; // That's me!
		
		$eeOutput .= '<p>' . __('Contact Me', 'ee-simple-file-list') . ': <a href="https://simplefilelist.com/support/">' . __('Feedback or Questions', 'ee-simple-file-list') . '</a></p>';
	
	if(is_array($eeContributors)) {
		
		$eeOutput .= '<hr />
		
		<h6>' . __('Contributors', 'ee-simple-file-list') . '</h6>
		
		<p>';
		
		// Contributors Output
		foreach( $eeContributors as $eeValue){
			
			$eeArray = explode('|', $eeValue);
			$eeOutput .= __('Thanks to', 'ee-simple-file-list') . ' <a href="' . @$eeArray[1] . '" target="_blank">' . @$eeArray[0] . ' </a>' . @$eeArray[2] . '<br />';
		}
		
		$eeOutput .= '</p>';
	}
		
	$eeOutput .= '</article>';

?>