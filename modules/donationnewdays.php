<?php

function donationnewdays_getmoduleinfo(){
$info = array(
	"name"=>"Donation Incentive - Newdays",
	"version"=>"1.0",
	"author"=>"`b`&Dexter`b",
	"category"=>"Lodge",
	"prefs"=>array(
		"Donation Incentive - Infinite Newdays,title",
		"turnton"=>"Does this user have the infinite newday button?,bool|",
		"`&Make sure to set the above to yes when giving the newday button.,note",
		"expirewhen"=>"When does this user's newday button get removed?,text|0",
		"`&Format the above like this: MM/DD,note",
		"`&Do not forget to add on to this time if the user donates more than once.,note",
		),
	);
    return $info;
}

function donationnewdays_install(){
	module_addhook("shades");
	module_addhook("village");
	return TRUE;
}

function donationnewdays_uninstall(){
	return TRUE;
}

function donationnewdays_dohook($hookname,$args){
	global $session;
	$expire = get_module_pref("expirewhen");
	$oper = get_module_pref("turnton");
	$date = date("m/d");
	switch($hookname){
		case "village":
		case "shades":
		if ($oper == 1){
			if ($expire === $date){
				set_module_pref("expirewhen","00/00","donationnewdays");
				set_module_pref("turnton",0,"donationnewdays");
				rawoutput("<big><center><font color='red'>Your Donation Incentive - Newday Button, has been REMOVED!</font></center></big>");
			}else{
				addnav("Other");
				addnav("`b`^Newday`b","newday.php");
			}
		}
		break;
	}
	return $args;
}

?>