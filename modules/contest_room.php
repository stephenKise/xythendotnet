<?php
function contest_room_getmoduleinfo(){
	$info = array(
		"name"=>"Trivia/Prize Room",
		"version"=>"1.1",
		"author"=>"Oak, lots of additions and fixed by `i`b`&Xpert`b`i",
		"category"=>"Village",
		"settings"=>array(
			"Prizes Amounts,title",
				"charm"=>"How much charm player player will get,int|5",
				"gold"=>"How much gold player will get,int|10000",
				"gems"=>"How many gems player will get,int|5000",
				"hp"=>"How many hitpoints player will get,int|100",
				"exp"=>"How much experience player will get,int|1000",
				"dks"=>"How many dk's player will get,int|3",
				"dps"=>"How many Donator Points will the player get?,int|50",
			"Prizes Costs,title",
				"charm2"=>"How many points to buy the charm?,int|1",
				"gold2"=>"How many points to buy the gold?,int|1",
				"gems2"=>"How many points to buy the gems?,int|1",
				"hp2"=>"How many points to buy the hitpoints?,int|1",
				"exp2"=>"How many points to buy the experience?,int|2",
				"dks2"=>"How many points to buy the dk's?,int|2",
				"namecost"=>"How many points to buy a name change?,int|3",
				"titlecost"=>"How many points to buy a title change?,int|3",
				"dpscost"=>"How many points to convert into Donator Points?,int|100",
			"Mail Settings,title",
				"subj"=>"What is the YOM subject?,text|contest points",
			"Name & Title Change Settings,title",
				"bold"=>"Allow bold?,bool|1",
				"italics"=>"Allow italics?,bool|1",
				"blank"=>"Allow blank names?,bool|1",
				"spaceinname"=>"Allow spaces in custom titles?,bool|1",
		),
		"prefs"=>array(
			"Trivia User Prefs,title",
			"inroom"=>"Is user in trivia room?,bool|0",
			"points"=>"How many contest points does the player have?,int|0",
			"cangive"=>"Can user give out contest points?,bool|0",
			"banned"=>"Is user banned from trivia?,bool|0",
		),
	);
	return $info;
}

function contest_room_install(){
	module_addhook("village");
	module_addhook("bioinfo");
	module_addhook("moderate");
//	module_addhook("charstats");
	module_addhook("commentary");
	return true;
}

function contest_room_uninstall(){
	return true;
}

function contest_room_dohook($hookname, $args){
	global $session;
	switch($hookname){
		case "village":
			set_module_pref("inroom", 0);
			if (!get_module_pref('banned')){
				tlschema($args['schemas']['compnav']);
				addnav($args["compnav"]);
				tlschema();
				addnav("3?`b`EC`b`eo`vn`b`Vt`b`i`ee`Es`i`Et `b`eR`b`i`vo`Vo`i`Em", "runmodule.php?module=contest_room&op=entrance");
			}
		break;
		case "bioinfo":
		if ($session['user']['superuser'] & SU_EDIT_USERS || get_module_pref("cangive"))
		{
        	addnav("Admin Functions");
			addnav("8?Contest Points", "runmodule.php?module=contest_room&op=give&id=".$args['acctid']);
		}
		break;
		case "moderate":
			$args['contest_room'] = "Trivia Room";
		break;
	case "charstats":
		setcharstat("Other", "Contest Points", (int)get_module_pref("points")); 
	break;
		case "commentary":
			if (get_module_pref('banned') && $args['section'] == 'contest_room') $args['post'] = false;
		break;
	}
	return $args;
}

