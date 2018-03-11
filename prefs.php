<?php

// addnews ready
// mail ready
// translator ready
require_once ("lib/http.php");
$skin=httppost('template');
if ($skin>"")
{
    setcookie("template", $skin, strtotime("+45 days"));
    $_COOKIE['template']=$skin;
}
require_once ("lib/villagenav.php");
require_once ("common.php");
addnav("Refresh", "prefs.php");
tlschema("prefs");

function new_prefs_selectors($input_type, $name, $saved_value, $pref_description)
{
    $input_selector=explode(',', $input_type);
    switch ($input_selector[0])
    {

        case "int":             //complete
            return "<tr><td valign='center'>$pref_description</td><td valign='center' align='right' width='325px'><input name='$name' value=\"".HTMLEntities("$saved_value", ENT_COMPAT, getsetting("charset", "ISO-8859-1"))."\" size='51'></td></tr>";
            break;

        case "enum":            //complete
            $select=("<select name='$name' style='width: 100%;'>");
            for ($i=0; $i<count($input_selector); $i++)
            {
                if ($i&1!=0)
                {
                    $select.="<option value='{$input_selector[$i]}'".($saved_value==$input_selector[$i+1] ? " selected":"").">".HTMLEntities($input_selector[$i+1], ENT_COMPAT, getsetting("charset", "ISO-8859-1"))."</option>";
                }
            }
            $select.="</select>";
            return "<tr><td valign='center'>$pref_description</td><td valign='center' align='right' width='325px'>$select</td></tr>";
            break;

        case "bool":
            return "<tr><td valign='center'>$pref_description</td><td wdith='325px' align='center'><input type='radio' name='$name' value='1' ".($saved_value ? "":"checked").">Yes</input><input type='radio' name='$name' value='2' ".($saved_value ? "checked":"").">No</input></td></tr>";
            break;

        case "textarea":            //complete?
            return "<tr><td valign='center'>$pref_description</td><td valign='center' align='right' width='325px'><textarea name='$name' class='input' cols='40' rows='5'>".HTMLEntities("$saved_value", ENT_COMPAT, getsetting("charset", "ISO-8859-1"))."</textarea></td></tr>";
            break;

        case "note":
            return "<tr><td colspan='2'>$pref_description</td></tr>";
            break;
    }
}

/*



if ($session['user']['superuser'] & SU_EDIT_USERS)

{

global $session;

// debug($session['user']['prefs']);

output("<table width='750px'>",true);

$prefs_info = file_get_contents('game_cache/userprefs.csv');

$prefs_sections = explode('<NEW_PREFERENCE_SECTION>',$prefs_info);

foreach($prefs_sections as $key)

{

$prefs_for_section = explode(':!:',$key);

output("<tr style='font-variant: small-caps;'><td colspan='2'>`b{$prefs_for_section[0]}`b</big></td></tr>",true);

// debug($prefs_for_section);

if ($prefs_for_section[1])

{

foreach($prefs_for_section as $items)

{

$individual_prefs = explode('<|>',$items);

if ($individual_prefs[1])

{

foreach($individual_prefs as $deep_key)

{

$pref_selector = explode('>>',$deep_key);

$pref_name = explode('[|]',$pref_selector[0]);

if ($pref_name[1] == "")

{

$pref_name = $pref_name[0];

$selector_name = $pref_name;

$grab_session_pref = $session['user']['prefs'][$pref_name];

}

else

{

$module_name = trim($pref_name[0]);

$pref_name = trim($pref_name[1]);

$selector_name = $module_name."|".$pref_name;

$grab_session_pref = $session['user']['prefs'][$module_name][$pref_name];

}

if (strpos($pref_selector[1],','))

$pref_input = trim(substr($pref_selector[1],strpos($pref_selector[1],',')+1));

$pref_description = trim(substr($pref_selector[1], 0, strpos($pref_selector[1], ",")));

debug($pref_input);

//if ($pref_input || ($pref_input && isset($module_name) && substr($pref_name,0,5) == "user_")) output(new_prefs_selectors(trim($pref_input),$selector_name,$grab_session_pref,$pref_description),true);

unset($module_name);

unset($pref_name);

unset($pref_description);

unset($pref_input);

}

}

}

}

output("<tr><td> </td></tr>",true);

}

output("</table>",true);

}



*/

