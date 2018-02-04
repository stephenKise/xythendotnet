<?php

function commentary_getmoduleinfo(){
$info = array(
"name"=>"Commentary Overhaul",
"author"=>"Maverick and `&`bStephen Kise`b",
"version"=>"1.0.1b",
"category"=>"Commentary",
"settings"=>array(
"Commentary Overhaul Settings,title",
"trivia_status"=>"Number of trivia questions to ask left:,viewonly",
"started_section"=>"Section we started trivia in:,viewonly",
"vote_time"=>"When was the vote started?,viewonly",
"voting_done"=>"Is voting done?,bool",
"vote_array"=>"Vote data:,text|a:0:{}",
"time_answered"=>"Window of the last answered question:,viewonly",
"question"=>"Trivia question asked:,viewonly",
"questions_answered"=>"How many questions have been answered",
"answered_time"=>"When was the question answered",
"max_answers"=>"How many questions should we give?,int|25",
),
"prefs"=>array(
"Commentary Overhaul Prefs,title",
"number_answered"=>"The last question this player got correct:,viewonly"
)
);
return $info;
}

function commentary_install(){
module_addhook("commentary");
module_addhook("viewcommentary");
module_addhook("everyhit-loggedin");
return TRUE;
}

function commentary_uninstall(){
return TRUE;
}

