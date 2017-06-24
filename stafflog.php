<?php

# file created (12/22/11) by (Brendan) 

require_once("common.php");
function stafflog($date,$account_id,$target_id,$message,$value){


	$message = addslashes($message);
	$date = date("Y-m-d H:i:s");
	$sql = "INSERT INTO ".db_prefix("stafflog")." VALUES('$id','$date',$account_id,$target_id,'$message','$value')";
	db_query($sql);
	$stafflog = "stafflog hook";
	

}

function stafflog_hook($message){
	global $session;
	
	$date = date("Y-m-d H:i:s");
	$id = $session['user']['acctid'];
	$name = $session['user']['name'];
	$message = $name." ".$message;
	$sql = "INSERT INTO ".db_prefix("stafflog")." VALUES('','$date','$id','$id','$message','')";
	db_query($sql);
}


?>
