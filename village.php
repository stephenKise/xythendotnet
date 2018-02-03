<?php
// translator ready
// addnews ready
// mail ready
require_once("common.php");
require_once("lib/commentary.php");
require_once("lib/http.php");
require_once("lib/events.php"); 
require_once("lib/experience.php");

tlschema('village');
//modulehook("eventmsg");
//output("<center><big>`\$Currently under construction!</big></center>",true);
//mass_module_prepare(array("village","validlocation","villagetext","village-desc"));
// See if the user is in a valid location and if not, put them back to
// a place which is valid
$myclan = db_fetch_assoc(db_query("SELECT clanname AS name FROM ".db_prefix("clans")." WHERE clanid = ".$session['user']['clanid']));
if ($session['user']['location']  == $myclan['name']) $session['user']['location'] = $session['user']['previouslocation'];
$valid_loc = array();
$vname = getsetting("villagename", LOCATION_FIELDS);
$iname = getsetting("innname", LOCATION_INN);
$valid_loc[$vname]="village";
$valid_loc = modulehook("validlocation", $valid_loc);

if (!isset($valid_loc[$session['user']['location']])) $session['user']['location']=$vname;
$newestname = "";
$newestplayer = getsetting("newestplayer", "");
if ($newestplayer == $session['user']['acctid']) {
    $newtext = "`c`nWelcome to the site! If you have any questions, feel free to check out our FAQ or petition staff!`c";
    $newestname = $session['user']['name'];
} else {
    $newtext = "`c`n`zLet's all give a warm welcome to %s`z, our most recent account!`c ";
    if ((int)$newestplayer != 0) {
        $sql = "SELECT name FROM " . db_prefix("accounts") . " WHERE acctid='$newestplayer'";
        $result = db_query_cached($sql, "newest");
        if (db_num_rows($result) == 1) {
            $row = db_fetch_assoc($result);
            $newestname = $row['name'];
        } else {
            $newestplayer = "";
        }
    } else {
        $newestplayer > ""?$newestname = $newestplayer:$newestname = "";
    }
}

$basetext = array("`c`b`LS`b`i`ly`3l`i`#`bi`b`Ls`i`lt`3e`i`Ln`7, the floating capital of `b`i`dX`i`b`^y`Dth`^e`dn`7, suspends one-hundred feet above the ocean with the assistance of aether technology. The city consists of several districts in order to maintain a systematic order. In the very center of the city is the Immigration Center, a large Classical-style building where the `b`~O`b`)m`dn`i`Di`i `b`DP`b`i`^o`dr`i`7t`)a`b`~l`b`7 is heavily guarded and protected. The `b`~O`b`)m`dn`i`Di`i `b`DP`b`i`^o`dr`i`7t`)a`b`~l`b`7 being the sole entry point into and out of the realm of `b`i`dX`i`b`^y`Dth`^e`dn`7, was created to create realm stability after the `b`8T`b`i`ee`En`i`Ft`Dr`^o`b`dm`b`i`Qe`i`Cc`Ah `AW`i`da`i`er`7. Outside of the Immigration Center is the center of `b`LS`b`i`ly`3l`i`#`bi`b`Ls`i`lt`3e`i`Ln`7. The city center contains a large and elaborate aether fountain using the same style of stone as the courthouse; decorated with several carvings and engravings meant to represent the `b`TH`b`i`ei`Es`i`tt`Eo`b`er`b`i`Ty`i `7`iof`i `b`i`dX`i`b`^y`Dth`^e`dn`7. Several stone benches surround the fountain for lounging and relaxing. Occasional traveling vendors can be found on the outskirts of the city center, typically offering meals or trinkets to those nearby. Located across from the Immigration Center is the `b`~L`b`i`:o`5c`i`%a`b`Ql`b `b`~T`b`i`:a`5v`i`%e`Br`b`Cn`b`7, where those who need a warm place to rest, eat or drink may head. To the right and left of the `b`~T`b`i`:a`5v`i`%e`Br`b`Cn`b `7are two other classical-style buildings, the `b`dN`b`i`Da`^t`b`mi`b`Xo`i`^n`Da`b`dl`b `b`yB`b`i`^a`Dn`b`dk`b`i`7 and the `b`TG`b`4r`b`Qe`i`^a`b`i`Tt `i`T`bL`bi`i`4b`i`Qr`i`^a`i`T`br`by`i`7. As one can see, the center of `b`LS`b`i`ly`3l`i`#`bi`b`Ls`i`lt`3e`i`Ln `7primarily caters to newcomers, while also offering a common place to meet up with friends.`n`n Streets lined with small shops and lower-class housing branch off of the city center, leading to different districts. North, and past the Immigration Center, leads to the `b`!R`b`i`Le`i`ls`i`[i`i`]d`-e`b`=n`b`Kt`ki`ga`Ul `b`!D`b`i`Li`ls`i`[t`]r`-i`b`=c`b`Kt `7for upper and middle class citizens. To the East, down the streets framing the `b`dN`b`i`Da`^t`b`mi`b`Xo`i`^n`Da`b`dl`b `b`yB`b`i`^a`Dn`b`dk`b`i`7, is the industrial quarter - where factories and laboratories can be found. Heading south, passing the `b`~L`b`i`:o`5c`i`%a`b`Ql`b `b`~T`b`i`:a`5v`i`%e`Br`b`Cn`b`7, would lead to the social district where the `b`&G`b`7u`=i`b`]l`b`-d `b`&H`b`7a`=l`b`]l`b`-s`7 and `b`)B`b`7o`b`gt`b`i`@a`kn`i`L`bi`b`lc`ia`il `)G`i`7a`gr`i`@d`b`ke`b`Ln`i`ls`i`7 reside. In addition, there are several bars and restaurants for those seeking a more social or private atmosphere. Finally, down the streets framing the `b`TG`b`4r`b`Qe`i`^a`b`i`Tt `i`T`bL`bi`i`4b`i`Qr`i`^a`i`T`br`by`i`7, leads to the central area for travel; where individuals could buy tickets to the main land or other major cities in `b`i`dX`i`b`^y`Dth`^e`dn`7.`n`n To the east of `b`LS`b`i`ly`3l`i`#`bi`b`Ls`i`lt`3e`i`Ln`7, on the main land, a small coastal area for fishing and oceanic trade rests nestled between the `b`3o`b`#c`i`ke`i`-a`_n `b`_w`b`-a`kt`i`#e`i`3r`b`ls`b`7 and a steep incline that separates the `b`LC`b`#r`i`3y`i`ls`Lt`b`3a`b`#l `b`@F`b`i`2o`gr`i`Ge`2s`@t`7 from the citizens. This area is primarily filled with fishermen, `b`&b`b`i`ko`i`La`$t`b`4s`b`7, docks and several fish merchants - but further in the distance there is a mile stretch of beach meant for lounging in the `b`^s`b`i`du`Dn`i`7.`c",$vname,$vname);

