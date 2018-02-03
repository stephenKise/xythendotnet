<?php
require_once("common.php");
require_once("lib/commentary.php");
require_once("lib/villagenav.php");
require_once("lib/events.php");
require_once("lib/http.php");

addcommentary();
if ($com=="" && !$comment && $op!="fleedragon") {
	if (module_events("inn", getsetting("innchance", 0)) != 0) {
		if (checknavs()) {
			page_footer();
		} else {
			$skipinndesc = true;
			$session['user']['specialinc'] = "";
			$session['user']['specialmisc'] = "";
			$op = "";
			httpset("op", "");
		}
	}
}
addnav("Things to do");
if (!isset($args['block']) || $args['block'] != 'yes') {
	addnav("Bar Section","inn.php?op=converse");
}
addnav(array("B?Talk to %s`0 the Barkeep",$barkeep),"inn.php?op=bartender");

addnav("Other");
addnav("Get a room (log out)","inn.php?op=room");


if (!$skipinndesc) {
	if ($op=="strolldown"){
		output("You stroll down the stairs of the inn, once again ready for adventure!`n");
	} elseif ($op=="fleedragon") {
		output("You pelt into the inn as if the Devil himself is at your heels.  Slowly you catch your breath and look around.`n");
		output("%s`0 catches your eye and then looks away in disgust at your cowardice!`n`n",$partner);
		output("You `\$lose`0 a charm point.`n`n");
		if ($session['user']['charm'] > 0) $session['user']['charm']--;
	} else {
		output(
		"`7You slip into the one place that's been known" .
		" in many lands to be the place where one can" .
		" get all three of their necessities for only" .
		" a few bucks: Food, Drink, and `&Shelter`7.");
	}
	output(
		"`7Of course, those three things aren't `iall`7`i" .
		" `7that's available here. There's also some other" .
		" forms of entertainment here for those with the" .
		" occasional guilty pleasure. For example, there" .
		" is gambling going on at some of the tables," .
		" which of course, occasionally breaks out into" .
		" the expected fight or argument due to some" .
		" people accusing another of cheating. Along" .
		" with that, there are a number of very" .
		" attractive wenches of a vast array of races," .
		" most appearing to be human or at least" .
		" humanoid, but as most learn around here, looks" .
		" can be quite deceiving.");

	if ($session['user']['sex']) {
		output("You give a special wave and wink to the bard, %s`7, who is tuning his harp by the fire.",$partner);
	} else {
		output("You give a special wave and wink to the barmaid, %s`7, who is serving drinks to some locals.",$partner);
	}
	output(
		"`)%s`7, the bartender and owner of the establishment," .
		" stands behind the counter chatting with some of the patrons" .
		" while serving a varity of alcholic beverages for all species.",$barkeep);

	$chats = array(
		translate_inline("dragons"),
		translate_inline(getsetting("bard", "`^Seth")),
		translate_inline(getsetting("barmaid", "`%Violet")),
		translate_inline("`#MightyE"),
		translate_inline("fine drinks"),
		$partner,
	);
	$chats = modulehook("innchatter", $chats);
	$talk = $chats[e_rand(0, count($chats)-1)];
	output("You can't quite make out what the Bartender is saying, but it's something about %s`0.`n`n", $talk);
	output("The clock on the mantle reads `6%s`0.`n", getgametime());
	modulehook("inn-desc", array());
}
modulehook("inn", array());
module_display_events("inn", "inn.php");
?>