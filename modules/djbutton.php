<?php
function djbutton_getmoduleinfo(){
    $info = array(
        "name" => "Button for DJ's",
        "version" => "1.1",
        "author" => "`i`b`&Xpert`i`b`2, fixes by `&Senare, list added by `i`)Ae`7ol`&us`i`0",
        "category" => "Administrative",
		"override_forced_nav" => true,
        "settings" => array(
            "DJ Button - Settings, title",
            "acctids" => "What ACCTID's have access to the DJ button (put a comma in between each acctid)?,text|",
            "on" => "Is the DJ Button currently turned on?,bool|0",
            "req" => "Are requests currently turned on?,bool|1",
			"dj" => "Current DJ,int",
        ),
		"prefs" => array(
			"DJ Button - Prefs, title",
            "comments" => "Comments about user,text|",
		),
    );
    return $info;
}

function djbutton_install(){
    module_addhook_priority("village-desc", 1);
    module_addhook("stafflist");
    return true;
}

function djbutton_uninstall(){
    return true;
}

function djbutton_dohook($hookname, $args){
    global $session;
	
	switch ($hookname){
		case "village-desc":
			$on = get_module_setting("on");
			$req = get_module_setting("req");
			$acctids = explode(",", get_module_setting('acctids'));
		   
			if ($on == 1){
				rawoutput("<center><big>");
				output("`n`QThe Radio is Currently: `^`bON`b`n");
				rawoutput("</big></center>");
			}
			
			if (in_array($session['user']['acctid'], $acctids)) {
				addnav("Other");
				if ($on){
					addnav("`b`&Turn Radio Off`b", "runmodule.php?module=djbutton&op=off");
					if ($req) addnav("`b`&Turn Requests Off`b", "runmodule.php?module=djbutton&op=reqoff");
					else addnav("`b`&Turn Requests On`b", "runmodule.php?module=djbutton&op=reqon");
				} else {
					addnav("`b`&Turn Radio On`b", "runmodule.php?module=djbutton&op=on");
				}
			}
		break;
		case "stafflist":
			dj_list();
		break;
	}

    return $args;
}

function djbutton_run(){
    global $session;
	$op = httpget("op");
    switch ($op) {
		case "reqon":
			set_module_setting("req", 1);
            addnews("`b`QXythen Radio Requests are now `\$ON`Q!`b");
            redirect("village.php");
		break;
		case "reqoff":
			set_module_setting("req", 0);
            redirect("village.php");
		break;
        case "on":
            set_module_setting("on", 1);
            set_module_setting("dj", $session['user']['acctid']);
            addnews("`b`QXythen Radio is now `\$ON`Q!`b");
            redirect("village.php");
		break;
        case "off":
            set_module_setting("on", 0);
			set_module_setting("dj", "");
            redirect("village.php");
		break;
		case "comment":
			require_once("lib/sanitize.php");
			require_once("lib/showform.php");
			
			$id = httpget('id');
			$del = httpget('del');
			$post = httppost('comment');
			$acctids = explode(",", get_module_setting('acctids'));
			
			if (!in_array($id, $acctids)){
				popup_header("Oops!");
				output_notl("`n`n`c");
				output("`\$User is not a DJ!`0");
				output_notl("`c`n`n");
				popup_footer();
				break;
			}
			
			if ($post){
				require_once("lib/systemmail.php");
				$comments = get_module_pref("comments", "djbutton", $id);
				$comments = unserialize($comments);
				if (!is_array($comments)) $comments = array();
				$comments[] = $session['user']['acctid']."|^|".str_replace("|^|", "", $post)."|^|".time();
				set_module_pref("comments", serialize($comments), "djbutton", $id);
				if ($id <> $session['user']['acctid']) systemmail($id, array("DJ Comment!"), array("`&{$session['user']['name']}`7 just posted a comment about your DJ'ing!`n`nYou can view this comment by viewing the DJ List (under the Staff list), and clicking the link."));
				output("`n`@Comment added!`n");
			}
			
			if ($del){
				if ($session['user']['superuser'] & SU_EDIT_USERS || $session['user']['acctid'] == $id){
					$del -= 1;
					$comments = get_module_pref("comments", "djbutton", $id);
					$comments = unserialize($comments);
					if (!is_array($comments)) $comments = array();
					unset($comments[$del]);
					set_module_pref("comments", serialize($comments), "djbutton", $id);
					header("Location: runmodule.php?module=djbutton&op=comment&id=$id");
				}
			}
			
			$sql = "SELECT name FROM ".db_prefix("accounts")." WHERE acctid = $id";
			$res = db_query($sql);
			$row = db_fetch_assoc($res);
			
			$name = $row['name'];
			$sanname = sanitize($name);
			$comname = str_replace(",", "&#44;", $sanname);
			
			popup_header(array("Comments for DJ: %s", $sanname));
			output_notl("`n");
			
			$form = array(
				"Write A Comment about $comname,title",
				"dj" => "DJ Name,viewonly",
				"comment" => "Comment,textarea",
			);
			$data = array(
				"dj" => $name,
				"comment" => "",
			);
			rawoutput("<form action='runmodule.php?module=djbutton&op=comment&id=$id' method='post'>");
			showform($form, $data);
			rawoutput("</form>");
			
			$comments = get_module_pref("comments", "djbutton", $id);
			$comments = unserialize($comments);
			if (!is_array($comments)) $comments = array();
			$comments = array_reverse($comments, true);
			foreach ($comments as $key => $info){
				list ($user, $comment, $time) = explode("|^|", $info);
				$sql = "SELECT name FROM ".db_prefix("accounts")." WHERE acctid = $user";
				$res = db_query($sql);
				$row = db_fetch_assoc($res);
				if ($session['user']['superuser'] & SU_EDIT_USERS || $session['user']['acctid'] == $id)
				output_notl("<a href='runmodule.php?module=djbutton&op=comment&id=$id&del=".($key+1)."'>[Del]</a>",true);
				output("`&%s`&: (%s) %s`&`n", $row['name'], date("M j, 'y", $time), stripslashes($comment));
			}
			output("`n");
			
			popup_footer();
		break;
    }
}