$origtexts = array(
    "text"=>$basetext,
    "clock"=>"The clock on the inn reads `^%s`@.`n",
    "title"=>array("%s Square", $vname),
    "sayline"=>"says",
    "newest"=>$newtext,
    "newestplayer"=>$newestname,
    "newestid"=>$newestplayer,
    "gatenav"=>"City Gates",
    "fightnav"=>"Blades Boulevard",
    "compnav"=>"Contest Lane",
    "marketnav"=>"Market Street",
    "tavernnav"=>"Tavern Street",
    "infonav"=>"Information",
    "othernav"=>"Other",
    "socialnav"=>"Social & Account",
    "section"=>"village",
    "innname"=>$iname,
    "stablename"=>"Merick's Stables",
    "mercenarycamp"=>"Mercenary Camp"
    );
$schemas = array(
    "text"=>"village",
    "clock"=>"village",
    "title"=>"village",
    "talk"=>"village",
    "sayline"=>"village",
    "newest"=>"village",
    "newestplayer"=>"village",
    "newestid"=>"village",
    "gatenav"=>"village",
    "fightnav"=>"village",
    "compnav"=>"village",
    "marketnav"=>"village",
    "tavernnav"=>"village",
    "infonav"=>"village",
    "othernav"=>"village",
    "section"=>"village",
    "innname"=>"village",
    "stablename"=>"village",
    "mercenarycamp"=>"village"
    );
$origtexts['schemas'] = $schemas;
$texts = modulehook("villagetext",$origtexts);
//and now a special hook for the village
$texts = modulehook("villagetext-{$session['user']['location']}",$texts);
$schemas = $texts['schemas'];

tlschema($schemas['title']);
page_header($texts['title']);
tlschema();

addcommentary();
$skipvillagedesc = handle_event("village");
checkday();

if ($session['user']['slaydragon'] == 1) $session['user']['slaydragon'] = 0;

if (!$session['user']['alive']) redirect("shades.php");
$op = httpget('op');
$com = httpget('comscroll');
$refresh = httpget("refresh");
$commenting = httpget("commenting");
$comment = httppost('insertcommentary');
// Don't give people a chance at a special event if they are just browsing
// the commentary (or talking) or dealing with any of the hooks in the village.
if (!$op && $com=="" && !$comment && !$refresh && !$commenting) {
// The '1' should really be sysadmin customizable.
    if (module_events("village", getsetting("villagechance", 0)) != 0) {
        if (checknavs()) {
            page_footer();
        } else {
        // Reset the special for good.
            $session['user']['specialinc'] = "";
            $session['user']['specialmisc'] = "";
            $skipvillagedesc=true;
            $op = "";
            httpset("op", "");
        }
    }
}

