<?php
// translator ready
// addnews ready
// mail ready
require_once("common.php");
require_once("lib/commentary.php");


tlschema("shades");

page_header("Land of the Shades");
addcommentary();
checkday();

if ($session['user']['alive']) redirect("village.php");
output("`7`cYou open your eyes to the realm of Xythen...However, it seems as though you are merely looking at the lives of others from a form that is not visible. You have become a ghost in this world, lost without purpose or direction for a time being... Rumor has it you can restore your life if you wait until the next day... Or you can test your luck with the Deity of Death. `n`nThe time reads %s on a large, grey faced clocktower.`n`n`c",getgametime());
modulehook("shades", array());
commentdisplay("`n`QNearby, some lost souls lament:`n", "shade","Despair",25,"despairs");

addnav("Log out","login.php?op=logout");
addnav("Locations");
addnav("The Graveyard","graveyard.php");
addnav("View the News","news.php");

tlschema("nav");

// the mute module blocks players from speaking until they
// read the FAQs, and if they first try to speak when dead
// there is no way for them to unmute themselves without this link.
addnav("Other");
//addnav("??F.A.Q. (Frequently Asked Questions)", "petition.php?op=faq",false,true);
//remove by Legacy^

if ($session['user']['superuser'] & SU_EDIT_COMMENTS){
	addnav("Superuser");
	addnav(",?Comment Moderation","moderate.php");
}
if ($session['user']['superuser']&~SU_DOESNT_GIVE_GROTTO){
	addnav("Superuser");
  addnav("X?Superuser Grotto","superuser.php");
}
if ($session['user']['superuser'] & SU_INFINITE_DAYS){
	addnav("Superuser");
  addnav("/?New Day","newday.php");
}

tlschema();

page_footer();
?>