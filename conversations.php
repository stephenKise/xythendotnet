<?php
require_once('common.php');
define("OVERRIDE_FORCED_NAV",true);
define('ALLOW_ANONYMOUS', true);
require_once("lib/datetime.php");
require_once('lib/http.php');
$op = httpget('op');
popup_header();
debug('ayyy');
output("THIS IS A BETA FEATURE AND IS IN NO WAY COMPLETE!`n");
output("<a href='mail.php'>Inbox</a> | <a href='mail.php?op=search_convo'>Create</a>",true);
// db_query("DELETE FROM alerts WHERE message = '' AND acctid = {$session['user']['acctid']}");
// invalidatedatacache("alerts-{$session['user']['acctid']}");

// if ($session['message']){
// 	output($session['message']);
// 	unset($session['message']);
// }

if (!isset($session['user']['prefs']['convo_textarea'])) $session['user']['prefs']['convo_textarea'] = 0;
if (!isset($session['user']['prefs']['convo_amount'])) $session['user']['prefs']['convo_amount'] = 10;

if (!$op){
	
	$sql = db_query("SELECT id, message, posted FROM alerts WHERE acctid = {$session['user']['acctid']}");
	if (db_num_rows($sql) >= 1){
		output("`n`n`b`iAlerts`i`b");
		output("<table width='700px'><tr><td width='500px'>Alert</td><td align='right' width='200px'>Date</td><td></td></tr>",true);
		while ($row = db_fetch_assoc($sql)){
			output("<tr><td>{$row['message']}</td><td align='right'>".timeoffset($row['posted'])."</td><td align='right'>`2[<a href='mail.php?op=clear_alert&id={$row['id']}'>x</a>`2]</td></tr>",true);
		}
		output("</table>",true);
	}
	
	
	
	
	output("`n`n`b`iOlde Scrolls`i`b");
	if (!isset($session['user']['conversation'])) debug('Not set!');
	$carray = unserialize($session['user']['conversation']);
	$list = "";
	if (is_array($carray)){
		output("<table width='700px'><tr><td width='100px'>Title</td><td>Recent Message</td><td width='100px' align='right'>Last Post</td></tr>",true);
		foreach($carray as $key => $val){
			$list .= "conversation = '$key'";
			$sql = db_query("SELECT * FROM conversations WHERE conversation = $val ORDER BY id+0 DESC LIMIT 0,1");
			$row = db_fetch_assoc($sql);
			$last = db_fetch_assoc(db_query("SELECT login FROM accounts WHERE acctid = ".$row['acctid']));
			if (strlen($row['message']) > 50) $include = " `&[...]";
				output("<tr><td width='100px'><a href='mail.php?op=read_convo&id=".$row['conversation']."'>".$row['title']."</a></td><td>".stripslashes(substr($row['message'],0,50)).$include."</td><td align='right' width='200px'>`&".timeoffset($row['posted'])." `7 by `&".ucfirst($last['login'])."</td></tr>",true);
		}
		output("</table>",true);
	}else{
		$row = db_fetch_assoc(db_query("SELECT * FROM conversations WHERE conversation = $carray ORDER BY id+0 DESC LIMIT 0,1"));
		$last = db_fetch_assoc(db_query("SELECT name FROM accounts WHERE acctid = ".$row['acctid']));
		output("<table><tr><td width='700px'>Title</td><td width='125px'>Recent Message</td><td width='200px' align='right'>Last Post</td></tr>",true);
		if (strlen($row['message']) > 50) $include = " `&[...]";
		output("<tr><td width='100px'><a href='mail.php?op=read_convo&id=".$row['conversation']."'>".stripslashes($row['title'])."</a></td><td>".preg_replace('{(.)\1+}','$1',stripslashes(substr($row['message'],0,50))).$include."</td><td width='100px' align='right'>`&".timeoffset($row['posted'])." `7 by `&".$last['name']."</td></tr></table>",true);
	}
}


debug($op);












