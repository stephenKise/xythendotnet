<?php

$intBookID = httpget("id");
$intBookID = htmlentities(stripslashes($intBookID));
if(get_module_setting("adminonly") == 1 && !($session["user"]["superuser"] & 16)){
	output("You could not find the book you had just revised. Perplexed, you head back to the entrance of the %s`0.", $strLibraryName);
	addnav("Return to the entrance", "runmodule.php?module=greatlibrary&op=enter");
	return false;
}
$strName = httppost("n");
$strContent = httppost("c");
$strGenre = httppost("g");
$strRate = httppost("r");
$intAuthor = $session["user"]["acctid"];
$strName = htmlentities(stripslashes($strName));
$strContent = stripslashes($strContent);
$strName = str_replace("`", "\`", $strName);
$strContent = str_replace("`", "\`", $strContent);
$strName = str_replace("'", "\'", $strName);
$strContent = str_replace("'", "\'", $strContent);
$boolAllowed = true;	
if(get_module_setting("usehooks") == 1){
	$arrHook = array(
	    "BookName" => $strName,
	    "BookContent" => $strContent
	);
	$varHook = modulehook("greatlibrary_codify", $arrHook);
	if($varHook == false){
		$boolAllowed = false;
	}else{
		$strName = $varHook["BookName"];
		$strContent = $varHook["BookContent"];
	}
}
if($boolAllowed == true){
	$strSQL = "UPDATE ".db_prefix("library")." SET BookName = '$strName', BookContent = '$strContent', BookGenre = '$strGenre', BookRate = '$strRate' WHERE BookID = '$intBookID';";
	db_query($strSQL);
	if(get_module_setting("discworld") == 1){
		output("The orangutan librarian roughly snatches the book from your hands and flicks through the pages, nodding in approval.");
		output("He delicates places %s`0 back into L-Space and continues about his labours.", $strName);
		addnav("Are you sure he's not a Monkey?", "runmodule.php?module=greatlibrary&op=monkey");
	}else{
		output("You lovingly replace your work on the shelves of the %s`0, for all who would visit to see.", $strLibraryName);
	}
	addnav("Return to the entrance", "runmodule.php?module=greatlibrary&op=enter");
}
?>