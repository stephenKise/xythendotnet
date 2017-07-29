<?php
// addnews ready
// translator ready
// mail ready
define("ALLOW_ANONYMOUS",true);
define("OVERRIDE_FORCED_NAV",true);
require_once("common.php");
require_once("lib/nltoappon.php");
require_once("lib/http.php");
require_once("lib/motd.php");
tlschema("motd");

$op = httpget('op');
$id = httpget('id');

popup_header("Xythen's Notification Center ");

if ($op == "changes") $session['user']['seenupdates'] = 1;
if ($op == "polls") $session['user']['prefs']['lastpollview'] = date("Y-m-d H:i:s");

$motd = "Important Messages";
$polls = "Site Polls";
$changes = "Game Updates ";

/* MOTD */
rawoutput("<center>[ <a href='motd.php'>$motd</a> | ");

/* POLLS */
$sql = "SELECT motddate FROM " . db_prefix("motd") . " WHERE motdtype = 1 ORDER BY motditem DESC LIMIT 1";
$res = db_query($sql);
$row = db_fetch_assoc($res);
// if (strtotime($row['motddate']) > strtotime($session['user']['prefs']['lastpollview']) && $session['user']['loggedin'])
// rawoutput("<a href='motd.php?op=view_current_poll' style='color:red;'>$polls  <img src='images/star.png' alt='new' valign='-2px' /></a> | ");
// else
rawoutput("<a href='motd.php?op=view_current_poll'>$polls</a> | ");

/* CHANGELOG */
if ($session['user']['seenupdates'] != 1 && $session['user']['loggedin'])
rawoutput("<a href='motd.php?op=changes' style='color:red;'>$changes <img src='images/star.png' alt='new' valign='-2px' /></a> ] </center><br/><br/>");
else
rawoutput("<a href='motd.php?op=changes'>$changes</a> ] </center><br/><br/>");

