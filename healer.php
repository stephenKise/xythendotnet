<?php
// addnews ready
// translator ready
// mail ready
require_once("common.php");
require_once("lib/forest.php");
require_once("lib/http.php");
require_once("lib/villagenav.php");

tlschema("healer");

$config = unserialize($session['user']['donationconfig']);

$return = httpget("return");
$returnline = $return>""?"&return=$return":"";

page_header("Healer's Hut");
output("`#`b`cHealer's Hut`c`b`n");

$cost = log($session['user']['level']) * (($session['user']['maxhitpoints']-$session['user']['hitpoints']) + 10);
$result=modulehook("healmultiply",array("alterpct"=>1.0));
$cost*=$result['alterpct'];
$cost = round($cost,0);

$op = httpget('op');
if ($op==""){
  	checkday();
	$newcost=round(100*$cost/100,0);
	if($session['user']['hitpoints'] < $session['user']['maxhitpoints']) $playerheal = true;
	if ($playerheal){
		$session['user']['hitpoints'] = $session['user']['maxhitpoints'];
		$session['user']['gold'] -= $newcost;
		require_once("lib/redirect.php");
		redirect("forest.php");
	}else if (!isset($playerheal)){
	output("`n`n<center><strong>`2You are already at full health!</strong></center>",true);
	addnav("Actions");
	addnav("Return to Forest","forest.php");
	}
}
tlschema();
output_notl("`0");
page_footer();
?>