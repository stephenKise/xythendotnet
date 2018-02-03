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

	output("`b`c`b`)B`b`7o`b`gt`b`i`@a`kn`i`L`bi`b`lc`ia`il `)G`i`7a`gr`i`@d`b`ke`b`Ln`i`ls`i`0 `c`b");
	output("`n`c`7Among the bustling city, there lies one area unlike any" .
	" other location in the floating refuge. A place of green amidst a" . 
	" vast, grey metropolis. A well-tended path leads to a grove that reminds" .
	" those who visit of the beauty of nature. These gardens offer an" .
	" obvious refuge for lovers and those seeking the opportunity to cast" .
	" away worry and seek inner peace and tranquillity in this world of strife" .
	" and hardship. The air is clean and crisp, as if this patch of land had" .
	" not been planted above section after section of stone and steel." .
	" Several sections of the cobblestone paths lead you towards different" .
	" locations; benches, fountains, decorative sculptures, as well as a small" .
	" babbling creek of the purest flowing waters.`c");
}

villagenav();
modulehook("gardens", array());

commentdisplay("", "gardens","Whisper here",15,"whispers");

module_display_events("gardens", "gardens.php");
page_footer();
?>
