<?php
// translator ready
// addnews ready
// mail ready

function increment_specialty($colorcode, $spec=false){
	global $session;
	if ($spec !== false) {
		$revertspec = $session['user']['specialty'];
		$session['user']['specialty'] = $spec;
	}
	tlschema("skills");
	if ($session['user']['specialty']!=""){
		$specialties = modulehook("incrementspecialty",
				array("color"=>$colorcode));
	}else{
		output("");
	}
	tlschema();
	if ($spec !== false) {
		$session['user']['specialty'] = $revertspec;
	}
}
?>