<?php

// Inspired by pkhonor.net, a RuneScape Private Server.
function adminchatcommands_getmoduleinfo()
{
	$info=array("name" => "Admin Chat Commands", "version" => "0.1", "author" => "`&`bStephen Kise`b", "category" => "Commentary", "download" => "nope", "settings" => array("Admin Commands,title", "usingtags" => "Do you have the core modded for bold and centering in the chat?,bool|0", "btag" => "Tag for bold:,text", "centertag" => "Tag for centering:,text", "color" => "Color to use when warning:,text", "maft" => "Message to declare AFTER player name:,text", "Player Commands,title", "afknews" => "Make a news addition when a player goes afk?,bool|0", "backnews" => "Make a news addition when a player comes back?,bool|0", "General Commands,title", "wepallow" => "Should we allow players to enter their own weapon names?,bool|0", "rwep" => "Text to replace with weapon name:,text|^W^", "armallow" => "Should we allow players to enter their own armor names?,bool|0", "rarm" => "Text to replace with armor name:,text|^A^"),);
	return $info;
}

function adminchatcommands_install()
{
	module_addhook("commentary");
	return true;
}

function adminchatcommands_uninstall()
{
	return true;
}

function adminchatcommands_dohook($hookname, $args)
{
	global $session;
	switch ($hookname)
	{

		case "commentary":
			//ADMIN COMMANDS
			$line=$args["commentline"];
			$b=get_module_setting("btag");
			$c=get_module_setting("centertag");
			$clr=get_module_setting("color");
			$u=get_module_setting("usingtags");
			$ma=get_module_setting("maft");
			if ((strpos($line, "/warn")!==false) && ($session['user']['superuser']&SU_IS_GAMEMASTER))
			{
				$msgyname=preg_replace('/\/warn/', '', $line);
				if ($u)
				{
					$endline="/game".$b.$c.$msgyname.$clr.$ma.$b.$c;
				}
				else
				{
					$endline="/game".$msgyname.$clr.$ma;
				}
				$args["commentline"]=$endline;
			}
			if (strpos($line, "/clear")!==false && ($session['user']['superuser'] & SU_IS_GAMEMASTER || substr_count($args['commentsection'], 'dwelling') == 1))
			{
				$colorCode = get_module_pref('user_emote','defaultcolor');
				$args["commentline"]=":`^has deleted all commentary for {$args['commentsection']}!";
				db_query("UPDATE ".db_prefix('commentary')." SET deleted = '1' WHERE section = '{$args['commentsection']}' AND comment != ':$colorCode`^has deleted all commentary for {$args['commentsection']}!'");
				invalidatedatacache("comments-{$args['commentsection']}");
			}
			//PLAYER COMMANDS
			$an=get_module_setting("afknews");
			$bn=get_module_setting("backnews");
			if (strpos($line, "/afk")!==false)
			{
				$args["commentline"]="";
				if ($an)
					addnews("%s `^is AFK and will be back shortly.", $session['user']['name']);
			}
			if (strpos($line, "/back")!==false)
			{
				$args["commentline"]="";
				if ($bn)
					addnews("%s `^is now back.", $session['user']['name']);
			}
			//GENERAL
			$wa=get_module_setting("wepallow");
			$rw=get_module_setting("rwep");
			$aa=get_module_setting("armallow");
			$ra=get_module_setting("rarm");
			if ($wa)
			{
				$args['commentline']=str_replace($rw, $session['user']['weapon'], $args['commentline']);
			}
			if ($aa)
			{
				$args['commentline']=str_replace($ra, $session['user']['armor'], $args['commentline']);
			}
			break;
	}
	return $args;
}

?>