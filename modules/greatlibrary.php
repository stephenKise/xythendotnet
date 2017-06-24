<?php

/*
	*****************************
	*****   GREAT LIBRARY   *****
	*****   Version 0.23    *****
	*****************************
	
	Developer: Danny Moules
	Website: http://www.rushyo.com/lotgd/
	Version: Developed + Tested on 1.1.1 DragonPrime Edition
	
	Required Modules:
		o None
		
	Additional Modules for Full Function: 
		o None
		
	Module Hooks:
		o village
		o charstats
	
	Licensed under Attribution-Non-Commercial-Share Alike 2.0 UK: England & Wales
	http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
	
	Attribution Text (In all derived scripts):
		//Derived from Great Library developed by Danny Moules (Rushyo)
	
	Attribution Text (In Module Author field):
		Original by Danny Moules (Rushyo)
*/

/////////////////////////////////////////////////////
/* Modifications by mingo, Aka, Cory
 o V 1.0
    o added book genre type
    o added book veiws
    o added rating system
    o changed browsing display (listings)
    
 o V 1.1
    o changed and fixed rating system
    o added to browsing display
*/

function greatlibrary_getmoduleinfo(){
	$arrInfo = array(
		"name" => "Great Library",
		"version" => "1.1",
		"author" => "Danny Moules (Rushyo), `n`&`bModified by <a href='http://www.supermingo.com' target='_new'>supermingo</a>`b`0",
		"category" => "Village",
		"description" => "A module that allows players to write, read and carry books.",
		"download" => "",
		"override_forced_nav"=>true,
        "settings" => array(
            "Great Library - Settings,title",
			"libraryname" => "Name of the library|`b`TG`b`4r`b`Qe`i`^a`b`i`Tt `i`T`bL`bi`i`4b`i`Qr`i`^a`i`T`br`by`i",
			"allowcheckout" => "Allow checking out of books?,bool|1",
			"adminonly" => "Only allow admins with 'Edit Item' rights to write books?,bool|0",
			"nocolours" => "Forbid colours from being used in book names?,bool|0",
			"discworld" => "Show Pratchett references?,bool|1",
			"publicburn" => "Display a news item when a book is burned?,bool|0",
			"usehooks" => "Allow execution of hooks?,bool|1",
		),
	);
	return $arrInfo;
}

function greatlibrary_install(){
	//Hate using DESCRIBE for this... but the upgrade flow is awful.
	//This is also retardedly slow if there's any indexing/version control nudging the timestamps.
	require_once("modules/greatlibrary/installer.php");
	require_once("modules/greatlibrary/upgraderules.php");
	
	module_addhook("village");
	module_addhook("charstats");
	return true;
}

function greatlibrary_uninstall(){
	if (!db_table_exists(db_prefix("library"))){
		output("`n`nWarning: Library table could not be found to be deleted`n`n");
	}else{
		$strSQL = "DROP TABLE ".db_prefix("library").";";
		output("`n`n`i%s`i`n`n", $strSQL);
		db_query($strSQL);
		if(db_table_exists(db_prefix("library"))){
			output("`n`n`bERROR: Library table could not be deleted`b`n`n");
			return false;
		}
	}
	if (!db_table_exists(db_prefix("bookcarry"))){
		output("`n`nWarning: Bookcarry table could not be found to be deleted`n`n");
	}else{
		$strSQL = "DROP TABLE ".db_prefix("bookcarry").";";
		output("`n`n`i%s`i`n`n", $strSQL);
		db_query($strSQL);
		if(db_table_exists(db_prefix("bookcarry"))){
			output("`n`n`bERROR: Bookcarry table could not be deleted`b`n`n");
			return false;
		}
	}
	if (!db_table_exists(db_prefix("library_ratings"))){
		output("`n`nWarning: Library_Ratings table could not be found to be deleted`n`n");
	}else{
		$strSQL = "DROP TABLE ".db_prefix("library_ratings").";";
		output("`n`n`i%s`i`n`n", $strSQL);
		db_query($strSQL);
		if(db_table_exists(db_prefix("library_ratings"))){
			output("`n`n`bERROR: Library_Ratings table could not be deleted`b`n`n");
			return false;
		}
	}
	return true;
}

function greatlibrary_dohook($strHookName, $args){
	if($strHookName == "village"){
		$strLibraryName = get_module_setting("libraryname");
		tlschema($args["schemas"]["tavernnav"]);
		addnav($args["tavernnav"]);
		tlschema();
		addnav($strLibraryName, "runmodule.php?module=greatlibrary&op=enter");
	}
	if($strHookName == "charstats" && get_module_setting("allowcheckout") == true){
		global $session;
		$intPlayer = $session["user"]["acctid"];
		$strSQL = "SELECT Book, BookName FROM ".db_prefix("bookcarry")." WHERE Owner = $intPlayer";
		$queCarrying = db_query($strSQL);
		while($arrBook = db_fetch_assoc($queCarrying)){
			$intBook = $arrBook["Book"];
			$strBookName = $arrBook["BookName"];
			$strLink = appendcount("runmodule.php?module=greatlibrary&op=read&cr=1&id=$intBook");
			addnav("", $strLink);
			$strBookTerm = translate_inline("Books Carried");
			if(get_module_setting("nocolours") == 1){
				$strBookName = prevent_colors($strBookName);
			}
			setcharstat($strBookTerm, $strBookName, "<a href=\"$strLink\" style=\"color: #DD55DD;\">Read</a>");
		}
	}
	return $args;
}

function greatlibrary_run(){
	global $session;
	$strLibraryName = get_module_setting("libraryname");
	page_header($strLibraryName);
	$strOp = httpget("op");
	if($strOp == "enter"){
		require_once("modules/greatlibrary/enter.php");
	}elseif($strOp == "browse"){
		require_once("modules/greatlibrary/browse.php");
	}elseif($strOp == "write"){
		require_once("modules/greatlibrary/write.php");
	}elseif($strOp == "revise"){
		require_once("modules/greatlibrary/revise.php");
	}elseif($strOp == "republish"){
		require_once("modules/greatlibrary/republish.php");
	}elseif($strOp == "codify"){
		require_once("modules/greatlibrary/codify.php");
	}elseif($strOp == "read"){
		require_once("modules/greatlibrary/read.php");
	}elseif($strOp == "ratings"){
	    require_once("modules/greatlibrary/ratings.php");
	}elseif($strOp == "checkout"){
		require_once("modules/greatlibrary/checkout.php");
	}elseif($strOp == "replace"){
		require_once("modules/greatlibrary/replace.php");
	}elseif($strOp == "burn"){
		require_once("modules/greatlibrary/burn.php");
	}elseif($strOp == "monkey"){
		require_once("modules/greatlibrary/monkey.php");
	}else{
		output("You appear to be lost in the halls of the %s.", $strLibraryName);
		addnav("Return to the entrance", "runmodule.php?module=greatlibrary&op=enter");
	}
	page_footer();
}
?>