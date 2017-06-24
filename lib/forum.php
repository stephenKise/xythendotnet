<?php
function categories_nav()
{
	global $session;
	$my_views = unserialize($session['user']['forumpeepdata']);
	$read_csv = file_get_contents('game_cache/forumcategories.csv');
	$forum_categories = explode('|',$read_csv);
	addnav("Quick Jump");
	addnav("`)Home","forum.php?op=main");
	foreach($forum_categories as $index => $cat)
	{
		$cat = substr($cat,0,strpos($cat,':'));
		$cat_time = db_query("SELECT posted FROM forum WHERE category = '$cat' ORDER BY posted DESC LIMIT 0,1");
		$row = db_fetch_assoc($cat_time);
		$math = strtotime($my_views[trim($cat)])-strtotime($row['posted']);
		if ($math < 0) addnav(ucfirst($cat)." - `i`\$New!`i","forum.php?op=view_cat&cat=$cat");
		else addnav("`7".ucfirst($cat),"forum.php?op=view_cat&cat=$cat");
	}
}

function display_categories()
{
	global $session;
	$read_csv = file_get_contents('game_cache/forumcategories.csv');
	$my_views = unserialize($session['user']['forumpeepdata']);
	$forum_categories = explode('|',$read_csv);
	output("<table align='center' width='700px' style='border: 1px dotted;'><tr><td colspan='2' width='400px'>Forum</td><td align='center'>Threads</td><td align='center'>Replies</td><td align='center'>Last Post</td></tr>",true);
	foreach($forum_categories as $index => $cat)
	{
		$category = substr($cat,0,strpos($cat,':'));
		$description = substr($cat,strpos($cat,':')+1);
		$thread_info = db_fetch_assoc(db_query("SELECT COUNT(DISTINCT title) AS threadcount, COUNT(message) AS replies, MAX(posted) AS lastpost FROM forum WHERE category = '$category'"));
		$math = strtotime($my_views[trim($category)])-strtotime($thread_info['lastpost']);
		if ($math < 0) $older = true;
			else $older = false;
		output("<tr style='border-bottom: 1px !important;' onClick=\"window.location='forum.php?op=view_cat&cat=$category'\" onMouseOver=\"this.style.backgroundColor='#212121';this.style.cursor='pointer'\" onMouseOut=\"this.style.backgroundColor='transparent'\"><td width='50px' align='center'><a href='forum.php?op=view_cat&cat=$category'><img width='25px' src='images/Forum".($older?"_new":"_old").".png'></a></td><td width='350px'><a href='forum.php?op=view_cat&cat=$category'><font size='+1.5'>`b`&$category`b</font>`n`i`7$description`i</a></td><td align='center'>{$thread_info['threadcount']}</td><td align='center'>{$thread_info['replies']}</td><td align='center'>{$thread_info['lastpost']}</td></tr>",true);
		addnav("","forum.php?op=view_cat&cat=$category");
	}
	output("</table>",true);
}

