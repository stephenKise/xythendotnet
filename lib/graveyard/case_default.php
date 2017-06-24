<?php
if (!$skipgraveyardtext) {
	output("`)`c`bThe Graveyard`b`c");
	output("You have found yourself in a cluttered area filled with scrap metal of all sorts.Glancing around, you find many different objects that would create a multitude of items if one searched through the area with good intent on finding parts. You are currently in The Pit, which is the leftover scrap products of Xythen. Here is where broken down items go to be salvaged. Once a structure has been robbed of all of its important materials, it is discarded into a tunnel system that lands into a massive cave. This place is filled with toxic fumes, resulting from decaying machinery and flesh... The ground, composed of rusty metal and bone ? with pools of dubious liquids scattered around. Everywhere you see symbioses of dead machinery and parasitic organic life completing each other, savaging through the trash to build and awaken the dead with their stingers; all created by Jester, the ultimate mutant, and who ensures that no one ever escapes... without his consent...");
	output("In the center of the Pit is an odd looking building. Upon further inspection, you see that it is a type of Lair. The plaque above the door reads Jester, Overlord of Chaos and Death.");
	output("You decide this would be a good time to fight for your way back to Xythen until you know Jester is willing to let you return. Or maybe you should just wait until the next new day, knowing he will do so then.`n`n");
	output("The plaque above the door reads `\$%s`), Overlord of Death`).",$deathoverlord);
	modulehook("graveyard-desc");
}
modulehook("graveyard");
	if ($session['user']['gravefights']) {
	addnav("Look for Something to Torment","graveyard.php?op=search");
}
addnav("Places");
addnav("W?List Warriors","list.php");
addnav("S?Return to the Shades","shades.php");
addnav("M?Enter the Mausoleum","graveyard.php?op=enter");
module_display_events("graveyard", "graveyard.php");
?>