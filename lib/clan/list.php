<?php
	page_header("Guild Listing");
	require_once('lib/level.php');
	$registrar=getsetting('clanregistrar','`%Karissa');
	addnav("List Guilds");
	$sql = "SELECT MAX(" . db_prefix("clans") . ".clanid) AS clanid, clanexp, clanad, MAX(clanshort) AS clanshort, MAX(clanname) AS clanname,count(" . db_prefix("accounts") . ".acctid) AS c FROM " . db_prefix("clans") . " LEFT JOIN " . db_prefix("accounts") . " ON " . db_prefix("clans") . ".clanid=" . db_prefix("accounts") . ".clanid AND clanrank>".CLAN_APPLICANT." GROUP BY " . db_prefix("clans") . ".clanid ORDER BY clanexp DESC";
	$result = db_query($sql);
	if (db_num_rows($result)>0){
		output("`7You ask %s`7 for the Guild List.  She points you toward a marquee board near the entrance of the lobby that lists the guilds.`0`n`n",$registrar);
		$v = 0;
		$memb_n = translate_inline("%s");
		$memb_1 = translate_inline("%s");
		rawoutput('<table cellspacing="0" cellpadding="2" align="left" width="100%" align="center"><tr><td width="65%">Guild Name</td><td align="center">Members</td><td align="right">Level</td></tr>');
		while ($row = db_fetch_assoc($result)){
			if ($row['c']==0){
				$sql = "DELETE FROM " . db_prefix("clans") . " WHERE clanid={$row['clanid']}";
				db_query($sql);
			}else{
				rawoutput('<tr>', true);
				if ($row['c'] == 1) {
					$memb = sprintf($memb_1, $row['c']);
				} else {
					$memb = sprintf($memb_n, $row['c']);
				}
				
				$clanlevel = 1;
				while($row['clanexp']>=experience($clanlevel)){
					$current = $clanlevel;
					$clanlevel++;
				}
				if ($memb != 1) $members = "members";
					else $members = "member";
// 				if ($row['clanad'] == "") $row['clanad'] = "`iThis Guild has not set up an advertisement just yet!`i";
// 				output_notl("<td align='left'>`&&#60;`2%s`&&#62; <a href='clan.php?detail=%s'>`@%s</a> </td><td align='center'>`^%s %s</td><td align='right'>`QLevel %s</td>".
// 						$row['clanshort'],
// 						$row['clanid'],
// 						$row['clanname'],
// 						$memb,
// 						$members,
// 						$current,
// 						true);
// 						
						
				output_notl("<td align='left'>`n`&&#60;`2%s`&&#62; <a href='clan.php?detail=%s'>`@%s</a> </td><td align='center'>`^%s %s</td><td align='right'>`QLevel %s</td>",
// 					"<tr><td colspan='3' align='center'>`&%s`n`n</td></tr>",
						$row['clanshort'],
						$row['clanid'],
						$row['clanname'],
						$memb,
						$members,
						$current,
// 						$row['clanad'],
						true);
				if ($row['clanad'] != "") output("<tr><td colspan='3' align='center'>`&%s</td></tr>",$row['clanad'],true);
				rawoutput('</tr>');
				addnav("","clan.php?detail={$row['clanid']}");
				$v++;
			}
		}
		rawoutput("</table>", true);
		addnav("Return to the Lobby","clan.php");
	}else{
		output("`7You ask %s`7 for the Guild listings.  She stares at you blankly for a few moments, then says, \"`5Sorry pal, no one has had enough gumption to start up a Guild yet.  Maybe that should be you, eh?`7\"",$registrar);
		addnav("Create a New Guild","clan.php?op=new");
		addnav("Return to the Lobby","clan.php");
	}

	page_footer();
?>