<?php
/*
	Modified by MarcTheSlayer
	05/04/2012 - v1.0.0
	+ This is a modified version of the module 'forestprefs' by Thanatos.
	  I've stripped out everything apart from the code to allow auto levelling
	  in the forest when the player has enough experience for the next level.
	+ Changed the file name to suit its function.
*/
function autolevel_getmoduleinfo()
{
	$info = array(
		"name"=>"Auto Level Up",
		"description"=>"Allow players to auto level up in the forest instead of fighting a Master.",
		"version"=>"1.0.0",
		"author"=>"`4Thanatos`2, modified by `@MarcTheSlayer",
		"category"=>"Forest",
		"settings"=>array(
			"levellimit"=>"Maximum level that can be gained:,int|15",
			"`2If you have modified your game to have more levels than 15 then enter the highest level here else leave it at 15.,note",
		),
	);
	return $info;
}

function autolevel_install()
{
	module_addhook('village');
	module_addhook('battle-victory');
	return TRUE;
}

function autolevel_uninstall()
{
	return TRUE;
}

function autolevel_dohook($hookname,$args)
{
	global $session;

	switch($hookname)
	{
		case 'village':
		if (!$session['user']['superuser'] & SU_MEGAUSER) blocknav('train.php');
		break;

		case 'battle-victory':
			global $SCRIPT_NAME;
			if ($args['autolevelhook']) break; // Prevent recurssion of hook
			
			if( get_module_setting('levellimit') > $session['user']['level']  && $SCRIPT_NAME != "pvp.php")
			{
				require_once('lib/increment_specialty.php');
				require_once('lib/experience.php');
				$level = $session['user']['level'];
				$dks = $session['user']['dragonkills'];
				$reqexp = exp_for_next_level($session['user']['level'],$session['user']['dragonkills']);
				if( $session['user']['experience'] + $args['creatureexp'] >= $reqexp)
				{
					output_notl("`c");
					output("`n`b`2-=-`@=-=`2-=- `@You Level Up! `2-=-`@=-=`2-=-`0`b`n`n");
					output("`^You `4ADVANCE `^a `)Level`%!`n");
					output("`^You `4gain `^an `)attack point`%!`n");
					output("`^You `4gain `^a `)defense point`%!`n");
					modulehook('training-victory');
					modulehook('battle-victory', array('type'=>'train', 'autolevelhook' => true)); // Prevent recurssion of hook
					if ($session['user']['referer']>0 && ($session['user']['level']>=getsetting("referminlevel",4) || $session['user']['dragonkills'] > 0) && $session['user']['refererawarded']<1){
						$sql = "UPDATE " . db_prefix("accounts") . " SET donation=donation+".getsetting("refereraward",25)." WHERE acctid={$session['user']['referer']}";
						db_query($sql);
						$session['user']['refererawarded']=1;
						$subj=array("`%One of your referrals advanced!`0");
						$body=array("`&%s`# has advanced to level `^%s`#, and so you have earned `^%s`# points!", $session['user']['name'], $session['user']['level'], getsetting("refereraward", 25));
						require_once("lib/systemmail.php");
						systemmail($session['user']['referer'],$subj,$body);
					}
					increment_specialty("`^");
					output_notl("`n`b`2-=-`@=-=`2-=-`@=-=`2-=-`@=-=`2-=-`@=-=`2-=-`@=-=`2-=-`0`b`n");
					output_notl("`c");
				}
				while (exp_for_next_level($session['user']['level'],$session['user']['dragonkills']) < $session['user']['experience']){
					$session['user']['level']++;
					$session['user']['maxhitpoints']+=10;
					$session['user']['hitpoints']=$session['user']['maxhitpoints'];
					$session['user']['soulpoints']+=5;
					$session['user']['attack']++;
					$session['user']['defense']++;
				}
			}
		break;
	}
	return $args;
}
?>