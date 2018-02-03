<?php
// translator ready
// addnews ready
// mail ready
require_once("common.php");
require_once("lib/http.php");
require_once("lib/buffs.php");
require_once("lib/sanitize.php");
require_once("lib/villagenav.php");

tlschema('stables');

$basetext=array(
	"title"=>"`i`#V`i`b`3e`b`Lh`b`li`b`~c`i`)l`i`7e `b`)D`b`~e`la`Ll`3e`#r",
	"desc"=>array(
		    "`7`cOut near the mountain of Sylisten, there lies a number" .
        " of quaint houses and tenement buildings, all in fairly" .
        " good condition and obviously quite sturdy. Closest to" .
        " the mountain, and quite practically built into the bottom" .
        " of the large geologic construct, you see a rather large building," .
        " but split into two. Each of the halves sporting totally different" .
        " styles and feels. One side was very much similar to that of a large" .
        " barn, housing many different types of animals from horses to" .
        " more exotic creatures" .
        " The other half was much more industrialized," .
        " having vehicles such as motorcycles, scooters, and even cars" .
        " should one be brave enough (or wealthy enough). `n`n" . 
        " As you move throughout the building you see two women," .
        " both similiar in appearance. The two seem busy as they move" .
        " throughout taking care of things on their respective sides." . 
        " The only way that you could tell the two women apart was " .
        " based on their clothing. One had a grease stains on her" .
        " face, her overalls, her tucker cap and even her hands." .
        " Based off of this, you can tell that she is the mechanic of the two." .
        " Her sister, on the other hand, wore jeans, a long-sleeved t-shirt" .
        " with the sleeves rolled up to just above her elbows, a pair of work" .
        " boots and sturdy work gloves for tending the live creatures." .
        " Once they noticed you, the two women welcomed you to their establishment.`c",

		array("`c`n`7When you approach, depending on what vehicle you plan to buy, you will either be greeted by a cheery \"`RHiya!`7\" or a short, but quick, \"`vHi.`7\" You would then be subject to the age-old saying of \"`RWhat can we do for you, %s?\" `7The two continue to speak, advertising their services, \"`vWe have the finest live mounts and mechanical mounts around!\"`c",translate_inline($session['user']['sex']?'lass':'lad'),getsetting('barkeep','`tCedrik')),
		"`0",
	),
	"nosuchbeast"=>"`m\"`&Are you joking? Such creation does not exist, here!`m\" shouts the shop keeper!",
	"finebeast"=>array(
		"`m\"`&That mount is one of my personal favorites!`m\" comments the shop keeper.`n`n",
		"`m\"`&You certainly have a fine eye for quality!`m\" exclaims the shop keeper.`n`n",
		"`m\"`&This mount will serve you well, indeed,`m\" says the shop keeper.`n`n",
		"`m\"`&Ill be sad to watch this one go, its perfection.`m\" says the shop keeper with pride.`n`n",
		"`m\"`&You have made a fine choice with this mount!`m\" says the shop keeper with pride.`n`n"
	),
	"toolittle"=>"`mThe shop keeper tilts his head to the side, giving you a sort of insulted look,  \"`&How do you expect to pay for this, then?  Maybe you did not hear me,  %s`& costs `^%s`& gold an' `%%s`& gems. Come back when you have the funds.`m\"",
	"replacemount"=>"`mYou hand over the reins to %s`m and the purchase price of your new mount, and the shop keeper leads out a fine new `&%s`m for you!`n`n",
	"newmount"=>"`mYou hand over the purchase price of your new critter, and the shop keeper leads out a fine `&%s`m for you!`n`n",
	"nofeed"=>"`m\"`&Arg, m'%s, what are you trying to do?  I cant recharge your device here!`m\"`nThe shop keeper thumps you on the back good naturedly, and sends you on your way.",
	"nothungry"=>"%s`m isn't hungry.  The shop keeper hands your gold back.",
	"halfhungry"=>"%s`m pinches a bit of the given food and leaves the rest alone. %s`m is fully restored. Because there is still more than half of the food left, the shop keeper gives you 50%% discount.`nYou only pay %s gold.",
	"hungry"=>"%s`m eats all the food greedily.`n%s`m is fully restored and you give your %s gold to the shop keeper.",
	"mountfull"=>"`n`m\"`&Yes,  there you go %s, your %s`& is full of fuel now!  I wont be able to fuel them again until tomorrow, however.  Well, enjoy your day!`m\"`nThe shop keeper whistles a jaunty tune and heads back to work.",
	"nofeedgold"=>"`mYou don't have enough gold with you to pay for the food. The shop keeper refuses to recharge your device and advises you to look for somewhere else to let %s`m graze for free, such as in the `@Forest`m.",
	"confirmsale"=>"`n`n`mThe shop keeper whistles.  \"`&Your device sure is a fond one, %s. Are you sure you want to part with it?`m\"`n`nHe waits for your answer.`m",
	"mountsold"=>"`mAs sad as it is to do so, you give up your precious %s`m, and a lone tear escapes your eye.`n`nHowever, the moment you spot the %s, you find that you're feeling quite a bit better.",
	"offer"=>"`n`n`&The shop keeper offers you `^%s`& gold and `%%s`& gems for %s`m.",
	"lass"=>"lass",
	"lad"=>"lad",
);
$schemas = array(
	'title'=>'stables',
	'desc'=>'stables',
	'nosuchbeast'=>'stables',
	'finebeast'=>'stables',
	'toolittle'=>'stables',
	'replacemount'=>'stables',
	'newmount'=>'stables',
	'nofeed'=>'stables',
	'nothungry'=>'stables',
	'halfhungry'=>'stables',
	'hungry'=>'stables',
	'mountfull'=>'stables',
	'nofeedgold'=>'stables',
	'confirmsale'=>'stables',
	'mountsold'=>'stables',
	'offer'=>'stables',
);
$basetext['schemas']=$schemas;
$texts = modulehook("stabletext", $basetext);
$schemas = $texts['schemas'];

