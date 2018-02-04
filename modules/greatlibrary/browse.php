<?php

output("You peruse the shelves of the %s, searching through the assorted", $strLibraryName);
output("aisles for the book of your choice.`n`n");

$strSQL = "SELECT * FROM ".db_prefix("library")." ORDER BY BookGenre ASC, AvgRating DESC, BookName DESC;";
$queBook = db_query($strSQL);

if(db_num_rows($queBook) == 0){
    output("After some time spent searching, nothing you find catches your interest.");
}else{
    $Genre = translate_inline("Genre Type");
    $BookTitle = translate_inline("Book Title");
    $BookAuthor = translate_inline("Author Name");
    $BookRatings = translate_inline("Avg Rating");
    $BookViews = translate_inline("Total Views");
    $strRateDisabled = translate_inline("Disabled");
    rawoutput("<table border='1px' cellpadding='5' cellspacing='0' width='650px'>");
    rawoutput("<tr class='trhead'><td>&nbsp;</td><td>$BookTitle</td><td>$BookAuthor</td><td>$Genre</td><td>$BookViews</td><td>$BookRatings</td></tr>");
    while($arrBook = db_fetch_assoc($queBook)){
        $intBookID = $arrBook["BookID"];
        $strName = $arrBook["BookName"];
        $intAuthor = $arrBook["BookAuthorID"];
        $strAuthorName = $arrBook["BookAuthorName"];
        $strGenre = $arrBook["BookGenre"];
        $strRate = $arrBook["BookRate"];
        $strAvgRating = $arrBook["AvgRating"];
        $strViews = $arrBook["BookViews"];
        if($strAuthorName == null){
            $strAuthorName = "Unknown Author";
        }
        $strLink = appendcount("runmodule.php?module=greatlibrary&op=read&id=$intBookID");
        addnav("", $strLink);
        if(get_module_setting("nocolours") == 1){
            $strName = prevent_colors($strName);
        }
        rawoutput("<tr class='trlight'>");
rawoutput("<td><a href=\"$strLink\"><img src='images/books.gif' width='20' height='20' border='0'></a></td>");
        rawoutput("<td><a href=\"$strLink\">");
        output("$strName");
        rawoutput("</a></td><td>");
        output("$strAuthorName");
        rawoutput("</td><td>");
        output("$strGenre");
        rawoutput("</td><td>");
        output("$strViews");
        rawoutput("</td><td>");
        if($strRate == 1){
            output("$strRateDisabled");
        }else{
            output_notl("%0.1f",$strAvgRating);
        }
        rawoutput("</td></tr>");
    }
}
rawoutput("</table>");
addnav("Return to the entrance", "runmodule.php?module=greatlibrary&op=enter");
?>