function dj_list(){	
	global $session;
	
	$current = get_module_setting("dj");
	$djs = get_module_setting("acctids");
	$num = count(explode(",", $djs));
	
	$sql = "SELECT acctid, name, login, laston, loggedin FROM ".db_prefix("accounts")." WHERE acctid IN ($djs)";
	$res = db_query($sql);
	
	output("`n`n`c`b`@DJ List`0`b`c`n`n");
	
	if ($djs){
		$hname = translate_inline("Name");
		$hlast = translate_inline("Last Online");
		$hon = translate_inline("Online");
		$hdj = translate_inline("DJing Now?");
		$hwc = translate_inline("Comments");
		$djyes = translate_inline("`@Yes`0");
		$djno = translate_inline("`\$No`0");
		$writemail = translate_inline("Write Mail");
		
		rawoutput("<center>");
		rawoutput("<table border='0' cellpadding='2' cellspacing='1' bgcolor='#999999'>");
		rawoutput("<tr class='trhead'><td>$hname</td><td>$hlast</td><td>$hon</td><td>$hdj</td><td>$hwc</td></tr>");
		for($i = 0; $i < $num; $i++){
			$row = db_fetch_assoc($res);
			rawoutput("<tr class='".($i%2?"trdark":"trlight")."'><td>");
			if ($session['user']['loggedin']){
				rawoutput("<a href=\"mail.php?op=write&to=".rawurlencode($row['login'])."\" target=\"_blank\" onClick=\"".popup("mail.php?op=write&to=".rawurlencode($row['login'])."").";return false;\">");
				rawoutput("<img src='images/newscroll.GIF' width='16' height='16' alt='$writemail' border='0'></a>");
				$link = "bio.php?char=".rawurlencode($row['login'])."&ret=".urlencode($_SERVER['REQUEST_URI']);
				rawoutput("<a href='$link'>");
				addnav("","$link");
			}
			output_notl("`&%s`0", $row['name']);
			if ($session['user']['loggedin']) rawoutput("</a>");
			rawoutput("</td><td>");	
				$laston = relativedate($row['laston']);
				output_notl("%s", $laston);
			rawoutput("</td><td align='center'>");
				$loggedin = (date("U") - strtotime($row['laston']) < getsetting("LOGINTIMEOUT",900) && $row['loggedin']);
				output_notl("%s",($loggedin?$djyes:$djno));
			rawoutput("</td><td align='center'>");	
				output_notl("%s",($row['acctid']==$current?$djyes:$djno));
			rawoutput("</td><td>");
				output_notl("[<a href='runmodule.php?module=djbutton&op=comment&id={$row['acctid']}' target='_blank'>Write Comment</a>]", true);
			rawoutput("</td></tr>");
		}
		rawoutput("</table></center>");
	} else {
		output("`c`@This server appears to not have any DJs.");
	}
}
?>