<?php

//strip_tags() ;)
function create_polls($num){
	$i = 0;
	output("<form action='motd.php?op=post_poll' method='POST'>",true);
	output("<table width='500px' align='center'><tr><td colspan='2'>`c`b`QCreate a new poll!`b`c</td></tr>",true);
	while($num > $i){
		output("<tr><td colspan='2' align='center'>`@Poll Item #".($i+1)."</td></tr>",true);
		output("<tr><td>`nShort message:</td><td align='right'><input type='text' name='poll_item_".$i."' size='50'></td></tr>",true);
		output("<tr><td valign='center'>Long Message:</td><td align='right' valign='center'><textarea class='input' cols='39' rows='5' name='extended_".$i."'></textarea>`n`n</td></tr>",true);
		
		$i++;
	}
	output("</table><input type='submit' value='Submit'>",true);
	output("</form>",true);
}

function save_poll_post($pollid=false){
	global $session;
	$posted = httpallpost();
	debug($posted);
	$sql = "INSERT INTO polls (pollitems,longitems,posted,creator) VALUES ";
	$items_array = array();
	$items_large_array = array();
	foreach($posted as $item=>$value){
		if (substr($item,0,10)=="poll_item_") array_push($items_array,$value);
			else array_push($items_large_array,$value);
	}
	$items_large_array = serialize($items_large_array);
	$items_array = serialize($items_array);
	$sql .= "('".addslashes($items_array)."','".addslashes($items_large_array)."','". date("Y-m-d H:i:s")."','{$session['user']['acctid']}')";
	db_query($sql);
	require_once('lib/alert.php');
	alerteveryone('`^New content poll! Make sure you vote `$:D `2[<a href="motd.php?op=view_current_poll">Click here to vote!</a>`2]');
	header('Location: motd.php?op=view_current_poll');
}

function display_poll($pollid){ //WORK ON BAR TO DISPLAY VOTES LATER
	global $session;
	if (!$session['user']['loggedin']) header('Location: motd.php');
	require_once('lib/bars.php');
	$sql = db_query("SELECT * FROM polls WHERE id = {$pollid}");
	while($row = db_fetch_assoc($sql)){
		$poll_items = unserialize($row['pollitems']);
		$upvote_array = unserialize($row['upvote_array']);
		$downvote_array = unserialize($row['downvote_array']);
		$voted_ips = unserialize($row['voted_ips']);
		if ((!in_array($pollid,unserialize($session['user']['pollvotes'])) && !in_array($session['user']['lastip'],$voted_ips)) && floor((time()-strtotime($row['posted']))/(60*60*24)) < 5){
			output("<div style='margin-left: auto; margin-right: auto; width: 500px;'><form action='motd.php?op=poll_vote&pollid={$pollid}' method='POST'>",true);
			output("`c`i`\$Please do not vote until you know `bin full extent`b `\$ of what you are voting for.`i`c`n");
			foreach($poll_items as $item=>$value){
				if ($value != ""){
					output("<fieldset style='margin-left: auto; margin-right: auto; width: 500px;'><legend>`2Question ".($item+1)."</legend>",true);
					output("`b`&".stripslashes($value)."`b`n",true);
					$long_description = unserialize($row['longitems']);
					output("`Q".stripslashes($long_description[$item])."`n`n",true);
					output("<input type='radio' name='item_{$item}' value='yes'>Yes`n<input type='radio' name='item_{$item}' value='no'>No",true);
					output("</fieldset>`n",true);
				}
			}
			output("`c`i`\$Please do not vote until you know `bin full extent`b `\$ of what you are voting for. If you are confused about some questions, see below!`i`c`n");
			output("<input type='submit' value'Submit'>",true);
			output("</form></div>",true);
		}else{
			output("<div style='margin-left: auto; margin-right: auto; width: 500px;'>",true);
			foreach($poll_items as $item=>$value){
				if ($value != ""){
// 					$total_votes = ($upvote_array['item_'.$item]+($downvote_array['item_'.$item]?$downvote_array['item_'.$item]:1));
					$total_votes = ($upvote_array['item_'.$item]+$downvote_array['item_'.$item]);
					$vote_percentage = (round($upvote_array['item_'.$item]/$total_votes,2)*100);
					if ($vote_percentage >= 66) $col = "`@";
						else $col = "`\$";					
					output("<fieldset style='margin-left: auto; margin-right: auto; width: 500px;'><legend>`7Question ".($item+1)."</legend>",true);
					output("`b`&".stripslashes($value)."`b`n");
					$long_description = unserialize($row['longitems']);
					output("`Q".stripslashes($long_description[$item])."`n`n",true);
					output("`n`c$col $vote_percentage%`c".simplebar($upvote_array['item_'.$item],$total_votes,'100%','5px','C11B17','4CC417'),true);
					output("</fieldset>`n",true);
				}
			}
			output("</div>",true);
			output("`c`i`QThese statistics are based on `&`b$total_votes`b `Qvotes!`i`c");			
			if ($session['user']['superuser'] & SU_EDIT_USERS){
				output("`QSo far ");
				foreach(unserialize($row['voted_ips']) as $num=>$ip_address){
// 					debug($user);
					$list_by_ip = db_query("SELECT name,login FROM accounts WHERE lastip = '".$ip_address."'");
					while ($users = db_fetch_assoc($list_by_ip)){
						output("`^".$users['login']."`Q, ");
					}
				}
				output("`Qhave all voted. Names of alts are included, which is why the number is more than the actual vote count.");
			}
		}
// 		if ($session['user']['superuser'] & SU_EDIT_USERS){
// 			debug(unserialize($row['downvote_array']));
// 		}
	}
}

function post_vote($pollid){
	global $session;
	$posted = httpallpost();
	$sql = db_query("SELECT * FROM polls WHERE id = {$pollid}");
	while ($row = db_fetch_assoc($sql)){
// 		debug($row);
		$row['upvote_array'] = unserialize($row['upvote_array']);
		$row['downvote_array'] = unserialize($row['downvote_array']);
		$row['voted_ips'] = unserialize($row['voted_ips']);
		array_push($row['voted_ips'],$session['user']['lastip']);
		$row['voted_ips'] = serialize($row['voted_ips']);
		
		foreach($posted as $item=>$value){
			if ($value=="yes"){
				$row['upvote_array'][$item]++;
			}else{
				$row['downvote_array'][$item]++;
			}
		}
		$row['upvote_array'] = serialize($row['upvote_array']);
		$row['downvote_array'] = serialize($row['downvote_array']);
// 		debug($row);
		$pre_sql = '';
		$post_sql = '';
		$query = 'UPDATE polls SET ';
		foreach($row as $key=>$val){
				if ($key != 'id'){
					if ($key != 'voted_ips') $query.=$key." = '".addslashes($val)."', ";
						else $query.=$key." = '".addslashes($val)."' ";
				}
		}
		$query .= 'WHERE id = '.$pollid;
		debug($query);
 		db_query($query);
	}
	$my_poll_votes = array();
	$my_poll_votes = unserialize($session['user']['pollvotes']);
	$my_poll_votes = array_merge($my_poll_votes,array(httpget('pollid')));
  	$session['user']['pollvotes'] = serialize($my_poll_votes);
}