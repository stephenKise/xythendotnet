<?php
/**
 * Library (supporting) functions for page output
 *      addnews ready
 *      translator ready
 *      mail ready
 *
 * @author core_module
 * @package defaultPackage
 *
 */

$nopopups = array();
$runheaders = array();
/**
 * Starts page output.  Inits the template and translator modules.
 *
 * @param array|string $title
 * Hooks provided:
 *      everyheader
 *      header-{scriptname}
 */
function page_header(){
    global $header,$SCRIPT_NAME,$session,$template, $runheaders, $nopopups;
    $nopopups["login.php"]=1;
    $nopopups["motd.php"]=1;
    $nopopups["index.php"]=1;
    $nopopups["create.php"]=1;
    $nopopups["about.php"]=1;
    $nopopups["mail.php"]=1;

    //in case this didn't already get called (such as on a database error)
    translator_setup();
    prepare_template();
    $script = substr($SCRIPT_NAME,0,strrpos($SCRIPT_NAME,"."));
    if ($script) {
        if (!array_key_exists($script,$runheaders))
            $runheaders[$script] = false;
        if (!$runheaders[$script]) {
            modulehook("everyheader", array('script'=>$script));
            if ($session['user']['loggedin']) {
                modulehook("everyheader-loggedin", array('script'=>$script));
            }
            $runheaders[$script] = true;
            modulehook("header-$script");
        }
    }

    $arguments = func_get_args();
    if (!$arguments || count($arguments) == 0) {
        $arguments = array("Realm of Xythen");
    }
    $title = call_user_func_array("sprintf_translate", $arguments);
    $title = holidayize($title,'title');
    $title = sanitize($title);
    calculate_buff_fields();

    $header = $template['header'];
    $header=str_replace("{title}",$title,$header);
    $header.=tlbutton_pop();
    rawoutput("<script type=\"text/javascript\" src=\"js/jquery_min.js\"></script>");
}

/**
 * Returns an output formatted popup link based on JavaScript
 *
 * @param string $page The URL to open
 * @param string $size The size of the popup window (Default: 550x300)
 * @return string
 */
function popup($page,$size="550x300"){
    global $session;
    if ($session['user']['prefs']['disable_popups'] == 1){
        return "window.open('$page','_blank').focus()";
    }else{
        $s = explode("x",$size);
        return "window.open('$page','".preg_replace("([^[:alnum:]])","",$page)."','scrollbars=yes,resizable=yes,width={$s[0]},height={$s[1]}').focus()";
    }
}

/**
 * Brings all the output elements together and terminates the rendering of the page.  Saves the current user info and updates the rendering statistics
 * Hooks provided:
 *  footer-{$script name}
 *  everyfooter
 *
 */
