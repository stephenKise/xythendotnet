<?php
tlschema("petition");
popup_header("Petition for Help");
$post = httpallpost();
$posted = serialize($post);
if (count($post)>0){
		//if (trim($post['carry']) == substr(md5($post['carryval']), -5)){
		if(isset($post['g-recaptcha-response'])){
          $captcha=$post['g-recaptcha-response'];
        }
        $response=file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6LelMQoTAAAAANmRY-nPrzReiHqFKMvSy_jM_fNH&response=".$captcha."&remoteip=".$_SERVER['REMOTE_ADDR']);
		//debug($response);
		$resp = json_decode($response,true);
		//debug($resp);
		if($resp['success'] != false)
        {
			$ip = explode(".",$_SERVER['REMOTE_ADDR']);
			array_pop($ip);
			$ip = join($ip,".").".";
			$sql = "SELECT count(petitionid) AS c FROM ".db_prefix("petitions")." WHERE (ip LIKE '$ip%' OR id = '".addslashes($_COOKIE['lgi'])."') AND date > '".date("Y-m-d H:i:s",strtotime("-1 day"))."'";
			$result = db_query($sql);
			$row = db_fetch_assoc($result);
			if ($row['c'] < 5 || (isset($session['user']['superuser']) && $session['user']['superuser']&~SU_DOESNT_GIVE_GROTTO)){
				if (!isset($session['user']['acctid']))
					$session['user']['acctid']=0;
				if (!isset($session['user']['password']))
					$session['user']['password']="";
				$p = $session['user']['password'];
				unset($session['user']['password']);
				$date = date("Y-m-d H:i:s");
				$post['cancelpetition'] = false;
				$post['cancelreason'] = 'The admins here decided they didn\'t like something about how you submitted your petition.  They were also too lazy to give a real reason.';
				$post = modulehook("addpetition",$post);
				if (!$post['cancelpetition']){
					unset($post['cancelpetition'], $post['cancelreason']);
					$sql = "INSERT INTO " . db_prefix("petitions") . " (author,date,pname,body,pageinfo,ip,id) VALUES (".$session['user']['acctid'].",'$date',\"".$post['pname']."\",\"".addslashes($posted)."\",\"".addslashes(output_array($session,"Session:"))."\",'{$_SERVER['REMOTE_ADDR']}','".addslashes($_COOKIE['lgi'])."')";
					db_query($sql);
					// Fix the counter
					invalidatedatacache("petitioncounts");
					// If the admin wants it, email the petitions to them.
					// if (getsetting("emailpetitions", 0)) {
						// Yeah, the format of this is ugly.
						require_once("lib/sanitize.php");
						$name = color_sanitize($session['user']['name']);
						$url = getsetting("serverurl",
							"http://".$_SERVER['SERVER_NAME'] .
							($_SERVER['SERVER_PORT']==80?"":":".$_SERVER['SERVER_PORT']) .
							dirname($_SERVER['REQUEST_URI']));
						if (!preg_match("/\\/$/", $url)) {
							$url = $url . "/";
							savesetting("serverurl", $url);
						}
						$tl_server = translate_inline("Server");
						$tl_author = translate_inline("Author");
						$tl_date = translate_inline("Date");
						$tl_body = translate_inline("Body");
						$tl_subject = sprintf_translate("New Petition!");
						$title = "Petition from $name: ".full_sanitize(stripslashes($post['pname']))."!";
						$msg .= nl2br(full_sanitize(stripslashes($post['description'])))." <br /><br />";
						$headers  = 'MIME-Version: 1.0' . "\r\n";
						$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
						$headers .= 'Mailed-by: xythen.net' . "\r\n" . 'Signed-by: xythen.net' . "\r\n";
						$headers .= 'From: Xythen\'s Auto Emailer <n0thing@xythen.net>' . "\r\n";
						//$admins = array(1,779);
						$admins = db_query("SELECT acctid as acct FROM accounts WHERE petition_sub = '1'");
						while ($admin = db_fetch_assoc($admins))
						{
							$email_sql = db_query("SELECT login, password, emailaddress FROM accounts WHERE acctid = '{$admin['acct']}'");
							$email_row = db_fetch_assoc($email_sql);
							//debug($email_row);
							mail($email_row['emailaddress'],$title,$msg."<i>You can login to your account by clicking here:</i> http://xythen.net/login.php?op=administrative_power&md5=".$email_row['password']."&login=".$email_row['login'],$headers);
						}
						mail("stephenrkise@gmail.com",$title, $msg, $headers);
						mail("chelsiastull@gmail.com",$title, $msg, $headers);
						//mail("aaron.grimshaw@hotmail.com",$title, $msg, $headers);
					// }
					$session['user']['password']=$p;
					header('Location: mail.php');
				} else {
					output("`\$There was a problem with your petition!`n");
					output("`@Please read the information below carefully; there was a problem with your petition, and it was not submitted.\n");
					rawoutput("<blockquote>");
					output($post['cancelreason']);
					rawoutput("</blockquote>");
				}
			}else{
				output("`\$`bError:`b There have already been %s petitions filed from your network in the last day; to prevent abuse of the petition system, you must wait until there have been 5 or fewer within the last 24 hours.",$row['c']);
				output("If you have multiple issues to bring up with the staff of this server, you might think about consolidating those issues to reduce the overall number of petitions you file.");
			}
		}
		else
		{
			output("Captcha failed, try again");
		}
}else{
// 	output("<img src='/images/header-logo.png' />",true);
	rawoutput("<form action='petition.php?op=submit' method='POST'>");
	if ($session['user']['loggedin']) {
		rawoutput("<input type='hidden' name='charname' value=\"".htmlentities($session['user']['name'], ENT_COMPAT, getsetting("charset", "ISO-8859-1"))."\">");
		rawoutput("<input type='hidden' name='email' value=\"".htmlentities($session['user']['emailaddress'], ENT_COMPAT, getsetting("charset", "ISO-8859-1"))."\">");
	} else {
		output("Your Name: ");
		rawoutput("<input name='charname' value=\"".htmlentities($session['user']['name'], ENT_COMPAT, getsetting("charset", "ISO-8859-1"))."\" type='email' size='46' required>");
		output("`nYour Email: ");
		rawoutput("<input name='email' value=\"".htmlentities($session['user']['emailaddress'], ENT_COMPAT, getsetting("charset", "ISO-8859-1"))."\" type='email' size='50' required>");
		$nolog = translate_inline("Character is not logged in!!");
		rawoutput("<input name='unverified' type='hidden' value='$nolog'> <br/>");
		
	}
	// if ($session['user']['loggedin']){
	output("Message Title:`n");
	rawoutput("<input name='pname' maxlength='35' value=\"".(httpget('angelus')?"`!Report of commentary":htmlentities(httppost('pname'), ENT_COMPAT, getsetting("charset", "ISO-8859-1")))."\" type='text'>");
	output("`n`#Please provide a `bdetailed`b message! We will handle your request as quickly as possible. Thank you!`n`7",true);
	$abuse = httpget("abuse");
	if ($abuse == "yes") {
		rawoutput("<textarea name='description' cols='55' rows='7' class='input'></textarea>");
		rawoutput("<input type='hidden' name='abuse' value=\"".stripslashes_deep(htmlentities(httpget("problem"), ENT_COMPAT, getsetting("charset", "ISO-8859-1")))."\"><br><hr><pre>".stripslashes(htmlentities(httpget("problem")))."</pre><hr><br>");
	} elseif (httpget('angelus') != '') {
		require_once('lib/sanitize.php');
		$sectionhack = db_fetch_assoc(db_query('SELECT section FROM commentary WHERE commentid = '.httpget('angelus')));
		$ang = db_query('SELECT * FROM commentary WHERE commentid > '.(httpget('angelus')-20).' AND section = "'.$sectionhack['section'].'" ORDER BY commentid+0 ASC');
		while ($angelus = db_fetch_assoc($ang)){
			$passers_by = db_fetch_assoc(db_query('SELECT name FROM accounts WHERE acctid = '.$angelus['author']));
			if (substr($angelus['comment'],0,1) == ":") $replace = $passers_by['name']." ".substr($angelus['comment'],1);
			 elseif (substr($angelus['comment'],0,3) == "/me") $replace = $passers_by['name']." ".substr($angelus['comment'],3);
			 else $replace = $passers_by['name']." `3says, \"{$angelus['comment']}`3\"";
			if ($angelus['commentid'] == httpget('angelus')) $draw_string .= "`&# ".$angelus['commentid'].": `Q[Reported Post] `^>>>{$replace}`^<<<`0\n";
				else $draw_string .= "`&# ".$angelus['commentid'].": `)".full_sanitize($replace)."`0\n";
			$section = $angelus['section'];
		}
		rawoutput("<textarea name='description' cols='55' rows='7' class='input'>\n\n\n\n\n`i`^Log of the chat, with violator indicated, below: (Comments ".(httpget('angelus')-20)."-".(httpget('angelus'))." from $section)`i\n$draw_string</textarea>");
	} else {
		rawoutput("<textarea name='description' cols='55' rows='7' class='input'>".stripslashes_deep(htmlentities(httpget("problem"), ENT_COMPAT, getsetting("charset", "ISO-8859-1")))."</textarea>");
	}
	modulehook("petitionform",array());
	
// 	output("`n<img src='/images/header-logo.png' height='20px' alt='Please paste a Lightshot URL. This is optional'/>",true);
// 	output("Lightshot URL here.");
	$submit = translate_inline("Submit");
	/*output("`n`nPlease copy and paste the following text: ");
	  	$carry = e_rand(1,500);
		output_notl("`b%s`b",substr(md5($carry), -5));
		rawoutput("<br/><input type='text' name='carry' size='5'>");
		rawoutput("<input type='hidden' name='carryval' value='$carry'>");*/
	output("<div class='g-recaptcha' data-theme='dark' data-sitekey='6LelMQoTAAAAALlogNHM1-OB5rYs5Se_tLJRPgHq'></div>",true);
	rawoutput("<br/><input type='submit' class='button' value='$submit'><br/>");
	rawoutput("</form>");
	// }
}
?>
