<?php

tlschema("user");

$subop = httpget("subop");

if ($subop == "add_ban")
{
	$row = db_fetch_assoc(db_query("SELECT name,lastip,uniqueid,emailaddress FROM " . db_prefix("accounts") . " WHERE acctid=\"$userid\""));
	
	if ($row['name'] != "") output("Ban information for: `\$".$row['name']);
	
	output("<form action='user.php?op=bans&subop=save_ban' method='POST'>",true);
	output("Set up a new ban by entering the accounts IP. The ID and Email Address of the entered IP will automatically be checked.`n`n");
	output("<input name='ip' id='ip' placeholder='IP to Ban:' value=\"".$row['lastip']."\">`n",true);
	output("<input name='reason' size=50 placeholder=\"Reason For Ban:\" value=\"\">",true);
	output_notl("`n");
	$confirm_ban = translate_inline("Are you sure you wish to issue this ban?");
	rawoutput("<input type='submit' class='button' value='Post Ban' onClick='return confirm(\"$confirm_ban\");'>");
	rawoutput("</form>");
	addnav("","user.php?op=bans&subop=save_ban");
	
	if ($row['name']!="")
	{
		$id = $row['uniqueid'];
		$ip = $row['lastip'];
		$email = $row['emailaddress'];
		$x = 0;
		
		$sql = "SELECT acctid,name,emailaddress,lastip,uniqueid FROM accounts WHERE lastip='".$ip."' OR emailaddress='".$email."' OR uniqueid='".addslashes($id)."' ORDER BY lastip";
		$result = db_query($sql);
		if (db_num_rows($result)>1)
		{
			while ($row = db_fetch_assoc($result))
			{
				if ($x == 0) output("The following accounts will also be banned due to the IP, ID, and Email Address of the entered account: ");
				if ($userid != $row['acctid']) output("`n`bName:`b %s`0 `n`bEmail:`b %s`0 `n`bLast IP:`b %s`0 `n`bUnique ID:`b %s`0`n", $row['name'],$row['emailaddress'],$row['lastip'],$row['uniqueid']);
				$x++;
			}
		}
		output_notl("`n");
	}
}

if ($subop == "save_ban")
{
	$reason = httppost("reason");
	if ($reason == "") $reason = "You are unwelcome here.";
	
	$to_ban = db_fetch_assoc(db_query("SELECT name,emailaddress,uniqueid,lastip,lasthit FROM accounts WHERE lastip='".httppost("ip")."'"));

	db_query("INSERT INTO bans (ipfilter,uniqueid,banexpire,banreason,banner,lasthit) VALUES ('".$to_ban['lastip']."','".$to_ban['uniqueid']."','0000-00-00','".translate_inline($reason)."','".$session['user']['name']."','".$to_ban['lasthit']."')");
	output("%s ban rows entered.`n`n", db_affected_rows());
	output_notl("%s", db_error(LINK));
	
	db_query("UPDATE accounts SET loggedin=0, locked=1 WHERE lastip='".httppost("ip")."' OR emailaddress='".$to_ban['emailaddress']."' OR uniqueid='".$to_ban['uniqueid']."'");
}

if ($subop == "remove_ban")
{
	$ipfilter = httpget('ipfilter');
	
	if ($ipfilter != "")
	{
		db_query("DELETE FROM " . db_prefix("bans") . " WHERE ipfilter='".$ipfilter."'");
		output("%s ban rows deleted.`n`n", db_affected_rows());
		output_notl("%s`n", db_error(LINK));
	}
	
	$sql = "SELECT * FROM " . db_prefix("bans") . " $since ORDER BY banexpire";
	$result = db_query($sql);
	
	rawoutput("<table border=0 cellpadding=2 cellspacing=1 bgcolor='#999999'>");
	$ops = translate_inline("Ops");
	$bauth = translate_inline("Ban Author");
	$ipd = translate_inline("IP / ID");
	$mssg = translate_inline("Reason");
	$aff = translate_inline("Affects");
	
	rawoutput("<tr class='trhead'><td>$ops</td><td>$bauth</td><td>$ipd</td><td>$mssg</td><td>$aff</td></tr>");
	$i=0;
	while ($row = db_fetch_assoc($result)) 
	{
		$liftban = translate_inline("Remove Ban");
		rawoutput("<tr class='".($i%2?"trlight":"trdark")."'>");
		rawoutput("<td><a href='user.php?op=bans&subop=remove_ban&ipfilter=".URLEncode($row['ipfilter'])."&uniqueid=".URLEncode($row['uniqueid'])."'>");
		output_notl("%s", $liftban, true);
		rawoutput("</a>");
		addnav("","user.php?op=bans&subop=remove_ban&ipfilter=".URLEncode($row['ipfilter'])."&uniqueid=".URLEncode($row['uniqueid']));
		rawoutput("</td><td>");
		output_notl("`&%s`0", $row['banner']);
		rawoutput("</td><td>");
		if ($row['uniqueid'] == "") $row['uniqueid'] = "No ID Entered";
		output_notl("%s / %s", $row['ipfilter'], $row['uniqueid']);
		rawoutput("</td><td>");
		output_notl("%s", $row['banreason']);
		rawoutput("</td><td>");
		
		$affected = "";
		$sql_affected = db_query("SELECT login,lastip,uniqueid FROM accounts WHERE lastip='".$row['ipfilter']."' OR uniqueid='".$row['uniqueid']."'");
		
		while($row_affected = db_fetch_assoc($sql_affected))
		{
			$affected .= "-".$row_affected['login']."`n";
		}
		
		if ($affected == "") $affected = "`iDeleted User`i";
		output_notl("%s", $affected, true);
		rawoutput("</td></tr>");
		$i++;
	}
	rawoutput("</table>");
}

?>