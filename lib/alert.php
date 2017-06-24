<?php 

function alert($acctid,$message=false){
	db_query("INSERT INTO alerts (acctid,message,posted) VALUES ('$acctid','".addslashes($message)."','".date("Y-m-d H:i:s")."')");
	invalidatedatacache("alerts-$acctid");
}

function alerteveryone($message){
	$sql = db_query("SELECT acctid FROM accounts WHERE acctid > 0");
	while ($row = db_fetch_assoc($sql)){
		alert($row['acctid'],$message);
	}
}

function alertstaff($message){
	$sql = db_query("SELECT acctid FROM accounts WHERE staff > 5");
	while ($row = db_fetch_assoc($sql)){
		alert($row['acctid'],$message);
	}
}
?>