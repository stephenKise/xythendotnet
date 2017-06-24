<?php

function blackplague_getmoduleinfo(){
	$info = array(
		"name"=>"Black Plague",
		"author"=>"Maverick",
		"version"=>"1.0b",
		"category"=>"Forest Specials",
		"prefs"=>array(
			"plague_stage"=>"What stage is this user in?,int|0",
			"turns_left"=>"How mant turns does this user have left before the plague can haunt them again?,int|0",
			"purchased"=>"How many items have been purchased from the apothecary?,int|0",
			)
		);
	return $info;
}

function blackplague_install(){
	global $session;
	(get_module_pref("turns_left","blackplague",$session['user']['acctid']) > 0) ? $chance = 0 : $chance = 5;
	module_addeventhook("forest", "return $chance;");
	module_addhook("village");
	module_addhook("battle-victory");
	module_addhook("newday");
	return TRUE;
}

function blackplague_uninstall(){
	return TRUE;
}

function blackplague_dohook($hook,$args){
	switch($hook){
		case "village":
			if (get_module_pref("plague_stage")==4) blocknav("forest.php");
			addnav($args['marketnav']);
			addnav('`b`4X`b`i`)y`7t`&hen`i `b`4C`b`i`)l`7i`&nic`i','runmodule.php?module=blackplague&op=enter');
		break;
		case "battle-victory":
			$sub = get_module_pref("turns_left")-1;
			if (get_module_pref("turns_left") > 0) set_module_pref("turns_left",$sub,"blackplague");
		break;
		case "newday":
			set_module_pref("purchased",0);
		break;
	}
	return $args;
}

function blackplague_runevent($type){
	global $session;
	$stage = get_module_pref("plague_stage")+1;
	switch($stage){
		case 1:
			output('`4While travelling in the forest you find yourself feeling rather feverish. As your hand floods to your forehead, you realize that you are burning up with a higher temperature thatn normal.`n`n`$ You better get that checked! That is one of the first signs of the Plague.... ');
			apply_buff("plague",array(
				"name"=>"`7Plague: Fever`0",
				"atkmod"=>0.8,
	 			"defmod"=>0.8,
				"allowintrain"=>1,
				"rounds"=>150,
				"schema"=>"module-blackplague",
				)
			);
			set_module_pref("plague_stage",$stage,"blackplague");
		break;
		case 2:
			output('`4While travelling in the forest, your fever dragging you down, your limbs start to feel as though they are limp noodles.`n`n`$ You better get to the Plague Doctor before your symptoms get worse! If your muscles can carry you there, and your brain can cooperate with such a high fever, that is...');
			apply_buff("plague",array(
				"name"=>"`7Plague: Limb Weakness`0",
				"atkmod"=>0.65,
	 			"defmod"=>0.65,
				"allowintrain"=>1,
				"rounds"=>160,
				"schema"=>"module-blackplague",
				)
			);
			set_module_pref("plague_stage",$stage,"blackplague");
		break;
		case 3:
			output('`4Travelling in the forest has become a burden to you; your fever growing higher and higher each sluggish step that you take... Your tender limbs ache as you continue forward, before you pull your arm up to wipe your brow. It is then that you notice your skin becoming covered in a painful blemish. You have fallen gravely ill, and need to seek a doctor before you can fight again or you will be instantly killed.');
			apply_buff("plague",array(
				"name"=>"`7Plague: Boils`0",
				"atkmod"=>0.45,
	 			"defmod"=>0.45,
				"allowintrain"=>1,
				"rounds"=>170,
				"schema"=>"module-blackplague",
				)
			);
			set_module_pref("plague_stage",$stage,"blackplague");
		break;
		case 4:
			output("Death is upon you!");
			apply_buff("plague",array(
				"name"=>"`7Plague: Death0",
				"atkmod"=>0.1,
	 			"defmod"=>0.1,
				"allowintrain"=>1,
				"rounds"=>100,
				"schema"=>"module-blackplague",
				)
			);
			set_module_pref("plague_stage",$stage,"blackplague");
		break;
	}
}

