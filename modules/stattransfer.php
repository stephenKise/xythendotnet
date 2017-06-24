<?php

function stattransfer_getmoduleinfo(){
	$info = array(
		"name"=>"Stat Transfers",
		"author"=>"`&Stephen Kise",
		"version"=>"1.0",
		"category"=>"Lodge",
		"download"=>"nope",
		"prefs"=>array(
			"Stat Transfer Prefs,title",
			"isset"=>"Has this player already asked for a stat transfer?,bool|0"
			)
		);
	return $info;
}


function stattransfer_install(){
	module_addhook_priority("lodge","11");
	module_addhook_priority("pointsdesc","11");
	return true;
}


function stattransfer_uninstall(){
	return true;
}


function stattransfer_dohook($hookname,$args){
	global $session;
	switch($hookname){
		case "lodge":
			if ($session['user']['donation'] >= 1000 && get_module_pref("isset") != 1){
				db_query("SELECT acctid FROM paylog WHERE acctid=".$session['user']['acctid']."");
				if (db_affected_rows()){
					addnav("Use Points");
					addnav("Stat Transfer`@ (1000 DP)","runmodule.php?module=stattransfer&op=enter");
				}
			}
		break;
		
		case "pointsdesc":
			$args['count']++;
			output("`7`bSilver:`b`n`\$- `^Ability to have a stat transfer of up to 100 TKs AND if you have donated.`n");
		break;
	}
	return $args;
}

function stattransfer_run(){
	global $session;
	$isset = get_module_pref("isset");
	$op = httpget('op');
	page_header("Donation Center");
	output("`Q`c`bStat Transfer`b`c");
	require_once("lib/redirect.php");
	switch($op){
		case "enter":
			if ($isset != 1){
				addnav("Go back to the Donation Center","lodge.php");
				output("`2Please include the following information:`n 1. Name of website `n2. Stats, including attack, defense, amount of dragon kills forge levels, and banked gold/gems.`n**Please note you can never have too much information. Send us as much as you can. `n**Maximum TK value allowed is 500.  `n`n");
				rawoutput("<form action='runmodule.php?module=stattransfer&op=send' method='POST'>");
				rawoutput("<textarea name='stattrans' class='input' cols='60' rows='9'>Place information about your stat transfer here!</textarea><br>");
				rawoutput("<input type='submit' value='Submit'>");
				rawoutput("</form>");
				addnav("","runmodule.php?module=stattransfer&op=send");
			}else{
				redirect("lodge.php");
			}
		break;
		case "send":
			$bod = array("charname"=>$session['user']['name'],
						"email"=>$session['user']['emailaddress'],
						"pname"=>"`QStat Transfer",
						"description"=>rawurldecode(httppost('stattrans'))."`n`n`\$`iThis is a message generated from the system. This player has donated for a stat transfer.`i"
						);
			$posted = serialize($bod);
			if ($isset != 1){
				db_query("INSERT INTO petitions (author, date, status, pname, body) VALUES ('{$session['user']['acctid']}', '".date("Y-m-d H:i:s")."', 0, '`QStat Tranfser', '$posted')");
				set_module_pref("isset",1);
				redirect("lodge.php");
			}else{
				redirect("lodge.php");
			}
		break;
	}
	page_footer();
}


?>