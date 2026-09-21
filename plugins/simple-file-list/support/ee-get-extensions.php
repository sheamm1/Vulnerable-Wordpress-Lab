<?php // Simple File List Script: ee-get-extensions.php | Author: Mitchell Bennis | support@simplefilelist.com | Revised: 11.23.2019
	
defined( 'ABSPATH' ) or die( 'No direct access is allowed' );
if ( ! wp_verify_nonce( $eeSFL_Nonce, 'eeInclude' ) ) exit('ERROR 98'); // Exit if nonce fails

$eeSFL_Log[] = 'Loaded: ee-get-simple-file-list-folders';
$eeSFL_Button = '';

$eeSFL_ThisDomain = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

$eeSFL_ThisEmail = get_option('admin_email');
if(!$eeSFL_ThisEmail) {
	$eeSFL_ThisEmail = 'mail@' . $eeSFL_ThisDomain;
}

if(filter_var($eeSFL_ThisDomain, FILTER_VALIDATE_URL)) {

	// Build the query URL
	$eeOrderURL = 'https://get.simplefilelist.com/index.php?eeDomain=' . urlencode( $eeSFL_ThisDomain ); // Add this domain name, with protocal
	$eeOrderURL .= '&eeEmail=' . urlencode($eeSFL_ThisEmail); // The notification email
}

// The Content
$eeOutput .= '<article class="eeSupp eeExtensions">

	<h2>' . __('Add Feature Extensions', 'ee-simple-file-list') . '</h2>
	
	<p>' . __('Extensions add extended feature support to the free version of Simple File List. They are designed to improve the management of larger, more complex, file lists.', 'ee-simple-file-list') . '</p>
	
	<hr />';
	
if(!$eeSFLF) {
	
	$eeSFL_Button = '<a class="button eeGet" target="_blank" href="' . $eeOrderURL . '&eeExtension=ee-simple-file-list-folders">' . __('Add Folder Support Now', 'ee-simple-file-list') . '</a>';
	
	$eeOutput .= '<h3>' . __('Folder Support', 'ee-simple-file-list') . '</h3>
	
	<img src="https://get.simplefilelist.com/images/Folder-Demo.jpg" width="400" height="331" class="eeRight" />
	
	<p>' . __('Adds an extension that allows folder listing, navigation and management capabilities.', 'ee-simple-file-list') . '</p>
	
	<ul>
		<li>' . __('Create folders and unlimited levels of sub-folders.', 'ee-simple-file-list') . '</li>
		<li>' . __('Use a shortcode attribute to display specific folders.', 'ee-simple-file-list') . '</li>
		<li>' . __('Display different folders in different places on your site.', 'ee-simple-file-list') . '<br />
			' . __('You can even show several different folders on the same page and within widgets.', 'ee-simple-file-list') . '<br />
			' . __('Front-side users cannot navigate above the folder you specify.', 'ee-simple-file-list') . '</li>
		<li>' . __('Breadcrumb navigation indicates where you are.', 'ee-simple-file-list') . '</li>
		<li>' . __('Easily move files or entire folders.', 'ee-simple-file-list') . '</li>
		<li>' . __('Easily rename any folder.', 'ee-simple-file-list') . '</li>
		<li>' . __('Easily delete any folder, along with all contents.', 'ee-simple-file-list') . '</li>
		<li>' . __('Choose to sort folders first or sort along with the files.', 'ee-simple-file-list') . '</li>
		<li>' . __('Optionally display folder sizes.', 'ee-simple-file-list') . '</li>
		<li>' . __('Updating to newer versions is just like other Wordpress plugins.', 'ee-simple-file-list') . '</li>
		<li><a target="_blank" href="https://simplefilelist.com/how-to-organize-your-files-into-folders/">' . __('How to Organize Your Files into Folders', 'ee-simple-file-list') . '</a></li>
		<li><a target="_blank" href="https://simplefilelist.com/add-folder-support/">' . __('See the Demo', 'ee-simple-file-list') . '</a></li>
	</ul>
	
	<p class="eeCentered">' . $eeSFL_Button . '</p>
	
	<br class="clearing" />

	<hr />';
	
}
	
if(!$eeSFLS) {
		
$eeSFL_Button = '<a class="button eeGet" target="_blank" href="' . $eeOrderURL . '&eeExtension=ee-simple-file-list-search">' . __('Add Search &amp; Pagination Now', 'ee-simple-file-list') . '</a>';

$eeOutput .= '<h3>Search &amp; Pagination</h3>
	
	<img src="https://get.simplefilelist.com/images/Search-Demo.jpg" width="400" height="301" class="eeRight" />
	
	<p>' . __('Adds searching and pagination functionality to Simple File List. It is designed to make very large file lists more manageable.', 'ee-simple-file-list') . '</p>
	
	<ul>
		<li>' . __('Adds a search bar above the file list.', 'ee-simple-file-list') . '</li>
		<li>' . __('Search by name and/or a date range (if the date column is shown).', 'ee-simple-file-list') . '</li>
		<li>' . __('Searches within sub-folder. (But not above the current folder)', 'ee-simple-file-list') . '</li>
		<li>' . __('Pagination breaks up large file lists into smaller pages.', 'ee-simple-file-list') . '</li>
		<li>' . __('Define the number of files per page in the settings.', 'ee-simple-file-list') . '</li>
		<li>' . __('Show or hide the search bar and/or pagination in the settings.', 'ee-simple-file-list') . '</li>
		<li>' . __('Use a shortcode to place a search form anywhere on your website.', 'ee-simple-file-list') . '</li>
		<li>' . __('Updating to newer versions is just like other Wordpress plugins.', 'ee-simple-file-list') . '</li>
		<li><a target="_blank" href="https://simplefilelist.com/add-search-pagination/">' . __('See the Demo', 'ee-simple-file-list') . '</a></li>
	</ul>
	
	<p class="eeCentered">' . $eeSFL_Button . '</p>
	
	<br class="clearing" />';
	
}

$eeOutput .= '</article>';
	
$eeSFL_Log[] = '$eeOrderURL ...';
$eeSFL_Log[] = urldecode($eeOrderURL);	

?>