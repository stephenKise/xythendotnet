<?php
	switch (httpget('type')){
		case "revive":
			if ($session['user']['deathpower'] >= get_module_setting("favor") && 
				$session['user']['gold'] >= get_module_setting("gold-revive")){
				output("`7MacCready looks to the sky and spreads his arms.");
				output("His eyes roll to the back of his skull and low murmers escape his throat.");
				output("Suddenly, your %s's body rises into thin air and a light pierces him.",$classarray[$class]);
				set_module_pref("dead",0);
				$session['user']['deathpower']-=get_module_setting("favor");
				$session['user']['gold']-=get_module_setting("gold-revive");
				output("`n`nMacCready looks at you, \"`&It is done...`7\"");
			}else{
				output(
                    "`7MacCready frowns, \"`&With not enough supplies," .
                    "I can't revive your mercenary" .
                    "Please come back when you have more supplies.`7\"");
			}
			break;
		case "level":
			// Sanity check
			if (get_module_pref("lsl") < get_module_setting("level")){
				output("`7MacCready looks stunned, \"`&I don't know who you are trying to fool... but your %s is not strong enough to face me.`7\"",$classarray[$class]);
			}else{
				$excesslevel = get_module_pref("lsl")-get_module_setting("level");
				set_module_pref("lsl",$excesslevel);
		
				increment_module_pref("level",1);
				output("`7MacCready shows you into a small room, that is highly decorated with little furnishing.");
				output("He pulls out his sword and looks at %s, \"`&Come at me!`7\"",$name);
				output("Your %s runs at MacCready and slashes him across the front.",$classarray[$class]);
				output("MacCready stands and pokes %s in the forehead, making him topple over.",$name);
				output("Your %s wipes the blood from his chin and strikes against MacCready once more.",$classarray[$class]);
				output("Amazingly, he is able to strike MacCready down!");
				output("\"`&Very good, you just might survive yet.`7\"");
				output("`n`n%s gained a level!",$name);
				output("His attack has increased!");
			}
			break;
		case "acc":
			if (get_module_pref("acc") < get_module_setting("miss")){
				output("`7\"`&So, you would like to train your %s's accuracy?`7\"",$classarray[$class]);
				output(
                    "`7MacCready takes the two of you to the shooting range." . 
                    "Your mercenary is assigned a lane and practice weapon.");

				output("\"`&Now, %s, `&let's see how well you can shoot the targets. Focus.\"`n`n",$name);
				output("`7You can see that %s is straining to keep on the target.",$name);
				output(
                    "Your mercenary takes several shots at the moving target" . 
                    "The more successful your mercenary is the faster the". 
                    " targets move.");
				switch(e_rand(1,4)){
					case 1: case 2: case 3:
						output(
                            "MacCready seems pleased when your merchenary" .
                            " is able to hit the center.");
						output("\"`&Very good job...`7\"");
						increment_module_pref("acc",1);
						break;
					case 4:
						output("`7You can see that %s is unable to focus on the target.",$name);
                        output("MacCready stops the targets and begins berating" . 
                            " your mercenary for poor ability. He sighs, resigned" .
                            " You leave the shooting range for another day," .
                            " understanding that practice makes perfect.");
						break;
					}
				set_module_pref("tacc",1);
			}else{
				output("`7MacCready observes %s.",$name);
				output("\"`&They're the best shot around, what more do you want?`7\"");
			}
			break;
		case "rename":
			$newname = httppost('name');
			$set = translate_inline("Set Name");
			if ($session['user']['gold'] >= get_module_setting("re")){
				if ($newname ==	""){
					output("`7MacCready walks forward, looking at you.");
					output("\"`&Let's get this over with.");
					output("Write the name you want here, and I shall transfer it over.`7\"`n`n");
					rawoutput("<form action='runmodule.php?module=academy&op=train&type=rename' method='post'>");
					rawoutput("<input name='name' size='20'>");
					rawoutput("<input type='submit' class='button' value='$set'></form>");
				}else{
					output("`7\"`&There we go, your %s has been renamed to %s`&.`7\"",$classarray[$class],$newname);
					set_module_pref("name",$newname."`0");
					$session['user']['gold']-=get_module_setting("re");
				}
			}else{
				output("`7\"`&What do you think this is, a free clinic?");
				output("Get out of here before I rend your limbs from your body.`7\"");
			}
			addnav("","runmodule.php?module=academy&op=train&type=rename");
			break;
		case "upgrade":
			$advance = httpget('ad');
			$current = $classarray[$class];
			$next = $classarray[$class+1];
			// Sanity Check
			if (get_module_pref("level") != $max[$class]){
				output("`7\"`&I have no idea how you got in here, but I want you out!`7\"");
			}else{
				if ($advance == ""){
					output("`7You approach MacCready, your %s at your side.",$current);
					if($class == 2){
						output("`7MacCready shakes his head, \"`&I am sorry, but %s `&is as strong as he will ever be.`7\"",$name);
					}else{
						output("`7MacCready looks at your %s, \"`&Ah... so I see that %s would like to train for a new class.",$current,$name);
						output("You do know that class would happen to be %s and is far more powerful than your %s class?`7\"",$next,$current);
						output("`n`nYou nod in agreement.");
						output("\"`&So then, let's get started...`7\"");
						addnav(array("Upgrade to %s",$next),"runmodule.php?module=academy&op=train&type=upgrade&ad=yes");
					}
				}elseif($advance == "yes"){
					$class++;
					set_module_pref("class",$class);
					set_module_pref("lsl",0);
					set_module_pref("level",0);
					output("`7%s heads into another room with MacCready.",$name);
					output("You hear clanging of weapons and the breaking of skin.");
					output("A cloth rips and you hear liquid hit the floor.");
					output("Hours later, he returns with a heavily inked tattoo on his arm.");
					output("\"`&Meet your new %s.`7\"",$next);
				}
			}
			break;
		}
?>