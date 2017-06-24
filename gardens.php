<?php
// addnews ready
// translator ready
// mail ready
require_once("common.php");
require_once("lib/commentary.php");
require_once("lib/villagenav.php");
require_once("lib/events.php");
require_once("lib/http.php");

tlschema("gardens");

page_header("The Gardens");

addcommentary();
$skipgardendesc = handle_event("gardens");
$op = httpget('op');
$com = httpget('comscroll');
$refresh = httpget("refresh");
$commenting = httpget("commenting");
$comment = httppost('insertcommentary');
// Don't give people a chance at a special event if they are just browsing
// the commentary (or talking) or dealing with any of the hooks in the village.
if (!$op && $com=="" && !$comment && !$refresh && !$commenting) {
	if (module_events("gardens", getsetting("gardenchance", 0)) != 0) {
		if (checknavs()) {
			page_footer();
		} else {
			// Reset the special for good.
			$session['user']['specialinc'] = "";
			$session['user']['specialmisc'] = "";
			$skipgardendesc=true;
			$op = "";
			httpset("op", "");
		}
	}
}
if (!$skipgardendesc) {
	checkday();

	output("`b`c`2T`ghe `2G`gardens`0`c`b");
	output("`n`c`gAmidst the desolation caused by the great machine upheaval, a well tended path leads into a grove that brings to mind the peaceful beauty that once went unappreciated in the world of man.");
	output("`gThe gardens offer a botanical refuge for lovers and those seeking tranquility. You have no need to wear a gas mask here, as the air is completely free of all toxins due to the many plants soaking in the carbon dioxide poison.");
	output("`gSeveral sections of the cobblestone paths lead you towards different locations; benches, fountains, decorative sculptures, as well as a small babbling creek of the purest flowing waters. `c`n`n");
}

villagenav();
modulehook("gardens", array());

commentdisplay("", "gardens","Whisper here",30,"whispers");

module_display_events("gardens", "gardens.php");
page_footer();
?>