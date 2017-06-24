<?php
// Removed addnews as directed by Charity - Maverick

function changelog($title, $desc, $affected, $id=0)
{
	global $session;
	if ((int)$id > 0){
		$sql = "UPDATE ".db_prefix('changelog')." SET date = '".date("Y-m-d")."', title = '".$title."', description = '".$desc."', affected = '".$affected."' WHERE id = $id";
		$result = db_query($sql);
		$now = date("F j, Y");
		output("Just edited a change:`n`Q`b%s`b `^- %s`n`@%s`n`^Pages changed: %s`n`n", stripslashes($title), $now, stripslashes($desc), $affected);
		//addnews("{$session['user']['name']} `^has edited a change entitled `Q\"$title\"");
	} else {
		$sql = "INSERT INTO ".db_prefix('changelog')." ( `id` , `date` , `title` , `description` , `affected`, `author` ) VALUES (NULL ,'".date("Y-m-d")."', '".$title."', '".$desc."', '".$affected."', ".$session['user']['acctid'].")";
		$result = db_query($sql);
		$now = date("F j, Y");
		output("Just implemented a new change:`n`Q`b%s`b `^- %s`n`@%s`n`^Pages changed: %s`n`n", stripslashes($title), $now, stripslashes($desc), $affected);
		//addnews("{$session['user']['name']} `^has made a new change entitled `Q\"$title\"");
	}
}
?>