function page_footer($saveuser=true){
    global $output,$nestedtags,$header,$nav,$session,$REMOTE_ADDR,
        $REQUEST_URI,$pagestarttime,$quickkeys,$template,$y2,$z2,
        $logd_version,$copyright,$SCRIPT_NAME,$nopopups, $footer,$charstats,
        $dbinfo;
    $z = $y2^$z2;
    $footer = $template['footer'];
    //page footer module hooks
    $script = substr($SCRIPT_NAME,0,strpos($SCRIPT_NAME,"."));
    $replacementbits = array();
    $replacementbits = modulehook("footer-$script",$replacementbits);
    if ($script == "runmodule" && (($module = httpget('module'))) > "") {
        // This modulehook allows you to hook directly into any module without
        // the need to hook into footer-runmodule and then checking for the
        // required module.
        modulehook("footer-$module",$replacementbits);
    }
    // Pass the script file down into the footer so we can do something if
    // we need to on certain pages (much like we do on the header.
    // Problem is 'script' is a valid replacement token, so.. use an
    // invalid one which we can then blow away.
    $replacementbits['__scriptfile__'] = $script;
    $replacementbits = modulehook("everyfooter",$replacementbits);
    if ($session['user']['loggedin']) {
        $replacementbits = modulehook("everyfooter-loggedin", $replacementbits);
    }
    unset($replacementbits['__scriptfile__']);
    //output any template part replacements that above hooks need (eg,
    //advertising)
    reset($replacementbits);
    while (list($key,$val)=each($replacementbits)){
        $header = str_replace("{".$key."}","{".$key."}".join($val,""),$header);
        $footer = str_replace("{".$key."}","{".$key."}".join($val,""),$footer);
    }

    $builtnavs = buildnavs();

    restore_buff_fields();
    calculate_buff_fields();

    tlschema("common");

    $charstats = charstats();
    restore_buff_fields();

    $sql = "SELECT motddate FROM " . db_prefix("motd") . " ORDER BY motditem DESC LIMIT 1";
    $result = db_query($sql);
    $row = db_fetch_assoc($result);
    db_free_result($result);
    $headscript = "";
    if (isset($session['user']['lastmotd']) &&
            ($row['motddate']>$session['user']['lastmotd']) &&
            (!isset($nopopup[$SCRIPT_NAME]) || $nopopups[$SCRIPT_NAME]!=1) &&
            $session['user']['loggedin']){
//      $headscript.=popup("motd.php");
        $session['needtoviewmotd']=true;
    }else{
        $session['needtoviewmotd']=false;
    }
    
    $pre_headscript = "<LINK REL=\"shortcut icon\" HREF=\"favicon.ico\" TYPE=\"image/x-icon\"/>";
    $pre_headscript .= "<script type=\"text/javascript\" src=\"js/jquery_min.js\"></script>";
    if ($headscript>""){
        $header=str_replace("{headscript}",$pre_headscript."<script language='JavaScript'>".$headscript."</script>",$header);
    }else{
        $header = str_replace("{headscript}",$pre_headscript,$header);
    }

    $script = "";

    if (!isset($session['user']['name'])) $session['user']['name']="";
    if (!isset($session['user']['login'])) $session['user']['login']="";

    //clean up unclosed output tags.
    while (list($key,$val)=each($nestedtags)){
        if ($nestedtags[$key] === true) $output.="</$key>";

        unset($nestedtags[$key]);
    }
    //output keypress script
    $script.="
<script language='JavaScript'>
    <!--
    document.onkeypress=keyevent;
    function initiateState(link)
    {
        $('body').load(link);
    }
    function keyevent(e){
        var c;
        var target;
        var altKey;
        var ctrlKey;
        if (window.event != null) {
            c=String.fromCharCode(window.event.keyCode).toUpperCase();
            altKey=window.event.altKey;
            ctrlKey=window.event.ctrlKey;
        }else{
            c=String.fromCharCode(e.charCode).toUpperCase();
            altKey=e.altKey;
            ctrlKey=e.ctrlKey;
        }
        if (window.event != null)
            target=window.event.srcElement;
        else
            target=e.originalTarget;
        if (target.nodeName.toUpperCase()=='INPUT' || target.nodeName.toUpperCase()=='TEXTAREA' || altKey || ctrlKey){
        }else{";
    reset($quickkeys);
    while (list($key,$val)=each($quickkeys)){
        if ($session['user']['prefs']['disable_hotkeys'] != 1) $script.="\n         if (c == '".strtoupper($key)."') { $val; return false; }";
    }
    $script.="
        }
    }
    //-->
    </script>";

    //handle paypal
    if (strpos($footer,"{paypal}") || strpos($header,"{paypal}")){ $palreplace="{paypal}"; }else{ $palreplace="{stats}"; }

    //NOTICE |
    //NOTICE | Although under the license, you're not required to keep this
    //NOTICE | paypal link, I do request, as the author of this software
    //NOTICE | which I have made freely available to you, that you leave it in.
    //NOTICE |
    $paypalstr = "<table class='paypalButton'><tr><td>";
    $currency = getsetting("paypalcurrency", "USD");

    if (!isset($_SESSION['logdnet']) || !isset($_SESSION['logdnet']['']) || $_SESSION['logdnet']['']=="" || !isset($session['user']['laston']) || date("Y-m-d H:i:s",strtotime("-1 hour"))>$session['user']['laston']){
        $already_registered_logdnet = false;
    }else{
        $already_registered_logdnet = true;
    }

    if (getsetting("logdnet",0) && $session['user']['loggedin'] && !$already_registered_logdnet){
        //account counting, just for my own records, I don't use this in the calculation for server order.
        $sql = "SELECT count(*) AS c FROM " . db_prefix("accounts");
        $result = db_query_cached($sql,"acctcount",600);
        $row = db_fetch_assoc($result);
        $c = $row['c'];
        $a = getsetting("serverurl","http://".$_SERVER['SERVER_NAME'].($_SERVER['SERVER_PORT'] == 80?"":":".$_SERVER['SERVER_PORT']).dirname($_SERVER['REQUEST_URI']));
        if (!preg_match("/\\/$/", $a)) {
            $a = $a . "/";
            savesetting("serverurl", $a);
        }

        $l = getsetting("defaultlanguage","en");
        $d = getsetting("serverdesc","Another LoGD Server");
        $e = getsetting("gameadminemail", "postmaster@localhost.com");
        $u = getsetting("logdnetserver","http://logdnet.logd.com/");
        if (!preg_match("/\\/$/", $u)) {
            $u = $u . "/";
            savesetting("logdnetserver", $u);
        }


        global $logd_version;
        $v = $logd_version;
        $c = rawurlencode($c);
        $a = rawurlencode($a);
        $l = rawurlencode($l);
        $d = rawurlencode($d);
        $e = rawurlencode($e);
        $v = rawurlencode($v);
        $u = rawurlencode($u);
        $paypalstr .= "<script language='JavaScript' src='images/logdnet.php?op=register&c=$c&l=$l&v=$v&a=$a&d=$d&e=$e&u=$u'></script>";
    }
        
        
//      $paypalstr .= '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
// <input type="hidden" name="cmd" value="_xclick">
// <input type="hidden" name="business" value="logd@mightye.org">
// <input type="hidden" name="item_name" value="Legend of the Green Dragon Author Donation from '.full_sanitize($session['user']['name']).'">
// <input type="hidden" name="item_number" value="'.htmlentities($session['user']['login'].":".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], ENT_COMPAT, getsetting("charset", "ISO-8859-1")).'">
// <input type="hidden" name="no_shipping" value="1">
// <input type="hidden" name="notify_url" value="http://lotgd.net/payment.php">
// <input type="hidden" name="cn" value="Your Character Name">
// <input type="hidden" name="cs" value="1">
// <input type="hidden" name="currency_code" value="USD">
// <input type="hidden" name="tax" value="0">
// <input type="image" src="images/paypal1.gif" border="0" name="submit" alt="Donate!">
// </form>';

    // DP Donation button
    $paypalstr .= '</td><td>';
//  $paypalstr .= '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
// <input type="hidden" name="cmd" value="_xclick">
// <input type="hidden" name="business" value="derbugmeister@shaw.ca">
// <input type="hidden" name="item_name" value="Legend of the Green Dragon DP Donation from '.full_sanitize($session['user']['name']).'">
// <input type="hidden" name="item_number" value="'.htmlentities($session['user']['login'].":".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], ENT_COMPAT, getsetting("charset", "ISO-8859-1")).'">
// <input type="hidden" name="no_shipping" value="1">
// <input type="hidden" name="notify_url" value="http://dragonprimelogd.net/payment.php">
// <input type="hidden" name="cn" value="Your Character Name">
// <input type="hidden" name="cs" value="1">
// <input type="hidden" name="currency_code" value="USD">
// <input type="hidden" name="tax" value="0">
// <input type="image" src="images/paypal3.gif" border="0" name="submit" alt="Donate!">
// </form>';
    $paysite = getsetting("paypalemail", "");
    if ($paysite != "") {
        $paypalstr .= '</td></tr><tr><td colspan=\'2\' align=\'center\'>';
        $paypalstr .= '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
<input type="hidden" name="cmd" value="_donations">
<input type="hidden" name="business" value="'.$paysite.'">
<input type="hidden" name="item_name" value="Donation to Xythen">
<input type="hidden" name="item_number" value="'.htmlentities($session['user']['login'].":".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], ENT_COMPAT, getsetting("charset", "ISO-8859-1")).'">
<input type="hidden" name="no_shipping" value="1">';
        if (file_exists("payment.php")) {
            $paypalstr .= '<input type="hidden" name="notify_url" value="http://'.$_SERVER["HTTP_HOST"].dirname($_SERVER['REQUEST_URI']).'/payment.php">';
        }
        $paypalstr .= '<input type="hidden" name="cn" value="Add a note!">
<input type="hidden" name="cs" value="1">
<input type="hidden" name="currency_code" value="'.$currency.'">
<input type="hidden" name="lc" value="'.getsetting("paypalcountry-code","US").'">
<input type="hidden" name="bn" value="PP-DonationsBF">
<input type="hidden" name="tax" value="0">
<input type="submit" name="" value="">
</form>';
    }
    $paypalstr .= '</td></tr>';
    //if ($session['user']['loggedin'])
    //$paypalstr .= '<tr><td><iframe scrolling="no" src="http://www.top-site-list.com/vote-button/index.php?siteID=457837&amp;layout=s&amp;font=0&amp;textColor=light&amp;buttonColor=orange&amp;" style="background:none; border:0; border:0; margin:0; padding:0; max-width:100%; width:90px; height:70px; "><a href="http://www.top-site-list.com"><img src="http://www.top-site-list.com/assets/img/tsl_light80.png" alt="Top Site Lists" /></a></iframe></td></tr>';
    $paypalstr .= '</table>';

    $footer=str_replace($palreplace,(strpos($palreplace,"paypal")?"":"{stats}").$paypalstr,$footer);
    $header=str_replace($palreplace,(strpos($palreplace,"paypal")?"":"{stats}").$paypalstr,$header);
    //NOTICE |
    //NOTICE | Although I will not deny you the ability to remove the above
    //NOTICE | paypal link, I do request, as the author of this software
    //NOTICE | which I made available for free to you that you leave it in.
    //NOTICE |

    //output the nav
    $footer = str_replace("{".($z)."}",$$z,$footer);
    $header=str_replace("{nav}",$builtnavs,$header);
    $footer=str_replace("{nav}",$builtnavs,$footer);
    //output the motd

    $header = str_replace("{motd}", motdlink(), $header);
    $footer = str_replace("{motd}", motdlink(), $footer);
    //output the mail link
    if (isset($session['user']['acctid']) && $session['user']['acctid']>0 && $session['user']['loggedin']) {
        $header=str_replace("{mail}",maillink(),$header);
        $footer=str_replace("{mail}",maillink(),$footer);
    }else{
        $header=str_replace("{mail}","",$header);
        $footer=str_replace("{mail}","",$footer);
    }
    //output petition count
    require_once('lib/sanitize.php');

    $replace_string = "<table align='left' width='300px'><tr><td colspan='5' align='center' border='1px solid white'>Player's Stats</td></tr>";
    $replace_string .= "<tr><td width='50px'>Name</td><td wdith='*'>".full_sanitize($session['user']['name'])."</td><td width='150'></td><td width='50px'>TKs</td><td width='*'>{$session['user']['dragonkills']}</td></tr>";
    $replace_string .= "<tr><td width='50px'>Attack</td><td wdith='*'>{$session['user']['attack']}</td><td width='150'></td><td width='50px'>Gold</td><td width='*'>".number_format($session['user']['gold'])."</td></tr>";
    $replace_string .= "<tr><td width='50px'>Defense</td><td wdith='*'>{$session['user']['defense']}</td><td width='150'></td><td width='50px'>Gems</td><td width='*'>".number_format($session['user']['gems'])."</td></tr>";
    $replace_string .= "</table>";
    $header = str_replace("{footer_replace}", $replace_string, $header);
    $footer = str_replace("{footer_replace}", $replace_string, $footer);
    $header=str_replace("{petition}","<a href='petition.php' onClick=\"".popup("petition.php","800x300").";return false;\" target='_blank' align='right' class='motd'>".translate_inline("Contact Us")."</a>",$header);
    $footer=str_replace("{petition}","<a href='petition.php' onClick=\"".popup("petition.php","800x300").";return false;\" target='_blank' align='right' class='motd'>".translate_inline("Contact Us")."</a>",$footer);
    if ($session['user']['superuser'] & SU_EDIT_PETITIONS){
        $sql = "SELECT count(petitionid) AS c,status FROM " . db_prefix("petitions") . " GROUP BY status";
        $result = db_query_cached($sql,"petition_counts");
        $petitions=array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>0,6=>0,7=>0);
        while ($row = db_fetch_assoc($result)) {
            $petitions[(int)$row['status']] = $row['c'];
        }
        $pet = translate_inline("`0`bPetitions:`b");
        $ued = translate_inline("`0`bUser Editor`b");
        db_free_result($result);
        if ($session['user']['superuser'] & SU_EDIT_USERS){
            $p = "<a href='user.php'>$ued</a>|<a href='viewpetition.php'>$pet</a>";
            addnav("", "user.php");
            addnav("", "viewpetition.php");
        } else {
            $p = "<a href='viewpetition.php'>$pet</a>";
            addnav("", "viewpetition.php");
        }
        
    //  $p .= " `\${$petitions[5]}`0|`^{$petitions[4]}`0|`b{$petitions[0]}`b|{$petitions[1]}|`!{$petitions[3]}`0|`#{$petitions[7]}`0|`%{$petitions[6]}`0|`i{$petitions[2]}`i";
        $p .= " `b`&{$petitions[0]}`b`&|`\${$petitions[1]}`&|`Q{$petitions[2]}`&|`^{$petitions[3]}`&|`@{$petitions[4]}`&|`#{$petitions[5]}`&|`!{$petitions[6]}`&|`)`i{$petitions[7]}`i`0";
        $pcount = templatereplace("petitioncount", array("petitioncount"=>appoencode($p, true)));
        $footer = str_replace("{petitiondisplay}", $pcount, $footer);
        $header = str_replace("{petitiondisplay}", $pcount, $header);
    } else {
        $footer = str_replace("{petitiondisplay}", "", $footer);
        $header = str_replace("{petitiondisplay}", "", $header);
    }
    //output character stats
    $footer=str_replace("{stats}",$charstats,$footer);
    $header=str_replace("{stats}",$charstats,$header);
    //do something -- I don't know what
    $header=str_replace("{script}",$script,$header);
    //output view PHP source link
    $sourcelink = "source.php?url=".preg_replace("/[?].*/","",($_SERVER['REQUEST_URI']));
    $footer=str_replace("{source}","<a href='$sourcelink' onclick=\"".popup($sourcelink).";return false;\" target='_blank'>".translate_inline("View PHP Source")."</a>",$footer);
    $header=str_replace("{source}","<a href='$sourcelink' onclick=\"".popup($sourcelink).";return false;\" target='_blank'>".translate_inline("View PHP Source")."</a>",$header);
    //output version
    $footer=str_replace("{version}", "Version: $logd_version", $footer);
    //output page generation time
    $gentime = getmicrotime()-$pagestarttime;
    $session['user']['gentime']+=$gentime;
    $session['user']['gentimecount']++;
    $footer=str_replace("{pagegen}","Page gen: ".round($gentime,3)."s / ".$dbinfo['queriesthishit']." queries (".round($dbinfo['querytime'],3)."s), Ave: ".round($session['user']['gentime']/$session['user']['gentimecount'],3)."s - ".round($session['user']['gentime'],3)."/".round($session['user']['gentimecount'],3)."",$footer);

    tlschema();

    //clean up spare {fields}s from header and footer (in case they're not used)
    $footer = preg_replace("/{[^} \t\n\r]*}/i","",$footer);
    $header = preg_replace("/{[^} \t\n\r]*}/i","",$header);

    //finalize output
    $output=$header.$output.$footer;
    $session['user']['gensize']+=strlen($output);
    
    if ($session['user']['prefs']['highlight']){
        $init = array("colBlack", "colLtBlack"); // Only edit this one.
        $repl = array_fill(0, count($init), "colWhiteBlack");
        $output = str_replace($init, $repl, $output);
    }
    
    $session['output']=$output;
    if ($saveuser === true) {
        saveuser();
    }
    unset($session['output']);
    //this somehow allows some frames to load before the user's navs say it can
    //session_write_close();
    
    echo $output;
    exit();
}

