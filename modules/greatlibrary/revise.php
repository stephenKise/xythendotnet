<?php

$intBookID = httpget("id");
$intPlayer = $session["user"]["acctid"];
$intBookID = htmlentities(stripslashes($intBookID));
$strSQL = "SELECT BookContent, BookName, BookGenre, BookRate FROM ".db_prefix("library")." WHERE BookID = '$intBookID' AND BookAuthorID = '$intPlayer';";
$queBook = db_query($strSQL);
if(db_num_rows($queBook) == 0){
	output("You could not find the book you were seeking on the shelves. Perplexed, you head back to the entrance of the %s.", $strLibraryName);
	addnav("Return to the entrance", "runmodule.php?module=greatlibrary&op=enter");
	return false;
}
$arrBook = db_fetch_assoc($queBook);
$strName = $arrBook["BookName"];
$strCurrent = $arrBook["BookContent"];
$strGenre = $arrBook["BookGenre"];
$strRate = $arrBook["BookRate"];
$strAction = appendcount("runmodule.php?module=greatlibrary&op=republish&id=$intBookID");
addnav("", $strAction);

$strBookTitle = translate_inline("Book Title");
$strContents = translate_inline("Contents");
$strGenre_text = translate_inline("This books Genre is Currently set as,");
$strGenre_text2 = translate_inline("Please Reselect Genre type even if it hasn't changed.");
$strSubmitValue = translate_inline("Place book back on the shelves");
$strRate_text = translate_inline("Rating on this book is currently");
$strRate_text2 = translate_inline("Please Reselect Rating type even if it hasn't changed.");
$strRate_text3 = translate_inline("Disable Rating?");
$strRate_text4 = translate_inline("Enable Rating?");
if($strRate == 1){ $strRATE = translate_inline("Disabled"); } elseif($strRate == 0){ $strRATE = translate_inline("Enabled"); }
$g1 = translate_inline("Archives");
$g2 = translate_inline("Biography");
$g3 = translate_inline("Comedy");
$g4 = translate_inline("Crime");
$g5 = translate_inline("Fiction");
$g6 = translate_inline("Informational");
$g7 = translate_inline("Kids");
$g8 = translate_inline("Non-Fiction");
$g9 = translate_inline("Poetry");
$g10 = translate_inline("Sci-Fi");
$g11 = translate_inline("Other");

output("You sit down and pull %s from the shelves, considering the revisions you wish to make.`n`n",$strName);

rawoutput("<form method=\"post\" action=\"$strAction\">");
rawoutput("<p>$strBookTitle: <input type=\"text\" name=\"n\" value=\"$strName\" size=\"40\" />");
rawoutput("<br />$strContents:<br /><textarea style=\"background-color: #FFFFBB;\"name=\"c\" rows=\"15\" cols=\"80\">$strCurrent</textarea>");
rawoutput("<br />$strGenre_text $strGenre; <br />$strGenre_text2");
rawoutput("<br /><select name=\"g\"><option value=\"Archives\">$g1</option><option value=\"Biography\">$g2</option><option value=\"Comedy\">$g3</option><option value=\"Crime\">$g4</option><option value=\"Fiction\">$g5</option><option value=\"Informational\">$g6</option><option value=\"Kids\">$g7</option><option value=\"Non-Fiction\">$g8</option><option value=\"Poetry\">$g9</option><option value=\"Sci-Fi\">$g10</option><option value=\"Other\">$g11</option></select>");
rawoutput("<br />$strRate_text $strRATE; <br />$strRate_text2");
rawoutput("<br />$strRate_text3<input type=\"checkbox\" name=\"r\" value=\"1\" />");
rawoutput("<br />$strRate_text4<input type=\"checkbox\" name=\"r\" value=\"0\" />");
rawoutput("<br /><input type=\"submit\" value=\"$strSubmitValue\" /></p>");
rawoutput("</form>");
addnav("Browse the books", "runmodule.php?module=greatlibrary&op=browse");
addnav("Return to the entrance", "runmodule.php?module=greatlibrary&op=enter");
villagenav();
?>