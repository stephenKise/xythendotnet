<?php
	addnav("Training Hall");
	if (!$active){
		output(
            "MacCready gestures to the left side of the room," . 
            "\"`&Our high level mercenaries are currently already spoken for," .
            " but I have a few contracts for some of our newer mercs." .
            " Now, I know what you're thinking... Don't worry. They'll get" .
            " the job done as long as they're paid in the end.\"");
		addnav("Buy Contract","runmodule.php?module=academy&op=buy");
	}else{
		output(
            "`7MacCready looks at you, \"`&You can rename your mercenary for" .
            " a small fee of `^%s `&gold.`6\"",get_module_setting("re"));
		addnav(array("Rename %s",$name),"runmodule.php?module=academy&op=train&type=rename");
        if ($dead){
			output(
                "It's customary for the contract dealer to do the revival," .
                "Let's get your %s all fixed up.\"",$classarray[$class]);
            output(
                "MacCready takes the resurrection stone and slaps it down on" .
                " on the table where you set the dead body of your follower" . 
                " after a bit of unorthodox chanting from MacCready, your" .
                " mercenary groggily opens their eyes...");
            if (get_module_setting("favor") > 0 && get_module_setting("gold-revive") <= 0){
				output("It will cost `\$%s `&favor.`6\"",get_module_setting("favor"));
				addnav(array("Revive %s (%s Favor)",$classarray[$class],	get_module_setting("favor")),
					"runmodule.php?module=academy&op=train&type=revive");
			}elseif (get_module_setting("favor") <= 0 && get_module_setting("gold-revive") > 0){
				output("It will cost `\$%s `&Gold.`6\"",get_module_setting("gold-revive"));
				addnav(array("Revive %s (%s Gold)",$classarray[$class],	get_module_setting("gold-revive")),
					"runmodule.php?module=academy&op=train&type=revive");
			}else{
				output("It will cost `\$%s `&Favor and `\$%s `&Gold.`6\"",
					get_module_setting("favor"), get_module_Setting("gold-revive"));
				addnav(array("Revive %s (%s Favor and %s Gold)",$classarray[$class],
					get_module_setting("favor"),get_module_setting("gold-revive")),
					"runmodule.php?module=academy&op=train&type=revive");
			}
		}
		if (get_module_pref("lsl") >= get_module_setting("level") && !$dead && get_module_pref("level") < get_module_setting("max")){
			addnav(array("Train %s", $classarray[$class]),"runmodule.php?module=academy&op=train&type=level");
			output("`n`n`6Dycedarg smiles, \"`&So, I see that %s `&has grown quite strong.",$name);
			output("`&I shall allow him to level, IF he can best me in battle.");
			output("`&Would you care to submit him into battle?`6\"");					
		}
		if (get_module_pref("level") == $max[$class] && !$dead){
			output("`n`n`6\"`&Splendid, simply splendid!");
			output("If you come with me, we shall make your servant much better!`6\"");
			addnav("Advance Class","runmodule.php?module=academy&op=train&type=upgrade");
		}
		if (!get_module_pref("tacc") && !$dead && get_module_pref("acc") < get_module_setting("miss")){
			output("`n`n`6\"`&I see... so, you would like to train your servant's accuracy today.");
			output("Very well then...`6\"");
			addnav("Train Accuracy","runmodule.php?module=academy&op=train&type=acc");
		}
	}
?>