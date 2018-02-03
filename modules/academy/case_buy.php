<?php
	if ($session['user']['gems'] >= get_module_setting("cost")){
		$names = array();
		$names = explode(",",get_module_setting("names"));
		$i = e_rand(1,count($names));
		$name = $names[$i];
		if ($name == "") $name = translate_inline("Useless Soldier");
		set_module_pref("name",$name);
		set_module_pref("active",1);
		set_module_pref("dead",0);
		set_module_pref("acc",65);
		output(
			"`7MacCready moves into the pub to grab the most sober humanoid" .
			" for hire possible. He brings the person towards you, seeming" .
			" to be giving the person a pep-talk of sorts before getting" .
			" in ear shot...");
		output("\"`&This one's name is %s. `&Say hello to your new owner...`7\"",$name);
		output("Your new companion takes their place at your side.");
		$session['user']['gems']-=get_module_setting("cost");
	}else{
		output("`7MacCready snatches you from the scruff of the neck and tosses you to the ground.");
		output("\"`&We don't take kindly to folks who don't pay their dues.`7\"");
	}
?>