/**
 * Page header for popup windows.
 *
 * @param string $title The title of the popup window
 */
function popup_header($title="Realm of Xythen"){
    global $header, $template, $session;
    rawoutput("<script type=\"text/javascript\" src=\"js/jquery_min.js\"></script>");

    translator_setup();
    prepare_template();

    modulehook("header-popup");

    $arguments = func_get_args();
    if (!$arguments || count($arguments) == 0) {
        $arguments = array("Realm of Xythen");
    }
    $title = call_user_func_array("sprintf_translate", $arguments);
    $title = holidayize($title,'title');

    $header = $template['popuphead'];
    $header = str_replace("{title}", $title, $header);
}

/**
 * Ends page generation for popup windows.  Saves the user account info - doesn't update page generation stats
 *
 */
function popup_footer(){
    global $output,$nestedtags,$header,$session,$y2,$z2,$copyright, $template;

    while (list($key,$val)=each($nestedtags)){
        if ($nestedtags[$key] === true) $output.="</$key>";
        unset($nestedtags[$key]);
    }

    $footer = $template['popupfoot'];

    // Pass the script file down into the footer so we can do something if
    // we need to on certain pages (much like we do on the header.
    // Problem is 'script' is a valid replacement token, so.. use an
    // invalid one which we can then blow away.
    $replacementbits = modulehook("footer-popup",array());
    //output any template part replacements that above hooks need
    reset($replacementbits);
    while (list($key,$val)=each($replacementbits)){
        $header = str_replace("{".$key."}","{".$key."}".join($val,""),$header);
        $footer = str_replace("{".$key."}","{".$key."}".join($val,""),$footer);
    }

    $z = $y2^$z2;
    $footer = str_replace("{".($z)."}",$$z, $footer);

    //clean up spare {fields}s from header and footer (in case they're not used)
    $footer = preg_replace("/{[^} \t\n\r]*}/i","",$footer);
    $header = preg_replace("/{[^} \t\n\r]*}/i","",$header);

    $output=$header.$output.$footer;
    saveuser();
    session_write_close();
    echo $output;
    exit();
}

