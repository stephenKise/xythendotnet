<?php

function rlage_getmoduleinfo(){
        $info = array(
			"name"=>"Player's Real Life Age",
			"author"=>"`&`bStephen Kise`b`0",
			"version"=>"3.2",
			"category"=>"Administrative",
			"download"=>"nope",
			"override_forced_nav"=>true,
			"settings"=>array(
				"RL Age Settings,title",
				"server"=>"Name of your server?,text|Legend of the Green Dragon",
				"allowed_age"=>"Age of adult status?,int|18",
				"RL Age Gifts,title",
				"lastgift"=>"Last time module gave out birthday gifts,text|2005-01-01 01:00:00",
				"dps" => "Gifted Donation points,int|300",
				"gems" => "Gifted Gems,int|10000",
			),
			"prefs"=>array(
				"RL Age Prefs,title",
				"day"=>"Day the player was born?,range,1,31,1",
				"month"=>"Month the player was born?,enum,0,Unset,1,January,2,February,3,March,4,April,5,May,6,June,7,July,8,August,9,September,10,October,11,November,12,December|0",
				"year"=>"Year that player was born in?,int",
			),
        );
        return $info;
}

function rlage_install(){
	module_addhook("header-village");
	module_addhook("superuser");
	module_addhook("biostat");
	module_addhook("newday");
	return true;
}

function rlage_uninstall(){
        return true;
}

function rlage_dohook($hookname,$args){
	$mymonth = get_module_pref("month");
	$myyear = get_module_pref("year");
	$myday = get_module_pref("day");
	
	switch($hookname){
		case "header-village":
			if ($myyear == 0 || $mymonth == 0 || $myday == 0)
				redirect("runmodule.php?module=rlage&op=go");
		break;
		case "superuser":
				global $SCRIPT_NAME;
				if ($SCRIPT_NAME == "superuser.php"){
					addnav("Statistics");
					addnav("Players Birthdays","runmodule.php?module=rlage&op=staff&times=0");
				}
		break;
		case "biostat":
			$char = httpget('char');
			if (!is_numeric($char)){
				$row = db_fetch_assoc(db_query("SELECT acctid FROM ".db_prefix("accounts")." WHERE login = '$char'"));
				$char = $row['acctid'];
			}
			if (isadult($char)) $what = "Adult";
				else $what = "Minor";
			$scolor = getstatcolor($args['acctid']);
			$args['tablebiostat']['Basic Info'][$scolor.'OOC Age'] = "`#$what";
		break;
		case "newday":
			if (strtotime(date("Y-m-d H:i:s")) - strtotime(get_module_setting('lastgift')) >= 86400){
				require_once('lib/systemmail.php');
				$sql = "SELECT acctid FROM ".db_prefix('accounts')."";
				$res = db_query($sql);
				while ($row = db_fetch_assoc($res)){
					if (get_module_pref('day',FALSE,$row['acctid']) == date("j") && get_module_pref('month',FALSE,$row['acctid']) == date("n")){
						db_query("UPDATE ".db_prefix('accounts')." SET donation = donation + ".get_module_setting('dps')." WHERE acctid = {$row['acctid']}");
						db_query("UPDATE ".db_prefix('accounts')." SET gems = gems + ".get_module_setting('gems')." WHERE acctid = {$row['acctid']}");
						systemmail($row['acctid'],array("Happy Birthday!"),array("`^`bHappy Birthday!`b`n`nYou've been gifted with `&".get_module_setting('dps')." `^DPs and `&".get_module_setting('gems')." `^gems!`n`nHave a great day!"));
					}
				}
				set_module_setting('lastgift',date("Y-m-d H:i:s"));
			}
		break;
	}
	return $args;
}

