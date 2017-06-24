<?php

//I know that this could probably not be viable, considering that 
function creationmute_getmoduleinfo(){
	$info = array(
		"name"=>"Creation Mute",
        "version"=>"1.0",
        "author"=>"`&Stephen Kise",
        "category"=>"Administrative",
        "download"=>"nope",
        "allowanonymous"=>true,
		"prefs"=>array(
                "Creation preferences,title",
                "unmuted"=>"Players allowed to message via chat and YoM?,bool|0",
                ),
	);
	return $info;
}

function creationmute_install(){
    module_addhook("process-create");
    module_addhook("superuser");
	module_addhook("insertcomment");
	module_addhook("mailfunctions");
    if (!is_module_active("creationmute")){
		$sql = db_query("SELECT acctid FROM accounts WHERE acctid >= 1");
		while ($row = db_fetch_assoc($sql)){
			set_module_pref("unmuted",1,"creationmute",$row['acctid']);
		}
	}
return true;
}

function creationmute_uninstall(){
	
	return true;
}

function creationmute_dohook($hookname,$args){
	global $session, $SCRIPT_NAME;
	switch($hookname){
        case "process-create":
			global $shortname;
			$sql = db_query("SELECT acctid FROM ".db_prefix("accounts")." WHERE login='$shortname'");
			$row = db_fetch_assoc($sql);
			$id = $row['acctid'];
			set_module_pref("unmuted",0,"creationmute",$id);
			
			require_once("lib/systemmail.php");
			systemmail($id, "`QCreation Mute", "`2'Creation Mute' is active on this server for the time being. You have to wait for one of the staff to validate your account before you can write any mail messages or post in the chat. While you wait, it is advised to play the other aspects of the game to get a good head start.`n`n`^Thanks for your cooperation!");
			break;
		
		case "superuser":
			if (($session['user']['superuser'] & SU_EDIT_USERS)) {
				addnav("*Check Often*");
				$check = db_query("SELECT userid FROM ".db_prefix("module_userprefs")." WHERE modulename = 'creationmute' AND seting = 'unmuted' AND value = 0");
				if (!db_num_rows($check)) addnav("`lVerify New Accounts","runmodule.php?module=creationmute&op=list");
					else addnav("`LVerify New Accounts `^ - `b`4New!`b","runmodule.php?module=creationmute&op=list");
			}
			break;
		
		case "insertcomment":
			if (get_module_pref("unmuted") == 0) {
				$args['mute']=1;
				$mutemsg="`n`\$Your account has yet to be verified for posting in the chat.`0`n`n";
				$mutemsg=translate_inline($mutemsg);
				$args['mutemsg']=$mutemsg;
			}
			break;
		
		case "mailfunctions":
			if ($SCRIPT_NAME == "mail.php"  && (httpget('op') == "address" || httpget('op') == "write") && get_module_pref("unmuted") == 0){
				debug("test");
 				$session['message'] = "`\$Your account has yet to be verified by the staff. Please wait patiently until the staff verify you before trying to write any mail.";
//   				httpset("op","");
   				header("Location: mail.php");
//   				require_once("lib/redirect.php");
//   				redirect("mail.php");
			}
			break;
        }
        return $args;
}

function creationmute_run(){
	global $session;
	require_once("lib/redirect.php");
	require_once("lib/systemmail.php");
	if ($session['user']['superuser'] & !SU_EDIT_USERS) redirect("village.php");
		//Never done a SU check. Anyone can revise this redirect. Was made when I was sick and didn't want to back check my syntax.
		
	$op=httpget("op");
	page_header("Creation Mute");
	addnav("Go back to the Grotto","superuser.php");
	
	switch($op){
		case "list":
			if ($session['modulemessage']){
				output($session['modulemessage']."`n`n");
				$session['modulemessage'] = NULL;
				//NULL instead of unset(). PHP 5.4+ makes NULL a lot faster in comparison to unset().
			}
			rawoutput("<table align='center' width='750px' nowrap><tr><td colspan='2'>Player Name</td><td>IP</td><td>ID</td><td>Email</td></tr>");
			$sql = db_query("SELECT userid FROM ".db_prefix("module_userprefs")." WHERE modulename = 'creationmute' AND setting = 'unmuted' AND value = 0");
			while ($row = db_fetch_assoc($sql)){
				$id = $row['userid'];
				$unverified_query = db_query("SELECT name,lastip,uniqueid,emailaddress FROM ".db_prefix("accounts")." WHERE acctid = $id");
				$account = db_fetch_assoc($unverified_query);
				output("<tr><td>`2[<a href='runmodule.php?module=creationmute&op=verify&acctid=$id'>Verify</a>`2]</td>",true);
				output("<td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>",$account[name],$account[lastip],$account[uniqueid],$account[emailaddress],true);
				addnav("","runmodule.php?module=creationmute&op=verify&acctid=$id");
			}
			if (!db_num_rows($sql)) output("<tr><td colspan='5'>`cNo players are awaiting activation at this time!`c</td></tr>",true);
			rawoutput("</table>");
			addnav("Refresh","runmodule.php?module=creationmute&op=list");
			break;
		
		case "verify":
			set_module_pref("unmuted",1,"creationmute",httpget("acctid"));
			if ($session['user']['acctid'] != 2 || $session['user']['acctid'] != 181){
				systemmail(2,"Player Verified!",$session['user']['name']." `2has verified a new users with the acctid of `^".httpget("acctid")."`2. They are to blame if something goes wrong!");
				systemmail(181,"Player Verified!",$session['user']['name']." `2has verified a new users with the acctid of `^".httpget("acctid")."`2. They are to blame if something goes wrong!");
			}
			$session['modulemessage'] = "Player with the acccount id of ".httpget("acctid")." has been verified and unmuted!";
			redirect("runmodule.php?module=creationmute&op=list");
			break;
	}
	page_footer();
}

?>
