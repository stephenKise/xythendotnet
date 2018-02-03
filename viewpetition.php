<?php
// translator ready
// addnews ready
// mail ready
define("OVERRIDE_FORCED_NAV",true);
require_once("common.php");
require_once("lib/commentary.php");
require_once("lib/http.php");

tlschema("petition");

check_su_access(SU_EDIT_PETITIONS);

addcommentary();


$statuses = [
    0 => "`&`bUnhandled`b",
    1 => "`\$Code Review",
    2 => "`QJob Postings",
    3 => "`^CDonation",
    4 => "`@Contest",
    5 => "`#Miscellaneous",
    6 => "`!Awaiting Response",
    7 => "`)`iClosed`i",
];

//$statuses = modulehook("petition-status", $status);
$statuses=translate_inline($statuses);

$op = httpget("op");
$id = httpget("id");

// Eric decide he didn't want petitions to be manually printer_delete_dc(printer_handle)	
//
//if ($op=="del"){
//	$sql = "DELETE FROM " . db_prefix("petitions") . " WHERE petitionid='$id'";
//	db_query($sql);
//	$sql = "DELETE FROM " . db_prefix("commentary") . " WHERE section='pet-$id'";
//	db_query($sql);
//	invalidatedatacache("petition_counts");
//	$op="";
//}
page_header("Petition Viewer");
require_once("lib/superusernav.php");
superusernav();

$hidepetitions = httpget('hidepetitions');
if ($hidepetitions){
$sethidepetitions = ($hidepetitions=="show"?1:0);
$session['user']['prefs']['hidepetitions'] = $sethidepetitions;
redirect("viewpetition.php");
}

