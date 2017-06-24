<?php

function simpleweather_getmoduleinfo(){
	$info = array(
		"name"=>"Simple Weather",
		"author"=>"`&`bStephen Kise`b",
		"category"=>"Village",
		"download"=>"nope",
		"version"=>"0.1b",
		"settings"=>array(
			"Simple Weather Message Settings,title",
			"`Q`bUsage`b:`n`^1. Place a ~ between each weather scenario`n`\$2. ONLY PUT FOUR SCENARIOS!,note",
			"winter"=>"Messages to display for the season of Winter:,textarearesizeable",
			"spring"=>"Messages to display for the season of Spring:,textarearesizeable",
			"summer"=>"Messages to display for the season of Summer:,textarearesizeable",
			"fall"=>"Messages to display for the season of Fall:,textarearesizeable",
			"override"=>"Currently overriden message >,viewonly",
			"Simple Weather Other Settings,title",
			"has_changed"=>"When was the weather last reset?,int",
			"e_rand"=>"Roll?:,int",
			)
		);
	return $info;
}

function simpleweather_install(){
	module_addhook("simpleweather");
	module_addhook("newday");
	return TRUE;
}

function simpleweather_uninstall(){
	return TRUE;
}

function simpleweather_dohook($hook,$args){
	switch($hook){
		case "simpleweather":
			global $session;
			$details = gametimedetails();
			$secstonewday = secondstonextgameday($details);
			$hellaseasons = array("Dec"=>"winter", "Jan"=>"winter", "Feb"=>"winter", "Mar"=>"spring", "Apr"=>"spring", "May"=>"spring", "Jun"=>"summer", "Jul"=>"summer", "Aug"=>"summer", "Sept"=>"fall", "Oct"=>"fall", "Nov"=>"fall");
			foreach($hellaseasons as $key => $val){
				if (date('M')==$key) $weather = explode('~',get_module_setting($val));
				if (date('M')==$key) $season = $val;
			} 
			output("<tr><td align='center'>".date("h:i:s A", time())."</td><td align='center'>".ucfirst($season)."</td><td align='center'>".ucfirst($weather[get_module_setting('e_rand')])."</td></tr>",true);
//			output("`^Server Time: ".date("h:i:s A", time())."`n");
//			output("`^Next Game Day: ".date("i\\m s\\s",$secstonewday)."`n");
//			output("`^Current Season: ".ucfirst($season)."`n");
//			output("`^Current Weather: ".ucfirst($weather[get_module_setting('e_rand')])."`n`n");
		break;
		case "newday":
			if (date('d') != get_module_setting('has_changed')){
				set_module_setting('has_changed',date('d'));
				set_module_setting('e_rand',floor(e_rand(1,4)-1));
			}			
		break;
	}
	return $args;
}

function simpleweather_run(){
}


?>