$charstat_info = array();
$last_charstat_label = "";
/**
 * Resets the character stats array
 *
 */
function wipe_charstats(){
    global $charstat_info, $last_charstat_label;
    $last_charstat_label = "";
    $charstat_info = array();
}

/**
 * Add a attribute and/or value to the character stats display
 *
 * @param string $label The label to use
 * @param mixed $value (optional) value to display
 */
function addcharstat($label, $value=false) {
    global $charstat_info, $last_charstat_label;
    if ($value === false) {
        if (!isset($charstat_info[$label]))
            $charstat_info[$label] = array();
        $last_charstat_label=$label;
    } else {
        if ($last_charstat_label=="") {
            $last_charstat_label = "Other Info";
            addcharstat($last_charstat_label);
        }
        $charstat_info[$last_charstat_label][$label]=$value;
    }                                            
}

/**
 * Returns the character stat related to the category ($cat) and the label
 *
 * @param string $cat The relavent category for the stat
 * @param string $label The label of the character stat
 * @return mixed The value associated with the stat
 */
function getcharstat($cat, $label) {
    global $charstat_info;
    return $charstat_info[$cat][$label];
}

/**
 * Sets a value to the passed category & label for character stats
 *
 * @param string $cat The category for the char stat
 * @param string $label The label associated with the value
 * @param mixed $val The value of the attribute
 */