function blackplague_run(){
	global $session;
	$op = httpget('op');
	$what = httpget('what');
	$used_tonic = get_module_pref('used_tonic');
	$gold_cost = 100+($session['user']['dragonkills']*2);
	$gem_cost = ceil($session['user']['dragonkills']/10);
	$purchased = get_module_pref('purchased');
	page_header("`b`4X`b`i`)y`7t`&hen`i `b`4C`b`i`)l`7i`&nic`i");
	output("`c`b`4X`b`i`)y`7t`&hen`i `b`4C`b`i`)l`7i`&nic`i`n A marvel of modern medicine where all ails can be treated and helpful items may be purchased to aid one with stamina and energy. At first glance, the building seems old and run down. Broken, cracked brick steps lead up to double white doors, which begin a theme of pristine white walls doors, floors... everything is bright, white and utterly spotless. The sterile environment plays a huge role in keeping viruses in check. Immediately to the right is a glass window set in one of those many bright walls. Beyond the glass, a nurse rifles through paperwork before glancing upwards with a smile and a polite greeting, welcoming you and asking what she may do to assist you today. On the counter, you notice a list of over-the-counter products you may purchase - you decide to browse these.`c`n`n",true);
	switch($op){
		case "enter":
			addnav('Actions');
			addnav(array('Return to %s',$session['user']['location']),'village.php');
			addnav('Purchase');
			addnav(array('Buy a Tonic`n(%s gold, %s gems)',$gold_cost,$gem_cost),'runmodule.php?module=blackplague&op=buy&what=tonic');
			addnav('Medicine');
			addnav(array('Buy a Remedy`n(%s gold, %s gems)',$gold_cost,$gem_cost),'runmodule.php?module=blackplague&op=buy&what=remedy');
			addnav(array('Buy a Antidote`n(%s gold, %s gems)',$gold_cost,$gem_cost),'runmodule.php?module=blackplague&op=buy&what=antidote');
			addnav(array('Buy a Antitoxin`n(%s gold, %s gems)',$gold_cost,$gem_cost),'runmodule.php?module=blackplague&op=buy&what=antitoxin');
			addnav(array('Buy a Anti-Inflammatory`n(%s gold, %s gems)',$gold_cost,$gem_cost),'runmodule.php?module=blackplague&op=buy&what=antiinflammatory');
			addnav("Other Medicine");
			addnav(array('Buy Protein`n(%s gold, %s gems)',$gold_cost,$gem_cost),'runmodule.php?module=blackplague&op=buy&what=protein');
			addnav(array('Buy Iron`n(%s gold, %s gems)',$gold_cost,$gem_cost),'runmodule.php?module=blackplague&op=buy&what=iron');
			addnav(array('Buy Vitamins`n(%s gold, %s gems)',$gold_cost,$gem_cost),'runmodule.php?module=blackplague&op=buy&what=vitamins');
			addnav(array('Buy Energy Tablets`n(%s gold, %s gems)',$gold_cost,$gem_cost),'runmodule.php?module=blackplague&op=buy&what=energytablets');
		require_once("lib/commentary.php");
addcommentary();
viewcommentary("apothrocary", "Speak with those gathered,", 20, "says");
			break;
		case "buy":
			addnav('Actions');
			addnav('Return to the Clinic','runmodule.php?module=blackplague&op=enter');
			addnav(array('Return to %s',$session['user']['location']),'village.php');
			if ($purchased >= 3){
				output("You have purchased too many items here today.");
			}else{
				switch($what){
					case "tonic":
						if ($session['user']['gold'] < $gold_cost || $session['user']['gems'] < $gem_cost){
							output("Sorry, you do not have enough gold or gems to purchase a tonic.`nYou need ".$gold_cost." gold and ".$gem_cost." gems!");
						}else{
							output("You have purchased a tonic! You have been cured of the plague and you will be less likely to encounter the plague in the forest for 1500 turns!");
							set_module_pref("plague_stage",0);
							set_module_pref("turns_left",1500);
							strip_buff("plague");
							sell_item();
						}
					break;
					case "remedy":
						if ($session['user']['gold'] < $gold_cost || $session['user']['gems'] < $gem_cost){
							output("Sorry, you do not have enough gold or gems to purchase a remedy.`nYou need ".$gold_cost." gold and ".$gem_cost." gems!");
						}else{
							output("You have purchased a remedy, mending minor aches and pains.");
							$session['user']['hitpoints']+=100;
							sell_item();
						}
					break;
					case "antidote":
						if ($session['user']['gold'] < $gold_cost || $session['user']['gems'] < $gem_cost){
							output("Sorry, you do not have enough gold or gems to purchase a antidote.`nYou need ".$gold_cost." gold and ".$gem_cost." gems!");
						}else{
							output("You have purchased an antidote, combating chemical-related injuries.");
							$session['user']['hitpoints']+=100;
							sell_item();
						}
					break;
					case "antitoxin":
						if ($session['user']['gold'] < $gold_cost || $session['user']['gems'] < $gem_cost){
							output("Sorry, you do not have enough gold or gems to purchase a anti toxin.`nYou need ".$gold_cost." gold and ".$gem_cost." gems!");
						}else{
							output("You have purchased an antitoxin, alleviating any infections caused by poison, pollution, and other toxins.");
							set_module_pref("plague_stage",(get_module_pref("plague_stage")-1));
							sell_item();
						}
					break;
					case "antiinflammatory":
						if ($session['user']['gold'] < $gold_cost || $session['user']['gems'] < $gem_cost){
							output("Sorry, you do not have enough gold or gems to purchase a anti-inflammatory.`nYou need ".$gold_cost." gold and ".$gem_cost." gems!");
						}else{
							output("You have purchased an Anti-Inflammatory, soothing swelling caused by stings, infections or other injuries.");
							set_module_pref("plague_stage",(get_module_pref("plague_stage")-1));
							$session['user']['hitpoints']+=100;
							sell_item();
						}
					break;
					case "protein":
						if ($session['user']['gold'] < $gold_cost || $session['user']['gems'] < $gem_cost){
							output("Sorry, you do not have enough gold or gems to purchase protein.`nYou need ".$gold_cost." gold and ".$gem_cost." gems!");
						}else{
							output("You have purchased Protein, giving you a boost of strength!");
							$session['user']['attack']+=20;
							sell_item();
						}
					break;
					case "iron":
						if ($session['user']['gold'] < $gold_cost || $session['user']['gems'] < $gem_cost){
							output("Sorry, you do not have enough gold or gems to purchase iron.`nYou need ".$gold_cost." gold and ".$gem_cost." gems!");
						}else{
							output("You have purchased Iron, giving you heightened feelings of being invincible.");
							$session['user']['defense']+=20;
							sell_item();
						}
					break;
					case "vitamins":
						if ($session['user']['gold'] < $gold_cost || $session['user']['gems'] < $gem_cost){
							output("Sorry, you do not have enough gold or gems to purchase vitamins.`nYou need ".$gold_cost." gold and ".$gem_cost." gems!");
						}else{
							output("You have purchased some Vitamins, giving you an all around health boost!");
							$session['user']['attack']+=20;
							$session['user']['defense']+=20;
							$session['user']['hitpoints']+=100;
							sell_item();
						}
					break;
					case "energytablets":
						if ($session['user']['gold'] < $gold_cost || $session['user']['gems'] < $gem_cost){
							output("Sorry, you do not have enough gold or gems to purchase energy tablets.`nYou need ".$gold_cost." gold and ".$gem_cost." gems!");
						}else{
							output("You have purchased Energy Tablets, giving you a boost of energy!");
							$session['user']['turns']+=20;
							sell_item();
						}
					break;
				}
			}
			if (get_module_pref("plague_stage")<0) set_module_pref("plague_stage",0);
		break;
	}
	page_footer();
}	

function sell_item(){
	global $session;
	$gold_cost = 100+($session['user']['dragonkills']*2);
	$gem_cost = ceil($session['user']['dragonkills']/10);
	$session['user']['gold']-=$gold_cost;
	$session['user']['gems']-=$gem_cost;
	increment_module_pref("purchased");
	debug(get_module_pref("purchased"));
}	
				
?>