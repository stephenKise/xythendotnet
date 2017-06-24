<?php
function lovemail_getmoduleinfo(){
    $art = "http://akatsukicoders.tk/Arthur/";
    $info = array(
        "name"=>"Love Mail",
        "version"=>"1.1",
        "author"=>"<a href='$art'>`QArthur</a> for <a href='http://tynastera.com'>Tynastera</a>, help from HunterD",
        "category"=>"Administrative",
        "allowanonymous"=>false,
        "override_forced_nav"=>true,
        "download"=>"$art/modules/lovemail.zip",
        "description"=>"An archive where users can view or submit lovemail, pending approval of administrators",
        "settings"=>array(
            "Love Mail Archive,title",
                "delete"=>"Delete lovemail on uninstall?,bool|1",
                "text-t"=>"Text to display above Lovemail:,text|Here you will find the love, thanks, and adoration of our many awesome players. We love and thank you all too.",
                "news"=>"Should news be displayed when a player submits lovemail?,bool|1",
                "text-n"=>"If so, what should the news say?,text|Playername loves our realm!",
                "Playername will be replaced by the name of the player,note",
                "notification"=>"How should you be notified in the Grotto?,enum,1,Nav,2,Text,3,Both|2",
            "Stats Link,title",
                "dstats"=>"Display link to archive in character stats?,bool|1",
                "stathead"=>"Stat section to display link under:,text|Love Mail",
                "slink"=>"Title for Link to archive in character stats:,text|Archive",
                "name"=>"Name of Link to archive:,text|View",
            "Village Link,title",
                "dvillage"=>"Display link to archive in villages?,bool|1",
                "villagehead"=>"Nav section to display link under in villages:,text|Other",
            "Home Page Link,title",
                "dhome"=>"Display link to archive on home page?,bool|1",
                "homehead"=>"Nav section to display link under on home page:,text|Other Info",
            "Lodge Link,title",
                "dlodge"=>"Display link to archive in lodge?,bool|0",
                "lodgehead"=>"Nav section to display link under in lodge:,text|Other Info",
            "Shades Link,title",
                "dshades"=>"Display link to archive in shades?,bool|1",
                "shadeshead"=>"Nav section to display link under in shades:,text|Other",
        ),
        "prefs"=>array(
            "Love Mail,title",
                "editor"=>"Can this user approve/deny Love Mail?,bool|0",
        ),
    );
    return $info;
}

function lovemail_install(){
    if (!db_table_exists(db_prefix("lovemail"))){//Adding table for Love Mail
        db_query("CREATE TABLE ".db_prefix("lovemail")." (id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT, approved TINYINT(1), author VARCHAR(255),"
             ."authid MEDIUMINT UNSIGNED, text TEXT, datetime TIMESTAMP DEFAULT NOW(), PRIMARY KEY (id), KEY (author), KEY (authid)) ENGINE=MyISAM DEFAULT CHARSET=utf8");
        echo db_error();
        output("Adding Table");
    }
    module_addhook("charstats");
    module_addhook("village");
    module_addhook("footer-lodge");
    module_addhook("footer-home");
    module_addhook("shades");
    module_addhook("header-superuser");
    return true;
}

function lovemail_uninstall(){
    if (get_module_setting("delete")){//Only delete the table if admin says to.
        db_query("DROP TABLE ".db_prefix("lovemail"));
        echo db_error();
        output("Removing Table");
    }
    return true;
}

