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
    $newtext = "`c`n`&Hey everyone! Let's all give a warm welcome to %s`&, our most recent account!`c ";
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

$basetext = array(
    "`i`c`n`7`LSy`i`l`i`3l`b`#i`b`ls`i`Lt`i`b`3e`b`#n`7, the `b`ufloating`u`b capital of `b`^X`b`i`my`i`6t`^h`ye`xn`7, is the central hub of the realm. A dusty rose haze fills the air and lingers over the `b`4V`b`Pi`pc`mt`bo`br`pi`i`Pa`4n`i`7 city. Keep in mind, however, this city does not house sky scrapers or any buildings which linger past the height of 40 feet. Sylisten is a `usmall city`u, with a multitude of jobs and resources available for all residents. `7Residents often find themselves interested in a variety of places depending on their interests or moods. The most popular location, `b`QC`b`tl`i`mo`i`^c`b`Qk`b`^w`i`mo`tr`i`Qk `QT`b`me`b`i`ta`i`6s`7 - owned by Lotta, a light refreshment or pastry. More quiet, elegant locations include the `b`TG`b`4r`b`Qe`i`^a`b`i`Tt `i`T`bL`bi`i`4b`i`Qr`i`^a`i`T`br`by`i`7 - owned by Edraude, and the `b`KA`b`#r`i`3t`i `bG`b`ka`b`Kl`b`7l`ke`3r`#y`7 where creative displays spark even the most dull individuals.  However, if you are seeking  a private place with more seclusion and beauty than art, games, and literature can provide - the `b`)B`b`7o`b`gt`b`i`@a`kn`i`L`bi`b`lc`ia`il `)G`i`7a`gr`i`@d`b`ke`b`Ln`i`ls`i`7 offers just that. Travelers enter and exit Sylisten via the hover rail station - a lengthy track which suspends in mid-air to carry passengers to their destinations. Off to the side of the floating city, a large airship docks peacefully with its doors open to children who are seeking education at `4R`\$os`Pen`pva`Mil`i `4A`\$c`Pa`pd`Me`mm`Ey`7`i. The free public education is of high quality  or low quality depending on the status of wealth for the parents / child seeking education. `i`n`n`b`4Latest News:`b `4 The city of Sylisten has gained flight!!  `n`^_____________________________`b`n`7Player Owned Locations`b`n`i`pT`th`\$e `pP`ta`\$s`pt`tr`\$y `pE`tm`\$p`po`tr`\$i`pu`tm`i `n`TT`eh`2or`2n`ee`Td `4R`\$o`Rs`&e C`Ra`\$f`4e/`TR`ees`2taur`ean`Tt `&- Shelz Warhound`n`b`)G`b`7e`i`ja`i`)r`7s `b`Ta`b`i`xn`i`qd `b`EG`b`Yi`ez`Tm`i`yo`i`es `&- Clarice`n`)K`^i`Ql`Ea`\$n`Ti`k'`es `\$G`Ta`kd`Tg`@e`Tt`^s `&- Kilani`n `7`iYou may request a venue at any time via petition`n We only ask that you actually `uroleplay advertise`u for yourself`i`n`^_____________________________`c",$vname,$vname);
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
    addnav("F?`GT`i`@o`i`b`)x`2i`b`gc `b`@F`b`i`2o`gr`i`2e`)s`b`gt`b","forest.php");
}
if (getsetting("pvp",1)) {
    addnav("R?`4Ra`~m`)p`4a`~g`4e`0","pvp.php");
}
addnav("Q?`)Q`7u`&i`)t `7t`&o `~F`)i`7e`&l`)d`~s","login.php?op=logout",true);
//if (getsetting("enablecompanions",true)) {
//	tlschema($schemas['mercenarycamp']);
//	addnav($texts['mercenarycamp'], "mercenarycamp.php");
//	tlschema();
//}

tlschema($schemas['fightnav']);
addnav($texts['fightnav']);
tlschema();
addnav("`b`GD`b`i`2o`i`@n`i`2a`i`gt`2i`b`@o`b`gn `b`@C`b`i`2e`i`gn`2t`i`@e`i`gr","lodge.php");
tlschema($schemas['stablename']);
addnav("`i`#V`i`b`3e`b`Lh`b`li`b`~c`i`)l`i`7e `b`)D`b`~e`la`Ll`3e`#r","stables.php");
tlschema();

tlschema($schemas['compnav']);
addnav($texts['compnav']);
addnav("1?`b`^A`b`i`6c`i`th`mi`i`&e`3v`#e`i`&m`i`me`i`tn`b`6t`b`^s ","hof.php");

tlschema();

tlschema($schemas['marketnav']);
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
    addnav(">?`b`TG`b`eu`4i`pl`ed C`eo`Tm`4m`po`en`Es","clan.php");
}

tlschema($schemas['infonav']);
addnav($texts['infonav']);
tlschema();
addnav("", "petition.php?op=faq",false,true);
addnav("`b`~D`b`)a`i`7i`&l`i`Xy `b`&N`b`7e`i`)w`i`~s","news.php");
addnav("`)Online Players","list.php");
addnav("`b`4 FAQ Archive`b","faq.php");

tlschema($schemas['socialnav']);
addnav($texts['socialnav']);
tlschema();
addnav("P?`b`2P`b`greferences","prefs.php");
addnav("`b`LF`b`lacebook `b`LG`b`lroup","https://www.facebook.com/groups/634025810014607/",false,true,"");
addnav("","https://www.google.com/calendar/embed?src=brjkv1049gn4v2v9n3ftbmilcs%40group.calendar.google.com&ctz=America/New_York",false,true,"");
addnav("`b`VP`b`Vlayer `b`VF`b`Vorum","forum.php?op=main");
addnav("`b`LXy Skype Group`b","skype:?chat&blob=ZZhL1hUxQjmsUz4oiCcBmf6RqHxi_RUrhS6Airn8erqrE6w0PFRXLZAim-XIeEMTW6totwfz_In5OglN",false,true,"");
tlschema($schemas['othernav']);
addnav($texts['othernav']);
tlschema();
addnav("`b`\$C`po`Ql`mo`^r `gT`@e`3s`Lt `VB`vo`)x`b","colortestbox.html",false,true,"");

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
        output($texts['text']);
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
    commentdisplay("",$texts['section'],"`@Converse with fellow Xythenians",20,$texts['sayline'], $schemas['sayline']);
}
/*output("
<audio src='pkmn_rpg/music/lavendar_town.mp3' autoplay loop>
</audio>",true);*/
module_display_events("village", "village.php");
page_footer();
?>