<?php

$intBookID = httpget("id");
$intBookID = htmlentities(stripslashes($intBookID));
$strSQL = "SELECT BookName FROM ".db_prefix("library")." WHERE BookID = '$intBookID';";
$queBook = db_query($strSQL);
$arrBook = db_fetch_assoc($queBook);
$strName = $arrBook["BookName"];
if(get_module_setting("nocolours") == 1){
	$strName = prevent_colors($strName);
}
$intOwner = $session["user"]["acctid"];
$strSQL = "DELETE FROM  ".db_prefix("bookcarry")." WHERE Book = $intBookID AND Owner = $intOwner";
db_query($strSQL);
db_query("UPDATE ".db_prefix("library")." SET BookCarried = '0' WHERE BookID = '$intBookID'");
output("You carefully place your copy of %s`0 back into the %s`0", $strName, $strLibraryName);
addnav("Continue browsing", "runmodule.php?module=greatlibrary&op=browse");
?>