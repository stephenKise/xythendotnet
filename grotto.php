<?php
// translator ready
// addnews ready
// mail ready
require_once("common.php");
require_once("lib/commentary.php");
require_once("lib/sanitize.php");
require_once("lib/http.php");

addcommentary();

require_once('lib/villagenav.php');
villagenav();

page_header("Superuser Grotto");






switch(httpget('op'))
{
	// case 'create_event':
	// 	// <input type="date" name="favcolor">
	// 	output("<form action='grotto.php?op=send_event' method='POST'>",true);
	// 		output("<input type='text' name='event_title' class='input'><br/>",true);
	// 		output("<textarea name='event_description' class='input' cols='43'></textarea><br/>",true);
	// 		output("<input type='date' name='event_day' class='input'>",true);
	// 		output("<input type='submit' value='Send'><br/>",true);
	// 	output("</form>",true);
	// 	addnav('','grotto.php?op=send_event');
	// 	addnav('DEES NIGGAS CANNOT HOLD ME BACK!','grotto.php');
	// break;
	// case 'send_event':
	// 	$httppost = httpallpost();
	// 	addnav('bounce, m\'dude..','grotto.php');
	// 	$date = explode('-',$httppost['event_day']);
	// 	$date = array('y'=>$date[0],'m'=>$date[1],'d'=>$date[2]);
	// 	output($date['y']);
	// break;
	default:

		$args = modulehook("superusertop", array("section"=>"superuser"));
		commentdisplay("", $args['section'],"Engage in conversation,",25);

		// modulehook("superuser", array(), true);
		// addnav('Other');
		// addnav('Create an Event','grotto.php?op=create_event');
	break;
}













modulehook("superuser", array());
page_footer();
?>