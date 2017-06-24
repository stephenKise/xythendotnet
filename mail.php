 <?php
 global $readfunctionsarray;
define("OVERRIDE_FORCED_NAV",true);
require_once("common.php");
require_once("lib/http.php");
require_once('lib/conversations.php');
tlschema("mail");
$op = httpget('op');
$id = (int)httpget('id');
db_query("DELETE FROM alerts WHERE message = '' AND acctid = {$session['user']['acctid']}");
// invalidatedatacache("alerts-{$session['user']['acctid']}");
// For some reason the mail loads slower when we do dis.
if($op=="del"){
	$sql = "DELETE FROM " . db_prefix("mail") . " WHERE msgto='".$session['user']['acctid']."' AND messageid='$id'";
	db_query($sql);
	invalidatedatacache("mail-{$session['user']['acctid']}");
	header("Location: mail.php");
	exit();
}elseif($op=="process"){
	$msg = httppost('msg');
	if (!is_array($msg) || count($msg)<1){
		$session['message'] = "`\$`bYou cannot delete zero messages!  What does this mean?  You pressed \"Delete Checked\" but there are no messages checked!  What sort of world is this that people press buttons that have no meaning?!?`b`0";
		header("Location: mail.php");
		exit();
	}else{
		$sql = "DELETE FROM " . db_prefix("mail") . " WHERE msgto='".$session['user']['acctid']."' AND messageid IN ('".join("','",$msg)."')";
		db_query($sql);
		invalidatedatacache("mail-{$session['user']['acctid']}");
		header("Location: mail.php");
		exit();
	}
}elseif ($op=="unread"){
	$sql = "UPDATE " . db_prefix("mail") . " SET seen=1 WHERE msgto='".$session['user']['acctid']."' AND messageid='$id'";
	db_query($sql);
	invalidatedatacache("mail-{$session['user']['acctid']}");
	header("Location: mail.php");
	exit();
}elseif ($op == "clear_alert"){
	global $session;
	$sql = db_fetch_assoc(db_query("SELECT acctid FROM alerts WHERE id = ".httpget('id')));
	if ($session['user']['acctid'] == $sql['acctid'] && httpget('id') != "all")
	{
		db_query("DELETE FROM alerts WHERE id = ".httpget('id'));
		header('Location: mail.php');
	}
	else
	{
		db_query("DELETE FROM alerts WHERE acctid = ".$session['user']['acctid']);
		header('Location: mail.php');
	}
}

popup_header("Mailbox");
$args = array();
// to use this hook,
// just call array_push($args, array("pagename", "functionname"));,
// where "pagename" is the name of the page to forward the user to,
// and "functionname" is the name of the mail function to add

output("<table width='100%' border='0' cellpadding='0' cellspacing='10px'>",TRUE);
rawoutput("<tr><td valign=\"top\" width='150px' nowrap>");
output("&bull;<a href='mail.php'>`tInbox</a>`n",TRUE);
output("&bull;<a href='runmodule.php?module=outbox'>`tOutbox</a>`n",TRUE);
output("&bull;<a href='mail.php?&op=address'>`tCompose</a>`n",TRUE);
output("&bull;<a href='petition.php'>`\$Petition for Help</a>`n",TRUE);
output("&bull;<a href='motd.php?op=view_current_poll'>`i`&Recent Content Poll`i</a>`n",true);
modulehook("mailfunctions", $args);
output_notl("</td><td valign='top' >",true);

if($op=="send"){
	require("lib/mail/case_send.php");
}