function show_category_threads($category,$page_number)
{
	// output($category);
	global $session;
	require_once('lib/datetime.php');
	//set forumpeepdata
	if (!httpget('page')) $page = 1;
		else $page = httpget('page');
	$total_pages = db_query("SELECT COUNT(title) FROM forum WHERE category = '$category' GROUP BY title");
	$total_pages = db_num_rows($total_pages);
	$total_pages = (int)(($total_pages-($total_pages % 15))/15);
	if ($total_pages > 1 && $page < $total_pages)
	{
		$increment_nav = "<a href='forum.php?op=view_cat&cat=$category&page=".($page+1)."'>&raquo;";
		addnav("","forum.php?op=view_cat&cat=$category&page=".($page+1));
	}
	else
	{
		$increment_nav = "&raquo;";
	}
	if ($total_pages > 1 && $page > 1)
	{
		$decrease_nav = "<a href='forum.php?op=view_cat&cat=$category&page=".($page-1)."'>&laquo;";
		addnav("","forum.php?op=view_cat&cat=$category&page=".($page-1));
	}
	else
	{
		$decrease_nav = "&laquo;";
	}

	$offset = ($page-1) * 15;
	if ($total_pages == 0) $total_pages = 1;
	$sql = db_query("SELECT * FROM (SELECT title, status, acctid, posted FROM forum WHERE category = '$category' ORDER BY posted DESC) AS threads GROUP BY title DESC ORDER BY posted DESC LIMIT $offset,15");
	if (($category == "Announcements" && $session['user']['superuser'] & SU_EDIT_USERS) || $category != "Announcements") $create = "<a href='forum.php?op=create_thread&cat=$category'>`&Create Thread</a>";
	output("<table width='700px' align='center' style='border: 1px dotted;'><tr><td colspan='2'><font size='+1'><a href='forum.php?op=main'>`)Home</a> `2> `\$`i$category`i</font> $decrease_nav $page / $total_pages $increment_nav</td><td align='center'>$create</td></tr>",true);
	addnav("","forum.php?op=create_thread&cat=$category");
	addnav("","forum.php?op=main");
	while ($row = db_fetch_assoc($sql))
	{
		//Who created the thread
		$originator = db_fetch_assoc(db_query("SELECT acctid, posted FROM forum WHERE category = '$category' AND title = '".addslashes($row['title'])."' ORDER BY posted ASC limit 0,1"));
		$time_created = $originator['posted'];
		$originator = db_fetch_assoc(db_query("SELECT name FROM accounts WHERE acctid = {$originator['acctid']}"));


		//How many replies we have
		$total_posts = db_fetch_assoc(db_query("SELECT count(id) as replies FROM forum WHERE title = '".addslashes($row['title'])."' AND category = '$category'"));


		//Who was the last to reply
		$last_post = db_fetch_assoc(db_query("SELECT acctid, posted FROM forum WHERE title = '".addslashes($row['title'])."' AND category = '$category' ORDER BY posted DESC LIMIT 0,1"));
		$last_time = $last_post['posted'];
		$last_post = db_fetch_assoc(db_query("SELECT name FROM accounts WHERE acctid = {$last_post['acctid']}"));
		$row['title'] = stripslashes($row['title']);
		output("<tr style='border-bottom: 1px !important;'onClick=\"window.location='forum.php?op=view_thread&thread=".rawurlencode($row['title'])."&cat=$category'\" onMouseOver=\"this.style.backgroundColor='#212121';this.style.cursor='pointer'\" onMouseOut=\"this.style.backgroundColor='transparent'\"><td><a href='forum.php?op=view_thread&thread=".rawurlencode($row['title'])."&cat=$category'><font size='+1'>`&".stripslashes($row['title'])."</font>`n`7Started by `^{$originator['name']} `7 on `\$".timeoffset($time_created)."</a></td><td width='50px' align='center'>{$total_posts['replies']}</td><td align='right' width='150px'>{$last_post['name']}`n".timeoffset($last_time)."</td></tr>",true);
		//THREAD NAME            |REPLIES| LASTPOST
		addnav("","forum.php?op=view_thread&thread=".rawurlencode($row['title'])."&cat=$category");
	}
	output("</table>",true);
}

