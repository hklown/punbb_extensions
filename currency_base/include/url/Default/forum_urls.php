<?php

  // Make sure no one attempts to run this script "directly"
  if ( !defined('FORUM') )
    exit;

    $forum_url = array_merge( $forum_url,
      array
      (
        'admin_settings_currency'	=> 'admin/settings.php?section=currency',
        'delete_currency_icon'		=> 'admin/settings.php?section=currency&action=delete_currency_icon&id=$1&csrf_token=$2',
      ));
?>