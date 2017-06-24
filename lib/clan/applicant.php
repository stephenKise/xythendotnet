<?php
	page_header("Guild Halls");
	$registrar=getsetting('clanregistrar','`%Karissa');
	addnav("Guild Options");
	output("`b`c`&Guild Halls`c`b");
	if ($op=="apply"){
		require_once("lib/clan/applicant_apply.php");
	}elseif ($op=="new"){
		require_once("lib/clan/applicant_new.php");
	}else{
		output("`7You enter the Guild Registry Center hoping to find a group who suits you best. Guilds are excellent if you are looking to meet a tight group of people who have similar occupations or interests. In the center of the lobby sits a highly polished desk, behind which sits `%%s`7, the Guild Receptionist.",$registrar);

/*//*/	modulehook("clan-enter");
		if ($op=="withdraw"){
			$session['user']['clanid']=0;
			$session['user']['clanrank']=CLAN_APPLICANT;
			$session['user']['clanjoindate']='0000-00-00 00:00:00';
			output("`7You tell `%%s`7 that you're no longer interested in joining %s.",$registrar, $claninfo['clanname']);
			output("She reaches into her desk, withdraws your application, and tears it up.  \"`5You wouldn't have been happy there anyhow, I don't think,`7\" as she tosses the shreds in her trash can.");
			$claninfo = array();
			$sql = "DELETE FROM " . db_prefix("mail") . " WHERE msgfrom=0 AND seen=0 AND subject='".serialize($apply_subj)."'";
			db_query($sql);
			output("You are not a member of any clan.");
			addnav("Apply for Membership to a Guild","clan.php?op=apply");
			addnav("Apply for a New Guild","clan.php?op=new");
		}else{
			if (isset($claninfo['clanid']) && $claninfo["clanid"]>0){
				//applied for membership to a clan
				output("`7You approach `%%s`7 who smiles at you, but lets you know that your application to %s hasn't yet been accepted.",$registrar,$claninfo['clanname']);
				addnav("Waiting Area","clan.php?op=waiting");
				addnav("Withdraw Application","clan.php?op=withdraw");
			}else{
				//hasn't applied for membership to any clan.
				output("You are not a member of any clan.");
				addnav("Apply for Membership to a Guild","clan.php?op=apply");
				addnav("Create a New Guild","clan.php?op=new");
			}
		}
	}
?>