<?php
function save_ips_getmoduleinfo(){
	$info = array(
		"name" => "Save IPs",
		"author" => "`i`)Ae`7ol`&us`i`0",
		"version" => "1.0",
		"category" => "Administrative",
		"download" => "http://dragonprime.net/index.php?topic=12465.0",
		"settings" => array(
			"Save IPs Settings,title",
			"num" => "Number of last IPs to save?,int|5",
		),
		"prefs" => array(
			"Save IPs Prefs,title",
			"saved" => "Last saved IPs,viewonly|",
		),
	);
	return $info;
}

function save_ips_install(){
	module_addhook("player-login");
	module_addhook("bioinfo");
	return true;
}

function save_ips_uninstall(){
	return true;
}

function save_ips_dohook($hookname, $args){
	global $session;
	switch ($hookname){
		case "player-login":
			$num = get_module_setting("num");
			
			// Had to include module name and user's acctid - wouldn't work without them for some reason :/
			$saved = unserialize(get_module_pref("saved","save_ips",$session['user']['acctid']));
			if (!is_array($saved)) $saved = array();
			
			if (!in_array($_SERVER['REMOTE_ADDR'], $saved))
				array_push($saved,$_SERVER['REMOTE_ADDR']);
			$saved = array_unique($saved);
			while (count($saved) > $num)
				array_shift($saved);
			set_module_pref("saved",serialize($saved),"save_ips",$session['user']['acctid']);
		break;
		case "bioinfo":
			if ($session['user']['superuser'] & SU_EDIT_USERS){
				$saved = unserialize(get_module_pref("saved","save_ips",$args['acctid']));
				if (is_array($saved)){
					$saved_o = implode("`n", $saved);
				} else {
					$user = db_fetch_assoc(db_query("SELECT lastip FROM ".db_prefix('accounts')." WHERE acctid = ".$args['acctid'].""));
					$saved_o = $user['lastip'];
				}
				output("`^Last IPs:`n`&%s`^`n", $saved_o);
			}
		break;
	}
	
	return $args;
}
?>