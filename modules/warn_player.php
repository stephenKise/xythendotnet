<?php


function warn_player_getmoduleinfo()
{
	$info = array(
		"name"=>"Warn Players",
		"author"=>"`&`bStephen Kise`b",
		"category"=>"Administrative",
		"version"=>"0.1b",
		"prefs"=>array(
			"Warn Player Preferences,title",
			"warn_array"=>"What has the player been warned for?,text",
			"warn_amount"=>"What is the weight of the player's warning level?,int",
			)
		);
	return $info;
}

function warn_player_install()
{
	module_addhook("bioinfo");
	return true;
}

function warn_player_uninstall()
{
	return true;
}

function warn_player_dohook($hook,$args)
{
	global $session;
	// debug($args);
		if (is_numeric(httpget('char')))
		{
			$char = httpget('char');
		}
		else
		{
			$sql = db_fetch_assoc(db_query("SELECT acctid FROM accounts WHERE login = '".httpget('char')."'"));
			$char = $sql['acctid'];
		}
		if ($hook == "bioinfo" && $session['user']['superuser'] & SU_EDIT_COMMENTS)
		{
		  addnav ("Admin Functions");
			addnav("!?`4Warn User","runmodule.php?module=warn_player&op=comment_selector&player=".$char);
			if (get_module_pref('warn_array','warn_player',$args['acctid']) != "")
				{
					output("`b`\$Warnings`b`n");
					$warn_data = unserialize(get_module_pref('warn_array','warn_player',$args['acctid']));
					foreach ($warn_data as $item => $amount)
					{
						output("`4Rule {$item}: $amount`n");
					}
				}
		}

	return $args;
}

function warn_player_run()
{
	global $session, $module_settings;
	$op = httpget('op');
	check_su_access('SU_EDIT_COMMENTARY');
	page_header("Warn Player");
	get_all_module_settings("siterules");
	switch ($op)
	{
		case "comment_selector":
			require_once('lib/datetime.php');
			//addnav("Warn Selector","runmodule.php?module=warn_player&op=warn_selector&player=".httpget('player'));
			addnav("Return to user's bio","bio.php?char=".httpget('player'));
			addnav("Go to the village","village.php");
			addnav("Refresh","runmodule.php?module=warn_player&op=comment_selector&player=".httpget('player'));
			output("`b`\$Users Last Comments`b`n`n");
			$sections = array();
			$player = db_fetch_assoc(db_query("SELECT name FROM accounts WHERE acctid = ".httpget('player')));
			$sql = db_query("SELECT comment,postdate,section,commentid FROM commentary WHERE author = ".httpget('player')." AND section NOT LIKE 'pet-%'order by section DESC, postdate+0 DESC LIMIT 0,50");
			while ($row = db_fetch_assoc($sql))
			{
				if ($sections[$row['section']] == 0)
				{
					output("`^`b".ucfirst($row['section']).":`b`n");
					$sections[$row['section']]++;
				}
				$math = (strlen($row['comment'])-strpos($row['comment'],";;;;"));
				if ($row['comment'][0] == ":" && !strstr($row['comment'],';;NPC;;'))
					output(timeoffset($row['postdate'])." {$player['name']}`0 ".substr($row['comment'], 1)."`n");
				else if (strstr($row['comment'],';;NPC;;'))
					output(timeoffset($row['postdate'])." `&({$player['name']}`&) `0".substr($row['comment'],15,(-1*$math))."`n");
			}
			
			if (db_num_rows($sql) < 1)
			{
				output("`c`i`7None.`i`c");
			}
			output("<hr>",true);
			output("`b`\$Warn User`b`n`n");
			
			$site_rules = $module_settings['siterules'];
			output("<style>
				div.rules:hover {background: #222}
					</style>",true);
			output("<table align='center' width='750px'><tr><td align='center'>`i`^Select which rule the player should be warned for below.`i</td></tr>",true);
			foreach ($site_rules as $key=>$hole)
			{
				if (trim(substr($key,0,4)) == "Rule" && $hole != "")
				{
				$rule_number = $key[4];
				//debug($rule_number);
					output("<tr><td><a href='runmodule.php?module=warn_player&op=warn_player&player=".httpget('player')."&rule_number=$rule_number'><div class='rules'>`\$Rule $rule_number: $hole<div></a></td></tr>",true);
					addnav("","runmodule.php?module=warn_player&op=warn_player&player=".httpget('player')."&rule_number=$rule_number");
				}
			}
			output("</table>",true);
		break;
		/*case "warn_selector":
			$site_rules = $module_settings['siterules'];
			output("<style>
				div.rules:hover {background: #222}
					</style>",true);
			output("<table align='center' width='750px'><tr><td align='center'>`i`^Select which rule the player should be warned for below.`i</td></tr>",true);
			foreach ($site_rules as $key=>$hole)
			{
				if (trim(substr($key,0,4)) == "Rule" && $hole != "")
				{
				$rule_number = $key[4];
				debug($rule_number);
					output("<tr><td><a href='runmodule.php?module=warn_player&op=warn_player&player=".httpget('player')."&rule_number=$rule_number'><div class='rules'>`\$Rule $rule_number: $hole<div></a></td></tr>",true);
					addnav("","runmodule.php?module=warn_player&op=warn_player&player=".httpget('player')."&rule_number=$rule_number");
				}
			}
			output("</table>",true);
			addnav("Return to user's bio","bio.php?char=".httpget('player'));
			addnav("Go to the village","village.php");
		break;
		*/
		case "warn_player":
			require_once('lib/alert.php');
			output("`c`i`^This user has been warned for `\$Rule: ".httpget('rule_number')."`^ and has been alerted accordingly.`i`c");
			$warned_data = unserialize(get_module_pref('warn_array','warn_player',httpget('player')));
			debug(httpget('rule_number'));
			$warned_data[httpget('rule_number')]++;
			$warned_data = serialize($warned_data);
			set_module_pref('warn_array',$warned_data,'warn_player',httpget('player'));
			alert(httpget('player'),"`^You have been warned for `\$Rule ".httpget('rule_number')."`^. If you need refreshed on the rules, you can find the `b`\$Site Rules`b `^in any village.");
			addnav("Return to user's bio","bio.php?char=".httpget('player'));
			addnav("Return to the village","village.php");
		break;
	}
	page_footer();
}
?>