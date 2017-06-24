<?php
output_notl("<form action='mail.php?op=write' method='post'>",true);
output("`b`2Address:`b`n");
$to = translate_inline("To: ");
$search = htmlentities(translate_inline("Search"), ENT_COMPAT, getsetting("charset", "ISO-8859-1"));
output_notl("<input name='to' id='to' placeholder='To:' value=\"".htmlentities(stripslashes(httpget('prepop')), ENT_COMPAT, getsetting("charset", "ISO-8859-1"))."\">",true);
output_notl("<input type='submit' class='button' value=\"$search\"><br>", true);
modulehook("mailaddressoptions");
/*
if ($session['user']['superuser'] & SU_IS_GAMEMASTER) {
	$from = translate_inline("From: ");
	output_notl("`n<input name='from' id='from' placeholder='From:'>`n", true);
	output("`7`iLeave empty to send from your account!`i");
}
*/
rawoutput("</form>");
rawoutput("<script type='text/javascript'>document.getElementById(\"to\").focus();</script>");
?>