<?php
// translator ready
// addnews ready
// mail ready
//This is a data caching library intended to lighten the load on lotgd.net
//use of this library is not recommended for most installations as it raises
//the issue of some race conditions which are mitigated on high volume
//sites but which could cause odd behavior on low volume sites, with out
//offering much if any advantage.

//basically the idea behind this library is to provide a non-blocking
//storage mechanism for non-critical data.
$datacache = array();
$datacachefilepath = "";
$checkedforolddatacaches = false;

function datacache($name, $duration=300){
	global $datacache;
	if (getsetting("usedatacache",0)){
		if (isset($datacache[$name])){
			return $datacache[$name];
		}else{
			$fullname = makecachetempname($name);
			if (@filemtime($fullname) > (time()-$duration)){
				$fullfile = @file_get_contents($fullname);
				if ($fullfile > ""){
					$datacache[$name] = json_decode($fullfile, true);
					return $datacache[$name];
				}else{
					return false;
				}
			}
		}
	}
	return false;
}

function updatedatacache($name,$data){
	global $datacache;
	if (getsetting("usedatacache",0)){
		$fullname = makecachetempname($name);
		$datacache[$name] = $data;
		$fp = @fopen($fullname,"w");
		if ($fp){
			if (!fwrite($fp,json_encode($data))){
			}else{
			}
			fclose($fp);
		}else{
		}
		return true;
	}
	return false;
}

function invalidatedatacache($name,$full=false){
	global $datacache;
	if (getsetting("usedatacache",0)){
		if(!$full) $fullname = makecachetempname($name);
		else $fullname = $name;
		if (file_exists($fullname)) @unlink($fullname);
		unset($datacache[$name]);
	}
}

function massinvalidate($name,$dir=false) {
	if (getsetting("usedatacache",0)){
		global $datacachefilepath;
		if ($datacachefilepath=="") $datacachefilepath = getsetting("datacachepath","/tmp");
		if ($dir) $datacachefilepath.="/".$dir;
		$cachepath = dir($datacachefilepath);
		while(false !== ($file = $cachepath->read())) {
			if (strpos($file, $name) !== false) {
				invalidatedatacache($cachepath->path."/".$file,true);
			}
		}
		$cachepath->close();
	}
}

function invalidatealldatacache(){
	global $datacache, $datacachefilepath;
    $iterator = new RecursiveDirectoryIterator($datacachefilepath);
	foreach (new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST) as $file) {
		debug('Deleted '.$file->getPathname());
		if (substr($file->getPathname(),-2) != (".." || "/.")) {
			if ($file->isDir()) rmdir($file->getPathname());
			else unlink($file->getPathname());
		}
	}
	$datacache = [];
}

function makecachetempname($name){
	global $datacache, $datacachefilepath,$checkedforolddatacaches;
	if ($datacachefilepath=="") $datacachefilepath = getsetting("datacachepath","/tmp");
	$path = pathinfo($name);
	if (!file_exists($datacachefilepath."/".$path['dirname'])){
		@mkdir($datacachefilepath."/".$path['dirname'],0777,1);
	}
	$fullname = $datacachefilepath."/".$name;
	$fullname = preg_replace("'//'","/",$fullname);
	$fullname = preg_replace("'\\\\'","\\",$fullname);
	if ($checkedforolddatacaches==false){
		$checkedforolddatacaches=true;
	}
	return $fullname;
}
?>
