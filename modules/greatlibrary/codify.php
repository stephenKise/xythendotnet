<?php

if(get_module_setting("adminonly") == 1 && !($session["user"]["superuser"] & 16)){
    output("You could not find the book you had just written. Perplexed, you head back to the entrance of the %s.", $strLibraryName);
    addnav("Return to the entrance", "runmodule.php?module=greatlibrary&op=enter");
    return false;
}
$strName = httppost("n");
$strContent = httppost("c");
$strGenre = httppost("g");
$strRate = httppost("r");
$intAuthor = $session["user"]["acctid"];
//Get name of author
$strSQL = "SELECT name FROM ".db_prefix("accounts")." WHERE acctid = '$intAuthor';";
$queAuthor = db_query($strSQL);
$arrAuthor = db_fetch_assoc($queAuthor);
$strAuthorName = addslashes($session['user']['name']);
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
    $strSQL = "INSERT INTO ".db_prefix("library")." (BookName, BookContent, BookAuthorID, BookAuthorName, BookGenre, BookRate) VALUES ('$strName', '$strContent', '$intAuthor', '$strAuthorName', '$strGenre', '$strRate');";
    db_query($strSQL);
    if(get_module_setting("discworld") == 1){
        output("Looking through your book, the orangutan librarian confirms the book is ready for the shelves with a wisened nod");
        output("and climbs up the shelves. After stamping it approved, he places the book in the logical mist of L-Space.");
        addnav("Thank you Mr. Monkey!", "runmodule.php?module=greatlibrary&op=monkey");
    }else{
        output("You lovingly place your work on the shelves of the %s, for all who would visit to see.", $strLibraryName);
    }
addnav("Return to the entrance", "runmodule.php?module=greatlibrary&op=enter");
}
?>