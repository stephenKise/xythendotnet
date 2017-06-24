<?php
function xythenplots_getmoduleinfo(){
	$info = array(
		"name" => "Xythen Plots",
		"author" => "`i`)Ae`7ol`&us`i`0",
		"version" => "1.0",
		"category" => "Roleplay",
		"settings" => array(
			"Settings,title",
		),
		"prefs" => array(
			"Prefs,title",
		),
	);
	return $info;
}

function xythenplots_install(){
	if (!db_table_exists(db_prefix("xythenplots"))){
		$sql = "CREATE TABLE ".db_prefix("xythenplots")." (
				`id` int(11) NOT NULL auto_increment,
				`author` int(11) NOT NULL default 0,
				`title` varchar(255) NOT NULL,
				`type` varchar(255) NOT NULL,
				`description` varchar(255) NOT NULL,
				`characters` varchar(255) NOT NULL,
				`status` int(11) NOT NULL default 0,
				`updatedate` int(11) NOT NULL default 0,
				PRIMARY KEY  (`id`)
		)";
		db_query($sql);
	}
	module_addhook("village");
	return true;
}

function xythenplots_uninstall(){
	return true;
}

function xythenplots_dohook($hookname, $args){
	
	tlschema($args['schemas']['infonav']);
	addnav($args['infonav']);
	tlschema();
	addnav("Xythen Plots", "runmodule.php?module=xythenplots");
	
	return $args;
}

function xythenplots_run(){
	global $session;
	require_once("lib/showform.php");
	require_once("lib/nltoappon.php");
	require_once("lib/villagenav.php");
	
	$id = httpget("id");
	$op = httpget("op");
	$accounts = db_prefix("accounts");
	$xythenplots = db_prefix("xythenplots");
	
	page_header("Xythen Plots");
	
	addnav("Return");
	villagenav();
	
	switch ($op){
		case "viewcurrent":
			xythenplots_list(array(0,1));
		break;
		case "addaplot":
			$post = httpallpost();
			
			if ($post['title'] && $post['type'] && $post['description'] && $post['characters']){
				$now = date("Y-m-d H:i:s");
				if ($id){
					$sql = "UPDATE $xythenplots SET title='{$post['title']}', type='{$post['type']}', description='{$post['description']}', characters='{$post['characters']}', updatedate='{$now}', status='{$post['status']}' WHERE id = $id";
				} else {
					$sql = "INSERT INTO $xythenplots VALUES ('', {$session['user']['acctid']}, '{$post['title']}', '{$post['type']}', '{$post['description']}', '{$post['characters']}', 0, '{$now}')";
				}
				db_query($sql);
				output("`n`cPlot has been %s!`c`n",$id?"edited":"added");
			}
			
			$form = array(
				"Add A Plot,title",
				"title" => "Title of Plot",
				"type" => "Type,enum,Personal,Personal,Public,Public",
				"description" => "Description,textarea",
				"characters" => "Characters"
			);
			if ($id) $form["status"] = "Status,enum,0,New,1,In Progress,2,Completed";
			
			if (!$id){
				$row = array(
					"title" => "",
					"type" => "Public",
					"description" => "",
					"characters" => ""
				);
			} else {
				$sql = "SELECT * FROM $xythenplots WHERE id = $id";
				$res = db_query($sql);
				$row = db_fetch_assoc($res);
			}
			
			$formlink = "runmodule.php?module=xythenplots&op=addaplot".($id?"&id=$id":"");
			rawoutput("<form action='$formlink' method='post'>");
			showform($form, $row);
			rawoutput("</form>");
			addnav("", $formlink);
		break;
		case "completeaplot":
			if ($id){
				if (httpget('yes')){
					$sql = "UPDATE $xythenplots SET status = 2 WHERE id = $id";
					db_query($sql);
					output("`n`cIt has been done!`c`n");
				} else {
					output("`n`cAre you sure you wish to mark this plot as complete, and send it to the archive?`c`n");
					addnav("Choose");
					addnav("Yes", "runmodule.php?module=xythenplots&op=completeaplot&yes=1");
					addnav("No", "runmodule.php?module=xythenplots");
				}
			} else {
				xythenplots_list(array(0,1), true);
			}
		break;
		case "plotarchive":
			xythenplots_list(2);
		break;
		default:
			output_notl("`n`n`c");
			output("`y`iListed below are the current `yplots...`i`0");
			output_notl("`c`n`n");
			
			xythenplots_list(array(0,1));
		break;
	}
	
	addnav("Options");
	addnav("View Current Plots", "runmodule.php?module=xythenplots&op=viewcurrent");
	addnav("Add A Plot", "runmodule.php?module=xythenplots&op=addaplot");
	addnav("Complete A Plot", "runmodule.php?module=xythenplots&op=completeaplot");
	addnav("Plot Archive", "runmodule.php?module=xythenplots&op=plotarchive");
	
	page_footer();
	
	// Status 0 : New
	// Status 1 : In Progress
	// Status 2 : Completed (Archived)
}

function xythenplots_list($status, $personal=false){
	global $session;
	$accounts = db_prefix('accounts');
	$xythenplots = db_prefix('xythenplots');
	
	$statussql = ( is_array($status) ? "status IN (".implode(",", $status).")" : "status = $status" );
	$personalsql = ( $personal ? "AND author = {$session['user']['acctid']}" : "" );
	
	$sql = "SELECT x.*, a.name FROM $xythenplots x INNER JOIN $accounts a ON x.author = a.acctid WHERE $statussql $personalsql";
	$res = db_query($sql);
	while($row = db_fetch_assoc($res)){
		if (!$isplots) $isplots = true;
		output_notl("<big>`&%s`0</big>", stripslashes($row['title']), true);
		if ($row['author'] == $session['user']['acctid'] && $row['status'] <> 2){
			rawoutput("[ <a href='runmodule.php?module=xythenplots&op=addaplot&id={$row['id']}'>E</a>");
			if (!$row['status']) rawoutput("| <a href='runmodule.php?module=xythenplots&op=del&id={$row['id']}'>X</a>");
			rawoutput(" ]");
			addnav("", "runmodule.php?module=xythenplots&op=addaplot&id={$row['id']}");
			addnav("", "runmodule.php?module=xythenplots&op=del&id={$row['id']}");
		}
		output_notl("`n`&%s`& (%s)`n", $row['name'], ucfirst($row['type']));
		output_notl("`x`iCharacters`i: `&%s`&`n", stripslashes(nltoappon($row['characters'])));
		output_notl("`x`iDescription`i:`n`&%s`&`n", stripslashes(nltoappon($row['description'])));
		output_notl("<hr />`n", true);
	}
	if (!$isplots){
		output_notl("`n`n");
		output("`x`i`cNo Plots Here!`c`i`0");
		output_notl("`n`n");
	}
}
?>