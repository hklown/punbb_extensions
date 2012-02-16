<?php
	if (!defined('FORUM_ROOT'))
		exit('The constant FORUM_ROOT must be defined and point to a valid PunBB installation root directory.');
	
	// Returns a HTML <img> tag for the Currency icon
	function CurrencyIcon($includeHoverText = true)
	{
		global $forum_config;
		global $ext_info;
		
		$hoverText = $forum_config['o_currency_name'];
		
		if ( UserHasCustomCurrencyIcon() )
			$imageURL = $ext_info['url'] . '/images/currencyicon.png';
		else
			$imageURL = $ext_info['url'] . '/images/defaultcurrencyicon.png';
		
		if ( $includeHoverText )
			return "<img src=\"$imageURL\" title=\"$hoverText\" style=\"vertical-align: middle;\" />";
		else
			return "<img src=\"$imageURL\" style=\"vertical-align: middle;\" />";
	}
	
	// Returns a bool indicating whether or not the user has uploaded a custom currency icon.
	function UserHasCustomCurrencyIcon()
	{
		global $ext_info;
	
		if ( file_exists( $ext_info['path'] . '/images/currencyicon.png' ) )
			return true;
		else
			return false;
	}
	
	// Sets the user with the username $user's currency to $newBalance.
	function SetCurrency( $user, $newBalance, $userCausingChange = "", $reason = "" )
	{
		global $forum_db;
		
		$query = array(
			'SELECT'	=> 'currency_balance',
			'FROM'		=> 'users',
			'WHERE'		=> "username = '$user'"
		);
		$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);
			
		$currentBalance = $forum_db->fetch_row($result);
		
		$currentBalance = $currentBalance[0];
		
		if ( $newBalance < $currentBalance )
			SubtractCurrency( $user, ( $currentBalance - $newBalance ), $userCausingChange, $reason );
		elseif ( $newBalance > $currentBalance )
			AddCurrency( $user, ( $newBalance - $currentBalance ), $userCausingChange, $reason );
	}
	
	// Adds $amountToAdd to $user's balance.
	function AddCurrency( $user, $amountToAdd, $userCausingChange = "", $reason = "" )
	{
		global $forum_db;
		
		$query = array(
			'UPDATE'	=> 'users',
			'SET'		=> "currency_balance = currency_balance + $amountToAdd",
			'WHERE'		=> "username = '$user'"
		);
		$forum_db->query_build($query) or error(__FILE__, __LINE__);
	}
	
	// Subtracts $amountToSubtract from $user's balance.
	function SubtractCurrency( $user, $amountToSubract, $userCausingChange = "", $reason = "" )
	{
		global $forum_db;
		
		$query = array(
			'UPDATE'	=> 'users',
			'SET'		=> "currency_balance = currency_balance - $amountToSubract",
			'WHERE'		=> "username = '$user'"
		);
		$forum_db->query_build($query) or error(__FILE__, __LINE__);
	}
	
	// Deletes the user's custom currency icon
	function DeleteCustomCurrencyIcon()
	{
		global $ext_info;
		global $forum_url;
		global $lang_currency_base;
		
		if ( !UserHasCustomCurrencyIcon() )
			redirect(forum_link($forum_url['admin_settings_currency'], $id), $lang_currency_base['No currency icon deleted redirect']);
			
		if ( fileperms( $ext_info['path'].'/images' ) < 755 )
			redirect(forum_link($forum_url['admin_settings_currency'], $id), $lang_currency_base['Not enough perms redirect']);
			
		unlink( $ext_info['path'] . '/images/currencyicon.png' );
		
		redirect(forum_link($forum_url['admin_settings_currency'], $id), $lang_currency_base['Currency icon deleted redirect']);
	}
?>