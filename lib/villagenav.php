<?php
function villagenav($extra=false)
{
	global $session;
	$myclan = db_fetch_assoc(db_query("SELECT clanname AS name FROM ".db_prefix("clans")." WHERE clanid = ".$session['user']['clanid']));
	$loc = $session['user']['location'];
	if ($loc == $myclan['name']) $loc = $session['user']['previouslocation'];
	if ($extra === false) $extra="";
	$args = modulehook("villagenav");
	if (array_key_exists('handled', $args) && $args['handled']) return;
	tlschema("nav");
	if ($session['user']['alive']) addnav(array("V?Return to %s", $loc), "village.php$extra");
	else addnav("S?Return to the Pit","shades.php");
	tlschema();
}
?>
