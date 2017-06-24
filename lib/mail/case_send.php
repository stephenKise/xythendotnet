<?php
$to = httppost('to');

if ($session['user']['superuser'] & SU_IS_GAMEMASTER) {
	$from = httppost('from');
	if ($from == "" || is_numeric(trim($from)) || $from == "0") {
		$from = $session['user']['acctid'];
	}
} else {
	$from = $session['user']['acctid'];
}

$sql = "SELECT acctid FROM " . db_prefix("accounts") . " WHERE login='$to'";
$result = db_query($sql);
if(db_num_rows($result)>0){
	$row1 = db_fetch_assoc($result);
	if (getsetting("onlyunreadmails",true)) {
		$maillimitsql = "AND seen=0";
	} else {
		$maillimitsql = "";
	}
	$sql = "SELECT count(messageid) AS count FROM " . db_prefix("mail") . " WHERE msgto='".$row1['acctid']."' $maillimitsql";
	$result = db_query($sql);
	$row = db_fetch_assoc($result);
	if ($row['count']>=getsetting("inboxlimit",50)) {
		output("`\$You cannot send that person mail, their mailbox is full!`0`n`n");
	}else{
		$subject = str_replace("`n","",httppost('subject'));
		$string = httppost('body');
		$string = trim($string);
		$i = 0;
		$last_pos = -1;
		while ($i < substr_count($string,"`"))
		{
			//debug($i);
			$last_pos = strpos($string,"`",($last_pos+1));
			if ($string[$last_pos+1] == " " || $string[$last_pos+1] == "" || $string[$last_pos+1] == "\r\n" || $string[$last_pos+1] == "\r" || $string[$last_pos+1] == "\n") $string[$last_pos] = "";
			$i++;
		}
		$body = str_replace("`n","\r\n",$body);
		$body = str_replace("\r","\r\n",$body);
		$body = addslashes(substr(stripslashes($body),0,(int)getsetting("mailsizelimit",1024)));
		$realbody = $string;
		$check_for_mk = explode('---Original',$body);
		//debug($check_for_mk[0]);
		require_once("lib/systemmail.php");
		// if ($row1['acctid'] == 1)
		// 	systemmail($from,$subject,"`i`QAiyanvi is to not be addressed via mail until further notice. She is under a `blot`b of stress, so she does not need to be bugged by anyone.`n`^Instead, you can message the co-owner - Knygaard, or any of their staff members.`i`n`n`n".$realbody,$from);
		// else
			systemmail($row1['acctid'],$subject,$realbody,$from);
		invalidatedatacache("mail-{$row1['acctid']}");
		output("Your message was sent!`n");
	}
}else{
	output("Could not find the recipient, please try again.`n");
}
if(httppost("returnto")){
	$op="read";
	httpset('op','read');
	$id = httppost('returnto');
	httpset('id',$id);
}else{
	$op="";
	httpset('op', "");
}
?>