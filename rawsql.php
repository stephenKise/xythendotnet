<?php
// translator ready
// addnews ready
// mail ready
require_once("common.php");
require_once("lib/http.php");

tlschema("rawsql");

check_su_access(SU_RAW_SQL);

page_header("Raw SQL/PHP execution");
require_once("lib/superusernav.php");
superusernav();
addnav("Execution");
addnav("SQL","rawsql.php");
addnav("PHP","rawsql.php?op=php");
rawoutput("<script type=\"text/javascript\">
function setSelectionRange(input, selectionStart, selectionEnd) {
  if (input.setSelectionRange) {
    input.focus();
    input.setSelectionRange(selectionStart, selectionEnd);
  }
  else if (input.createTextRange) {
    var range = input.createTextRange();
    range.collapse(true);
    range.moveEnd('character', selectionEnd);
    range.moveStart('character', selectionStart);
    range.select();
  }
}

function replaceSelection (input, replaceString) {
	if (input.setSelectionRange) {
		var selectionStart = input.selectionStart;
		var selectionEnd = input.selectionEnd;
		input.value = input.value.substring(0, selectionStart)+ replaceString + input.value.substring(selectionEnd);
    
		if (selectionStart != selectionEnd){ 
			setSelectionRange(input, selectionStart, selectionStart + 	replaceString.length);
		}else{
			setSelectionRange(input, selectionStart + replaceString.length, selectionStart + replaceString.length);
		}

	}else if (document.selection) {
		var range = document.selection.createRange();

		if (range.parentElement() == input) {
			var isCollapsed = range.text == '';
			range.text = replaceString;

			 if (!isCollapsed)  {
				range.moveStart('character', -replaceString.length);
				range.select();
			}
		}
	}
}


// We are going to catch the TAB key so that we can use it, Hooray!
function catchTab(item,e){
	if(navigator.userAgent.match(\"Gecko\")){
		c=e.which;
	}else{
		c=e.keyCode;
	}
	if(c==9){
		replaceSelection(item,String.fromCharCode(9));
		setTimeout(\"document.getElementById('\"+item.id+\"').focus();\",0);	
		return false;
	}
		    
}
</script>");

$op = httpget("op");
if ($op=="" || $op=="sql"){
	$sql = httppost('sql');
	if ($sql != "") {
		$sql = stripslashes($sql);
		modulehook("rawsql-execsql",array("sql"=>$sql));
		debuglog('Ran Raw SQL: ' . $sql);
		stafflog("",$session['user']['acctid'],$session['user']['acctid'],"".$session['user']['name']." Ran Raw SQL: ". $sql ." ","0");
		$r = db_query($sql, false);
		if (!$r) {
			output("`\$SQL Error:`& %s`0`n`n",db_error($r));
		} else {
			if (db_affected_rows() > 0) {
				output("`&%s rows affected.`n`n",db_affected_rows());
			}
			rawoutput("<table cellspacing='1' cellpadding='2' border='0' bgcolor='#999999'>");
			$number = db_num_rows($r);
			for ($i = 0; $i < $number; $i++) {
				$row = db_fetch_assoc($r);
				if ($i == 0) {
					rawoutput("<tr class='trhead'>");
					$keys = array_keys($row);
					foreach ($keys as $value) {
						rawoutput("<td>$value</td>");
					}
					rawoutput("</tr>");
				}
				rawoutput("<tr class='".($i%2==0?"trlight":"trdark")."'>");
				foreach ($keys as $value) {
					rawoutput("<td valign='top'>{$row[$value]}</td>");
				}
				rawoutput("</tr>");
			}
			rawoutput("</table>");
		}
	}

	output("Type your query");
	$execute = translate_inline("Execute");
	$ret = modulehook("rawsql-modsql",array("sql"=>$sql));
	$sql = $ret['sql'];
	rawoutput("<form action='rawsql.php' method='post'>");
	rawoutput("<textarea onkeydown=\"return catchTab(this,event)\" name='sql' class='input' cols='60' rows='10'>".htmlentities($sql, ENT_COMPAT, getsetting("charset", "ISO-8859-1"))."</textarea><br>");
	rawoutput("<input type='submit' class='button' value='$execute'>");
	rawoutput("</form>");
	addnav("", "rawsql.php");
}else{
	$php = stripslashes(httppost("php"));
	$source = translate_inline("Source:");
	$execute = translate_inline("Execute");
	if ($php>""){
		rawoutput("<div style='background-color: #FFFFFF; color: #000000; width: 100%'><b>$source</b><br>");
		rawoutput(highlight_string("<?php\n$php\n?>",true));
		rawoutput("</div>");
		output("`bResults:`b`n");
		modulehook("rawsql-execphp",array("php"=>$php));
		ob_start();
		eval($php);
		output_notl(ob_get_contents(),true);
		ob_end_clean();
		debuglog('Ran Raw PHP: ' . $php);
		stafflog("",$session['user']['acctid'],$session['user']['acctid'],"".$session['user']['name']." Ran Raw PHP: " . $php ." ","0");

	}
	output("`n`nType your code:");
	$ret = modulehook("rawsql-modphp",array("php"=>$php));
	$php = $ret['php'];
	rawoutput("<form action='rawsql.php?op=php' method='post'>");
	rawoutput("&lt;?php<br><textarea onkeydown=\"return catchTab(this,event)\" name='php' class='input' cols='60' rows='10'>".htmlentities($php, ENT_COMPAT, getsetting("charset", "ISO-8859-1"))."</textarea><br>?&gt;<br>");
	rawoutput("<input type='submit' class='button' value='$execute'>");
	rawoutput("</form>");
	addnav("", "rawsql.php?op=php");
}
page_footer();
?>