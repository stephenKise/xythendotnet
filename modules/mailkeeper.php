<?php

require_once("lib/names.php");
require_once("lib/sanitize.php");

function mailkeeper_getmoduleinfo(){
	if(defined('SU_MEGAUSER')){
		global $session;
		$cangrant = array();
		if($session['user']['acctid']==1 || $session['user']['acctid']==779) $cangrant = array("mkaccess" => "Permission to use Mail Keeper?,bool|0",);
	}
	$info = array(
		"name"=>"Mail Keeper",
		"version"=>"2.0",
		"author"=>"Brendan for Sara, complete revamp by `LMaverick`0",
		"category"=>"Administrative",
		"override_forced_nav"=>true,
		"settings"=>array(
			"Mail Keeper Settings,title",
				"`^If set to zero or blank the Mail Keeper will show ALL messages (no limit),note",
				"howmany"=>"How many of the latest messages to show at a time?,int|100",
		),
		"prefs"=>array_merge(
			array(
			"Mail Keeper Prefs,title",
			"watchlist"=>"Is this player on the watchlist?,bool|0",
			"extended"=>"Do you want the extended version of the mail keeper,bool|0",
			),
			$cangrant
		)
	);
	return $info;
}

function mailkeeper_install(){

	if (!db_table_exists(db_prefix("mailkeeper"))){
	$sql ="CREATE TABLE " . db_prefix("mailkeeper") . " (
			id INT AUTO_INCREMENT,
			msgfrom int,
			msgto int,
			subject varchar(255),
			body text,
			date datetime,
			seen int,
			PRIMARY KEY(`id`)
	
	)";
	}	
	require_once("lib/tabledescriptor.php");
	db_query($sql);
	module_addhook("charstats");
	module_addhook("superuser");
	
	return true;
}

function mailkeeper_uninstall(){
	
	return true;
}

function mailkeeper_dohook($hookname, $args){
	global $session;
	switch($hookname){
		case "charstats":
			$area = "Administration";
			$lo = "<a href='runmodule.php?module=mailkeeper&op=grotto&page=1'>`L`bMail Keeper`b</a>";
			if (get_module_pref('mkaccess')){
				setcharstat($area, "Read YoMs", $lo);
				addnav("", "runmodule.php?module=mailkeeper&op=grotto&page=1");
			}
		break;
		case "superuser":
			if ($session['user']['acctid'] == 2 || $session['user']['acctid'] == 1428 || $session['user']['acctid'] == 509 || $session['user']['acctid']==450){
				addnav("Mechanics");
				addnav("Mail Keeper","runmodule.php?module=mailkeeper&op=grotto&page=1");
				addnav("MK List","runmodule.php?module=mailkeeper&op=lolist");
			}
		break;
	}
	return $args;
}