function contest_room_run(){
	global $session;
	require_once("lib/names.php");
	require_once("lib/systemmail.php");

	$op = httpget('op');
	$id = httpget('id');
	$pts = get_module_pref("points");
	$set = get_all_module_settings();

	page_header("The Trivia Room");
	addnav("Options");
	
	set_module_pref("inroom", 1);

	switch ($op){
		case "entrance":
			require_once("lib/commentary.php");
			require_once("lib/villagenav.php");

			villagenav();
			if ($session['user']['superuser'] & SU_EDIT_COMMENTS) addnav("Add/Remove Ban", "runmodule.php?module=contest_room&op=ban");
			
			addnav("Buy");
			addnav("V?`%{$set['charm']} `&Charm `@({$set['charm2']}CP)", "runmodule.php?module=contest_room&op=are_you_sure&want=charm&cost=".$set['charm2']);
			addnav("V?`%{$set['gold']} `&Gold `@({$set['gold2']}CP)", "runmodule.php?module=contest_room&op=are_you_sure&want=gold&cost=".$set['gold2']);
			addnav("V?`%{$set['gems']} `&Gems `@({$set['gems2']}CP)", "runmodule.php?module=contest_room&op=are_you_sure&want=gems&cost=".$set['gems2']);
			//addnav("`%{$set['charm2']} Hitpoints", "runmodule.php?module=contest_room&op=hitpoints");
			//addnav("Experience", "runmodule.php?module=contest_room&op=experience");
			addnav("V?`%{$set['dks']} `&TKs `@({$set['dks2']}CP)", "runmodule.php?module=contest_room&op=are_you_sure&want=dragonkills&cost=".$set['dks2']);
			addnav("V?`%{$set['dps']} `&DP `@({$set['dpscost']}CP)", "runmodule.php?module=contest_room&op=are_you_sure&want=dps&cost=".$set['dpscost']);
			addnav("V?`&Name Color `@({$set['namecost']}CP)","runmodule.php?module=contest_room&op=are_you_sure&want=name&cost=".$set['namecost']);
			addnav("V?`&Title Color `@({$set['titlecost']}CP)","runmodule.php?module=contest_room&op=are_you_sure&want=title&cost=".$set['titlecost']);
			addnav("Buy");
			addnav("`i`&You have `@$pts `&points`i","");

			rawoutput("<font style='font-size: 14pt;'>");
			output("`n`b`c`&Welcome to the Trivia Room!`c`b");
			rawoutput("</font>");

			output("`L_______________________________`n
`b`^Trivia`b`L__________________________`n
`l1.) `^TriviaBot`L hosts trivia for the staff and can be answered at any time during the day after it is started.`n
`l2.) `^2`L for each correct answer.`l`n
3.)`L Answers within `g5 seconds `Lof each other will be counted.`n
`l4.) `LMake sure you are not adding any extra characters such as emotes or punctuation when answering.`n
`l5.) `LPlease `^petition`L if there are any wrong or wonky answers, or if you do not obtain your points.`n
`l6.) `LAll `bnumber`b answers will be in number format. There is no need to spell out the number.  `n
`l7.) `LWe cannot accept player donated trivia questions, unfortunately, to maintain fairness.        `n
`l8.) `LDo not announce the answers to trivia questions to other players - keep it secret, keep it safe! (lotr <3)    `n
`n`n");
			
			//output("`n`c`3`bHave some contest points you want to spend? Check out the prizes...`b`c`n`n");
			
		   /*	output("`c`%%s `&point%s for `#%s `&charm`c", $set['charm2'], ($set['charm2'] == 1 ? "" : "s"), $set['charm']);
			output("`c`%%s `&point%s for `#%s `&gold pieces`c", $set['gold2'], ($set['gold2'] == 1 ? "" : "s"), $set['gold']);
			output("`c`%%s `&point%s for `#%s `&gems`c", $set['gems2'], ($set['gems2'] == 1 ? "" : "s"), $set['gems']);
			//output("`c`%%s `&point%s for `#%s `&hitpoints`c", $set['hp2'], ($set['hp2'] == 1 ? "" : "s"), $set['hp']);
			//output("`c`%%s `&point%s for `#%s `&experience`c", $set['exp2'], ($set['exp2'] == 1 ? "" : "s"), $set['exp']);
			output("`c`%%s `&point%s for `#%s `&TK's`c", $set['dks2'], ($set['dks2'] == 1 ? "" : "s"), $set['dks']);
			output("`c`%%s `&point%s for a name colour change`c", $set['namecost'], ($set['namecost'] == 1 ? "" : "s"));
			output("`c`%%s `&point%s for a title change`c", $set['titlecost'], ($set['titlecost'] == 1 ? "" : "s"));*/
			//output("`n`c`@You have `&`b%s`b`@ trivia point%s!`c`n", $pts, ($pts==1?"":"s"));
			
			$sql = "SELECT acctid,name,loggedin FROM ".db_prefix('accounts')." a INNER JOIN ".db_prefix('module_userprefs')." m ON a.acctid = m.userid WHERE modulename = 'contest_room' AND setting = 'inroom' AND value = 1 AND userid <> {$session['user']['acctid']} AND loggedin=1";
			
			
			// require_once("modules/whoshere.php");
			// whoshere($sql);
		output("<style>
		.noselect {
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}
</style>",true);
		output("<div class='noselect'>",true);
		addcommentary();
		viewcommentary("contest_room","",25,"answers");
		output("</div>",true);
		break;
		
		case "give":
			require_once("lib/showform.php");
			addnav("Return", "bio.php?char=$id");
			addnav("Trivia Room", "runmodule.php?module=contest_room&op=entrance");
			
			$postam = httppost('amount');
			if ($postam){
				increment_module_pref("points", (int)$postam, "contest_room", $id);
				$name = db_fetch_assoc(db_query("SELECT name FROM ".db_prefix('accounts')." WHERE acctid = $id"));
				output("`&%s`# was rewarded %s point%s!`n", $name['name'], $postam, ($postam==1?"":"s"));
				$subj = array("%s", get_module_setting("subj"));
				$body = array("You recieved %s contest point%s.",$postam,($postam==1?"":"s"));
				systemmail($id, $subj, $body);
			}
			output("`c`&Give user how many points?`0`c");
			addnav("", "runmodule.php?module=contest_room&op=give&id=$id");
			rawoutput("<form action='runmodule.php?module=contest_room&op=give&id=$id' method='post'>");
			showform(array("amount"=>"Amount of Points,int"), array("amount"=>0));
			rawoutput("</form>");
		break;
		
		case "ban":
			require_once("lib/showform.php");
			$postid = httppost('id');
			if ($postid){
				$b = get_module_pref('banned', 'contest_room', $postid);
				set_module_pref('banned', ( $b ? 0 : 1 ), 'contest_room', $postid);
				$name = db_fetch_assoc(db_query("SELECT name FROM ".db_prefix('accounts')." WHERE acctid = $postid"));
				output("`&%s`# is %s from trivia!`n", $name['name'], ( $b ? "unbanned" : "banned" ));
			}
			addnav("Trivia Room", "runmodule.php?module=contest_room&op=entrance");
			addnav("", "runmodule.php?module=contest_room&op=ban");
			rawoutput("<form action='runmodule.php?module=contest_room&op=ban' method='post'>");
			showform(array("id"=>"User ID,int"), array("id"=>""));
			rawoutput("</form>");
			
			$sql = "SELECT acctid,name FROM ".db_prefix('module_userprefs')." m INNER JOIN  ".db_prefix('accounts')." a ON m.userid = a.acctid WHERE modulename = 'contest_room' AND setting = 'banned' AND value = '1'";
			$res = db_query($sql);
			while ($row = db_fetch_assoc($res)){
				output_notl("`&%s`& (%s)`n", $row['name'], $row['acctid']);
			}
		break;

		case "charm":
			if ($pts >= $set['charm2']){
				addnav("Back", "runmodule.php?module=contest_room&op=entrance");
				output("`c`&You recieve %s charm points.`0`c", $set['charm']);
				$session['user']['charm'] += $set['charm'];
				increment_module_pref("points", -$set['charm2']);
			} else {
				addnav("Back", "runmodule.php?module=contest_room&op=entrance");
				output("`c`&You dont have enough contest points for this.`0`c");
			}
		break;
		
		case "gold":
			if ($pts >= $set['gold2']){
				addnav("Back", "runmodule.php?module=contest_room&op=entrance");
				output("`c`&You recieve %s gold pieces.`0`c", $set['gold']);          
				$session['user']['gold'] += $set['gold'];
				increment_module_pref("points", -$set['gold2']);
			} else {
				addnav("Back", "runmodule.php?module=contest_room&op=entrance");
				output("`c`&You dont have enough contest points for this.`0`c");
			}
		break;
		
		case "gems":
			if ($pts >= $set['gems2']){
				addnav("Back", "runmodule.php?module=contest_room&op=entrance");
				output("`c`&You recieve %s gems.`0`c", $set['gems']);
				$session['user']['gems'] += $set['gems'];
				increment_module_pref("points", -$set['gems2']);
			} else {
				addnav("Back", "runmodule.php?module=contest_room&op=entrance");
				output("`c`&You dont have enough contest points for this.`0`c");
			}
		break;
		
		case "hitpoints":
			if ($pts >= $set['hp2']){
				addnav("Back", "runmodule.php?module=contest_room&op=entrance");
				output("`c`&You recieve %s hitpoints.`0`c", $set['hp']);
				$session['user']['hitpoints'] += $set['hp'];
				increment_module_pref("points", -$set['hp2']);
			} else {
				addnav("Back", "runmodule.php?module=contest_room&op=entrance");
				output("`c`&You dont have enough contest points for this.`0`c");
			}
		break;
		
		case "experience":
			if ($pts >= $set['exp2']){
				addnav("Back", "runmodule.php?module=contest_room&op=entrance");
				output("`c`&You recieve %s experience.`0`c", $set['exp']);
				$session['user']['experience'] += $set['exp'];
				increment_module_pref("points", -$set['exp2']);
			} else {
				addnav("Back", "runmodule.php?module=contest_room&op=entrance");
				output("`c`&You dont have enough contest points for this.`0`c");
			}
		break;
		
		case "dragonkills":
			if ($pts >= $set['dks2']){
				addnav("New Day", "newday.php");
				output("`c`&You recieve %s Master Kills.`0`c", $set['dks']);
				$session['user']['dragonkills'] += $set['dks'];
				increment_module_pref("points", -$set['dks2']);
			} else {
				addnav("Back", "runmodule.php?module=contest_room&op=entrance");
				output("`c`&You dont have enough contest points for this.`0`c");
			}
		break;
		
		case "dps":
			if ($pts >= $set['dpscost']){
				addnav("Back", "runmodule.php?module=contest_room&op=entrance");
				output("`c`&You recieve %s donator points.`0`c", $set['dps']);
				$session['user']['donation'] += $set['dps'];
				increment_module_pref("points", -$set['dpscost']);
			} else {
				addnav("Back", "runmodule.php?module=contest_room&op=entrance");
				output("`c`&You dont have enough contest points for this.`0`c");
			}
		break;
		
		case "name":
			//With code from Eric's namecolor
			output("`3`bName Color Change`b`0`n`n");

			$regname = get_player_basename();
			output("Your name currently is this:");
			rawoutput($regname);
			output(", which looks like %s`7`n`n", $regname);
			output("How would you like your name to look?`n");
			rawoutput("<form action='runmodule.php?module=contest_room&op=namepreview' method='POST'><input name='newname' value=\"".HTMLEntities($regname, ENT_COMPAT, getsetting("charset", "ISO-8859-1"))."\"> <input type='submit' class='button' value='Preview'></form>");
			addnav("","runmodule.php?module=contest_room&op=namepreview");

			addnav("Go Back","runmodule.php?module=contest_room&op=entrance");
		break;
		
		case "namepreview":
			$regname = get_player_basename();
			$newname = str_replace("`0", "", httppost("newname"));

//			if (!get_module_setting("bold")) $newname = str_replace("`b", "", $newname);
//			if (!get_module_setting("italics")) $newname = str_replace("`i", "", $newname);
//			$newname = preg_replace("/[`][cHw]/", "", $newname);

			$comp1 = strtolower(sanitize($regname));
			$comp2 = strtolower(sanitize($newname));
			$err = 0;
			if ($comp1 != $comp2) {
				if (!$err) output("`3`bInvalid name`b`0`n");
				$err = 1;
				output("Your new name must contain only the same characters as your current name; you can add or remove colors, and you can change the capitalization, but you may not add or remove anything else. You chose %s.`n", $newname);
			}
			if (strlen($newname) > 100) {
				if (!$err) output("`3`bInvalid name`b`0`n");
				$err = 1;
				output("Your new name is too long.  Including the color markups, you are not allowed to exceed 100 characters in length.`n");
			}
			if (!$err) {
				output("`7Your name will look this this: %s`n`n`7Is this what you wish?`n`n`0", $newname);
				addnav("Confirm Name Change");
				addnav("Yes", "runmodule.php?module=contest_room&op=changename&name=".rawurlencode($newname));
				addnav("No", "runmodule.php?module=contest_room&op=name");
			} else {
				output("`n");
				require_once("modules/namecolor.php");
				namecolor_form();
				addnav("Go Back","runmodule.php?module=contest_room&op=entrance");
			}
		break;
		
		case "changename":
			if ($pts >= $set['namecost']){
				increment_module_pref("points", -$set['namecost']);
				$fromname = $session['user']['name'];
				$newname = change_player_name(rawurldecode(httpget('name')));
				$session['user']['name'] = $newname;
				addnews("%s`^ has become known as %s.",$fromname,$session['user']['name']);
				output("`7Congratulations, your name is now {$session['user']['name']}`7!`n`n");
				modulehook("namechange", array());
				addnav("Go Back","runmodule.php?module=contest_room&op=entrance");
			} else {
				output("`\$Sorry, you do not have enough points.");
				addnav("Go Back","runmodule.php?module=contest_room&op=entrance");
			}
		break;
		
		case "title":
			//With code from JT Traub's trivia
			output("`3`bCustomize Title`b`0`n`n");
			$otitle = get_player_title();
			addnav("Go Back","runmodule.php?module=contest_room&op=entrance");
			output("`7Your title is currently`^ ");
			rawoutput($otitle);
			output_notl("`0`n");
			output("`7which looks like %s`n`n", $otitle);
			if (httpget("err")==1) output("`\$Please enter a title.`n");
			output("`7How would you like your title to look?`n");
			rawoutput("<form action='runmodule.php?module=contest_room&op=titlepreview' method='POST'>");
			rawoutput("<input id='input' name='newname' width='25' maxlength='25' value='".htmlentities($otitle, ENT_COMPAT, getsetting("charset", "ISO-8859-1"))."'>");
			rawoutput("<input type='submit' class='button' value='Preview'>");
			rawoutput("</form>");
			addnav("", "runmodule.php?module=contest_room&op=titlepreview");
		break;
		
		case "titlepreview":
			$ntitle = rawurldecode(httppost('newname'));
			$ntitle = newline_sanitize($ntitle);
			
			if ($ntitle=="") {
				if (get_module_setting("blank")) {
					$ntitle = "`0";
				} else{
					redirect("runmodule.php?module=contest_room&op=contest_room&err=1");
				}
			}
//			if (!get_module_setting("bold")) $ntitle = str_replace("`b", "", $ntitle);
//			if (!get_module_setting("italics")) $ntitle = str_replace("`i", "", $ntitle);
//			$ntitle = sanitize_colorname(get_module_setting("spaceinname"), $ntitle);
//			$ntitle = preg_replace("/[`][cHw]/", "", $ntitle);
			$ntitle = sanitize_html($ntitle);

			$nname = get_player_basename();
			output("`7Your new title will look like this: %s`0`n", $ntitle);
			output("`7Your entire name will look like: %s %s`0`n`n", $ntitle, $nname);
			output("`7Is this how you wish it to look?");
			addnav("`bConfirm Custom Title`b");
			addnav("Yes", "runmodule.php?module=contest_room&op=changetitle&newname=".rawurlencode($ntitle));
			addnav("No", "runmodule.php?module=contest_room&op=title");
		break;
		
		case "changetitle":
			if ($pts >= $set['titlecost']){
				$ntitle = rawurldecode(httpget('newname'));
				$fromname = $session['user']['name'];
				$newname = change_player_ctitle($ntitle);
				$session['user']['ctitle'] = $ntitle;
				$session['user']['name'] = $newname;
				addnews("%s`^ has become known as %s.",$fromname,$session['user']['name']);
				increment_module_pref("points", -$set['titlecost']);
				output("Your custom title has been set.");
				modulehook("namechange", array());
				addnav("Go Back","runmodule.php?module=contest_room&op=entrance");
			} else {
				output("`\$Sorry, you do not have enough points.");
				addnav("Go Back","runmodule.php?module=contest_room&op=entrance");
			}
		break;
		
		case "are_you_sure";
			$user_want = httpget("want");
			$cost = httpget("cost");
			picky_users($user_want,$cost);
		break;
	}

	page_footer();
}

function picky_users($user_want,$cost)
{
	
	debug($user_want);
	output("Are you sure you want to spend ".$cost." contest points?");
	addnav("Options");
	addnav("Yes","runmodule.php?module=contest_room&op=".$user_want);
	addnav("No","runmodule.php?module=contest_room&op=entrance");
}

?>