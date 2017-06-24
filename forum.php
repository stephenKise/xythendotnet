<?php
require_once('common.php');
require_once('lib/forum.php');
$op = httpget('op');
global $session;
output("<style>
	.forum_thread
	{
		padding-left: 3px;
		padding-right: 3px;
		padding-top: 10px;
		padding-bottom: 2px;
		min-height: 50px;
		border-top: 2px solid silver;
	}

	.thread_hr
	{
		width: 650px;
		border-top: 0px;
		opacity: .3;
	}
	</style>",true);
page_header('Xythen\'s Forum');
	addnav('Return to the Village','village.php');
if ($session['message'])
{
	output($session['message']);
	unset($session['message']);
}
if ($op == 'main')
{
	display_categories();
}
elseif ($op == 'view_cat')
{
	$cat = httpget('cat');
	$page = httpget('page');
	if (!$page) $page = 1;
	show_category_threads($cat,$page);
}
elseif ($op == 'view_thread')
{
	$cat = httpget('cat');
	$thread = httpget('thread');
	$page = httpget('page');
	if (!$page) $page = 1;
	display_thread($thread,$cat,$page);
}
elseif ($op == 'save_reply')
{
	global $session;
	require_once('lib/redirect.php');
	require_once('lib/alert.php');
	$cat = httpget('cat');
	$thread = httpget('thread');
	$all_post = httpallpost();
	debug($all_post);
	$sql = "INSERT INTO forum (title, category, status, acctid, posted, message) VALUES ('".addslashes(rawurldecode($thread))."', '".rawurldecode($cat)."', '1', '{$session['user']['acctid']}', '".date("Y-m-d H:i:s")."', '".addslashes($all_post['reply'])."')";
	db_query($sql);
	$grab_forum_subs = db_fetch_assoc(db_query("SELECT accts FROM forum_subs WHERE title = '".rawurldecode($thread)."'"));
	$subscribers = explode(',',$grab_forum_subs['accts']);
	foreach ($subscribers as $key => $val)
	{
		alert($val,'`@A new reply has been made to `2'.$thread.'`@!');
		debug($val);
	}
	redirect("forum.php?op=view_thread&thread=".rawurlencode(stripslashes($thread))."&cat=$cat");
	debug($sql);
}
elseif ($op == 'save_thread')
{
	require_once('lib/redirect.php');

	$all_post = httpallpost();
	debug($all_post);
	$sql = "INSERT INTO forum (acctid, title, category, status, posted, message) VALUES ('{$session['user']['acctid']}', '".rawurldecode(addslashes($all_post['thread']))."', '".httpget("cat")."', '1', '".date("Y-m-d H:i:s")."', '".addslashes($all_post['message'])."')";
	debug($sql);
	db_query($sql);
	$sql = "INSERT INTO forum_subs (title, accts) VALUES ('".addslashes($all_post['thread'])."', '')";
	db_query($sql);
	redirect('forum.php?op=view_thread&thread='.rawurlencode(addslashes($all_post['thread'])).'&cat='.httpget('cat'));
}
elseif ($op == 'create_thread')
{
	$cat = httpget('cat');
	debug($cat);
	output("`c<div style='width: 550px;' align='center'><form action='forum.php?op=save_thread&cat=$cat' method='post'>`<<input type='text' name='thread' size='25' placeholder='Thread name'>`<`><input type='submit' value='Post' align='right'>`>`n<textarea name='message' cols='65' rows='5' class='input' placeholder='What do you want to talk about?'></textarea></form></div>`c",true);
	addnav("","forum.php?op=save_thread&cat=$cat");
}
elseif ($op == 'create_category')
{
	check_su_access(SU_EDIT_USERS);
	output("<form action='forum.php?op=save_category' method='POST'><input type='text' name='category' size='34' placeholder='What category should we add?'>`n<textarea name='description' class='input' cols='27' rows='3' placeholder='Description of category.'></textarea>`n<input type='submit' value='Create'></form>",true);
	addnav("","forum.php?op=save_category");
}
elseif ($op == 'save_category')
{
	check_su_access(SU_EDIT_USERS);
	$all_post = httpallpost();
	debug($all_post);
	$get_csv = file_get_contents('./game_cache/forumcategories.csv');
	$category = trim(ucfirst(strtolower($all_post['category'])));
	$description = trim(ucfirst(strtolower($all_post['description'])));
	$put_csv = $get_csv."|$category:$description";
	debug($put_csv);
	file_put_contents('./game_cache/forumcategories.csv', $put_csv);
}
elseif ($op == 'sub_to_thread')
{
	global $session;
	$title = httpget('title');
	$cat = httpget('cat');
	debug($title);
	$grab_forum_subs = db_fetch_assoc(db_query("SELECT accts FROM forum_subs WHERE title = '$title'"));
	if ($grab_forum_subs['accts'] == "") $sql = "INSERT INTO forum_subs (title, accts) VALUES ('".rawurldecode($title)."', '{$session['user']['acctid']}')";
		else $sql = "UPDATE forum_subs SET accts = '".$grab_forum_subs['accts'].",".$session['user']['acctid']."'";
	db_query($sql);
	redirect("forum.php?op=view_thread&thread=$title&cat=$cat");
}
elseif ($op == 'delete_reply')
{
	$thread = httpget('thread');
	$cat = httpget('cat');
	$acctid = httpget('acctid');
	$id = httpget('id');
	if ($session['user']['acctid'] == $acctid || $session['user']['superuser'] & SU_MEGAUSER)
	{
		db_query("DELETE FROM forum WHERE id = $id AND acctid = $acctid");
		$session['message'] = "`c`i`\$You have deleted a post from ".rawurldecode($thread)."!`i`c`n";
	}
	redirect("forum.php?op=view_thread&thread=".rawurlencode(stripslashes($thread))."&cat=$cat");
}
categories_nav();	
if ($session['user']['superuser'] & SU_EDIT_USERS)
{
	addnav("Staff Functions");
	addnav("Create Category","forum.php?op=create_category");
	// addnav("Categories Manager","forum.php?op=manage_categories");
}
if (httpget('cat'))
{
	$forumpeepdata = @unserialize($session['user']['forumpeepdata']);
	$forumpeepdata[httpget('cat')] = date('Y-m-d H:i:s');
	$session['user']['forumpeepdata'] = serialize($forumpeepdata);
}
page_footer();
?>