if ($op==""){
$closehide = translate_inline($session['user']['prefs']['hidepetitions']?"Show":"Hide");
$closehidelink = ($session['user']['prefs']['hidepetitions']?"hide":"show");
addnav("", "viewpetition.php?hidepetitions=$closehidelink");
$sql = "DELETE FROM " . db_prefix("petitions") . " WHERE status= 7  AND closedate < '".date("Y-m-d H:i:s",strtotime("-7 days"))."'";
db_query($sql);
if(db_affected_rows()) {
invalidatedatacache("petition_counts");
}
$setstat = httpget("setstat");
if ($setstat!=""){
$sql = "SELECT author,pname,status,petitionid FROM " . db_prefix("petitions") . " WHERE petitionid='$id'";
$result = db_query($sql);
$row = db_fetch_assoc($result);
if ($row['status']!=$setstat){
$sql = "UPDATE " . db_prefix("petitions") . " SET status='$setstat', closeuserid='{$session['user']['acctid']}', closedate='".date("Y-m-d H:i:s")."' WHERE petitionid='$id'";
db_query($sql);
invalidatedatacache("petition_counts");
}
if ($setstat == 7){
$allowed = explode(" ",$row['author']);
require_once("lib/systemmail.php");
foreach ($allowed as $author)
systemmail($author, "`QYour petition has been resolved!", "`QYour petition titled `&[<a href='runmodule.php?module=inboxpetitions&op=viewpet&petid=".$row['petitionid']."'>{$row['pname']}</a>]`Q has been resolved! Please `&[<a href='petition.php'>petition</a>]`Q if you run into any other issues with the game.`0`n`n`i`7Click on the name of your petition in this message to read the latest responses.`i`n`n`Q-- `&{$session['user']['name']}`0");
}
redirect("viewpetition.php");
}

reset($statuses);
$sort = "";
$pos = 1;
while (list($key,$val)=each($statuses)){
$sort.=" WHEN $key THEN $pos";
$pos++;
}

$petitionsperpage = 50;
$sql = "SELECT count(petitionid) AS c from ".db_prefix("petitions");
$result = db_query($sql);
$row = db_fetch_assoc($result);
$totalpages = ceil($row['c']/$petitionsperpage);

$page = httpget("page");
if ($page == "") {
if (isset($session['petitionPage'])){
$page = (int)$session['petitionPage'];
} else {
$page = 1;
}
}
if ($page < 1) $page = 1;
if ($page > $totalpages) $page = $totalpages;
$session['petitionPage'] = $page;

// No need to show the pages if there is only one.
if ($totalpages != 1)  {
addnav("Page");
for ($x=1; $x <= $totalpages; $x++){
if ($page == $x){
addnav(array("`b`#Page %s`0`b", $x),"viewpetition.php?page=$x");
} else {
addnav(array("Page %s", $x),"viewpetition.php?page=$x");
}
}
}
if ($page > 1){
$limit = (($page-1) * $petitionsperpage) . "," . $petitionsperpage;
} else {
$limit = "$petitionsperpage";
}


//WHEN 5 THEN 4 WHEN 4 THEN 0 WHEN 0 THEN 3 WHEN 3 THEN 7 WHEN 7 THEN 1 WHEN 1 THEN 2 WHEN 2 THEN 6 WHEN 6 THEN 5 
$sql =
"SELECT
petitionid,
".db_prefix("accounts").".name,
".db_prefix("accounts").".title,
".db_prefix("accounts").".ctitle,
".db_prefix("petitions").".date,
".db_prefix("petitions").".status,
".db_prefix("petitions").".pname,
".db_prefix("petitions").".author,
".db_prefix("petitions").".body,
".db_prefix("petitions").".closedate,
accts.name AS closer,
accts.ctitle AS closer_c,
accts.title AS closer_t,
CASE status WHEN 9 THEN 9 WHEN 2 THEN 2 WHEN 8 THEN 8 WHEN 7 THEN 7 WHEN 6 THEN 6 WHEN 5 THEN 5 WHEN 4 THEN 4 WHEN 3 THEN 3 WHEN 1 THEN 1 END AS sortorder
FROM
".db_prefix("petitions")."
LEFT JOIN
".db_prefix("accounts")."
ON	".db_prefix("accounts").".acctid=".db_prefix("petitions").".author
LEFT JOIN
".db_prefix("accounts")." AS accts
ON	accts.acctid=".db_prefix("petitions").".closeuserid
ORDER BY
sortorder ASC,
date ASC
LIMIT $limit";
$result = db_query($sql);
addnav("Petitions");
addnav("Refresh","viewpetition.php");
addnav(($session['user']['petition_sub']?"Unsubscribe":"Subscribe"),"viewpetition.php?op=switchsub");
$tit = translate_inline("`b<big>`&`iTitle`i</big>`b");
$from = translate_inline("`b<big>`&`iSender`i</big>`b");
$com = translate_inline("`b<big>`&`iCom`i</big>`b");
$lastup = translate_inline("`b<big>`&`iLast Updater`i</big>`b");
$mark = translate_inline("`b<big>`&`iSort`i</big>`b");

output("`c`&`bLast Updater:`b `^Top Row `&= `QLast Mover`&, `^Bottom Row `&= `QLast Commenter`0`n `$1-Code Review`0, `Q2-Job Posting`0, `^3-Donation`0, `@4-Contest`0, `#5-Misc`0, `L6-Pending`0, `)X-Closed`c`0");
output("<table border='0' align='center'><tr class='trhead' align='center'><td>$tit</td><td>$from</td><td width='3%'>$com</td><td>$lastup</td><td>$mark</td></tr>",TRUE);
$i=0;
$laststatus=-1;
require_once("lib/names.php");
require_once("lib/sanitize.php");
while($row = db_fetch_assoc($result)){
$i++;
$sql = "SELECT count(commentid) AS c FROM ". db_prefix("commentary") .  " WHERE section='pet-{$row['petitionid']}'";
$res = db_query($sql);
$counter = db_fetch_assoc($res);
$closed = (strtolower(sanitize($statuses[$row['status']])) == "closed");
if (array_key_exists('status', $row) && $row['status']!=$laststatus){
rawoutput("<tr class='".($i%2?"trlight":"trdark")."'>");
rawoutput("<td colspan='9'>");
output_notl("<big><center>`b%s`b</center></big>", $statuses[$row['status']].($closed?" <a href='viewpetition.php?hidepetitions=$closehidelink'><small>[$closehide]</small></a>":""),TRUE);
rawoutput("</td></tr>");
$i++;
$laststatus=$row['status'];
}
if ($closed && $session['user']['prefs']['hidepetitions']) continue;
rawoutput("<tr class='".($i%2?"trlight":"trdark")."'>");
rawoutput("<td width='175'>");
if ($row['pname']){
output_notl("<a href='viewpetition.php?op=view&id={$row['petitionid']}'>`&%s</a>",$row['pname'],TRUE);
} else {
output_notl("<a href='viewpetition.php?op=view&id={$row['petitionid']}'>`&No Subject</a>",TRUE);
}

rawoutput("</td><td width='275'>");
if ($row['name']==""){
$v = substr($row['body'],0,strpos($row['body'],"[email"));
$v = preg_replace("'\\[PHPSESSID\\] = .*'", "", $v);
$v = preg_replace("'[^a-zA-Z\\[\\]= @.!,?-]'","", $v);
// Make sure we don't get something too large.. 50 chars max
$v = substr($v, 0, 50);
output_notl("`)%s `@`>(%s)`>`0", $v,relativedate($row['date']));
} elseif (is_numeric($row['author'])){
$player = array('name' => $row['name'], 'title' => $row['title'], 'ctitle' => $row['ctitle']);
output_notl("`)%s `@`>(%s)`>`0", get_player_basename($player),relativedate($row['date']));
} else {
output_notl("`\$Group Petition `@`>(%s)`>`0",relativedate($row['date']));
}
rawoutput("</td>");
rawoutput("<td>");
output_notl("`#`c%s`c`0", $counter['c']);
rawoutput("</td>");
rawoutput("<td>");
if ($row['closedate'] != 0){
$player = array('name' => $row['closer'], 'title' => $row['closer_t'], 'ctitle' => $row['closer_c']);
output_notl("`)%s `)(%s)`0", get_player_basename($player), reltime(strtotime($row['closedate'])));
} else {
output_notl("`)`iNone`i`0");
}
output("`n`n");
$com_s = "SELECT postdate,name,title,ctitle FROM ".db_prefix('accounts')." a INNER JOIN ".db_prefix('commentary')." c ON a.acctid = c.author WHERE section = 'pet-{$row['petitionid']}' ORDER BY postdate DESC LIMIT 1";
$com_d = db_query($com_s);
$com_r = db_fetch_assoc($com_d);
if ($com_r['postdate'] != 0){
$player = array('name' => $com_r['name'], 'title' => $com_r['title'], 'ctitle' => $com_r['ctitle']);
output_notl("`)%s `)(%s)`0", get_player_basename($player), reltime(strtotime($com_r['postdate'])));
} else {
output_notl("`)`iNone`i`0");
}
rawoutput("</td>");
rawoutput("<td nowrap>");
output("`c<a href='viewpetition.php?setstat=0&id={$row['petitionid']}'>`&`bU`b`0</a>`&-<a href='viewpetition.php?setstat=1&id={$row['petitionid']}'>`\$1`0</a>`&-<a href='viewpetition.php?setstat=2&id={$row['petitionid']}'>`Q2`0</a>`&-<a href='viewpetition.php?setstat=3&id={$row['petitionid']}'>`^3`0</a>`&-<a href='viewpetition.php?setstat=4&id={$row['petitionid']}'>`@4`0</a>`&-<a href='viewpetition.php?setstat=5&id={$row['petitionid']}'>`#5`0</a>`&-<a href='viewpetition.php?setstat=6&id={$row['petitionid']}'>`L6`0</a>`&-<a href='viewpetition.php?setstat=7&id={$row['petitionid']}'>`)`iX`i`0</a>`c",TRUE);

rawoutput("</td>");
addnav("","viewpetition.php?op=view&id={$row['petitionid']}");
addnav("","viewpetition.php?setstat=0&id={$row['petitionid']}");
addnav("","viewpetition.php?setstat=1&id={$row['petitionid']}");
addnav("","viewpetition.php?setstat=2&id={$row['petitionid']}");
addnav("","viewpetition.php?setstat=3&id={$row['petitionid']}");
addnav("","viewpetition.php?setstat=4&id={$row['petitionid']}");
addnav("","viewpetition.php?setstat=5&id={$row['petitionid']}");
addnav("","viewpetition.php?setstat=6&id={$row['petitionid']}");
addnav("","viewpetition.php?setstat=7&id={$row['petitionid']}");

rawoutput("</tr>");
}
rawoutput("</table>");

} elseif($op=="view"){
addnav("V?Petition Viewer","viewpetition.php");

addnav("User Ops");

addnav("Archive");

addnav("Archive to FAQ","viewpetition.php?op=archivetofaq&id=$id");
addnav("Peitition Ops");
addnav(array("Mark %s",$statuses[0]),"viewpetition.php?setstat=0&id=$id");
addnav(array("Mark %s",$statuses[1]),"viewpetition.php?setstat=1&id=$id");
addnav(array("Mark %s",$statuses[2]),"viewpetition.php?setstat=2&id=$id");
addnav(array("Mark %s",$statuses[3]),"viewpetition.php?setstat=3&id=$id");
addnav(array("Mark %s",$statuses[4]),"viewpetition.php?setstat=4&id=$id");
addnav(array("Mark %s",$statuses[5]),"viewpetition.php?setstat=5&id=$id");
addnav(array("Mark %s",$statuses[6]),"viewpetition.php?setstat=6&id=$id");
addnav(array("Mark %s",$statuses[7]),"viewpetition.php?setstat=7&id=$id");
addnav("Merge and Delete","viewpetition.php?op=merge&mergeid=$id");
addnav("","viewpetition.php?op=view&id=$id&showraw=on");
addnav("","viewpetition.php?op=view&id=$id");

addnav("View");

$sql = "SELECT " . db_prefix("accounts") . ".name," .  db_prefix("accounts") . ".login," .  db_prefix("accounts") . ".acctid," .  "date,closedate,status,petitionid,ip,body,pageinfo,author,pname," .  "accts.name AS closer FROM " .  db_prefix("petitions") . " LEFT JOIN " .  db_prefix("accounts ") . "ON " .  db_prefix("accounts") . ".acctid=author LEFT JOIN " .  db_prefix("accounts") . " AS accts ON accts.acctid=".  "closeuserid WHERE petitionid='$id' ORDER BY date ASC";
$result = db_query($sql);
$row = db_fetch_assoc($result);
addnav("User Ops");
if (isset($row['login'])) 
addnav("View User Biography","bio.php?char={$row['acctid']}&ret=%2Fviewpetition.php%3Fop%3Dview%26id={$id}");
if ($row['acctid']>0 && $session['user']['superuser'] & SU_EDIT_USERS){
addnav("User Ops");
addnav("Edit User","user.php?op=edit&userid={$row['acctid']}&returnpetition=$id");
}
if ($row['acctid']>0 && $session['user']['superuser'] & SU_EDIT_DONATIONS){
addnav("User Ops");
addnav("Edit User Donations","donators.php?op=add1&name=".rawurlencode($row['login'])."&ret=".urlencode($_SERVER['REQUEST_URI']));
}

addnav('User Ops');
addnav("Update Alert!","viewpetition.php?op=alert&id=$id&author={$row['author']}");
//Mod below by Senare
$body = $row['body'];
$string = @unserialize($body);
$serialnote = NULL;
if ($string === false && $string !== "b:0;") {
$serialnote = "`4This message looks different from other petitions because it is from `i`4before`i`4 the remake of the petition system!";
$description = stripslashes($body);
} else {
$body = unserialize($body);
$description = stripslashes($body['description']);
}
//$description = ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]",'<a href="\\0" target="_blank">\\0</a>', $description);
// debug($row['body']);
$yourpeti = translate_mail("Your Petition",0);
$write = translate_inline("Write Mail");
$peti = translate_mail("Petition",0);
// For email replies, make sure we don't overflow the URI buffer.
$reppet = substr(stripslashes($description), 0, 2000);
$issue = $row['pname'];
$char = $row['author'];
if ($char == 0)
{
if ($session['user']['superuser'] & SU_EDIT_USERS)
{
$linked_account = db_fetch_assoc(db_query("SELECT acctid,name,title,ctitle FROM accounts WHERE lastip = '".$row['ip']."'"));
if ($linked_account == "")
{
$allowedstring = $row['ip'];
}
else
{
require_once("lib/names.php");
$account = array('name' => $linked_account['name'], 'title' => $linked_account['title'], 'ctitle' => $linked_account['ctitle']);
$allowedstring = $row['ip']." (".get_player_basename($account)."`^)";
}
}
else
{
$allowedstring = "`i`7IP Restricted to Admin Only.`i";
}
}
else
{
$allowed = explode(" ",$char);
$numallowed = count($allowed);
$allowedstring = '';
for ($in=0;$in<$numallowed;$in++){
//output($in);
$players = db_fetch_assoc(db_query("SELECT name,login FROM accounts WHERE acctid = ".$allowed[$in]));
if ($in != floor($numallowed-1)) $punctuality = ', ';
else $punctuality = '.';
$mail = " <a href=\"mail.php?op=write&to=".$players['login']."&body=".rawurlencode("\n\n`^----- $yourpeti -----\n$reppet")."&subject=RE:+$issue\" target=\"_blank\" onClick=\"".popup("mail.php?op=write&to=".$players['login']."&body=".rawurlencode("\n\n`^----- $yourpeti -----\n$reppet")."&subject=RE:+$issue").";return false;\"><img src='images/rsz_newscroll.png' valign='-2px' alt='$write' border='0'></a>";
$allowedstring .= $players['name'].$mail.$punctuality;
}
}

// 	rawoutput("<a href=\"mail.php?op=write&to=".$allowed[$in]."&body=".rawurlencode("\n\n`^----- $yourpeti -----\n$reppet")."&subject=RE:+$peti\" target=\"_blank\" onClick=\"".popup("mail.php?op=write&to=".$allowed[$in]."&body=".rawurlencode("\n\n`^----- $yourpeti -----\n$reppet")."&subject=RE:+$peti").";return false;\"><img src='images/newscroll.GIF' width='16' height='16' alt='$write' border='0'></a><br>");
output("`@From: `^%s`n", $allowedstring,TRUE);
if (!is_string($body)) output("`@Email: ".$body['email']."`n");
output("`@Issue: `\$`b".$issue."`b `2[<a href='viewpetition.php?op=edittitle&toedit=$id'>`i`0Edit`i</a>`2]`n",TRUE);
addnav("","viewpetition.php?op=edittitle&toedit=$id");
output("`@Status: %s`n", $statuses[$row['status']]);	
if($row['closedate'] != '0000-00-00 00:00:00') output("`@Updated: `^%s`@ on `^%s `n", $row['closer'], date($session['user']['prefs']['timeformat'],strtotime($row['closedate'])));
else output("`@Updated: `^`iNeeds updating!`i`n");
output("`@Received: `0%s`n", date($session['user']['prefs']['timeformat'],strtotime($row['date'])));
rawoutput("<hr color='#C11B17' align='left' width='200px'/>");

$start = strpos($description, "prntscr.com/");
if ($start >= 1)
{
$end = strpos($description, " ", $start+1);
$prntscr_link = substr($description, $start, ($end-$start-1));
//$meta = get_meta_tags("http://".$prntscr_link);
//$description = str_replace("http://".$prntscr_link, "<img src='{$meta['twitter:image:src']}' />", $description);
}
if (httpget('showraw')!='on'){
output("<a href='viewpetition.php?op=view&id=$id&showraw=on'>`2[`0Show Rawoutput`2]</a>`n",TRUE);
output("`n`0".nl2br($description),TRUE);
} else {
output("<a href='viewpetition.php?op=view&id=$id'>`2[`0Disable Rawoutput`2]</a>`n",TRUE);
output("`n`0");
rawoutput(nl2br($description));
}

output_notl("`n`n");
if ($session['user']['superuser'] & SU_MEGAUSER)
{
output("`2[<a href='viewpetition.php?op=edit_data&id=$id'>Edit Petition</a>`2]",true);
addnav("","viewpetition.php?op=edit_data&id=$id");
}
rawoutput("<hr color='#C11B17' align='left' width='200px'/>");

if (isset($serialnote) != FALSE) output("`n`n`b`\$Notice`b`4: ".$serialnote."`^");
commentdisplay("", "pet-$id","",200);
//End of by Senare


// 	if ($viewpageinfo){
// 		output("`n`n`@Page Info:`&`n");
// 		$row['pageinfo']=stripslashes($row['pageinfo']);
// 		$body = HTMLEntities($row['pageinfo'], ENT_COMPAT, getsetting("charset", "ISO-8859-1"));
// 		$body = preg_replace("'([[:alnum:]_.-]+[@][[:alnum:]_.-]{2,}([.][[:alnum:]_.-]{2,})+)'i","<a href='mailto:\\1?subject=RE: $peti&body=".str_replace("+"," ",URLEncode("\n\n----- $yourpeti -----\n".$row['body']))."'>\\1</a>",$body);
// 		$body = preg_replace("'([\\[][[:alnum:]_.-]+[\\]])'i","<span class='colLtRed'>\\1</span>",$body);
// 		rawoutput("<span style='font-family: fixed-width'>".nl2br($body)."</span>");
// 	}
}

