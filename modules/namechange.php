<?php

function namechange_getmoduleinfo(){
    $info = array(
        "name"=>"Names Department",
        "author"=>"Derek0, Chris Vorndran",
        "version"=>"1.0",
        "category"=>"Administrative",
        "download"=>"",
        "settings"=>array(
            "Names Department Settings,title",
            "cost"=>"Cost `iin gems`i for a Name,int|5"
        ),
        "prefs"=>array(
            "Name Changes,title",
            "changednames"=>"Name Changes,viewonly|",
            "lastname"=>"What was the player's last name,viewonly|"
        ),
    );
    return $info;
}
function namechange_install(){
    module_addhook("village");
    module_addhook("changesetting");
    module_addhook("lastnames");
    return true;
}
function namechange_uninstall(){
    return true;
}
function namechange_dohook($hookname,$args){
    global $session;
    switch ($hookname){
        case "village":
                tlschema($args['schemas']['marketnav']);
                addnav($args['marketnav']);
                tlschema();
                addnav("`b`~I`b`)d`i`7e`kn`i`3t`b`#i`b`Lt`i`7y`i `b`)C`b`~r`b`)i`b`i`7s`b`ki`b`3s`i `#I`Ln`i`7c`i`). ","runmodule.php?module=namechange&op=enter");
            break;
        case "lastnames":
            $name = $args['acctid'];
            $names = unserialize(get_module_pref("lastname","namechange",$args['acctid']));
            $num_names = count($names);
            if ($num_names > 0) output("`c`i`^Last known as ".($names[$num_names-1])."`i`c`n");
            break;
        }
    return $args;
}
function namechange_run(){
    global $session;
    require_once('lib/sanitize.php');
    require_once('lib/names.php');
    page_header("Identity Crisis Inc.");
    
    $op = httpget('op');
    $namec = httppost('name');
    $name = $session['user']['name'];
    $cost = get_module_setting("cost");
    $rename = translate_inline("Rename");

    switch ($op){
        case "enter":
            if ($session['user']['gems'] >= $cost){
                    output("`c`7You know, sometimes people don't feel like their" .
                    " name suits them anymore. Sometimes, they need something" .
                    " new, something different. Something unique. Luckily for" .
                    " you, the `b`~I`b`)d`i`7e`kn`i`3t`b`#i`b`Lt`i`7y`i" .
                    " `b`)C`b`~r`b`)i`b`i`7s`b`ki`b`3s`i `#I`Ln`i`)c`i`7 is" .
                    " here for you! All is costs it but a small fee and you" . 
                    " can remake your entire being right here! A new name, a" .
                    " new face, a new being, a new you! The voice reverberates" .
                    " throughout the unsettling clean facility, white covering" .
                    " the walls and marble floor to give off a properly modern" .
                    " sense of the technology present within the establishment." .
                    " Some people came here to remake themselves, while others" .
                    " came here to escape their past. Whatever their, or even" .
                    " your, purpose for entering those two sliding doors," .
                    " it was clear that everyone here was all to eager to take" .
                    " the fee and fulfill your dream. You approach the" .
                    " reception desk and the man behind it looks up to you as" .
                    " you begin to fill out forms about changing your" .
                    " name. Once you determine that you are eligible, you" .
                    " are directed by the receptionist towards a hallway to the left" .
                    " You make your way down the hallway, when suddenly you" .
                    " pass an outbound citizen who you cannot seem to" .
                    " recognize. Your eyes follow them for a couple of" .
                    " moments before directing forward once more and settling on" .
                    " a shape shifting man in a lab coat, \"`LI see that you're interested" .
                    " in our identity changing services..." .
                    " They cost `^%s Gems`L and afterwards, no one will" .
                    " remember you unless you let them.`7\"`c",$cost);

                addnav("Legal Name Options");
                addnav("Change your name","runmodule.php?module=namechange&op=name");
            } else {
                output("`c`7You know, sometimes people don't feel like their" .
                    " name suits them anymore. Sometimes, they need something" .
                    " new, something different. Something unique. Luckily for" .
                    " you, the `b`~I`b`)d`i`7e`kn`i`3t`b`#i`b`Lt`i`7y`i" .
                    " `b`)C`b`~r`b`)i`b`i`7s`b`ki`b`3s`i `#I`Ln`i`)c`i`7 is" .
                    " here for you! All is costs it but a small fee and you" . 
                    " can remake your entire being right here! A new name, a" .
                    " new face, a new being, a new you! The voice reverberates" .
                    " throughout the unsettling clean facility, white covering" .
                    " the walls and marble floor to give off a properly modern" .
                    " sense of the technology present within the establishment." .
                    " Some people came here to remake themselves, while others" .
                    " came here to escape their past. Whatever their, or even" .
                    " your, purpose for entering those two sliding doors," .
                    " it was clear that everyone here was all to eager to take" .
                    " the fee and fulfill your dream.`n`n" .
                    "Today, you walk into Identity Crisis Inc in order to determine" .
                    " whether or not you would be eligible for a new name and face." .
                    " When you enter, you walk up to the receptionist who promptly" .
                    " dismisses a small fidget device and gives you his full" .
                    " attention. `^\"Welcome to Identity Crisis Inc, is there" .
                    " something I can help you with?\" `7You go ahead and ask" .
                    " him several questions about the procedure, such as the cost." .
                    " The receptionist offers you a smile before answering your" .
                    " questions, `^\"Well, we offer new faces to our customers for" .
                    " `%%s `^gems!\" `7You pause, checking your pockets..." .
                    " `9It does not seem that you have the gems on hand`7.`c ",$cost);
               
            }
                break;
        case "name":
            if ($namec == ""){
                output("`7The man looks at you and then opens a desk droor, searching for a fle.");
                output("\"`LHere we go. Your name is `&%s`L. You seem to have a rather dull name. I can see why you may want a new one",$name);
                output("`LSo, what would you like your new name to be?`7\"`n`n");
                rawoutput("<form action='runmodule.php?module=namechange&op=name' method='POST'>");
                output("`^New Name:");
                rawoutput("<input id='input' name='name' value='$name' maxlength='25'> <input type='submit' class='button' value='$rename'>");
                rawoutput("</form>");
                output("<script language='javascript'>document.getElementById('input').focus();</script>",true);
                addnav ("", "runmodule.php?module=namechange&op=name");
                output("`n`n`&`i(Note: Do be aware that changing your name will not give you colours. Any colours added with be automatically removed.)`i`n`0");
            } else {
                output("`7You shake the man's hand, smile, and hand him `%%s `7gems.",$cost);
                output(" You turn around and walk out of the town hall, smiling happily.");
//              NAME CHANGES ARRAY
                $changednames = unserialize(get_module_pref("changednames"));
                if (!is_array($changednames)) $changednames = array();
                array_push($changednames,base_name($session['user']['acctid']));
//              array_shift($changednames);
                set_module_pref("changednames",serialize($changednames));
//              END ARRAY

                $lastname = unserialize(get_module_pref("lastname"));
                if (!is_array($changednames)) $changednames = array();
                array_push($changednames,base_name($session['user']['acctid']));
//              array_shift($changednames);
                set_module_pref("lastname",serialize($changednames));
                $session['user']['gems'] -= $cost;
                $session['user']['name'] = $namec;
            }
        break;
    }
    addnav("Leave");
    villagenav();
    page_footer();
}
?>