// This is your playground, Aaron.
if ($op == 'read_convo'){
	debug($session);
	$myconvos = unserialize($session['user']['conversation']);
	if ((!in_array(httpget('id'),$myconvos)) && $session['user']['conversation'] != httpget('id')){
		//angelus("Glitch Attempt","Tried to spy on a conversation they are not allowed in.");
		header('Location: mail.php');
	}
	
	$num = db_fetch_assoc(db_query("SELECT count(id) AS ids FROM conversations WHERE conversation = ".httpget('id')));
	if (($num['ids']-$session['user']['prefs']['convo_amount']) >= 0) $arith = ($num['ids']-$session['user']['prefs']['convo_amount']);
		else $arith = 0;
	$sql = db_query("SELECT * FROM conversations WHERE conversation = ".httpget('id')." AND deleted = 0 ORDER BY id+0 ASC LIMIT ".$arith.",".$session['user']['prefs']['convo_amount']);
	output("<table align='center' width='700px'>",true);
	while($row = db_fetch_assoc($sql)){
		if ($session['user']['acctid'] == $row['originator']) $owner = 1;
		if ($owner == 1) $options = "`2[<a href='#'>Del</a> `2| <a href='mail.php?op=kick&conversation={$row['conversation']}&target={$row['acctid']}'>Kick</a>`2] ";
		$target = db_fetch_assoc(db_query("SELECT name FROM accounts WHERE acctid = {$row['acctid']}"));
		if (substr($row['message'],0,1) == "*" && substr($row['message'],-1,1) == "*") output("<tr><td colspan='3' align='center'><fieldset style='border: 0px;'><legend style='padding: 0.1em 1em; font-size:80%; text-align: center;'>$options `&{$target['name']} `&- ".timeoffset($row['posted'])."</legend>".preg_replace('/(..+?)(\1){2,}/','$1',stripslashes($row['message']))."</fieldset><br></td></tr>",true);
			else if($row['acctid'] == $session['user']['acctid']) output("<tr><td width='350px'></td><td width='50px'></td><td width='350px'><fieldset style='border: 1px solid #222; text-align: right; -webkit-border-radius: 6px; -moz-border-radius: 6px; border-radius: 6px;'><legend style='padding: 0.1em 1em; font-size: 80%; text-align: right;'>$options`& {$target['name']} `&- ".timeoffset($row['posted'])."</legend>".preg_replace('/(..+?)(\1){2,}/','$1',stripslashes($row['message']))."</fieldset></td></tr>",true);
			else output("<tr><td width='350px'><fieldset style='border: 1px solid #666; -webkit-border-radius: 6px; -moz-border-radius: 6px; border-radius: 6px;'><legend style='padding: 0.1em 1em; font-size:80%; text-align:left;'>$options `&{$target['name']} `&- ".timeoffset($row['posted'])."</legend>".preg_replace('/(..+?)(\1){2,}/','$1',stripslashes($row['message']))."</fieldset></td><td width='50px'></td><td width='350px'></td></tr>",true);
	}
	output("<tr><td colspan='3'><hr style='height: 1px; border:none; background-color:#777;'>",true);
	if ($owner == 1) output("`c`2[<a href='#'>Add</a> `2| <a href='#'>Rename</a> `2| <a href='#'>Close</a>`2]`c<br>",true);
	if ($session['user']['prefs']['convo_textarea'] == 0) output("<form action='mail.php?op=reply' method='POST'>`<<input type='text' name='reply' placeholder='Reply:' size='102'>`<`><input type='submit' value='Post'>`>",true);
		else output("<form action='mail.php?op=reply' method='POST'><textarea name='reply' placeholder='Reply:' class='input' rows='3' cols='92'></textarea><br><input valign='50px' type='submit' value='Submit'>",true);
	output("<input type='hidden' name='conversation' value='".httpget('id')."'></form>",true);
	output("</td></tr></table>",true);
}


if ($op == 'reply'){
	$posted = httpallpost();
	$current = db_fetch_assoc(db_query("SELECT title, originator FROM conversations WHERE conversation = ".$posted['conversation']." ORDER BY id+0 DESC LIMIT 0,1"));
	db_query("INSERT INTO conversations (acctid,conversation,title,deleted,message,posted,originator) VALUES ('".$session['user']['acctid']."','".$posted['conversation']."','".$current['title']."','0','".addslashes($posted['reply'])."','".date("Y-m-d H:i:s")."','".$current['originator']."')");
	header('Location: mail.php?op=read_convo&id='.$posted['conversation']);
}



























