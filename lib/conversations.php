<?php


function add2convo($user,$convo,$newconvo=true){
	global $session;
	$target = db_fetch_assoc(db_query("SELECT login, conversation FROM accounts WHERE acctid = $user"));
	$cnvs = unserialize($target['conversation']);	
	$tmp = array();
	foreach($cnvs as $key => $val){
		array_push($tmp,$val);
	}
	array_push($tmp,$convo);
	$tmp = array_unique($tmp);
	$target['conversation'] = serialize($tmp);
 	db_query("UPDATE accounts SET conversation = '".addslashes($target['conversation'])."' WHERE acctid = ".$user);
	unset($tmp);
	unset($cnvs);
}

function kickfromconvo($user,$convo){
	global $session;
	$target = db_fetch_assoc(db_query("SELECT login, conversation FROM accounts WHERE acctid = $user"));
	$cnvs = unserialize($target['conversation']);
	$tmp = array();
	foreach($cnvs as $key => $val){
		if ($val != $convo) array_push($tmp,$val);
	}
	$cnvs = serialize($tmp);
	db_query("UPDATE accounts SET conversation = '".addslashes($cnvs)."' WHERE acctid = $user");
	unset($tmp);
	unset($cnvs);
}

?>