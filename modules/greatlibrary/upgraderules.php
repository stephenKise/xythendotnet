<?php   

/***** v0.1 to v0.2 *****/
$strSQL = "DESCRIBE ".db_prefix("library");
$queTable = db_query($strSQL);
if(db_num_rows($queTable) == 4){
    $strSQL = "ALTER TABLE ".db_prefix("library")." ( ADD COLUMN AuthorName varchar(60) );";
    debug($strSQL);
    db_query($strSQL);
}
?>