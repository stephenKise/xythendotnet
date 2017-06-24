<?php
	page_header("Guild  for %s",  full_sanitize($claninfo['clanname']));
	addnav("Management");
	if ($op==""){
		require_once("lib/clan/clan_default.php");
	}elseif ($op=="motd"){
		require_once("lib/clan/clan_motd.php");
	}elseif ($op=="membership"){
		require_once("lib/clan/clan_membership.php");
	}elseif ($op=="withdrawconfirm"){
		output("Are you sure you want to withdraw from your Guild?");
		addnav("Withdraw?");
		addnav("No","clan.php");
		addnav("!?Yes","clan.php?op=withdraw");
	}elseif ($op=="withdraw"){
		require_once("lib/clan/clan_withdraw.php");
	}

?>