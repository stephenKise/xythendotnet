<?php
// translator ready
// addnews ready
// mail ready

function cities_getmoduleinfo(){
	$info = array(
		"name"=>"Multiple Cities",
		"version"=>"1.0",
		"author"=>"Eric Stevens",
		"category"=>"Village",
		"download"=>"core_module",
		"allowanonymous"=>true,
		"override_forced_nav"=>true,
		"settings"=>array(
			"Cities Settings,title",
			"allowance"=>"Daily Travel Allowance,int|3",
			"coward"=>"Penalise Cowardice for running away?,bool|1",
			"travelspecialchance"=>"Chance for a special during travel,int|7",
			"safechance"=>"Chance to be waylaid on a safe trip,range,0,100,1|50",
			"dangerchance"=>"Chance to be waylaid on a dangerous trip,range,0,100,1|66",
		),
		"prefs"=>array(
			"Cities User Preferences,title",
			"traveltoday"=>"How many times did they travel today?,int|0",
			"homecity"=>"User's current home city.|",
		),
		"prefs-mounts"=>array(
			"Cities Mount Preferences,title",
			"extratravel"=>"How many free travels does this mount give?,int|0",
		),
		"prefs-drinks"=>array(
			"Cities Drink Preferences,title",
			"servedcapital"=>"Is this drink served in the capital?,bool|1",
		),
	);
	return $info;
}

function cities_install(){
	module_addhook("villagetext");
	module_addhook("village");
	module_addhook("travel");
	module_addhook("count-travels");
	module_addhook("cities-usetravel");
	module_addhook("validatesettings");
	module_addhook("newday");
	module_addhook("charstats");
	module_addhook("mountfeatures");
	module_addhook("faq-toc");
	module_addhook("drinks-check");
	module_addhook("stablelocs");
	module_addhook("camplocs");
	module_addhook("master-autochallenge");
	return true;
}

function cities_uninstall(){
	// This is semi-unsafe -- If a player is in the process of a page
	// load it could get the location, uninstall the cities and then
	// save their location from their session back into the database
	// I think I have a patch however :)
	$city = getsetting("villagename", LOCATION_FIELDS);
	$inn = getsetting("innname", LOCATION_INN);
	$sql = "UPDATE " . db_prefix("accounts") . " SET location='".addslashes($city)."' WHERE location!='".addslashes($inn)."'";
	db_query($sql);
	$session['user']['location']=$city;
	return true;
}

