<?php

$return = round(get_module_setting("cost")/2);

if (httpget('confirm')){
	output("MacCready nods and then gestures %s back to the bar tables.",$name);
	output("On the counter, MacCready had left %s gems for you.",$return);
	$session['user']['gems']+=$return;
	set_module_pref("name","");
	set_module_pref("active",0);
	set_module_pref("lsl",0);
	set_module_pref("level",0);
	set_module_pref("acc",65);
	set_module_pref("tacc",0);
	set_module_pref("class",0);
}else{
	output("`7You approach MacCready at the bar, laying down the contract" .
	  " that you had signed for your current mercenary, \"`&I want to end" .
	  " my contract with this %s, will you buy the contract back from me?`7\"`n`n",$classarray[$class]);
	output("MacCready releases a long drawn out sigh, reaching" .
		"into his pockets for %s gems as compensation.",$return);
	output("`n`n\"`&Are you sure you wish to end your contract with your %s?`7\"",$classarray[$class]);
	addnav("Decide");
	addnav("Dismiss","runmodule.php?module=academy&op=dismiss&confirm=1");
}
?>