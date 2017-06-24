<?php

function barracks_getmoduleinfo(){
	$info = array(
		"name"=>"Guild Barracks",
		"author"=>"`&`bStephen Kise`b",
		"version"=>"0.1b",
		"download"=>"nope",
		"category"=>"Clan",
		);
	return $info;
}

function barracks_install(){
	module_addhook("clanhall");
	return TRUE;
}

function barracks_uninstall(){
	return TRUE;
}

function barracks_dohook($hookname,$args){
	global $session;
		switch ($hookname){
			case "clanhall":
				$myclan = db_fetch_assoc(db_query("SELECT clanname AS name FROM ".db_prefix("clans")." WHERE clanid = ".$session['user']['clanid']));
				if ($session['user']['location'] != $myclan['name']) $session['user']['previouslocation'] = $session['user']['location'];
				$session['user']['location'] = $myclan['name'];
				addnav("Guild Amenities");
				addnav("Go to Sleep","login.php?op=logout",true);
			break;
		}
	return $args;
}
?>