if ($op=="vote"){
	$motditem = httppost('motditem');
	$choice = httppost('choice');

	$sql = "SELECT motddate FROM " . db_prefix("motd") . " WHERE motditem = '$motditem'";
	$res = db_query($sql);
	$row = db_fetch_assoc($res);

	$sql = "DELETE FROM " . db_prefix("pollresults") . " WHERE motditem='$motditem' AND account='{$session['user']['acctid']}'";
	db_query($sql);

	$givegemsforpoll = true;
	if (db_affected_rows() > 0) $givegemsforpoll = false;
	if (strtotime("now") - strtotime($row['motddate']) >= 604800) $givegemsforpoll = false;

	$sql = "INSERT INTO " . db_prefix("pollresults") . " (choice,account,motditem) VALUES ('$choice','{$session['user']['acctid']}','$motditem')";
	db_query($sql);

	if ($givegemsforpoll){
		$gems = 500;
		$sql = "UPDATE ".db_prefix('accounts')." SET gems = gems + $gems WHERE acctid = {$session['user']['acctid']}";
		db_query($sql);
	}

// 	invalidatedatacache("poll-$motditem");
	header("Location: motd.php?op=polls");
	exit();
}
if ($op == "add" || $op == "del")  {
	if ($session['user']['superuser'] & SU_POST_MOTD) {
// 		require_once('lib/poles.php');
// 		create_polls(10);
 		if ($op == "add") motd_form($id);
// 		elseif ($op == "addpoll") motd_poll_form($id);
 		elseif ($op == "del") motd_del($id);
	} else {
		if ($session['user']['loggedin']){
			$session['user']['experience'] = round($session['user']['experience']*0.9,0);
			addnews("%s was penalized for attempting to defile the gods.",$session['user']['name']);
			output("You've attempted to defile the gods.  You are struck with a wand of forgetfulness.  Some of what you knew, you no longer know.");
			saveuser();
		}
	}
}
if ($op == "" || $op == "polls") {
if ($session['user']['loggedin']){
	$motdtype = ( $op ? "1" : "0" );

	if ($session['user']['superuser'] & SU_POST_MOTD) {
		$addm = "Add MoTD";
		$addp = "Add Poll";
		rawoutput(" [ <a href='motd.php?op=add'>$addm</a> ]<br/><br/>");
	}

	$count = getsetting("motditems", 5);
	$newcount = httppost("newcount");
	if (!$newcount || (!httppost('proceed') && !httppost('proceedp'))) $newcount = 0;
	if (httppost('proceedp')) $newcount -= (2 * $count);
	$newcountp = $newcount + $count;

	$c_sql = "SELECT count(*) AS total FROM ".db_prefix("motd")." WHERE motdtype = $motdtype";
	$c_res = db_query($c_sql);
	$c_row = db_fetch_assoc($c_res);
	$total = $c_row['total'];

	$m = httppost("month");

	$m>""?$sql = "SELECT " . db_prefix("motd") . ".*,name AS motdauthorname FROM " . db_prefix("motd") . " LEFT JOIN " . db_prefix("accounts") . " ON " . db_prefix("accounts") . ".acctid = " . db_prefix("motd") . ".motdauthor WHERE motdtype = $motdtype AND motddate >= '{$m}-01' AND motddate <= '{$m}-31' ORDER BY motddate DESC":$sql = "SELECT " . db_prefix("motd") . ".*,name AS motdauthorname FROM " . db_prefix("motd") . " LEFT JOIN " . db_prefix("accounts") . " ON " . db_prefix("accounts") . ".acctid = " . db_prefix("motd") . ".motdauthor WHERE motdtype = '$motdtype' ORDER BY motddate DESC LIMIT $newcount,$count";
	$result = db_query($sql);

	while ($row = db_fetch_assoc($result)) {
		if (!isset($session['user']['lastmotd'])) $session['user']['lastmotd']=0;
		if ($row['motdauthorname']=="") $row['motdauthorname']="`@Xythen Staff`0";
		if ($row['motdtype']==0){
			motditem($row['motdtitle'], $row['motdbody'],
					$row['motdauthorname'], $row['motddate'],
					$row['motditem']);
		} else {
			pollitem($row['motditem'], $row['motdtitle'], $row['motdbody'],
					$row['motdauthorname'],$row['motddate'], $row['motditem']);
			if ($session['user']['superuser'] & SU_POST_MOTD){
				output("List of voters: ");
				$voters = db_query("SELECT account FROM pollresults WHERE motditem = ".$row['motditem']);
				$max = db_num_rows($voters);
				$i = 0;
				while ($acc = db_fetch_assoc($voters)){
					$i++;
					$voter = db_fetch_assoc(db_query("SELECT name FROM accounts WHERE acctid = ".$acc['account']));
					if ($i < $max) output($voter['name']."`&, ");
						else output($voter['name']."`&.");
				}
				rawoutput("<hr>");
			}
		}
	}
	$result = db_query("SELECT mid(motddate,1,7) AS d, count(*) AS c FROM ".db_prefix("motd")." WHERE motdtype = $motdtype GROUP BY d ORDER BY d DESC");
	rawoutput("<form action='motd.php".($op?"?op=polls":"")."' method='POST'>");
	$archive = ($op?"Poll":"MoTD");
	output("%s Archives:", $archive);
	rawoutput("<select name='month' onChange='this.form.submit();' >");
	rawoutput("<option value=''>--Current--</option>");
	while ($row = db_fetch_assoc($result)){
		$time = strtotime("{$row['d']}-01");
		$mn = date("M",$time);
		rawoutput ("<option value='{$row['d']}'".(httppost("month")==$row['d']?" selected":"").">$mn".date(", Y",$time)." ({$row['c']})</option>");
	}

	rawoutput("</select>".tlbutton_clear());
	rawoutput("<input type='hidden' name='newcount' value='$newcountp'>");
	if (!$m){
		if ($newcount > 0) rawoutput("<input type='submit' value='&lt;' name='proceedp' class='button'>");
		if ($newcountp < $total) rawoutput("<input type='submit' value='&gt;' name='proceed' class='button'>");
	}
	rawoutput("</form>");

	output("`n`n");
	}else{
	output("`b`c`QYou must be logged in to view this information.`c`b");
	}
}






















if ($op=='create_poll'){
	require_once('lib/poles.php');
	create_polls(10);
}

if($op=='post_poll'){
	require_once('lib/poles.php');
	save_poll_post();
	
// 	db_query()
}
if ($op=='poll_vote'){
	require_once('lib/poles.php');
	post_vote(httpget('pollid'));
	header('Location: motd.php?op=view_current_poll');
}

if ($op=='view_current_poll'){
	require_once('lib/poles.php');
	if ($session['user']['superuser'] & SU_POST_MOTD)output("`&[ <a href='motd.php?op=create_poll'>Create a new poll</a> `&]",true);
	$sql = db_query('SELECT id FROM polls ORDER BY id+0 DESC LIMIT 0,1');
	while ($row = db_fetch_assoc($sql)){
		display_poll($row['id']);
	}
// 	display_poll()
}