function setcharstat($cat, $label, $val) {
    global $charstat_info, $last_charstat_label;
    if (!isset($charstat_info[$cat][$label])) {
        $oldlabel = $last_charstat_label;
        addcharstat($cat);
        addcharstat($label, $val);
        $last_charstat_label = $oldlabel;
    } else {
        $charstat_info[$cat][$label] = $val;
    }
}

/**
 * Returns output formatted character stats
 *
 * @param array $buffs
 * @return string
 */
function getcharstats($buffs){
    //returns output formatted character statistics.
    global $charstat_info;
    $charstat_str = templatereplace("statstart");
    reset($charstat_info);
    foreach ($charstat_info as $label=>$section) {
        if (count($section)) {
            $arr = array("title"=>translate_inline($label));
            $sectionhead = templatereplace("stathead", $arr);
            reset($section);
            foreach ($section as $name=>$val) {
                if ($name==$label){
                    // when the section and stat name are equal, use
                    // 'statbuff' template piece.
                    $a2 = array("title"=>translate_inline("`0$name"),
                            "value"=>"`^$val`0");
                    $charstat_str .= templatereplace("statbuff", $a2);
                }else{
                    $a2 = array("title"=>translate_inline("`&$name`0"),
                            "value"=>"`^$val`0");
                    $charstat_str .= $sectionhead.templatereplace("statrow", $a2);
                    $sectionhead = "";
                }
            }
        }
    }
    $charstat_str .= templatereplace("statbuff",
            array("title"=>translate_inline("`0Buffs"),"value"=>$buffs));
    $charstat_str .= templatereplace("statend");
    return appoencode($charstat_str,true);
}


