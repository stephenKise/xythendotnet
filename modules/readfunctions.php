<?php

function readfunctions_getmoduleinfo(){
	$info = array(
		"name"=>"Options in Mail",
		"category"=>"Mail",
		"author"=>"`&`bStephen Kise`b",
		"version"=>"1.0",
		"download"=>"nope",
		"override_forced_nav"=>true,
		);
	return $info;
}

function readfunctions_install(){
	module_addhook_priority("mailfunctions","100");
	return TRUE;
}

function readfunctions_uninstall(){
	output("Nuuu");
	return TRUE;
}
function readfunctions_dohook($hook,$args){
	// global $REQUEST_URI,$session;
	global $SCRIPT_NAME,$session;
	switch ($hook){
		case "mailfunctions":
		
			$sql = db_query("SELECT * FROM archived WHERE owner = '{$session['user']['acctid']}'");
			while ($row = db_fetch_assoc($sql)){
				$sq = "INSERT INTO mail (messageid,msgfrom,msgto,subject,body,sent,seen,originator) VALUES ";
				$sq .= "('{$row['messageid']}','{$row['sender']}','{$session['user']['acctid']}','".addslashes($row['subject'])."','".addslashes($row['body'])."','{$row['archived']}','2','{$session['user']['acctid']}')";
				db_query($sq);
				db_query("DELETE FROM archived WHERE messageid = {$row['messageid']}");
			}
			// $id = httpget('id');
			$id = ( httpget('id') ? httpget('id') : httppost('returnto') );
// 			output("&bull;<a href='runmodule.php?module=readfunctions&op=view'>`\$Mail Archive</a>`n",true);
			// if (substr($REQUEST_URI,0,16) == "mail.php?op=read" && isset($id)){
			if ($SCRIPT_NAME == "mail.php" && isset($id) && (httpget('op') == "read" || httpget('op') == "send")){
					$result = db_query("SELECT messageid FROM ".db_prefix("mail")." WHERE msgto='{$session['user']['acctid']}' AND messageid < '$id' ORDER BY messageid DESC LIMIT 1");
					if (db_num_rows($result)>0){
						$row = db_fetch_assoc($result);
						$pid = $row['messageid'];
					}else{
						$pid = 0;
					}
					$result= db_query("SELECT messageid FROM ".db_prefix("mail")." WHERE msgto='{$session['user']['acctid']}' AND messageid > '$id' ORDER BY messageid  LIMIT 1");
					if (db_num_rows($result)>0){
						$row = db_fetch_assoc($result);
						$nid = $row['messageid'];
					}else{
						$nid = 0;
					}
					if ($pid > 0) {
						$prev = "`<<a href='mail.php?op=read&id=$pid' class='motd'><<</a> `<";
					}else{
						$prev = "`<<< `<";
					}
					if ($nid > 0){
						$next = "`> <a href='mail.php?op=read&id=$nid' class='motd'>>></a>`>";
					}else{
						$next = "`> >>`>";
					}
					output("`n`n`c`b`&Message Functions`b`c");
	 				output("`b`^-`b<a href='mail.php?op=unread&id=$id' class='motd'>Mark Unread</a>`n",true);	
					output("`b`^-`b<a href='mail.php?op=archive&msgid=$id'>`tArchive</a>`n",true);
					if ($session['user']['superuser'] & SU_EDIT_PETITIONS) {
		 				output("`b`^-`b<a href='mail.php?opmailaspet=peti&id=$id'>`i`\$Send to Petition`i</a>`n",true);
		 			}
// 					output("`b`^-`b<a href=\"petition.php?problem=".rawurlencode($problem)."&abuse=yes\" style='color:red' class='motd'>Report to Admin</a>`n",true);
	 				output("`c$prev  $next`c`n",true);	
			}
		break;	
	}
	return $args;
}


