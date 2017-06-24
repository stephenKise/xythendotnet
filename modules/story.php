<?php

function story_getmoduleinfo(){
	$info = array(
		"name"=>"About Server",
		"version"=>"1.1",
		"allowanonymous"=>true,
		"author"=>"Robley Puddingcups, modifications by `&`bStephen Kise`b",
		"category"=>"Administrative",
		"download"=>"",
	);

	return $info;
}

function story_install(){
	module_addhook("aboutserver");
	return true;
}

function story_uninstall(){
	return true;
}

function story_dohook($hookname,$args){
	global $session;
	switch($hookname){
		case "aboutserver":
			output("`c`@About Xythen`c`n`^X`ty`mt`6h`te`^n`7; a peaceful realm, in which those who resided could live in harmony with one another. The unique races that had evolved and developed on the region thrived magnificently. As time expanded, the evolutionary process allowed for the residents of `^X`ty`mt`6h`te`^n`7 to create objects of epic proportion. In time, two scientists forged together to create a time travel device; unfortunately, when they attempted to test the stabilizer, something happened which was spectacularly unplanned.
			`n`n
			A group of portals opened; swirling with an energy that could fuel a thousand generators for an eternity. These portals soon expanded throughout the region; expanding like a shock-wave towards the outermost regions of the realm. These portals were opening and closing through the space time continuum and soon - these portals began to engulf residents of other realms.
			`n`n
			No one could predict where, or when, a portal will appear, as they had been known to go as far back as the very birth of a parallel universe, and as far as the death of the realm itself. The size of the portals could, and still can, be unpredictable as well. Most of them being able to whisk people away, while others can transport entire homes. However, there exists an endless number of diminutive portals that are unable to transport matter, but are vital in introducing to `^X`ty`mt`6h`te`^n`7 a newfound resource: Mana, the essence of magic.
			`n`n
			This, of course, was seen as frightening at first to the residents of `^X`ty`mt`6h`te`^n`7. The new technologies and knowledge that were brought forth from the portals was overwhelming to the residents that were there originally. However, in time, they began to realize how important it was for their cultures to mix, and how the diversity would allow the realm to grow. This would cause a new life to form in the realm; new technologies, new educational expansions, and new cultures that mix together with the original.
			`n`n
			A little note about portals: There are over one hundred portals scattered throughout the forest region of `^X`ty`mt`6h`te`^n`7, as well as the more treacherous regions. These portals are very well hidden, however, and there are those who have been known to walk right by their opaque qualities - or even stumble into them unknowingly. Some of the creatures and individuals who transport themselves through the portals do not survive the journey from their origin to a city of life, and they cannot turn back - for as soon as they go through, the portal closes to either re-open, or completely vanish.
			`n`n
			There are some - a very rare few - who have mastered the portals that reside around `^X`ty`mt`6h`te`^n`7. These individuals can travel from realm to realm and, in essence, travel back and forth through time. The only reason they have not caused a paradox issue, is the fact that once you leave `^X`ty`mt`6h`te`^n`7 through a portal you are the equivalent of a ghost to those around you.
			`n`n
			However, the old society that had come to rest within `^X`ty`mt`6h`te`^n`7 created a group of machines which lay dormant within the underground of Sylisten. These machines were the mental and spiritual replicas of those who found and raised `^X`ty`mt`6h`te`^n`7 from the ground; they were not happy with the innovation. They were displeased with the diversity that now spread across the land.
			`n`n
			As the rage grew from within the underground, the machines rebelled against their homeland of `^X`ty`mt`6h`te`^n`7. They awoke the most treacherous war machine known to the people of the realm - an ancient construct that was mysterious to even themselves... The `b`~T`b`)e`7n`mt`pr`i`Po`mm`i`7e`)c`b`~h`b`7. This giant structure resembled a gargantuan mechanical Kraken with countless powerful tentacles that allowed it to move across any terrain with ease, even lurking beneath the deep waters of `^X`ty`mt`6h`te`^n`7's polluted oceans. The creature had an internal recycling system, which would make the automated guns especially vile. With its large suction cups, the `b`~T`b`)e`7n`mt`pr`i`Po`mm`i`7e`)c`b`~h`b`7 could swallow up resources and burn them down into fuel. This fuel could be used to push the internal factories that made the millions of bullets.
			`n`n
			The `b`~T`b`)e`7n`mt`pr`i`Po`mm`i`7e`)c`b`~h`b`7  lay waste across the realm, burning down cities, consuming its resources to power itself and departing, leaving behind an infertile, and barren wasteland. It destroyed city after city, easily laying ruin to the strongholds of each race, until eventually, all races were forced to live together in the handful of remaining cities. United by desperation, the diverse races of `^X`ty`mt`6h`te`^n`7 grouped together to form the first alliance ever against the old ways of the realm. Together, the alliance battled the `b`~T`b`)e`7n`mt`pr`i`Po`mm`i`7e`)c`b`~h`b`7 in one final monumental stand, risking everything for the sake of their home.
			`n`n
			With luck, the alliance won the battle against the `b`~T`b`)e`7n`mt`pr`i`Po`mm`i`7e`)c`b`~h's`b`7 and the Rebel Machines. Despite the great casualties, the alliance managed to confine the `b`~T`b`)e`7n`mt`pr`i`Po`mm`i`7e`)c`b`~h`b `7into its original lair - a large mountain within a polluted lagoon filled with oil and waste products.
			`n`n
			However, the mountain is not at all dormant to this day, even after the `b`~T`b`)e`7n`mt`pr`i`Po`mm`i`7e`)c`b`~h's`b`7 was brutally damaged. The Rebel Machines work daily on repairing the `b`~T`b`)e`7n`mt`pr`i`Po`mm`i`7e`)c`b`~h's`b`7 structure, as well as once again reinforcing themselves a tremendous army that will one day lay chaos across the land.
			`n`n
			And that is where you come in, young one. Destiny has been stretched across space and time, and has pulled you to the realm of `^X`ty`mt`6h`te`^n`7, where you - in this diverse, innovative age - may thrive to destroy the remnants of the old society and its war machine. Live and flourish, young creature - make your mark in the realm of `^X`ty`mt`6h`te`^n`7.");
		break;
	}
	return $args;
}
?>