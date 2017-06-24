<?php

function baneditor_getmoduleinfo(){
	$info = array(
		"name"=>"Ban Editor",
		"author"=>"Chris Vorndran",
		"category"=>"Administrative",
		"version"=>"1.0",
		"download"=>"http://dragonprime.net/index.php?module=Downloads;sa=dlview;id=1074",
	);
	return $info;
}
function baneditor_install(){
	module_addhook("superuser");
	$banid = array(
		'banid'=>array('name'=>'banid', 'type'=>'int unsigned',	'extra'=>'not null auto_increment'),
		'key-PRIMARY'=>array('name'=>'PRIMARY', 'type'=>'primary key',	'unique'=>'1', 'columns'=>'banid'),
		'index-banid'=>array('name'=>'banid', 'type'=>'index', 'columns'=>'banid'));
	require_once("lib/tabledescriptor.php");
	synctable(db_prefix("bans"),$banid,true);
	return true;
}
function baneditor_uninstall(){
	return true;
}
function baneditor_dohook($hookname,$args){
	global $session;
	switch ($hookname){
		case "superuser":
			if ($session['user']['superuser'] & SU_EDIT_USERS){
				addnav("Editors");
				addnav("Ban Editor","runmodule.php?module=baneditor");
			}
			blocknav("baneditor.php");
			break;
	}
	return $args;
}
function baneditor_run(){
	global $session;

	addnav("Options");
	addnav("Add a Ban","runmodule.php?module=baneditor&op=add");
	addnav("List Bans","runmodule.php?module=baneditor");
	require_once("lib/superusernav.php");
	superusernav();
	$op = httpget('op');

	check_su_access(SU_EDIT_USERS);
	page_header("Ban Editor");
	tlschema("bans");

	$ip_filter = translate_inline("IP Filter");
	$filter = translate_inline("IP/ID");
	$unique_id = translate_inline("Unique ID");
	$banner_who = translate_inline("Banner");
	$ban_reason = translate_inline("Ban Reason");
	$affected = translate_inline("Affected Users");
	$duration = translate_inline("Duration");
	$none = translate_inline("None");

	if (httppost('add')){
		if (httppost('type') == "ip"){
			$doot = "ipfilter";
			$grab = httppost('ip');
		}else{
			$doot = "uniqueid";
			$grab = httppost('id');
		}
		$reason = addslashes(httppost('reason'));
		$duration = (int)httppost("duration");
		if ($duration == 0) $duration = "0000-00-00";
		else $duration = date("Y-m-d", strtotime("+$duration days"));
		$sql = "INSERT INTO ".db_prefix("bans")."
				(banner,$doot,banreason,banexpire)
				VALUES (\"{$session['user']['name']}\", \"$grab\", \"$reason\",\"$duration\")";
		db_query($sql);
		output("%s ban rows entered.`n`n", db_affected_rows());
	}elseif (httppost('edit')){
		$banid = httpget('banid');
		$post_ip = httppost('ip');
		$post_id = httppost('id');
		if ($ip == "")
			$ip = "ipfilter=ipfilter";
		else
			$ip = "ipfilter='$post_ip'";
		if ($id == "")
			$id = "uniqueid=uniqueid";
		else
			$id = "uniqueid='$post_id'";
		$banner = httppost('banner');
		$reason = addslashes(httppost('reason'));
		$duration = httppost('duration');
		$dur_sql = "banexpire='".date("Y-m-d H:i:s",strtotime("+$duration days"))."'";
		if ($duration == ""){
			$dur_sql = "banexpire=banexpire";
		}elseif ($duration == 0){
			$dur_sql = "banexpire = '0000-00-00 00:00:00'";
		}
		$sql = "UPDATE ".db_prefix("bans")."
				SET $ip, $id, banner='$banner', banreason='$reason', $dur_sql
				WHERE banid='$banid'";
		db_query($sql);
	}elseif (httppost('delete')){
		$id = httpget('id');
		$sql = "DELETE FROM ".db_prefix("bans")."
				WHERE banid='$id'";
		db_query($sql);
	}

	switch ($op){
		case "":
			db_query("DELETE FROM ".db_prefix("bans")." WHERE banexpire < \"".date("Y-m-d")."\" AND banexpire>'0000-00-00'");
			$duration =  httpget("duration");
			if ($duration == "") {
				$since = " WHERE banexpire <= '".date("Y-m-d H:i:s",strtotime("+2 weeks"))."' AND banexpire > '0000-00-00'";
					output("`bShowing bans that will expire within 2 weeks.`b`n`n");
			}else{
				if ($duration == "forever") {
					$since = "";
					output("`bShowing all bans.`b`n`n");
				}else{
					$since = " WHERE banexpire <= '".date("Y-m-d H:i:s",strtotime("+".$duration))."' AND banexpire > '0000-00-00'";
					output("`bShowing bans that will expire within %s.`b`n`n",$duration);
				}
			}
			$ops = translate_inline("Ops");
			$expiration = translate_inline("Ban Expiration");
			$edit = translate_inline("Edit");
			$del = translate_inline("Del");
			rawoutput("<table cellpadding='1' cellspacing='1' align='center' bgcolor='#666666' width='100%'>");
			rawoutput("<tr class='trhead'>");
			rawoutput("<td>$ops</td><td>$banner_who</td><td>$ban_reason</td><td>$filter</td><td>$affected</td><td>$expiration</td>");
			rawoutput("</tr>");
			$sql = "SELECT * FROM ".db_prefix("bans")." $since ORDER BY banexpire ASC";
			$res = db_query($sql);
			$i = 0;
			if (db_num_rows($res) > 0){
			while($row = db_fetch_assoc($res)){
				rawoutput("<tr class='".($i%2?"trlight":"trdark")."'><td style='text-align:center' nowrap>");
				// Using ipfilter, since the bans table doesn't have an banid, but it should ;)
				rawoutput("[ <a href='runmodule.php?module=baneditor&op=edit&id={$row['banid']}'>");
				addnav("","runmodule.php?module=baneditor&op=edit&id={$row['banid']}");
				output_notl($edit);
				rawoutput("</a> | ");
				rawoutput("<a href='runmodule.php?module=baneditor&op=delete&id={$row['banid']}'>");
				addnav("","runmodule.php?module=baneditor&op=delete&id={$row['banid']}");
				output_notl($del);
				rawoutput("</a> ]");
				rawoutput("</td><td style='text-align:center'>");
				output("`@%s",$row['banner']);
				rawoutput("</td><td style='text-align:center'>");
				require_once("lib/nltoappon.php");
				output("`@%s",nltoappon($row['banreason']));
				rawoutput("</td><td style='text-align:left' nowrap>");
				output_notl("%s`n",($row['ipfilter'] == "" ? "`^ID: `@{$row['uniqueid']}" : "`^IP: `@{$row['ipfilter']}"));
				rawoutput("</td><td style='text-align:left'>");
				$acc = db_prefix("accounts");
				$bans = db_prefix("bans");
				$names = "";
				$sqla = "SELECT DISTINCT $acc.name
						FROM $bans, $acc
						WHERE (ipfilter='".addslashes($row['ipfilter'])."'
								AND $bans.uniqueid='".addslashes($row['uniqueid'])."')
						AND ((substring($acc.lastip,1,length(ipfilter))=ipfilter "."AND ipfilter<>'')
							OR ($bans.uniqueid=$acc.uniqueid AND $bans.uniqueid<>''))";
				$resa = db_query($sqla);
				while($rowa = db_fetch_assoc($resa)){
					$names .= "{$rowa['name']}`n";
				}
				if (db_num_rows($resa)==0) $names = $none;
				output_notl($names);
				rawoutput("</td><td style='text-align:center'>");
				$expire= sprintf_translate("%s days",
						round((strtotime($row['banexpire'])+43200-strtotime("now"))/86400,0));
				if (substr($expire,0,2)=="1 ")
					$expire= translate_inline("1 day");
				if (date("Y-m-d",strtotime($row['banexpire'])) == date("Y-m-d"))
					$expire=translate_inline("Today");
				if (date("Y-m-d",strtotime($row['banexpire'])) ==
						date("Y-m-d",strtotime("1 day")))
					$expire=translate_inline("Tomorrow");
				if ($row['banexpire']=="0000-00-00")
					$expire=translate_inline("Never");
				output_notl("%s", $expire);
				rawoutput("</td></tr>");
				$i++;
			}
			}else{
				rawoutput("<tr class='trdark'><td colspan='6' align='center'>");
				output_notl($none);
				rawoutput("</td></tr>");
			}
			rawoutput("</table>");
			addnav("Will Expire Within");
			addnav("1 week","runmodule.php?module=baneditor&duration=1+week");
			addnav("2 weeks","runmodule.php?module=baneditor&duration=2+weeks");
			addnav("Forever","runmodule.php?module=baneditor&duration=forever");
			break;
		case "add":
			rawoutput("<form action='baneditor.php' method='POST'>");
			$ip = "";
			$id = "";
			if (httpget('userid') && httpget('userid') != $session['user']['acctid']){
				$sql = "SELECT name,lastip,uniqueid FROM ".db_prefix("accounts")." WHERE acctid='".httpget('userid')."'";
				$res = db_query($sql);
				$row = db_fetch_assoc($res);
				$ip = $row['lastip'];
				$id = $row['uniqueid'];
			}
			if (httpget('userid') == $session['user']['acctid'] ||
				($ip == $session['user']['lastip'] && $id == $_COOKIE['lgi'])){
				output("`#We aren't going to allow you to ban yourself.`n`n`0");
				$ip = "";
				$id = "";
			}
			rawoutput("<a href='runmodule.php?module=baneditor&op=search'>");
			output("Search for User");
			rawoutput("</a><br/><br/>");
			addnav("","runmodule.php?module=baneditor&op=search");
			rawoutput("<input type='radio' value='ip' name='type' checked='checked'> $ip_filter: <input type='text' name='ip' value='$ip'/><br/>");
			rawoutput("<input type='radio' value='id' name='type'> $unique_id: <input size='50' type='text' name='id' value='$id'/><br/>");
			rawoutput("$banner_who: <input size='50' type='text' value='{$session['user']['name']}' name='banner'><br/>");
			rawoutput("$ban_reason:<br/><textarea cols='50' rows='10' name='reason'></textarea>");
			rawoutput("<br/>");
			rawoutput("$duration: <input name='duration' type='text' value='14'/><br/>");
			output("`ienter 0 for a Permanent Ban`i`n`n");
			rawoutput("<input type='submit' class='button' value='".translate_inline("Add")."' name='add'/></form>");
			addnav("","baneditor.php");
			if (httpget('userid') 
				&& httpget('userid') != $session['user']['acctid']
				&& ($ip != "" || $id != "")){
				$name = $row['name'];
				output("`0To help locate similar users to `@%s`0, here are some other users who are close:`n", $name);
				output("`bSame ID (%s):`b`n", $id);
				$sql = "SELECT name, lastip, uniqueid, laston, gentimecount FROM " . db_prefix("accounts") . " WHERE uniqueid='".addslashes($id)."' ORDER BY lastip";
				$result = db_query($sql);
				while ($row = db_fetch_assoc($result)){
					output("`0• (%s) `%%s`0 - %s hits, last: %s`n", $row['lastip'],
							$row['name'], $row['gentimecount'],
							reltime(strtotime($row['laston'])));
				}
				output_notl("`n");

				$oip = "";
				$dots = 0;
				output("`bSimilar IP's`b`n");
				for ($x = strlen($ip); $x > 0; $x--){
					if ($dots>1) break;
					$thisip = substr($ip,0,$x);
					$sql = "SELECT name, lastip, uniqueid, laston, gentimecount FROM " . db_prefix("accounts") . " WHERE lastip LIKE '$thisip%' AND NOT (lastip LIKE '$oip') ORDER BY uniqueid";
					//output("$sql`n");
					$result = db_query($sql);
					if (db_num_rows($result)>0){
						output("• IP Filter: %s ", $thisip);
						rawoutput("<a href='#' onClick=\"document.getElementById('ip').value='$thisip'; document.getElementById('ipradio').checked = true; return false\">");
						output("Use this filter");
						rawoutput("</a>");
						output_notl("`n");
						while ($row=db_fetch_assoc($result)){
							output("&nbsp;&nbsp;",true);
							output("• (%s) [%s] `%%s`0 - %s hits, last: %s`n",
									$row['lastip'], $row['uniqueid'], $row['name'],
									$row['gentimecount'],
									reltime(strtotime($row['laston'])));
						}
						output_notl("`n");
					}
					if (substr($ip,$x-1,1)==".") {
						$x--;
						$dots++;
					}
					$oip = $thisip."%";
				}
			}
			break;
		case "edit":
			$id = httpget('id');
			$sql = "SELECT * FROM ".db_prefix("bans")." WHERE banid = '$id'";
			$res = db_query($sql);
			$row = db_fetch_assoc($res);
			$extra = "";
			if ($row['ipfilter']){
				$extra = "ipfilter='{$row['ipfilter']}'";
				$ip_check = "checked='checked'";
				$id_check = "";
			}
			if ($row['uniqueid']){
				$extra = "uniqueid='{$row['uniqueid']}'";
				$id_check = "checked='checked'";
				$ip_check = "";
			}
			$sql = "SELECT * FROM ".db_prefix("bans")." WHERE $extra";
			$res = db_query($sql);
			rawoutput("<form action='runmodule.php?module=baneditor&banid=$id' method='POST'>");
			while ($row = db_fetch_assoc($res)){
				rawoutput("<input type='radio' value='ip' name='type' $ip_check> $ip_filter: <input type='text' value='{$row['ipfilter']}' name='new'/><br/>");
				rawoutput("<input type='radio' value='id' name='type' $id_check> $unique_id: <input size='50' type='text' value='{$row['uniqueid']}' name='id'/><br/>");
				rawoutput("$banner_who: <input size='50' type='text' value='{$row['banner']}' name='banner'><br/>");
				rawoutput("$ban_reason:<br/><textarea cols='50' rows='10' name='reason'>".htmlentities($row['banreason'])."</textarea>");
				rawoutput("<br/>");
				rawoutput("$duration: <input name='duration' type='text'/><br/>");
				output("`ienter 0 for `bforever`b or nothing for no change`i`n");
			}
			rawoutput("<input type='submit' class='button' value='".translate_inline("Edit")."' name='edit'/></form>");
			addnav("","runmodule.php?module=baneditor&banid=$id");
			break;
		case "delete":
			$id = httpget('id');
			$sql = "SELECT ipfilter, uniqueid FROM ".db_prefix("bans")." WHERE banid='$id'";
			$r = db_fetch_assoc(db_query($sql));
			output("`#Are you sure you wish to delete the ban that falls underneath the IP/ID: %s%s?",$r['ipfilter'],$r['uniqueid']);
			rawoutput("<form action='runmodule.php?module=baneditor&id=$id' method='POST'>");
			rawoutput("<input type='submit' class='button' name='delete' value='".translate_inline("Delete")."'/></form>");
			addnav("","runmodule.php?module=baneditor&id=$id");
			break;
		case "search":
			$name = httppost('name');
			if ($name == ""){
				rawoutput("<form action='runmodule.php?module=baneditor&op=search' method='POST'>");
				output("Input Name:");
				rawoutput("<input name='name' type='text'><input class='button' type='submit' value='".translate_inline("Search")."'>");
				rawoutput("</form>");
			}else{
				$search = "%";
				for ($i = 0; $i < strlen($name); $i++){
					$search .= substr($name,$i,1)."%";
				}
				$sql = "SELECT name,acctid,lastip,uniqueid
						FROM ".db_prefix("accounts")." 
						WHERE (name LIKE '$search' OR login LIKE '$search') 
						LIMIT 25";
				$res = db_query($sql);
				$ops = translate_inline("Ops");
				$n = translate_inline("Name");
				$ip = translate_inline("IP");
				$id = translate_inline("Unique ID");
				rawoutput("<table border=0 cellpadding=1 cellspacing=2>");
				rawoutput("<tr class='trhead'><td>$ops</td><td>$n</td><td>$ip</td><td>$id</td></tr>");
				if (db_num_rows($res) > 0){
					while ($row = db_fetch_assoc($res)){
						$ac = $row['acctid'];
						rawoutput("<tr><td>");
						rawoutput("<a href='runmodule.php?module=baneditor&op=add&userid=".rawurlencode($ac)."'>");
						output("Ban");
						rawoutput("</a>");
						rawoutput("</td><td>");
						output_notl("%s", $row['name']);
						addnav("","runmodule.php?module=baneditor&op=add&userid=".rawurlencode($ac));
						rawoutput("</td><td>");
						output_notl("`@%s`0",$row['lastip']);
						rawoutput("</td><td>");
						output_notl("`@%s`0",$row['uniqueid']);
						rawoutput("</td></tr>");
					}
				}else{
					rawoutput("<tr><td>");
					rawoutput("<a href='runmodule.php?module=baneditor&op=search'>");
					output("Try Again?");
					rawoutput("</a></td></tr>");
				}
				rawoutput("</table>");	
			}
			addnav("","runmodule.php?module=baneditor&op=search");
			break;
	}
page_footer();
}
?>