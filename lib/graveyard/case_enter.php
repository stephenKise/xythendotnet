<?php
$max = $session['user']['level'] * 5 + 50;
$favortoheal = round(10 * ($max-$session['user']['soulpoints'])/$max);
output("`)`b`cThe Lair`c`b");
output("You walk into the Lair. You have been doing various jobs to get enough favor with Jester, or at least you hope so. You see that the main area as you walk in is just as toxic as The Pit. It has some statues that are place strategically. Wait... are those statues? You look more closely and you are shocked! Those are not statues, but other people that had tried to escape without permission! You pray that you have enough, or you may not see another day! Nervously, you walk on towards what looks like a throne, where you see a figure sitting on it. Is it the Jester himself, or is it just another simulacrum, like the others you had seen in The Pit and The Wastelands? You keep going forward and are shocked when the figure moves and addresses you. `4What do you want, mortal?");
addnav(array("Question `\$%s`0 about the worth of your soul",$deathoverlord),"graveyard.php?op=question");
addnav(array("Restore Your Soul (%s favor)", $favortoheal),"graveyard.php?op=restore");
addnav("Places");
addnav("S?Land of the Shades","shades.php");
addnav("G?Return to the Graveyard","graveyard.php");
modulehook("mausoleum");
?>