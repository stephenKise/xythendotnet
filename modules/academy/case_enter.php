<?php
	output(
		"You make your way into the seedy bar, the scent of smoke, beer," .
		" blood and other less savory fluids fills your nostrils." .
		" Scattered across the many tables and benches are some of" .
		" the most dangerous citizens of the realm. Bandits, assassins," .
		" cut-throats, military, professional bodyguards. Their" . 
		" backgrounds are varied, but they are all united in this" .
		" place by one common creed, anything for money." .
		" As you approach the bar, MacCready gives you a sly nod" .
		" of acknowledgement as he puts a glass away. Working as the" .
		" face and intermediate of this fine establishment," .
		" many wouldn't guess that the thin, scrappy-looking man behind" .
		" the bar was, in fact, one of the best shots around." .
		"`n`n\"`&The name's MacCready. What can I get ya?" . 
		" Cheap beer or some muscle to aid you in battle?" . 
		"`&If it's hired help you need, I can sell you a contract" . 
		" for `%%s gems.\"",get_module_setting("cost")); 
	addnav("Options");
	addnav("Training Hall","runmodule.php?module=academy&op=hall");
	if ($active && !$dead){
		output(
			"`n`n`7MacCready pauses and gives you glance before continuing," .
			"`&\"I see `^%s`& is still alive and kicking." .
			" Beer then?`7\"",$name);
		addnav(array("Dismiss %s",$classarray[$class]),"runmodule.php?module=academy&op=dismiss");
	}elseif($dead){
		output("`n`n`&MacCready quirks a brow at you for dragging a dead body" . 
			" into his pub. `&\"I'll get the resurrection stone,\" MacCready" . 
			" said, in a resigned manner.");	}
?>