<?php

$intBookID = httppost("id");
$intPlayer = httppost("authorid");
$strRating = httppost("ratings");

$updateSQL = "INSERT INTO ".db_prefix("library_ratings")." (BookID, RateAuthorID, Rated, Ratings) VALUES ('$intBookID', '$intPlayer', '1', '$strRating');";
db_query($updateSQL);

$strSQL = "SELECT * FROM ".db_prefix("library")." WHERE BookID = '$intBookID';";
$queBook = db_query($strSQL);
$arrBook = db_fetch_assoc($queBook);
$strName = $arrBook["BookName"];
$strAuthorName = $arrBook["BookAuthorName"];
$strAvgRating = $arrBook["AvgRating"];
$strCarried = $arrBook["BookCarried"];
if(get_module_setting("nocolours") == 1){
    $strName = prevent_colors($strName);
}

$avgSQL = "SELECT AVG(Ratings) AS AVG_Ratings FROM ".db_prefix("library_ratings")." WHERE BookID = '$intBookID'";
$avgResult = db_query($avgSQL);
$avgFetch = db_fetch_assoc($avgResult);
$avgRating = $avgFetch["AVG_Ratings"];
db_query("UPDATE ".db_prefix("library")." SET AvgRating = '$avgRating' WHERE BookID = '$intBookID'");

rawoutput("<h3>");
output("`&Thank you for rating my book; `7%s`&.",$strName);
rawoutput("</h3>");
output("`7If you would like to send me feed back please send them via `^Ye Olde Mail`7, `nI'd be happy to hear your thoughts.");
output("`n`n`7Yours gratefully %s",$strAuthorName);
output("`n`n`7This book now has an average rating of %0.1f",$avgRating);

if($strCarried == 1){
    villagenav();
}else{
    addnav("Continue browsing", "runmodule.php?module=greatlibrary&op=browse");
}
if($session["user"]["superuser"] & 16){
    addnav("Burn all copies", "runmodule.php?module=greatlibrary&op=burn&id=$intBookID");
}

?>