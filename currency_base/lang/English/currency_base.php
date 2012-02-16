<?php
	if (!defined('FORUM')) die();
	
	$lang_currency_base = array
	(
		// User Management
		'Edit Currency'    					=> 'Edit currency balance',
		'Invalid Currency Assignment'		=> 'New currency balance must be a non-negative integral value!',
		
		// Admin Control Panel
		'AOP Currency Tab'					=> 'Currency',
		
		'Setup currency'					=> 'Configure your forum\'s currency',
		'Setup currency legend'				=> 'Core Currency settings',
		
		'Currency name label'				=> 'Currency name',
		
		'Currency icon label'				=> 'Currency Icon',

		'Delete currency icon label'		=> 'Delete Currency icon',
		'Delete currency icon'				=> 'Delete custom Currency Icon (reset to default)',
		'No Currency icon to delete'		=> '(No Currency icon to delete)',

		'Upload new Currency icon label'	=> 'Upload Currency Icon file',
		'Currency icon replace warning'		=> 'Uploading a new currency icon will replace your existing currency icon.',
		'Currency icon constraints'			=> 'The maximum image size allowed is 20x20 pixels and 5,000 bytes (5 KB)',
		'Allowed filetypes'					=> 'The allowed image file types are gif and png.',

		'Change currency errors'			=> '<strong>Warning!</strong> The following errors must be corrected before your currency can be updated',
		'Invalid currency name'				=> '<span class="warn hn"><strong>Currency name can not be blank!</strong></span>',
		
		'No currency icon deleted redirect'	=> 'There was no custom icon to delete! Redirecting...',
		'Not enough perms redirect'			=> 'Not enough permission to delete the custom currency icon (need 775+!). Custom currency icon not deleted, redirecting...',
		'Currency icon deleted redirect'	=> 'Custom currency icon deleted. Redirecting...',
	);
?>