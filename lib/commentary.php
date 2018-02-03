<?php
// translator ready
// addnews ready
// mail ready
require_once("lib/datetime.php");
require_once("lib/sanitize.php");
require_once("lib/http.php");

$comsecs = array();
function commentarylocs() {
	global $comsecs, $session;
	if (is_array($comsecs) && count($comsecs)) return $comsecs;

	$vname = getsetting("villagename", LOCATION_FIELDS);
	$iname = getsetting("innname", LOCATION_INN);
	tlschema("commentary");
	$comsecs['village'] = sprintf_translate("%s Square", $vname);
	if ($session['user']['superuser'] & ~SU_DOESNT_GIVE_GROTTO) {
		$comsecs['superuser']=translate_inline("Grotto");
	}
	$comsecs['shade']=translate_inline("Land of the Shades");
	$comsecs['grassyfield']=translate_inline("Grassy Field");
	$comsecs['inn']="$iname";
	$comsecs['motd']=translate_inline("MotD");
	$comsecs['veterans']=translate_inline("Veterans Club");
	$comsecs['hunterlodge']=translate_inline("Hunter's Lodge");
	$comsecs['gardens']=translate_inline("Gardens");
	$comsecs['waiting']=translate_inline("Clan Hall Waiting Area");
	if (getsetting("betaperplayer", 1) == 1 && @file_exists("pavilion.php")) {
		$comsecs['beta']=translate_inline("Pavilion");
	}
	tlschema();
	// All of the ones after this will be translated in the modules.
	$comsecs = modulehook("moderate", $comsecs);
	rawoutput(tlbutton_clear());
	return $comsecs;
}

function addcommentary() {
	global $session, $emptypost;
	$section = httppost('section');
	$talkline = httppost('talkline');
	$schema = httppost('schema');
	$comment = trim(httppost('insertcommentary'));
	$counter = httppost('counter');
	$remove = URLDecode(httpget('removecomment'));
	if ($remove>0) {
		$return = '/' . httpget('returnpath');
		$section = httpget('section');
        $sql = "SELECT deleted FROM ".db_prefix("commentary")." WHERE commentid=$remove";
		$row = db_fetch_assoc(db_query($sql));
		// $sql = "INSERT LOW_PRIORITY INTO ".db_prefix("moderatedcomments").
		// 	" (moderator,moddate,comment) VALUES ('{$session['user']['acctid']}','".date("Y-m-d H:i:s")."','".addslashes(serialize($row))."')";
		// db_query($sql);
		// $sql = "DELETE FROM ".db_prefix("commentary")." WHERE commentid='$remove';";
		if ($row['deleted'] == 0)
			$sql = "UPDATE ".db_prefix("commentary")." SET deleted = 1 WHERE commentid='$remove'";
		else
			$sql = "UPDATE ".db_prefix("commentary")." SET deleted = 0 WHERE commentid='$remove'";
		db_query($sql);
// 		invalidatedatacache("comments-$section");
// *** DRAGONBG.COM CORE PATCH START ***
// 		invalidatedatacache("comments-");
// *** DRAGONBG.COM CORE PATCH END ***
		$session['user']['specialinc']==''; //just to make sure he was not in a special
		$return = cmd_sanitize($return);
		$return = substr($return,strrpos($return,"/")+1);
		if (strpos($return,"?")===false && strpos($return,"&")!==false){
			$x = strpos($return,"&");
			$return = substr($return,0,$x-1)."?".substr($return,$x+1);
		}
		redirect($return);
	}
	if (array_key_exists('commentcounter',$session) &&
			$session['commentcounter']==$counter) {
		if ($section || $talkline || $comment) {
			$tcom = $comment;
			if ($tcom == "" || $tcom == ":" || $tcom == "::" || $tcom == "/me")
				$emptypost = 1;
			else injectcommentary($section, $talkline, $comment, $schema);
		}
	}
}

function injectsystemcomment($section,$comment) {
	//function lets gamemasters put in comments without a user association...be careful, it is not trackable who posted it
	if (strncmp($comment, "/game", 5) !== 0) {
		$comment = "/game" . $comment;
	}
	injectrawcomment($section,0,$comment);
}

function injectrawcomment($section, $author, $comment)
{
	$text = $comment;
	$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
	$text = "look at le reddit: reddit.come";
   	if (preg_match($reg_exUrl, $comment, $url)) {
		$text = "(:";
	}
	else
	{
		$reg_exUrl = "/(?i)\b((?:[a-z][\w-]+:(?:\/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?«»“”‘’]))/";
		if(preg_match($reg_exUrl, $comment, $url)) {
	   		$text = "(:";
		}
	   	else
		{
		   /*	$reg_exUrl = "(.com|.net|.info|.us)";
			if (preg_match($reg_exUrl, $comment, $url)){
				$text = "(:";
			}
		  	else
			{*/
		   		$text = $comment;
		   //	}
		}
	}
	//$text = preg_replace("/(\<(span) (class)\>)/", '<>', $text);
	$comment = str_replace(PHP_EOL, '', $comment);
    $comment = str_replace("\r", '', $comment);
	$comment = trim($comment);
	if ($section == "superuser")
		$comment = $text;
	$sql = "INSERT INTO " . db_prefix("commentary") . " (postdate,section,author,comment) VALUES ('".date("Y-m-d H:i:s")."','$section',$author,\"$comment\")";
	db_query($sql);
	invalidatedatacache("comments-{$section}");
	// invalidate moderation screen also.
// *** DRAGONBG.COM CORE PATCH START ***
	invalidatedatacache("comments-");
// *** DRAGONBG.COM CORE PATCH END ***
}

