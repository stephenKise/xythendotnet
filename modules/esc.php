<?php

function esc_getmoduleinfo(){
	$info = array(
		"name"=>"Email String Creator",
		"author"=>"Chris Vorndran",
		"version"=>"1.0",
		"category"=>"Administrative",
		"download"=>"http://dragonprime.net/index.php?module=Downloads;sa=dlview;id=52",
		"vertxtloc"=>"http://dragonprime.net/users/Sichae/",
		"description"=>"Allows Megausers to create a string of Emails from everyone on the site, seperated by Commas, so that an Email Provider would allow for a Mass Messaging.",
		);
	return $info;
}
function esc_install(){
	module_addhook("superuser");
	return true;
}
function esc_uninstall(){
	return true;
}
function esc_dohook($hookname,$args){
	global $session;
	switch ($hookname){
		case "superuser":
			addnav("Actions");
			if ($session['user']['superuser'] & SU_MEGAUSER) addnav("Email String Creator","runmodule.php?module=esc&op=enter");
			break;
		}
	return $args;
}
function esc_run(){
	global $session;
	$op = httpget('op');
	$l = httppost('limit');
	$ch = httppost('change');
	page_header("Email String Creator");
	switch ($op){
		case "enter":
			output("`#This will allow you to generate a string of emails, seperated by commas.");
			output("In doing this, you will be able to send a mass email, as most Email providers accept multiple emails, that are in this type of format.");
			rawoutput("<form action='runmodule.php?module=esc&op=gen' method='POST'>");
			output("`n`@How do you want this listed:`n %s Order of Acctid`n
%s `@Random`0`n", "<input type='radio' name='change' value='1' checked>", "<input type='radio' name='change' value='0'>",true);
			output("`n`@To prevent too much spamminess, please insert a limit.");
			rawoutput("<br><input value='200' name='limit' size='5'>");
			output("`n`i`^Place no value or 0 to generate all.`i`n`n");
			rawoutput("<input type='submit' class='button' value='".translate_inline("Generate")."'></form>");
			addnav("","runmodule.php?module=esc&op=gen");
			break;
		case "gen":
			$b = "";
			if ($l > 0 && $l != ""){
				$b = "LIMIT $l";
			}
			if ($ch == 1){
				$a = "ORDER BY acctid ASC";
			}else{
				$a = "ORDER BY RAND(".e_rand().")";
			}
			$sql = "SELECT DISTINCT emailaddress FROM ".db_prefix("accounts")." 
					WHERE emailaddress!='' $a $b";
			$res = db_query($sql);
			$count = db_num_rows($res);
			debug ($sql);
			output("`^%s Emails have been retrieved.`0`n`n",$count);
			$str = "";
			while($row = db_fetch_assoc($res)){
				$str = $str.$row['emailaddress'].", ";
			}
			output("%s",$str);
			break;
		}
	addnav("Return to the Grotto","superuser.php");
page_footer();
}
?>			