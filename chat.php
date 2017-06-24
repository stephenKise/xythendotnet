 <?php
 global $session;
define("OVERRIDE_FORCED_NAV",true);
require_once("common.php");
require_once('lib/commentary.php');
require_once("lib/http.php");
popup_header('Popup Commentary!');
    commentdisplay('', $session['current_commentary_area'], "`@Converse with fellow Xythenians", 20, 'says', 'x');
popup_footer();
?>