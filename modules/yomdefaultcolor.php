<?php

function yomdefaultcolor_getmoduleinfo(){

	$info = array(
	"name"=>"YoM Default Colors",
	"category"=>"Mail",
	"version"=>"1.0",
	"author"=>"`b`&Xpert`b",
	"prefs"=>array(
	//"Default Colors,title",
	//"user_yomcolor"=>"What default color do you wish to use in YoMs?,text|",
	),
		);
	return $info;
}

function yomdefaultcolor_install(){
return TRUE;
}

function yomdefaultcolor_uninstall(){
return TRUE;
}

function yomdefaultcolor_dohook(){
}

?>
	