<?php
function logoutcharstat_getmoduleinfo(){
	$info = array(
		"name"=>"Log Out in Charstats",
		"version"=>"1.0",
		"author"=>"`&`bStephen Kise`b",
		"category"=>"Stat Display",
		"settings"=>array(
			"Log Out in Charstats,title",
			"You must comment out the addnav for login.php in village.php,note"
			)
 );
	return $info;
}

function logoutcharstat_install(){
	module_addhook("charstats");
    return true;
}

function logoutcharstat_uninstall(){
	return true;
}


function logoutcharstat_dohook($hookname,$args){
	global $session, $SCRIPT_NAME;
    	switch($hookname){
		case "charstats":
			if ($SCRIPT_NAME == "village.php"){
				setcharstat("Vital Info", "Leaving?", "<a href='login.php?op=logout'>`&`bLog out`b</a>");
				addnav("","login.php?op=logout",true);
			}
		break;
	}
	return $args;
}

function logoutcharstat_run(){

}
?>
