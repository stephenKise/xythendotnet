<?php
function radio_getmoduleinfo(){
	$info = array(
		"name"=>"Radio",
		"version"=>"1.0",
		"author"=>"`&Senare, modified by `i`)Ae`7ol`&us`i`0",
		"category"=>"General",
		"override_forced_nav"=>true,
		"allowanonymous"=>true,
		"settings"=>array(
			"Radio Settings,title",
			"requests"=>"Requests,text|",
		),
	);
	return $info;
}

function radio_install(){
	module_addhook("charstats");
	return true;
}

function radio_uninstall(){
	return true;
}

function radio_dohook($hookname,$args){
	switch($hookname){
		case "charstats":
			$fullname.="<a href='runmodule.php?module=radio&op=index' onClick=\"".popup("runmodule.php?module=radio&op=index").";return false;\" target='_blank' align='center'>Listen</a>";
			addcharstat("Other");
			addcharstat("Radio", $fullname);
			addnav("","runmodule.php?module=radio&op=index");
		break;
	}
	return $args;
}

function radio_run(){
	global $session;
	
	popup_header("Radio Xythen");
	output("`c`bRadio Xythen`b`c `n`n");
	$dj = get_module_setting("dj","djbutton");
	$req = get_module_setting("req","djbutton");	
	$op = httpget('op');
	switch ($op){
		case "index":
		require_once('lib/mobiledetect.php');
		$detect = new Mobile_Detect();
		
		// Check for any mobile device.
		if ($detect->isMobile()){
			rawoutput("<a href='http://cp8.shoutcheap.com:2199/tunein/xythen.pls' target='_blank'>Click here to open the radio!</a></center><br>");
		}else{
			rawoutput("<center><div id=\"container\"><audio controls autoplay><source src='http://69.175.94.98:8228/;stream&lang=auto&codec=mp3&volume=75' type='audio/mpeg'></audio><br>");
		}
			
			output_notl("`c");
			
			if ($session['user']['loggedin'] && $req) output_notl("`2[`0<a href='runmodule.php?module=radio&op=request' target='_blank'>Request a song</a>`2]`n`n", true);
			if ($dj){
				$rn = db_fetch_assoc(db_query("SELECT name FROM ".db_prefix('accounts')." WHERE acctid = $dj"));
				output("`&Current DJ: %s`0`n",$rn['name']);
				output_notl("<script language=\"javascript\" type=\"text/javascript\" src=\"http://cp8.shoutcheap.com:2199/system/streaminfo.js\"></script>
				`&Current song: <a href=\"http://cp8.shoutcheap.com:2199/tunein/xythen.pls\" id=\"cc_strinfo_song_xythen\" class=\"cc_streaminfo\">Loading ...</a>`n`n", true);
			}
			if ($dj == $session['user']['acctid'] && $session['user']['loggedin']){
				$requests = unserialize(get_module_setting('requests'));
				if (!is_array($requests)) $requests = array();
				$count = count($requests);
				output_notl("`#There are `&`b<span id='radioreqcount'>$count</span>`b`# requests awaiting!`n`n", true);
				
				rawoutput("<script type='text/javascript'>
				function requests_update(){
					var xmlhttp;
					if (window.XMLHttpRequest) {
						xmlhttp = new XMLHttpRequest();
					} else { 
						xmlhttp = new ActiveXObject('Microsoft.XMLHTTP');
					}
					xmlhttp.open('GET','ajax_execute.php?file=ajax/php/radio.php',true);
					xmlhttp.send();
					xmlhttp.onreadystatechange = function() {
						if (xmlhttp.readyState==4 && xmlhttp.status==200){
							document.getElementById('radioreqcount').innerHTML = xmlhttp.responseText;
						}
					}
					setTimeout(function(){requests_update()}, 15000);
				}
				requests_update();
			</script>");
			}
		break;
		case "request":
			if (!$req){
				output("`\$`cRequests turned off!`c`n`n`0");
				popup_footer();
			}
			$p = httpallpost();
			if ($p['song'] > "" && $p['artist'] > ""){
				$requests = unserialize(get_module_setting('requests'));
				if (!is_array($requests)) $requests = array();
				$info = array("user" => $session['user']['acctid'], "song" => $p['song'], "artist" => $p['artist'], "dedicate" => $p['dedicate']);
				if (!in_array($info, $requests)) $requests[] = $info;
				set_module_setting("requests", serialize($requests));
				output("`#Request has been added!`0`n`n");
			}
			$form = array(
				"Request A Song!,title",
				"song" => "Name of song",
				"artist" => "Artist of song",
				"dedicate" => "Dedicated to:`n(Optional)",
			);
			require_once("lib/showform.php");
			rawoutput("<form action='runmodule.php?module=radio&op=request' method='post'>");
			showform($form, array());
			rawoutput("</form>");
			if ($isdj){
				output("`#`bRequests:`b`0`n`n");
				$requests = unserialize(get_module_setting('requests'));
				if (!is_array($requests)) $requests = array();
				foreach ($requests as $key => $r){
					$rn = db_fetch_assoc(db_query("SELECT name FROM ".db_prefix('accounts')." WHERE acctid = {$r['user']}"));
					output_notl("`^Requester: `&%s `^Song: `&%s `^Artist: `&%s", $rn['name'], $r['song'], $r['artist'], true);
					if ($r['dedicate']) output("`^Dedicated To: `&%s`0", $r['dedicate']);
					output_notl("<a href='runmodule.php?module=radio&op=del&id=$key'>[Del]</a>`n", true);
				}
				if (!count($requests)) output("`&None.`0");
			}
			output_notl("`n`n");
		break;
		case "del":
			$requests = unserialize(get_module_setting('requests'));
			if (!is_array($requests)) $requests = array();
			unset($requests[httpget('id')]);
			set_module_setting("requests", serialize($requests));
			header("Location: runmodule.php?module=radio&op=request");
		break;
	}
	
	popup_footer();
}
?>