//SEARCH FOR A CONVO / CREATE A NEW CONVO.
if ($op == 'search_convo') output("`n`n<form action='mail.php?op=createconvo' method='POST'><input type='search' name='target' placeholder='Start conversation with:' maxcols='75' width='75'><input type='submit' value='Search'></form>",true);
if ($op == 'createconvo'){
	debug(httpallpost());
	$sql = db_query('SELECT acctid FROM accounts WHERE login LIKE "%'.httppost('target').'%" ORDER BY superuser+0 DESC, acctid+0 ASC LIMIT 0,25');
	$build = array();
	while ($row = db_fetch_assoc($sql)){
		$target = $row['acctid'];
		array_push($build,$row['acctid']);
	}
	if (db_num_rows($sql) == 1) header("Location: mail.php?op=create_convo&target=".$target);
	if (count($build) == 0){
		output("`n`i`\$Sorry! There was an issue! We cannot find a player similar to that name!i");
		output("<form action='mail.php?op=createconvo' method='POST'><input type='search' name='target' placeholder='Try again:' maxcols='50' width='50'><input type='submit' value='Enter'></form>",true);
	}else{
		output("<form action='mail.php?op=create_convo' method='POST'><select name='target'>",true);
		foreach($build as $key => $val){
			$name = db_fetch_assoc(db_query("SELECT login AS l FROM accounts WHERE acctid = $val"));
			debug("<option  value='$val'>".$name['l']."</option>",true);
		}
		output("</select><input type='submit' value='Select'></form>",true);
	}
}
if ($op == 'create_convo'){
	if (httpget('target')) $target = httpget('target');
		else $target = httppost('target');
	$lastcreated = db_fetch_assoc(db_query("SELECT conversation FROM conversations ORDER BY conversation+0 DESC LIMIT 0,1"));
	add2convo($target,($lastcreated['conversation']+1));
	add2convo($session['user']['acctid'],($lastcreated['conversation']+1));
	$target = db_fetch_assoc(db_query("SELECT login,conversation,acctid FROM accounts WHERE acctid = $target"));
 	db_query("INSERT INTO conversations (acctid,conversation,message,posted,originator) VALUES ('1','".($lastcreated['conversation']+1)."','*".ucfirst($session['user']['name'])." has started a conversation with ".$target['name']."*','".date("Y-m-d H:i:s")."','".$session['user']['acctid']."')");
  	header("Location: mail.php");
}

//make kick function
if ($op == 'kick'){
	global $session;
	$target = httpget('target');
	$conversation = httpget('conversation');
	//Check for originator
	$row = db_fetch_assoc(db_query("SELECT originator FROM conversations WHERE conversation = $conversation ORDER BY id+0 DESC LIMIT 0,1"));
	if ($session['user']['acctid'] == $row['originator']) kickfromconvo($target,$conversation);
// 	else angelus("Glitch Attempt","Tried to kick a player from a conversation when they were not the originator.");
	header("Location: mail.php?op=read_convo&id=$conversation");
}

if ($op == 'leave'){
	global $session;
	kickfromconvo($session['user']['acctid'],httpget('id'));
	header("Location: mail.php");
}

if ($op == 'close'){
	$id = httpget('id');
	$myconvos = unserialize($session['user']['conversation']);
	if (!in_array($id,$myconvos)){
		$session['message'] = "`c`b`\$Your account has been flagged for attempting to perform a glitch.`b`c";
		// 		Need to make angelus()
		// 		angelus("Glitch Attempt","Tried to close conversation they are not in.");
	}else{
		$sql = db_query("SELECT acctid FROM conversations WHERE conversation = $id GROUP BY acctid");
		$num = 0;
		while ($num < db_num_rows($sql)){
			$row = db_fetch_assoc($sql);
			kickfromconvo($row['acctid'],$id);
			$num++;
		}
		$session['message'] = "`c`i`\$That conversation has been cleared! All participants have been notified and removed!`i`c";
		db_query("UPDATE conversations SET deleted = 1 WHERE conversation = $id");
	}
	header('Location: mail.php');
}

if ($op == "clear_alert"){
	db_query("DELETE FROM alerts WHERE id = ".httpget('id'));
	header('Location: mail.php');
}
popup_footer();
?>