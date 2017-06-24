<?php

function experience($L) {
	//-Converts level into experience
	$a=0;
	for($x=1; $x<$L; $x++) {
		$a += floor($x+300*pow(2, ($x/7)));
	}
	return floor($a/4);
}

function level($experience){
	//-Converts experience into level
	$base_num = 1;
	while($experience>=experience($base_num)){
 		$current = $base_num;
		$base_num++;
	}
	return $current;
}

function nextlevel($experience){
	//Calculates what our next level would be
	$base_num = 1;
	while($experience>=experience($base_num)){
 		$current = $base_num;
		$base_num++;
	}
	return $base_num;
}

function expgainedforlevel($experience){
	//Checks how far in we are for this level - exp-wise
	//This is mainly for progression bars
	$level = level($experience);
	$levels_exp = experience($level);
	$gained = $experience-$levels_exp;
	return $gained;
}

function expforlevel($experience){
	$current_level = level($experience);
	$current_level = experience($current_level);
	$next_level = nextlevel($experience);
	$next_level = experience($next_level);
	$exp_needed = $next_level-$current_level;
	return $exp_needed;
}
?>