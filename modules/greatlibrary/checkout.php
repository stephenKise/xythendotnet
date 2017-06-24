<?php

$intBookID = httpget("id");
$intBookID = htmlentities(stripslashes($intBookID));
$strSQL = "SELECT BookName FROM ".db_prefix("library")." WHERE BookID = '$intBookID';";
$queBook = db_query($strSQL);
$arrBook = db_fetch_assoc($queBook);
$strName = $arrBook["BookName"];
$intOwner = $session["user"]["acctid"];
$strSQL = "SELECT BookName FROM  ".db_prefix("bookcarry")." WHERE Book = $intBookID AND Owner = $intOwner";
$queCarrying = db_query($strSQL);
if(db_num_rows($queCarrying) == 0){
	$strSQL = "INSERT INTO ".db_prefix("bookcarry")." (Book, Owner, BookName) VALUES ('$intBookID', '$intOwner', '$strName');";
	db_query($strSQL);
	if(get_module_setting("nocolours") == 1){
		$strName = prevent_colors($strName);
	}
	output("You pull a copy of %s`0 from the shelves of the %s`0,", $strName, $strLibraryName);
	output("diligently filling out the required forms that will transfer the book");
	output("unto your custody.");
	db_query("UPDATE ".db_prefix("library")." SET BookCarried = '1' WHERE BookID = '$intBookID'");
}else{
	output("You go to check the book out before realising you already have a copy stashed away.");
}
addnav("Read $strName", "runmodule.php?module=greatlibrary&op=read&id=$intBookID");
addnav("Continue browsing", "runmodule.php?module=greatlibrary&op=browse");
?>