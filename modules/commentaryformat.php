<?php

function commentaryformat_getmoduleinfo()
{
	$info = array(
		"name" => "Commentary Formatting",
		"version" => "1.0",
		"author" => "`i`)Ae`7ol`&us`i`0",
		"category" => "Commentary",
		"settings" => array(
			"Commentary Formatting Settings,title",
			"bold" => "Allow bold?,bool|1",
			"bold_code" => "Code for bold,text|`b",
			"italics" => "Allow italics?,bool|1",
			"italics_code" => "Code for bold,text|`i",
			"center" => "Allow center?,bool|0",
			"center_code" => "Code for bold,text|`c",
			"underline" => "Allow underline?,bool|1",
			"underline_code" => "Code for bold,text|`u",
			"strikethrough" => "Allow strikethrough?,bool|1",
			"strikethrough_code" => "Code for bold,text|`s",
			"justify" => "Justify text?,bool|1",
		),
	);
	return $info;
}

function commentaryformat_install(){
	module_addhook("viewcommentary");
	return true;
}

function commentaryformat_uninstall(){
	return true;
}

function commentaryformat_dohook($hookname, $args){
	global $session;
	
	switch ($hookname){
		case "viewcommentary":
			$set = get_all_module_settings();
			$val = $args['commentline'];
			
			if (!$set['bold']) $val = str_replace("`".$set['bold_code'], "", $val);
			if (!$set['italics']) $val = str_replace("`".$set['italics_code'], "", $val);
			if (!$set['center']) $val = str_replace("`".$set['center_code'], "", $val);
			if (!$set['underline']) $val = str_replace("`".$set['underline_code'], "", $val);
			if (!$set['strikethrough']) $val = str_replace("`".$set['strikethrough_code'], "", $val);
			
			$countb = substr_count($val, $set['bold_code']);
			$counti = substr_count($val, $set['italics_code']);
			$countc = substr_count($val, $set['center_code']);
			$countu = substr_count($val, $set['underline_code']);
			$counts = substr_count($val, $set['strikethrough_code']);
			
			if (strstr($val,$set['underline_code'])){
				$exu = explode("`".$set['underline_code'],$val);
				for ($i = 0; $i < count($exu); $i++){
					if ($i != count($exu)-1){
						if (!($i%2)) $exu[$i] = $exu[$i] . "<u>";
						else $exu[$i] = $exu[$i] . "</u>";
					}
				}
				$val = implode("",$exu);
			}
			
			if (strstr($val,$set['strikethrough_code'])){
				$exs = explode("`".$set['strikethrough_code'],$val);
				for ($i = 0; $i < count($exs); $i++){
					if ($i != count($exs)-1){
						if (!($i%2)) $exs[$i] = $exs[$i] . "<s>";
						else $exs[$i] = $exs[$i] . "</s>";
					}
				}
				$val = implode("",$exs);
			}
			
			if ($countb%2) $val .= "`".$set['bold_code'];
			if ($counti%2) $val .= "`".$set['italics_code'];
			if ($countc%2) $val .= "`".$set['center_code'];
			if ($countu%2) $val .= "</u>";
			if ($counts%2) $val .= "</s>";
			
			$val = appoencode($val, true);
			
			$args['commentline'] = $val;
		break;
	}
	return $args;
}
?>