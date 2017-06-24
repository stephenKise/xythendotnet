<?php
//This array is needed to run the prefs/settings/installs/hooks.
//Do not remove. If you have any bugs, let me know on DragonPrime.
//-Stephen

function textarearesize_getmoduleinfo(){
	$implement = array(
        "footer-prefs"=>"preferences", //untested.
        "blockcommentarea"=>"commentary",
        "footer-configuration"=>"game/module configuration", //untested
        "footer-user"=>"user editor", //untested
		"mailfunctions"=>"mail composition",
		"petitionform"=>"petitions",
		"footer-rawsql"=>"rawsql" //What I made this for in the first place.
        );
	$info = array(
		"name"=>"Textarea Resizing",
		"description"=>"Uses jQuery to automatically resize selected textarea tags based on preferences and settings.",
		"version"=>"1.0b",
		"author"=>"`&`bStephen Kise`b",
		"category"=>"General",
		"download"=>"http://dragonsource.info/download.php?cat=General&mod=textarearesize",
		"prefs"=>array(
			"Textarea Prefs,title",
			"This is for resizing textareas as you type within them. They will automatically grow based on how many lines you enter.,note"
		),
		"settings"=>array(
			"Textarea Prefs,title",
			"You can override where players/staff can resize their textarea - if you want. We are using google's most recent jQuery repository. If you do not want to use this - `bmake sure you install jquery on your templates.`b,note")
		);
		foreach($implement as $setting => $explanation)
		{
			$info["settings"][$setting] = "Should we allow players/staff to resize textareas in $explanation?,bool|1";
			$info["prefs"]["user_$setting"] = "Do you want to resize textareas in $explanation?,bool|1";
		}
	return $info;
}

function textarearesize_install(){
$implement = array(
        "footer-prefs"=>"preferences",
        "blockcommentarea"=>"commentary",
        "footer-configuration"=>"game/module configuration",
        "footer-user"=>"user editor",
		"mailfunctions"=>"mail composition",
		"petitionform"=>"petitions",
		"footer-rawsql"=>"rawsql"
        );
	foreach ($implement as $setting => $explanation)
	{
		module_addhook($setting);
	}
	return true;
}

function textarearesize_uninstall()
{
	output("Thanks for using the Textarea Resizing! If there was a bug you found, or something you did not like, let us know at <a href='http://dragonprime.net'>DragonPrime</a>!",true);
	addnav("","http://dragonprime.net");
	return true;
}


function textarearesize_dohook($hook, $args){
	global $template;                                             
			if (!strstr($template['header'],"jquery")) //Just checking if we have jquery installed...
				rawoutput("<script src=\"https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js\"></script>");
			if (get_module_setting($hook) == 1 && get_module_pref("user_$hook") == 1)
				rawoutput("<script type='text/javascript'>$(window).load(function(){jQuery.each(jQuery('textarea'), function() {var offset = this.offsetHeight - this.clientHeight;var resizeTextarea = function(el) {jQuery(el).css('height', 'auto').css('height', el.scrollHeight + offset);};jQuery(this).on('keyup input click mouseover', function() { resizeTextarea(this); });});});</script>");
	return $args;
}

?>