if ($op ==  "switchsub")
{

if ($session['user']['petition_sub'] == 0)
{
$session['user']['petition_sub'] = 1;
output("You have subscribed to the petition listing. From now on, when a player petitions, you will receive an email to notify you.");
}
else
{
$session['user']['petition_sub'] = 0;
output("You have changed your subscription settings for petitions. We will no longer notify you when a petition is made.");
}

addnav("Go back","viewpetition.php");
}
if ($op == "edit_data")
{
check_su_access(SU_MEGAUSER);
$sql = db_query("SELECT body FROM petitions WHERE petitionid = '$id'");
$row = db_fetch_assoc($sql);
$body = unserialize($row['body']);
output("<form action='viewpetition.php?op=save_data&id=$id' method='POST'>",true);
output("Edit the petition's data:`n");
rawoutput("<textarea name='save' class='input' rows='7' cols='75'>".stripslashes($body['description'])."</textarea>");
rawoutput("<br><input type='submit' class='button' value='Save Petition'>");
output("</form>",true);
addnav("","viewpetition.php?op=save_data&id=$id");
addnav("Refresh","viewpetition.php?op=edit_data&id=$id");
}

if ($op == "save_data")
{
require_once("lib/redirect.php");
$post = httpallpost();
$sql = db_query("SELECT body FROM petitions WHERE petitionid = '$id'");
$row = db_fetch_assoc($sql);
$body = unserialize($row['body']);
$body['description'] = $post['save'];
$body = serialize($body);
db_query("UPDATE petitions SET body = '".addslashes($body)."' WHERE petitionid = $id");
redirect("viewpetition.php?op=view&id=$id");
}

