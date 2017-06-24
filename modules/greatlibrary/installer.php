<?php

if (!db_table_exists(db_prefix("library"))){
	$strSQL = "CREATE TABLE  " . db_prefix("library") . " (BookID int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY(BookID), BookName varchar(255), BookContent text, BookAuthorID int(11), BookAuthorName varchar(255), BookGenre varchar(255), BookViews int(11) NOT NULL, BookCarried int(11), BookRate int(11), AvgRating decimal(2,1) NOT NULL );";
	debug($strSQL);
	db_query($strSQL);
	if(!db_table_exists(db_prefix("library"))){
		debug("ERROR: Library table could not be created");
		return false;
	}
}

if (!db_table_exists(db_prefix("library_ratings"))){
	$strSQL = "CREATE TABLE  " . db_prefix("library_ratings") . " (RateID int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY(RateID), BookID int(11), RateAuthorID int(11), Rated int(11), Ratings int(11) );";
	debug($strSQL);
	db_query($strSQL);
	if(!db_table_exists(db_prefix("library_ratings"))){
		debug("ERROR: Library_ratings table could not be created");
		return false;
	}
}

if (!db_table_exists(db_prefix("bookcarry"))){
	$strSQL = "CREATE TABLE  " . db_prefix("bookcarry") . " (Book int(11), Owner int(11), PRIMARY KEY(Book, Owner), BookName varchar(255) );";
	debug($strSQL);
	db_query($strSQL);
	if(!db_table_exists(db_prefix("bookcarry"))){
		debug("ERROR: Bookcarry table could not be created");
		return false;
	}
}

?>