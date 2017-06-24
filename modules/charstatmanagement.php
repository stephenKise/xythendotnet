<?php
function charstatmanagement_getmoduleinfo(){
	$info = array(
		"name"=>"Charstat Management",
		"version"=>"0.1b",
		"author"=>"`&`bStephen Kise`b",
		"category"=>"Stat Display",
		"download"=>"nope",
		"prefs"=>array(
			"Charstat Prefs,title",
			"user_clanexp"=>"Do you want to display your clan's level and experience?,bool|1",
			"Please note that this module is still in the building process and is in `i`bbeta`b`i!",
		),
	);

	return $info;
}

function charstatmanagement_install(){
	return true;
}

function charstatmanagement_uninstall(){
	return true;
}

function charstatmanagement_dohook($hookname,$args){
	return $args;
}
?>