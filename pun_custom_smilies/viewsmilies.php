<?php
if (!defined('FORUM_ROOT'))
	exit;

// Setup breadcrumbs
$forum_page['crumbs'] = array(
    array($forum_config['o_board_title'], forum_link($forum_url['index'])),
    'Smilies' // Set up your page title here
);

define('FORUM_ALLOW_INDEX', 1);
define('FORUM_PAGE', 'viewsmilies'); // Set up your page id here
require FORUM_ROOT.'header.php';

// lets get the smilie categories from the db
$query = array(
			'SELECT'	=> '*',
			'FROM'		=> 'custom_smilies_categories'
			);
$smilie_categories = $forum_db->query_build($query) or error(__FILE__, __LINE__);

function getSmiliesByCategory($category = 0)
{
	global $forum_db;

	$query = array(
			'SELECT'	=> '*',
			'FROM'		=> 'custom_smilies',
			'WHERE'		=> "category = $category"
			);
			
	$result = $forum_db->query_build($query) or error(__FILE__, __LINE__);
			
	return $result;
}

// START SUBST - <!-- forum_main -->
ob_start();

?>
<div id="brd-main" class="main">
<?php while($category = $forum_db->fetch_assoc($smilie_categories)): ?>
	<?php $smilies = getSmiliesByCategory($category['id']); ?>
    <div class="main-head">
        <h2><span id="smileyCategory"><?php echo stripslashes($category['category_name']); ?></span></h2>
    </div>

    <div class="main-content frm">
        <div class="userbox">
			<div id="smilies">
				<?php while($smiley = $forum_db->fetch_assoc($smilies)): ?>
					<div id="smileyBox">
						<span id="smileyReplaceText"><?php echo $smiley['replace_text']."<br /><br />"; ?></span>
						<img src="<?php echo $base_url."/img/smilies/".$smiley['smilie_file']; ?>" title="<?php echo stripslashes($smiley['alt_text']); ?>" alt="<?php echo stripslashes($smiley['alt_text']); ?>" />
					</div>
				<?php endwhile; ?>
			</div>
        </div>
    </div>
<?php endwhile; ?>
</div>
<?php

$tpl_temp = trim(ob_get_contents());
$tpl_main = str_replace('<!-- forum_main -->', $tpl_temp, $tpl_main);
ob_end_clean();
// END SUBST - <!-- forum_main -->

require FORUM_ROOT.'footer.php';

?>