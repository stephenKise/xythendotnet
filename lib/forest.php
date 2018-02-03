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

		output("`c`i`3`nYou slowly step into the Crystal forest, and " .
            "immediately know why it was named such. an ever-present " .
            "luminous haze hangs around the area, casting prismatic hues " .
            "in all directions and obstructing sight beyond a few feet. " .
            "Large, ancient trees rise from the glowing soil, the dark " .
            "bark splinting into hundreds of small flexible branches, small " .
            "crystals sprouting from each tip, and larger ones from the bark. " .
            "The air feels heavy and oppressive, as if there was too much " .
            "oxygen in the air, or too much energy, a faint scent of ozone " .
            "lingering in each breath. The Crystal Forest stands as a testament " .
            "of what can happen after long-term Aether exposure, twisting both " .
            "body and mind. Any creature that calls this place home has evolved " .
            "or changed to be fit of it...as should any adventurer treading " .
            "these lands. `i`c`n`n");
		output("`0");
		output("`0");
		modulehook("forest-desc");
	}
	modulehook("forest", array());
	module_display_events("forest", "forest.php");
	tlschema();
}

?>
