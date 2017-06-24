<?php
function clanbar_getmoduleinfo(){
	$info = array(
		"name"=>"Clan Exp Bar",
		"version"=>"0.1b",
		"author"=>"`&`bStephen Kise`b",
		"category"=>"Stat Display",
		"download"=>"nope",
	);

	return $info;
}

function clanbar_install(){
	module_addhook("charstats");
	return true;
}

function clanbar_uninstall(){
	return true;
}

function clanbar_dohook($hookname,$args){
	switch($hookname){
		case "charstats":
		global $session;
		$display = get_module_pref("user_clanexp","charstatmanagement");
		if ($display == 1 && $session['user']['clanid'] != 0){
			require_once("lib/bars.php");
			require_once("lib/level.php");
			$clandata = db_fetch_assoc(db_query("SELECT clanexp FROM clans WHERE clanid = ".$session['user']['clanid']));
			setcharstat("Guild Info","Guild Level","`c".level($clandata['clanexp'])."`n".fadebar(expgainedforlevel($clandata['clanexp']),expforlevel($clandata['clanexp']))."`c");
		}
		break;
	}
	return $args;
}
?>