function cities_dohook($hookname,$args){
	global $session;
	$city = getsetting("villagename", LOCATION_FIELDS);
	$home = $session['user']['location']==get_module_pref("homecity");
	$capital = $session['user']['location']==$city;
	switch($hookname){
    case "validatesettings":
		if ($args['dangerchance'] < $args['safechance']) {
			$args['validation_error'] = "Danger chance must be equal to or greater than the safe chance.";
		}
		break;
	case "faq-toc":
		$t = translate_inline("`@Frequently Asked Questions on Multiple Villages`0");
		output_notl("&#149;<a href='runmodule.php?module=cities&op=faq'>$t</a><br/>", true);
		break;
	case "drinks-check":
		if ($session['user']['location'] == $city) {
			$val = get_module_objpref("drinks", $args['drinkid'], "servedcapital");
			$args['allowdrink'] = $val;
		}
		break;
	case "count-travels":
		global $playermount;
		$args['available'] += get_module_setting("allowance");
		if ($playermount && isset($playermount['mountid'])) {
			$id = $playermount['mountid'];
			$extra = get_module_objpref("mounts", $id, "extratravel");
			$args['available'] += $extra;
		}
		$args['used'] += get_module_pref("traveltoday");
		break;
	case "cities-usetravel":
		global $session;
		$info = modulehook("count-travels",array());
		if ($info['used'] < $info['available']){
			set_module_pref("traveltoday",get_module_pref("traveltoday")+1);
			if (isset($args['traveltext'])) output($args['traveltext']);
			$args['success']=true;
			$args['type']='travel';
		}elseif ($session['user']['turns'] >0){
// 			$session['user']['turns']--;
			if (isset($args['foresttext'])) output($args['foresttext']);
			$args['success']=true;
			$args['type']='forest';
		}else{
			if (isset($args['nonetext'])) output($args['nonetext']);
			$args['success']=false;
			$args['type']='none';
		}
		$args['nocollapse'] = 1;
		return $args;
		break;
	case "master-autochallenge":
		global $session;
		if (get_module_pref("homecity")!=$session['user']['location']){
			$info = modulehook("cities-usetravel",
				array(
					"foresttext"=>array("`n`n`^Startled to find your master in %s`^, your heart skips a beat, costing a forest fight from shock.", $session['user']['location']),
					"traveltext"=>array("`n`n`%Surprised at finding your master in %s`%, you feel a little less inclined to be gallivanting around the countryside today.", $session['user']['location']),
					)
				);
			if ($info['success']){
				if ($info['type']=="travel") debuglog("Lost a travel because of being truant from master.");
				elseif ($info['type']=="forest") debuglog("Lost a forest fight because of being truant from master.");
				else debuglog("Lost something, not sure just what, because of being truant from master.");
			}
		}
		break;
	case "mountfeatures":
		$extra = get_module_objpref("mounts", $args['id'], "extratravel");
		$args['features']['Travel']=$extra;
		break;
	case "newday":
		if ($args['resurrection'] != 'true') {
			set_module_pref("traveltoday",0);
		}
		set_module_pref("paidcost", 0);
		break;
	case "villagetext":
		if ($session['user']['location'] == $city){
			// DESCRIPTION / RECENT NEWS / PLAYER OWNED LOCATIONS
		$args['text'] = array("`c`b`LS`b`i`ly`3l`i`#`bi`b`Ls`i`lt`3e`i`Ln`7, the floating capital of `b`i`dX`i`b`^y`Dth`^e`dn`7, suspends one-hundred feet above the ocean with the assistance of aether technology. The city consists of several districts in order to maintain a systematic order. In the very center of the city is the Immigration Center, a large Classical-style building where the `b`~O`b`)m`dn`i`Di`i `b`DP`b`i`^o`dr`i`7t`)a`b`~l`b`7 is heavily guarded and protected. The `b`~O`b`)m`dn`i`Di`i `b`DP`b`i`^o`dr`i`7t`)a`b`~l`b`7 being the sole entry point into and out of the realm of `b`i`dX`i`b`^y`Dth`^e`dn`7, was created to create realm stability after the `b`8T`b`i`ee`En`i`Ft`Dr`^o`b`dm`b`i`Qe`i`Cc`Ah `AW`i`da`i`er`7. Outside of the Immigration Center is the center of `b`LS`b`i`ly`3l`i`#`bi`b`Ls`i`lt`3e`i`Ln`7. The city center contains a large and elaborate aether fountain using the same style of stone as the courthouse; decorated with several carvings and engravings meant to represent the `b`TH`b`i`ei`Es`i`tt`Eo`b`er`b`i`Ty`i `7`iof`i `b`i`dX`i`b`^y`Dth`^e`dn`7. Several stone benches surround the fountain for lounging and relaxing. Occasional traveling vendors can be found on the outskirts of the city center, typically offering meals or trinkets to those nearby. Located across from the Immigration Center is the `b`~L`b`i`:o`5c`i`%a`b`Ql`b `b`~T`b`i`:a`5v`i`%e`Br`b`Cn`b`7, where those who need a warm place to rest, eat or drink may head. To the right and left of the `b`~T`b`i`:a`5v`i`%e`Br`b`Cn`b `7are two other classical-style buildings, the `b`dN`b`i`Da`^t`b`mi`b`Xo`i`^n`Da`b`dl`b `b`yB`b`i`^a`Dn`b`dk`b`i`7 and the `b`TG`b`4r`b`Qe`i`^a`b`i`Tt `i`T`bL`bi`i`4b`i`Qr`i`^a`i`T`br`by`i`7. As one can see, the center of `b`LS`b`i`ly`3l`i`#`bi`b`Ls`i`lt`3e`i`Ln `7primarily caters to newcomers, while also offering a common place to meet up with friends. `n`nStreets lined with small shops and lower-class housing branch off of the city center, leading to different districts. North, and past the Immigration Center, leads to the `b`!R`b`i`Le`i`ls`i`[i`i`]d`-e`b`=n`b`Kt`ki`ga`Ul `b`!D`b`i`Li`ls`i`[t`]r`-i`b`=c`b`Kt `7for upper and middle class citizens. To the East, down the streets framing the `b`dN`b`i`Da`^t`b`mi`b`Xo`i`^n`Da`b`dl`b `b`yB`b`i`^a`Dn`b`dk`b`i`7, is the industrial quarter - where factories and laboratories can be found. Heading south, passing the `b`~L`b`i`:o`5c`i`%a`b`Ql`b `b`~T`b`i`:a`5v`i`%e`Br`b`Cn`b`7, would lead to the social district where the `b`&G`b`7u`=i`b`]l`b`-d `b`&H`b`7a`=l`b`]l`b`-s`7 and `b`)B`b`7o`b`gt`b`i`@a`kn`i`L`bi`b`lc`ia`il `)G`i`7a`gr`i`@d`b`ke`b`Ln`i`ls`i`7 reside. In addition, there are several bars and restaurants for those seeking a more social or private atmosphere. Finally, down the streets framing the `b`TG`b`4r`b`Qe`i`^a`b`i`Tt `i`T`bL`bi`i`4b`i`Qr`i`^a`i`T`br`by`i`7, leads to the central area for travel; where individuals could buy tickets to the main land or other major cities in `b`i`dX`i`b`^y`Dth`^e`dn`7.`n`nTo the east of `b`LS`b`i`ly`3l`i`#`bi`b`Ls`i`lt`3e`i`Ln`7, on the main land, a small coastal area for fishing and oceanic trade rests nestled between the `b`3o`b`#c`i`ke`i`-a`_n `b`_w`b`-a`kt`i`#e`i`3r`b`ls`b`7 and a steep incline that separates the `b`LC`b`#r`i`3y`i`ls`Lt`b`3a`b`#l `b`@F`b`i`2o`gr`i`Ge`2s`@t`7 from the citizens. This area is primarily filled with fishermen, `b`&b`b`i`ko`i`La`$t`b`4s`b`7, docks and several fish merchants - but further in the distance there is a mile stretch of beach meant for lounging in the `b`^s`b`i`du`Dn`i`7.`c",$city,$city);


			//$args['text'] = array("`c`b`iCamelot, home of Trade and Diplomacy`i`b`nThe streets of Camelot bustle with the new visitors, journeying to the home of trade and diplomacy in hopes of claiming their stake in this fresh environment. The sound of traders advertising their wares permeates the air of the new market, hoping to rise in the ranks of being the best new barterer. The King's men stand guard watching over the cobbled streets, wearing the Camelonian coat of arms with such pride and honor. The eastern side of the town is where most of the housing seems to be, offering many townhouses for new residents to claim as their own.`c");
			$args['schemas']['text'] = "module-cities";
			$args['clock']="`0";
			$args['schemas']['clock'] = "module-cities";
			if (is_module_active("calendar")) {
				$args['calendar']="`n`QYou hear a townsperson say that today is `^%1\$s`Q, `^%3\$s %2\$s`Q, `^%4\$s`Q.`n";
				$args['schemas']['calendar'] = "module-cities";
			}
			$args['title']=array("%s, the Capital City",$city);
			$args['schemas']['title'] = "module-cities";
			$args['fightnav']="Combat Avenue";
			$args['schemas']['fightnav'] = "module-cities";
			$args['compnav']="Competition";
			$args['schemas']['compnav'] = "module-cities";
			$args['marketnav']="Store Street";
			$args['schemas']['marketnav'] = "module-cities";
			$args['tavernnav']="Ale Alley";
			$args['schemas']['tavernnav'] = "module-cities";
			$args['newestplayer']="";
			$args['schemas']['newestplayer'] = "module-cities";
		}
		if ($home){
			//in home city.
			//blocknav("inn.php");
			//blocknav("stables.php");
			//blocknav("hof.php");
			blocknav("mercenarycamp.php");
		}elseif ($capital){
			//in capital city.
			//blocknav("forest.php");
			//blocknav("train.php");
		}else{
			//in another city.
			//blocknav("train.php");
			//blocknav("inn.php");
			//blocknav("stables.php");
			//blocknav("clans.php");
			//blocknav("hof.php");
			blocknav("mercenarycamp.php");
		}
		break;
//	case "charstats":
		// if ($session['user']['alive']){
		//	addcharstat("Personal Info");
		//	addcharstat("Home City", $session['user']['alive']?get_module_pref("homecity"):translate_inline("Shades"));
		//	$args = modulehook("count-travels", array('available'=>0,'used'=>0));
		//	$free = max(0, $args['available'] - $args['used']);
//			addcharstat("Personal Info");
//			addcharstat("Free Travel", $session['user']['alive']?$free:"0");
		// }
//		break;
	case "village":
		if ($capital) {
			tlschema($args['schemas']['fightnav']);
			addnav($args['fightnav']);
		}
		tlschema($args['schemas']['gatenav']);
		addnav($args['gatenav']);
		tlschema();
		addnav("`#Tr`&a`7v`#el","runmodule.php?module=cities&op=travel");
		if (get_module_pref("paidcost") > 0) set_module_pref("paidcost", 0);
		break;
	case "travel":
	require_once('lib/sanitize.php');
		addnav("Travel");
		$city_sanitized = full_sanitize($city);
		$hotkey = $city_sanitized[0];
		if ($session['user']['location']!=$city){
			addnav(array("%s?%s", $hotkey, $city),"runmodule.php?module=cities&op=travel&city=".urlencode($city));
		}
		break;
	case "stablelocs":
		$args[$city] = sprintf_translate("The City of %s", $city);
		break;
	case "camplocs":
		$args[$city] = sprintf_translate("The City of %s", $city);
		break;
	}
	return $args;
}

