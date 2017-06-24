<?php

function namecolor_getmoduleinfo(){
	$info = array(
		"name"=>"Name Colorization",
		"author"=>"Eric Stevens",
		"version"=>"1.0",
		"download"=>"core_module",
		"category"=>"Lodge",
		"settings"=>array(
			"Name Colorization Module Settings,title",
			"initialpoints"=>"How many points will the first color change cost?,int|300",
			"extrapoints"=>"How many points will subsequent color changes cost?,int|25",
			"maxcolors"=>"How many color changes are allowed in names?,int|10",
			"bold"=>"Allow bold?,bool|1",
			"italics"=>"Allow italics?,bool|1",
		),
		"prefs"=>array(
			"Name Colorization User Preferences,title",
			"boughtbefore"=>"Has user bought a color change before?,bool|0",
		),
	);
	return $info;
}

function namecolor_install(){
	module_addhook_priority("lodge","12");
	module_addhook_priority("pointsdesc","12");
	return true;
}
function namecolor_uninstall(){
	return true;
}

function namecolor_dohook($hookname,$args){
	global $session;
	switch($hookname){
		case "pointsdesc":
			$args['count']++;
			output("`\$- `^Ability to color your name.`n");
		break;
		case "lodge":
$pointsavailable =
	$session['user']['donation']-$session['user']['donationspent'];
			if ($pointsavailable >= 250){
				addnav("Use Points");
				addnav("Color Name `@(250 DP)", "runmodule.php?module=namecolor&op=namechange");
			}
		break;
	}
	return $args;
}

function namecolor_form() {
	$regname = get_player_basename();
	output("`^Your name colors currently look like: `0");
	rawoutput($regname);
	output("`n`^Your name looks like: `0%s`n`n", $regname);
	output("");
	rawoutput("<form action='runmodule.php?module=namecolor&op=namepreview' method='POST'>");
	output("`2Name colors: ");
	rawoutput("<input name='newname' value=\"".HTMLEntities($regname, ENT_COMPAT, getsetting("charset", "ISO-8859-1"))."\"><br><input type='submit' class='button' value='Preview'></form>");
	addnav("","runmodule.php?module=namecolor&op=namepreview");
}

function namecolor_run(){
	require_once("lib/sanitize.php");
	require_once("lib/names.php");
	global $session;

	$config = unserialize($session['user']['donationconfig']);
	if (!is_array($config)) $config=array();
		if (isset($config['namechange']) && $config['namechange']) {
		set_module_pref("boughtbefore", 1);
		unset($config['namechange']);
		$session['user']['donationconfig'] = serialize($config);
	}

	$rebuy = get_module_pref("boughtbefore");
	$cost = get_module_setting( $rebuy ? "extrapoints" : "initialpoints");
	$op = httpget("op");

	page_header("Hunter's Lodge");
		output("`Q`c`bName Color Change`b`c");
	if ($op=="namechange"){
			namecolor_form();
		addnav("L?Return to the Lodge","lodge.php");
	}elseif ($op=="namepreview"){
		$regname = get_player_basename();
		$newname = httppost("newname");

		if (!get_module_setting("bold")) $newname = str_replace("`b", "", $newname);
		if (!get_module_setting("italics")) $newname = str_replace("`i", "", $newname);
		$newname = preg_replace("/[`][cHw]/", "", $newname);
		$newname = preg_replace("/[$]/","\$",$newname);
		
		$comp1 = strtolower(sanitize($regname));
		$comp2 = strtolower(sanitize($newname));
		$err = 0;
		if ($comp1 != $comp2) {
			$err = 1;
			output("`\$`iYour new name must contain only the same characters as your current name; you can add or remove colors, and you can change the capitalization, but you may not add or remove anything else. You chose `i`0%s`\$.`n`n`c`b`&If you are using the `\$Bright Red `&color, please make sure to include ` 0 , without the space in between, right after the $ symbol, otherwise this error will persist.`c`b`0", $newname);
		}
		if (strlen($newname) > 300) {
			$err = 1;
			output("`\$`iYour new name is too long.  Including the color markups, you are not allowed to exceed 300 characters in length.`i`n");
		}
		if ((substr_count($newname,'`i') % 2) != 0 || (substr_count($newname,'`b') % 2) != 0){
			$err = 1;
			output("`\$You have a bold or italics issue with your name! You can copy what you typed out below to fix the issue:`n");
			rawoutput($newname."<br>");
		}
			
		$colorcount = 0;
		for ($x = 0; $x < strlen($newname); $x++) {
			if (substr($newname, $x, 1) == "`") {
				$x++;
				$colorcount++;
			}
		}
		$max = get_module_setting("maxcolors");
		if ($colorcount > 250) {
			if (!$err) output("`3`bInvalid name`b`0`n");
			$err = 1;
			output("`\$`iYou have used too many colors in your name.  You may not exceed 250 colors total.`i`n", $max);
		}
		if (!$err) {
			$newname = str_replace("`0","",$newname);
			output("`^Your name will look this this: `0%s`n`n`2Is this what you wish?`n`n`0", $newname);
			addnav("Confirm Name Change");
			addnav("Yes", "runmodule.php?module=namecolor&op=changename&name=".rawurlencode($newname));
			addnav("No", "runmodule.php?module=namecolor&op=namechange");
		} else {
			output("`n");
			namecolor_form();
			addnav("L?Return to the Lodge","lodge.php");
		}
	} elseif ($op=="changename") {
		set_module_pref("boughtbefore", 1);
		$fromname = $session['user']['name'];
		$newname = change_player_name(rawurldecode(httpget('name')));
		$session['user']['name'] = $newname;
		$session['user']['donationspent'] += 250;
		addnews("%s`^ has become known as %s.",$fromname,$session['user']['name']);
		output("`@Congratulations, your name is now {$session['user']['name']}`@!`n`n");
		modulehook("namechange", array());
		addnav("L?Return to the Lodge","lodge.php");
	}
	page_footer();
}
?>