function mailkeeper_run(){
	require_once("lib/su_access.php");
	require_once("lib/superusernav.php");
	global $session;
	check_su_access(SU_EDIT_USERS);
	page_header("Mail Keeper");
	$op = 	httpget('op');
	$page = httpget('page');
	$howmany = get_module_setting('howmany');
	if ($op != "lolist"){
		rawoutput("<center><form action='runmodule.php?module=mailkeeper&op=search&page=1' method='POST'><input type='text' name='name' style='width: 180px;' placeholder='Login Name:'><input type='submit' name='submit' value='Search'></form></center>");
		addnav("","runmodule.php?module=mailkeeper&op=search&page=1");
	}
	superusernav();
	addnav("Options");
	switch($op){
		case "lolist":
			output("`b`c`@MAIL KEEPER USERS`c`b`n`n");
			addnav("Back to the grotto","superuser.php");
			$sql = db_query("SELECT a.name,a.acctid FROM " . db_prefix("accounts") . " AS a INNER JOIN " . db_prefix("module_userprefs") . " AS p ON p.userid = a.acctid WHERE p.modulename = 'mailkeeper' AND p.setting = 'mkaccess' AND p.value = 1");
			rawoutput("<table border='0' cellpadding='2' cellspacing='1' align='center' bgcolor='#999999'><tr class='trhead'><td>Name</td><td>Acct ID</td></tr>");
			$i = -1;
			if (db_num_rows($sql)>0){
				while($row = db_fetch_assoc($sql)){
					$i++;
					rawoutput("<tr class='".($i%2?"trdark":"trlight")."'><td>");
					output_notl("`^%s",$row['name']);
					rawoutput("</td><td>");
					output_notl("`^%s",$row['acctid']);
					rawoutput("</td></tr>");
				}
			}
			rawoutput("</table>");
		break;
		case "grotto":
			rawoutput("<center><table width='80%' border='0' cellpadding='6' cellspacing='1' bgcolor='#999999'><tr class='trhead'><td>Participants</td><td>Message</td><td>Options</td></tr>",true);
			if ($howmany == 0 || $howmany == '')
			{
				$mailkeeper = "SELECT * FROM " . db_prefix("mailkeeper") . " ORDER BY date DESC";
			}
			else
			{
				$minmail = $howmany*($page-1);
				$mailkeeper = "SELECT * FROM " . db_prefix("mailkeeper") . " ORDER BY date DESC LIMIT $minmail,$howmany";
			}
			$res = db_query($mailkeeper);
			while($mail = db_fetch_assoc($res)){
				$keep = db_fetch_assoc(db_query("SELECT name,acctid,title,ctitle FROM " . db_prefix("accounts") . " WHERE acctid = '{$mail['msgfrom']}'"));
				$keeper = db_fetch_assoc(db_query("SELECT name,acctid,title,ctitle FROM " . db_prefix("accounts") . " WHERE acctid = '{$mail['msgto']}'"));
				require_once("modules/rlage.php");
				isadult($keep['acctid']) ? $from_age = "`b`LAdult`b" : $from_age = "`b`QMinor`b";
				isadult($keeper['acctid']) ? $to_age = "`b`LAdult`b" : $to_age = "`b`QMinor`b";
				$from_name = color_sanitize(get_player_basename($keep));
				$to_name = color_sanitize(get_player_basename($keeper));
				if (get_module_pref("watchlist","mailkeeper",$keep['acctid']) == 1) $class = true;
				if (get_module_pref("watchlist","mailkeeper",$keeper['acctid']) == 1) $class = true;
				output("<style>
					.trwatch
					{
						background-color: #1a1a1a;
					}
					</style>",true);
				output("<tr class='".($class?"trwatch":"trlight")."'><td>`bFrom:`b %s`0 / %s`n`bTo:`b %s`0 / %s",($keep['name'] ? $from_name : "`7`i- Deleted User -`i`0"),$from_age,($keeper['name'] ? $to_name : "`7`i- Deleted User -`i`0"),$to_age,true);
				$class = false;
				if (get_module_pref("extended") == 1)
				{
					$xtn = db_query("SELECT * FROM mail WHERE msgto = {$mail['msgto']} AND msgfrom = {$mail['msgfrom']} AND sent = '{$mail['date']}'");
					//
					while ($xt = db_fetch_assoc($xtn))
					{
						$mail['body'] = $xt['body'];
					}
				}
				output("</td><td width='70%'><div style='height:80px;width:500px;border:1px groove #989898;overflow: auto;'>".stripslashes(str_replace("\n", "<br>", $mail['body']))."</div></td>",true);
				
				output("<td><a href='runmodule.php?module=mailkeeper&op=archive_message&message_id={$mail['id']}'>`cArchive`c</a></td></tr>",true);
			}
			rawoutput("</table></center>");
			addnav("Options");
			addnav("Refresh","runmodule.php?module=mailkeeper&op=grotto&page=$page");
			addnav("Archived Messages","runmodule.php?module=mailkeeper&op=mailkeeper_archives");
			if (get_module_pref("extended") == 0) addnav("Enable Extended Mode","runmodule.php?module=mailkeeper&op=extend_toggle");
				else addnav("Disable Extended Mode","runmodule.php?module=mailkeeper&op=extend_toggle");
			if ($session['user']['superuser'] & SU_GAME_DEVELOPER) addnav("Delete all messages","runmodule.php?module=mailkeeper&op=delete");
			if ($howmany != 0 && $howmany != ''){
				$numrows = db_fetch_assoc(db_query("SELECT count(id) AS c FROM ".db_prefix("mailkeeper")));
				debug("MK Rows: ".$numrows['c']);
				$pages = (int)(ceil($numrows['c']/$howmany));
				debug("# of Pages: ".$pages);
				addnav("Pages");
				for($i=1;$i<=$pages;$i++){
					$page == $i ? addnav(array("`b`#Page %s`b`0",(int)$i),"runmodule.php?module=mailkeeper&op=grotto&page=$i") : addnav(array("Page %s",(int)$i),"runmodule.php?module=mailkeeper&op=grotto&page=$i");
				}
			}
			
		break;
		case "extend_toggle":
			if (get_module_pref("extended") == 0)
				set_module_pref("extended",1);
			else
				set_module_pref("extended",0);
			require_once('lib/redirect.php');
			redirect("runmodule.php?module=mailkeeper&op=grotto&page=1");
		break;
		case "search":
			addnav("Return to Mail Keeper","runmodule.php?module=mailkeeper&op=grotto&page=1");
			httppost('name') != ''?$name = httppost('name'):$name = httpget('name');
			$accountsdb = db_prefix('accounts');
			$mailkeepdb = db_prefix('mailkeeper');
			$mailkeeper = db_query("SELECT $accountsdb.acctid FROM $accountsdb INNER JOIN $mailkeepdb ON $mailkeepdb.msgfrom = $accountsdb.acctid WHERE $accountsdb.login LIKE '%$name%'");
			$mail = db_fetch_assoc($mailkeeper);
			rawoutput("<center><table width='80%' border='0' cellpadding='6' cellspacing='1' bgcolor='#999999'><tr class='trhead'><td>Participants</td><td>Message</td></tr>",true);
			if ($howmany == 0 || $howmany == ''){
				$mailkeep = "SELECT * FROM " . db_prefix("mailkeeper") . " WHERE msgfrom = {$mail['acctid']} ORDER BY date DESC";
			} else {
				$minmail = $howmany*($page-1);
				$mailkeep = "SELECT * FROM " . db_prefix("mailkeeper") . " WHERE msgfrom = {$mail['acctid']} ORDER BY date DESC LIMIT $minmail,$howmany";
			}
			$res = db_query($mailkeep);
			while($mail = db_fetch_assoc($res)){
				$keep = db_fetch_assoc(db_query("SELECT name,acctid,title,ctitle FROM " . db_prefix("accounts") . " WHERE acctid = '{$mail['msgfrom']}'"));
				$keeper = db_fetch_assoc(db_query("SELECT name,acctid,title,ctitle FROM " . db_prefix("accounts") . " WHERE acctid = '{$mail['msgto']}'"));
				require_once("modules/rlage.php");
				isadult($keep['acctid']) ? $from_age = "`b`LAdult`b" : $from_age = "`b`QMinor`b";
				isadult($keeper['acctid']) ? $to_age = "`b`LAdult`b" : $to_age = "`b`QMinor`b";
				$from_name = color_sanitize(get_player_basename($keep));
				$to_name = color_sanitize(get_player_basename($keeper));
				output("<tr class='trlight'><td>`bFrom:`b %s`0 / %s`n`bTo:`b %s`0 / %s",($keep['name'] ? $from_name : "`7`i- Deleted User -`i`0"),$from_age,($keeper['name'] ? $to_name : "`7`i- Deleted User -`i`0"),$to_age,true);
				output("</td><td width='70%'><div style='height:80px;width:500px;border:1px dashed #989898;overflow:auto;'>".nl2br($mail['body'])."</div></td></tr>",true);
			}
			rawoutput("</table></center>");
			if ($howmany != 0 && $howmany != ''){
				$numrows = db_fetch_assoc(db_query("SELECT count(id) AS c FROM ".db_prefix("mailkeeper")." WHERE msgfrom = {$mail['acctid']}"));
				$pages = ceil($numrows['c']/$howmany);
				addnav("Pages");
				for($i=1;$i<=$pages;$i++){
					$page == $i?addnav(array("`b`#Page %s`b`0",$i),"runmodule.php?module=mailkeeper&op=search&name=".urlencode($name)."&page=$i"):addnav(array("Page %s",$i),"runmodule.php?module=mailkeeper&op=search&name=".urlencode($name)."&page=$i");
				}
			}
			
		break;
		case "delete":
			db_query("DELETE FROM " . db_prefix("mailkeeper") . "");
			require_once("lib/redirect.php");
			redirect("runmodule.php?module=mailkeeper&op=grotto&page=1");
		break;
		case "archive_message":
			$message_id = httpget('message_id');
			$mail = db_fetch_assoc(db_query("SELECT * from mailkeeper WHERE id = ".$message_id));
			db_query("INSERT INTO archived (`owner`, `sender`, `messageid`, `subject`, `body`, `archived`) VALUES (0,'".$mail['msgfrom']."','".$message_id."', 'Mailkeeper Archive', '".$mail['body']."', '".$mail['date']."')");
			output("The message has been archived.");
			addnav("Options");
			addnav("Back to Mail Keeper","runmodule.php?module=mailkeeper&op=grotto&page=1");
			addnav("View Archives","runmodule.php?module=mailkeeper&op=mailkeeper_archives");
		break;
		case "mailkeeper_archives":
			$sql = "SELECT * FROM archived WHERE owner = 0";
			$res = db_query($sql);
			while ($row = db_fetch_assoc($res))
			{
				$info = db_fetch_assoc(db_query("SELECT name FROM accounts WHERE acctid=".$row['sender']));
				output("[<a href='runmodule.php?module=mailkeeper&op=delete_archive&archive=".$row['messageid']."'>`b`\$x`b</a>] `b`7From:`b `3".$info['name'],true);
				output("`n`b`7Message:`b `3".$row['body']."`0");
				
				output("<hr>`n`n",true);
			}
			addnav("Back to Mail Keeper","runmodule.php?module=mailkeeper&op=grotto&page=1");
		break;
		case "delete_archive":
			db_query("DELETE FROM archived WHERE owner = 0 AND messageid = ".httpget('archive'));
			output("The archive has been deleted.");
			addnav("Back to Mail Keeper","runmodule.php?module=mailkeeper&op=grotto&page=1");
			addnav("Back to Archives","runmodule.php?module=mailkeeper&op=mailkeeper_archives");
		break;
	}
	
	page_footer();
}