<?php
// addnews ready
// translator ready
// mail ready
require_once("lib/villagenav.php");

function forest($noshowmessage=false) {
	global $session,$playermount;
	tlschema("forest");
//	mass_module_prepare(array("forest", "validforestloc"));
if ($session['user']['level']>=15  && $session['user']['seendragon']==0){
		// Only put the green dragon link if we are a location which
		// should have a forest.   Don't even ask how we got into a forest()
		// call if we shouldn't have one.   There is at least one way via
		// a superuser link, but it shouldn't happen otherwise.. We just
		// want to make sure however.
		$isforest = 0;
		$vloc = modulehook('validforestloc', array());
		foreach($vloc as $i=>$l) {
			if ($session['user']['location'] == $i) {
				$isforest = 1;
				break;
			}
		}
		if ($isforest || count($vloc)==0) {
			addnav("Advance");
			addnav("`\$Obliterate Tentromech","forest.php?op=dragon");
		}
	}
	$cost = log($session['user']['level']) * (($session['user']['maxhitpoints']-$session['user']['hitpoints']) + 10);
	$cost = round($cost,0);
	$newcost=round(100*$cost/100,0);
	addnav("Actions");
 	if ($session['user']['gold'] >= $newcost) addnav("H?Heal Yourself","healer.php");
// 	addnav("Search Easy","forest.php?op=search&auto=full&type=slum");
// 	if ($session['user']['level']>1)
	addnav("r?Search the Forest","forest.php?op=search&auto=full");
	villagenav();
	addnav("Options");
	addnav("Other");


	if ($noshowmessage!=true){
		output("`c`7`bThe Forest`b`0`c");
		output("`0");
		output("`c`i`gAs you enter the industrial forest, you pull a scarf over your breathing outlets to avoid ingesting the horrid smell that carries on such contaminated air ...This polluted, dissolute area, is home to some of the most vile creatures known to Xythen. Everything in you virtually screams to warn you of the creatures you'll face if you choose to venture further in, a single voice calling out for you to run in the other direction to avoid imminent death. Enemies of the most heinous origins lurk within the shadowed, hazy confines of the forest, some just a breath away from where you stand... Each creature present has been mutated by their choice of habituation, their entire frames adapting to what we as cyborgs, humanoids, automations, humans, and robots alike cannot.`i`c`n`n");
		output("`0");
		output("`0");
		modulehook("forest-desc");
	}
	modulehook("forest", array());
	module_display_events("forest", "forest.php");
	tlschema();
}

?>
