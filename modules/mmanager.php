<?php
function mmanager_getmoduleinfo(){
	$info = array(
		"name"=>"Module Manager Links",
		"version"=>"1.0",
		"category"=>"Administrative",
		"author"=>"`i`b`~Sh`\$a`~dow`i`b",
		"download"=>"",
	);
	return $info;
}

function mmanager_install(){
	module_addhook("village");
	module_addhook("forest");
	module_addhook("shadoes");
	return true;
}

function mmanager_uninstall(){
	return true;
}

function mmanager_dohook($hookname, $args){
	global $session;
	switch ($hookname){
		case "village":
		case "shades":
			if ($session['user']['superuser'] & SU_MANAGE_MODULES){
				addnav("Superuser");
				addnav("1?`@Manage Modules","modules.php");
			}
		break;
		case "forest":
			if ($session['user']['superuser'] & SU_MANAGE_MODULES){
				addnav("Superuser Navs");
				addnav("`b`^Visit Grotto`b","superuser.php");
				addnav("1?`@Manage Modules","modules.php");
			}
		break;
	}
	return $args;
}
?>