//Mod below by Senare.
if ($op == "edittitle"){
$toedit = httpget("toedit");
$edit = db_fetch_assoc(db_query("SELECT pname FROM ".db_prefix("petitions")." WHERE petitionid = $toedit"));
output("Choose a new name for this petition:`n");
rawoutput("<form action='viewpetition.php?op=edittitle&toedit=$toedit&new=name' method='POST'>");
rawoutput("<input type='text' size='50' name='newpname' id='newpname' placeholder='".$edit['pname']."'><br>");
rawoutput("<input type='submit' class='button' value='Set'>");
rawoutput("</form>");
rawoutput("<script type='text/javascript'>document.getElementById('newpname').focus();</script>");
addnav("","viewpetition.php?op=edittitle&toedit=$toedit&new=name");
addnav("Back to ".$edit['pname'],"viewpetition.php?op=view&id=$toedit");
if (httpget("new") == "name"){
$newpname = rawurldecode(httppost("newpname"));
$strinks = "UPDATE petitions SET pname = '".$newpname."' WHERE petitionid = ".$toedit;
db_query("UPDATE petitions SET pname = '".$newpname."' WHERE petitionid = ".$toedit);
debug("Newname = ".$newpname);
debug($strinks);
invalidatedatacache("petition_counts");
redirect("viewpetition.php?op=view&id=".$toedit);
}
}
//End of mod by Senare



