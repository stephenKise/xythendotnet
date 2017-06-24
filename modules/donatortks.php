<?php

function donatortks_getmoduleinfo()
{
	$info = array(
		"name" => "Donator TKs",
		"author" => "`&Stephen Kise",
		"version"=> "0.1b",
		"category" => "Lodge",
		"settings" => array(
			"dp10" => "How many DPs for 10 TKs?,int|500",
			"dp25" => "How many DPs for 25 TKs?,int|1000",
			"dp50" => "How many DPs for 50 TKs?,int|2000"
		)
	);
	return $info;
}

function donatortks_install()
{
	module_addhook("header-lodge");
	module_addhook("lodge");
	return TRUE;
}

function donatortks_uninstall()
{
	return TRUE;
}

function donatortks_dohook($hook,$args)
{
	global $session;
	switch ($hook)
	{
		case "header-lodge":
			output($session['message']);
			unset($session['message']);
		break;
		case "lodge":
			$dpsleft = $session['user']['donation']-$session['user']['donationspent'];
			$prefs = get_all_module_settings();
			addnav("Gameplay Advantage");
			if ($dpsleft >= $prefs['dp10']) addnav("10 Tks `@({$prefs['dp10']} DP)","runmodule.php?module=donatortks&op=buy&amt=10");
			if ($dpsleft >= $prefs['dp25']) addnav("25 Tks `@({$prefs['dp25']} DP)","runmodule.php?module=donatortks&op=buy&amt=25");
			if ($dpsleft >= $prefs['dp50']) addnav("50 Tks `@({$prefs['dp50']} DP)","runmodule.php?module=donatortks&op=buy&amt=50");
		break;
	}
	return $args;
}

function donatortks_run()
{
	require_once('lib/redirect.php');
	global $session;
	$op = httpget('op');
	$amt = httpget('amt');
	$prefs = get_all_module_settings();
	page_header("");
	switch ($op)
	{
		case "buy":
			$session['user']['dragonkills'] += $amt;
			$session['user']['donationspent'] += $prefs['dp'.$amt];
			$session['message'] = "`c`b`i`QThank you for your purchase of `@$amt `QTKs!`i`b`c`n";
			addnews("%s `^has bought `@$amt `^TKs as a donation reward!",trim($session['user']['name']));
			redirect("lodge.php");
		break;
	}
	page_footer();
}
?>