/**
 * Returns the value associated with the section & label.  Returns an empty string if the stat isn't set
 *
 * @param string $section The character stat section
 * @param string $title The stat display label
 * @return mixed The value associated with the stat
 */
function getcharstat_value($section,$title){
    global $charstat_info;
    if (isset($charstat_info[$section][$title])){
        return $charstat_info[$section][$title];
    }else{
        return "";
    }
}

/**
 * Returns the current character stats or (if the character isn't logged in) the currently online players
 * Hooks provided:
 *      charstats
 *
 * @return array The current stats for this character or the list of online players
 */
function charstats(){
    global $session, $playermount, $companions, $SCRIPT_NAME;

    wipe_charstats();

    $u =& $session['user'];

    if ($session['loggedin']){
        $u['hitpoints']=round($u['hitpoints'],0);
        $u['experience']=round($u['experience'],0);
        $u['maxhitpoints']=round($u['maxhitpoints'],0);
        $spirits=array(-6=>"Resurrected",-2=>"Very Low",-1=>"Low","0"=>"Normal",1=>"High",2=>"Very High");
        if ($u['alive']){ }else{ $spirits[(int)$u['spirits']] = "DEAD"; }
        //calculate_buff_fields();
        reset($session['bufflist']);
        $atk=$u['attack'];
        $def=$u['defense'];
        $buffcount = 0;
        $buffs = "";
        while (list($key,$val)=each($session['bufflist'])){
            if (isset($val['suspended']) && $val['suspended']) continue;
            if (isset($val['atkmod'])) {
                $atk *= $val['atkmod'];
            }
            if (isset($val['defmod'])) {
                $def *= $val['defmod'];
            }
            // Short circuit if the name is blank
            if ($val['name'] > "" || $session['user']['superuser'] & SU_DEBUG_OUTPUT){
                tlschema($val['schema']);
                if ($val['name']=="")
                    $val['name'] = "DEBUG: {$key}";
                if (is_array($val['name'])) {
                    $val['name'][0] = str_replace("`%","`%%",$val['name'][0]);
                    $val['name']=call_user_func_array("sprintf_translate", $val['name']);
                } else { //in case it's a string
                    $val['name']=translate_inline($val['name']);
                }
                if ($val['rounds']>=0){
                    // We're about to sprintf, so, let's makes sure that
                    // `% is handled.
                    //$n = translate_inline(str_replace("`%","`%%",$val['name']));
                    $b = translate_inline("`#%s `7(%s rounds left)`n","buffs");
                    $b = sprintf($b, $val['name'], $val['rounds']);
                    $buffs.=appoencode($b, true);
                }else{
                    $buffs.= appoencode("`#{$val['name']}`n",true);
                }
                tlschema();
                $buffcount++;
            }
        }
        if ($buffcount==0){
            $buffs.=appoencode(translate_inline("`^None`0"),true);
        }

        $atk = round($atk, 2);
        $def = round($def, 2);
        if ($atk < $u['attack']){
            $atk = round($u['attack'],1)."`\$".round($atk-$u['attack'],1);
        }else if($atk > $u['attack']){
            $atk = round($u['attack'],1)."`@+".round($atk-$u['attack'],1);
        } else {
            // They are equal, display in the 1 signifigant digit format.
            $atk = round($atk,1);
        }
        if ($def < $u['defense']){
            $def = round($u['defense'],1)."`\$".round($def-$u['defense'],1);
        }else if($def > $u['defense']){
            $def = round($u['defense'],1)."`@+".round($def-$u['defense'],1);
        } else {
            // They are equal, display in the 1 signifigant digit format.
            $def = round($def, 1);
        }

        //DISPLAY TK RANK
        if (get_module_pref("user_displaytk","multidkachievement")){
            $tkrank = 1;
            $tkrankstr = "";
            $sql = db_query("SELECT acctid,dragonkills FROM accounts ORDER BY dragonkills DESC");
            while($row=db_fetch_assoc($sql)){
                $row['acctid'] != $session['user']['acctid']?$tkrank++:$tkrankstr = "<sub>Rank: $tkrank</sub>";
            }
        }
        modulehook("topcharstats");
        addcharstat("Administration");
        $name = "`c<small>`2[<a href='login.php?op=switch_selector'>Switch Accounts</a>`2]</small>`c";
        addnav("","login.php?op=switch_selector");
        if ($session['user']['prefs']['switch_accounts'] == 1 && $SCRIPT_NAME == "village.php") setcharstat($name," "," ");
        addcharstat("Vital Info");
        addcharstat("Name", $u['name']);
        addcharstat("Level", $u['level']);
        if ($u['alive']) {
            addcharstat("HP", $u['hitpoints'].check_temp_stat("hitpoints",1)."`0/".$u['maxhitpoints'].check_temp_stat("maxhitpoints",1));
            //addcharstat("Turns", $u['turns'].check_temp_stat("turns",1));
            //addcharstat("Experience", $u['experience'].check_temp_stat("experience",1));
            addcharstat("ATK", $atk.check_temp_stat("attack",1));
            addcharstat("DEF", $def.check_temp_stat("defense",1));
        } else {
            $maxsoul = $u['level'] * 5 + 50;
            addcharstat("Torments", $u['gravefights'].check_temp_stat("gravefights",1));
            addcharstat("Soulpoints", $u['soulpoints'].check_temp_stat("soulpoints",1)."`0/".$maxsoul);
            addcharstat("Psyche", 10+round(($u['level']-1)*1.5));
            addcharstat("Spirit", 10+round(($u['level']-1)*1.5));
        }
        if (get_module_pref("user_displaytk","multidkachievement")) addcharstat("TKs", $session['user']['dragonkills']." ".$tkrankstr);
        else addcharstat("TKs", $session['user']['dragonkills']);
        addcharstat("TKs Today", get_module_pref("dkstoday","multidkachievement",$u['acctid']));
        if (is_module_active("pktrack") && get_module_pref("user_showpk")) addcharstat("Player Kills","");
//      if (is_module_active("friendlist")) addcharstat("Friends","");
        if (is_module_active("logoutcharstat") && $SCRIPT_NAME == "village.php") addcharstat("Leaving?","");
        addcharstat("Item Info");
        addcharstat("Gold",number_format($u['gold']));
        addcharstat("Gems",number_format($u['gems']));
        if (!$u['alive']) addcharstat("Favor", $u['deathpower'].check_temp_stat("deathpower",1));
//      addcharstat("Donator Points",$u['donation']-$u['donationspent']);
//      if (is_module_active("buildsnowman")) addcharstat("Snowman Material","");
//      if (is_module_active("medcontest")){
//          if ($session['user']['alive'] && get_module_pref('medhunt','medcontest')) addcharstat("Medallions","");
//      }
//      addcharstat("Personal Info");
//      if ($u['prefs']['showcharm']==1 || !is_numeric($u['prefs']['showcharm'])) addcharstat("Charm",$u['charm']);
//      addcharstat("Race", "");
//      if (is_module_active("marriage") && $u['marriedto']!=0 && $u['marriedto']!=4294967295 && get_module_pref('user_stats','marriage'))
//          addcharstat("Marriage","");
        if ($u['hashorse']) addcharstat("Mount", $playermount['mountname'] . "`0");
        addcharstat("Other");
        require_once("lib/datetime.php");
        $details = gametimedetails();
        $off = ($details['realsecstotomorrow'] - ($offset));
        if ($off < 0) $off += 86400;
        $newday = strtotime("+".$off." secs");
        addcharstat("Next New Day",reltime($newday)."`0");
//      if (is_module_active("academy")){
//          if (get_module_pref("user_showstats","academy") && get_module_pref("active","academy")) addcharstat("Squire Info","");
//      }
        if (is_module_active("additionalbioinfos")) addcharstat("Your Bio","");
        if (is_module_active("siterules")) addcharstat("Site Rules", "<a href='runmodule.php?module=siterules&op=popup' onClick=\"".popup("runmodule.php?module=siterules&op=popup").";return false;\" target='_blank'>View</a>");
        modulehook("charstats");

        $charstat = getcharstats($buffs);

        if (!is_array($session['bufflist'])) $session['bufflist']=array();
        return $charstat;
    }else{
        $ret = "";
        if (!$ret = datacache("charlisthomepage")){

            $onlinecount=0;
            // If a module wants to do it's own display of the online chars,
            // let it.
            $list = modulehook("onlinecharlist", array());
            if (isset($list['handled']) && $list['handled']) {
                $onlinecount = $list['count'];
                $ret = $list['list'];
            } else {
                $sql="SELECT acctid,name,alive,location,sex,level,laston,loggedin,lastip,uniqueid FROM " . db_prefix("accounts") . " WHERE locked=0 AND loggedin=1 AND laston>'".date("Y-m-d H:i:s",strtotime("-".getsetting("LOGINTIMEOUT",900)." seconds"))."' ORDER BY level DESC";
                $result = db_query($sql);
                $ret.=appoencode(sprintf(translate_inline("`b%s Online:`b`n"),db_num_rows($result)));
                while ($row = db_fetch_assoc($result))
                {
                        $ret.=appoencode("`^{$row['name']}`n");
                        $onlinecount++;
                }
                //if ($SCRIPT_NAME != "list.php")                                             
                db_free_result($result);
                if ($onlinecount==0)
                    $ret.=appoencode(translate_inline("`iNone`i"));
                $ret .= "<br><center><a href='list.php'>List all characters</a></center>";
            }
            savesetting("OnlineCount",$onlinecount);
            savesetting("OnlineCountLast",strtotime("now"));
            updatedatacache("charlisthomepage",$ret);
        }
        return $ret;
    }
}
/**
 * Loads the template into the current session.  If the template doesn't
 * exist - uses the default (admin-defined) template, and then falls back
 * to jade.htm
 *
 * @param string $templatename The template name (minus the path)
 * @return array The template split into the sections defined by <!--!
 * @see Templates
 * @todo Template Help
 */