if ($op == "alert")
{
$id = httpget('id');
$author = httpget('author');
require_once('lib/redirect.php');
require_once('lib/alert.php');
$path = "runmodule.php?module=inboxpetitions&op=viewpet&petid=$id";
$message = "`@Your petition has been updated! Click <a href='$path'>`i`^here`i</a> `@to view it!";
alert($author,$message);
redirect('viewpetition.php?op=view&id='.httpget('id'));
}

//Mod below by Senare
if ($op == "merge"){
$mergeid = httpget('mergeid');
$merge = db_fetch_assoc(db_query("SELECT author,pname FROM petitions WHERE petitionid = ".$mergeid));
$targets = db_query("SELECT pname,petitionid,author FROM petitions WHERE petitionid != ".$mergeid);
output("`^Choose what petition to merge `@".$merge['pname']."`^ to. Keep in mind that this cannot be undone, and that the original content is going to be deleted!!!`n`n");
for($m=0;$m<db_num_rows($targets);$m++){
$other = db_fetch_assoc($targets);
$player = db_fetch_assoc(db_query("SELECT name FROM accounts WHERE acctid = ".$other['author']));
if (is_numeric($other['author'])) $players = $player['name'];
else $players = "`\$Group Petition";

output("<a href='viewpetition.php?op=mergeto&mergeid=$mergeid&mergeto=".$other['petitionid']."'>`0".$other['pname']." `0by `^$players</a>`n",TRUE);
addnav("","viewpetition.php?op=mergeto&mergeid=$mergeid&mergeto=".$other['petitionid']);
}
addnav("Back to ".$merge['pname'],"viewpetition.php?op=view&id=$mergeid");
}
//End of mod by Senare