tlschema($schemas['gatenav']);
addnav($texts['gatenav']);
tlschema();

if (is_module_active("blackplague") && get_module_pref("plague_stage","blackplague",$session['user']['acctid'])==4) {
    addnav("Go to the Clinic...","",true);
} else {
    addnav("F?`b`LC`b`#r`i`3y`i`ls`Lt`b`3a`b`#l `b`@F`b`i`2o`gr`i`Ge`2s`@t","forest.php");
}
if (getsetting("pvp",1)) {
    addnav("R?`4Ra`~m`)p`4a`~g`4e`0","pvp.php");
}
addnav("X?`b`+L`b`i`3o`i`-g`[o`b`]u`b`i`#t`i","login.php?op=logout",true);
//if (getsetting("enablecompanions",true)) {
//	tlschema($schemas['mercenarycamp']);
//	addnav($texts['mercenarycamp'], "mercenarycamp.php");
//	tlschema();
//}

tlschema($schemas['fightnav']);
addnav($texts['fightnav']);
tlschema();
addnav("`b`dH`b`i`Di`^g`i`mh `b`dR`b`i`Do`i`6l`b`^l`b`i`ye`i`mr `b`dC`b`Dl`i`^u`i`yb","lodge.php");
tlschema($schemas['stablename']);
addnav("`b`TM`b`i`eo`Eu`i`Fn`8t`b `*S`b`ph`i`Bo`i`dp","stables.php");
tlschema();

tlschema($schemas['compnav']);
addnav($texts['compnav']);
addnav("1?`b`^A`b`i`6c`i`th`mi`i`&e`3v`#e`i`&m`i`me`i`tn`b`6t`b`^s ","hof.php");

tlschema();

tlschema($schemas['fightnav']);
addnav($texts['marketnav']);
tlschema();
addnav("`b`^B`b`i`ma`Mn`tk`i `)o`7f `b`LS`b`i`ly`kl`3i`#s`3t`ke`ln`i","runmodule.php?module=banking&op=main",false,true,"800x350");

tlschema($schemas['tavernnav']);
addnav($texts['tavernnav']);
tlschema();
tlschema($schemas['innname']);
addnav("`b`)T`b`7i`&t`i`Ra`i`5n`b`Ri`b`7u`i`)m `RR`i`5o`i`Rs`b`Xe`b `b`)I`b`7n`&n`i","inn.php",true);
tlschema();

addnav("G?`b`)B`b`7o`b`gt`b`i`@a`kn`i`L`bi`b`lc`ia`il `)G`i`7a`gr`i`@d`b`ke`b`Ln`i`ls`i", "gardens.php");
if (getsetting("allowclans",1)) {
    addnav("G?`b`&G`b`7u`=i`b`]l`b`-d `b`&H`b`7a`=l`b`]l`b`-s","clan.php");
}