require_once ("lib/is_email.php");
require_once ("lib/showform.php");
require_once ("lib/sanitize.php");
global $session;
page_header("Preferences");
$op=httpget('op');
if ($op=="suicide" && getsetting("selfdelete", 0)!=0)
{
    $userid=httpget('userid');
    require_once ("lib/charcleanup.php");
    char_cleanup($userid, CHAR_DELETE_SUICIDE);
    $sql="DELETE FROM ".db_prefix("accounts")." WHERE acctid='$userid'";
    db_query($sql);
    output("Your character has been deleted!");
    addnews("`#%s quietly passed from this world.", $session['user']['name']);
    addnav("Login Page", "index.php");
    $session=array();
    $session['user']=array();
    $session['loggedin']=false;
    $session['user']['loggedin']=false;
    invalidatedatacache("charlisthomepage");
    invalidatedatacache("list.php-warsonline");
}
else
{
    checkday();
    if ($session['user']['alive'])
    {
        villagenav();
    }
    else
    {
        addnav("Return to the news", "news.php");
    }
    $oldvalues=stripslashes(httppost('oldvalues'));
    $oldvalues=unserialize($oldvalues);
    $post=httpallpost();
    unset ($post['oldvalues']);
    if (count($post)==0)
    {
    }
    else
    {
        $pass1=httppost('pass1');
        $pass2=httppost('pass2');
        if ($pass1!=$pass2)
        {
            output("`#Your passwords do not match.`n");
        }
        else
        {
            if ($pass1!="")
            {
                if (strlen($pass1)>4 && !preg_match('/[A-Z]+[a-z]+[0-9]+/', $password))
                {
                    $pass1=md5(md5($pass1));
                    $session['user']['password']=$pass1;
                    output("`#Your password has been changed.`n");
                    $session['user']['loggedin']=0;
                }
                else
                {
                    output("`#Your password does not meet proper credentials! ");
                    output("Your password must have: `i`^5 or more characters, one upper case letter, one lower case letter, and one symbol.`n");
                }
            }
        }
        reset($post);
        $nonsettings=array("pass1" => 1, "pass2" => 1, "email" => 1, "template" => 1,
        //"bio"=>1
        );
        foreach ($post as $key => $val)
        {
        // If this is one we don't save, skip
            if (isset ($nonsettings[$key]) && $nonsettings[$key])
                continue;
            if (isset ($oldvalues[$key]) && stripslashes($val)==$oldvalues[$key])
                continue;
            // If this is a module userpref handle and skip
            debug("Setting $key to $val");
            if (strstr($key, "___"))
            {
                $val=httppost($key);
                $x=explode("___", $key);
                $module=$x[0];
                $key=$x[1];
                modulehook("notifyuserprefchange", array("name" => $key, "old" => $oldvalues[$module."___".$key], "new" => $val));
                set_module_pref($key, $val, $module);
                continue;
            }
            $session['user']['prefs'][$key]=httppost($key);
        }

        /*

        $bio = stripslashes(httppost('bio'));

        $bio = comment_sanitize($bio);

        if ($bio!=comment_sanitize($session['user']['bio'])){

        if ($session['user']['biotime']>"9000-01-01") {

        output("`\$You cannot modify your bio.");

        output("It has been blocked by the administrators!`0`n");

        }else{

        $session['user']['bio']=$bio;

        $session['user']['biotime']=date("Y-m-d H:i:s");

        }

        }

        */
        $email=httppost('email');
        if ($email!=$session['user']['emailaddress'])
        {
            if (is_email($email))
            {
                if (getsetting("requirevalidemail", 0)==1)
                {
                    output("`#Your email cannot be changed, system settings prohibit it.");
                }
                else
                {
                    output("`#Your email address has been changed.`n");
                    $session['user']['emailaddress']=$email;
                }
            }
            else
            {
                if (getsetting("requireemail", 0)==1)
                {
                    output("`#That is not a valid email address.`n");
                }
                else
                {
                    output("`#Your email address has been changed.`n");
                    $session['user']['emailaddress']=$email;
                }
            }
        }
        if ($oldvalues['text_size']!=$session['user']['prefs']['text_size'])
        {
            $session['say_settings_saved']=true;
            require_once ('lib/redirect.php');
            redirect('prefs.php');
        }
        output("Settings Saved");
    }
    if ($session['say_settings_saved']==true)
    {
        output("Settings Saved");
        $session['say_settings_saved']=false;
    }
    if (!isset ($session['user']['prefs']['timeformat']))
        $session['user']['prefs']['timeformat']="[m/d h:ia]";
    $form = [
        "Account Preferences, title",
        "pass1" => "Password,password",
        "pass2" => "Retype,password",
        "You SHOULD use one capital letter&#44; one lower case letter&#44; and one number in your password!,note",
        "You will be logged out when you change your password!,note",
        "email" => "Email Address",
        "switch_accounts" => "Should we add the option for quick switching accounts above your character's stats?,bool",
        "allowed_names" => "What other logins should we allow you to switch to?,text",
        "Please be aware that you need to have the same password set for this account and the account that you want to switch to! If there is more than one account you want to switch to - separate the names with a comma.,note",
        "Display Preferences,title",
        "template" => "Skin,theme",
        "tabconfig" => "Show config sections in tabs,bool",
        "Game Behavior Preferences,title",
        "disable_hotkeys" => "Should we have the hotkeys disabled?,bool",
        "PvP Notification,title",
        "pvpnotif" => "Would you like to be notified when someone slays you?,bool",
        "Charm,title",
        "showcharm" => "Would you like to see your charm under your Personal Info?,bool",
    ];
    $staffprefs = [
        "Staff Preferences, title",
        "debug" => "Should we enable the debug output for you?,bool",
        "hide_deleted" => "Do you want deleted comments to show?,bool",
    ];
    if ($session['user']['superuser']&SU_EDIT_COMMENTS)
        $form=array_merge($form,$staffprefs);
    rawoutput("<script language='JavaScript' src='lib/md5.js'></script>");
    $warn=translate_inline("Your password is too short.  It must be at least 4 characters long.");
    rawoutput("<script language='JavaScript'>

    <!--

    function md5pass(){

        //encode passwords before submission to protect them even from network sniffing attacks.

        var passbox = document.getElementById('pass1');

        if (passbox.value.len < 4 && passbox.value.len > 0){

            alert('$warn');

            return false;

        }else{

            var passbox2 = document.getElementById('pass2');

            if (passbox2.value.substring(0, 5) != '!md5!') {

                passbox2.value = '!md5!' + hex_md5(passbox2.value);

            }

            if (passbox.value.substring(0, 5) != '!md5!') {

                passbox.value = '!md5!' + hex_md5(passbox.value);

            }

            return true;

        }

    }

    //-->

    </script>");
    //
    $prefs=$session['user']['prefs'];
    //  $prefs['bio'] = $session['user']['bio'];
    $prefs['template']=$_COOKIE['template'];
    if ($prefs['template']=="")
        $prefs['template']=getsetting("defaultskin", "jade.htm");
    $prefs['email']=$session['user']['emailaddress'];
    // Default tabbed config to true
    if (!isset ($prefs['tabconfig']))
        $prefs['tabconfig']=1;
    // Okay, allow modules to add prefs one at a time.
    // We are going to do it this way to *ensure* that modules don't conflict
    // in namespace.
    $sql="SELECT modulename FROM ".db_prefix("modules")." WHERE infokeys LIKE '%|prefs|%' AND active=1 ORDER BY modulename";
    $result=db_query($sql);
    $everfound=0;
    $foundmodules=array();
    $msettings=array();
    $mdata=array();
    while ($row=db_fetch_assoc($result))
    {
        $module=$row['modulename'];
        $info=get_module_info($module);
        if (count($info['prefs'])<=0)
            continue;
        $tempsettings=array();
        $tempdata=array();
        $found=0;
        while (list($key, $val)=each($info['prefs']))
        {
            $isuser=preg_match("/^user_/", $key);
            $ischeck=preg_match("/^check_/", $key);
            if (is_array($val))
            {
                $v=$val[0];
                $x=explode("|", $v);
                $val[0]=$x[0];
                $x[0]=$val;
            }
            else
            {
                $x=explode("|", $val);
            }
            if (is_array($x[0]))
                $x[0]=call_user_func_array('sprintf', $x[0]);
            $type=explode(",", $x[0]);
            if (isset ($type[1]))
                $type=trim($type[1]);
            else
                $type="string";
            // Okay, if we have a title section, let's copy over the last
            // title section
            if (strstr($type, "title"))
            {
                if ($found)
                {
                    $everfound=1;
                    $found=0;
                    $msettings=array_merge($msettings, $tempsettings);
                    $mdata=array_merge($mdata, $tempdata);
                }
                $tempsettings=array();
                $tempdata=array();
            }
            if (!$isuser && !$ischeck && !strstr($type, "title") && !strstr($type, "note"))
                continue;
            if ($isuser)
            {
                $found=1;
            }
            // If this is a check preference, we need to call the modulehook
            // checkuserpref  (requested by cortalUX)
            if ($ischeck)
            {
                $args=modulehook("checkuserpref", array("name" => $key, "pref" => $x[0], "default" => $x[1]), false, $module);
                if (isset ($args['allow']) && !$args['allow'])
                    continue;
                $x[0]=$args['pref'];
                $x[1]=$args['default'];
                $found=1;
            }
            $tempsettings[$module."___".$key]=$x[0];
            if (array_key_exists(1, $x))
            {
                $tempdata[$module."___".$key]=$x[1];
            }
        }
        if ($found)
        {
            $msettings=array_merge($msettings, $tempsettings);
            $mdata=array_merge($mdata, $tempdata);
            $everfound=1;
        }
        // If we found a user editable one
        if ($everfound)
        {
        // Collect the values
            $foundmodules[]=$module;
        }
    }
    if ($foundmodules!=array())
    {
        $sql="SELECT * FROM ".db_prefix("module_userprefs")." WHERE modulename IN ('".implode("','", $foundmodules)."') AND (setting LIKE 'user_%' OR setting LIKE 'check_%') AND userid='".$session['user']['acctid']."'";
        $result1=db_query($sql);
        while ($row1=db_fetch_assoc($result1))
        {
            $mdata[$row1['modulename']."___".$row1['setting']]=$row1['value'];
        }
    }
    addnav('B?View Bio', 'bio.php?char='.$session['user']['acctid'].'&ret='.urlencode($_SERVER['REQUEST_URI']));
    $form=array_merge($form, $msettings);
    $prefs=array_merge($prefs, $mdata);
    rawoutput("<form action='prefs.php?op=save' method='POST' onSubmit='return(md5pass)'>");
    $info=showform($form, $prefs);
    rawoutput("<input type='hidden' value=\"".htmlentities(serialize($info), ENT_COMPAT, getsetting("charset", "ISO-8859-1"))."\" name='oldvalues'>");
    rawoutput("</form><br />");
    addnav("", "prefs.php?op=save");
    // Stop clueless users from deleting their character just because a monster killed them.
    if ($session['user']['alive'] && getsetting("selfdelete", 0)!=0)
    {
        rawoutput("<form action='prefs.php?op=suicide&userid={$session['user']['acctid']}' method='POST'>");
        $deltext=translate_inline("Delete Character");
        $conf=translate_inline("Are you sure you wish to delete your character?");
        rawoutput("<table class='noborder' width='100%'><tr><td width='100%'></td><td style='background-color:#FF00FF' align='right'>");
        rawoutput("<input type='submit' class='button' value='$deltext' onClick='return confirm(\"$conf\");'>");
        rawoutput("</td></tr></table>");
        rawoutput("</form>");
        addnav("", "prefs.php?op=suicide&userid={$session['user']['acctid']}");
    }
}
page_footer();

?>