function readfunctions_run(){
	global $session;
	$op = httpget('op');
	$id = httpget('id');
	popup_header("Mail Archive");
	switch($op){
		case "archive":
			
			$num = db_query("SELECT messageid FROM archived WHERE messageid = $id");
			if (db_num_rows($num) >= 1){
				$session['message'] = "`QThis message is already in your archives!";
			}else{
				$info = db_fetch_assoc(db_query("SELECT subject, body, sent, msgfrom FROM mail WHERE messageid = $id"));
				if ($info['subject'] != ""){
					$subject = $info['subject'];
				}else{
					$subject = '`i`&No Subject`i';
				}
	 			$fua = db_query("INSERT INTO archived (`owner`,`sender`,`messageid`,`subject`,`body`,`archived`) VALUES ('".$session['user']['acctid']."','".$info['msgfrom']."','".$id."','".addslashes($subject)."','".addslashes($info['body'])."','".addslashes($info['sent'])."')");
				$session['message'] = "`QThis mail has been added to your archives!";
			}
			header("Location: mail.php?op=read&id=$id");
		break;
		case "view":
			output("<table width='100%' border='0' cellpadding='0' cellspacing='10px'>",TRUE);
			rawoutput("<tr><td valign='top' width='150px' nowrap>");
			output("&bull;<a href='mail.php'>`tInbox</a>`n",TRUE);
			output("&bull;<a href='runmodule.php?module=outbox'>`tOutbox</a>`n",TRUE);
			output("&bull;<a href='mail.php?&op=address'>`tCompose</a>`n",TRUE);
			output("&bull;<a href='petition.php'>`\$Petition for Help</a>`n",TRUE);
			modulehook("mailfunctions");
			output_notl("</td><td valign='top'>",true);
			output($session['message']);
			$session['message'] = "";
			output("`n");
			output("<table width=600px><tr><td colspan=2>`b`iArchived Messages`i`b</td><td align='right'>`bReceived`b</td></tr>",true);
			$num = db_query("SELECT messageid,sender,subject,archived FROM archived WHERE owner = ".$session['user']['acctid']." ORDER BY (messageid/1) DESC");
			for($i=0;$i<db_num_rows($num);$i++){
				$row = db_fetch_assoc($num);
				$from = db_fetch_assoc(db_query("SELECT name FROM accounts WHERE acctid = ".$row['sender']));
				output("<tr><td nowrap><a href='runmodule.php?module=readfunctions&op=open&id=".$row['messageid']."'>".$row['subject']."</a></td>",true);
				output("<td align='center'><a href='runmodule.php?module=readfunctions&op=open&id=".$row['messageid']."'>".$from['name']."</a></td>",true);
				output("<td align='right' width=150px>".date($session['user']['prefs']['timeformat'],strtotime($row['sent'])+($session['user']['prefs']['timeoffset'] * 60 * 60))."</td></tr>",true);
			}
			if (db_num_rows($num) < 1) output("<tr><td colspan=3>`i`&You have nothing archived!`i</td></tr>",true);			
			rawoutput("</table>",TRUE);
			
			rawoutput("</td></tr></table>",TRUE);
		break;
		case "open":
		
			output("<table width='100%' border='0' cellpadding='0' cellspacing='10px'>",TRUE);
			rawoutput("<tr><td valign='top' width='150px' nowrap>");
			output("&bull;<a href='mail.php'>`tInbox</a>`n",TRUE);
			output("&bull;<a href='runmodule.php?module=outbox'>`tOutbox</a>`n",TRUE);
			output("&bull;<a href='mail.php?&op=address'>`tCompose</a>`n",TRUE);
			output("&bull;<a href='petition.php'>`\$Petition for Help</a>`n",TRUE);
			modulehook("mailfunctions");
			output_notl("</td><td valign='top'>",true);
			
			$row = db_fetch_assoc(db_query("SELECT * FROM archived WHERE messageid = $id"));
			$from = db_fetch_assoc(db_query("SELECT name FROM accounts WHERE acctid = ".$row['sender']));
			
			
			
			/*
			$sql = db_query("SELECT * FROM archived WHERE owner = '{$session['user']['acctid']}'");
			while ($row = db_fetch_assoc($sql)){
				$sq = "INSERT INTO mail (messageid,msgfrom,msgto,subject,body,sent,seen,originator) VALUES ";
				$sq .= "('{$row['messageid']}','{$row['sender']}','{$session['user']['acctid']}','".addslashes($row['subject'])."','".addslashes($row['body'])."','{$row['archived']}','2','{$session['user']['acctid']}')";
				db_query($sq);
				db_query("DELETE FROM archived WHERE messageid = {$row['messageid']}");
			}
			header("Location: mail.php");
			
			
			
			*/
			
			output("`b`2From:`b `^".$from['name']."`n");
			output("`b`2Subject:`b `^".$row['subject']."`n");
			output("`b`2Received:`b `^".date($session['user']['prefs']['timeformat'],strtotime($row['archived'])+($session['user']['prefs']['timeoffset'] * 60 * 60))."`n");
			output("<a href='runmodule.php?module=readfunctions&op=del&id=$id' class='motd'>Delete</a>`n",true);
			output_notl("<hr color='#C11B17' width='182px' align='left'>",true);
			output(nl2br($row['body']),true);
			
			rawoutput("</td></tr></table>",TRUE);
		break;
		case "del":
			$row = db_fetch_assoc(db_query("SELECT subject FROM archived WHERE messageid = $id"));
			db_query("DELETE FROM archived WHERE messageid = $id");
			$session['message'] = "`QYou have deleted a message entitled `^".$row['subject']."`Q from your archived mail."; 
			header("Location: runmodule.php?module=readfunctions&op=view");
		break;
	}
	popup_footer();
}

?>