switch ($op) {
case "read":
	require("lib/mail/case_read.php");
	break;
case "address":
	require("lib/mail/case_address.php");
	break;
case "write":
	require("lib/mail/case_write.php");
	break;
case "archive":
	if (httpget('msgid') != ""){
		db_query("UPDATE mail SET seen = 2 WHERE messageid = ".httpget('msgid'));
		header('Location: mail.php');
	}
	break;
	
case 'read_convo':
	if (isset($session['message'])){
		output("`i`b`c{$session['message']}`c`b`i`n");
		unset($session['message']);
	}
	$myconvos = unserialize($session['user']['conversation']);
	if ((!in_array(httpget('id'),$myconvos)) && $session['user']['conversation'] != httpget('id')){
		//angelus("Glitch Attempt","Tried to spy on a conversation they are not allowed in.");
		header('Location: mail.php');
	}
	
	debug($session['user']['prefs']['convo_amount']);
	$num = db_fetch_assoc(db_query("SELECT count(id) AS ids FROM conversations WHERE conversation = ".httpget('id')));
	if (($num['ids']-$session['user']['prefs']['convo_amount']) >= 0) $arith = ($num['ids']-$session['user']['prefs']['convo_amount']);
		else $arith = 0;
		debug($num['ids']);
		debug($arith);
	$sql = db_query("SELECT * FROM conversations WHERE conversation = ".httpget('id')." AND deleted = 0 ORDER BY id+0 ASC LIMIT $arith,10");
	output("<table align='center' width='700px'>",true);
	while($row = db_fetch_assoc($sql)){
		if ($session['user']['acctid'] == $row['originator']) $owner = 1;
		if ($owner == 1) $options = "`2[<a href='mail.php?op=delete&delete={$row['id']}&convo_id={$row['conversation']}'>Del</a> `2| <a href='mail.php?op=kick&conversation={$row['conversation']}&target={$row['acctid']}'>Kick</a>`2] ";
		$target = db_fetch_assoc(db_query("SELECT name FROM accounts WHERE acctid = {$row['acctid']}"));
		if (substr($row['message'],0,1) == "*" && substr($row['message'],-1,1) == "*") output("<tr><td colspan='3' align='center'><fieldset style='border: 0px;'><legend style='padding: 0.1em 1em; font-size:80%; text-align: center;'>$options `&".($target['name']?$target['name']:"`^`iSystem`i")." `&- ".timeoffset($row['posted'])."</legend>".preg_replace('/(..+?)(\1){2,}/','$1',stripslashes($row['message']))."</fieldset><br></td></tr>",true);
			else if($row['acctid'] == $session['user']['acctid']) output("<tr><td width='350px'></td><td width='50px'></td><td width='350px'><fieldset style='border: 1px solid #222; text-align: right; -webkit-border-radius: 6px; -moz-border-radius: 6px; border-radius: 6px;'><legend style='padding: 0.1em 1em; font-size: 80%; text-align: right;'>$options`& {$target['name']} `&- ".timeoffset($row['posted'])."</legend>".preg_replace('/(..+?)(\1){2,}/','$1',stripslashes($row['message']))."</fieldset></td></tr>",true);
			else output("<tr><td width='350px'><fieldset style='border: 1px solid #666; -webkit-border-radius: 6px; -moz-border-radius: 6px; border-radius: 6px;'><legend style='padding: 0.1em 1em; font-size:80%; text-align:left;'>$options `&{$target['name']} `&- ".timeoffset($row['posted'])."</legend>".preg_replace('/(..+?)(\1){2,}/','$1',stripslashes($row['message']))."</fieldset></td><td width='50px'></td><td width='350px'></td></tr>",true);
	}
	output("<tr><td colspan='3'><hr style='height: 1px; border:none; background-color:#777;'>",true);
	if ($owner == 1) output("`c`2[<a href='mail.php?op=search_convo&id=".httpget('id')."'>Add</a> `2| <a href='#'>Rename</a> `2| <a href='mail.php?op=close&id=".httpget('id')."'>Close</a>`2]`c<br>",true);
	else output("<a href='mail.php?op=leave&id=".httpget('id')."'>leave</a>",true);
	require_once('lib/forms.php');
	if ($session['user']['prefs']['convo_textarea'] == 1)
	{
		output("<form action='mail.php?op=reply' method='POST'>`<<input type='text' name='reply' placeholder='Reply:' size='90'>`<`><button class='input' onclick='mail.php?op=reply'>Post</button><button class='input' value='Refresh!' onclick='mail.php?op=reply'>Refresh</button>`>",true);
	}
	else
	{ 
		output("<form action='mail.php?op=reply' method='POST'>",true);
		// output(previewfield("reply",false,false,false,array("type"=>"textarea","class"=>"input","rows"=>"3","cols"=>"92"))."<textarea name='reply' placeholder='Reply:' class='input' rows='3' cols='92'></textarea><br><button class='input' onclick='mail.php?op=reply'>Post</button><button class='input' value='Refresh!' onclick='mail.php?op=reply'>Refresh</button>",true);
		output(previewfield("reply",false,false,false,array("type"=>"textarea","class"=>"input","rows"=>"3","cols"=>"92"))."<br><button class='input' onclick='mail.php?op=reply'>Post</button><button class='input' value='Refresh!' onclick='mail.php?op=reply'>Refresh</button>",true);
	}
	output("<input type='hidden' name='conversation' value='".httpget('id')."'></form>",true);
	output("</td></tr></table>",true);
break;
case 'reply':
	$posted = httpallpost();
	if ($posted['reply'] != '')
	{
		$current = db_fetch_assoc(db_query("SELECT title, originator FROM conversations WHERE conversation = ".$posted['conversation']." ORDER BY id+0 DESC LIMIT 0,1"));
		db_query("INSERT INTO conversations (acctid,conversation,title,deleted,message,posted,originator) VALUES ('".$session['user']['acctid']."','".$posted['conversation']."','".$current['title']."','0','".addslashes($posted['reply'])."','".date("Y-m-d H:i:s")."','".$current['originator']."')");
	}
	header('Location: mail.php?op=read_convo&id='.$posted['conversation']);
break;
case 'search_convo':
	if (httpget('id') == '') output("`n`n<form action='mail.php?op=createconvo' method='POST'><input type='search' name='target' placeholder='Start conversation with:' maxcols='75' width='75'><input type='submit' value='Search'></form>",true);
		else output("`n`n<form action='mail.php?op=addtoconvo' method='POST'><input type='hidden' name='convo_id' value='".httpget('id')."'><input type='search' name='target' placeholder='Add to conversation:' maxcols='75' width='75'><input type='submit' value='Search'></form>",true);
	
break;
case 'createconvo':
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
break;
case 'addtoconvo':
	debug(httpallpost());
	$sql = db_query('SELECT acctid FROM accounts WHERE login LIKE "%'.httppost('target').'%" ORDER BY superuser+0 DESC, acctid+0 ASC LIMIT 0,25');
	$build = array();
	while ($row = db_fetch_assoc($sql)){
		$target = $row['acctid'];
		array_push($build,$row['acctid']);
	}
// 	if (db_num_rows($sql) == 1) header("Location: mail.php?op=addto_convo&target=".$target."&convo_id=".httppost('convo_id'));
	if (count($build) == 0){
		output("`n`i`\$Sorry! There was an issue! We cannot find a player similar to that name!i");
		output("<form action='mail.php?op=addto_convo' method='POST'><input type='hidden' name='convo_id' value='".httppost('convo_id')."'><input type='search' name='target' placeholder='Try again:' maxcols='50' width='50'><input type='submit' value='Enter'></form>",true);
	}else{
		output("<form action='mail.php?op=addto_convo' method='POST'><select name='target'>",true);
		foreach($build as $key => $val){
			$name = db_fetch_assoc(db_query("SELECT login AS l FROM accounts WHERE acctid = $val"));
			debug("<option  value='$val'>".$name['l']."</option>",true);
		}
		output("</select><input type='hidden' name='convo_id' value='".httppost('convo_id')."'><input type='submit' value='Select'></form>",true);
	}
break;
case 'addto_convo':
debug(httpallpost());
	if (httpget('target')) $target = httpget('target');
		else $target = httppost('target');
	$lastcreated = db_fetch_assoc(db_query("SELECT conversation FROM conversations ORDER BY conversation+0 DESC LIMIT 0,1"));
	if (httpget('convo_id')) $convo_id = httpget('convo_id');
		else $convo_id = httppost('convo_id');
	
	debug($convo_id);
	debug($target);
	add2convo($target,$convo_id);
	$target = db_fetch_assoc(db_query("SELECT login,conversation,acctid FROM accounts WHERE acctid = $target"));
 	db_query("INSERT INTO conversations (acctid,conversation,message,posted,originator) VALUES ('0','".$convo_id."','*".ucfirst($session['user']['login'])." has added ".ucfirst($target['login'])." to the conversation*','".date("Y-m-d H:i:s")."','".$session['user']['acctid']."')");
  	header("Location: mail.php");
break;
case 'create_convo':
	if (httpget('target')) $target = httpget('target');
		else $target = httppost('target');
	$lastcreated = db_fetch_assoc(db_query("SELECT conversation FROM conversations ORDER BY conversation+0 DESC LIMIT 0,1"));
	add2convo($target,($lastcreated['conversation']+1));
	add2convo($session['user']['acctid'],($lastcreated['conversation']+1));
	$target = db_fetch_assoc(db_query("SELECT login,conversation,acctid FROM accounts WHERE acctid = $target"));
 	db_query("INSERT INTO conversations (acctid,conversation,message,posted,originator) VALUES ('0','".($lastcreated['conversation']+1)."','*".ucfirst($session['user']['login'])." has started a conversation with ".ucfirst($target['login'])."*','".date("Y-m-d H:i:s")."','".$session['user']['acctid']."')");
  	header("Location: mail.php");
break;
case 'kick':
	global $session;
	$target = httpget('target');
	$row = db_fetch_assoc(db_query("SELECT login FROM accounts WHERE acctid = $target"));
	$target_name = $row['login'];
	$conversation = httpget('conversation');
	//Check for originator
	$row = db_fetch_assoc(db_query("SELECT originator,title FROM conversations WHERE conversation = $conversation ORDER BY id+0 DESC LIMIT 0,1"));
	if ($session['user']['acctid'] == $row['originator']){
		kickfromconvo($target,$conversation);
		db_query("INSERT INTO conversations (acctid,conversation,title,message,posted,originator) VALUES ('0','".$conversation."','{$row['title']}','*".ucfirst($session['user']['login'])." has kicked ".ucfirst($target_name)." from the conversation*','".date("Y-m-d H:i:s")."','".$session['user']['acctid']."')");
	}
// 	else angelus("Glitch Attempt","Tried to kick a player from a conversation when they were not the originator.");
	header("Location: mail.php?op=read_convo&id=$conversation");
break;
case 'leave':
	global $session;
	kickfromconvo($session['user']['acctid'],httpget('id'));
	$current = db_query('SELECT title FROM conversations WHERE conversation = '.httpget('id'));
	db_query("INSERT INTO conversations (acctid,conversation,title,message,posted,originator) VALUES ('0','".httpget('id')."',{$current['title']},'*".ucfirst($session['user']['login'])." has left the conversation*','".date("Y-m-d H:i:s")."','".$session['user']['acctid']."')");
	header("Location: mail.php");
break;
case 'close':
	$id = httpget('id');
	$myconvos = unserialize($session['user']['conversation']);
	if (!in_array($id,$myconvos)){
		$session['message'] = "`c`b`\$Your account has been flagged for attempting to perform a glitch.`b`c";
		// 		Need to make angelus()
		// 		angelus("Glitch Attempt","Tried to close conversation they are not in.");
		// ^DISREGARD; OTR
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
break;
case 'clear_alert':
	db_query("DELETE FROM alerts WHERE id = ".httpget('id'));
	header('Location: mail.php');
break;
case 'delete':
	$delete = httpget('delete');
	$convo_id = httpget('convo_id');
	$originator = db_fetch_assoc(db_query("SELECT acctid,originator FROM conversations WHERE id = {$delete}"));
	if (in_array($session['user']['acctid'],$originator)) db_query("UPDATE conversations SET deleted = 1 WHERE id = {$delete}");
		else header("Location: mail.php");
	header("Location: mail.php?op=read_convo&id=$convo_id");
break;
default:
 	require("lib/mail/case_default.php");
	break;
}
rawoutput("</td></tr></table>",TRUE);

popup_footer();
?>