function injectcommentary($section, $talkline, $comment, $schema=false) {
	global $session,$doublepost, $timer_delay, $translation_namespace;
	if ($schema===false) $schema=$translation_namespace;
	// Make the comment pristine so that we match on it correctly.
	$comment = stripslashes(comment_sanitize($comment));
	tlschema("commentary");
	$doublepost=0;
	$timer_delay=0;
	$emptypost = 0;
	$colorcount = 0;
	if ($comment !="") {
		$commentary = str_replace("`n","",$comment);
		$y = strlen($commentary);
		for ($x=0;$x<$y;$x++){
			if (substr($commentary,$x,1)=="`"){
				$colorcount++;
				if ($colorcount>=getsetting("maxcolors",10)){
					$commentary = substr($commentary,0,$x).substr($commentary,$x);
					$x=$y;
				}
				$x++;
			}
		}

		$args = array('commentline'=>$commentary, 'commenttalk'=>$talkline, 'commentsection'=>$section);
		$args = modulehook("commentary", $args);
		$commentary = $args['commentline'];
		$talkline = $args['commenttalk'];
		tlschema($schema);
		$talkline = translate_inline($talkline);
		tlschema();

		//$commentary = preg_replace("'([^[:space:]]{45,45})([^[:space:]])'","\\1 \\2",$commentary);
		$commentary = addslashes($commentary);
		// do an emote if the area has a custom talkline and the user
		// isn't trying to emote already.
		if ($talkline!="says" && substr($commentary,0,1)!=":" &&
				substr($commentary,0,2)!="::" &&
				substr($commentary,0,3)!="/me" &&
				substr($commentary,0,5) != "/game") {
			$commentary = ":`3$talkline, \\\"`#$commentary`3\\\"";
		}
		if (substr($commentary,0,5)=="/game" && ($session['user']['superuser'] & SU_IS_GAMEMASTER)) {
			//handle game master inserts now, allow double posts
			injectsystemcomment($section,$commentary);
		} else {
			$sql = "SELECT comment,author,postdate FROM " . db_prefix("commentary") . " WHERE section='$section' AND author='{$session['user']['acctid']}' ORDER BY commentid DESC LIMIT 1";
			$result = db_query($sql);
			$row = db_fetch_assoc($result);
			db_free_result($result);
			if (isset($args['commentline']) && $args['commentline'] != get_module_pref("user_color","defaultcolor")){
				if (strtotime($row['postdate']) > strtotime('-3 minutes') && getsetting("post_timer","0") == 0)
				{
					$timer_delay = 1;
				}
				elseif ($row['comment'] == $commentary && $section != "contest_room")
				{
					$doublepost = 1;
				}
				else
				{
					injectrawcomment($section, $session['user']['acctid'],
							$commentary);
					$session['user']['laston']=date("Y-m-d H:i:s");
				}
			}else{
				invalidatedatacache("comments-{$section}");
			}
		}
		tlschema();
	}
}

function commentdisplay($intro, $section, $message="Interject your own commentary?",$limit=10,$talkline="says",$schema=false) {
	// Let's add a hook for modules to block commentary sections
	$args = modulehook("blockcommentarea", array("section"=>$section));
	if (isset($args['block']) && ($args['block'] == "yes"))
		return;

	if ($intro) output($intro);
	viewcommentary($section, $message, $limit, $talkline, $schema);
}

