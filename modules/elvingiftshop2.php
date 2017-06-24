<?php
function elvingiftshop2_getmoduleinfo(){	$info = array(
		"name"=>"Elvin Gift Shop 2",
		"version"=>"1.55",
		"author"=>"`#Lonny Luberts, text modified for elvin gift shop by Niksolo, modified by `i`)Ae`7ol`&us`i`0",
		"category"=>"Village",
		"download"=>"http://dragonprime.net/users/Finwe/elvingiftshop2.zip",
		"settings"=>array(
			"PQ Gift Shop Module Settings,title",
			"gsloc"=>"Where does the Gift Shop appear,location|".getsetting("villagename", LOCATION_FIELDS),
			"gsowner"=>"Gift Shop Clerk Name,text|`#F`3inwe",
			"gsheshe"=>"Gift Shop Owner Sex,text|he",
			"special"=>"Shop Specialty,text|Elven Enthusiast",
			"gift1"=>"Gift1,text|Elven Brooch",
			"gift1price"=>"Gift1 Price,int|0",
			"gift2"=>"Gift2,text|Magic Rope",
			"gift2price"=>"Gift2 Price,int|10",
			"gift3"=>"Gift3,text|Elven Cloak",
			"gift3price"=>"Gift3 Price,int|20",
			"gift4"=>"Gift4,text|Silverwood Bow",
			"gift4price"=>"Gift4 Price,int|40",
			"gift5"=>"Gift5,text|Gem Necklace",
			"gift5price"=>"Gift5 Price,int|60",
			"gift6"=>"Gift6,text|Star Necklace",
			"gift6price"=>"Gift6 Price,int|100",
			"gift7"=>"Gift7,text|Ring of Galadriel",
			"gift7price"=>"Gift7 Price,int|200",
			"gift8"=>"Gift8,text|Eternal Love Pendant",
			"gift8price"=>"Gift8 Price,int|500",
			"gift9"=>"Gift9,text|Elven Headdress",
			"gift9price"=>"Gift9 Price,int|1000",
			"gift10"=>"Gift10,text|Gem Bracelet",
			"gift10price"=>"Gift10 Price,int|1500",
			"gift11"=>"Gift11,text|Eternal Love Ear Rings",
			"gift11price"=>"Gift11 Price,int|2000",
			"gift12"=>"Gift12,text|Elven Love Ring",
			"gift12price"=>"Gift12 Price,int|3000",
		),	);	return $info;
}

function elvingiftshop2_install(){	module_addhook("village");	return true;
}

function elvingiftshop2_uninstall(){	return true;
}

function elvingiftshop2_dohook($hookname,$args){	global $session;	switch($hookname){		case "village":
			if ($session['user']['location'] == get_module_setting("gsloc")){
				tlschema($args['schemas']['marketnav']);    
				addnav($args['marketnav']);    
				tlschema();
				addnav(array("`b`kB`b`#r`3ist`#e`kl `bT`b`i`3r`#in`i`#k`ie`i`b`3t`b`ks",get_module_setting('gsowner')), "runmodule.php?module=elvingiftshop2");
			}
		break;	}	return $args;
}