tlschema($schemas['infonav']);
addnav($texts['infonav']);
tlschema();
addnav("", "petition.php?op=faq",false,true);
addnav("`b`~D`b`)a`i`7i`&l`i`Xy `b`&N`b`7e`i`)w`i`~s","news.php");
addnav("`i`b`^O`i`b`Dn`Xl`b`&i`b`i`7n`)e`i `b`~P`b`)l`i`7a`my`i`De`b`^r`b`7s
","list.php");
addnav("`!`bF`bA`iQ`i `b`i`&&`b`i `!`bG`b`iui`ide`bs`b","faq.php");

tlschema($schemas['socialnav']);
addnav($texts['socialnav']);
tlschema();
addnav("`b`5S`b`i`.e`i`,t`b`/t`b`i`?i`i`Rn`b`Dg`b`i`ds`i","prefs.php");
addnav("`b`LF`b`i`[a`]c`i`=eb`#o`ko`K`bk`b `b`LG`b`[r`i`b`]o`b`ku`Kp`i","https://www.facebook.com/groups/634025810014607/",false,true,"");
addnav("`b`|P`:l`b`i`Va`5y`i`%e`,r `bF`b`%o`5r`:u`|m","forum.php?op=main");
addnav("`b`]S`b`i`=k`Ly`i`lp`b`\\e`b `b`]G`b`=r`i`Lo`lu`i`\\p","skype:?chat&blob=ZZhL1hUxQjmsUz4oiCcBmf6RqHxi_RUrhS6Airn8erqrE6w0PFRXLZAim-XIeEMTW6totwfz_In5OglN",false,true,"");
tlschema($schemas['othernav']);
addnav($texts['othernav']);
tlschema();
addnav("`b`(C`b`i`\$o`i`9l`b`Ao`b`Qr `b`dT`b`i`^e`i`Ds`b`gt `oB`b`i`ko`i`Lx","colortestbox.html",false,true,"");


//addnav("Refer a Friend", "referral.php");

tlschema('nav');
addnav("Superuser");
if ($session['user']['superuser'] & SU_EDIT_COMMENTS) addnav(",?`#Comment Moderation","moderate.php");
if ($session['user']['superuser']&~SU_DOESNT_GIVE_GROTTO) addnav("+?`LLe Wild Grotto","superuser.php");
// else addnav("+?`LLe Wild Grotto","grotto.php");
if ($session['user']['superuser'] & SU_INFINITE_DAYS) addnav("/?`^New Day","newday.php");
tlschema();
//let users try to cheat, we protect against this and will know if they try.
addnav("","superuser.php");
addnav("","user.php");
addnav("","donators.php");
addnav("","viewpetition.php");
addnav("","weaponeditor.php");

if (!$skipvillagedesc) {
    modulehook("collapse{", array("name"=>"villagedesc-".$session['user']['location']));
    tlschema($schemas['text']);
    if ($texts['image'] != '') {
        rawoutput("<div class='village container' style='display: block;'>");
        rawoutput("<div class='village description' style='max-width: 750px; display: table; margin: auto;'>");
        rawoutput("<img src='{$texts['image']}' style='display: inline-block; float: left;' />");
        output($texts['text'], true);
        rawoutput("</div>");
        rawoutput("</div>");
    }
    else {
        output($texts['text'], true);
    }
    modulehook("village-desc",$texts);
    modulehook("}collapse");
    modulehook("collapse{", array("name"=>"villageclock-".$session['user']['location']));
    tlschema();
    modulehook("}collapse");
    //support for a special village-only hook
    modulehook("village-desc-{$session['user']['location']}",$texts);
    if ($texts['newestplayer'] > "" && $texts['newest']) {
        modulehook("collapse{", array("name"=>"villagenewest-".$session['user']['location']));
        tlschema($schemas['newest']);
        output($texts['newest'], $texts['newestplayer']);
        tlschema();
        $id = $texts['newestid'];
        if ($session['user']['superuser'] & SU_EDIT_USERS && $id) {
            $edit = translate_inline("");
            rawoutput("");
            addnav("","user.php?op=edit&userid=$id");
        }
        output_notl("`n");
        modulehook("}collapse");
    }
}

//medallion contest

$sqlcontest = "SELECT userid FROM ".db_prefix("module_userprefs")." WHERE modulename = 'medcontest' and setting = 'medpoints' and value > 0 ORDER BY value+0 DESC LIMIT 1";
$resultcontest = db_query_cached($sqlcontest, "medcontest");
$rowcontest = db_fetch_assoc($resultcontest);
if ($rowcontest['userid'] <> ""){
    $sql2contest="SELECT name FROM ".db_prefix("accounts")." WHERE acctid ='".$rowcontest['userid']."'";
    $result2contest = db_query_cached($sql2contest, "contestleader");
    $row2contest = db_fetch_assoc($result2contest);
    $plaque = $row2contest['name'];
}

$leader = get_module_setting("leader","battlearena");
if ($leader != 0) {
    $sql3 = "SELECT name FROM " . db_prefix("accounts") . " WHERE acctid='$leader'";
    $result3 = db_query_cached($sql3, "battleleader");
    $row3 = db_fetch_assoc($result3);
    $leadername = $row3['name'];
}

$mayorid = getsetting("xythenmayor",0);
if ($mayorid>0) {
    $mayorsql = db_fetch_assoc(db_query_cached("SELECT name FROM accounts WHERE acctid=$mayorid", "mayor"));
}

//modulehook("breakingnews");
$texts = modulehook("village",$texts);
//special hook for all villages... saves queries...
$texts = modulehook("village-{$session['user']['location']}",$texts);

if ($skipvillagedesc) output("`n");

$args = modulehook("blockcommentarea", array("section"=>$texts['section']));
if (!isset($args['block']) || $args['block'] != 'yes') {
    tlschema($schemas['talk']);
    output($texts['talk']);
    tlschema();
    commentdisplay("",$texts['section'],"`@Converse with fellow Xythenians",15,$texts['sayline'], $schemas['sayline']);
}
/*output("
<audio src='pkmn_rpg/music/lavendar_town.mp3' autoplay loop>
</audio>",true);*/
module_display_events("village", "village.php");
page_footer();
?>
