<?php

function check_flags_getmoduleinfo(){
	$info = array(
		"name"=>"Check Superuser Flags",
		"author"=>"Chris Vorndran",
		"version"=>"1.01",
		"category"=>"Administrative",
		"download"=>"http://dragonprime.net/index.php?module=Downloads;sa=dlview;id=51",
		"vertxtloc"=>"http://dragonprime.net/users/Sichae/",
	);
	return $info;
}
function check_flags_install(){
	module_addhook("superuser");
	return true;
}
function check_flags_uninstall(){
	return true;
}
function check_flags_dohook($hookname,$args){
	global $session;
	switch ($hookname){
		case "superuser":
			if ($session['user']['superuser'] & SU_MEGAUSER){
				addnav("Actions");
				addnav("Check SU Flags","runmodule.php?module=check_flags&op=enter");
			}
			break;
		}
	return $args;
}
function check_flags_run(){
	global $session;
	$op = httpget('op');
	page_header("Check Superuser Flags");
	
	switch ($op){
		case "enter":
			output("`c`b`^Here you will be able to check who has which Superuser Flag.`b`c`0`n");
			rawoutput("<form action='runmodule.php?module=check_flags&op=select' method='POST'>");
			rawoutput("<table align='center'><tr><td>");
			$su_flags = array(
							SU_MEGAUSER=>"Megauser",
							SU_EDIT_CONFIG=>"Edit Configuration",
							SU_EDIT_USERS=>"Edit Users",
							SU_EDIT_MOUNTS=>"Edit Mounts",
							SU_EDIT_CREATURES=>"Edit Creatures & Taunts",
							SU_EDIT_EQUIPMENT=>"Edit Armor & Weapons",
							SU_EDIT_RIDDLES=>"Edit Riddles",
							SU_MANAGE_MODULES=>"Manage Modules",
							SU_EDIT_PETITIONS=>"Check Petitions",
							SU_EDIT_COMMENTS=>"Comment Moderation",
							SU_MODERATE_CLANS=>"Moderate Clan Commentary",
							SU_AUDIT_MODERATION=>"Audit Moderated Comments",
							SU_OVERRIDE_YOM_WARNING=>"Give YOM Warning",
							SU_POST_MOTD=>"Post MotDs",
							SU_EDIT_DONATIONS=>"Edit Donations",
							SU_EDIT_PAYLOG=>"Edit Paylog",
							SU_INFINITE_DAYS=>"Infintite Days",
							SU_DEVELOPER=>"Developer",
							SU_IS_TRANSLATOR=>"Enable Translator Tool",
							SU_DEBUG_OUTPUT=>"View Debug Output",
							SU_SHOW_PHPNOTICE=>"Show PHP Notice",
							SU_RAW_SQL=>"Run Raw SQL",
							SU_VIEW_SOURCE=>"View PHP Source",
							SU_GIVE_GROTTO=>"Grotto Access",
							SU_NEVER_EXPIRE=>"Account Never Expires",
						);
			rawoutput("<select name='flag'>");
			foreach($su_flags AS $priv => $name){
				rawoutput("<option value='$priv|$name'>$name</option>");
			}
			rawoutput("</select>");
			$submit = translate_inline("Choose");
			rawoutput("</td><td><input type='submit' class='button' value='$submit'></td>");
			rawoutput("</tr></table></form>");
			addnav("","runmodule.php?module=check_flags&op=select");
			break;
		case "select":
			$flag = explode("|",httppost('flag'));
			$sql = "SELECT name,superuser FROM ".db_prefix("accounts")." WHERE (superuser & {$flag[0]}) != 0 ORDER BY acctid ASC";
			$res = db_query($sql);
			$name = translate_inline("Name");
			$su = translate_inline("Superuser");
			output("`c`b`^Currently Display all Characters with the SU Flag: %s.`c`b`n`0",$flag[1]);
			rawoutput("<table border='0' cellpadding='2' cellspacing='1' align='center' bgcolor='#999999'>");
			rawoutput("<tr class='trhead'><td>$name</td><td>$su</td></tr>");
			$i = 0;
			while($row = db_fetch_assoc($res)){
				$i++;
				if ($row['name']==$session['user']['name']){
					rawoutput("<tr class='trhilight'><td>");
				} else {
					rawoutput("<tr class='".($i%2?"trdark":"trlight")."'><td>");
				}
				output_notl("`&%s`0",$row['name']);
				rawoutput("</td><td>");
				output_notl("`c`@%s`c`0",$row['superuser']);
				rawoutput("</td></tr>");
			}
			rawoutput("</table>");
			addnav("Check Another Flag","runmodule.php?module=check_flags&op=enter");
			break;
		}
	require_once("lib/superusernav.php");
	superusernav();
	page_footer();
}
?>			