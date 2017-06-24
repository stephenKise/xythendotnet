<?php

//WHAT WE GRAB FROM PETS
// petitionid, author, date, status, pname, closeruserid

require_once("common.php");
require_once("lib/http.php");






$total_alerts = 0;
//Alerts system is being ported from otR

output("<table width='600px'>",true);
$sql = db_query("SELECT id, message, posted FROM alerts WHERE acctid = {$session['user']['acctid']}");
	if (db_num_rows($sql) >= 1){
		output("<tr><td colspan='2'>`b`iAlerts`i`b</td><td width='100px' align='right'>`b`iDate`i`b</td></tr>",true);
		while ($row = db_fetch_assoc($sql)){
			output("<tr><td colspan='2'>`2[<a href='mail.php?op=clear_alert&id={$row['id']}'>x</a>`2] `&{$row['message']}</td><td align='right'>".timeoffset($row['posted'])."</td></tr>",true);
		}
		output("<tr><td colspan='3' align='center'><small>`2[<a href='mail.php?op=clear_alert&id=all'>`\$Delete All Alerts</a>`2]</small>`n`n</td></tr>",true);
	}
	




		$hidepetitions = get_module_pref("hidepetitions", "inboxpetitions");
		$sql = "SELECT * FROM petitions WHERE author LIKE '%".$session['user']['acctid']."%' ORDER BY status ASC";
		$res = db_query($sql);
		$max = db_num_rows($res);
		$tit = translate_inline("`b<big>`&`i`cPetition`c`i</big>`b");
		$lastup = translate_inline("`>`b<big>`&`iLast Update`i</big>`b`>");
		
		$statuses=array(
			0=>"`&`bUnhandled`b",
			1=>"`\$Errors",
			2=>"`QVotes",
			3=>"`^Contest",
			4=>"`@Donation",
			5=>"`#Progressive",
			6=>"`!Miscellaneous",
			7=>"`)`iClosed`i",
		);
		output("<tr><td colspan=2>`b`iPetitions`i`b</td><td align='right'>`bLast update`b</td></tr>",true);
		if ($max){
			while ($max>0){
				$row = db_fetch_assoc($res);
				if ($row['status'] == 7 && $hidepetitions) break;
				
				$chars = $row['author'];
				$allowed = explode(" ",$chars);
				if (in_array($session['user']['acctid'],$allowed)){
					$pn = $row['pname'];
					if ($pn == "")
						$pn = "No Subject";
					output("<tr><td>",true);
					output("<a href='runmodule.php?module=inboxpetitions&op=viewpet&petid=".$row['petitionid']."'>`^`b".$pn."`b</a>",true);
					output("</td><td align='center'>",true);
					
					output($statuses[$row['status']]);
					output("</td><td width=150px>",true);
					if ($row['closedate'] == "0000-00-00 00:00:00") output("`>`0".date($session['user']['prefs']['timeformat'],strtotime($row['date'])+($session['user']['prefs']['timeoffset'] * 60 * 60))."`>");
					else output("`>`0".date($session['user']['prefs']['timeformat'],strtotime($row['closedate'])+($session['user']['prefs']['timeoffset'] * 60 * 60))."`>");
					output("</td></tr>",true);
				}
				$max--;
			}
			output("<tr><td><small><a href='runmodule.php?module=inboxpetitions&op=hidepetitions&ret=".urlencode($_SERVER['REQUEST_URI'])."'>[".($hidepetitions?"Show":"Hide")." closed petitions]</a></small></td></tr>", true);
		}else{
			output("<tr><td colspan=3>`iGood! You have no petitions that are incomplete!`i</td></tr>",true);
		}



		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
if (!isset($session['user']['prefs']['convo_textarea'])) $session['user']['prefs']['convo_textarea'] = 0;
if (!isset($session['user']['prefs']['convo_amount'])) $session['user']['prefs']['convo_amount'] = 10;

if (!$op){
	if (!isset($session['user']['conversation'])) debug('Not set!');
	$carray = unserialize($session['user']['conversation']);
	$list = "";
	/*
	if (is_array($carray)){
		output("<tr><td>`n`n`b`iConversations BETA`i`b</td><td>`n`n`2[<a href='mail.php?op=search_convo'>Create</a>`2]</td><td>`n`n`>`bReceived`b`></td></tr>",true);
		foreach($carray as $key => $val){
			$list .= "conversation = '$key'";
			$sql = db_query("SELECT * FROM conversations WHERE conversation = $val AND deleted = 0 ORDER BY id+0 DESC LIMIT 0,1");
			$row = db_fetch_assoc($sql);
			$last = db_fetch_assoc(db_query("SELECT name,login FROM accounts WHERE acctid = ".$row['acctid']));
			if (strlen($row['message']) > 50) $include = " `&[...]";
				output("<tr><td><a href='mail.php?op=read_convo&id=".$row['conversation']."'>".$row['title']."</a></td><td>".stripslashes(substr($row['message'],0,50)).$include."</td><td align='right' width='200px'>`&".timeoffset($row['posted'])." `7 by `&".ucfirst($last['name'])."</td></tr>",true);
		}
		output("",true);
	}else{
		$row = db_fetch_assoc(db_query("SELECT * FROM conversations WHERE conversation = $carray ORDER BY id+0 DESC LIMIT 0,1"));
		$last = db_fetch_assoc(db_query("SELECT name FROM accounts WHERE acctid = ".$row['acctid']));
		output("<tr><td width='700px'>Title</td><td width='125px'>Recent Message</td><td width='200px' align='right'>Last Post</td></tr>",true);
		if (strlen($row['message']) > 50) $include = " `&[...]";
		output("<tr><td width='100px'><a href='mail.php?op=read_convo&id=".$row['conversation']."'>".stripslashes($row['title'])."</a></td><td>".preg_replace('{(.)\1+}','$1',stripslashes(substr($row['message'],0,50))).$include."</td><td width='100px' align='right'>`&".timeoffset($row['posted'])." `7 by `&".$last['name']."</td></tr>",true);
	}
	*/
}
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		

