<?php


// addnews ready
// mail ready
function mightyblogs_getmoduleinfo()
{
	$info = array("name" => "MightyE Blogs Public Release", "version" => "1.1", "author" => "Eric Stevens", "allowanonymous" => true, "category" => "General", "download" => "core_module", "settings" => array("Blog Settings,title", "blogtitle" => "Title of the blog section.|MightyBlogs", "blogkey" => "Hot key for blog nav.,string,1|#", "lastblog" => "Last time any blog was posted|0000-00-00 00:00:00", "words" => "Path to words dictionary file|/usr/share/dict/words",), "prefs" => array("Blog User Preferences,title", "lastblog" => "Last time user read blog|0000-00-00 00:00:00", "canblog" => "User can blog,bool|0", "blogsig" => "Blog Signature|", "votes" => "User blog votes,text|a:0:{}",));
	return $info;
}


function mightyblogs_install()
{
	require_once ("lib/tabledescriptor.php");
	$blogdesc = array('blogid' => array('name' => 'blogid', 'type' => 'int(11) unsigned', 'extra' => 'auto_increment'), 'author' => array('name' => 'author', 'type' => 'int(11) unsigned', 'default' => '1'), 'date' => array('name' => 'date', 'type' => 'datetime'), 'subject' => array('name' => 'subject', 'type' => 'varchar(255)'), 'body' => array('name' => 'body', 'type' => 'text'), 'hits' => array('name' => 'hits', 'type' => 'int(11) unsigned', 'default' => '0'), 'key-PRIMARY' => array('name' => 'PRIMARY', 'type' => 'primary key', 'unique' => '1', 'columns' => 'blogid'), 'index-date' => array('name' => 'date', 'type' => 'index', 'columns' => 'date'), 'index-author' => array('name' => 'author', 'type' => 'index', 'columns' => 'author'));
	synctable(db_prefix('mod_mightyblogs'), $blogdesc, true);
	module_addhook("village");
	module_addhook("footer-shades");
	//module_addhook("index");
	module_addhook_priority("lodge", "13");
	module_addhook_priority("pointsdesc", "13");
	return true;
}


function mightyblogs_uninstall()
{
	debug("Dropping mod_mightyblogs table. All blogs are lost.  Woe is them.");
	$sql = "DROP TABLE IF EXISTS " . db_prefix("mod_mightyblogs");
	db_query($sql);
	return true;
}


function mightyblogs_dohook($hookname, $args)
{
	global $session;
	$title = get_module_setting("blogtitle");
	$blogkey = get_module_setting("blogkey");
	switch ($hookname)
	{
		case "village" :
		case "footer-shades" :
		case "index" :
// $args only has the othernav stuff from the village.
			if ($hookname == "village")
			{
				tlschema($args['schemas']['othernav']);
				addnav($args['othernav']);
				tlschema();
			}
			else
			{
				addnav("Other");
			}
			if (get_module_pref("lastblog") < get_module_setting("lastblog"))
			{
				addnav(array("%s?`b%s`b", $blogkey, $title), "runmodule.php?module=mightyblogs&op=view");
			}
			else
			{
				addnav(array("%s?%s", $blogkey, $title), "runmodule.php?module=mightyblogs&op=view");
			}
			break;
		case "lodge" :
			if ($session['user']['donation'] >= 1000)
			{
				addnav("Silver Grade");
				addnav($title, "runmodule.php?module=mightyblogs&op=view");
			}
			break;
		case "pointsdesc" :
			$args['count']++;
			output("`\$- `^Ability to post your own blog.`n");
			break;
	}
	return $args;
}


