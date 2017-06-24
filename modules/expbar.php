<?php

function expbar_getmoduleinfo(){
	$info = array(
		"name"=>"Experience Bar",
		"version"=>"1.0",
		"author"=>"JT Traub<br>based on idea by Dan Van Dyke`n`4Modified by Talisman",
		"category"=>"Stat Display",
		"download"=>"http://dragonprime.net/index.php?module=Downloads;sa=dlview;id=512",
		"settings"=>array(
			"Experience Bar Module Settings,title",
			"showexpnumber"=>"Show current experience number,bool|1",
			"shownextgoal"=>"Show the exp needed for next level (only if current exp is shown),bool|0",
			"showbar"=>"Show the experience toward next level as a bar,bool|1",
		),
	);
	return $info;
}

function expbar_install(){
	module_addhook("charstats");
	return true;
}

function expbar_uninstall(){
	return true;
}

function expbar_dohook($hookname,$args){
	global $session;
	switch($hookname){
	case "charstats":
		require_once("lib/experience.php");
		$level = $session['user']['level'];
		$dks = $session['user']['dragonkills'];
		$min = exp_for_next_level($level-1, $dks);
		$req = exp_for_next_level($level, $dks);
		$exp = round($session['user']['experience'], 0) . check_temp_stat("experience",1);

		// If the user has dropped below the previous level, make that the
		// min and they need 100%  They will continue to need 100% until
		// they reach 'min' again.
		if ($exp < $min) $min = $exp;
		if ($req-$min > 0) $nonpct = floor(($req-$exp)/($req-$min) * 100);
		else $nonpct = 0;
		$pct = 100-$nonpct;
		if ($pct > 100) {
			$pct = 100;
			$nonpct = 0;
		}
		if ($pct < 0) {
			$pct = 0;
			$nonpct = 100;
		}
		if ($exp >= $req) {
			$bar = "<table cellpadding='0' cellspacing='0' width='70' height='15'><tr><td colspan='2' align='center'>`b`^".$session['user']['level']."`b</td></tr></table><table cellpadding='0' cellspacing='0' width='70' height='5'><tr><td colspan='2' align='center'><tr style='border: solid 1px #000000;'><td width='$pct%' bgcolor='blue'></td><td width='$nonpct%' bgcolor='blue'></td></tr></table>";
		}else if ($exp == 0) {
			$bar = "<table cellpadding='0' cellspacing='0' width='70' height='15'><tr><td colspan='2' align='center'>`b`^".$session['user']['level']."`b</td></tr></table><table cellpadding='0' cellspacing='0' width='70' height='5'><tr><td colspan='2' align='center'><tr style='border: solid 1px #000000;'><td width='$pct%' bgcolor='red'></td><td width='$nonpct%' bgcolor='red'></td></tr></table>";
		} else {
			$bar = "<table cellpadding='0' cellspacing='0' width='70' height='15'><tr><td colspan='2' align='center'>`b`^".$session['user']['level']."`b</td></tr></table><table cellpadding='0' cellspacing='0' width='70' height='5'><tr><td colspan='2' align='center'><tr style='border: solid 1px #000000;'><td width='$pct%' bgcolor='white'></td><td width='$nonpct%' bgcolor='red'></td></tr></table>";
		}
		$old = getcharstat("Vital Info", "Experience");
		$new = "";
		$shownum = get_module_setting("showexpnumber");
		$shownext = get_module_setting("shownextgoal");
		$showbar = get_module_setting("showbar");
		if (!$shownum && !$showbar) $new="`b`\$hidden`b";
		//if ($shownum) $new .= $old;
// 		if ($shownum && $shownext) $new .= "`c`b".$session['user']['level']."`b`n";
		if ($showbar) {
			//if ($shownum) $new .= "<br />";
			$new .= $bar;
		}
		setcharstat("Vital Info", "Level", $new);
		break;
	}
	return $args;
}

function expbar_run(){

}
?>
