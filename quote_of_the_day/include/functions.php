<?php
	// smash the cache of the config table, so $forum_config is updated properly
	if ( file_exists( FORUM_CACHE_DIR.'cache_config.php' ) )
		unlink( FORUM_CACHE_DIR.'cache_config.php' );

	// The default index to use for fetching a quote.
    $defaultQuoteIndex = 12;
    
    // Default duration of a quote before cycling
    $quoteDuration = 86400;
    
	// Checks to see if the current QOTD is expired, and if so updates the
    // expiry time and resets all seen quotes.
    function checkForUpdate()
    {
		global $forum_config;
        global $quoteDuration;
		                
        if ( time() > $forum_config['o_qotd_next_update'] )
        {
            updateQOTD();
			resetAllSeenQOTDs();
		}
    }
    
    // changes the quote of the day in the forum db to a new, random quote.
    function updateQOTD()
    {
        global $forum_db;
        global $forum_config;
        global $quoteDuration;
        
        $query = array
        (
            'UPDATE'    => 'config',
            'SET'       => 'conf_value = ' . getRandomQuoteID(),
            'WHERE'     => 'conf_name = "o_qotd_quote_id"'
        );
        $forum_db->query_build( $query ) or error(__FILE__, __LINE__);
        
        $query = array
        (
            'UPDATE'    => 'config',
            'SET'       => 'conf_value = ' . ( $forum_config['o_qotd_next_update'] + $quoteDuration ),
            'WHERE'     => 'conf_name = "o_qotd_next_update"'
        );
        $forum_db->query_build( $query ) or error(__FILE__, __LINE__);
    }
    
    // gets a random valid ID of a quote
    function getRandomQuoteID()
    {
        global $forum_db;
        global $defaultQuoteIndex;
        
        $randomQuoteID = $defaultQuoteIndex;
        
        $query = array
        (
			'SELECT'	=> 'id',
			'FROM'		=> 'qotd_quotes',
			'ORDER BY'	=> 'rand()',
			'LIMIT'		=> 1
        );
        $result = $forum_db->query_build($query) or error(__FILE__, __LINE__);
        
		$data = $forum_db->fetch_assoc( $result );
       
		if ( count($data) == 1 )
			return $data['id'];
        else
			return $defaultQuoteIndex;
    }
    
    // resets whether or not all users have seen the current QOTD.
    function resetAllSeenQOTDs()
    {
		global $forum_db;
		
		$query = array
		(
			'UPDATE'	=> 'users',
			'SET'		=> 'qotd_seen = 0'
		);
		
		$forum_db->query_build($query) or error(__FILE__, __LINE__);
    }
    
    // marks the user with the given ID as having seen the current QOTD.
    function flagQOTDAsSeen( $id )
    {
		global $forum_db;
		
		$query = array
		(
			'UPDATE'	=> 'users',
			'SET'		=> 'qotd_seen = 1',
			'WHERE'		=> "id = $id"
		);
		
		$forum_db->query_build($query) or error(__FILE__, __LINE__);
    }
    
    // gets the current Quote of the Day
    function getCurrentQOTD()
    {
        global $forum_config;
        
        return getQuoteByID( $forum_config['o_qotd_quote_id'] );
    }
    
    // gets a quote by ID
    function getQuoteByID( $quoteID )
    {
		global $forum_db;
        
		$query = array
		(
			'SELECT'	=> 'u.username, q.quote, q.rating, q.submitter_id',
			'FROM'		=> 'users AS u',
			'JOINS'		=> array(
				array(
					'RIGHT JOIN'	=> 'qotd_quotes AS q',
					'ON'			=> 'u.id = q.submitter_id',
				),
			),
			'WHERE'		=> "q.id=$quoteID",
			'LIMIT'		=> 1,
		);
		$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);
        
        $quote = $forum_db->fetch_assoc($result);
        
        if ( empty( $quote ) )
			return prepareText( "Error getting quote of the day." );
		
		$quote['quote'] = prepareText( $quote['quote'] );
				
		return $quote;
    }
    
    // Creates the HTML markup to display the current quote of the day.
    function generateQOTDDisplay()
    {   
		global $lang_quote_of_the_day;
		global $forum_url;
		global $forum_user;
		
		$quote	= getCurrentQOTD();
        $quoteSubmitter = (( $forum_user['g_view_users'] == '1' && $quote['submitter_id'] > 1 ) ? '<a title="'.sprintf($lang_quote_of_the_day['Go to profile'], forum_htmlencode($quote['username'])).'" href="'.forum_link($forum_url['user'], $quote['submitter_id']).'">'.forum_htmlencode($quote['username']).'</a>' : '<strong>'.forum_htmlencode($quote['username']).'</strong>') . '</span>';

		$qotd = getQOTDDisplayTemplate();
		
		$qotd = str_replace( '<!-- qotd_title -->', $lang_quote_of_the_day['qotdTitle'], $qotd );
		$qotd = str_replace( '<!-- qotd_date -->', date( $lang_quote_of_the_day['dateFormat'] ), $qotd );
		$qotd = str_replace( '<!-- quote_text -->', $quote['quote'], $qotd );
		$qotd = str_replace( '<!-- quote_rating -->', $quote['rating'], $qotd );
		$qotd = str_replace( '<!-- rating_style -->', getQuoteRatingStyle( $quote ), $qotd );
		$qotd = str_replace( '<!-- submitted_by_text -->', $lang_quote_of_the_day['submitter'], $qotd );
		$qotd = str_replace( '<!-- submitted_by -->', $quoteSubmitter, $qotd );
		
		return $qotd;
    }
    
    // Gets the contents of the .TPL for displaying the quote of the day
    function getQOTDDisplayTemplate()
    {
		global $ext_info;
		global $forum_user;
		
		if ($forum_user['style'] != 'Oxygen' && file_exists($ext_info['path'].'/style/'.$forum_user['style'].'/quotebox.tpl'))
			$qotd = file_get_contents( $ext_info['path'].'/style/'.$forum_user['style'].'/quotebox.tpl' );
		else
			$qotd = file_get_contents( $ext_info['path'].'/style/Oxygen/quotebox.tpl' );

		return $qotd;
    }
    
	// Returns a CSS class corresponding to the way the quote's rating should be colored.
    function  getQuoteRatingStyle( $quote )
    {
		if ( $quote['rating'] > 0 )
			return 'ratingPositive';
		
		if ( $quote ['rating'] < 0 )
			return 'ratingNegative';
			
		return 'ratingNeutral';
    }
    
    // strips slashes and fixes newlines for html
    function prepareText( $stringToPrep )
    {
        $stringToPrep = stripslashes($stringToPrep);
        $stringToPrep = preg_replace("/[\r\n]/", '<br />', $stringToPrep);
        
        return $stringToPrep;
    }
?>