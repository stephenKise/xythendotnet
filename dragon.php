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
page_header("The TentroMech!");
$op=httpget('op');
if ($op=="")
{
	if (!httpget('nointro'))
	{
		output("`\$Gas mask strapped firmly against your face, you fight the fear that was instilled upon you previously. Your path leading you towards the mountain, in which you will seek the Tentromech's lair...



As quiet as you attempt to be, you know the echoes of the mountain will upset one of the many rouge machines inside of the cave, but once you find the entrance - no one is waiting for you. Cautiously, you enter the cave...Only to find it empty.");
		output("A thunderous sound leads to the ground shaking, stalagmites on the roof of the cave loosening and cascading to the floor...The Tentromech has discovered you, actually having watched you from the moment you stepped near the lagoon... It has you trapped.");
	}
	$badguy=array("creaturename" => translate_inline("`\$Tentromech`0"), "creaturelevel" => 18, "creatureweapon" => translate_inline("Tentacles"), "creatureattack" => 45, "creaturedefense" => 25, "creaturehealth" => 300, "diddamage" => 0, "type" => "dragon");
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
	output("`7The mountain continues to shake as the Tentromech's titanium tentacles unleash from the mountainside - the sharp prongs that extended from the heavy metal were the only thing keeping the creature suctioned perfectly to the rocks, now mangled and broken. You hear a screeching sound, as though the creature was letting out once last mechanical roar into the highly polluted air... ");
	output("`7From head to toe you are covered in a thick oil substance, probably one of the final defense motions of the creature...A few loud explosions occur within the giant mechanism, fire inside the aqua-domed eyes that resembled control panels at one point. Whatever was left of the rouge machines must have exploded along with the machine of death, at this rate. ");
	output("`7From the mouth of the Tentromech falls a machine, mangled from the waist down...As the creature crawls towards your legs, you stamper back. Reaching, the machine pounds its fist into the ground furiously. `^You are foolish, you impure resident of Xythen!`7 The device mutters, having great difficulty between all of the spark sounds and ticking of its mechanical heart...`^We..The true residents of Xythen...Before the impure portals to other dimensions showed up...`7 The creature released a mechanical cough as the rest of the Tentromech disappeared into the lagoon..`^There are thousands of us ready to repair the war machine, Tentromech! We shall not give up the right to Xythen! We shall not share!`7 As the machine continued to animate itself, oil leaked onto the floor - steam flowing from all angles of the machine's body... ");
	output("`7You shake your head...`^Xythen belongs to many...Not just one race.`7 You utter, kicking at the mechanical creature to end it's misery... ");
	output("As soon as you do this, however...The creature quickly swipes at your legs with a syringe, penetrating your armor with a defensive drill. As a strange fluid flows into your veins, the mechanical creature states, `^You are as dead as the Tentromech...`7 .`n`n");
	output("Realizing that already the edges of your vision are a little dim, you flee from the cave, bound to reach the healer's hut before it is too late. Somewhere along the way you lose your weapon, and finally you trip on a stone and into the polluted lagoon, sight now limited to only a small circle that seems to float around your head. As you lay, staring up through the smoky sky, you think that nearby you can hear the sounds of the village. Your final thought is that although you defeated the Tentromech, you reflect on the irony that it defeated you in an attempt to even the odds. As your vision winks out, far away in the polluted lagoon...");
	if ($flawless)
	{
		output("`n`nYou fall forward, and remember at the last moment that you at least managed to grab some of the Tentromech's treasure, so maybe it wasn't all a total loss.");
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
		die("ACK!! Somehow this user would end up perma-dead.. Not allowing DK to proceed!  Notify admin and figure out why this would happen so that it can be fixed before DK can continue.");
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
	output("`n`nYou wake up in the midst of some trees.  Nearby you hear the sounds of a village.");
	output("Dimly you remember that you are a new warrior, and something of a dangerous War Machine controlled by Rogue Machines plaguing the area. You decide you would like to earn a name for yourself by perhaps some day confronting this deadly creature. ");
	// allow explanative text as well.
	modulehook("dragonkilltext");
	$regname=get_player_basename();
	output("`n`n`^You are now known as `&%s`^!!", $session['user']['name']);
	if ($session['user']['dragonkills']==1)
	{
		addnews("`#%s`# is now `&%s`# for slaying the `\$Tentromech `^%s`# time!", $regname, $session['user']['title'], $session['user']['dragonkills']);
		addnews("`7The `\$Tentromech`7 has been obliterated by %s!", $regname, $session['user']['title']);
		output("`n`n`&Because you have slain the Tentromech %s time, you start with some extras.  You also keep additional permanent hitpoints you've earned.`n", $session['user']['dragonkills']);
	}
	elseif (($session['user']['dragonkills']%25)==0)
	{
		addnews("`#%s`# is now `&%s`# for slaying the `\$Tentromech `^%s`# times!", $regname, $session['user']['title'], $session['user']['dragonkills']);
		output("`n`n`&Because you have slain the Tentromech %s times, you start with some extras.  You also keep additional permanent hitpoints you've earned.`n", $session['user']['dragonkills']);
	}
	$session['user']['charm']+=5;
	output("`^You gain FIVE charm points for having defeated the `\$Tentromech!`n");
	debuglog("slew the dragon and starts with {$session['user']['gold']} gold and {$session['user']['gems']} gems");
	// Moved this here to make some things easier.
	modulehook("dragonkill", array());
	invalidatedatacache("list.php-warsonline");
}
if ($op=="run")
{
	output("The creature's tail blocks the only exit to its lair!");
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
		output("`&With a mighty final blow, `\$Tentromech`& lets out a tremendous bellow and falls at your feet, dead at last.");
		//addnews("`&%s has slain the hideous creature known as `\$Tentromech`&.  All across the land, people rejoice!",$session['user']['name']);
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
				addnews("`%%s`5 has been slain by the `\$Tentromech`5.`n%s", $session['user']['name'], $taunt);
			}
			else
			{
				addnews("`%%s`5 has been slain by the `\$Tentromech`5e.`n%s", $session['user']['name'], $taunt);
			}
			$session['user']['alive']=false;
			debuglog("lost {$session['user']['gold']} gold when they were slain");
			$session['user']['gold']=0;
			$session['user']['hitpoints']=0;
			output("`b`&You have been slain by `$Tentromech`&!!!`n");
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