require_once('lib/bars.php');
$sql = db_query("SELECT count(messageid) AS num FROM mail WHERE seen = 1 AND msgto = {$session['user']['acctid']}");
$unread = db_fetch_assoc($sql);
$sql = db_query("SELECT count(messageid) AS num FROM mail WHERE seen = 2 AND msgto = {$session['user']['acctid']}");
$archived = db_fetch_assoc($sql);
$sql = db_query("SELECT count(messageid) AS num FROM mail WHERE seen = 0 AND msgto = {$session['user']['acctid']}");
$read = db_fetch_assoc($sql);
$total_mail = ($unread['num']+$archived['num']+$read['num']);
if ($total_mail > 100)
	$full = "`\$Full <small>`i`\$Clear your inbox`i</small>";
else
	$full = $total_mail."`7/`)100";

output("<tr><td colspan=2>`n`n`b`iMail`i`b</td><td>`n`n`>`bReceived`b`></td></tr>",true);
output("<tr><td colspan=3 align=center>`i`7Capacity: `&$full`i`n".inboxbar($unread['num'],$archived['num'],$read['num'])."</td></tr>",true);
if (isset($session['message'])) {
	output($session['message']);
}
$session['message']="";
$mail = db_prefix("mail");
$accounts = db_prefix("accounts");
$sql = "SELECT subject,messageid,$accounts.name,msgfrom,seen,sent FROM $mail LEFT JOIN $accounts ON $accounts.acctid=$mail.msgfrom WHERE msgto=\"".$session['user']['acctid']."\" ORDER BY seen DESC, sent DESC";
$result = db_query($sql);
$db_num_rows = db_num_rows($result);
if ($db_num_rows>0){
	$sections = array("colors"=>array(0=>"`)",1=>"`^",2=>"`L"),
					"status"=>array(0=>"Read Messages",1=>"Unread Messages",2=>"Archived Messages"),
					"spacer_needed"=>array(0=>"`n",1=>"`n"));
	$no_subject = translate_inline("`i(No Subject)`i");
	rawoutput("<form action='mail.php?op=process' method='post'>");
	while($row = db_fetch_assoc($result)){
		if ($sections[$row['seen']] != "set")
			output("<tr><td colspan=3>{$sections['spacer_needed'][$row['seen']]}`b{$sections['colors'][$row['seen']]} {$sections['status'][$row['seen']]}`b</td></tr>",true);
		$sections[$row['seen']] = "set";
		rawoutput("<tr>");
		rawoutput("<td nowrap><input type='checkbox' name='msg[]' value='{$row['messageid']}'>");
		rawoutput("<img src='images/".($row['seen']?"new":"old")."scroll.GIF' width='16px' height='16px' alt='".($row['seen']?"Old":"New")."'>");
		if ($row['msgfrom']==0 || !is_numeric($row['msgfrom'])){
			if ($row['msgfrom'] == 0 && is_numeric($row['msgfrom'])) {
				$row['name']=translate_inline("`i`^System`0`i");
			} else {
				$row['name']=$row['msgfrom'];
			}
			// Only translate the subject if it's an array, ie, it came from the game.
			$row_subject = @unserialize($row['subject']);
			if ($row_subject !== false) {
				$row['subject'] = call_user_func_array("sprintf_translate", $row_subject);
			} else {
         			$row['subject'] = translate_inline($row['subject']);
        		}
		}
		// In one line so the Translator doesn't screw the Html up
		output_notl("<a href='mail.php?op=read&id={$row['messageid']}'>".((trim($row['subject']))?$row['subject']:$no_subject)."</a>", true);
		rawoutput("</td><td align='center' width='180px'><a href='mail.php?op=read&id={$row['messageid']}'>");
		output_notl($row['name']);
		output("</a></td><td align='right'>`0".date($session['user']['prefs']['timeformat'],strtotime($row['sent'])+($session['user']['prefs']['timeoffset'] * 60 * 60))."</td>",true);
		rawoutput("</tr>");
	}
	rawoutput("</table>");
	$read_check = db_fetch_assoc(db_query("SELECT count(messageid) AS num FROM mail WHERE msgto = {$session['user']['acctid']} AND seen = 2"));
	
	$checkall = htmlentities(translate_inline("Check All"), ENT_COMPAT, getsetting("charset", "ISO-8859-1"));
	rawoutput("<input type='button' value=\"$checkall\" class='button' onClick='
		var elements = document.getElementsByName(\"msg[]\");
		for(i = {$read_check['num']}; i < elements.length; i++) {
			elements[i].checked = true;
		}
	'>");
	$delchecked = htmlentities(translate_inline("Delete Checked"), ENT_COMPAT, getsetting("charset", "ISO-8859-1"));
	rawoutput("<input type='submit' class='button' value=\"$delchecked\">");
	rawoutput("</form>");
}else{
	output("<tr><td colspan='3'>`iYour mailbox is currently empty!`i</td></tr></table>",true);
}
?>