tlschema($schemas['title']);
page_header($texts['title']);
tlschema();

addnav("Other");
villagenav();
//addnav("Go to Forest","forest.php");
modulehook("stables-nav");

require_once("lib/mountname.php");
list($name, $lcname) = getmountname();

$repaygold = 0;
$repaygems = 0;
$grubprice = 0;

if ($playermount) {
	$repaygold = round($playermount['mountcostgold']*2/3,0);
	$repaygems = round($playermount['mountcostgems']*2/3,0);
	$grubprice = round($session['user']['level']*$playermount['mountfeedcost'], 0);
}
$confirm = 0;

$op = httpget('op');
$id = httpget('id');

global $playermount;

if ($op==""){
	checkday();
	tlschema($schemas['desc']);
  	if (is_array($texts['desc'])) {
  		foreach ($texts['desc'] as $description) {
  			output_notl(sprintf_translate($description));
  		}
  	} else {
  		output($texts['desc']);
  	}
	tlschema();
	modulehook("stables-desc");
}elseif($op=="examine"){
require_once('lib/sanitize.php');
	$sql = "SELECT * FROM " . db_prefix("mounts") . " WHERE mountid='$id'";
	$result = db_query_cached($sql, "mountdata-$id", 3600);
	if (db_num_rows($result)<=0){
		tlschema($schemas['nosuchbeast']);
		output($texts['nosuchbeast']);
		tlschema();
	}else{
		// Idea taken from Robert of dragonprime.cawsquad.net
		$t = e_rand(0,count($texts['finebeast'])-1);
		tlschema($schemas['finebeast']);
		output($texts['finebeast'][$t]);
		tlschema();
		$mount = db_fetch_assoc($result);
		output("`mCreature: `&%s`m`n", $mount['mountname']);
		output("`mDescription: `&%s`m`n", $mount['mountdesc']);
		output("`mCost: `^%s`& gold, `%%s`& gems`n`n", $mount['mountcostgold'], $mount['mountcostgems']);
		$grr = color_sanitize($mount['mountname']);
		addnav(array("New %s", $grr));
		addnav("Buy this Mount","stables.php?op=buymount&id={$mount['mountid']}");
	}
}elseif($op=='buymount'){
	if ($session['user']['hashorse']) {
		tlschema($schemas['confirmsale']);
		output($texts['confirmsale'],
				($session['user']['sex']?$texts["lass"]:$texts["lad"]));
		tlschema();
		addnav("Confirm trade");
		addnav("Yes", "stables.php?op=confirmbuy&id=$id");
		addnav("No","stables.php");
		$confirm = 1;
	} else {
		$op="confirmbuy";
		httpset("op",$op);
	}
}
if ($op == 'confirmbuy') {
	$sql = "SELECT * FROM " . db_prefix("mounts") . " WHERE mountid='$id'";
	$result = db_query_cached($sql, "mountdata-$id", 3600);
	if (db_num_rows($result)<=0){
		tlschema($schemas['nosuchbeast']);
		output($texts['nosuchbeast']);
		tlschema();
	}else{
		$mount = db_fetch_assoc($result);
		if (($session['user']['gold']+$repaygold) < $mount['mountcostgold'] ||
			($session['user']['gems']+$repaygems) < $mount['mountcostgems']){
			tlschema($schemas['toolittle']);
			output($texts['toolittle'], $mount['mountname'], $mount['mountcostgold'], $mount['mountcostgems']);
			tlschema();
		}else{
			if ($session['user']['hashorse']>0){
				tlschema($schemas['replacemount']);
				output($texts['replacemount'], $lcname, $mount['mountname']);
				tlschema();
			}else{
				tlschema($schemas['newmount']);
				output($texts['newmount'], $mount['mountname']);
				tlschema();
			}
			$debugmount1=isset($playermount['mountname'])?$playermount['mountname']:false;
			if ($debugmount1) $debugmount1="a ".$debugmount1;
			$session['user']['hashorse']=$mount['mountid'];
			$debugmount2=$mount['mountname'];
			$goldcost = $repaygold-$mount['mountcostgold'];
			$session['user']['gold']+=$goldcost;
			$gemcost = $repaygems-$mount['mountcostgems'];
			$session['user']['gems']+=$gemcost;
			debuglog(($goldcost <= 0?"spent ":"gained ") . abs($goldcost) . " gold and " . ($gemcost <= 0?"spent ":"gained ") . abs($gemcost) . " gems trading $debugmount1 for a new mount, a $debugmount2");
			$buff = unserialize($mount['mountbuff']);
			if ($buff['schema'] == "") $buff['schema'] = "mounts";
			apply_buff('mount',unserialize($mount['mountbuff']));
			// Recalculate so the selling stuff works right
			$playermount = getmount($mount['mountid']);
			$repaygold = round($playermount['mountcostgold']*2/3,0);
			$repaygems = round($playermount['mountcostgems']*2/3,0);
			// Recalculate the special name as well.
			modulehook("stable-mount", array());
			modulehook("boughtmount");
			require_once("lib/mountname.php");
			list($name, $lcname) = getmountname();
			$grubprice = round($session['user']['level']*$playermount['mountfeedcost'], 0);
		}
	}
}elseif($op=='feed'){
	if (getsetting("allowfeed", 0) == 0) {
		tlschema($schemas['nofeed']);
		output($texts['nofeed'],
				($session['user']['sex']?$texts["lass"]:$texts["lad"]));
		tlschema();
	} elseif($session['user']['gold']>=$grubprice) {
		$buff = unserialize($playermount['mountbuff']);
		if (!isset($buff['schema']) || $buff['schema'] == "") $buff['schema'] = "mounts";
		if (isset($session['bufflist']['mount']) && $session['bufflist']['mount']['rounds'] == $buff['rounds']) {
			tlschema($schemas['nothungry']);
			output($texts['nothungry'],$name);
			tlschema();
		} else {
			if (isset($session['bufflist']['mount']) && $session['bufflist']['mount']['rounds'] > $buff['rounds']*.5) {
				$grubprice=round($grubprice/2,0);
				tlschema($schemas['halfhungry']);
				output($texts['halfhungry'], $name, $name, $grubprice);
				tlschema();
				$session['user']['gold']-=$grubprice;
			}else{
				$session['user']['gold']-=$grubprice;
				tlschema($schemas['hungry']);
				output($texts['hungry'], $name, $name, $grubprice);
				tlschema();
			}
			debuglog("spent $grubprice feeding their mount");
			apply_buff('mount',$buff);
			$session['user']['fedmount'] = 1;
			tlschema($schemas['mountfull']);
			output($texts['mountfull'],
				($session['user']['sex']?$texts["lass"]:$texts["lad"]),
				($playermount['basename']?
				 $playermount['basename']:$playermount['mountname']));
			tlschema();
		}
	} else {
		tlschema($schemas['nofeedgold']);
		output($texts['nofeedgold'], $lcname);
		tlschema();
	}
}elseif($op=='sellmount'){
	tlschema($schemas['confirmsale']);
	output($texts['confirmsale'],
			($session['user']['sex']?$texts["lass"]:$texts["lad"]));
	tlschema();
	addnav("Confirm sale");
	addnav("Yes", "stables.php?op=confirmsell");
	addnav("No","stables.php");
	$confirm = 1;
}elseif($op=='confirmsell'){
	$session['user']['gold']+=$repaygold;
	$session['user']['gems']+=$repaygems;
	$debugmount=$playermount['mountname'];
	debuglog("gained $repaygold gold and $repaygems gems selling their mount, a $debugmount");
	strip_buff('mount');
	$session['user']['hashorse']=0;
	modulehook("soldmount");

	$amtstr = "";
	if ($repaygold > 0) {
		$amtstr .= "%s gold";
	}
	if ($repaygems > 0) {
		if ($repaygold) $amtstr .= " and ";
		$amtstr .= "%s gems";
	}
	if ($repaygold > 0 && $repaygems > 0) {
		$amtstr = sprintf_translate($amtstr, $repaygold, $repaygems);
	} elseif ($repaygold > 0) {
		$amtstr = sprintf_translate($amtstr, $repaygold);
	} else {
		$amtstr = sprintf_translate($amtstr, $repaygems);
	}

	tlschema($schemas['mountsold']);
	output($texts['mountsold'],
			($playermount['newname']?
			   $playermount['newname']:$playermount['mountname']),
			$amtstr);
	tlschema();
}