function lovemail_dohook($hookname,$args){
    switch($hookname){
        case "charstats":
            if (get_module_setting("dstats")) {//If admin wants a link in charstats, put it there.
                addcharstat(get_module_setting("stathead"));
                addcharstat(get_module_setting("slink"),"<a href='runmodule.php?module=lovemail&from=stats' onClick='"
                        .popup("runmodule.php?module=lovemail&from=stats").";return false;' target='_blank' align='center'>"
                        .get_module_setting("name")."</a>");
                addnav("","runmodule.php?module=lovemail&from=archive");
            }
            break;
        case "village":
        case "footer-lodge":
        case "footer-home":
        case "shades"://In all three cases, if admin wants a link there, add it.
            if (substr($hookname,0,1) == "f"){
                $hookname = substr($hookname,6);
            }
            if (get_module_setting("d$hookname")) {
                addnav(get_module_setting($hookname."head"));
                addnav("`vL`i`Ro`5v`i`b`%e`b `VM`va`b`Ri`b`5l","runmodule.php?module=lovemail&from=$hookname");
            }
            break;
        case "header-superuser":
            if (get_module_pref("editor")){//If user can approve/deny, give them link to it.
                $num = db_num_rows(db_query("SELECT * FROM ".db_prefix("lovemail")." WHERE approved IS NULL OR approved = 0"));
                if ($num > 0){
					addnav("Actions");
                    switch (get_module_setting("notification")){
                        case 1:
                            addnav(array("Approve Reviews `Q(%s)",$num),"runmodule.php?module=lovemail&from=grotto");
                            break;
                        case 2:
                            addnav("Approve Reviews","runmodule.php?module=lovemail&from=grotto");
                            output("`c`n`n`JThere are `b`Q%s`b `JLovemails awaiting approval.`0`n`n`c",$num);
                            break;
                        case 3:
                            addnav(array("Approve Reviews `Q(%s)",$num),"runmodule.php?module=lovemail&from=grotto");
                            output("`c`n`n`JThere are `b`Q%s`b `JLovemails awaiting approval.`0`n`n`c",$num);
                            break;
                    }
                }
            }
            break;
    }
    return $args;
}

