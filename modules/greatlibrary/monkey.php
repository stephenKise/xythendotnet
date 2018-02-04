<?php

$intDamage = round($session["user"]["maxhitpoints"] / 5, 0);
output("`\$OW!`0`n`n");
output("You lose `\$%s hitpoints`0 as the librarian flies from the shelves and hits you atop the head,", $intDamage);
output("snorting at you in a most disgruntled fashion.");
$session["user"]["hitpoints"] -= $intDamage;
if($session["user"]["hitpoints"] <= 0){
    output("`n`nYour dying screams serve only to infuriate the librarian more as he points");
    output("hysterically at a sign that reads 'Quiet Please'. `\$You are dead.`0");
    $session["user"]["alive"] = false;
    $session["user"]["hitpoints"] = 0;
    addnav("Daily News", "news.php");
}else{
    addnav("Stupid monkey!", "runmodule.php?module=greatlibrary&op=monkey");
    addnav("Return to the entrance", "runmodule.php?module=greatlibrary&op=enter");
}
?>