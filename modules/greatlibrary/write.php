<?php

$strAction = appendcount("runmodule.php?module=greatlibrary&op=codify");
addnav("", $strAction);
$strBookTitle = translate_inline("Book Title");
$strContents = translate_inline("Contents");
$strGenre = translate_inline("Select the Genre of this book");
$strRate = translate_inline("Disable this book from being Rated?");
$strRate2 = translate_inline("Allow this book to be Rated?");
$strSubmitValue = translate_inline("Place book on the shelves");
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

output("You sit down and, fiddling with your quill in the other hand, pull a blank tome from the available pile.`n`n");

rawoutput("<form method=\"post\" action=\"$strAction\">");
rawoutput("<p>$strBookTitle: <input type=\"text\" name=\"n\" size=\"40\" />");
output("<br />$strContents:<br /><textarea style=\"background-color: #FFFFBB;\"name=\"c\" rows=\"15\" cols=\"80\"></textarea>",true);
rawoutput("<br />$strGenre:<select name=\"g\"><option value=\"Archives\">$g1</option><option value=\"Biography\">$g2</option><option value=\"Comedy\">$g3</option><option value=\"Crime\">$g4</option><option value=\"Fiction\">$g5</option><option value=\"Informational\">$g6</option><option value=\"Kids\">$g7</option><option value=\"Non-Fiction\">$g8</option><option value=\"Poetry\">$g9</option><option value=\"Sci-Fi\">$g10</option><option value=\"Other\">$g11</option></select>");
rawoutput("<br />$strRate:<input type=\"checkbox\" name=\"r\" value=\"1\" />");
rawoutput("<br />$strRate2:<input type=\"checkbox\" name=\"r\" value=\"0\" />");
rawoutput("<br /><input type=\"submit\" value=\"$strSubmitValue\" /></p>");
rawoutput("</form>");

addnav("Browse the books", "runmodule.php?module=greatlibrary&op=browse");
addnav("Return to the entrance", "runmodule.php?module=greatlibrary&op=enter");
villagenav();

?>