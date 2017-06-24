<?php
output($session['message']);
$session['message'] = "";
$mail = db_prefix('mail');
$accounts = db_prefix('accounts');
$sql = "SELECT $mail.*, $accounts.name FROM $mail LEFT JOIN $accounts ON $accounts.acctid=$mail.msgfrom WHERE msgto=\"".$session['user']['acctid']."\" AND messageid=\"".$id."\"";
$result = db_query($sql);
if (db_num_rows($result)>0){
	$row = db_fetch_assoc($result);
	if ($row['msgfrom']==0  || !is_numeric($row['msgfrom'])){
		if ($row['msgfrom'] == 0 && is_numeric($row['msgfrom'])) {
			$row['name']=translate_inline("`i`^System`0`i");
		} else {
			$row['name']=$row['msgfrom'];
		}
		// No translation for subject if it's not an array
		$row_subject = @unserialize($row['subject']);
		if ($row_subject !== false) {
			$row['subject'] = call_user_func_array("sprintf_translate", $row_subject);
		}
		// No translation for body if it's not an array
		$row_body = @unserialize($row['body']);
		if ($row_body !== false) {
			$row['body'] = call_user_func_array("sprintf_translate", $row_body);
		}
	}
	if ($row['seen'] == 1) {
		output("`b`#NEW`b`n");
	}
	if ($row['subject'] != ""){
		$subject = $row['subject'];
	}else{
		$subject = "`i`&No Subject`i";
	}
	output("`b`2From:`b `^%s`n",$row['name']);
	output("`b`2Subject:`b `^%s`n",$subject);
	output("`b`2Received:`b `^%s`n",date($session['user']['prefs']['timeformat'],strtotime($row['sent'])+($session['user']['prefs']['timeoffset'] * 60 * 60)));
	$reply = translate_inline("Reply");
	$del = translate_inline("Delete");
	$unread = translate_inline("Mark Unread");
	$report = translate_inline("Report to Admin");
	$problem = "Abusive Email Report:\nFrom: {$row['name']}\nSubject: {$row['subject']}\nSent: {$row['sent']}\nID: {$row['messageid']}\nBody:\n{$row['body']}";
	rawoutput("<table width='50%' border='0' cellpadding='0' cellspacing='5'><tr>");
	if ($row['msgfrom'] > 0 && is_numeric($row['msgfrom'])) {
		rawoutput("<td><a href='mail.php?op=write&replyto={$row['messageid']}' class='motd'>$reply</a></td>");
	}else if ($row['subject'] == "`Q`bClan Points EOM`b"){
		rawoutput("<td><a href='mail.php?op=write&replyto={$row['messageid']}&eom=1' class='motd'>$reply</a></td>");
	} else {
		rawoutput("<td>&nbsp;</td>");
	}
	rawoutput("<td><a href='mail.php?op=del&id={$row['messageid']}' class='motd'>$del</a></td>");
	rawoutput("</tr></table>");
	output_notl("<hr color='#C11B17' width='182px' align='left'>",true);
	//$row['body'] = preg_replace("[[:alpha:]] //[^<>[:space:]] [[:alnum:]/]",'<a href="\\0" target="_blank">\\0</a>', $row['body']);
	output_notl(str_replace("\n","`n",$row['body']),true);
	if ($row['seen']==1){
		$sql = "UPDATE mail SET seen=0 WHERE messageid='{$id}'";
		db_query($sql);
	}
	invalidatedatacache("mail-{$session['user']['acctid']}");
}else{
	output("Eek, no such message was found!");
}
?>