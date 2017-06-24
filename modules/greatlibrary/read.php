<?php

$strAction = appendcount("runmodule.php?module=greatlibrary&op=ratings");
addnav("", $strAction);

$intBookID = abs((int)httpget("id"));
$intPlayer = $session["user"]["acctid"];
$strSQL = "SELECT * FROM ".db_prefix("library")." WHERE BookID = '$intBookID';";
$queBook = db_query($strSQL);
$arrBook = db_fetch_assoc($queBook);
$strName = $arrBook["BookName"];
$strContent = $arrBook["BookContent"];
$intAuthor = $arrBook["BookAuthorID"];
$strAuthorName = $arrBook["BookAuthorName"];
$strGenre = $arrBook["BookGenre"];
$strRate = $arrBook["BookRate"];
$strViews = $arrBook["BookViews"];
$strAvgRating = $arrBook["AvgRating"];
$strViews++;
db_query("UPDATE ".db_prefix("library")." SET BookViews = '$strViews' WHERE BookID = '$intBookID'");

$rSQL = "SELECT * FROM ".db_prefix("library_ratings")." WHERE BookID = '$intBookID' AND RateAuthorID = '$intPlayer';";
$rRate = db_query($rSQL);
$arrRate = db_fetch_assoc($rRate);
$rRatings = $arrRate["Ratings"];
$rRated = $arrRate["Rated"];

// Deleting/Burning of books
if($session["user"]["superuser"] & SU_MANAGE_MODULES)
{
	output("`0[<a href='runmodule.php?module=greatlibrary&op=burn&id={$intBookID}' onClick=\"return confirm('Are you sure you want to delete this book?');\">`\$Delete`0</a>]",true);
	addnav("","runmodule.php?module-greatlibrary&op=burn&id=$intBookID");
}

if($strAuthorName == null){
	$strAuthorName = "Unknown Author";
}
if(get_module_setting("nocolours") == 1){
	$strName = prevent_colors($strName);
}
$strContent = str_replace("\n", "`n", $strContent);
$strSubmitRating = translate_inline("Rate this book");
rawoutput("<h2>");
output("$strName");
rawoutput("</h2>");
output("Written by `b%s`b",$strAuthorName);
output("`n`n$strContent`n`n");
if($strRate == 1){ 
    output("`\$Rating for this book has been Disabled.`0"); 
}else{
    if($rRated == 1){
        output("`&You have already rated this book. `nYou gave it a rating of `\$%s`&. `nThe book now has an average rating of %0.1f",$rRatings,$strAvgRating);
    }else{
        output("`&You've not rated this book yet, would you like to? `nThe books current rating is %0.1f",$strAvgRating);
        rawoutput("<form method=\"post\" action=\"$strAction\">");
        rawoutput("<select name=\"ratings\"><option>1</option><option>2</option><option>3</option><option>4</option><option>5</option></select>");
        rawoutput("<input type=\"hidden\" name=\"id\" value=\"$intBookID\">");
        rawoutput("<input type=\"hidden\" name=\"authorid\" value=\"$intPlayer\">");
        rawoutput("<br /><input type=\"submit\" value=\"$strSubmitRating\" />");
        rawoutput("</form>");
    }
}
$intCarried = httpget("cr");
if($intCarried == 1){
	villagenav();
}else{
	if($intPlayer == $intAuthor){
		$boolRevise = true;
		if(get_module_setting("adminonly") == 1){
			if(!($session["user"]["superuser"] & 16)){
				$boolRewrite = false;
			}
		}
		if($boolRevise == true){
			addnav("Revise this book", "runmodule.php?module=greatlibrary&op=revise&id=$intBookID");
		}
	}
	if(get_module_setting("allowcheckout") == true){
		$strSQL = "SELECT Book FROM  ".db_prefix("bookcarry")." WHERE Book = $intBookID AND Owner = $intPlayer";
		$queCarrying = db_query($strSQL);
		if(db_num_rows($queCarrying) == 0){
			addnav("Check out this book", "runmodule.php?module=greatlibrary&op=checkout&id=$intBookID");
		}else{
			addnav("Replace this book", "runmodule.php?module=greatlibrary&op=replace&id=$intBookID");
		}
	}
	addnav("Continue browsing", "runmodule.php?module=greatlibrary&op=browse");
}
//if($session["user"]["superuser"] & SU_MANAGE_MODULES){
//	addnav("Burn Book", "runmodule.php?module=greatlibrary&op=burn&id=$intBookID");
//}
?>