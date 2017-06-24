<?php
// newday runonce
	// Let's do a new day operation that will only fire off for
	// one user on the whole server.
	// run the hook.
	modulehook("newday-runonce",array());

	// Do some high-load-cleanup

	// Moved from lib/datacache.php
	if (getsetting("usedatacache",0)){
		$handle = opendir($datacachefilepath);
		while (($file = readdir($handle)) !== false) {
			if (substr($file,0,strlen(DATACACHE_FILENAME_PREFIX)) ==
					DATACACHE_FILENAME_PREFIX){
				$fn = $datacachefilepath."/".$file;
				$fn = preg_replace("'// '","/",$fn);
				$fn = preg_replace("'\\\\'","\\",$fn);
				if (is_file($fn) && filemtime($fn) < strtotime("-24 hours")){
					unlink($fn);
				}
			}
		}
	}
	// Expire Chars
	require_once("lib/expire_chars.php");

	// Clean up old mails
	$sql = "DELETE FROM " . db_prefix("mail") . " WHERE sent<'".date("Y-m-d H:i:s",strtotime("-".getsetting("oldmail",14)."days"))."' AND seen = 0";
	db_query($sql);
	massinvalidate("mail");
	
	// Clean up old bans
	db_query("DELETE FROM " . db_prefix("bans") . " WHERE banexpire < \"".date("Y-m-d")."\" AND banexpire>'0000-00-00'");
	
	if (getsetting("expirecontent",180)>0){
		// Clean up debug log, moved from there
		$timestamp = date("Y-m-d H:i:s",strtotime("-".round(getsetting("expirecontent",180)/10,0)." days"));
		$sql = "DELETE FROM " . db_prefix("debuglog") . " WHERE date <'$timestamp'";
 	   	db_query($sql);

		// Clean up old comments
		// Changed it to "NOT LIKE 'superuser%'", in case we add more SU chats in the future.
		$sql = "DELETE FROM " . db_prefix("commentary") . " WHERE postdate<'".date("Y-m-d H:i:s",strtotime("-".getsetting("expirecontent",180)." days"))."' AND section NOT LIKE 'superuser%' AND section NOT LIKE 'pet-%'";
		db_query($sql);
		
		// Clean up old moderated comments
		$sql = "DELETE FROM " . db_prefix("moderatedcomments") . " WHERE moddate<'".date("Y-m-d H:i:s",strtotime("-".getsetting("expirecontent",180)." days"))."'";
		db_query($sql);
	}
	if (strtotime(getsetting("lastdboptimize", date("Y-m-d H:i:s", strtotime("-1 day")))) < strtotime("-1 day"))
	require_once("lib/newday/dbcleanup.php");
?>