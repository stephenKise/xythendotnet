<?php
// translator ready
// addnews ready
// mail ready

function sanitize($in){
	$out = preg_replace("/[`][!-_a-~]/", "", $in);
	return $out;
}

function newline_sanitize($in){
	$out = preg_replace("/`n/", "", $in);
	return $out;
}

function color_sanitize($in){
	$out = preg_replace("/[`][!-_a-~]/", "", $in);
	return $out;
}

function comment_sanitize($in) {
	// to keep the regexp from boinging this, we need to make sure
	// that we're not replacing in with the ` mark.
	$out=preg_replace("/[`](?=[!-;\=?-GI-_abd-mo-rt-~])/", chr(1).chr(1), $in);
	$out = str_replace(chr(1),"`",$out);
	return $out;
}

function logdnet_sanitize($in)
{
	// to keep the regexp from boinging this, we need to make sure
	// that we're not replacing in with the ` mark.
	$out=preg_replace("/[`](?=[^1234567890!@#\$%^&)Qqbi])/", chr(1).chr(1), $in);
	$out = str_replace(chr(1),"`",$out);
	return $out;
}

function full_sanitize($in) {
	$out = preg_replace("/[`]./", "", $in);
	return $out;
}

function cmd_sanitize($in) {
	$out = preg_replace("'[&?]c=[[:digit:]-]+'", "", $in);
	return $out;
}

function comscroll_sanitize($in) {
	$out = preg_replace("'&c(omscroll)?=([[:digit:]]|-)*'", "", $in);
	$out = preg_replace("'\\?c(omscroll)?=([[:digit:]]|-)*'", "?", $out);
	$out = preg_replace("'&(refresh|comment)=1'", "", $out);
	$out = preg_replace("'\\?(refresh|comment)=1'", "?", $out);
	return $out;
}

function prevent_colors($in) {
	$out = str_replace("`", "&#0096;", $in);
	return $out;
}

function translator_uri($in){
	$uri = comscroll_sanitize($in);
	$uri = cmd_sanitize($uri);
	if (substr($uri,-1)=="?") $uri = substr($uri,0,-1);
	return $uri;
}

function translator_page($in){
	$page = $in;
	if (strpos($page,"?")!==false) $page=substr($page,0,strpos($page,"?"));
	//if ($page=="runmodule.php" && 0){
	//	//we should handle this in runmodule.php now that we have tlschema.
	//	$matches = array();
	//	preg_match("/[&?](module=[^&]*)/i",$in,$matches);
	//	if (isset($matches[1])) $page.="?".$matches[1];
	//}
	return $page;
}

function modulename_sanitize($in){
	return preg_replace("'[^0-9A-Za-z_]'","",$in);
}

// the following function borrowed from mike-php at emerge2 dot com's post
// to php.net documentation.
//Original post is available here: http://us3.php.net/stripslashes
function stripslashes_array( $given ) {
   return is_array( $given ) ?
	   array_map( 'stripslashes_array', $given ) : stripslashes( $given );
}

// Handle spaces in character names
function sanitize_name($spaceallowed, $inname)
{
	if ($spaceallowed)
		$expr = "([^[:alpha:] _-])";
	else
		$expr = "([^[:alpha:]])";
	return preg_replace($expr, "", $inname);
}

// Handle spaces and color in character names
function sanitize_colorname($spaceallowed, $inname, $admin = false)
{
	if ($admin && getsetting("allowoddadminrenames", 0)) return $inname;
	if ($spaceallowed)
		$expr = "([^[:alpha:]`!@#$%^&)12345670 _-])";
	else
		$expr = "([^[:alpha:]`!@#$%^&)12345670])";
	return preg_replace($expr, "", $inname);
}


/**
 * Alias of sanitizeHTMl().
 * 
 * @param string $input
 * @return string
 */
function sanitize_html(string $input): string
{
    return sanitizeHTML($input);
}

/**
 * Strips HTML codes and the contents within script, style, and comment tags.
 * 
 * @param string $input
 * @return string
 */
function sanitizeHTML(string $input): string
{
    $input = preg_replace("/<script[^>]*>.+<\\/script[^>]*>/", "", $input);
    $input = preg_replace("/<style[^>]*>.+<\\/style[^>]*>/", "", $input);
    $input = preg_replace("/<!--.*-->/", "", $input);
    return $input;
}

?>
