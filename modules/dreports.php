<?php

function dreports_getmoduleinfo(){
	global $session;
	$info = array(
		"name"=>"Guild Reports",
		"author"=>"`&`i`bXpert`b`i`0",
		"version"=>"3.0",
		"category"=>"Clan",
		"prefs"=>array(
			"Guild Reports Prefs,title",
			"lastsent"=>"Date of last sent,viewonly|0000-00-00 00:00:00",
			// "user_autosend"=>"Opt out of automatic sending of the guild report?,bool|0",
		)
	);
	if ($session['user']['clanrank'] >= 30)
		$info['prefs']['user_autosend'] = "Opt out of automatic sending of the guild report?,bool|0";
	return $info;
}

function dreports_install(){
	module_addhook("newday");
	module_addhook("footer-clan");
	return true;
}

function dreports_uninstall(){
	return true;
}

function dreports_dohook($hookname,$args){
	global $session;
	require_once("lib/systemmail.php");
	$clan = $session['user']['clanid'];
	switch ($hookname){
		
		case "newday":
			require_once('lib/datetime.php');
			$time = gametimedetails();
			$time = $time['now'];
//			debug($time."DICK!");
//			debug(get_module_pref("lastsent"));
			if ($time != get_module_pref("lastsent")){
				set_module_pref("lastsent",$time);
				$gold = get_module_objpref("clans", $session['user']['clanid'], "vaultgold", "clanvault");
				$gems = get_module_objpref("clans", $session['user']['clanid'], "vaultgems", "clanvault");
				$vault = "`c`bStatus of your Guild Funds`n`n`^Gold: `&$gold`n`@Gems: `&$gems`b";
	// 			$sql = db_query("SELECT acctid,name,clanid FROM accounts WHERE clanrank>=30");
	// 			while($row = db_fetch_assoc($sql)){
					$msg = "";
					$msg .= "<center><table cellpadding='1' cellspacing='5'><tr><td>Clan Member</td><td>Amount of TKs</td><td>Gold Donated</td><td>Gems Donated</td></tr>";
					$sql2 = db_query("SELECT a.login AS login, a.acctid AS acctid, a.name AS name, a.clanrank AS clanrank, a.clanid AS clanid, a.golddonated AS golddonated, a.gemsdonated AS gemsdonated, t1.value AS dkstoday FROM accounts AS a INNER JOIN module_userprefs AS t1 ON t1.userid = a.acctid WHERE t1.modulename = 'multidkachievement' AND t1.setting = 'dkstoday' AND t1.value > 0 AND a.clanid='".$session['user']['clanid']."' ORDER BY clanrank DESC,dkstoday+0 DESC");
					while($row2 = db_fetch_assoc($sql2)){
						$msg .= "<tr><td>";
						$msg .= color_sanitize($row2['name']);
						$msg .= "</td><td align='center'>";
						$msg .= get_module_pref("dkstoday","multidkachievement",$row2['acctid']);
						$msg .= "</td><td align='center'>";
						$msg .=	$row2['golddonated'];
						$msg .= "</td><td align='center'>";
						$msg .=	$row2['gemsdonated'];
						$msg .= "</td></tr>";
					}
					$msg .= "</table></center></fieldset>";
					if ($session['user']['clanrank'] >= 30 && get_module_pref('user_autosend') == 0) systemmail($session['user']['acctid'],"`b`#Guild Report!`b`0","`c`b`&Greetings`b ".$session['user']['name']."`0`b`&!`b`c`n`n<fieldset><legend align='center'>`b`QDaily Guild Report`b</legend>`n`n$vault`n`n$msg");
					//Dumb as fuck...  ^ this is all you need. Timer will be made.
// 	 			}
				db_query("UPDATE accounts SET golddonated=0, gemsdonated=0");
			}
		break;
		
		case "footer-clan":
			//if ($session['user']['clanrank'] >= CLAN_LEADER){
				addnav("Finances");
				addnav("Guild Report","runmodule.php?module=dreports");
			//}
		break;
	
	}		
	return $args;
}

function dreports_run(){
	global $session;
	page_header("Guild Report");
	require_once("lib/redirect.php");
	require_once("lib/systemmail.php");
	$gold = get_module_objpref("clans", $session['user']['clanid'], "vaultgold", "clanvault");
	$gems = get_module_objpref("clans", $session['user']['clanid'], "vaultgems", "clanvault");
	$vault = "`c`bStatus of your Guild Funds`n`n`^Gold: `&$gold`n`@Gems: `&$gems`b";
	$msg = "";
	$msg .= "<center><table cellpadding='1' cellspacing='5'><tr><td>Clan Member</td><td>Amount of TKs</td><td>Gold Donated</td><td>Gems Donated</td></tr>";
	$sql2 = db_query("SELECT a.login AS login, a.acctid AS acctid, a.name AS name, a.clanrank AS clanrank, a.clanid AS clanid, a.golddonated AS golddonated, a.gemsdonated AS gemsdonated, t1.value AS dkstoday FROM accounts AS a INNER JOIN module_userprefs AS t1 ON t1.userid = a.acctid WHERE t1.modulename = 'multidkachievement' AND t1.setting = 'dkstoday' AND t1.value > 0 AND a.clanid='".$session['user']['clanid']."' ORDER BY clanrank DESC,dkstoday+0 DESC");
	while($row2 = db_fetch_assoc($sql2)){
		$msg .= "<tr><td>";
		$msg .= color_sanitize($row2['name']);
		$msg .= "</td><td align='center'>";
		$msg .= $row2['dkstoday'];
		$msg .= "</td><td align='center'>";
		$msg .=	$row2['golddonated'];
		$msg .= "</td><td align='center'>";
		$msg .=	$row2['gemsdonated'];
		$msg .= "</td></tr>";
	}
	$msg .= "</table></center></fieldset>";
	systemmail($session['user']['acctid'],"`b`#Guild Report!`b`0","`c`b`&Greetings`b ".$session['user']['name']."`0`b`&!`b`c`n`n<fieldset><legend align='center'>`b`QDaily Guild Report`b</legend>`n`n$vault`n`n$msg");
	redirect("clan.php");
	page_footer();
}
