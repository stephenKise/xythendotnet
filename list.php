<?php
// addnews ready
// translator ready
// mail ready
define("ALLOW_ANONYMOUS",true);
require_once("common.php");
require_once("lib/http.php");
require_once("lib/villagenav.php");
invalidatedatacache("list.php-warsonline");
tlschema("list");

page_header("List Warriors");
if ($session['user']['loggedin']) {
	checkday();
	if ($session['user']['alive']) {
		villagenav();
	} else {
		addnav("Return to the Graveyard", "graveyard.php");
	}
	addnav("Currently Online","list.php");
	if ($session['user']['clanid']>0){
		addnav("Members of Guild","list.php?op=clan");
		if ($session['user']['alive']) {
			addnav("Guild Commons","clan.php");
		}
	}
}else{
	addnav("Login Screen","index.php");
	addnav("Currently Online","list.php");
}

$playersperpage=50;

$sql = "SELECT count(acctid) AS c FROM " . db_prefix("accounts") . " WHERE locked=0";
$result = db_query($sql);
$row = db_fetch_assoc($result);
$totalplayers = $row['c'];

$op = httpget('op');
$page = httpget('page');
$search = "";
$limit = "";

if ($op=="search"){
	$search="%";
	$n = httppost('name');
	for ($x=0;$x<strlen($n);$x++){
		$search .= substr($n,$x,1)."%";
	}
	$search=" AND name LIKE '".addslashes($search)."' ";
}else{
	$pageoffset = (int)$page;
	if ($pageoffset>0) $pageoffset--;
	$pageoffset*=$playersperpage;
	$from = $pageoffset+1;
	$to = min($pageoffset+$playersperpage,$totalplayers);

	$limit=" LIMIT $pageoffset,$playersperpage ";
}
addnav("Pages");
for ($i=0;$i<$totalplayers;$i+=$playersperpage){
	$pnum = $i/$playersperpage+1;
	if ($page == $pnum) {
		addnav(array(" ?`b`#Page %s`0 (%s-%s)`b", $pnum, $i+1, min($i+$playersperpage,$totalplayers)), "list.php?page=$pnum");
	} else {
		addnav(array(" ?Page %s (%s-%s)", $pnum, $i+1, min($i+$playersperpage,$totalplayers)), "list.php?page=$pnum");
	}
}

// Order the list by level, dragonkills, name so that the ordering is total!
// Without this, some users would show up on multiple pages and some users
// wouldn't show up
if ($page=="" && $op==""){
	$title = translate_inline("Xythenian's Currently Online");
	$sql = "SELECT acctid,name,login,alive,location,race,sex,laston,loggedin,lastip,uniqueid FROM " . db_prefix("accounts") . " WHERE locked=0 AND loggedin=1 AND laston>'".date("Y-m-d H:i:s",strtotime("-".getsetting("LOGINTIMEOUT",900)." seconds"))."' ORDER BY level DESC, dragonkills DESC, login ASC";
	$result = db_query_cached($sql,"list.php-warsonline");
}elseif($op=='clan'){
	$title = translate_inline("Dimension Members Online");
	$sql = "SELECT acctid,name,login,alive,location,race,sex,laston,loggedin,lastip,uniqueid FROM " . db_prefix("accounts") . " WHERE locked=0 AND loggedin=1 AND laston>'".date("Y-m-d H:i:s",strtotime("-".getsetting("LOGINTIMEOUT",900)." seconds"))."' AND clanid='{$session['user']['clanid']}' ORDER BY level DESC, dragonkills DESC, login ASC";
	$result = db_query($sql);
}else{
	if ($totalplayers > $playersperpage && $op != "search") {
		$title = sprintf_translate("Citizens of Xythen `n", ($pageoffset/$playersperpage+1), $from, $to, $totalplayers);
	} else {
		$title = sprintf_translate("Citizens of Xythen `n");
	}
	rawoutput(tlbutton_clear());
	$sql = "SELECT acctid,name,login,alive,hitpoints,location,race,sex,laston,loggedin,lastip,uniqueid FROM " . db_prefix("accounts") . " WHERE locked=0 $search ORDER BY level DESC, dragonkills DESC, login ASC $limit";
	$result = db_query($sql);
}
if ($session['user']['loggedin']){
	$search = translate_inline("Search by name: ");
	$search2 = translate_inline("Search");

	rawoutput("<form action='list.php?op=search' method='POST'>$search<input name='name' type='text'><input type='submit' class='button' value='$search2'></form>");
	addnav("","list.php?op=search");
}

$max = db_num_rows($result);
if ($max>getsetting("maxlistsize", 100)) {
	output("`\$Too many names match that search.  Showing only the first %s.`0`n", getsetting("maxlistsize", 100));
	$max = getsetting("maxlistsize", 100);
}

if ($page=="" && $op==""){
	$title .= sprintf_translate(" (%s warriors)", $max);
}
output("`c`b$title`b`c", true);
$name = translate_inline("Name");
$loc = translate_inline("Location");
$sex = translate_inline("Sex");
$last = translate_inline("Last Click");

rawoutput("<table class='listCharacters'>",true);
rawoutput("<tr><th>$name</th><th>$loc</th></tr>");
$writemail = translate_inline("Write Mail");
for($i=0;$i<$max;$i++){
	$row = db_fetch_assoc($result);
	rawoutput("<tr class='".($i%2?"trdark":"trlight")."'>",true);
	rawoutput("<td class='listCharacter'>");
	if ($session['user']['loggedin'])
	{
			rawoutput("<a href=\"mail.php?op=write&to=".rawurlencode($row['login'])."\" target=\"_blank\" onClick=\"".popup("mail.php?op=write&to=".rawurlencode($row['login'])."").";return false;\">");
			rawoutput("<img src='images/newscroll.GIF' width='16' height='16' border='0'></a>");
			rawoutput("<a href='bio.php?char=".$row['acctid']."'>");
			addnav("","bio.php?char=".$row['acctid']."");
	}
	output_notl("`&%s`0", $row['name']);
	if ($session['user']['loggedin'])
		rawoutput("</a>");
	rawoutput("</td><td class='listLocation'>");
		$loggedin=(date("U") - strtotime($row['laston']) < getsetting("LOGINTIMEOUT",900) && $row['loggedin']);
		output_notl("`&%s`0", $row['location']);
	rawoutput("</td></tr>");
}
rawoutput("</table>");

page_footer();
?>