function elvingiftshop2_run(){
	global $session;	require_once("lib/systemmail.php");
	page_header(get_module_setting('gsowner')."'s Ol' Gifte Shoppe");	
	$op = httpget('op');
	$gift = httpget('gid');
	$name = httpget('name');
	
	output("`c`b`mP`b`Mh`b`mi`b`yl`i`ga`i`kn`b`Kt`b`kh`i`gr`yo`i`mp`b`Ei`b`Me`ms `0`c`n`n");
	
	if ($op==""){
		output("`7You walk into the gift shop and see many items for sale.`n");
		output("`7%s stands behind the counter and smiles at you. An assortment of wares reside around the shop on shelves. It seems there are only a few items that are within your price range.`n",get_module_setting('gsowner'),get_module_setting('gsheshe'));
		output("`7You see a sign on the wall that says \"Free Delivery and gift wrapping.\"`n");
		output("`7This shop specializes in gifts for your %s, things you can afford...`n`n",get_module_setting('special'));
		for ($i=1;$i<13;$i+=1){			$currentgift = "gift".$i;			$currentprice = "gift".$i."price";			if ($session['user']['gold'] >= get_module_setting($currentprice)){				output("%s `&%s`0 `3- %s gold%s","<a href=\"runmodule.php?module=elvingiftshop2&op=send&gid=".$i."\">",get_module_setting($currentgift),get_module_setting($currentprice),"</a><br>",TRUE);
				addnav("","runmodule.php?module=elvingiftshop2&op=send&gid=".$i."");			}
		}
		addnav("Back to Bristel","village.php");
	}
	
	if ($op == "send"){		output("To whom would you like to send your gift to?`n");		rawoutput("<form action='runmodule.php?module=elvingiftshop2&op=send2&gid=$gift' method='POST'>");		rawoutput("<p><input type=\"text\" name=\"whom\" size=\"37\"></p>");		rawoutput("<p><input type=\"submit\" value=\"Submit\" name=\"B1\"><input type=\"reset\" value=\"Reset\" name=\"B2\"></p>");		rawoutput("</form>");		addnav("","runmodule.php?module=elvingiftshop2&op=send2&gid=$gift");		addnav("Go Back","runmodule.php?module=elvingiftshop2");
	}
	
 	if ($op == "send2"){		$whom = addslashes("%".implode("%",str_split(httppost('whom')))."%");		output("Choose who to send your gift to:`n");		rawoutput("<table cellpadding='3' cellspacing='0' border='0'>");		rawoutput("<tr class='trhead'><td>Name</td><td>Level</td></tr>");		$i = 0;		$sql = "SELECT login,name,level,acctid FROM ".db_prefix("accounts")." WHERE name LIKE '%".$whom."%' and acctid <> '".$session['user']['acctid']."' ORDER BY level,login LIMIT 100";		$result = db_query($sql);		while ($row = db_fetch_assoc($result)){
			rawoutput("<tr class='".($i%2?"trlight":"trdark")."'><td>");
				addnav("","runmodule.php?module=elvingiftshop2&op=send3&gid=$gift&name=".HTMLEntities($row['acctid']));				rawoutput("<a href='runmodule.php?module=elvingiftshop2&op=send3&gid=$gift&name=".HTMLEntities($row['acctid'])."'>");				output_notl($row['name']);				rawoutput("</a>");
			rawoutput("</td><td>");
				output_notl($row['level']);
			rawoutput("</td></tr>");			$i++;
		}		if (!$i){			rawoutput("<tr class='trlight'><td colspan='2'>");			output("No-One Found!");			rawoutput("</td></tr>");		}
		rawoutput("</table>");
		output("`n");		addnav("Go Back","runmodule.php?module=elvingiftshop2");	}
	
	if ($op=="send3"){		output("Fill in the Note Card that goes with the gift.`n");		output("Leave blank for no note.");		rawoutput("<form action='runmodule.php?module=elvingiftshop2&op=send4&gid=$gift&name=$name' method='POST'>");		rawoutput("<p><input type=\"text\" name=\"mess\" size=\"37\"></p>");		rawoutput("<p><input type=\"submit\" value=\"Submit\" name=\"B1\"><input type=\"reset\" value=\"Reset\" name=\"B2\"></p>");		rawoutput("</form>");		addnav("","runmodule.php?module=elvingiftshop2&op=send4&gid=$gift&name=$name");		addnav("Go Back","runmodule.php?module=elvingiftshop2");
	}
	
	if ($op=="send4"){		$mess = httppost('mess');		$price = (int)get_module_setting("gift".$gift."price");		$thegift = get_module_setting("gift".$gift."");		$session['user']['gold']-=$price;		$mailmessage=$session['user']['name'];		$mailmessage.="`7 has sent you a gift.  When you open it you see it is a `6";		$mailmessage.=$thegift;		$mailmessage.="`7 from ".get_module_setting('gsowner')."'s Philantrophie Shop!`n`n";		if ($mess <> ""){
			$mailmessage.="The attached note says \"";			$mailmessage.=$mess;			$mailmessage.=".\"";		}		systemmail($name,"`2You have recieved a gift!`2",$mailmessage);		output("You gift of a %s has been sent!",$thegift);		addnav("Continue","runmodule.php?module=elvingiftshop2");
	}
	
	page_footer();
}
?>