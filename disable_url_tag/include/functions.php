<?php
	if (!defined('FORUM_ROOT'))
		exit('The constant FORUM_ROOT must be defined and point to a valid PunBB installation root directory.');
	
	function StripURLTags( $message )
	{
		return preg_replace( '#\[url(?:=.*?)?\](.*?)\[/url\]#', '$1', $message );
	}
?>