function loadtemplate($templatename){
    if ($templatename=="" || !file_exists("templates/$templatename") || substr($templatename, -4) != '.htm')
        $templatename=getsetting("defaultskin", "Xythen.htm");
    if ($templatename=="" || !file_exists("templates/$templatename"))
        $templatename="jade.htm";
    $fulltemplate = file_get_contents("templates/$templatename");
    $fulltemplate = explode("<!--!",$fulltemplate);
    while (list($key,$val)=each($fulltemplate)){
        $fieldname=substr($val,0,strpos($val,"-->"));
        if ($fieldname!=""){
            $template[$fieldname]=substr($val,strpos($val,"-->")+3);
            modulehook("template-{$fieldname}",
                    array("content"=>$template[$fieldname]));
        }
    }
    return $template;
}

/**
 * Returns a display formatted (and popup enabled) mail link - determines if unread mail exists and highlights the link if needed
 *
 * @return string The formatted mail link
 */
function maillink(){
    global $session;
    $sql = "SELECT sum(if(seen=0,1,0)) AS seencount, sum(if(seen=1,1,0)) AS notseen FROM " . db_prefix("mail") . " WHERE msgto=\"".$session['user']['acctid']."\"";
    $result = db_query($sql);
    $row = db_fetch_assoc($result);
// db_free_result($result);
    $sql = db_query("SELECT count(id) AS total FROM alerts WHERE acctid = {$session['user']['acctid']}");
//  $result = db_query_cached("alerts-{$session['user']['acctid']}",86400);
// For some reason the mail loads slower when we do dis.
    $alerts = db_fetch_assoc($sql);
    db_free_result($result);
    if ($alerts['total'] != 0 || $row['notseen'] != 0)
        return sprintf("<a name='maillink' href='mail.php' target='_blank' onClick=\"".popup("mail.php","800x300").";return false;\" class='unreadmotd'>Mailbox</a>");
    else
        return sprintf("<a name='maillink' href='mail.php' target='_blank' onClick=\"".popup("mail.php","800x300").";return false;\" class='motd'>Mailbox</a>");
    // if ($alerts['total'] != 0) $new_alerts = " - <b><i><span class='colLtYellow'>New Alert!</span></i></b>";
    //  else $new_alerts = "";
    // $row['seencount']=(int)$row['seencount'];
    // $row['notseen']=(int)$row['notseen'];
    // if ($row['notseen']>0 ){
    //  return sprintf("<a href='mail.php' target='_blank' onClick=\"".popup("mail.php","800x300").";return false;\" class='colLtRed'>".translate_inline("Mailbox: %s new, %s old%s", 'common')."</a>",$row['notseen'],$row['seencount'],$new_alerts);
    // }else{
    //  return sprintf("<a href='mail.php' target='_blank' onClick=\"".popup("mail.php","800x300").";return false;\" class='motd'>".translate_inline("Mailbox: %s new, %s old%s", 'common')."</a>",$row['notseen'],$row['seencount'],$new_alerts);
    // }
}

/**
 * Returns a display formatted (and popup enabled) MOTD link - determines if unread MOTD items exist and highlights the link if needed
 *
 * @return string The formatted MOTD link
 */
function motdlink(){
    global $session;
    $row = db_fetch_assoc(db_query("SELECT motddate FROM " . db_prefix("motd") . " WHERE motdtype = 1 ORDER BY motditem DESC LIMIT 1"));
    if (($session['needtoviewmotd'] || $session['user']['seenupdates'] != 1) && $session['user']['loggedin'] == 1){
        return "<a href='motd.php' target='_blank' onClick=\"".popup("motd.php").";return false;\" class='unreadmotd'><b>".translate_inline("MoTD")."</b></a>";
    }else if ($session['user']['loggedin']){
        return "<a href='motd.php' target='_blank' onClick=\"".popup("motd.php").";return false;\" class='motd'><b>".translate_inline("MoTD")."</b></a>";
    }
}
?>