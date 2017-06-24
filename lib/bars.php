<?php


//Just show a bar.  Nothing more, nothing less.
function simplebar($cur, $max, $width=70, $height=5, $bgcol="222222", $barcol="F6FF00"){
	if ($cur<$max){
		$pct = ($cur/$max)*100;
		$nonpct = 100-$pct;
		$px = ($width/100)*$pct;
		$nonpx = $width-$px;
		$bar = "<table style='border: solid 1px #000000' bgcolor='#".$bgcol."' cellpadding='0' cellspacing='0' width='".$width."' height='".$height."'><tr><td width='".$px."' bgcolor='#".$barcol."'></td><td width='".$nonpx."'></td></tr></table>";
	} else {
		$bar = "<table style='border: solid 1px #000000' bgcolor='#".$barcol."' cellpadding='0' cellspacing='0' width='".$width."' height='".$height."'><tr><td></td></tr></table>";
	}
	return $bar;
}

//MAKE MAX FOR INBOX AS 50

function inboxbar($unread, $archived, $read, $max=100, $width=300, $height=5){
	if (($unread+$archived)<$max){
		$non = ($unread+$read+$archived);
		$non = 100-$non;
		$non = ($width/100)*$non;
		$unread = ($width/100)*$unread;
		$archived = ($width/100)*$archived;
		$read = ($width/100)*$read;
 		$bar = "<table style='border: solid 1px #000000' bgcolor='transparent' cellpadding='0' cellspacing='0' width='300px' height='5px'><tr><td width='{$unread}px' bgcolor='#FFFF00'></td><td width='{$archived}px' bgcolor='#0099FF'></td><td width='{$read}px' bgcolor='#353535'></td><td width='{$non}px'></td></tr></table>";
	} else {
		$bar = "<table style='border: solid 1px #000000' bgcolor='#C11B17' cellpadding='0' cellspacing='0' width='300px' height='5px'><tr><td></td></tr></table>";
	}
	return $bar;
}

function multibar($cur, $max, $width=70, $height=5, $bgcol="222222", $barcols=false){
	if (!$barcols){
		$barcols = array(
			"F6FF00", //yellow
			"660088", //purple
			"FFA000", //orange
			"0000FF", //blue
			"00FCFF", //cyan
			"00FF00", //green
		);
	}
	if ($cur>$max){
		$cindex = (ceil($cur/$max))-1;
		$final = (count($barcols)-1);
		if ($cindex > $final){
			$bar = simplebar(100, 100, $width, $height, $barcols[$final], $barcols[$final]);
			return $bar;
		}
		$barcol = $barcols[$cindex];
		$bindex = $cindex-1;
		$bgcol = $barcols[$bindex];
		$adjustedcur = $cur - ($max*($cindex));
		
		$bar = simplebar($adjustedcur, $max, $width, $height, $bgcol, $barcol);
	} else {
		$bar = simplebar($cur, $max, $width, $height, $bgcol);
	}
	return $bar;
}

/*
	FLEXIBAR
	When the bar fills beyond 100%, it changes colour and expands, with the section over 100% filled with a different colour.  Used in situations where the player _can_ exceed a value, but really shouldn't.  Don't use this in charstats, because it can stretch out the stats section if the numbers go too wacky.
	
	Suggested uses:
	Weight limit for backpacks or other inventory systems that encumber the player if they go above a certain weight.
	Anything with a quasi-soft limit where it needs to be immediately apparent if the limit is exceeded.
*/

function flexibar($cur, $max, $width=70, $height=5, $bgcol="222222", $barcol="00FF00", $barcol2="990000", $overcol="FF0000"){
	$pct = ($cur/$max)*100;
	$nonpct = 100-$pct;
	$px = ($width/100)*$pct;
	$nonpx = $width-$px;

	if ($nonpx>0){
		$bar = "<table style='border: solid 1px #000000' width='".$width."' height='".$height."' bgcolor='#".$bgcol."' cellpadding=0 cellspacing=0><tr><td width='".$px."' bgcolor='#".$barcol."'></td><td width='".$nonpx."'></td></tr></table>";
	} else {
		$over = $px-$width;
		$twidth = $over+$width;
		$bar = "<table style='border: solid 1px #000000' width='".$twidth."' height='".$height."' bgcolor='#".$barcol2."' cellpadding=0 cellspacing=0><tr><td width='".$width."' bgcolor='#".$barcol2."'></td><td width='".$over."' bgcolor='#".$overcol."'></td></tr></table>";
	}
	return $bar;
}

/*
	SWITCHBAR
	This bar changes colour once it is filled, in order to draw the player's attention to its fullness.
	
	Suggested uses:
	Experience bars in standard LotGD core.
	Combat talents that must be charged before they can be fired.
*/

function switchbar($cur, $max, $width=70, $height=5, $bgcol="222222", $barcol="F6FF00", $fullcol="00FF00"){
	if ($cur<$max){
		$bar = simplebar($cur, $max, $width, $height, $bgcol, $barcol);
	} else {
		$bar = simplebar($cur, $max, $width, $height, $fullcol, $fullcol);
	}
	return $bar;
}

/*
	FADEBAR
	This bar fades smoothly from red to green as it fills or empties, depending on the $reverse argument passed.
	
	Suggested uses:
	Makes a real nice hitpoint representation.
*/

function fadebar($cur, $max, $width=70, $height=5, $bgcol="222222", $reverse=false){
	$pct = ($cur/$max)*100;
	$nonpct = 100-$pct;
	$px = ($width/100)*$pct;
	$nonpx = $width-$px;
	
	if ($cur < $max){	
		if ($pct<50){
			$red = 5*$pct;
			$redhex = dechex($red);
			$greenhex = "FF";
		} else {
			$green = 5*$nonpct;
			$greenhex = dechex($green);
			$redhex = "FF";
		}
		
		//check for missing characters
		if (strlen($greenhex)<2){
			$greenhex = "0".$greenhex;
		} else if (strlen($redhex)<2){
			$redhex = "0".$redhex;
		}
		
		if ($reverse){
						$barcol = $redhex.$greenhex."00";
		} else {
			$barcol = $greenhex.$redhex."00";
		}
		$bar = simplebar($cur, $max, $width, $height, $bgcol, $barcol);
	} else {
		if ($reverse){
			$barcol = "FF0000";
		} else {
			$barcol = "00FF00";
		}
		$bar = simplebar($cur, $max, $width, $height, $bgcol, $barcol);
	}
	return $bar;
}

?>