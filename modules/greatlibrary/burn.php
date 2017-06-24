<?php

if ($session['user']['superuser'] & SU_MANAGE_MODULES)
{
	debug(httpget('id'));
	$sql = "DELETE FROM library WHERE BookID = '".httpget('id')."'";
	$res = db_query($sql);
	output("This book has been deleted.");
}
else
{
	output("You do not have the proper permissions to do this.");
}
	
addnav("Actions");
addnav("Continue browsing", "runmodule.php?module=greatlibrary&op=browse");
?>