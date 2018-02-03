<?php

// addnews ready
// translator ready
// mail ready
require_once ("common.php");
require_once ("lib/fightnav.php");
require_once ("lib/titles.php");
require_once ("lib/http.php");
require_once ("lib/buffs.php");
require_once ("lib/taunt.php");
require_once ("lib/names.php");
tlschema("dragon");
$battle=false;
page_header("The Crystal Guardian!");
$op=httpget('op');
if ($op=="")
{
	if (!httpget('nointro'))
	{
		output("`n`c`3Fighting back against the instinct to run, you reach forth " .
			"and place your palm firmly against the crystal. Light shoots " .
			"from it towards the heavens above as the ground around you " .
			"quakes...Every crystal shard in the massive crater rattling and " .
			"trembling, filling the air with strange melodious music, before " .
			"they shoot into the air, coalescing before you in a single " .
			"form...barely humanoid in shape and held by raw Aether, " .
			"the manifestation of this land appears before you, " .
			"ready to vanquish you for your gall!`c`n");
	}
	$badguy=array("creaturename" => translate_inline("`\$Crystal Guardian`0"), "creaturelevel" => 18, "creatureweapon" => translate_inline("a Shardblade"), "creatureattack" => 45, "creaturedefense" => 25, "creaturehealth" => 300, "diddamage" => 0, "type" => "dragon");
	//toughen up each consecutive dragon.
	// First, find out how each dragonpoint has been spent and count those
	// used on attack and defense.
	// Coded by JT, based on collaboration with MightyE
	$points=0;
	restore_buff_fields();
	reset($session['user']['dragonpoints']);
	while (list($key, $val)=each($session['user']['dragonpoints']))
	{
		if ($val=="at" || $val=="de")
			$points++;
	}
	// Now, add points for hitpoint buffs that have been done by the dragon
	// or by potions!
	$points+=(int) (($session['user']['maxhitpoints']-150)/5);
	$points=round($points*.75, 0);
	$atkflux=e_rand(0, $points);
	$defflux=e_rand(0, $points-$atkflux);
	$hpflux=($points-($atkflux+$defflux))*5;
	debug("DEBUG: $points modification points total.`0`n");
	debug("DEBUG: +$atkflux allocated to attack.`n");
	debug("DEBUG: +$defflux allocated to defense.`n");
	debug("DEBUG: +".($hpflux/5)."*5 to hitpoints.`0`n");
	calculate_buff_fields();
	$badguy['creatureattack']+=$atkflux;
	$badguy['creaturedefense']+=$defflux;
	$badguy['creaturehealth']+=$hpflux;
	$badguy=modulehook("buffdragon", $badguy);
	$session['user']['badguy']=createstring($badguy);
	$battle=true;
}
elseif ($op=="prologue1")
{
	output("`@Victory!`n`n");
	$flawless=(int) (httpget('flawless'));
	if ($flawless)
	{
		output("`b`c`&~~ Flawless Fight ~~`0`c`b`n`n");
	}
	output("`c`3As you continue to battle, you begin to notice the Aether " .
		"Guardian becoming less and less cohesive, with small crystals " .
		"falling with every blow, until your last strike rends apart it's " .
		"core. The creature begins to fall, limbs collapsing as the energy " .
		"emanating it continues to pulse, before exploding in a flash of " .
		"brilliance, the energy moving through you as the crystals begin to " .
		"sink back into the earth peppered around the crater. The larger ". 
		"crystal behind you ceases to glow, the haze ceasing its spread, for " .
		"now. `n`nYou won. You have killed the Aether Guardian...But as you " .
		"walk away you know it was not a clean victory. Your vision blurs, " .
		"your legs wobble beneath you. The Aether radiation...even with all " .
		"your protection, its getting to you. You keep walking, trying to get " .
		"out of this place, trying to reach civilisation!...You collapse upon " .
		"the ground as darkness takes you. Before your eyes close, you notice " .
		"a small spark of light drum within the central crystal. " .
		"`n`nYou awake along the edge of Crystal Forest, a well-travelled " .
		"path leading you to a nearby city. You dimly recall you are a " .
		"fighter, an adventurer, and that something in the Crystal Forest has " .
		"been creating a haze, spreading the Forest more and more with each " .
		"year. Deciding to make a name for yourself you decide that you shall " .
		"train until one day you can find the source of this evil.`c");

	if ($flawless)
	{
		output("`n`n`#You fall forward, and remember at the last moment that " .
			"you at least managed to grab some of the Crystal Guardian's" .
			" treasure, so maybe it wasn't all a total loss.`0");
	}
	addnav("It is a new day", "news.php");
	strip_all_buffs();
	$sql="DESCRIBE ".db_prefix("accounts");
	$result=db_query($sql);
	reset($session['user']['dragonpoints']);
	$dkpoints=0;
	while (list($key, $val)=each($session['user']['dragonpoints']))
	{
		if ($val=="hp")
			$dkpoints+=5;
	}
	restore_buff_fields();
	$hpgain=array('total' => $session['user']['maxhitpoints'], 'dkpoints' => $dkpoints, 'extra' => $session['user']['maxhitpoints']-$dkpoints-($session['user']['level']*10), 'base' => $dkpoints+($session['user']['level']*10),);
	$hpgain=modulehook("hprecalc", $hpgain);
	calculate_buff_fields();
	$nochange=array("acctid" => 1, "name" => 1, "sex" => 1, "password" => 1, "marriedto" => 1, "title" => 1, "login" => 1, "dragonkills" => 1, "locked" => 1, "loggedin" => 1, "superuser" => 1, "gems" => 1, "hashorse" => 1, "gentime" => 1, "gentimecount" => 1, "lastip" => 1, "uniqueid" => 1, "dragonpoints" => 1, "laston" => 1, "prefs" => 1, "lastmotd" => 1, "emailaddress" => 1, "emailvalidation" => 1, "gensize" => 1, "bestdragonage" => 1, "dragonage" => 1, "donation" => 1, "donationspent" => 1, "donationconfig" => 1, "bio" => 1, "charm" => 1, "banoverride" => 1, "referer" => 1, "refererawarded" => 1, "ctitle" => 1, "beta" => 1, "clanid" => 1, "clanrank" => 1, "clanjoindate" => 1, "regdate" => 1, "seenupdates" => 1, "pollvotes" => 1, "forumpeepdata" => 1, "dwgold" => 1, "dwgems" => 1);
	$nochange=modulehook("dk-preserve", $nochange);
	$session['user']['dragonage']=$session['user']['age'];
	if ($session['user']['dragonage']<$session['user']['bestdragonage'] || $session['user']['bestdragonage']==0)
	{
		$session['user']['bestdragonage']=$session['user']['dragonage'];
	}
	$number=db_num_rows($result);
	for ($i=0; $i<$number; $i++)
	{
		$row=db_fetch_assoc($result);
		if (array_key_exists($row['Field'], $nochange) && $nochange[$row['Field']])
		{
		}
		elseif ($row['Field']=="location")
		{
			$session['user'][$row['Field']]=getsetting("villagename", LOCATION_FIELDS);
		}
		else
		{
			$session['user'][$row['Field']]=$row["Default"];
		}
	}
	$session['user']['gold']=getsetting("newplayerstartgold", 50);
	$newtitle=get_dk_title($session['user']['dragonkills'], $session['user']['sex']);
	$restartgold=$session['user']['gold']+getsetting("newplayerstartgold", 50)*$session['user']['dragonkills'];
	$restartgems=0;
	if ($restartgold>getsetting("maxrestartgold", 300))
	{
		$restartgold=getsetting("maxrestartgold", 300);
		$restartgems=max(0, ($session['user']['dragonkills']-(getsetting("maxrestartgold", 300)/getsetting("newplayerstartgold", 50))-1));
		if ($restartgems>getsetting("maxrestartgems", 10))
		{
			$restartgems=getsetting("maxrestartgems", 10);
		}
	}
	$session['user']['gold']=$restartgold;
	$session['user']['gems']+=$restartgems;
	if ($flawless)
	{
		$session['user']['gold']+=3*getsetting("newplayerstartgold", 50);
		$session['user']['gems']+=1;
	}
	$session['user']['maxhitpoints']=10+$hpgain['dkpoints']+$hpgain['extra'];
	$session['user']['hitpoints']=$session['user']['maxhitpoints'];
	// Sanity check
	if ($session['user']['maxhitpoints']<1)
	{
	// Yes, this is a freaking hack.
		die("ACK!! Somehow this user would end up perma-dead.. Not allowing CK to proceed!  Notify admin and figure out why this would happen so that it can be fixed before DK can continue.");
		exit ();
	}
	// Set the new title.
	$newname=change_player_title($newtitle);
	$session['user']['title']=$newtitle;
	$session['user']['name']=$newname;
	reset($session['user']['dragonpoints']);
	while (list($key, $val)=each($session['user']['dragonpoints']))
	{
		if ($val=="at")
		{
			$session['user']['attack']++;
		}
		if ($val=="de")
		{
			$session['user']['defense']++;
		}
	}
	$session['user']['laston']=date("Y-m-d H:i:s", strtotime("-1 day"));
	$session['user']['slaydragon']=1;
	$companions=array();
	$session['user']['companions']=array();
	// allow explanative text as well.
	modulehook("dragonkilltext");
	$regname=get_player_basename();
	output("`n`n`^You are now known as `&%s`^!!", $session['user']['name']);
	if ($session['user']['dragonkills']==1)
	{
		addnews("`#%s`# is now `&%s`# for slaying the `\$Crystal Guardian `^%s`# time!", $regname, $session['user']['title'], $session['user']['dragonkills']);
		addnews("`7The `\$Crystal Guardian`7 has been obliterated by %s!", $regname, $session['user']['title']);
		output("`n`n`&Because you have slain the Crystal Guardian %s time, you start with some extras.  You also keep additional permanent hitpoints you've earned.`n", $session['user']['dragonkills']);
	}
	elseif (($session['user']['dragonkills']%25)==0)
	{
		addnews("`#%s`# is now `&%s`# for slaying the `\$Crystal Guardian `^%s`# times!", $regname, $session['user']['title'], $session['user']['dragonkills']);
		output("`n`n`&Because you have slain the Crystal Guardian %s times, you start with some extras.  You also keep additional permanent hitpoints you've earned.`n", $session['user']['dragonkills']);
	}
	$session['user']['charm']+=5;
	output("`^You gain FIVE charm points for having defeated the `\$Crystal Guardian!`n");
	debuglog("slew the dragon and starts with {$session['user']['gold']} gold and {$session['user']['gems']} gems");
	// Moved this here to make some things easier.
	modulehook("dragonkill", array());
	invalidatedatacache("list.php-warsonline");
}
if ($op=="run")
{
	output("The exit is blocked by a barricade of crystal shards...");
	$op="fight";
	httpset('op', 'fight');
}
if ($op=="fight" || $op=="run")
{
	$battle=true;
}
if ($battle)
{
	require_once ("battle.php");
	if ($victory)
	{
		$flawless=0;
		if ($badguy['diddamage']!=1)
			$flawless=1;
		$session['user']['dragonkills']++;
		output("`&With a mighty final blow, `\$Crystal Guardian`& lets out a tremendous bellow and falls at your feet, dead at last.");
		//addnews("`&%s has slain the hideous creature known as `\$Crystal Guardian`&.  All across the land, people rejoice!",$session['user']['name']);
		modulehook("newraces");
		tlschema("nav");
		addnav("Continue", "dragon.php?op=prologue1&flawless=$flawless");
		tlschema();
	}
	else
	{
		if ($defeat)
		{
			tlschema("nav");
			addnav("Daily news", "news.php");
			tlschema();
			$taunt=select_taunt_array();
			if ($session['user']['sex'])
			{
				addnews("`%%s`5 has been slain by the `\$Crystal Guardian`5.`n%s", $session['user']['name'], $taunt);
			}
			else
			{
				addnews("`%%s`5 has been slain by the `\$Crystal Guardian`5e.`n%s", $session['user']['name'], $taunt);
			}
			$session['user']['alive']=false;
			debuglog("lost {$session['user']['gold']} gold when they were slain");
			$session['user']['gold']=0;
			$session['user']['hitpoints']=0;
			output("`b`&You have been slain by `$Crystal Guardian`&!!!`n");
			output("`4All gold on hand has been lost!`n");
			output("You may begin fighting again tomorrow.");
			page_footer();
		}
		else
		{
			fightnav(true, false);
		}
	}
}
page_footer();

?>