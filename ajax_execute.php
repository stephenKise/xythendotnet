<?php

header('content-type: text/html; charset=utf-8');

define("OVERRIDE_FORCED_NAV", true);

require_once("common.php");

$file = httpget('file');
if($file == "")
{
	$file = httppost('file');
}

require_once "modules/".$file;

?>