if ($op == "changes"){
if ($session['user']['loggedin']){
	$s = $session['user']['loggedin'];
	
	$perpage = 20;
	$page = ( httpget('page') ? httpget('page') : 1 );
	
	$return = "Return";
	$addchange = "Add Update";
	if ($session['user']['superuser'] & SU_POST_MOTD) rawoutput(" [ <a href='motd.php?op=update'>$addchange</a> ] <br/><br/>");
	
	output("`c`Q`b<big>Xythen Updates</big>`b`c`n",true);

	$c1 = "SELECT count(*) as c FROM ".db_prefix('changelog')."";
	$c2 = db_query($c1);
	$c3 = db_fetch_assoc($c2);
	$nump = ceil($c3['c']/$perpage);

	$page = ( $page > $nump ? $nump : $page );
	$min = $perpage * ($page - 1);

	output("`>");
	motd_log_pages($page, $nump, 5);
	output("`>`n");

	$sql = "SELECT * FROM ".db_prefix('changelog')." ORDER BY id DESC LIMIT $min,$perpage";
	$result = db_query($sql);
	if (db_num_rows($result)>0){
		while($row = db_fetch_assoc($result)){
			$idd = $row['id'];

			if ($session['user']['superuser'] & SU_POST_MOTD) $title = $row['title']." `&[ <a href='motd.php?op=update&id=$idd'>Edit</a> `&| <a href='motd.php?op=delete&id=$idd'>Delete</a> ]";

			//Summer/Bright Colors
			//Orange, Red, Yellow
			output_notl("<fieldset><legend style='color:red;' align='center'>".$title."</legend><div style='color:#FF4500;'><b>".date('F jS, Y', strtotime($row['date']))."</b></div><div style='color:#FFA500;'>Changes: ".nltoappon($row['description'])."</div>",true);

            if ($session['user']['superuser'] & SU_GIVE_GROTTO) output_notl("<div style='color:#FFD700;'><i>Pages Updated: ".$row['affected']."</i></div>", true);

			//Cool/Wintery Colors
			//Blue, Green, Aqua
			//output_notl("<fieldset><legend style='color:#00FFCC;' align='center'>".$title."</legend><div style='color:#3399FF;'><b>".date('F jS, Y', strtotime($row['date']))."</b></div><div style='color:#6699CC;'>Changes: ".nltoappon($row['description'])."</div><div style='color:#00FF66;'><i>Pages Updated: ".$row['affected']."</i></div>", true);

			$sqln = "SELECT name FROM ".db_prefix('accounts')." WHERE acctid = {$row['author']}";
			$resn = db_query($sqln);
			$rown = db_fetch_assoc($resn);
			output_notl("`G`iAuthor:`i `&%s", $rown['name']?$rown['name']:"`&Xythen Staff`0");

			output_notl("</fieldset><br />",true);
		}
	}
	output("`n");
	}else{
	output("`b`c`QYou must be logged in to view this information.`c`b");
	}
}

if ($op == "update"){
	if ($session['user']['superuser'] & SU_POST_MOTD){
		$sql = "SELECT * FROM ".db_prefix('changelog')." WHERE id = $id";
		$res = db_query($sql);
		$row = db_fetch_assoc($res);
		output("`b`c`QChangelog Entry`b`n`c");
			rawoutput("<form action='motd.php?op=insert' method='POST'>");
				rawoutput("<input type='text' size='70' id='title' name='title' placeholder=\"Title of Change\" value='{$row['title']}'><br>");
				rawoutput("<textarea class='input' name='desc' cols='54' rows='7' placeholder='What happened here..'>{$row['description']}</textarea><br>");
				rawoutput("<input type='text' size='70' name='affected' placeholder=\"Files changed here\" value='{$row['affected']}'><br>");
				rawoutput("<input type='hidden' name='id' value='{$id}'><br>");
				rawoutput("<input type='submit' class='button' value='Set'>");
			rawoutput("</form>");
			rawoutput("<script type='text/javascript'>document.getElementById('title').focus();</script>");
		addnav("", "motd.php?op=insert");
	}else{
		output("Try it again, kid. You are lucky Aaron coded this.");
	}
}

if ($op == "insert"){
	if ($session['user']['superuser'] & SU_POST_MOTD){
		$t = rawurldecode(httppost("title"));
		$d = rawurldecode(httppost("desc"));
		$a = rawurldecode(httppost("affected"));
		$i = rawurldecode(httppost("id"));
		require_once("lib/changelog.php");
		changelog($t,$d,$a,$i);
		db_query("UPDATE ".db_prefix("accounts")." SET seenupdates = 0 WHERE acctid <> {$session['user']['acctid']}");
	}else{
		output("Try it again, kid. You are lucky Aaron coded this.");
	}
}

if ($op == "delete"){
	if ($session['user']['superuser'] & SU_POST_MOTD){
		db_query("DELETE FROM ".db_prefix("changelog")." WHERE id = $id");
		output("`c`b`^Change Deleted`b`c`0`n`n");
	}else{
		output("Child.");
	}
}

$session['needtoviewmotd']=false;

$sql = "SELECT motddate FROM " . db_prefix("motd") ." ORDER BY motditem DESC LIMIT 1";
$result = db_query_cached($sql, "motddate");
$row = db_fetch_assoc($result);
$session['user']['lastmotd']=$row['motddate'];

popup_footer();
?>