function display_thread($title,$category,$page_number=FALSE)
{
	global $session;
	require_once('lib/datetime.php');
	//remember to offset. (PAGE_NUMBER)

	addnav("Quick Jump");
	addnav("`&Refresh","forum.php?op=view_thread&thread=".rawurlencode(stripslashes($title))."&cat=$category");

	if (!httpget('page')) $page = 1;
		else $page = httpget('page');
	$total_pages = db_query("SELECT id FROM forum WHERE category = '$category' AND title = '$title'");
	$total_pages = db_num_rows($total_pages);
	$total_pages = (int)((($total_pages-($total_pages % 15))/15)+1);
	if ($total_pages > 1 && $page < $total_pages)
	{
		$increment_nav = "<a href='forum.php?op=view_thread&thread=".rawurlencode($title)."&cat=$category&page=".($page+1)."'>&raquo;</a>";
		addnav("","forum.php?op=view_thread&thread=".rawurlencode($title)."&cat=$category&page=".($page+1));
	}
	else
	{
		$increment_nav = "&raquo;";
	}
	if ($total_pages > 1 && $page > 1)
	{
		$decrease_nav = "<a href='forum.php?op=view_thread&thread=".rawurlencode($title)."&cat=$category&page=".($page-1)."'>&laquo;</a>";
		addnav("","forum.php?op=view_thread&thread=".rawurlencode($title)."&cat=$category&page=".($page-1));
	}
	else
	{
		$decrease_nav = "&laquo;";
	}

	$offset = ($page-1) * 15;

	$sql = db_query("SELECT id, acctid, message, posted, category, status FROM forum WHERE title = '".addslashes($title)."' ORDER BY id ASC LIMIT $offset,15");
	output("<table align='center' width='700px' style='border: 1px dotted;'><tr><td colspan='2'><font size='+1'><a href='forum.php?op=main'>`)Home</a> `2> <a href='forum.php?op=view_cat&cat=$category'>`7$category</a> `2> `i`\$".stripslashes($title)."`i</font> $decrease_nav $page / $total_pages $increment_nav</td><td align='right' width='300px;'>`c`b<a href='forum.php?op=sub_to_thread&title=".rawurlencode(stripslashes($title))."&cat=".$category."'>Subscribe</a>`b`c</td></tr>",true);
	addnav("","forum.php?op=sub_to_thread&title=$title&cat=$category");
	addnav("","forum.php?op=view_cat&cat=$category");
	addnav("","forum.php?op=main");
	while ($row = db_fetch_assoc($sql))
	{
		preg_match_all('/\=\*(.*?)\*/', $row['message'], $matches);
		$total_matches = count($matches);
		$quotes_array = array();
		$amount = 0;
		if ($matches[1][0] != "")
		{
			foreach($matches as $match => $split_matches)
			{
				if ($match != 0)
				{
					foreach($split_matches as $deeper_match => $acct)
					{
						$name = db_fetch_assoc(db_query("SELECT name FROM accounts WHERE acctid = $acct"));
						$quotes_array[$amount++] = $name['name'];
						$message = str_replace('[/QUOTE]','</div>`><font size="+1">"</font>`>`n`c'.stripslashes($name['name']).'`c</div>',$message);
					}
				}
			}
		}
		$message = preg_replace('/\=\*(.*?)\*/','',$row['message']);
		$message = str_replace('[QUOTE]','<div style="background-color: rgba(0, 0, 0, 0.125);padding: 5px 10px;"><font size="+1">"</font><div style="padding-left: 3px;padding-right: 3px;">',$message);
		$wipe = 0;
		while ($wipe < count($quotes_array))
		{
			$message = preg_replace('/\[\/QUOTE\]/','</div>`><font size="+1">"</font>`>`n`c'.$quotes_array[$wipe].'`c</div>',$message,1);
			$wipe++;
		}
		output("<tr><td colspan='3'><div class='forum_thread'>".nl2br(stripslashes($message))."</div><hr class='thread_hr'></td></tr>",true);
		$name = db_fetch_assoc(db_query("SELECT name FROM accounts WHERE acctid = {$row['acctid']}"));
		if ($session['user']['acctid'] == $row['acctid'] || $session['user']['superuser'] & SU_MEGAUSER) $post_options = "<small> `&| `2[<a href='forum.php?op=delete_reply&thread=".rawurlencode(stripslashes(httpget("thread")))."&cat=".httpget("cat")."&acctid={$row['acctid']}&id={$row['id']}'>`\$Delete</a>`2]</small>";
		addnav("","forum.php?op=delete_reply&thread=".rawurlencode(stripslashes(httpget("thread")))."&cat=".httpget("cat")."&acctid={$row['acctid']}&id={$row['id']}");
		output("<tr><td width='33%'><div style='padding-left: 10px'>{$name['name']} $post_options</div></td><td align='center' width='33%'><a href='forum.php?op=view_thread&thread=".rawurlencode(stripslashes(httpget("thread")))."&cat=".httpget("cat")."&persontoquote={$row['acctid']}&quote=".rawurlencode($row['message'])."'>Quote</a></td><td align='right' width='33%'><div style='padding-right: 10px;'>".timeoffset($row['posted'])."</div></td></tr>",true);
		addnav("","forum.php?op=view_thread&thread=".rawurlencode(stripslashes(httpget("thread")))."&cat=".httpget("cat")."&persontoquote={$row['acctid']}&quote=".rawurlencode($row['message']));
	}
	if (httpget("quote"))
	{
		$quote = '[QUOTE=*'.httpget("persontoquote").'*]';
		$quote .= rawurldecode(httpget("quote"));
		$quote .= "[/QUOTE]\n\r";
		$quote = stripslashes($quote);
	}
	else
	{
		$quote = "";
	}
	output("<tr><td colspan='3' align='center'><hr class='thread_hr'></td></tr><tr><td colspan='3' valign='bottom' align='center'><form name='reply' method='POST' action='forum.php?op=save_reply&thread=".rawurlencode(stripslashes($title))."&cat=$category'>`><input type='submit' value='Send'>`>`n<textarea valign='bottom' id='reply' class='input' rows='5' cols='85' name='reply'>".stripslashes($quote)."</textarea></form></td></tr>",true);
	output("</table>",true);
	addnav("","forum.php?op=save_reply&thread=".rawurlencode(stripslashes($title))."&cat=$category");
}

?>