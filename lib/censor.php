<?php
// translator ready
// addnews ready
// mail ready
// function soap($input,$debug=false,$skiphook=false){
// 	global $session;
// 	require_once("lib/sanitize.php");
// 	$final_output = $input;
// 	// $output is the color code-less (fully sanitized) input against which
// 	// we search.
// 	$output = full_sanitize($input);
// 	return $final_output;
// }



// translator ready
// addnews ready
// mail ready
function soap($input,$debug=false,$skiphook=false){
	global $session;
	// $naughty_words = file_get_contents('./game_cache/naughtywords.csv');
	// $naughty_words = explode(', ',$naughty_words);
	// $final_output = str_ireplace($naughty_words,'cuss',$input);
	// return $final_output;
	return $input;
}

function good_word_list(){
	$sql = "SELECT * FROM " . db_prefix("nastywords") . " WHERE type='good'";
	$result = db_query_cached($sql,"goodwordlist");
	$row = db_fetch_assoc($result);
	return explode(" ",$row['words']);
}

function nasty_word_list(){
	$search = datacache("nastywordlist",600);
	if ($search!==false && is_array($search)) return $search;

	$sql = "SELECT * FROM " . db_prefix("nastywords") . " WHERE type='nasty'";
	$result = db_query($sql);
	$row = db_fetch_assoc($result);
	$search = " ".$row['words']." ";
	$search = preg_replace('/(?<=.)(?<!\\\\)\'(?=.)/', '\\\'', $search);
	$search = str_replace("a",'[a4@ªÀÁÂÃÄÅàáâãäå]',$search);
	$search = str_replace("b",'[bß]',$search);
	$search = str_replace("d",'[dÐÞþ]',$search);
	$search = str_replace("e",'[e3ÉÊËÈèéêë]',$search);
	$search = str_replace("n",'[nÑñ]',$search);
	$search = str_replace("o",'[o°º0ÒÓÔÕÖØðòóôõöø¤]',$search);
	$search = str_replace("p",'[pÞþ¶]',$search);
	$search = str_replace("r",'[r®]',$search);
//	$search = str_replace("s",'[sz$§]',$search);
	$search = preg_replace('/(?<!\\\\)s/','[sz$§]',$search);
	$search = str_replace("t",'[t7+]',$search);
	$search = str_replace("u",'[uÛÜÙÚùúûüµ]',$search);
	$search = str_replace("x",'[x×¤]',$search);
	$search = str_replace("y",'[yÝ¥ýÿ]',$search);
	//these must happen in exactly this order:
	$search = str_replace("l",'[l1!£]',$search);
	$search = str_replace("i",'[li1!¡ÌÍÎÏìíîï]',$search);
	$search = str_replace("k",'c',$search);
	$search = str_replace("c",'[c\\(kç©¢]',$search);
	$start = "'\\b";
	$end = "\\b'iU";
	$ws = "[^[:space:]\\t]*"; //whitespace (\w is not hungry enough)
	//space not preceeded by a star
	$search = preg_replace("'(?<!\\*) '",")+$end ",$search);
	//space not anteceeded by a star
	$search = preg_replace("' (?!\\*)'"," $start(",$search);
	//space preceeded by a star
	$search = str_replace("* ",")+$ws$end ",$search);
	//space anteceeded by a star
	$search = str_replace(" *"," $start$ws(",$search);
	$search = "$start(".trim($search).")+$end";
	$search = str_replace("$start()+$end","",$search);
	$search = explode(" ",$search);
	updatedatacache("nastywordlist",$search);
	return $search;
}
?>