function commentary_dohook($hook,$args){
global $session;
switch($hook){
case "commentary":
$talk_col = get_module_pref('user_color','defaultcolor');
$emote_col = get_module_pref('user_emote','defaultcolor');
$line = $args["commentline"];
//		NO EMOTE CLAN CHAT

$fuckYouRespectTheTables = [
    "(╯ಠ益ಠ)╯︵ ┻━┻",
    "(ノಠ益ಠ)ノ彡┻━┻",
    "┻━┻ ︵﻿ ¯\(ツ)/¯ ︵ ┻━┻",
    "┻━┻ ︵ヽ(`Д´)ﾉ︵﻿ ┻━┻",
    "（╯°□°）╯︵ ┻━┻"
];
foreach ($fuckYouRespectTheTables as $key) {
    if (strpos($line, $key) != 0) {
        $args['commentline'] = '';
        db_query("INSERT INTO commentary (section, author, comment, postdate) VALUES ('globalooc', '{$session['user']['acctid']}', '$key', '".date("Y-m-d H:i:s")."')");
        db_query("INSERT INTO commentary (section, author, comment, postdate) VALUES ('globalooc', '1873', '┬─┬ノ(ಠ_ಠノ)', '".date("Y-m-d H:i:s")."')");
        invalidatedatacache('comments-globalooc');
    }
}

preg_match_all("/\/(\d+)d(\d+)(\+\d+|\-\d+|)/", $args['commentline'], $matches);
$max = count($matches[1]);
if ($max > 0) {
    for ($i=0;$i<$max;$i++) {
        if (is_numeric($matches[3][$i]) && $matches[3][$i] != 0) {
            $modifier = $matches[3][$i];
        }
        $string = "( ";
        $total = 0;
        for ($die=0;$die<$matches[1][$i];$die++) {
            $rand = rand(1, $matches[2][$i]);
            $total += $rand;
            $string .= "$rand ";
        }
        $string .= "$modifier )";
        $total += $modifier;
        $replace = "<span class='commentaryRoll' data-roll='$string' data-dice='{$matches[1][$i]}' data-sides='{$matches[2][$i]}'>$total</span>";
        $args['commentline'] = str_replace($matches[0][$i], $replace, $args['commentline']);
    }
}

if(strpos($line, "/c ") == "0" && strpos($line, "/c ") !== false && ($session['user']['clanid']>0)){
$post .= $talk_col.preg_replace('/\/c /','',$line);
db_query("INSERT INTO commentary (section,author,comment,postdate) VALUES ('clan-".$session['user']['clanid']."',".$session['user']['acctid'].",'".addslashes($post)."','".date("Y-m-d H:i:s")."')");
unset($args['commentline']);
}
if(strpos($line, "/g ") == "0" && strpos($line, "/g ") !== false && ($session['user']['clanid']>0)){
$post .= $talk_col.preg_replace('/\/g /','',$line);
db_query("INSERT INTO commentary (section,author,comment,postdate) VALUES ('clan-".$session['user']['clanid']."',".$session['user']['acctid'].",'".addslashes($post)."','".date("Y-m-d H:i:s")."')");
unset($args['commentline']);
}
//		EMOTE CLAN CHAT
if(strpos($line, ":c ") == 0 && strpos($line, ":c ") !== false && ($session['user']['clanid']>0)){
$post .= $emote_col.preg_replace('/\:c /','',$line);
db_query("INSERT INTO commentary (section,author,comment,postdate) VALUES ('clan-".$session['user']['clanid']."',".$session['user']['acctid'].",':".addslashes($post)."','".date("Y-m-d H:i:s")."')");
unset($args['commentline']);
}
if(strpos($line, ":g ") == 0 && strpos($line, ":g ") !== false && ($session['user']['clanid']>0)){
$post .= $emote_col.preg_replace('/\:g /','',$line);
db_query("INSERT INTO commentary (section,author,comment,postdate) VALUES ('clan-".$session['user']['clanid']."',".$session['user']['acctid'].",':".addslashes($post)."','".date("Y-m-d H:i:s")."')");
unset($args['commentline']);
}
//		NO EMOTE OOC CHAT
if(strpos($line, "/ooc ") == 0 && strpos($line, "/ooc ") !== false){
$post .= $talk_col.preg_replace('/\/ooc /','',$line);
// 			debug("INSERT INTO commentary (section,author,comment,postdate) VALUES ('globalooc',".$session['user']['acctid'].",'".addslashes($post)."','".date("Y-m-d H:i:s")."')");
db_query("INSERT INTO commentary (section,author,comment,postdate) VALUES ('globalooc',".$session['user']['acctid'].",'".addslashes($post)."','".date("Y-m-d H:i:s")."')");
unset($args['commentline']);
}
//		EMOTE OOC CHAT
if(strpos($line, ":ooc ") !== false && strpos($line, ":ooc ") == 0){
$post .= $emote_col.preg_replace('/\:ooc /','',$line);
db_query("INSERT INTO commentary (section,author,comment,postdate) VALUES ('globalooc',".$session['user']['acctid'].",':".addslashes($post)."','".date("Y-m-d H:i:s")."')");
unset($args['commentline']);
}

if ($line == "/nvm" || $line == "nvm" || $line == "NVM" || $line == "RMV" || $line == "rmv"){
//		if (strpos($line,"nvm") !== false){
unset($args['commentline']);
$mylast = db_fetch_assoc(db_query("SELECT commentid,postdate FROM commentary WHERE section = '{$args['commentsection']}' AND author = ".$session['user']['acctid']." AND deleted = 0 ORDER BY postdate+0 DESC LIMIT 0,1"));
// 			$args['commentline'] = "My last post was ".(time()-strtotime($mylast['postdate']))." seconds ago in the {$args['commentsection']} section.";
if (time()-strtotime($mylast['postdate']) < 60){
db_query("UPDATE commentary SET deleted = 1 WHERE commentid = ".$mylast['commentid']);
invalidatedatacache('comments-'.$args['commentsection']);
invalidatedatacache('comments-globalooc');
}
// 				else output("`c`i`LYour last comment was more than a minute old!`i`c`n");
}

if ($line == "/timer" && $session['user']['superuser'] & SU_EDIT_USERS){
//$args['commentline'] = "Timer has been toggled.";
if (getsetting("post_timer",'0') == 0)
{
$args['commentline'] = "Timer has been disabled";
savesetting("post_timer",1);
}
else
{
$args['commentline'] = "Timer has been enabled.";
savesetting("post_timer",0);
}
/*	else
{
$args['commentline'] = 'error';
}*/
}

if (substr($line,0,5) == '/roll')
{
require_once('lib/dicebag.php');
require_once('lib/systemmail.php');
$line = trim(str_replace('/roll','',$line));
$args['commentline'] = $line;
if ($line != '')
{
$params = explode(' ',$line);
if (count($params) != 1) $roll = roll($params[0],$params[1]);
else $roll = roll($params[0]);
}
else
{
$roll = roll();
}
debug($roll);
$rolldata = '';
foreach ($roll['dice'] as $die => $result) {
$rolldata .= ", $result";
}
$rolldata = substr($rolldata, 2);
$comment = ":`)rolled a <span class='commentaryRoll' data-roll='$rolldata' data-dice='{$roll['amt']}' data-sides='{$roll['sides']}'>{$roll['total']}</span>";
db_query("INSERT INTO commentary (section, author, comment, postdate) VALUES ('{$args['commentsection']}', '{$session['user']['acctid']}', '" . addslashes($comment) . "', '" . date('Y-m-d H:i:s') . "');");
db_query("INSERT INTO commentary (section, author, comment, postdate) VALUES ('blackhole', '{$session['user']['acctid']}', 'Valid Roll!', '" . date('Y-m-d H:i:s') . "');");
$args['commentline'] = '';
invalidatedatacache('comments-blackhole');
invalidatedatacache("comments-{$args['commentsection']}");
}










































if ($line == "/edit")
{
global $session;
require_once('lib/redirect.php');
$ret = URLEncode($_SERVER['REQUEST_URI']);
$sql = db_query("SELECT * FROM commentary WHERE author = {$session['user']['acctid']} ORDER BY postdate DESC LIMIT 0,1");
$row = db_fetch_assoc($sql);
unset($args['commentline']);
redirect("runmodule.php?module=commentary&op=edit&commid={$row['commentid']}&ret=$ret");
}
if (substr($line,0,13) == "/start_trivia" && $session['user']['superuser'] & SU_EDIT_USERS)
{
unset($args['commentline']);
if (substr($line,14) != "")
$amt_ans = (substr($line,14)+1);
else
$amt_ans = 11;
set_module_setting("max_answers",$amt_ans);
set_module_setting("trivia_status",1);
set_module_setting("started_section",$args['commentsection']);
set_module_setting('answered_time','not_answered');
db_query("INSERT INTO commentary (section,author,comment,postdate) VALUES ('".get_module_setting('started_section')."','0','".addslashes('/game `LTriviaBot`7: `@Trivia has started, with `^'.($amt_ans-1).' `@questions to answer! `7`iJust a reminder that emotes after answers cannot be read by TriviaBot. Feel free to submit more trivia questions and report errors when they are noticed!`i')."','".date("Y-m-d H:i:s")."')");
$args['commentline'] = '';
invalidatedatacache("comments-".$args['section']);
invalidatedatacache("comments-or11");

$trivia_list = file_get_contents('modules/trivia_questions.csv');
$trivia_list = explode("\n",$trivia_list);
$trivia_list = array_filter($trivia_list,'trim');
$display = $trivia_list[rand(0,(count($trivia_list)-1))];
$selected_answer = explode(':',$display);
$selected_answer[0] = htmlspecialchars($selected_answer[0],ENT_QUOTES,'utf-8');
set_module_setting('question',$selected_answer[1]);
set_module_setting('trivia_status',1);
db_query("UPDATE module_userprefs SET value='0' WHERE modulename='commentary' AND setting='number_answered'");
db_query("INSERT INTO commentary (section,author,comment,postdate) VALUES ('".get_module_setting('started_section')."','0','".addslashes('/game `LTriviaBot`l: `bQuestion 1: '.$selected_answer[0].'`b')."','".date("Y-m-d H:i:s")."')");
}
if (get_module_setting('trivia_status') != 0 && $args['commentsection'] == get_module_setting('started_section') && get_module_pref("number_answered") != get_module_setting("trivia_status"))
{
$question = get_module_setting('question');
$initial_answered_time = strtotime(get_module_setting('answered_time'));
if (levenshtein(trim($question,' '), trim(ucfirst(strtolower($args['commentline'])),' '))<=2)
{
if ($initial_answered_time == 0) set_module_setting('answered_time', date('Y-m-d H:i:s'));
$args['commentline'] = ':`^ has guessed the right answer and received two points!';
increment_module_pref("points",2,"contest_room");
set_module_pref("number_answered",get_module_setting("trivia_status"));
}
}
break;
case "everyhit-loggedin":
$check_for_five = (strtotime(date('Y-m-d H:i:s'))-strtotime(get_module_setting('answered_time')));
// debug(get_module_setting('question'));
if ($check_for_five > 10 && get_module_setting('answered_time') != "not_answered" && get_module_setting('trivia_status') != 0)
{
$vote_array = get_module_setting('vote_array');
$vote_array = unserialize($vote_array);
$val = max($vote_array);
$section = array_search($val,$vote_array);
$section = "Misc";
$trivia_list = file_get_contents('modules/trivia_questions.csv');
$trivia_list = explode("\n",$trivia_list);
$trivia_list = array_filter($trivia_list,'trim');
$category_list = array();
foreach($trivia_list as $key => $val)
{
$soap_items = explode('|',$val);
if ($soap_items[0] == $section)
{
array_push($category_list, $soap_items[1]);
}
}
$display = $trivia_list[rand(0,(count($trivia_list)-1))];
$selected_answer = $category_list[rand(0,(count($category_list)-1))];
$selected_answer[0] = htmlspecialchars($selected_answer[0],ENT_QUOTES,'utf-8');
$propose_question = explode(':',$display);
$how_many_answered = abs(9-get_module_setting('trivia_status'));
debug($how_many_answered);
if ((get_module_setting('trivia_status')+1) < get_module_setting("max_answers")) set_module_setting('trivia_status',(get_module_setting('trivia_status')+1));
else set_module_setting('trivia_status',0);
set_module_setting('question',$propose_question[1]);
set_module_setting('answered_time','not_answered');
debug($propose_question);
if (get_module_setting('trivia_status') != 0)
{
//db_query("INSERT INTO commentary (section,author,comment,postdate) VALUES ('".get_module_setting('started_section')."','0','".addslashes('/game `LTriviaBot`l: Congratulations, to those who answered correctly! Your points have been tallied!')."','".date("Y-m-d H:i:s")."')");
db_query("INSERT INTO commentary (section,author,comment,postdate) VALUES ('".get_module_setting('started_section')."','0','".addslashes('/game `LTriviaBot`l: `bQuestion '.get_module_setting('trivia_status').': '.$propose_question[0].'`b')."','".date("Y-m-d H:i:s")."')");
}
else
{
db_query("INSERT INTO commentary (section,author,comment,postdate) VALUES ('".get_module_setting('started_section')."','0','".addslashes('/game `LTriviaBot`l:`7 Congratulations everyone who won points, and thank you for participating in this session of trivia!')."','".date("Y-m-d H:i:s")."')");
}

}
break;
}
return $args;
}

