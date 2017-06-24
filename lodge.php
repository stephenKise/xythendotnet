<?php
// translator ready
// addnews ready
// mail ready
require_once("common.php");
require_once("lib/commentary.php");
require_once("lib/sanitize.php");
require_once("lib/http.php");
require_once("lib/villagenav.php");
require_once("lib/names.php");

tlschema("lodge");

addcommentary();

$op = httpget('op');
if ($op == "") checkday();

$pointsavailable =
	$session['user']['donation']-$session['user']['donationspent'];
$entry = ($session['user']['donation'] > 0) || ($session['user']['superuser'] & SU_EDIT_COMMENTS);
if ($pointsavailable < 0) $pointsavailable = 0; // something weird.

page_header("JCP Lodge");
addnav("Referrals", "referral.php");
if ($op != "" && $entry)
	addnav("L?Back to the Lodge", "lodge.php");
//addnav("Describe Points","lodge.php?op=points");
villagenav();


if ($op==""){
	output("`c`b`LJCP Lodge`b`c`n");
	output("`0");
	output(" `7As you enter the high class establishment named the `3JCP Lodge`7, you find yourself feeling slightly under dressed...A man at the entrance looks you over, clearing his throat in a displeased manner. You feel as though you may be cast back out onto the city streets...Quickly, you flash a voucher towards the man. `3Very good. `7The man says, his chin high as though he was some figure of importance, `3Carry on.`n`n");

	if ($entry){
		output("`7Down the hall, you pass another set of large doors that were decorated in a rustic golden material. These, lead to a foyer room filled with elegant furniture, a bar, and very highly established decor. Continuing onward, you are urged to move towards a secluded counter in the middle of the room`n`n");
		output("`c`7A small woman in a pinstriped dress greets you from behind a very well crafted reception desk, `&Hello, and welcome to the JCP lodge. According to our records, you have earned `^%s`& points and have `^%s`& points available to spend.`7 `7The woman moves a slice of paper towards the edge of the desk, with the available purchases. Patiently, she awaits your decision...The list seems to be extensive, so you take your time. `n`n`c", $session['user']['donation'], $pointsavailable);
		commentdisplay("`7Nearby, you can hear a few high-end individuals discussing their worth:`n", "hunterlodge","Talk quietly",25);
		addnav("Use Points");
		modulehook("lodge");
		addnav("Gameplay Advantage");
		addnav("Other");
		addnav("`4C`Qo`^l`@o`#u`Vr T`#e`@s`^t`Qe`4r","colortestbox.html",false,true);
		if ($session['user']['superuser'] & SU_MEGAUSER) addnav("SU Add 10k DP","lodge.php?op=superuseradd");
	}else{
		$iname = getsetting("innname", LOCATION_INN);
		output("`0You pull out your Frequent Boozer Card from %s, with 9 out of the 10 slots punched out with a small profile of %s`0's Head.`n`n", $iname,getsetting('barkeep','`tCedrik'));
		output("`0The guard glances at it, advises you not to drink so much, and directs you down the path.");
	}
}else if ($op == "superuseradd")
{
	require_once('lib/redirect.php');
	$session['user']['donation'] += 10000;
	redirect('lodge.php');
}else if ($op=="points"){
	output("`b`3Points:`b`n`n");
	$points_messages = modulehook(
		"donator_point_messages",
		array(
			'messages'=>array(
				'default'=>tl("`7For each $1 donated, the account which makes the donation will receive 100 contributor points in the game.`n`n")
			)
		)
	);
	foreach($points_messages['messages'] as $id => $message){
		output_notl($message, true);
	}
//	output("\"`&But what are points,`7\" you ask?");
//	output("Points can be redeemed for various advantages in the game.");
//	output("You'll find access to these advantages in the Hunter's Lodge.");
//	output("As time goes on, more advantages will likely be added, which can be purchased when they are made available.`n`n");
//	output("`0Donating even one dollar will gain you a membership card to the Hunter's Lodge, an area reserved exclusively for contributors.");
//	output("Donations are accepted in whole dollar increments only.`n`n");
	//output("\"`&But I don't have access to a PayPal account, or I otherwise can't donate to your very wonderful project!`7\"`n");
           // yes, "referer" is misspelt here, but the game setting was also misspelt
	if (getsetting("refereraward", 25)) {
	//	output("`0Well, there is another way that you can obtain points: by referring other people to our site!");
	//	output("`0You'll get %s points for each person whom you've referred who makes it to level %s.", getsetting("refereraward", 25), getsetting("referminlevel", 4));
	//	output("`0Even one person making it to level %s will gain you access to the Hunter's Lodge.`n`n", getsetting("referminlevel", 4));
	}
	//output("`0You can also gain contributor points for contributing in other ways that the administration may specify.");
	//output("`0So, don't despair if you cannot send cash, there will always be non-cash ways of gaining contributor points.`n`n");
//	output("`b`3Purchases that are currently available:`0`b`n");
	$args = modulehook("pointsdesc", array("format"=>"`#&#149;`7 %s`n", "count"=>0));
	if ($args['count'] == 0) {
		output("`#&#149;`7None -- Please talk to your admin about creating some.`n", true);
	}
}

page_footer();
?>