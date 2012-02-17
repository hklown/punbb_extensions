<?php
	// smash the cache of the config table, so $forum_config is updated properly
	if ( file_exists( FORUM_CACHE_DIR.'cache_config.php' ) )
		unlink( FORUM_CACHE_DIR.'cache_config.php' );
    
    if ( FORUM_PAGE != 'message')
        define( "BOOK_DISPLAY_TEMPLATE", GetBookDisplayTemplate() );

    // returns information about a book by it's isbn number
    function getBookInfoByISBN( $isbn = '' )
    {
        global $lang_bbcode_book;
        global $forum_config;
        
        $isbn = SanitizeISBN( $isbn );
    
        if ( !IsValidISBN( $isbn ) )
            return errorMessage( $isbn, $lang_bbcode_book['invalid isbn'] );
           
        if ( !array_key_exists( 'o_booktag_isbndbkey', $forum_config ) || $forum_config['o_booktag_isbndbkey'] == '' )
            return errorMessage( $isbn, $lang_bbcode_book['isbndb key not set'] );
            
        return BookInfo( $isbn );
    }
    
    // Removes all non-digit characters from a string.
    function SanitizeISBN( $isbn = '' )
    {
        return preg_replace("/[^0-9]/", "", $isbn);
    }
    
    // determines whether or not a given string is a valid ISBN
    function IsValidISBN( $isbn = '' )
    {
        if (!preg_match("/(?:^[0-9]{10}$)|(?:^[0-9]{13}$)/", $isbn))
            return false;
            
        return true;
    }
    
    // gets the info for a book based on it's isbn
    function BookInfo( $isbn )
    {
        $book = BookInfoFromCache( $isbn );
        
        if ( empty( $book ) )
        {
            $book = BookInfoFromISBNDB( $isbn );
            
            if ( !empty( $book ) )
                CacheBook( $book );
        }
           
        return RenderBook( $book );
    }
    
    // gets info about a book from the cache
    function BookInfoFromCache( $isbn )
    {
        global $forum_db;
        
        $query = array(
            'SELECT'    => '*',
            'FROM'      => 'book_cache AS b',
            'WHERE'     => 'b.isbn='.$isbn
        );
        $result = $forum_db->query_build($query) or error(__FILE__, __LINE__);
        
        return $forum_db->fetch_assoc( $result );
    }
    
    // gets info about a book from ISBNDB
    function BookInfoFromISBNDB( $isbn )
    {
        global $forum_config;
    
        $request_url = 'https://isbndb.com/api/books.xml?access_key='.$forum_config['o_booktag_isbndbkey'].'&results=texts&index1=isbn&value1='.$isbn;
        $response = simplexml_load_string(get_content($request_url));
        
        $book = array();
        
        if ($response->BookList['total_results'] == 0)
            return $book;

        //$image_url = "http://images.amazon.com/images/P/$isbn.01.LZZZZZZZ.jpg";
        //$image_url = "http://covers.openlibrary.org/b/isbn/$isbn-L.jpg";
        $book['isbn'] = $isbn;
        $book['title'] = $response->BookList[0]->BookData[0]->Title;
        $book['full_title'] = $response->BookList[0]->BookData[0]->TitleLong;
        $book['author'] = $response->BookList[0]->BookData[0]->AuthorsText;
        $book['summary'] = $response->BookList[0]->BookData[0]->Summary;
        
        return $book;
    }
    
    // caches a book in the database
    function CacheBook( $book )
    {
        global $forum_db;
        
		$query = array(
            'INSERT'    => 'isbn, title, full_title, author, summary',
            'INTO'      => 'book_cache',
            'VALUES'    => '"' . $forum_db->escape($book['isbn']) . '", "' . $forum_db->escape($book['title']) . '", "' . $forum_db->escape($book['full_title']) . '", "' . $forum_db->escape($book['author']) . '", "' . $forum_db->escape($book['summary']) . '"',
        );
        $forum_db->query_build($query) or error(__FILE__, __LINE__);
    }
    
    // returns the markup for a book to be displayed on the forum
    function RenderBook( $book )
    {
        global $lang_bbcode_book;
        
        $template = BOOK_DISPLAY_TEMPLATE;
        
        $template = str_replace( '<!-- cover_url -->', 'http://covers.openlibrary.org/b/isbn/' . $book['isbn'] . '-L.jpg', $template );
		
		$template = str_replace( '<!-- book_title -->', $book['title'], $template );
		$template = str_replace( '<!-- book_full_title -->', $book['full_title'], $template );
		$template = str_replace( '<!-- book_author -->', $book['author'], $template );
        $template = str_replace( '<!-- book_summary -->', $book['summary'], $template );
        
        $template = str_replace( '<!-- title -->', $lang_bbcode_book['title'], $template );
        $template = str_replace( '<!-- full_title -->', $lang_bbcode_book['full title'], $template );
        $template = str_replace( '<!-- author -->', $lang_bbcode_book['author'], $template );
        $template = str_replace( '<!-- summary -->', $lang_bbcode_book['summary'], $template );

		return $template;
    }
    
    // gets the template to use for rendering a book's markup
    function GetBookDisplayTemplate()
    {
        global $ext_info;
        global $forum_user;
        
		if ($forum_user['style'] != 'Oxygen' && file_exists($ext_info['path'].'/style/'.$forum_user['style'].'/book_template.tpl'))
			$template = file_get_contents( $ext_info['path'].'/style/'.$forum_user['style'].'/book_template.tpl' );
		else
			$template = file_get_contents( $ext_info['path'].'/style/Oxygen/book_template.tpl' );

        $template = str_replace( "\n", '', $template );
        $template = str_replace( "\t", '', $template );

        return $template;
    }
    
    // returns markup for an error message in a tooltip
    function errorMessage( $message, $tooltip )
    {
        return '<span class="book-bbcode-error tooltip warning" tooltip="'. $tooltip . '">' . $message . '</span>';
    }

    function get_content($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);      

        $string = curl_exec ($ch);
        curl_close ($ch);
       
        return $string;    
    }
?>