if ($confirm == 0) {
	if ($session['user']['hashorse']>0){
		addnav(array("%s", color_sanitize($name)));
		tlschema($schemas['offer']);
		output($texts['offer'], $repaygold, $repaygems, $lcname);
		tlschema();
		addnav(array("Sell %s`m", $lcname),"stables.php?op=sellmount");
		if (getsetting("allowfeed", 0) && $session['user']['fedmount']==0) {
			addnav(array("Recharge %s`m (`^%s`m gold)", $lcname, $grubprice),
					"stables.php?op=feed");
		}
	}

	$sql = "SELECT mountname,mountid,mountcategory,mountdkcost FROM " . db_prefix("mounts") .  " WHERE mountactive=1 AND mountlocation IN ('all','{$session['user']['location']}') ORDER BY mountcategory,mountcostgems,mountcostgold";
	$result = db_query($sql);
	$category="";
	$number=db_num_rows($result);
	for ($i=0;$i<$number;$i++){
		$row = db_fetch_assoc($result);
		if ($category!=$row['mountcategory']){
			addnav(array("%s", $row['mountcategory']));
			$category = $row['mountcategory'];
		}
		if ($row['mountdkcost'] <= $session['user']['dragonkills'])
			addnav(array("%s`m", $row['mountname']),"stables.php?op=examine&id={$row['mountid']}");
	}
}

page_footer();
?>