function cities_dangerscale($danger) {
	global $session;
	$dlevel = ($danger ?
			get_module_setting("dangerchance"):
			get_module_setting("safechance"));
	if ($session['user']['dragonkills'] <= 1) $dlevel = round(.50*$dlevel, 0);
	elseif ($session['user']['dragonkills'] <= 30) {
		$scalef = 50/29;
		$scale = (($session['user']['dragonkills']-1)*$scalef + 50)/100;
		$dlevel = round($scale*$dlevel, 0);
	} // otherwise, dlevel is unscaled.
	return $dlevel;
}

function cities_run(){
	global $session;
	$op = httpget("op");
	$city = urldecode(httpget("city"));
	$continue = httpget("continue");
	$danger = httpget("d");
	$su = httpget("su");
	if ($op != "faq") {
		require_once("lib/forcednavigation.php");
		do_forced_nav(false, false);
	}

	// I really don't like this being out here, but it has to be since
	// events can define their own op=.... and we might need to handle them
	// otherwise things break.
	require_once("lib/events.php");
	if ($session['user']['specialinc'] != "" || httpget("eventhandler")){
		$in_event = handle_event("travel",
			"runmodule.php?module=cities&city=".urlencode($city)."&d=$danger&continue=1&",
			"Travel");
		if ($in_event) {
			addnav("Continue","runmodule.php?module=cities&op=travel&city=".urlencode($city)."&d=$danger&continue=1");
			module_display_events("travel",
				"runmodule.php?module=cities&city=".urlencode($city)."&d=$danger&continue=1");
			page_footer();
		}
	}

	if ($op=="travel"){
		$args = modulehook("count-travels", array('available'=>0,'used'=>0));
		$free = max(0, $args['available'] - $args['used']);
		if ($city==""){
			require_once("lib/villagenav.php");
			page_header("Travel");
			modulehook("collapse{", array("name"=>"traveldesc"));
			output("`%Travelling the world can be a dangerous occupation.");
			modulehook("}collapse");
			addnav("Forget about it");
			villagenav();
			modulehook("pre-travel");
// 			if (!($session['user']['superuser']&SU_EDIT_USERS) && ($session['user']['turns']<=0) && $free == 0) {
				// this line rewritten so as not to clash with the hitch module.
				output("`nYou don't feel as if you could face the prospect of walking to another city today, it's far too exhausting.`n");
// 			}else{
				addnav("`#Tr`&a`7v`#el");
				modulehook("travel");
// 			}
			module_display_events("travel",
				"runmodule.php?module=cities&city=".urlencode($city)."&d=$danger&continue=1");
			page_footer();
		}else{
			if ($continue!="1" && $su!="1" && !get_module_pref("paidcost")){
				set_module_pref("paidcost", 1);
				if ($free > 0) {
					// Only increment travel used if they are still within
					// their allowance.
					set_module_pref("traveltoday",get_module_pref("traveltoday")+1);
					//do nothing, they're within their travel allowance.
				}elseif ($session['user']['turns']>0){
// 					$session['user']['turns']--;
				}else{
					output("Hey, looks like you managed to travel with out having any forest fights.  How'd you swing that?");
					debuglog("Travelled with out having any forest fights, how'd they swing that?");
				}
			}
			// Let's give the lower DK people a slightly better chance.
			$dlevel = cities_dangerscale($danger);
			if (e_rand(0,100)< $dlevel && $su!='1'){
				//they've been waylaid.

				if (module_events("travel", get_module_setting("travelspecialchance"),"runmodule.php?module=cities&city=".urlencode($city)."&d=$danger&continue=1&") != 0) {
					page_header("Something Special!");
					if (checknavs()) {
						page_footer();
					} else {
						// Reset the special for good.
						$session['user']['specialinc'] = "";
						$session['user']['specialmisc'] = "";
						$skipvillagedesc=true;
						$op = "";
						httpset("op", "");
						addnav("Continue","runmodule.php?module=cities&op=travel&city=".urlencode($city)."&d=$danger&continue=1");
						module_display_events("travel",
							"runmodule.php?module=cities&city=".urlencode($city)."&d=$danger&continue=1");
						page_footer();
					}
				}

				$args = array("soberval"=>0.9,
						"sobermsg"=>"`&Facing your bloodthirsty opponent, the adrenaline rush helps to sober you up slightly.", "schema"=>"module-cities");
				modulehook("soberup", $args);
				require_once("lib/forestoutcomes.php");
				$sql = "SELECT * FROM " . db_prefix("creatures") . " WHERE creaturelevel = '{$session['user']['level']}' AND forest = 1 ORDER BY rand(".e_rand().") LIMIT 1";
				$result = db_query($sql);
				restore_buff_fields();
				if (db_num_rows($result) == 0) {
					// There is nothing in the database to challenge you,
					// let's give you a doppleganger.
					$badguy = array();
					$badguy['creaturename']=
						"An evil doppleganger of ".$session['user']['name'];
					$badguy['creatureweapon']=$session['user']['weapon'];
					$badguy['creaturelevel']=$session['user']['level'];
					$badguy['creaturegold']=0;
					$badguy['creatureexp'] =
						round($session['user']['experience']/10, 0);
					$badguy['creaturehealth']=$session['user']['maxhitpoints'];
					$badguy['creatureattack']=$session['user']['attack'];
					$badguy['creaturedefense']=$session['user']['defense'];
				} else {
					$badguy = db_fetch_assoc($result);
					$badguy = buffbadguy($badguy);
				}
				calculate_buff_fields();
				$badguy['playerstarthp']=$session['user']['hitpoints'];
				$badguy['diddamage']=0;
				$badguy['type'] = 'travel';
				$session['user']['badguy']=createstring($badguy);
				$battle = true;
			}else{
				set_module_pref("paidcost", 0);
				//they arrive with no further scathing.
				$session['user']['location']=$city;
				redirect("village.php");
			}
		}
	}elseif ($op=="fight" || $op=="run"){
		if ($op == "run" && e_rand(1, 5) < 3) {
			// They managed to get away.
			page_header("Escape");
			output("You set off running through the forest at a breakneck pace heading back the way you came.`n`n");
			$coward = get_module_setting("coward");
			if ($coward) {
				modulehook("cities-usetravel",
				array(
					"foresttext"=>array("In your terror, you lose your way and become lost, losing time for a forest fight.`n`n", $session['user']['location']),
					"traveltext"=>array("In your terror, you lose your way and become lost, losing precious travel time.`n`n", $session['user']['location']),
					)
				);
			}
			output("After running for what seems like hours, you finally arrive back at %s.", $session['user']['location']);

			addnav(array("Enter %s",$session['user']['location']), "village.php");
			page_footer();
		}
		$battle=true;
	} elseif ($op == "faq") {
		cities_faq();
	} elseif ($op == "") {
		page_header("Travel");
		output("A divine light ends the fight and you return to the road.");
		addnav("Continue your journey","runmodule.php?module=cities&op=travel&city=".urlencode($city)."&continue=1&d=$danger");
		module_display_events("travel",
			"runmodule.php?module=cities&city=".urlencode($city)."&d=$danger&continue=1");
		page_footer();
	}

	if ($battle){
		page_header("You've been waylaid!");
		require_once("battle.php");
		if ($victory){
			require_once("lib/forestoutcomes.php");
			forestvictory($newenemies,"This fight would have yielded an extra turn except it was during travel.");
			addnav("Continue your journey","runmodule.php?module=cities&op=travel&city=".urlencode($city)."&continue=1&d=$danger");
			module_display_events("travel",
				"runmodule.php?module=cities&city=".urlencode($city)."&d=$danger&continue=1");
		}elseif ($defeat){
			require_once("lib/forestoutcomes.php");
			forestdefeat($newenemies,array("travelling to %s",$city));
		}else{
			require_once("lib/fightnav.php");
			fightnav(true,true,"runmodule.php?module=cities&city=".urlencode($city)."&d=$danger");
		}
		page_footer();
	}

}


?>