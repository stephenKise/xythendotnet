<?php

function vessa_getmoduleinfo(){
	$info = array(
		"name"=>"Vessa's Gem Shop",
		"version"=>"8.1",
		"author"=>"Reznarth + Changes by Stark",
		"category"=>"Village Venue",
		"download"=>"http://dragonprime.net/users/Reznarth/vessa98.zip",
		"settings"=>array(
			"Vessa Settings,title",
			"geminventory"=>"Amount of gems in stock,int|1000",
			"gemcost"=>"Gold needed to buy gem,int|2000",
			"gemsell"=>"Gold paid to sell gem,int|500",
			"gemsdailyb"=>"Max gems allowed to buy per new day,int|50",
			"gemsdailys"=>"Max gems allowed to sell per new day,int|50",
			"enterlevel"=>"Min level needed to enter gemshop,int|8",
			"maxlevel"=>"Max level allowed to enter gemshop,int|14",
			"maxmessage"=>"Not allowed max level message,text|Don't you think it's time to kill the dragon?",
 			"vessaloc"=>"Where does Vessa appear,location|".getsetting("villagename", LOCATION_FIELDS)		),		"prefs"=>array(			"Vessa User Preferences,title",			"gemsbuytoday"=>"How many gems bought today?,int|0",			"gemsselltoday"=>"How many gems sold today?,int|0",		),	);	return $info;}