http://arcane.us/test/viewpetition.php?op=mergeto&mergeid=886&mergeto=889
//Mod below by Senare
if ($op == "mergeto"){
$mergeto = httpget('mergeto');
$mergeid = httpget('mergeid');
$merging = db_fetch_assoc(db_query("SELECT author FROM petitions WHERE petitionid = $mergeid"));
db_query("UPDATE petitions SET author = CONCAT(author, ' ".$merging['author']."') WHERE petitionid = $mergeto");
db_query("DELETE FROM petitions WHERE petitionid = $mergeid");
require_once('lib/systemmail.php');
systemmail($merging['author'],"`4Group Petition!","`QYour petition was similar to one that other players have experienced. Therefore, your petition was deleted and you were placed into that group discussion.`n`n`^Thank you,`n`tXythen Staff");
invalidatedatacache("petition_counts");
redirect("viewpetition.php?op=view&id=".$mergeto);
}
//End of mod by Senare

//Mod by Maverick
if ($op == "archivetofaq"){

$archive = db_fetch_assoc(db_query("SELECT author,pname,body FROM petitions WHERE petitionid = $id LIMIT 1"));
$body = $archive['body'];
$string = @unserialize($body);
if ($string === false && $string !== "b:0;") {
$description = stripslashes($body);
} else {
$body = unserialize($body);
$description = stripslashes($body['description']);
}

rawoutput("<form action='viewpetition.php?op=archivetofaq&id=$id&answer=1' method='POST'>");
rawoutput("<input type='text' size='70' id='title' name='title' placeholder=\"Title of Question...\" value='{$archive['pname']}'><br>");
rawoutput("<textarea class='input' name='question' cols='54' rows='7' placeholder='Question to the Petition...'>{$description}</textarea><br>");
rawoutput("<textarea class='input' name='answer' cols='54' rows='7' placeholder='Answer...'>{$row['answer']}</textarea><br>");
rawoutput("<input type='submit' class='button' value='Set'>");
rawoutput("</form>");
rawoutput("<script type='text/javascript'>document.getElementById('ans').focus();</script>");
addnav("", "viewpetition.php?op=archivetofaq&id=$id&answer=1");

if (httpget('answer')){
$post = httpallpost();
db_query("INSERT INTO faq (author,archived,pname,body,answer) VALUES ('".$archive['author']."','".date("Y-m-d H:i:s")."','".$post['title']."','".$post['question']."','".$post['answer']."')");
header("Location: viewpetition.php?op=view&id=".httpget('id'));
}
}

