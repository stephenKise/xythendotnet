<?php

function teashop_getmoduleinfo(){
	$info = array(
		"name"=>"Xythen Tea Shop",
		"author"=>"Maverick",
		"version"=>"1.0",
		"category"=>"Village",
		"prefs"=>array(
		"Tea Shop Preferences,title",
		"amount"=>"How many teas has this person had today?,int|0",
		),
		);
	return $info;
}

function teashop_install(){
	module_addhook("village");
	module_addhook("newday");
	return TRUE;
}

function teashop_uninstall(){
	return TRUE;
}

function teashop_dohook($hookname,$args){
	switch($hookname){
		case "village":
			addnav($args['marketnav']);
			addnav("`b`QC`b`tl`i`mo`i`^c`b`Qk`b`^w`i`mo`tr`i`Qk `QT`b`me`b`i`ta`i`6s","runmodule.php?module=teashop&op=enter");
		break;
		case "newday":
			set_module_pref("amount",0);
		break;
	}
	return $args;
}

function teashop_run(){
	global $session;
	$op = httpget('op');
	$item = httpget('item');
	$teas = array("Water","Green","White","Chai","Oolong","Raspberry","Herbal","Peach","Black","Coffee","Latte"); 	// insert tea names here
	$gold_cost = array(10,20,30,40,50,60,65,100,200,300,400); 				// insert tea gold costs of the tea names above
	$gem_cost = array(3,5,7,10,12,15,20,22,25,30,35); 					// insert tea gem costs of the tea names (put 0 if no gems needed)
	$turns_given = array(2,2,2,3,3,3,5,5,5,7,7); 					// insert the amount of turns for each tea
	page_header("Tea Shop");
	addnav("Leave");
	require_once("lib/villagenav.php");
	villagenav();
	output("`c<big>Tea Shop</big>`c`n`n<hr>`n",true);
	switch($op){
		case "enter":
			output("`c`XStepping inside the tea shop, you are greeted with a bright smile from `VL`vo`&tt`Va, `Xthe owner. In front of the door is the counter, and on the wall behind it are two large blackboards, listing the variety of beverages, pastries and cakes which are available for sale. Once you have ordered yourself a drink, you take a seat to wait. The shop is quite small, but the large window at the front makes sure it is not dark. Through the window, the city square can be seen, with the fountain in the centre: a perfect spot to people watch. The tea shop itself is quite simply decorated; the floor is made up on dark mahogany wood, but the walls are painted a pale cream. The seating, too, is unusual; instead of proper chairs and tables, `VL`vo`&tt`Va`X has elected to furnish with lower tables and comfortable looking low slung chairs, perfect for curling up in with a cup of tea and watching the world go by.`c");
			addnav("Purchase");
			for($i=0;$i<count($teas);$i++){
				addnav($teas[$i]." (".$gold_cost[$i]." gold, ".$gem_cost[$i]." gems)","runmodule.php?module=teashop&op=purchase&item=".$i);
			}
			
			require_once("lib/commentary.php");
			addcommentary();
			viewcommentary("clockwork-teas", "Speak with those gathered,", 20, "says");
		break;
		case "purchase":
			addnav("Go Back","runmodule.php?module=teashop&op=enter");
			$gold = $gold_cost[$item];
			$gems = $gem_cost[$item];
			$tea = $teas[$item];
			$turns = $turns_given[$item];
			if ($session['user']['gold'] < $gold || $session['user']['gems'] < $gems){
				output("Insufficient Funds!`n`nYou need ".$gold." gold and ".$gems." gems!");
			}else if (get_module_pref("amount") > 2){
				output("You've had too many today!!");
			}else{
				output("Thank you for purchasing the '".$tea."'.`n`nI hope you enjoy it!`n`n`i`tYou feel energized! You have gained ".$turns." turns!");
				$session['user']['gold']-=$gold;
				$session['user']['gems']-=$gems;
				$session['user']['turns']+=$turns;
				increment_module_pref("amount");
			}
		break;
	}
	page_footer();
}
?>