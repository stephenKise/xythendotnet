<?php
function fightarea_getmoduleinfo(){
	$info = array(
		"name"=>"RP Fight Area",
		"version"=>"1.0",
		"author"=>"`b`~Dr`)e`6z`^e`7l`)l`b, modified greatly by `i`)Ae`7ol`&us`i`0",
		"category"=>"General",
		"download"=>"nope",
		"settings"=>array(
			"Welcomeing Settings,title",
				"welcomebanner"=>"What should the Welcome Banner say?,text|Welcome to the Fighting Arena!",
				"welcomemsg"=>"What should the Welcome Message say?,text|`7If you wish to compete, please petition for access to a designated Arena.",
				"update"=>"Time (in hrs) to update descriptions,int|2",
				"allprefs"=>"Allprefs,text|a:0:{}",
				"`^`bDo not edit the above setting - except for manual resetting.`b`0,note",
				"`QClear the Allprefs setting if you wish to manually reset the descriptions.`0,note",
			"Descriptions 1-5,title",
				"desc1"=>"Description 1,textarea",
				"desc2"=>"Description 2,textarea",
				"desc3"=>"Description 3,textarea",
				"desc4"=>"Description 4,textarea",
				"desc5"=>"Description 5,textarea",
			"Descriptions 6-10,title",
				"desc6"=>"Description 6,textarea",
				"desc7"=>"Description 7,textarea",
				"desc8"=>"Description 8,textarea",
				"desc9"=>"Description 9,textarea",
				"desc10"=>"Description 10,textarea",
		),
		"prefs"=>array(
			"Fight Arena Prefs,title",
			"allowed"=>"Is this user allowed to visit the Arena?,bool|1",
		),
	);

	return $info;
}

function fightarea_install(){
	module_addhook("newday");
	module_addhook("village");
	module_addhook("moderate");
	return true;
}

function fightarea_uninstall(){
	return true;
}

function fightarea_dohook($hookname,$args){
	switch($hookname){
		case "newday":
			fightarea_check();
		break;
		case "village":
			global $session;
			if (get_module_pref("allowed") || $session['user']['superuser'] & SU_MODERATE_COMMENTS) {
				addnav($args['fightnav']);
				addnav("`\$R`4P `bB`b`ia`ittle `b`iA`i`brena","runmodule.php?module=fightarea");
			}
		break;
		case "moderate":
			$args['fightarea'] = "Battle Zones";
		break;
	}
	return $args;
}

function fightarea_run(){
	global $session;
	require_once("lib/commentary.php");
	require_once("lib/villagenav.php");
	
	fightarea_check();
	$op = httpget('op');
	$set = get_all_module_settings();
	$allprefs = unserialize($set['allprefs']);
	$ordinal = array("", "First", "Second", "Third", "Fourth");

	if ($op){
		$arena = (int)str_replace("fightarena", "", $op);
		page_header("{$ordinal[$arena]} Arena");
		addnav("Actions");
		addnav("Go Back", "runmodule.php?module=fightarea");

		if ($set['desc'.$allprefs[$arena]]) output("`n`n`c%s`c", $set['desc'.$allprefs[$arena]]);
		
		addcommentary();
		viewcommentary("fightarea".$arena,"Speak", 35, "says");
	} else {
		page_header("The Battle Zone");
		
		addnav("Arenas");
		addnav("First Arena","runmodule.php?module=fightarea&op=fightarena1");
		addnav("Second Arena","runmodule.php?module=fightarea&op=fightarena2");
		addnav("Third Arena","runmodule.php?module=fightarea&op=fightarena3");
		addnav("Fourth Arena","runmodule.php?module=fightarea&op=fightarena4");
		
		rawoutput("<font style='font-size: 14pt;'>");
		output("`n`c`b`^%s`c`b`n`n", $set['welcomebanner']);
		rawoutput("</font>");
		output("`c`n`n%s`n`n`n`c", $set['welcomemsg']);
	}
	
	villagenav();
	page_footer();
}

function fightarea_check(){
	$update = get_module_setting("update")*3600;
	$allprefs = unserialize(get_module_setting("allprefs"));
	if ((isset($allprefs[0]) && (time() - $allprefs[0] >= $update)) || !isset($allprefs[0])){
		fightarea_update();
	}
}

function fightarea_update(){
	$random = range(1, 10);
    shuffle($random);
    $allprefs = array_slice($random, 0, 4);
	array_unshift($allprefs, time());
	
	ksort($allprefs);
	set_module_setting("allprefs",serialize($allprefs));
}
?>