function commentary_run()
{
global $session;
$op = httpget('op');
require_once("lib/forms.php");
page_header('Commentary Functions');
switch ($op)
{
case "edit":
$all_get = httpallget();
//return link
$all_get['ret'] = str_replace('comment=1','',$all_get['ret']);
$all_get['ret'] = str_replace('/','',$all_get['ret']);
$ret = urlencode($all_get['ret']);
addnav("Go back",$all_get['ret']);

//edit comm
$sql = db_query("SELECT * FROM commentary WHERE commentid = {$all_get['commid']}");
$row = db_fetch_assoc($sql);
debug($row);
output("<form action='runmodule.php?module=commentary&op=save_edit' method='POST'>",true);
//commid={$all_get['commid']}&section={$all_get['section']}
$row['comment'] = htmlentities($row['comment'], ENT_COMPAT, getsetting("charset", "ISO-8859-1"));
$row['comment'] = stripslashes($row['comment']);
output("<input type='hidden' name='ret' value='$ret'>",true);
output("<input type='hidden' name='commid' value='{$all_get['commid']}'>",true);
output("<input type='hidden' name='section' value='{$row['section']}'>",true);
rawoutput("<textarea class='input' name='comment' cols='90' rows='7'>{$row['comment']}</textarea>");
output("<br/><input type='submit' value='Submit'>",true);
output("</form>",true);
addnav("","runmodule.php?module=commentary&op=save_edit");
break;
case "save_edit":
$all_post = httpallpost();
debug($all_post);
addnav("Go back",urldecode($all_post['ret']));
$first_four = substr($all_post['comment'],0,4);
if ($first_four == ":ooc") debug('globalooc');
else debug('not globalooc');
db_query("UPDATE commentary SET deleted = 1 WHERE commentid = {$all_post['commid']}");
if ($first_four != ":ooc" && $first_four != "/ooc")
db_query("INSERT INTO commentary (section,author,comment,postdate) VALUES ('{$all_post['section']}','{$session['user']['acctid']}','{$all_post['comment']}','".date("Y-m-d H:i:s")."')");
else
db_query("INSERT INTO commentary (section,author,comment,postdate) VALUES ('globalooc','{$session['user']['acctid']}','".substr($all_post['comment'],4)."','".date("Y-m-d H:i:s")."')");
invalidatedatacache("comments-".$all_post['section']);
invalidatedatacache("comments-globalooc");
require_once('lib/redirect.php');
redirect(urldecode($all_post['ret']));
break;
}
page_footer();
}
