<?php
// addnews ready
// translator ready
// mail ready
define("ALLOW_ANONYMOUS",true);
define("OVERRIDE_FORCED_NAV",true);
require_once("common.php");
require_once("lib/systemmail.php");
require_once("lib/output_array.php");
require_once("lib/http.php");
require_once("lib/stripslashes_deep.php");
$op = httpget('op');
if ($session['user']['loggedin']){
	output("<table width='100%' border='0' cellpadding='0' cellspacing='10px'>",TRUE);
	rawoutput("<tr><td valign=\"top\" width='150px' nowrap>");
	output("&bull;<a href='mail.php'>`tInbox</a>`n",TRUE);
	output("&bull;<a href='runmodule.php?module=outbox'>`tOutbox</a>`n",TRUE);
	output("&bull;<a href='mail.php?&op=address'>`tCompose</a>`n",TRUE);
	output("&bull;<a href='petition.php'>`\$Petition for Help</a>`n",TRUE);
	modulehook("mailfunctions");
	output_notl("</td><td>",true);
}
switch ($op) {
	case "primer": case "faq": case "faq1": case "faq2": case "faq3":
		require("lib/petition/petition_$op.php");
		break;
	default:
		require("lib/petition/petition_default.php");
		break;
}
if ($session['user']['loggedin']) rawoutput("</td></tr></table>",TRUE);
popup_footer();
?>