function lovemail_run(){
	global $session;
    $from = httpget('from');
    $op = httpget('op');
    
    if (($from == "home" || !$from) && !get_module_setting("dhome") && !$session['user']['loggedin']){
        page_header("Please go away, spammer.");
        output("Go away, spammers are not appreciated here. Thank you, and have a nice day! :)");
        addnav("Spammer Bait","http://69.5.2.49/trap.html");
        page_footer();
    } elseif ($from == "grotto") { //If they want the editor, take them there.
        page_header("Love Mail Approval");
        require_once("lib/superusernav.php");
        require_once("lib/systemmail.php");
        superusernav();//Adds grotto and mundane links.
        addnav("View Reviews","runmodule.php?module=lovemail&from=editor");
        $approve = translate_inline("Approve");
        $deny = translate_inline("Deny");
        if ($op == "approve"){
			if (get_module_pref("editor")){
				$app = httpget("app");
				$uid = httpget('uid');
				$id = httpget("id");
				if ($app){
					db_query("UPDATE ".db_prefix("lovemail")." SET approved=1 WHERE id=$id");
					$n = db_fetch_assoc(db_query("SELECT name FROM ".db_prefix("accounts")." WHERE acctid = $uid"));
					addnews_for_user($uid,str_replace("Playername",$n['name'],get_module_setting("text-n")));
					
					// $insertcom = "/me `)approved lovemail from `&".$n['name']." `i(auto msg)`i";
					// injectcommentary("superuser", "", $insertcom);
					//require_once("stafflog.php");
					//stafflog("",$session['user']['acctid'],$uid,"{$session['user']['name']} `&approved lovemail from {$n['name']}","0");
					
					//db_query("UPDATE ".db_prefix("accounts")." SET donation = donation + 500 WHERE acctid = $uid");
					//systemmail($uid, "Review!", "`^The Staff accepted your review, and you got 500 DPs for it!");
				} else {
					db_query("DELETE FROM ".db_prefix("lovemail")." WHERE id=$id");
				}
				output("Review no. $id has been %s.",translate_inline($app ? "approved" : "denied"));
			}
        }
        $result = db_query("SELECT * FROM ".db_prefix("lovemail")." WHERE approved IS NULL OR approved = 0");//Get all unapproved lovemail.
        rawoutput("<table><tr class='trhead'><td>$approve</td><td>$deny</td><td>".translate_inline("Author")."</td><td>".translate_inline("Text")."</td></tr>");
        $i = 0;
		while ($row = db_fetch_assoc($result)){
            $link = "runmodule.php?module=lovemail&from=grotto&op=approve&id={$row['id']}&uid={$row['authid']}&app=";
            addnav("",$link."1");
            addnav("",$link."0");
			if ((substr_count($row['author'],"`b"))%2) $row['author'] .= "`b";
			if ((substr_count($row['author'],"`i"))%2) $row['author'] .= "`i";
			if ((substr_count($row['text'],"`b"))%2) $row['text'] .= "`b";
			if ((substr_count($row['text'],"`i"))%2) $row['text'] .= "`i";
            output_notl("
					<tr class='".($i%2?'trlight':'trdark')."'><td>[<a href='{$link}1'>$approve</a>]</td>"
                  ."<td>[<a href='{$link}0'>$deny</a>]</td>"
                  ."<td>{$row['author']}</td><td>{$row['text']}</td></tr>",TRUE);
			$i++;
        }
        rawoutput("</table>");
        page_footer();
    } elseif ($op == "subtract") {
		if (get_module_pref("editor")){
			popup_header("Disapproval");
			$id = httpget("id");
			db_query("UPDATE ".db_prefix("lovemail")." SET approved=0 WHERE id=".$id);
			output("Review id no. %s has been disapproved",$id);
			popup_footer();
		}
    } else {
        $header = translate_inline("Review Archive");
        if ($from == "stats"){
            popup_header($header);
        } else {
            page_header($header);
        }
        if (!$op){
            output("%s`n",get_module_setting("text-t"));
            rawoutput(translate_inline("Feel free to ")."<a href='runmodule.php?module=lovemail&from=$from&op=add'>".translate_inline("submit one")."</a>!<br><br>");
            $result = db_query("SELECT * FROM ".db_prefix("lovemail")." WHERE approved=1 ORDER BY datetime DESC");
            rawoutput("<table><tr class='trhead'>");
			if (get_module_pref("editor")) rawoutput("<td>".translate_inline("Options")."</td>");
			rawoutput("<td>".translate_inline("Author")."</td><td>".translate_inline("Text")."</td></tr>");
			$i = 0;
            while ($row = db_fetch_assoc($result)){
                rawoutput("<tr class='".($i%2?'trlight':'trdark')."'><td>");
                if (get_module_pref("editor")){
                    rawoutput("<a href='runmodule.php?module=lovemail&from=$from&op=subtract&id={$row['id']}' onClick='".popup("runmodule.php?module=lovemail&from=$from&op=subtract&id={$row['id']}").";return false;' target='_blank' align='center'>".translate_inline("Disapprove")."</a></td><td>");
                }
				if ((substr_count($row['author'],"`b"))%2) $row['author'] .= "`b";
				if ((substr_count($row['author'],"`i"))%2) $row['author'] .= "`i";
				if ((substr_count($row['text'],"`b"))%2) $row['text'] .= "`b";
				if ((substr_count($row['text'],"`i"))%2) $row['text'] .= "`i";
                output("%s",$row['author'],TRUE);
                rawoutput("</td><td>");
                output("%s",$row['text'],TRUE);
                rawoutput("</td></tr>");
				$i++;
            }
            rawoutput("</table>");
			output_notl("`n`n");
            addnav("Submit Review","runmodule.php?module=lovemail&from=$from&op=add");
        } else {
            require_once "lib/showform.php";
            $text = httppost("text");
            $add = httpget("add");
            if ($add && $text){
				if (strlen($text) >= 100){
					output("Thank you. Your review will be reviewed by our staff.");
					db_query("INSERT INTO ".db_prefix("lovemail")." (author,authid,text) VALUES ('{$session['user']['name']}',{$session['user']['acctid']},'$text')");
				} else {
					output("Your review needs to be greater than 100 characters.");
				}
            } elseif ($add) {
                output("You forgot to write us a message!");
            }
            addnav("Read Reviews","runmodule.php?module=lovemail&from=$from");
            $form = array(
                "Review Submission,title",
                "name"=>"Name:,viewonly",
                "text"=>"Message:,textarea",
            );
            $data = array("name"=>$session['user']['name']);
            $link = "runmodule.php?module=lovemail&from=$from&op=add&add=true";
            rawoutput("<form action='$link' method='post'>");
            showform($form,$data);
            rawoutput("</form>");
            addnav("",$link);
        }
        if ($from == "editor"){
            $from = "superuser";
        }
        addnav("Return to ".ucfirst($from),"$from.php");
        if ($from == "stats"){
            popup_footer();
        } else {
            page_footer();
        }
    }
}
?>