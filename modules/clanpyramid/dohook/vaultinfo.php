<?php

//Avarice
if ($owned3==0){
	output_notl("`n`i`7Labyrinth C has just been discovered.`i`n");
}elseif($owned3>0){
	$sql = "SELECT * FROM " .db_prefix("clans"). " WHERE clanid = '$owned3'";
	$res = db_query($sql);
	$row = db_fetch_assoc($res);
	$clanname3 = $row['clanname'];
	output_notl("<tr><td>`i`7Labyrinth C is currently under the control of:</td><td>`i`0 %s</td></tr>`n`n",$clanname3,true);
}

//Rapacity
if ($owned1==0){
	output("`n`i`7Labyrinth A has just been discovered.`i`n");
}elseif ($owned1>0){
	$sql = "SELECT * FROM " .db_prefix("clans"). " WHERE clanid = '$owned1'";
	$res = db_query($sql);
	$row = db_fetch_assoc($res);
	$clanname1 = $row['clanname'];
	output("<tr><td>`i`7Labyrinth A is currently under the control of:</td><td>`i`0 %s</td></tr>",$clanname1,true);
}

//Cupidity
if ($owned2==0){
	output("`n`i`7Labyrinth B has just been discovered.`i`n");
}elseif ($owned2>0){
	$sql = "SELECT * FROM " .db_prefix("clans"). " WHERE clanid = '$owned2'";
	$res = db_query($sql);
	$row = db_fetch_assoc($res);
	$clanname2 = $row['clanname'];
	output("<tr><td>`i`7Labyrinth B is currently under the control of:</td><td>`i`0 %s</td></tr>",$clanname2);
}

//Last Month Winner
$lm=get_module_setting("lastwinner");
output_notl("`n");
if ($lm=="") output("");
else output("<tr><td>`i`7Guild in the lead for Labyrinth takeovers is...`i</td><td>%s`7!</td></tr>",$lm);

?>