function mightyblogs_run()
{
	global $session;
	require_once ("lib/datetime.php");
	require_once ("lib/villagenav.php");
	$op = httpget("op");
	$id = httpget("id");
	$author = httpget("author");
	$day = httpget("day");
	$month = httpget("month");
	page_header(get_module_setting("blogtitle"));
	rawoutput("<script language='JavaScript'>

	<--

	function showHide(id){

		var item = document.getElementById(id);

		if (item.style.display=='block'){

			item.style.display='none';

		}else{

			item.style.display='block';

		}

	}

	//-->

	</script>");
	rawoutput("<style type='text/css'>

		span.tangent {

			display: none;

			border: 1px dotted #0000FF;

		}

		table.calendar {

			border-left: 1px solid #000000;

			border-right: 0px solid #000000;

			border-bottom: 1px solid #000000;

			border-top: 0px solid #000000;

		}

		table.calendar tr {



		}

		table.calendar td {

			border-left: 0px solid #000000;

			border-right: 1px solid #000000;

			border-bottom: 0px solid #000000;

			border-top: 1px solid #000000;

			font-size: 10px;

			background-color: #003366;

			color: #FFFFFF;

			text-align: center;

		}

		table.calendar td.new {

			background-color: #006699;

		}

		table.calendar td.new a {

			color: #FFFF66;

			text-decoration: none;

		}

		table.calendar td.offmonth {

			background-color: #006633;

		}

	</style>");
	if ($op == "del")
	{
		$sql = "DELETE FROM " . db_prefix("mod_mightyblogs") . " WHERE blogid='" . httpget("id") . "'";
		db_query($sql);
		output(db_affected_rows() . " blogs deleted.`n");
		$op = "view";
		$id = "";
	}
	if ($id > "")
	{
		$where = "WHERE blogid='$id'";
		addnav("Calendar");
	}
	elseif ($author > "")
	{
		$where = "WHERE " . db_prefix("accounts") . ".login='$author'";
		if ($day > "")
		{
			$where .= " AND date>='$day 00:00:00' AND date<='$day 23:59:59'";
		}
		addnav(array("%s's Calendar", $author));
	}
	else
	{
		if ($day > "")
		{
			$where = " AND date>='$day 00:00:00' AND date<='$day 23:59:59'";
		}
		else
		{
			$where = "WHERE date>'" . date("Y-m-d H", strtotime("-7 days")) . "'";
		}
		addnav("Calendar");
	}
	$calendar = mightyblogs_calendar($month, $day, $author);
	global $templatename;
	if ($templatename == "Classic.htm")
	{
		$calendar = "<tr><td>$calendar</td></tr>";
	}
	addnav("$calendar", "!!!addraw!!!", true);
	$sql = "SELECT " . db_prefix("accounts") . ".name, " . db_prefix("mod_mightyblogs") . ".* FROM " . db_prefix("mod_mightyblogs") . " INNER JOIN " . db_prefix("accounts") . " ON " . db_prefix("accounts") . ".acctid = " . db_prefix("mod_mightyblogs") . ".author $where ORDER BY date DESC LIMIT 15";
/*debug($sql); */
// Start Blog Votes
	if ($op == "vote")
	{
		require_once ("lib/redirect.php");
		$v = httpget('v');
		$i = ($v == "up" ? 1 : 0);
		$votes = unserialize(get_module_pref('votes'));
		if (!is_array($votes))
			$votes = array();
		if (isset ($votes[$id]))
		{
			if ($votes[$id] == $i)
				unset ($votes[$id]);
			else
				$votes[$id] = $i;
		}
		else
			$votes[$id] = $i;
		set_module_pref("votes", serialize($votes));
		$ret = substr(httpget('ret'), 1);
		redirect($ret);
	}
// End Blog Votes
	if ($op == "view")
	{
		$result = db_query($sql);
		while ($row = db_fetch_assoc($result))
		{
			mightyblogs_show($row);
		}
	}
	elseif ($op == "edit")
	{
		$result = db_query($sql);
		if (db_num_rows($result) == 0)
		{
			$row = array("name" => $session['user']['name'], "blogid" => "", "author" => "", "date" => date("Y-m-d H:i:s"), "subject" => "", "body" => "", "hits" => 0);
		}
		else
		{
			$row = db_fetch_assoc($result);
		}
		mightyblogs_form($row);
		if ($row['subject'] > "" || $row['body'] > "")
			mightyblogs_show($row);
	}
	elseif ($op == "save")
	{
		$post = httpallpost();
		if (isset ($post['save']))
		{
			if ($post['blogid'] > "")
			{
				$sql = "UPDATE " . db_prefix("mod_mightyblogs") . " SET body='{$post['body']}', subject='{$post['subject']}' WHERE blogid='{$post['blogid']}'";
				db_query($sql);
				output(db_affected_rows() . " rows updated.`n");
			}
			else
			{
				$blogsig = get_module_pref("blogsig");
				if ($blogsig > "")
					$post['body'] .= "`0`n" . addslashes(get_module_pref("blogsig")) . "`0";
				$date = date("Y-m-d H:i:s");
				$sql = "INSERT INTO " . db_prefix("mod_mightyblogs") . " (body, subject, author, date) VALUES ('{$post['body']}','{$post['subject']}',{$session['user']['acctid']},'$date')";
				db_query($sql);
				output(db_affected_rows() . " rows inserted.`n");
				set_module_setting("lastblog", $date);
			}
			$post['body'] = stripslashes($post['body']);
			$post['subject'] = stripslashes($post['subject']);
			$post['author'] = $session['user']['acctid'];
		}
		else
		{
//we're previewing the blog
			$post['body'] = stripslashes($post['body']);
			$post['subject'] = stripslashes($post['subject']);
			mightyblogs_form($post);
		}
		$post['body'] = mightyblogs_spell($post['body']);
		mightyblogs_show($post);
	}
	addnav("Options");
	addnav("Blog Homepage", "runmodule.php?module=mightyblogs&op=view");
	if (!$session['user']['loggedin'])
	{
		addnav("L?Return to Login", "index.php");
	}
	elseif ($session['user']['alive'])
	{
		villagenav();
	}
	else
	{
		addnav("S?Return to the Shades", "shades.php");
	}
	if (get_module_pref("canblog") || $session['user']['donation'] >= 1000)
	{
		addnav("Add a blog", "runmodule.php?module=mightyblogs&op=edit&id=-1");
	}
	addnav("Browse by Author");
	$sql1 = "SELECT name,max(login) AS login, max(date) AS date FROM " . db_prefix("mod_mightyblogs") . " INNER JOIN " . db_prefix("accounts") . " ON acctid = author GROUP BY name";
	$result = db_query($sql1);
	while ($row = db_fetch_assoc($result))
	{
		addnav(array("%s (%s)", $row['name'], reltime(strtotime($row['date']))), "runmodule.php?module=mightyblogs&op=view&author=" . rawurlencode($row['login']));
	}
	global $seenblogs;
	if (count($seenblogs) > 0)
	{
		$sql = "UPDATE " . db_prefix("mod_mightyblogs") . " SET hits=hits+1 WHERE blogid IN (" . join(",", $seenblogs) . ")";
		db_query($sql);
	}
	page_footer();
}


$lastblogdate = "";
$seenblogs = array();


function mightyblogs_show($blog)
{
	require_once ("lib/nltoappon.php");
	global $lastblogdate, $session, $seenblogs;
	if (!is_array($seenblogs))
		$seenblogs = array();
	if ($blog['blogid'] > "" && $session['user']['acctid'] != $blog['author'])
		array_push($seenblogs, $blog['blogid']);
	$d = strtotime($blog['date']);
	$thisblogdate = substr($blog['date'], 0, 10);
	if ($thisblogdate != $lastblogdate)
	{
		$lastblogdate = $thisblogdate;
		output_notl("`^<font size=+1>" . date("l, F d" . (date("Y", $d) != date("Y") ? ", Y" : " "), $d) . "</font>`0`n", true);
	}
	if ($blog['date'] > get_module_pref("lastblog"))
		set_module_pref("lastblog", $blog['date']);
	output_notl("`^" . date("h:i a T", $d) . "`0 — ");
	output_notl("`@%s`0", $blog['name']);
	if ($blog['subject'] > "")
		output_notl(" — `%%s`0", $blog['subject']);
// Start Blog Votes
	$votes = unserialize(get_module_pref('votes'));
	if (!is_array($votes))
		$votes = array();
	$numl = db_fetch_assoc(db_query("SELECT count(userid) AS c FROM " . db_prefix('module_userprefs') . " WHERE modulename = 'mightyblogs' AND setting = 'votes' AND value LIKE '%i:{$blog['blogid']};i:1;%'"));
	$numd = db_fetch_assoc(db_query("SELECT count(userid) AS c FROM " . db_prefix('module_userprefs') . " WHERE modulename = 'mightyblogs' AND setting = 'votes' AND value LIKE '%i:{$blog['blogid']};i:0;%'"));
	$numl = $numl['c'];
	$numd = $numd['c'];
	$choice_uc = ((isset ($votes[$blog['blogid']]) && $votes[$blog['blogid']] == 1) ? "`&" : "`@");
	$choice_dc = ((isset ($votes[$blog['blogid']]) && $votes[$blog['blogid']] == 0) ? "`&" : "`\$");
	if ($session['user']['loggedin'])
	{
		output_notl("<a href='runmodule.php?module=mightyblogs&op=vote&id={$blog['blogid']}&v=up&ret=" . urlencode($_SERVER['REQUEST_URI']) . "' title='Vote Up'>$choice_uc&#9650; $numl</a> ", true);
		output_notl("<a href='runmodule.php?module=mightyblogs&op=vote&id={$blog['blogid']}&v=down&ret=" . urlencode($_SERVER['REQUEST_URI']) . "' title='Vote Down'>$choice_dc&#9660; $numd</a>", true);
		addnav("", "runmodule.php?module=mightyblogs&op=vote&id={$blog['blogid']}&v=up&ret=" . urlencode($_SERVER['REQUEST_URI']) . "");
		addnav("", "runmodule.php?module=mightyblogs&op=vote&id={$blog['blogid']}&v=down&ret=" . urlencode($_SERVER['REQUEST_URI']) . "");
	}
	else
	{
		output_notl("$choice_uc&#9650; $numl", true);
		output_notl("$choice_dc&#9660; $numd", true);
	}
// End Blog Votes
	output_notl("`n");
	if ($session['user']['acctid'] == $blog['author'])
	{
		$edit = translate_inline("Edit");
		$del = translate_inline("Delete");
		$delconf = translate_inline("Are you sure you want to delete this blog?");
		output_notl("[ <a href='runmodule.php?module=mightyblogs&op=edit&id={$blog['blogid']}'>$edit</a>", true);
		addnav("", "runmodule.php?module=mightyblogs&op=edit&id={$blog['blogid']}");
		output_notl("| <a href='runmodule.php?module=mightyblogs&op=del&id={$blog['blogid']}' onClick=\"return(confirm('$delconf'));\">$del</a>", true);
		addnav("", "runmodule.php?module=mightyblogs&op=del&id={$blog['blogid']}");
		output_notl(" ]");
	}
	output("Hits: %s`n", $blog['hits']);
//add in raw links
//$urlcodes = "[!-;=?-~]"; //all keyboard chars sans space, < and >
	$bodyparts = preg_split("/([<>])/", $blog['body'], - 1, PREG_SPLIT_DELIM_CAPTURE);
	$body = "";
	$intag = false;
	while (list($key, $val) = each($bodyparts))
	{
//$body .= "`n--------------`n".htmlentities($val, ENT_COMPAT, getsetting("charset", "ISO-8859-1"));
		if ($val == "<")
		{
			$intag = true;
		}
		elseif ($val == ">")
		{
			$intag = false;
		}
		elseif (!$intag)
		{
//we're not within any HTML tags, we are safe to add links here.
			$val = htmlentities($val, ENT_COMPAT, getsetting("charset", "ISO-8859-1")); //get quotes and such encoded.
			$val = str_replace("`&amp;", "`&", $val);
			$val = preg_replace("/([[:alpha:]]+:\\/\\/)([!-~]+)/", "<a href=\"\\1\\2\" target=\"_blank\">\\1\\2</a>", $val);
		}
		elseif ($intag)
		{
			$tag = split("[ \t\n]", $val);
			if (strtolower($tag[0]) == "a")
			{
				$targetfound = false;
				while (list($k, $v) = each($tag))
				{
					if (substr(strtolower($v), 0, 6) == "target")
					{
						$targetfound = true;
						break;
					}
				}
				if (!$targetfound)
					$val .= " target=\"_blank\"";
			}
		}
		$body .= $val;
	}
	$body = str_replace("<tangent>", "<a href='#' onClick='showHide(\"tangent{$blog['blogid']}\");return false;'>Tangent here</a>.<br><span class='tangent' id='tangent{$blog['blogid']}'>", $body);
	$body = str_replace("</tangent>", "</span>", $body);
//$body = preg_replace("/(>?)([[:alpha:]]+:\\/\\/)($urlcodes+)[[:punct:]]?/","\\1<a href=\"\\2\\3\" target=\"_blank\">\\2\\3</a>\\4",$blog['body']);
//$body = preg_replace("/([[:alpha:]]+:\\/\\/)([!-~]+)[[:punct:]]*/","<a href=\"\\1\\2\" target=\"_blank\">\\1\\2</a>",$blog['body']);
//yeah, we want to allow HTML, blogs are only being given to trusted users.
	output_notl("`@" . nltoappon($body) . "`0`n`n", true);
}


function mightyblogs_form($blog)
{
	rawoutput("<form action='runmodule.php?module=mightyblogs&op=save' method='POST'>");
	addnav("", "runmodule.php?module=mightyblogs&op=save");
	output("`bAdd / Edit a Blog:`b`n");
	rawoutput("<input type='hidden' name='blogid' value=\"" . htmlentities($blog['blogid'], ENT_COMPAT, getsetting("charset", "ISO-8859-1")) . "\">");
	rawoutput("<input type='hidden' name='name' value=\"" . htmlentities($blog['name'], ENT_COMPAT, getsetting("charset", "ISO-8859-1")) . "\">");
	rawoutput("<input type='hidden' name='date' value=\"" . htmlentities($blog['date'], ENT_COMPAT, getsetting("charset", "ISO-8859-1")) . "\">");
	output("Subject: ");
	rawoutput("<input name='subject' value=\"" . htmlentities($blog['subject'], ENT_COMPAT, getsetting("charset", "ISO-8859-1")) . "\" size='50' maxlength='255'><br/>");
	output("Body:`n");
	rawoutput("<textarea name='body' cols='70' rows='15' class='input'>" . htmlentities($blog['body'], ENT_COMPAT, getsetting("charset", "ISO-8859-1")) . "</textarea><br/>");
	$prev = translate_inline("Preview");
	$save = translate_inline("Save");
	rawoutput("<input type='submit' value='$prev' name='preview' class='button'>");
	rawoutput("<input type='submit' value='$save' name='save' class='button'>");
	rawoutput("</form>");
	output("All HTML is legitimate in the blogs.");
	output("Things that look like links pasted right in will automatically link, and things that are manually linked (<a href>) will automatically open in a new window.");
	output("You can also use the <tangent> tag to insert tangents, and they'll be clickable & expandable.`n");
}


function mightyblogs_spell($input, $prefix = "<span style='border: 1px dotted #FF0000;'>", $postfix = "</span>")
{
	$words = get_module_setting("words");
	require_once ("lib/spell.php");
	return spell($input, $words, $prefix, $postfix);
}


function mightyblogs_calendar($month, $day, $author)
{
	if ($month > "")
	{
		$month = strtotime($month . "-01");
	}
	else
	{
		$month = strtotime(date("Y-m-01"));
	}
//start should be the Sunday before on on the first of the month.
	$start = strtotime("-" . date("w", $month) . " days", $month);
	$end = strtotime("+5 weeks", $start);
	$calrange = "

	SELECT DISTINCT

		MID(date,1,10) AS d

	FROM " . db_prefix("mod_mightyblogs") . "

	INNER JOIN " . db_prefix("accounts") . "

		ON acctid=author

	WHERE " . ($author > "" ? "login='$author' " : "1=1") . "

		AND date>='" . date("Y-m-d", $start) . "'

		AND date<='" . date("Y-m-d", $end) . "'

	ORDER BY date";
	$result = db_query($calrange);
	$blogdays = array();
	while ($row = db_fetch_assoc($result))
	{
		$blogdays[$row['d']] = true;
	}
	$calendar = "<table class='calendar' cellpadding='1' cellspacing='0'>";
	$calendar .= "<tr>";
	$link = "runmodule.php?module=mightyblogs&op=view&author=$author&month=" . date("Y-m", strtotime("-1 month", $month)) . "&day=$day";
	addnav("", $link);
	$calendar .= "<td class='new'><a href='$link'>&lt;</a></td>";
	$calendar .= "<td colspan='5' class='new'>" . date("F y", $month) . "</td>";
	$link = "runmodule.php?module=mightyblogs&op=view&author=$author&month=" . date("Y-m", strtotime("+1 month", $month)) . "&day=$day";
	addnav("", $link);
	$calendar .= "<td class='new'><a href='$link'>&gt;</a></td></tr>";
	for ($d = $start; $d < $end; $d = strtotime("+1 day", $d))
	{
		if (date("w", $d) == 0)
			$calendar .= "<tr>";
		if (isset ($blogdays[date("Y-m-d", $d)]))
		{
			$link = "runmodule.php?module=mightyblogs&op=view&author=$author&month=" . date("Y-m", $month) . "&day=" . date("Y-m-d", $d) . "";
			addnav("", $link);
			$calendar .= "<td class='new'><a href='$link'>" . date("d", $d) . "</a></td>";
		}
		else
		{
			if (date("m", $d) == date("m", $month))
			{
				$calendar .= "<td>" . date("d", $d) . "</td>";
			}
			else
			{
				$calendar .= "<td class='offmonth'>" . date("d", $d) . "</td>";
			}
		}
		if (date("w", $d) == 6)
			$calendar .= "</tr>";
	}
	$calendar .= "</table>";
	return $calendar;
}


?>

