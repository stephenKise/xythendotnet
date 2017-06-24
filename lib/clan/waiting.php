<?php
	page_header("Guild");
	addnav("Guild Options");
	output("`b`c`&Guild`c`b`n`n`7You must wait until you are approved to access your potential new guild. Other citizens seem to be waiting as well... Hopefully your application will spark interest to the Guild Masters.");
	commentdisplay("", "waiting","Speak",25);
	if ($session['user']['clanrank']==CLAN_APPLICANT) {
		addnav("Return to the Lobby","clan.php");
	} else {
		addnav("Return","clan.php");
	}
?>