//End of mod by Maverick

if ($id && $op != ""){
$prevsql="SELECT p1.petitionid, p1.status FROM ".db_prefix("petitions")." AS p1, ".db_prefix("petitions")." AS p2
WHERE p1.petitionid<'$id' AND p2.petitionid='$id' AND p1.status=p2.status ORDER BY p1.petitionid DESC LIMIT 1";
$prevresult=db_query($prevsql);
$prevrow=db_fetch_assoc($prevresult);
if ($prevrow){
$previd=$prevrow['petitionid'];
$s=$prevrow['status'];
$status=$statuses[$s];
addnav("Navigation");
addnav(array("Previous %s",$status),"viewpetition.php?op=view&id=$previd");
}
$nextsql="SELECT p1.petitionid, p1.status FROM ".db_prefix("petitions")." AS p1, ".db_prefix("petitions")." AS p2
WHERE p1.petitionid>'$id' AND p2.petitionid='$id' AND p1.status=p2.status ORDER BY p1.petitionid ASC LIMIT 1";
$nextresult=db_query($nextsql);
$nextrow=db_fetch_assoc($nextresult);
if ($nextrow){
$nextid=$nextrow['petitionid'];
$s=$nextrow['status'];
$status=$statuses[$s];
addnav("Navigation");
addnav(array("Next %s",$status),"viewpetition.php?op=view&id=$nextid");
}
}
page_footer();
?>