function vessa_install(){
	module_addhook("village");
	module_addhook("newday");
	return true;
}
function vessa_uninstall(){
	return true;
}function vessa_dohook($hookname,$args){
// 	if ($args['old'] == get_module_setting("vessaloc")) {
// 		set_module_setting("vessaloc", $args['new']);
// 	}
	global $session;
	switch($hookname){
		case "newday":
			if ($args['resurrection'] != 'true') {
				set_module_pref("gemsbuytoday",0);
				set_module_pref("gemsselltoday",0);
			}
		break;
		case "village":
			//if ($session['user']['location'] == get_module_setting("vessaloc") || $session['user']['location'] == "Siochanta") {
				tlschema($args['schemas']['marketnav']);
				addnav($args['marketnav']);
				tlschema();
				addnav("Gem Trader","runmodule.php?module=vessa");
			//}
		break;	}
	return $args;
}
function vessa_run(){
	global $session;
	$op = httpget('op');
	page_header("Gem Trader");
	$geminventory = get_module_setting("geminventory");
	$gemcost = get_module_setting("gemcost");
	$gemsell = get_module_setting("gemsell");
	$gemsdailyb = get_module_setting("gemsdailyb");
	$gemsdailys = get_module_setting("gemsdailys");
	$enterlevel = get_module_setting("enterlevel");
	$maxlevel = get_module_setting("maxlevel");
	$maxmessage = get_module_setting("maxmessage");		$clanid = $session['user']['clanid'];	
	if ($session['user']['level'] < $enterlevel) {
		output("You walk up to the Gem Trader's window, however the screen remains closed no matter what. After waiting a few minutes, you notice a sign on the window stating that no sales can be made at this time. Perhaps you should go slay the Crystal Guardian?`n`n");
	} else {
		if ($session['user']['level'] <= $maxlevel){

			if ($op == ""){
				output(
                    "`7The Gem Trader is located behind an open window; the" .
                    " neon sign reading as `bopen`b during most hours of the day." .
                    " You walk up to the window in the wall and lean on the" .
                    " small ledge, peering into the dimmed room that was about" .
                    " the size of a small closet. Inside the small room there" .
                    " are several safes; some loose gems lay scattered about" .
                    " in nooks, indicating that the small objects are in heavy" .
                    " supply. A small button can be found on the ledge with a" . 
                    "\"`Vring me`7\" sign written nearby. You press the button," .
                    " and a woman opens the door to the small closet-sized" .
                    " space. The woman was in her later years, grey hair" .
                    " pulled up into a bun, a professional business-like" .
                    " outfit fitted perfectly for her size. Overall, the" .
                    " woman seemed incredibly professional - at least before" .
                    " she put a large set of goggles onto her head with" .
                    " several zoom lenses to help her in assessing quality" .
                    " of gems before purchase. `n`n \"`vWelcome to the Gem" .
                    " Trader. Our services include the buying and selling of" .
                    " gems for gold. Here is a list of our prices.`7\""); 
                output("`n`nPurchase Gems for %s gold.`n",$gemcost);
				output("Sell Gems for %s gold.`n",$gemsell);
				output("Daily Purchase Limit: %s gems `n ",$gemsdailyb);
				output("Daily Sell Limit: %s gems today.`n",$gemsdailys);
				output("There are %s gems in stock.`n",$geminventory);
			}

			if ($op == "gembuy"){
				if (get_module_pref("gemsbuytoday")<get_module_setting("gemsdailyb")){
					output("`%How many gems would you like to buy?`n");
					output("<form action='runmodule.php?module=vessa&op=gembuy2' method='POST'><input name='buy' id='buy'><input type='submit' class='button' value='buy'></form>",true);
					addnav("","runmodule.php?module=vessa&op=gembuy2");
				} else {					output("You can't buy anymore gems today`n");				}
			}
			if ($op == "gembuy2"){
				$max=(get_module_setting("gemsdailyb") - get_module_pref("gemsbuytoday"));
				$stock=(get_module_setting("geminventory"));
				$buy = httppost('buy');
				if ($buy < 0) $buy = 0;
				if ($buy >= $max) $buy = ($max);
				if ($buy >= $stock) $buy = ($stock);
				if ($session['user']['gold'] < ($buy * $gemcost)) {					output("The Gem Trader gives you the finger after you attempt to pay her less than her gems are worth.`n`n");				} else {
					$cost=($buy * $gemcost);
					$session['user']['gold']-=$cost;
					$session['user']['gems']+=$buy;
					set_module_pref("gemsbuytoday",get_module_pref("gemsbuytoday")+$buy);
					set_module_setting("geminventory",get_module_setting("geminventory")-$buy);
					output("The Gem Trader takes the %s gold pieces",$cost);
					output(" and hands you %s gems.",$buy);
					debuglog("spent $cost gold buying $buy gems from the Gem Trader");
				}
			}

			if ($op == "gemsell"){
				if (get_module_pref("gemsselltoday")<get_module_setting("gemsdailys")){
					output("`%How many gems would you like to sell?`n");
					output("<form action='runmodule.php?module=vessa&op=gemsell2' method='POST'><input name='sell' id='sell'><input type='submit' class='button' value='sell'></form>",true);
					addnav("","runmodule.php?module=vessa&op=gemsell2");
				} else {					output("You can't sell anymore gems today`n");				}
			}

			if ($op == "gemsell2"){
				$max=(get_module_setting("gemsdailys") - get_module_pref("gemsselltoday"));
				$sell = httppost('sell');
				if ($sell < 0) $sell = 0;
				if ($sell >= $max) $sell = ($max);
				if ($session['user']['gems'] < $sell) {					output("The Gem Trader raises her fist at you, noticing that you do not have that many gems.`n`n");				} else {
					$cost=($sell * $gemsell);
					$session['user']['gems']-=$sell;
					$session['user']['gold']+=$cost;
					if ($clanid){
						debuglog('soldgems', $session['user']['acctid'], false, 'vessabug', 10);
					}
					set_module_pref("gemsselltoday",get_module_pref("gemsselltoday")+$sell);
					set_module_setting("geminventory",get_module_setting("geminventory")+$sell);
					output("The Gem Trader gives you %s gold",$cost);
					output(" in return for %s gems.",$sell);
					debuglog("got $cost gold selling $sell gems to the Gem Trader.");
				}
			}

		} else {
			output("$maxmessage");		}
	}

	addnav("Buy a gem - $gemcost gp","runmodule.php?module=vessa&op=gembuy");
	addnav("Sell a gem - $gemsell gp","runmodule.php?module=vessa&op=gemsell");
	require_once("lib/villagenav.php");
	villagenav();
	page_footer();
}
?>