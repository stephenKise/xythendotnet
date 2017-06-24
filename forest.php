<?php
// addnews ready
// translator ready
// mail ready
require_once("common.php");
require_once("lib/forest.php");
require_once("lib/fightnav.php");
require_once("lib/http.php");
require_once("lib/taunt.php");
require_once("lib/events.php");
require_once("lib/battle-skills.php");

tlschema("forest");

$fight = false;
page_header("The Forest");
$dontdisplayforestmessage=handle_event("forest");

$op = httpget("op");

$battle = false;

if ($op=="run"){
	if (e_rand()%3 == 0){
		output ("`c`b`&You have successfully fled your opponent!`0`b`c`n");
		$op="";
		httpset('op', "");
		unsuspend_buffs();
		foreach($companions as $index => $companion) {
			if(isset($companion['expireafterfight']) && $companion['expireafterfight']) {
				unset($companions[$index]);
			}
		}
	}else{
		output("`c`b`\$You failed to flee your opponent!`0`b`c");
	}
}
if ($op=="dragon"){
	require_once("lib/partner.php");
	addnav("Search The Mountain","dragon.php");
	addnav("Retreat in Shame","inn.php?op=fleedragon");
	output("`\$As you approach the forbidden lagoon of pollution, a shiver buckles down your spine. From where you are, beyond the tree line, you can see the mountainous structure that encased the resting place of the Tentromech. As you press forward, the bubbling green water before you releases a gurgling noise - as though the war-machine knew you were drawing close...");
	output("There is no way to see through the polluted water before you. Each ripple of the lagoon pushes oil and scrap metal chunks into the blackened sand. The stench of the pollution nearly chokes you, but glancing around the area - you notice a corpse nearby with a gas mask on...");
	output("Ripping the mask off of the corpse, you flinch back in disgust... The rotting flesh of the being before you, mangled - even with its defenses. Gulping, you look to the mask and question your future actions.");
	output("If you chose to go home, you would not have the possible chance of being mangled and murdered like the being before you...`n`n");
	output("But if you allow the war machine to continue its reign of terror...You are no friend of Xythen - and you would live in shame at dismissing such an opportunity. ");
	output("The sound starts again, and stops again in a regular rhythm.`n`n");
	output("What do you do?`0");
	$session['user']['seendragon']=1;
}
//Claburatura
if ($op=="search"){
	checkday();
	/*if ($session['user']['turns']<=0){
		output("`\$`bYou are too tired to search the forest any longer today.  Perhaps tomorrow you will have more energy.`b`0");
		$op="";
		httpset('op', "");
	}else{*/
		modulehook("forestsearch", array());
		$args = array(
			'soberval'=>0.9,
			'sobermsg'=>"`&Faced with the prospect of death, you sober up a little.`n",
			'schema'=>'forest');
		modulehook("soberup", $args);
		if (module_events("forest", getsetting("forestchance", 15)) != 0) {
			if (!checknavs()) {
				// If we're showing the forest, make sure to reset the special
				// and the specialmisc
				$session['user']['specialinc'] = "";
				$session['user']['specialmisc'] = "";
				$dontdisplayforestmessage=true;
				$op = "";
				httpset("op", "");
			} else {
				page_footer();
			}
		}else{
			$session['user']['turns']--;
			$battle=true;
			if (e_rand(0,2)==1){
				$plev = (e_rand(1,5)==1?1:0);
				$nlev = (e_rand(1,3)==1?1:0);
			}else{
				$plev=0;
				$nlev=0;
			}
			$type = httpget('type');
			if ($type=="slum"){
				$nlev++;
				output("`\$You head for the section of forest you know to contain foes that you're a bit more comfortable with.`0`n");
			}
			if ($type=="thrill"){
				$plev++;
				output("`\$You head for the section of forest which contains creatures of your nightmares, hoping to find one of them injured.`0`n");
			}
			$extrabuff = 0;
			if ($type=="suicide"){
				if ($session['user']['level'] <= 7) {
					$plev += 1;
					$extrabuf = .25;
				} elseif ($session['user']['level'] < 14) {
					$plev+=2;
					$extrabuf = 0;
				} else {
					$plev++;
					$extrabuff = .4;
				}
				output("`\$You head for the section of forest which contains creatures of your nightmares, looking for the biggest and baddest ones there.`0`n");
			}
			$multi = 1;
			$targetlevel = ($session['user']['level'] + $plev - $nlev );
			$mintargetlevel = $targetlevel;
			if (getsetting("multifightdk", 10) <= $session['user']['dragonkills']) {
				if (e_rand(1,100) <= getsetting("multichance", 25)) {
					$multi = e_rand(getsetting("multibasemin", 2),getsetting("multibasemax", 3));
					if ($type=="slum") {
						$multi -= e_rand(getsetting("multislummin", 0),getsetting("multislummax", 1));
						if (e_rand(0,1)) {
							$mintargetlevel = $targetlevel - 1;
						} else {
							$mintargetlevel = $targetlevel - 2;
						}
					} else if ($type == "thrill") {
						$multi += e_rand(getsetting("multithrillmin", 1),getsetting("multithrillmax", 2));
						if (e_rand(0,1)) {
							$targetlevel++;
							$mintargetlevel = $targetlevel - 1;
						} else {
							$mintargetlevel = $targetlevel-1;
						}
					} else if ($type == "suicide") {
						$multi += e_rand(getsetting("multisuimin", 2),getsetting("multisuimax", 4));
						if (e_rand(0,1)) {
							$mintargetlevel = $targetlevel - 1;
						} else {
							$targetlevel++;
							$mintargetlevel = $targetlevel - 1;
						}
					}
					$multi = min($multi, $session['user']['level']);
				}
			} else {
				$multi = 1;
			}
			$multi = max(1, $multi);
			if ($targetlevel<1) $targetlevel=1;
			if ($mintargetlevel<1) $mintargetlevel=1;
			if ($mintargetlevel > $targetlevel) $mintargetlevel = $targetlevel;
			if ($targetlevel>17) {
				$multi += $targetlevel - 17;
				$targetlevel=17;
			}
// 			debug("Creatures: $multi Targetlevel: $targetlevel Mintargetlevel: $mintargetlevel");
			if ($multi > 1) {
				$packofmonsters = (bool)(e_rand(0,5) == 0 && getsetting("allowpackofmonsters", true)); // true or false
				switch($packofmonsters) {
					case false:
						$sql = "SELECT * FROM " . db_prefix("creatures") . " WHERE creaturelevel <= $targetlevel AND creaturelevel >= 0 AND forest=1 ORDER BY rand(".e_rand().") LIMIT $multi";
						break;
					case true:
						$sql = "SELECT * FROM " . db_prefix("creatures") . " WHERE creaturelevel <= $targetlevel AND creaturelevel >= 0 AND forest=1 ORDER BY rand(".e_rand().") LIMIT 1";
						break;
				}
			} else {
				$sql = "SELECT * FROM " . db_prefix("creatures") . " WHERE creaturelevel <= $targetlevel AND creaturelevel >= 0 AND forest=1 ORDER BY rand(".e_rand().") LIMIT 1";
				$packofmonsters = 0;
			}
			$result = db_query($sql);
			restore_buff_fields();
			if (db_num_rows($result) == 0) {
				// There is nothing in the database to challenge you, let's
				// give you a doppleganger.
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
				$stack[] = $badguy;
			} else {
				require_once("lib/forestoutcomes.php");
				if ($packofmonsters == true) {
					$initialbadguy = db_fetch_assoc($result);
					$prefixs = array("Elite","Dangerous","Lethal","Savage","Deadly","Malevolent","Malignant");
					for($i=0;$i<$multi;$i++) {
						$initialbadguy['creaturelevel'] = e_rand(0, $targetlevel);
						$initialbadguy['playerstarthp']=$session['user']['hitpoints'];
						$initialbadguy['diddamage']=0;
						$badguy = buffbadguy($initialbadguy);
						if ($type == "thrill") {
							// 10% more experience
							$badguy['creatureexp'] = round($badguy['creatureexp']*1.1, 0);
							// 10% more gold
							$badguy['creaturegold'] = round($badguy['creaturegold']*1.1, 0);
						}
						if ($type == "suicide") {
							// Okay, suicide fights give even more rewards, but
							// are much harder
							// 25% more experience
							$badguy['creatureexp'] = round($badguy['creatureexp']*1.25, 0);
							// 25% more gold
							$badguy['creaturegold'] = round($badguy['creaturegold']*1.25, 0);
							// Now, make it tougher.
							$mul = 1.25 + $extrabuff;
							$badguy['creatureattack'] = round($badguy['creatureattack']*$mul, 0);
							$badguy['creaturedefense'] = round($badguy['creaturedefense']*$mul, 0);
							$badguy['creaturehealth'] = round($badguy['creaturehealth']*($session['user']['level']/$badguy['creaturelevel']), 0);
							// And mark it as an 'elite' troop.
							$prefixs = translate_inline($prefixs);
							$key = array_rand($prefixs);
							$prefix = $prefixs[$key];
							$badguy['creaturename'] = $prefix . " " . $badguy['creaturename'];
						}
						$stack[$i] = $badguy;
					}
					if ($multi > 1) {
						output("`2You encounter a group of `^%i`2 %s`2.`n`n", $multi, $badguy['creaturename']);
					}
				} else {
					while ($badguy = db_fetch_assoc($result)) {
						$badguy['playerstarthp']=$session['user']['hitpoints'];
						$badguy['diddamage']=0;
						$badguy = buffbadguy($badguy);
						// Okay, they are thrillseeking, let's give them a bit extra
						// exp and gold.
						if ($type == "thrill") {
							// 10% more experience
							$badguy['creatureexp'] = round($badguy['creatureexp']*1.1, 0);
							// 10% more gold
							$badguy['creaturegold'] = round($badguy['creaturegold']*1.1, 0);
						}
						if ($type == "suicide") {
							// Okay, suicide fights give even more rewards, but
							// are much harder
							// 25% more experience
							$badguy['creatureexp'] = round($badguy['creatureexp']*1.25, 0);
							// 25% more gold
							$badguy['creaturegold'] = round($badguy['creaturegold']*1.25, 0);
							// Now, make it tougher.
							$mul = 1 + $extrabuff;
							$badguy['creatureattack'] = round($badguy['creatureattack']*$mul, 0);
							$badguy['creaturedefense'] = round($badguy['creaturedefense']*$mul, 0);
							$badguy['creaturehealth'] = round($badguy['creaturehealth']*$mul, 0);
							// And mark it as an 'elite' troop.
							$prefixs = array("Elite","Dangerous","Lethal","Savage","Deadly","Malevolent","Malignant");
							$prefixs = translate_inline($prefixs);
							$key = array_rand($prefixs);
							$prefix = $prefixs[$key];
							$badguy['creaturename'] = $prefix . " " . $badguy['creaturename'];
						}
						$stack[] = $badguy;
					}
				}
			}
			calculate_buff_fields();
			$attackstack = array(
				"enemies"=>$stack,
				"options"=>array(
					"type"=>"forest"
				)
			);
			$session['user']['badguy']=createstring($attackstack);
			// If someone for any reason wanted to add a nav where the user cannot choose the number of rounds anymore
			// because they are already set in the nav itself, we need this here.
			// It will not break anything else. I hope.
			if(httpget('auto') != "") {
				httpset('op', 'fight');
				$op = 'fight';
			}
		}
	//}
}

if ($op=="fight" || $op=="run" || $op == "newtarget"){
	$battle=true;
}

if ($battle){

	require_once("battle.php");

	if ($victory){
		require_once("lib/forestoutcomes.php");
		$op="";
		httpset('op', "");
		forestvictory($newenemies,isset($options['denyflawless'])?$options['denyflawless']:false);
		$dontdisplayforestmessage=true;
	}elseif($defeat){
		require_once("lib/forestoutcomes.php");
		forestdefeat($newenemies);
	}else{
		fightnav();
	}
}

if ($op==""){
	// Need to pass the variable here so that we show the forest message
	// sometimes, but not others.
	forest($dontdisplayforestmessage);
}
page_footer();
?>