function displayooc()
{
	// COPY AND PASTE - DON'T GIVE A FUCK.

 	global $session,$REQUEST_URI,$doublepost, $timer_delay, $translation_namespace;
	global $emptypost,$SCRIPT_NAME;
	if (!$session['user']['prefs']['oocnews']) $session['user']['prefs']['oocnews'] = 0;
	if ($session['user']['prefs']['oocnews'] == 1) $hidenews = "AND author > 0 ";
		$sql = db_query("SELECT ". db_prefix("commentary") . ".*, " .
			db_prefix("accounts").".name, " .
			db_prefix("accounts").".acctid, " .
			db_prefix("accounts").".clanrank, " .
			db_prefix("clans") .  ".clanshort FROM " .
			db_prefix("commentary") . " LEFT JOIN " .
			db_prefix("accounts") . " ON " .
			db_prefix("accounts") .  ".acctid = " .
			db_prefix("commentary"). ".author LEFT JOIN " .
			db_prefix("clans") . " ON " .
			db_prefix("clans") . ".clanid=" .
			db_prefix("accounts") .
			".clanid WHERE section = 'globalooc' AND deleted = 0 $hidenews AND" .
			"( ".db_prefix("accounts") . ".locked=0 OR ".db_prefix("accounts") .".locked is null ) ".
			"ORDER BY commentid DESC LIMIT 0,10");
		if (db_num_rows($sql) > 0)
		{
		while ($row = db_fetch_assoc($sql)) $commentbuffer[] = $row;
			$commentbuffer = array_reverse($commentbuffer);
			output("`b`i`QOOC Chat`i`b`n");
			for ($i=0; $i < db_num_rows($sql); $i++){

				$clanrankcolors = array("`)","`&","`m","`^");
				$row = $commentbuffer[$i];
				$commentids[$i] = $row['commentid'];
				if (date("Y-m-d",strtotime($row['postdate']))==date("Y-m-d")){
					if ($row['name']==$session['user']['name']) $counttoday++;
				}
				$x=0;
				$ft="";
				for ($x=0;strlen($ft)<5 && $x<strlen($row['comment']);$x++){
					if (substr($row['comment'],$x,1)=="`" && strlen($ft)==0) {
						$x++;
					}else{
						$ft.=substr($row['comment'],$x,1);
					}
				}

				$link = "bio.php?char=" . $row['acctid'] .
					"&ret=".URLEncode($_SERVER['REQUEST_URI']);

				if (substr($ft,0,2)=="::")
					$ft = substr($ft,0,2);
				elseif (substr($ft,0,1)==":")
					$ft = substr($ft,0,1);
				elseif (substr($ft,0,3)=="/me")
					$ft = substr($ft,0,3);

				$row['comment'] = str_replace('``', '`', $row['comment']);
				$row['comment'] = holidayize($row['comment'],'comment');
				if (substr_count($row['comment'], '`i') % 2 != 0) {
					$row['comment'] .= '`i';
				}
				if (substr_count($row['comment'], '`b') % 2 != 0) {
					$row['comment'] .= '`b';
				}
				$row['name'] = holidayize($row['name'],'comment');
				if ($row['clanrank']) {
					$clanrankcolors = array("`)","`&","`m","`^");
					$row['name'] = ($row['clanshort']>""?"`)".$clanrankcolors[floor($row['clanrank']/10)]."&lt;`2{$row['clanshort']}`)".$clanrankcolors[floor($row['clanrank']/10)]."&gt; `&":"").$row['name'];
				}
				$linkbios = true;
				if ($ft=="::" || $ft=="/me" || $ft==":"){
					$x = strpos($row['comment'],$ft);
					if ($x!==false){
						if ($linkbios)
							$op[$i] = str_replace("&amp;","&",htmlentities(substr($row['comment'],0,$x), ENT_COMPAT, getsetting("charset", "ISO-8859-1")))."`0<a href='$link' style='text-decoration: none'>\n`&{$row['name']}`0</a>\n`& ".str_replace("&amp;","&",htmlentities(substr($row['comment'],$x+strlen($ft)), ENT_COMPAT, getsetting("charset", "ISO-8859-1")))."`0`n";
						else
							$op[$i] = str_replace("&amp;","&",htmlentities(substr($row['comment'],0,$x), ENT_COMPAT, getsetting("charset", "ISO-8859-1")))."`0`&{$row['name']}`0`& ".str_replace("&amp;","&",htmlentities(substr($row['comment'],$x+strlen($ft)), ENT_COMPAT, getsetting("charset", "ISO-8859-1")))."`0`n";
						$rawc[$i] = str_replace("&amp;","&",htmlentities(substr($row['comment'],0,$x), ENT_COMPAT, getsetting("charset", "ISO-8859-1")))."`0`&{$row['name']}`0`& ".str_replace("&amp;","&",htmlentities(substr($row['comment'],$x+strlen($ft)), ENT_COMPAT, getsetting("charset", "ISO-8859-1")))."`0`n";
					}
				}
				if ($ft=="/game" && !$row['name']) {
					$x = strpos($row['comment'],$ft);
					if ($x!==false){
					 $op[$i] = str_replace("&amp;","&",htmlentities(substr($row['comment'],0,$x), ENT_COMPAT, getsetting("charset", "ISO-8859-1")))."`0`&".str_replace("&amp;","&",htmlentities(substr($row['comment'],$x+strlen($ft)), ENT_COMPAT, getsetting("charset", "ISO-8859-1")))."`0`n";
					}
				}
				if (!isset($op) || !is_array($op)) $op = array();
				if (!array_key_exists($i,$op) || $op[$i] == "")  {
					if ($linkbios && ($SCRIPT_NAME != "runmodule.php" && httpget('module') != "inboxpetitions"))
						$op[$i] = "`0<a href='$link' style='text-decoration: none'>`&{$row['name']}`0</a>`3 says, \"`#".str_replace("&amp;","&",htmlentities($row['comment'], ENT_COMPAT, getsetting("charset", "ISO-8859-1")))."`3\"`0`n";
					elseif (substr($ft,0,5)=='/game' && !$row['name'])
						$op[$i] = str_replace("&amp;","&",htmlentities($row['comment'], ENT_COMPAT, getsetting("charset", "ISO-8859-1")));
					else
						$op[$i] = "`&{$row['name']}`3 says, \"`#".str_replace("&amp;","&",htmlentities($row['comment'], ENT_COMPAT, getsetting("charset", "ISO-8859-1")))."`3\"`0`n";
					$rawc[$i] = "`&{$row['name']}`3 says, \"`#".str_replace("&amp;","&",htmlentities($row['comment'], ENT_COMPAT, getsetting("charset", "ISO-8859-1")))."`3\"`0`n";
				}

				if (!array_key_exists('timestamp', $session['user']['prefs']))
					$session['user']['prefs']['timestamp'] = 0;

				$session['user']['prefs']['timeoffset'] = round($session['user']['prefs']['timeoffset'],1);

				if ($session['user']['prefs']['timestamp']==1) {
					if (!isset($session['user']['prefs']['timeformat'])) $session['user']['prefs']['timeformat'] = "[m/d h:ia]";
					$time = strtotime($row['postdate']) + ($session['user']['prefs']['timeoffset'] * 60 * 60);
					$s=date("`7" . $session['user']['prefs']['timeformat'] . "`0 ",$time);
					$op[$i] = $s.$op[$i];
				} elseif ($session['user']['prefs']['timestamp']==2) {
					$s=reltime(strtotime($row['postdate']));
					$op[$i] = "<font size='1px'>`)($s)</font>`0 ".$op[$i];
				}
				if ($message=="X")
					$op[$i]="`0({$row['section']}) ".$op[$i];

				addnav("",$link);
				$auth[$i] = $row['author'];
				if (isset($rawc[$i])) {
					$rawc[$i] = $rawc[$i];
					$rawc[$i] = htmlentities($rawc[$i], ENT_QUOTES, getsetting("charset", "ISO-8859-1"));
				}
				output($op[$i],true);
			}
		}
}