function rlage_run(){
	global $session;
    $age=httppost('age');
	$month=httppost('month');
	$day=httppost('day');
	$year=httppost('year');
	$op = httpget("op");
	$times = httpget("times");
	$mu = db_prefix("module_userprefs");
	$ac = db_prefix("accounts");

	page_header("Ages");
	switch($op){
		case "go":
			output("`Q`c`bSet Age`b`0`n");
			output("<h3>`^You are required to enter your truthful birthdate. Do so now!</h3>`n`n",true);
			rawoutput("<form action=\"runmodule.php?module=rlage&op=set\" method='POST'>");
			output("Month");
			
			rawoutput("<select name=\"month\">");
			$mc = 0;
			while ($mc < 13){
				if ($mc != 0){
					$stringmonth = date("F", mktime(0, 0, 0, ($mc)));
				}else{
					$stringmonth = "";
				}
				rawoutput("<option value='$mc'>$stringmonth</option>");
				$mc++;
			}
			rawoutput("</select>");
			output("Day");
			
			rawoutput("<select name=\"day\">");
			for ($i=1;$i<32;$i++){
				if ($i==0) $x="";
				else $x=$i;
				rawoutput("<option value='$i'>$x</option>");
			}
			rawoutput("</select>");
			output("Year");
			
			rawoutput("<select name=\"year\">");
			for ($i=0;$i<75;$i++){
				if ($i==0){
					$x="";
					rawoutput("<option value='$i'>$x</option>");
				}else{
					$x=1935+$i;
					rawoutput("<option value='$x'>$x</option>");
				}
			}
			rawoutput("</select>");
			$createbutton = translate_inline("Enter!");
			rawoutput("<br><br><input type='submit' class='button' value='$createbutton'>");
			rawoutput("</form></center>");
			output("`c`0");
			addnav("","runmodule.php?module=rlage&op=set");
		break;
		case "set":
			output("`Q`c`bThank you!`n`^Have a nice time in %s!`b`c`n",get_module_setting("server"));
			$id = $session['user']['acctid'];
			set_module_pref('month',$month,'rlage',$id);
			set_module_pref('day',$day,'rlage',$id);
			set_module_pref('year',$year,'rlage',$id);
			villagenav();
		break;
		case "staff":
			check_su_access("SU_GIVE_GROTTO");
			addnav("Leave");
			addnav("The Grotto","superuser.php");
			villagenav();
			addnav("List B-Days!!");
			$navcounts = 1;
			while ($navcounts < 13){
				$stringmonth = date("F", mktime(0, 0, 0, ($navcounts)));
				addnav($stringmonth,"runmodule.php?module=rlage&op=staff&times=".$navcounts);
				$navcounts++;
			}
			switch($times){
				case 0:
					output("<h1> `n`n`c`QPlayer*s Ages!`c`n`n </h1>",true);
				break;
				case 1:
				case 2:
				case 3:
				case 4:
				case 5:
				case 6:
				case 7:
				case 8:
				case 9:
				case 10:
				case 11:
				case 12:
					$result = db_query("SELECT a.name AS name, t2.value AS day FROM accounts AS a
						INNER JOIN module_userprefs AS t1 ON t1.userid = a.acctid
						INNER JOIN module_userprefs AS t2 ON t2.userid = a.acctid
						WHERE t1.modulename = 'rlage' AND t1.setting = 'month' AND t1.value = $times
						AND t2.modulename = 'rlage' AND t2.setting = 'day' AND t2.value > 0 ORDER BY day/1 ASC");
					$player = translate_inline("Birthday People");
					$thedays = translate_inline("Date");
					output("`b`c`@%s BIRTHDAYS`c`b`n`n",strtoupper(date("F", mktime(0, 0, 0, ($times)))));
					rawoutput("<table border='0' cellpadding='2' cellspacing='1' align='center' bgcolor='#999999'>");
					rawoutput("<tr class='trhead'><td>$player</td><td>$thedays</td></tr>");
					$i = 0;
					if (db_num_rows($result)>0){
						while($row = db_fetch_assoc($result)){
							rawoutput("<tr class='".($i%2?"trdark":"trlight")."'><td>");
								output_notl("`^%s",$row['name']);
							rawoutput("</td><td>");
								output_notl("`^%s",$row['day']);
							rawoutput("</td></tr>");
							$i++;
						}
					}
					rawoutput("</table>");
				break;
			}
		break;
	}
	page_footer();
}


function isadult($account){
	$allowed_age = get_module_setting("allowed_age","rlage");
	$birthdate = get_module_pref("month","rlage",$account)."/".get_module_pref("day","rlage",$account)."/".get_module_pref("year","rlage",$account);
	$age = floor((time() - strtotime($birthdate))/31556926);
	if ($age >= $allowed_age) return TRUE;
		else return FALSE;
}

function birthdays_eom(){
	$m = date('n');
	$months = array("Error","January","February","March","April","May","June","July","August","September","October","November","December");
	$month = $months[$m];
	$result = db_query("SELECT a.name AS name, t2.value AS day FROM accounts AS a
						INNER JOIN module_userprefs AS t1 ON t1.userid = a.acctid
						INNER JOIN module_userprefs AS t2 ON t2.userid = a.acctid
						WHERE t1.modulename = 'rlage' AND t1.setting = 'month' AND t1.value = $m
						AND t2.modulename = 'rlage' AND t2.setting = 'day' AND t2.value > 0 ORDER BY day/1 ASC");
	$player = translate_inline("Birthdays");
	$thedays = translate_inline("Day");
	output("<big>`b`c`QBirthdays In ".ucfirst($month)."`c`b</big>`n`n",true);
	rawoutput("<table align='center'>");
	rawoutput("<tr class='trhead'><td>$player</td><td>$thedays</td></tr>");
	$i = 0;
	if (db_num_rows($result)>0){
		while($row = db_fetch_assoc($result)){
			rawoutput("<tr><td>");
			output_notl("`^%s",$row['name']);
			rawoutput("</td><td>");
			output_notl("`^%s",$row['day']);
			rawoutput("</td></tr>");
			$i++;
		}
	}
	rawoutput("</table>");
}
?>