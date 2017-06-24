<?php

function closed_counter_getmoduleinfo()
{
	$info = array(
		"name"=>"Closed Counter",
		"author"=>"`b`&Stephen Kise`b",
		"category"=>"General",
		"version"=>"0.1b",
		"download"=>"nope", //I know that I do not need the 'nope' here, or the download at all. I choose to say nope because I can do whatever the fuck I want. Lata.
		"settings"=>array(
			"Closed Counter Settings,title",
			"count"=>"How many times have we closed?,range,0,3",
			)
		);
	return $info;
}

function closed_counter_install()
{
	module_addhook_priority('index',1);
	return TRUE;
}

function closed_counter_uninstall()
{
	return TRUE;
}


function closed_counter_dohook($hook,$pirate)
{
	$amt = get_module_setting('count');
	$msgs = array(0=>"`iWe have not closed... Yet ;)`i",1=>"`iWe are back in business, baby!`i",2=>"We will get it right this time, guys!",3=>"Please, just donate to us so we can waste your money some more.");
	output("`b`i`^Closed Counter`i`b`n`QWe have closed `@$amt `Qtimes!`n{$msgs[$amt]}`n`n");
	//Should already be centered. If no, swap with message below.
	//output("`c`b`i`^Closed Counter`i`b`n`QWe have closed `@$amt `Qtimes!`n{$msgs[$amt]}`n`c");

	return $pirate;
}

?>