function viewcommentary($section,$message="Interject your own commentary?",$limit=10,$talkline="says",$schema=false) {
 	global $session,$REQUEST_URI,$doublepost, $timer_delay, $translation_namespace;
	global $emptypost,$SCRIPT_NAME;
	require_once('lib/names.php');

//	output("`b`i`QCurrent Chat`b`i`n");
// 	debug($SCRIPT_NAME);

// *** AJAX CHAT MOD START ***
	if($section) {
		$args1 = array('section'=>$section,'talkline'=>$talkline,'schema'=>$schema);
		$args2 = modulehook("viewcommentaryheader", $args1);
	}
// *** AJAX CHAT MOD END ***

// *** DRAGONBG.COM CORE PATCH START ***
	if($section) {
		rawoutput("<a name='$section'></a>");

		// Let's add a hook for modules to block commentary sections
		$args = modulehook("blockcommentarea", array("section"=>$section));
		if (isset($args['block']) && ($args['block'] == "yes"))
			return;
	}
// *** DRAGONBG.COM CORE PATCH END ***

	if ($schema === false)
		$schema=$translation_namespace;
	tlschema("commentary");

	$nobios = array("motd.php"=>true);
	if (!array_key_exists(basename($_SERVER['SCRIPT_NAME']),$nobios)) $nobios[basename($_SERVER['SCRIPT_NAME'])] = false;
	if ($nobios[basename($_SERVER['SCRIPT_NAME'])])
		$linkbios=false;
	else
		$linkbios=true;

	if ($message=="X") $linkbios=true;

//	if ($doublepost) output("`\$`bDouble post?`b`0`n");
	if ($timer_delay) output("`\$`bYou need to wait three minutes before you can post.`b`0`n");
	if ($emptypost) output("`\$`bWell, they say silence is a virtue.`b`0`n");

// *** DRAGONBG.COM CORE PATCH START ***
	// $clanrankcolors=array(CLAN_APPLICANT=>"`!",CLAN_MEMBER=>"`#",CLAN_OFFICER=>"`^",CLAN_LEADER=>"`&", CLAN_FOUNDER=>"`\$");
	$args = modulehook("clanrankcolors", array("clanrankcolors"=>$clanrankcolors, "clanid"=>$session['user']['clanid']));
	// $clanrankcolors = $args['clanrankcolors'];
	$clanrankcolors = array("`)","`&","`m","`^");
// *** DRAGONBG.COM CORE PATCH END ***


	// Needs to be here because scrolling through the commentary pages, entering a bio, then scrolling again forward
	// then re-entering another bio will lead to $com being smaller than 0 and this will lead to an SQL error later on.
	$com=(int)httpget("comscroll");
	if ($com < 0) $com = 0;
	$cc = false;
	if (httpget("comscroll") !==false && (int)$session['lastcom']==$com+1)
		$cid = (int)$session['lastcommentid'];
	else
		$cid = 0;

	$session['lastcom'] = $com;
 	if (!isset($session['user']['prefs']['togglecc'])) $session['user']['prefs']['togglecc'] = 1;
//  	if (!isset($session['user']['prefs']['toggleooc'])) $session['user']['prefs']['toggleooc'] = 0;
 	if ($section == 'globalooc' && $session['user']['clanid'] && $session['user']['prefs']['togglecc'] == 1 && !strstr($_SERVER['REQUEST_URI'], "trivia") && (!preg_match('/^pet-*/',$section) && $section != "superuser")) $additionalsections .= "OR section='clan-".$session['user']['clanid']."' ";
 	if ($session['user']['prefs']['toggleooc'] == 1 && !strstr($_SERVER['REQUEST_URI'], "trivia") && (!preg_match('/^pet-*/',$section) && $section != "superuser")) $additionalsections .= "OR section='globalooc'";
 	if (!($session['user']['superuser'] & SU_EDIT_COMMENTS) || ($session['user']['superuser'] & SU_EDIT_COMMENTS && $session['user']['prefs']['hide_deleted'] == 0)) $hideremoved = "AND deleted = 0";
 		else $hideremoved = "";

	if ($com > 0 || $cid > 0) {
		// Find newly added comments.
		$sql = "SELECT COUNT(commentid) AS newadded FROM " .
			db_prefix("commentary") . " LEFT JOIN " .
			db_prefix("accounts") . " ON " .
			db_prefix("accounts") . ".acctid = " .
			db_prefix("commentary"). ".author WHERE section='$section' AND" .
			"(".db_prefix("accounts").".locked=0 or ".db_prefix('accounts').".locked is null) AND commentid > '$cid'";
		$result = db_query($sql);
		$row = db_fetch_assoc($result);
		$newadded = $row['newadded'];

	} else {
		$newadded = 0;
	}

	$commentbuffer = array();

	if (strstr( $_SERVER['REQUEST_URI'], "/moderate.php" ) === $_SERVER['REQUEST_URI'] )
	{
		$sql = "SELECT ". db_prefix("commentary") . ".*, " .
			db_prefix("accounts").".name, " .
			db_prefix("accounts").".acctid, " .
			db_prefix("accounts").".clanrank, " .
			db_prefix("clans") .  ".clanshort FROM " .
			db_prefix("commentary") . " LEFT JOIN " .
			db_prefix("accounts") . " ON " .
			db_prefix("accounts") .  ".acctid = " .
			db_prefix("commentary"). ".author LEFT JOIN " .
			db_prefix("clans") . " ON " .
			db_prefix("clans") . ".clanid=" .
			db_prefix("accounts") .
			".clanid WHERE " .
			"( ".db_prefix("accounts") . ".locked=0 OR ".db_prefix("accounts") .".locked is null ) ".
			"ORDER BY commentid DESC LIMIT " .
			($com*$limit).",$limit";
		if ($com==0 && strstr( $_SERVER['REQUEST_URI'], "/moderate.php" ) !== $_SERVER['REQUEST_URI'] )
			$result = db_query($sql,"comments-{$section}");
		else
			$result = db_query($sql);
		while($row = db_fetch_assoc($result)) $commentbuffer[] = $row;
	}
	else
	if ($cid == 0) {
		$sql = "SELECT ". db_prefix("commentary") . ".*, " .
			db_prefix("accounts").".name, " .
			db_prefix("accounts").".acctid, " .
			db_prefix("accounts").".clanrank, " .
			db_prefix("clans") .  ".clanshort FROM " .
			db_prefix("commentary") . " LEFT JOIN " .
			db_prefix("accounts") . " ON " .
			db_prefix("accounts") .  ".acctid = " .
			db_prefix("commentary"). ".author LEFT JOIN " .
			db_prefix("clans") . " ON " .
			db_prefix("clans") . ".clanid=" .
			db_prefix("accounts") .
			".clanid WHERE section = '$section' $hideremoved AND" .
			"( ".db_prefix("accounts") . ".locked=0 OR ".db_prefix("accounts") .".locked is null ) ".
			"ORDER BY commentid DESC LIMIT " .
			($com*$limit).",$limit";
		// *** DRAGONBG.COM CORE PATCH END ***

		if ($com==0 && strstr( $_SERVER['REQUEST_URI'], "/moderate.php" ) !== $_SERVER['REQUEST_URI'] )
			$result = db_query($sql,"comments-{$section}");
		else
			$result = db_query($sql);
		while($row = db_fetch_assoc($result)) $commentbuffer[] = $row;
	} else {

	// *** DRAGONBG.COM CORE PATCH START ***
		$sql = "SELECT " . db_prefix("commentary") . ".*, " .
			db_prefix("accounts").".name, " .
			db_prefix("accounts").".acctid, " .
			db_prefix("accounts").".clanrank, " .
			db_prefix("clans").".clanshort FROM " .
			db_prefix("commentary") . " LEFT JOIN " .
			db_prefix("accounts") . " ON " .
			db_prefix("accounts") . ".acctid = " .
			db_prefix("commentary"). ".author LEFT JOIN " .
			db_prefix("clans") . " ON " . db_prefix("clans") . ".clanid=" .
			db_prefix("accounts") .
			".clanid WHERE section='$section' $hideremoved AND".
			"( ".db_prefix("accounts") . ".locked=0 OR ".db_prefix("accounts") .".locked is null ) ".
			"AND commentid > '$cid' " .
			"ORDER BY commentid ASC LIMIT $limit";
	// *** DRAGONBG.COM CORE PATCH END ***

		$result = db_query($sql);
		while ($row = db_fetch_assoc($result)) $commentbuffer[] = $row;
		$commentbuffer = array_reverse($commentbuffer);
	}

	$rowcount = count($commentbuffer);
	if ($rowcount > 0)
		$session['lastcommentid'] = $commentbuffer[0]['commentid'];

	$counttoday=0;
	for ($i=0; $i < $rowcount; $i++){
		$row = $commentbuffer[$i];
		$commentids[$i] = $row['commentid'];
		if (date("Y-m-d",strtotime($row['postdate']))==date("Y-m-d")){
			if ($row['name']==$session['user']['name']) $counttoday++;
		}
		$x=0;
		$ft="";
		for ($x=0;strlen($ft)<5 && $x<strlen($row['comment']);$x++){
			if (substr($row['comment'],$x,1)=="`" && strlen($ft)==0) {
				$x++;
			}else{
				$ft.=substr($row['comment'],$x,1);
			}
		}

		$link = "bio.php?char=" . $row['acctid'] .
			"&ret=".URLEncode($_SERVER['REQUEST_URI']);

		if (substr($ft,0,2)=="::")
			$ft = substr($ft,0,2);
		elseif (substr($ft,0,1)==":")
			$ft = substr($ft,0,1);
		elseif (substr($ft,0,3)=="/me")
			$ft = substr($ft,0,3);

		$row['comment'] = holidayize($row['comment'],'comment');
		$row['name'] = holidayize($row['name'],'comment');
		if ($row['clanrank']) {
			$row['name'] = ($row['clanshort']>""?"`)".$clanrankcolors[floor($row['clanrank']/10)]."&lt;`2{$row['clanshort']}`)".$clanrankcolors[floor($row['clanrank']/10)]."&gt; `&":"").$row['name'];
		}

		$linkbios = true;
		if ($row['deleted'] != 0) $pre = "<del style='color: #CCC;'>";
		if ($ft=="::" || $ft=="/me" || $ft==":"){
			$x = strpos($row['comment'],$ft);
			if ($x!==false){
				if ($linkbios)
					$op[$i] = substr($row['comment'], 0, $x)."`0<a href='$link' style='text-decoration: none'>\n`&".$row['name']."`0</a>\n`& ".substr($row['comment'],$x+strlen($ft))."`0`n";
				else
					$op[$i] = substr($row['comment'], 0, $x)."`0`&".$row['name']."`0`& ".substr($row['comment'],$x+strlen($ft))."`0`n";
				$rawc[$i] = substr($row['comment'], 0, $x)."`0`&".$row['name']."`0`& ".substr($row['comment'],$x+strlen($ft))."`0`n";
			}
		}
		if ($ft=="/game" && !$row['name']) {
			$x = strpos($row['comment'],$ft);
			if ($x!==false){
			 $op[$i] = str_replace("&amp;","&",htmlentities(substr($row['comment'],0,$x), ENT_COMPAT, getsetting("charset", "ISO-8859-1")))."`0`&".str_replace("&amp;","&",htmlentities(substr($row['comment'],$x+strlen($ft)), ENT_COMPAT, getsetting("charset", "ISO-8859-1")))."`0`n";
			}
		}
		if (!isset($op) || !is_array($op)) $op = array();
		if (!array_key_exists($i,$op) || $op[$i] == "")  {
			if ($linkbios && ($SCRIPT_NAME != "runmodule.php" && httpget('module') != "inboxpetitions"))
				$op[$i] = "`0<a href='$link' style='text-decoration: none'>`&{$row['name']}`0</a>`3 says, \"`#".str_replace("&amp;","&",htmlentities($row['comment'], ENT_COMPAT, getsetting("charset", "ISO-8859-1")))."`3\"`0`n";
			elseif (substr($ft,0,5)=='/game' && !$row['name'])
				$op[$i] = $row['comment'];
			else
				$op[$i] = "`&".$row['name']."`3 says, \"`#{$row['comment']}`3\"`0`n";
			$rawc[$i] = "`&".$row['name']."`3 says, \"`#{$row['comment']}`3\"`0`n";
		}
		if ($row['deleted'] != 0) $post = "</del>";
		$op[$i] = $pre.$op[$i].$post;
		unset($pre);
		unset($post);

		if (!array_key_exists('timestamp', $session['user']['prefs']))
			$session['user']['prefs']['timestamp'] = 0;

		$session['user']['prefs']['timeoffset'] = round($session['user']['prefs']['timeoffset'],1);

		if ($row['section'] == 'clan-'.$session['user']['clanid'] && $section == 'globalooc') $op[$i] = "`^[Guild] ".$op[$i];
		if ($row['section'] == 'globalooc' && $section != 'globalooc') $op[$i] = "`Q[OOC] ".$op[$i];

		if ($session['user']['prefs']['timestamp']==1) {
			if (!isset($session['user']['prefs']['timeformat'])) $session['user']['prefs']['timeformat'] = "[m/d h:ia]";
			$time = strtotime($row['postdate']) + ($session['user']['prefs']['timeoffset'] * 60 * 60);
			$s=date("`7" . $session['user']['prefs']['timeformat'] . "`0 ",$time);
			$op[$i] = $s.$op[$i];
		} elseif ($session['user']['prefs']['timestamp']==2) {
			$s=reltime(strtotime($row['postdate']));
			$op[$i] = "<font size='1px'>`)($s)</font>`0 ".$op[$i];
		}
		if ($message=="X")
			$op[$i]="`0({$row['section']}) ".$op[$i];

		addnav("",$link);
		$auth[$i] = $row['author'];
		if (isset($rawc[$i])) {
			$rawc[$i] = $rawc[$i];
			$rawc[$i] = htmlentities($rawc[$i], ENT_QUOTES, getsetting("charset", "ISO-8859-1"));
		}
	}
	$i--;
	$outputcomments=array();
	$sect="x";

	$moderating=false;
	if (($session['user']['superuser'] & SU_EDIT_COMMENTS) && $message=="X")
		$moderating=true;

	$del=translate_inline("Del");
	$scriptname=substr($_SERVER['SCRIPT_NAME'],strrpos($_SERVER['SCRIPT_NAME'],"/")+1);
	$pos=strpos($_SERVER['REQUEST_URI'],"?");
	$return=$scriptname.($pos==false?"":substr($_SERVER['REQUEST_URI'],$pos));
	$one=(strstr($return,"?")==false?"?":"&");

	for (;$i>=0;$i--){
		$out="";
		if ($moderating){
			if ($session['user']['superuser'] & SU_EDIT_USERS){
				$out.="`0[ <input type='checkbox' name='comment[{$commentids[$i]}]'> | <a href='user.php?op=setupban&userid=".$auth[$i]."&reason=".rawurlencode($rawc[$i])."'>Ban</a> ]&nbsp;";
				addnav("","user.php?op=setupban&userid=$auth[$i]&reason=".rawurlencode($rawc[$i]));
			}else{
				$out.="`0[ <input type='checkbox' name='comment[{$commentids[$i]}]'> ]&nbsp;";
			}
			$matches=array();
			preg_match("/[(]([^)]*)[)]/",$op[$i],$matches);
			$sect=trim($matches[1]);
			if (substr($sect,0,5)!="clan-" || $sect==$section){
				if (substr($sect,0,4)!="pet-"){
					$out.=$op[$i];
					if (!isset($outputcomments[$sect]) ||
							!is_array($outputcomments[$sect]))
						$outputcomments[$sect]=array();
					array_push($outputcomments[$sect],$out);
				}
			}
		}else{

			/*if ($session['user']['superuser'] & SU_EDIT_COMMENTS) {
				$out.="`2[<a href='".$return.$one."removecomment={$commentids[$i]}&section=$section&returnpath=".URLEncode($return)."'>$del</a>`2]`0&nbsp;";
				addnav("",$return.$one."removecomment={$commentids[$i]}&section=$section&returnpath=".URLEncode($return)."");
			}*/
			if ($session['user']['superuser'] & SU_EDIT_COMMENTS && $session['user']['superuser'] & SU_EDIT_USERS){
				$out.="`2[<a href='".$return.$one."removecomment={$commentids[$i]}&section=$section&returnpath=".URLEncode($return)."'>Del</a>`2] [";
				addnav("",$return.$one."removecomment={$commentids[$i]}&section=$section&returnpath=".URLEncode($return)."");
                $out .= "`2<a href='user.php?op=edit&userid={$auth[$i]}'>E</a>`2]`0&nbsp;";
                addnav("", "user.php?op=edit&userid={$auth[$i]}");
			} elseif ($session['user']['superuser'] & SU_EDIT_COMMENTS) {
				$out.="`2[<a href='".$return.$one."removecomment={$commentids[$i]}&section=$section&returnpath=".URLEncode($return)."'>X</a>`2]`0&nbsp;";
				addnav("",$return.$one."removecomment={$commentids[$i]}&section=$section&returnpath=".URLEncode($return)."");
			} elseif ($session['user']['superuser'] & SU_EDIT_USERS) {
                $out .= "`2[<a href='user.php?op=edit&userid={$auth[$i]}'>E</a>`2]`0&nbsp;";
                addnav("", "user.php?op=edit&userid={$auth[$i]}");
            }
            if (!($session['user']['superuser'] & SU_EDIT_COMMENTS) && $session['user']['prefs']['deputy_moderator'] == 0) $out .= "`2[<a href='petition.php?angelus={$commentids[$i]}' target='_blank'>`\$R</a>`2] ";
			if ($session['user']['prefs']['spacedchat'] == 1 && $section != 'globalooc') $out.=$op[$i]."`n";
				else $out.=$op[$i];
			if (!array_key_exists($sect,$outputcomments) || !is_array($outputcomments[$sect]))
				$outputcomments[$sect]=array();
			array_push($outputcomments[$sect],$out);
		}
	}

	if ($moderating){
		$scriptname=substr($_SERVER['SCRIPT_NAME'],strrpos($_SERVER['SCRIPT_NAME'],"/")+1);
		addnav("","$scriptname?op=commentdelete&return=".URLEncode($_SERVER['REQUEST_URI']));
		$mod_Del1 = htmlentities(translate_inline("Delete Checked Comments"), ENT_COMPAT, getsetting("charset", "ISO-8859-1"));
		$mod_Del2 = htmlentities(translate_inline("Delete Checked & Ban (3 days)"), ENT_COMPAT, getsetting("charset", "ISO-8859-1"));
		$mod_Del_confirm = addslashes(htmlentities(translate_inline("Are you sure you wish to ban this user and have you specified the exact reason for the ban, i.e. cut/pasted their offensive comments?"), ENT_COMPAT, getsetting("charset", "ISO-8859-1")));
		$mod_reason = translate_inline("Reason:");
		$mod_reason_desc = htmlentities(translate_inline("Banned for comments you posted."), ENT_COMPAT, getsetting("charset", "ISO-8859-1"));

	// *** DRAGONBG.COM CORE PATCH START ***
		output_notl("<form action='$scriptname?op=commentdelete&return=".URLEncode($_SERVER['REQUEST_URI'])."' method='POST' id='comments'>",true);
	// *** DRAGONBG.COM CORE PATCH END ***

		output_notl("<input type='submit' class='button' value=\"$mod_Del1\">",true);
		output_notl("<input type='submit' class='button' name='delnban' value=\"$mod_Del2\" onClick=\"return confirm('$mod_Del_confirm');\">",true);

	// *** DRAGONBG.COM CORE PATCH START ***
		$mod_checkall = htmlspecialchars(translate_inline("Check all"));
		// Check all - by Pr0t3ct0r
		output_notl('<script type="text/javascript">function checkall() {
			var isChecked = document.getElementById("CheckAllID").checked
			var f = document.getElementById("comments")
			for (var i=0;i<f.length;i++) {
			  if (f.elements[i].type == "checkbox"){
				  f.elements[i].checked = isChecked
			  }
			}
		}</script>',true);
		output_notl("<input type='checkbox' id='CheckAllID' onclick='checkall()' /> $mod_checkall", true);
		output_notl("`n$mod_reason <input name='reason0' id='reason0' size='40' value=\"$mod_reason_desc\" onChange=\"document.getElementById('reason').value=this.value;\">",true);
	// *** DRAGONBG.COM CORE PATCH END ***

	}


	//output the comments
	ksort($outputcomments);
	reset($outputcomments);
	$sections = commentarylocs();
	$needclose = 0;

	// *** AJAX CHAT MOD START ***
	//rawoutput('<div id="commentarydisplaydiv">');
	// *** AJAX CHAT MOD END ***

	while (list($sec,$v)=each($outputcomments)){
		if ($sec!="x") {
			if($needclose) modulehook("}collapse");
			output_notl("`n<hr><a href='moderate.php?area=%s'>`b`^%s`0`b</a>`n",
				$sec, isset($sections[$sec]) ? $sections[$sec] : "($sec)", true);
			addnav("", "moderate.php?area=$sec");
			modulehook("collapse{",array("name"=>"com-".$sec));
			$needclose = 1;
		} else {
			modulehook("collapse{",array("name"=>"com-".$section));
			$needclose = 1;
		}
		reset($v);
		while (list($key,$val)=each($v)){
			$args = array('commentline'=>$val);
			$args = modulehook("viewcommentary", $args);
			$val = $args['commentline'];
			//output_notl("<span class='commentaryLine'>$val</span>", true);
			output("<div class='commentaryLine'>$val</div>", true);
		}
	}

	// *** AJAX CHAT MOD START ***
//	if (round($limit/2,0)-$counttoday < 3){
//		output("`)(You have %s posts left today)`n`0",(round($limit/2,0)-$counttoday));
//	}
	//rawoutput('</div>');
	// *** AJAX CHAT MOD END ***

	if ($moderating && $needclose) {
		modulehook("}collapse");
		$needclose = 0;
	}

	modulehook('endofcommentary');
	if ($moderating){
		output_notl("`n");
		rawoutput("<input type='submit' class='button' value=\"$mod_Del1\">");
		rawoutput("<input type='submit' class='button' name='delnban' value=\"$mod_Del2\" onClick=\"return confirm('$mod_Del_confirm');\">");
		output_notl("`n%s ", $mod_reason);

	// *** DRAGONBG.COM CORE PATCH START ***
		rawoutput("<input name='reason' size='40' id='reason' value=\"$mod_reason_desc\" onchange=\"document.getElementById('reason0').value=this.value;\">");
	// *** DRAGONBG.COM CORE PATCH END ***

		rawoutput("</form>");
		output_notl("`n");
	}

	if ($session['user']['loggedin']) {
		$args = modulehook(
			"insertcomment",
			['section' => $section]
		);
		if (array_key_exists("mute",$args) && $args['mute'] &&
				!($session['user']['superuser'] & SU_EDIT_COMMENTS)) {
			output_notl("%s", $args['mutemsg']);
		} elseif ($counttoday<($limit/2) ||
				($session['user']['superuser']&~SU_DOESNT_GIVE_GROTTO)
				|| !getsetting('postinglimit',1)){
			if ($message!="X"){
				$message="`n$message";
				//output($message);
                rawoutput("<jquerycommentaryend></jquerycommentaryend>");
				talkform($section,$talkline,$limit,$schema);
			}
		}else{
			$message="`@$message";
			output($message);
			output("Sorry, you've exhausted your posts in this section for now.`0`n");
		}
	}

	$jump = false;
	if (!isset($session['user']['prefs']['nojump']) || $session['user']['prefs']['nojump'] == false) {
		$jump = true;
	}

//	$firstu = translate_inline("&lt;&lt; First Unseen");
	$prev = translate_inline("&lt; Previous");
	$ref = translate_inline("Refresh");
	$next = translate_inline("Next &gt;");
//	$lastu = translate_inline("Last Page &gt;&gt;");
	output("`c");
	if ($rowcount>=$limit || $cid>0){
		$sql = "SELECT count(commentid) AS c FROM " . db_prefix("commentary") . " WHERE section='$section' AND postdate > '{$session['user']['recentcomments']}'";
		$r = db_query($sql);
		$val = db_fetch_assoc($r);
		$val = round($val['c'] / $limit + 0.5,0) - 1;
//		if ($val>0){
//			$first = comscroll_sanitize($REQUEST_URI)."&comscroll=".($val);
//			$first = str_replace("?&","?",$first);
//			if (!strpos($first,"?")) $first = str_replace("&","?",$first);
//			$first .= "&refresh=1";
//			if ($jump) {
//				$first .= "#$section";
//			}
//			output_notl("<a href=\"$first\">$firstu</a>",true);
//			addnav("",$first);
//		}else{
//			output_notl($firstu,true);
//		}
		$req = comscroll_sanitize($REQUEST_URI)."&comscroll=".($com+1);
		$req = str_replace("?&","?",$req);
		if (!strpos($req,"?")) $req = str_replace("&","?",$req);
		$req .= "&refresh=1";
		if ($jump) {
			$req .= "#$section";
		}
		output_notl("<a href=\"$req\">$prev</a>",true);
		addnav("",$req);
	}else{
		output_notl("$prev",true);
	}
	$last = appendlink(comscroll_sanitize($REQUEST_URI),"refresh=1");

	// Okay.. we have some smart-ass (or stupidass, you guess) players
	// who think that the auto-reload firefox plugin is a good way to
	// avoid our timeouts.  Won't they be surprised when I take that little
	// hack away.
	$last = appendcount($last);

	$last = str_replace("?&","?",$last);
	//if (!strpos($last,"?")) $last = str_replace("&","?",$last);
	//debug($last);
		rawoutput("
		<script language='JavaScript'>
			document.onkeydown = function(e) {
				var object;

				e = e || window.event;
				object = e.originalTarget || window.event.srcElement;

				if (object.nodeName.toLowerCase()=='input' || object.nodeName.toLowerCase()=='textarea') return;
				if (e.ctrlKey || e.altKey || e.shiftKey) return;

				switch (e.keyCode) {
					case 115:
					case 116: // Inbox
						window.location='$last';
						return false;
					break;
				}
			}
			</script>");
	output_notl("&nbsp;<a href=\"$last\">$ref</a>&nbsp;",true);
	addnav("",$last);
	if ($com > 0){
		$req = comscroll_sanitize($REQUEST_URI)."&comscroll=".($com-1);
		$req = str_replace("?&","?",$req);
		if (!strpos($req,"?")) $req = str_replace("&","?",$req);
		$req .= "&refresh=1";
		if ($jump) {
			$req .= "#$section";
		}
		output_notl(" <a href=\"$req\">$next</a>",true);
		addnav("",$req);
//		output_notl(" <a href=\"$last\">$lastu</a>",true);
	}
	else{
		output_notl("$next",true);
	}
	output("`c");
	if (!$cc) db_free_result($result);
	tlschema();
	if ($needclose) modulehook("}collapse");

	// *** AJAX CHAT MOD START ***
	modulehook("viewcommentaryfooter");
	// *** AJAX CHAT MOD END ***
}

function talkform($section,$talkline,$limit=10,$schema=false){
	require_once("lib/forms.php");
	global $REQUEST_URI,$session,$translation_namespace;
	if ($schema===false) $schema=$translation_namespace;
	tlschema("commentary");

	$jump = false;
	if (isset($session['user']['prefs']['nojump']) && $session['user']['prefs']['nojump'] == true) {
		$jump = true;
	}

	$counttoday=0;
	if (substr($section,0,5)!="clan-"){
		$sql = "SELECT author FROM " . db_prefix("commentary") . " WHERE section='$section' AND postdate>'".date("Y-m-d 00:00:00")."' ORDER BY commentid DESC LIMIT $limit";
		$result = db_query($sql);
		while ($row=db_fetch_assoc($result)){
			if ($row['author']==$session['user']['acctid']) $counttoday++;
		}
		if (round($limit/2,0)-$counttoday <= 0 && getsetting('postinglimit',1)){
			if ($session['user']['superuser']&~SU_DOESNT_GIVE_GROTTO){
				output("`n`)(You'd be out of posts if you weren't a superuser or moderator.)`n");
			}else{
				output("`n`)(You are out of posts for the time being.  Once some of your existing posts have moved out of the comment area, you'll be allowed to post again.)`n");
				return false;
			}
		}
	}
	if (translate_inline($talkline,$schema)!="says")
		$tll = strlen(translate_inline($talkline,$schema))+11;
		else $tll=0;
	$req = comscroll_sanitize($REQUEST_URI)."&comment=1";
	$req = str_replace("?&","?",$req);
	if (!strpos($req,"?")) $req = str_replace("&","?",$req);
	if ($jump) {
		$req .= "#$section";
	}
	addnav("",$req);

	// *** AJAX CHAT MOD START ***
	//output_notl("<form action=\"$req\" method='POST' autocomplete='false'>",true);
	if ($session['user']['acctid'] == 779)
	{
	$args1 = array(
			"formline"	=>	"<form action=\"$req\" id='commentaryform' method='post' autocomplete='false'",
			"section"	=>	$section,
			"talkline"	=>	$talkline,
			"schema"	=>	$schema
		);
	}
	else
	{
		$args1 = array(
			"formline"	=>	"<form action=\"$req\" id='jquerycommentaryform' method='post' autocomplete='false' onSubmit='jquerypostcommentary();return false;'",
			"section"	=>	$section,
			"talkline"	=>	$talkline,
			"schema"	=>	$schema
		);
	}
		$args1 = modulehook("commentarytalkline",$args1);
		if (get_module_pref('user_disable', 'jquerycommentary') == 0 && httpget('comscroll') == '') {
			rawoutput('<div id="commentaryformcontainer">
					<div class="is_typing">
						<span class="colLtGreen">
							Who\'s typing:
							<br />
							No one
							<br />
						</span>
						<br />
				</div> ');
		}
		else {
			rawoutput('<div id="commentaryformcontainer">');
		}
		output_notl($args1['formline'] . ">",true);
	// *** AJAX CHAT MOD END ***

	$add = htmlentities(translate_inline("Add"), ENT_QUOTES, getsetting("charset", "ISO-8859-1"));

	output("",true);
	if (!check_user_agent('mobile')){
		previewfield("insertcommentary", $session['user']['name'], $talkline, true, array("type"=>"textarea", "class"=>"input", "placeholder"=>"Xythen Chat Commands: (note: replace WORDS with your text)\rOOC: /ooc WORDS or :ooc WORDS \rRemove Last Post (within 60 secs): /nvm \rNeed to leave? /afk \rHave you returned? /back \rEdit your last post with /edit ", "cols"=>"90", "rows"=>"7"), false, true);
	} else {
		previewfield("insertcommentary", $session['user']['name'], $talkline, true, array("type"=>"textarea", "class"=>"input", "placeholder"=>"Xythens Chat Commands:
																			Remove a comment with /nvm as long as it was posted less than 60 seconds ago.
									Talk in OOC chat with /ooc, perform actions in OOC with :ooc
													Talk in your guild with /g, perform actions with :g
														Let players know you are AFK with /afk, and when you are back with /back
													Edit your last post with /edit", "cols"=>"90", "rows"=>"7"), false, true);
	}
	output("",true);
	// *** DRAGONBG.COM CORE PATCH START***
	output_notl("<input type='submit' class='button' value='$add'>",true);
	rawoutput("<input type='hidden' name='talkline' value='$talkline'>");
	rawoutput("<input type='hidden' name='schema' value='$schema'>");
	rawoutput("<input type='hidden' name='counter' value='{$session['counter']}'>");
	$session['commentcounter'] = $session['counter'];
	if ($section=="X"){
		$vname = getsetting("villagename", LOCATION_FIELDS);
		$iname = getsetting("innname", LOCATION_INN);
		$sections = commentarylocs();
		reset ($sections);
		output_notl("<select name='section'>",true);
		while (list($key,$val)=each($sections)){
			output_notl("<option value='$key'>$val</option>",true);
		}
		output_notl("</select>",true);
	}else{
		output_notl("<input type='hidden' name='section' value='$section'>",true);
	}
		modulehook("commentarytrail",array());
	// *** DRAGONBG.COM CORE PATCH END***

	// *** AJAX CHAT MOD START ***
	//if (round($limit/2,0)-$counttoday < 3 && getsetting('postinglimit',1)){
	//	output("`)(You have %s posts left today)`n`0",(round($limit/2,0)-$counttoday));
	//}
	rawoutput("<div id='previewtext'></div></form>");
	rawoutput('</div>');
	// *** AJAX CHAT MOD END ***

	tlschema();
}
?>
