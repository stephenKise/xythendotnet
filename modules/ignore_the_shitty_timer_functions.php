<?php

function ignore_the_shitty_timer_functions_getmoduleinfo()
{
	$info = array(
		"name" => "Ignore the `iShitty`i LotGD timer functions",
		"author" => "`&Stephen Kise",
		"category" => "Lodge",
		"version" => "0.1b",
		"prefs" => array(
			"stage" => "What stage of timeout is the player at?,int|0",
		),
		"settings" => array(
			"dp20" => "How many DPs for 20 minute timer?,int|100",
			"dp45" => "How many DPs for 45 minute timer?,int|250",
			"dp60" => "How many DPs for an hour timer?,int|600"
		)

	);
	return $info;
}

function ignore_the_shitty_timer_functions_install()
{
	module_addhook("lodge");
	return TRUE;
}

function ignore_the_shitty_timer_functions_uninstall()
{
	return TRUE;
}

function ignore_the_shitty_timer_functions_dohook($hook,$args)
{
	global $session;
	switch ($hook)
	{
		case "lodge":
			$sets = get_all_module_settings();
			$amt_left = $session['user']['donation']-$session['user']['donationspent'];
			$stages = 0;
			$timer_count = array("dp20" => "20", "dp45" => "45", "dp60" => "60");
			addnav("Gameplay Advantage");
			debug(get_module_pref("stage"));
			foreach($sets as $key=>$val)
			{
				if (get_module_pref("stage") < 3 && $amt_left >= $val && $links_provided < 1)
				{
					$sets_keys = array_keys($sets);
					debug($sets_keys);
					addnav("Increase Timeout `@(".$sets[$sets_keys[get_module_pref("stage")]]." DP)","runmodule.php?module=ignore_the_shitty_timer_functions&op=extend_timer");
					//debug($sets[$sets_keys[get_module_pref("stage")]]);
					$links_provided = 1;
				}
			}
		break;
	}
	return $args;
}

function ignore_the_shitty_timer_functions_run()
{
	global $session;
	require_once("lib/systemmail.php");
	require_once("lib/redirect.php");
	$op = httpget('op');
	$sets = get_all_module_settings();
	page_header();
	switch ($op)
	{
		case "extend_timer":
			$stage = get_module_pref("stage");
			$set_keys = array_keys($sets);
			$time = array("dp20"=>20,"dp45"=>45,"dp60"=>60);
			$set_vals = array_values($sets);
			increment_module_pref("stage",1);
			addnews("{$session['user']['name']} `^has extended their timeout counter as a donation reward!");
			$session['user']['donationspent'] += $set_vals[$stage];
			systemmail($session['user']['acctid'],"Timeout Function","`QYour timeout function has changed, forever! From now on, you can leave the site for ".
			"up to `@".$time[$set_keys[$stage]]." minutes`Q! Keep this in mind from now on, that your character will remain logged in for this duration.`n`^Sincerely,`nXythen Staff");
			redirect("lodge.php");
		break;
	}
	page_footer();
}

?>