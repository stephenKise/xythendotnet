<?php
// translator ready
// addnews ready
// mail ready
const ALLOW_ANONYMOUS = true;
require_once('common.php');

tlschema('referral');
page_header('Referrals');
if ($session['user']['loggedin']){
    addnav('L?Return to the Lodge', 'lodge.php');
    villagenav();
    $afterdk = (getsetting("referminlevel", 4)==1?" (i.e. after their first TK)":"");
    output("You will automatically receive %s points for each person that you refer to this website who makes it to level %s%s.`n`n", getsetting("refereraward", 25), getsetting("referminlevel", 4),$afterdk);

    $url = getsetting("serverurl",
    "http://".$_SERVER['SERVER_NAME'] .
    ($_SERVER['SERVER_PORT']==80?"":":".$_SERVER['SERVER_PORT']) .
    dirname($_SERVER['REQUEST_URI']));
    if (!preg_match("/\\/$/", $url)) {
    $url = $url . "/";
    savesetting("serverurl", $url);
    }

    output("How does the site know that I referred a person?`n Easy!  When you tell your friends about this site, give out the following link:`n`n");
    output_notl("http://%sreferral.php?r=%s`n`n", $url, rawurlencode($session['user']['login']));
    output("If you do, the site will know that you were the one who sent them here. When they reach level %s for the first time%s, you'll get your points!", getsetting("referminlevel", 4), $afterdk);

    $sql = "SELECT name,level,refererawarded FROM " . db_prefix("accounts") . " WHERE referer={$session['user']['acctid']} ORDER BY dragonkills,level";
    $result = db_query($sql);
    $name=translate_inline("Name");
    $level=translate_inline("Level");
    $awarded=translate_inline("Awarded?");
    $yes=translate_inline("`@Yes!`0");
    $no=translate_inline("`\$No!`0");
    $none=translate_inline("`iNone`i");
    output("`n`nAccounts which you referred:`n");
    rawoutput("<table border='0' cellpadding='3' cellspacing='0'><tr><td>$name</td><td>$level</td><td>$awarded</td></tr>");
    $number=db_num_rows($result);
    for ($i=0;$i<$number;$i++){
    $row = db_fetch_assoc($result);
    rawoutput("<tr class='".($i%2?"trlight":"trdark")."'><td>");
    output_notl($row['name']);
    rawoutput("</td><td>");
    output_notl($row['level']);
    rawoutput("</td><td>");
    output_notl($row['refererawarded']?$yes:$no);
    rawoutput("</td></tr>");
    }
    if (db_num_rows($result)==0){
    rawoutput("<tr><td colspan='3' align='center'>");
    output_notl($none);
    rawoutput("</td></tr>");
    }
    rawoutput("</table>",true);
} else {
    output("`c`i`EThe Realm of Xythen is a multiplayer role playing game adaptation of the Legend of the Green Dragon games. Adventure into this unqiue world of industrialized danger and create friendships that last a lifetime. Destroy the Tentromech and get your name on in the Achievement Archives, or simply indulge your creative side with roleplay.`c`i`n`n");
    addnav("Create a character","create.php?r=".HTMLEntities(httpget('r'), ENT_COMPAT, getsetting("charset", "ISO-8859-1")));
    addnav("Login Page","index.php");
}
page_footer();
?>