<?php

define("OVERRIDE_FORCED_NAV",true);
require_once("common.php");
require_once("lib/http.php");

page_header("Recently Asked Questions");

$op = httpget('op');
$id = (int)httpget('topic');
$search = httppost('search');

$remove_these_words = array("a","is","are","we","where","what","the");

addnav("Actions");
addnav("View All","faq.php?op=view_all");
addnav("Return to Village","village.php");
if ($op != "") addnav("Go Back","faq.php");

if ($session['user']['superuser'] & SU_EDIT_USERS) output("[<a href='faq.php?op=create'>Create</a>]",true);
output("<center><form action='faq.php?op=search' method='POST'><input type='text' name='search' style='width: 180px;' placeholder='Search FAQ For Keywords:'><input type='submit' name='submit' value='Submit'></form></center>",true);
addnav("","faq.php?op=search");

if (!$op) faq_display($sql=db_query("SELECT faqid,author,archived,pname FROM faq ORDER BY faqid DESC LIMIT 5"));

if ($op == "search")
{
	$clean_search = array();
	$string_search = explode(" ",$search);
	foreach($string_search as $word)
	{
		if (in_array($word,$remove_these_words))
		{
			$search = str_replace($word," ",$search);
		}
		else
		{
			array_push($clean_search,$word);
		}
	}
	
	$faq_ids = array();
	foreach($clean_search as $clean_word)
	{
		$sql = db_query("SELECT faqid FROM faq WHERE pname LIKE '%".addslashes($clean_word)."%' OR body LIKE '%".addslashes($clean_word)."%' OR answer LIKE '%".addslashes($clean_word)."%'");
		while($row = db_fetch_assoc($sql))
		{
			array_push($faq_ids,$row['faqid']);
		}
		unset($sql);
		unset($row);
	}
	
	$search_ids = "";
	foreach($faq_ids as $id)
	{
		$search_ids .= "faqid = ".$id." OR ";
	}
	
	$search_ids = substr($search_ids, 0, -4);
	
	faq_display($sql=db_query("SELECT faqid,author,archived,pname,body FROM faq WHERE ".$search_ids));
}
	
if ($op == "view") faq_display($sql=db_query("SELECT faqid,author,archived,pname,body,answer FROM faq WHERE faqid = $id"),true);

if ($op == "view_all") faq_display($sql=db_query("SELECT faqid,author,archived,pname,body FROM faq ORDER BY faqid DESC"));

if ($op == "create") create_form();
	
if ($op == "delete"){
	db_query("DELETE FROM faq WHERE faqid = ".$id);
	header("Location: faq.php");
}

page_footer();

function faq_display($sql,$view=false){
	global $session;
	output("<fieldset style='border-color:#F4FA58; border-style: solid; border-radius: 5px;'><legend><b><font color='#FFFF00'>Recently Asked Questions:</font></b></legend>`n",true);
	if (!db_num_rows($sql)){
		output("`c`iSorry! Nothing found.`i`c");
	}else{
		while($row = db_fetch_assoc($sql)){
			$name = db_fetch_assoc(db_query("SELECT name FROM accounts WHERE acctid = ".$row['author']." LIMIT 1"));
			if (!$row['pname']) $row['pname'] = "`iNo Title`i";
			style_bar();
			if (!$view) output("`c`bTitle:`b <a href='faq.php?op=view&topic=".$row['faqid']."'>".$row['pname']."</a> - `bArchived On:`b ".$row['archived']."`c`n",true);
			else output("`c`bTitle:`b ".$row['pname']."</a> - `bArchived On:`b ".$row['archived']."`n`n`bQuestion:`b ".$row['body']."`n`n`bAnswer:`b ".$row['answer']."`c`n",true);
			if ($session['user']['superuser'] & SU_EDIT_USERS) output("`c[<a href='faq.php?op=delete&topic={$row['faqid']}' onClick='return confirm(\"Are you sure you want to delete this topic?\");'>Delete</a> | <a href='faq.php?op=create&topic={$row['faqid']}'>Edit</a>]`c",true);
			style_bar();
		}
	}
	output("</fieldset>",true);
}

function style_bar(){
	output("`c<span style='align:center;display:block;border:none;color:white;width:75%;height:1px;background:black;background: -webkit-gradient(radial, 50% 50%, 0, 50% 50%, 350, from(#FFFF00), to(transparent));'></span>`c`n",true);
}

function create_form(){
	global $session;
	$row = db_fetch_assoc(db_query("SELECT pname,body,answer FROM faq WHERE faqid='".httpget('topic')."'"));
	output("`b`c`QFAQ Entry`b`n`c");
	rawoutput("<form action='faq.php?op=create&accept=1' method='POST'>");
	rawoutput("<input type='text' size='70' id='title' name='title' placeholder=\"Title of Question...\" value='{$row['pname']}'><br>");
	rawoutput("<textarea class='input' name='question' cols='54' rows='7' placeholder='Question...'>{$row['body']}</textarea><br>");
	rawoutput("<textarea class='input' name='answer' cols='54' rows='7' placeholder='Answer...'>{$row['answer']}</textarea><br>");
	rawoutput("<input type='hidden' name='id' value='".httpget('topic')."'><br>");
	rawoutput("<input type='submit' class='button' value='Set'>");
	rawoutput("</form>");
	rawoutput("<script type='text/javascript'>document.getElementById('title').focus();</script>");
	addnav("", "faq.php?op=create&accept=1");
	
	if(httpget('accept')){
		(httppost('id') > 0) ? db_query("UPDATE faq SET author = '".$session['user']['acctid']."', pname = '".httppost('title')."', body = '".httppost('question')."', answer = '".httppost('answer')."' WHERE faqid = ".httppost('id')) : db_query("INSERT INTO faq (author,archived,pname,body,ip,answer) VALUES ('".$session['user']['acctid']."','".date("Y-m-d H:i:s")."','".rawurldecode(httppost('title'))."','".rawurldecode(httppost('question'))."','".$session['user']['lastip']."','".rawurldecode(httppost('answer'))."')");
		header('Location: faq.php');
	}		
}
	
?>