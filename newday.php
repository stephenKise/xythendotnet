<?php
// translator ready
// addnews ready
// mail ready
require_once("common.php");
require_once("lib/http.php");
require_once("lib/sanitize.php");
require_once("lib/buffs.php");

tlschema("newday");
//mass_module_prepare(array("newday-intercept", "newday"));
modulehook("newday-intercept",array());
/***************
 **  SETTINGS **
 ***************/
$turnsperday = getsetting("turns",10);
$maxinterest = ((float)getsetting("maxinterest",10)/100) + 1; //1.1;
$mininterest = ((float)getsetting("mininterest",1)/100) + 1; //1.1;
$dailypvpfights = getsetting("pvpday",3);

$resline = (httpget('resurrection')=="true") ? "&resurrection=true" : "" ;
/******************
 ** End Settings **
 ******************/
$dk = httpget('dk');
if ((count($session['user']['dragonpoints']) <
            $session['user']['dragonkills']) && $dk!="") {
    array_push($session['user']['dragonpoints'],$dk);
    switch($dk){
    case "hp":
        $session['user']['maxhitpoints']+=5;
        break;
    case "at":
        $session['user']['attack']++;
        break;
    case "de":
        $session['user']['defense']++;
        break;
    }
}

$labels = array(
        "hp"=>"Max Hitpoints + 5",
        "ff"=>"Forest Fights + 1",
        "at"=>"Attack + 1",
        "de"=>"Defense + 1",
        "unknown"=>"Unknown Spends (contact an admin to investigate!)",
);
$canbuy = array(
        "hp"=>1,
        "ff"=>1,
        "at"=>1,
        "de"=>1,
        "unknown"=>0,
);
$retargs = modulehook("dkpointlabels", array('desc'=>$labels, 'buy'=>$canbuy));
$labels = $retargs['desc'];
$canbuy = $retargs['buy'];
$pdks = array();
reset($labels);
foreach($labels as $type=>$label) {
    $pdks[$type] = (int)httppost($type);
}

$pdk=httpget("pdk");

$dp = count($session['user']['dragonpoints']);
$dkills = $session['user']['dragonkills'];

if ($pdk==1){
    reset($labels);
    $pdktotal = 0;
    $pdkneg = false;
    modulehook("pdkpointrecalc");
    foreach($labels as $type=>$label) {
        $pdktotal += (int)$pdks[$type];
        if((int)$pdks[$type] < 0) $pdkneg = true;
    }
    if ($pdktotal == $dkills-$dp && !$pdkneg) {
        $dp += $pdktotal;
        $session['user']['maxhitpoints'] += (5 * $pdks["hp"]);
        $session['user']['attack'] += $pdks["at"];
        $session['user']['defense'] += $pdks["de"];
        reset($labels);
        foreach($labels as $type=>$label) {
            $count = 0;
            if (isset($pdks[$type])) $count = (int)$pdks[$type];
            while($count) {
                $count--;
                array_push($session['user']['dragonpoints'],$type);
            }
        }
    }else{
        output("`\$Error: Please spend the correct total amount of crystal guardian points.`n`n");
    }
}

if ($dp < $dkills) {
    require_once("lib/newday/dragonpointspend.php");
}
//elseif (!$session['user']['race'] || $session['user']['race']==RACE_UNKNOWN){
  //    require_once("lib/newday/setrace.php");
//}elseif ($session['user']['specialty']==""){
//  require_once("lib/newday/setspecialty.php");
//}
else{
    page_header("It is a new day!");
    rawoutput("<font size='+1'>");
    output("`c`b`#It is a New Day!`0`b`c`n");
    rawoutput("</font>");
    
    if (!isset($session['user']['prefs']['toggleooc'])) $session['user']['prefs']['toggleooc'] = 1;
    output("`cYour stats have been restored...`c`n`n");
    
    $motdsql = db_query("SELECT motdbody FROM motd WHERE motdtype = 0 ORDER BY motditem desc LIMIT 1");
    $motdrow = db_fetch_assoc($motdsql);
    $changelogsql = db_query("SELECT title,description FROM changelog ORDER BY id DESC LIMIT 5");
    output("<center><div style='height:0%;width:80%;border:1px groove #989898;overflow:auto;'>`n`b`7Latest Important Message`b`n`n".nl2br($motdrow['motdbody'])."</div></center>`n`n",true);
    
    $rp = $session['user']['restorepage'];
    $x = max(strrpos("&",$rp),strrpos("?",$rp));
    if ($x>0) $rp = substr($rp,0,$x);
    if (substr($rp,0,10)=="badnav.php"){
        addnav("Continue...","news.php");
    }else{
        addnav("Continue...", cmd_sanitize($rp));
    }
    
    addnav("Server Updates");
    while($changelogrow = db_fetch_assoc($changelogsql)){
        addnav("`\$`b".$changelogrow['title']."`b","",true);
        addnav("`e".$changelogrow['description'],"",true);
        addnav("`)_______________","",true);
    }
    
    $resurrection = httpget('resurrection');

    if ($session['user']['alive']!=true){
        $session['user']['resurrections']++;
        output("`@You are resurrected!  This is resurrection number %s.`0`n",$session['user']['resurrections']);
        $session['user']['alive']=true;
        invalidatedatacache("list.php-warsonline");
    }
    $session['user']['age']++;
    $session['user']['seenmaster']=0;
    $turnstoday = "Base: $turnsperday";
    $args = modulehook("pre-newday",array("resurrection"=>$resurrection, "turnstoday"=>$turnstoday));
    $turnstoday = $args['turnstoday'];

    $interestrate = e_rand($mininterest*100,$maxinterest*100)/(float)100;
    if ($session['user']['turns']>getsetting("fightsforinterest",4) && $session['user']['goldinbank']>=0) $interestrate=1;
    elseif (getsetting("maxgoldforinterest", 100000) && $session['user']['goldinbank']>=getsetting("maxgoldforinterest", 100000)) $interestrate=1;

    //clear all standard buffs
    $tempbuf = unserialize($session['user']['bufflist']);
    $session['user']['bufflist']="";
    strip_all_buffs();
    tlschema("buffs");
    while(list($key,$val)=@each($tempbuf)){
        if (array_key_exists('survivenewday', $val) &&
                $val['survivenewday']==1){
            if (array_key_exists('schema', $val) && $val['schema'])
                tlschema($val['schema']);
            apply_buff($key,$val);
            if (array_key_exists('schema', $val) && $val['schema'])
                tlschema();
        }
    }
    tlschema();

    reset($session['user']['dragonpoints']);
    $dkff=0;
    while(list($key,$val)=each($session['user']['dragonpoints'])){
        if ($val=="ff"){
            $dkff++;
        }
    }
    
    if ($session['user']['hashorse']){
        $buff = unserialize($playermount['mountbuff']);
        if (!isset($buff['schema']) || $buff['schema'] == "")
            $buff['schema']="mounts";
        apply_buff('mount',$buff);
    }
    
    $r1 = e_rand(-1,1);
    $r2 = e_rand(-1,1);
    $spirits = $r1+$r2;
    $resurrectionturns=$spirits;
    if ($resurrection=="true"){
        addnews("`&%s`& has been resurrected by %s`&.",$session['user']['name'],getsetting('deathoverlord','`$Ramius'));
        $spirits=-6;
        $resurrectionturns=getsetting('resurrectionturns',-6);
        if (strstr($resurrectionturns,'%')) {
            $resurrectionturns=strtok($resurrectionturns,'%');
            $resurrectionturns=(int)$resurrectionturns;
            if ($resurrectionturns<-100) $resurrectionturns=-100;
            $resurrectionturns=round(($turnsperday+$dkff)*($resurrectionturns/100),0);
        } else {
            if ($resurrectionturns<-($turnsperday+$dkff)) $resurrectionturns=-($turnsperday+$dkff);
        }
        $session['user']['deathpower']-=100;
        $session['user']['restorepage']="village.php?c=1";
    }

    $sp = array((-6)=>"Resurrected", (-2)=>"Very Low", (-1)=>"Low",
            (0)=>"Normal", 1=>"High", 2=>"Very High");
    $sp = $sp;
    if (abs($spirits)>0){
        if($resurrectionturns>0){
            $gain="gain";
        }else{
            $gain="lose";
        }
        $sff = abs($resurrectionturns);
    }

    $session['user']['laston'] = date("Y-m-d H:i:s");
    $bgold = $session['user']['goldinbank'];
    $session['user']['goldinbank']*=$interestrate;
    $nbgold = $session['user']['goldinbank'] - $bgold;

    if ($nbgold != 0) {
        debuglog(($nbgold >= 0 ? "earned " : "paid ") . abs($nbgold) . " gold in interest");
    }
    
    // TURNS CARRIED OVER EACH NEWDAY - MAVERICK
    $turns_leftover = $session['user']['turns'];
    $donation_grades = array(500=>0.2, 1000=>0.3, 5000=>0.5, 10000=>0.65, 20000=>0.8);
    $keep_turns = 0;
    foreach($donation_grades as $key => $val){
        if ($session['user']['donation'] >= $key) $keep_turns = ceil($val*$turns_leftover);
    }
//  debug($keep_turns);
    // END TURNS CARRIED OVER
    
    $turnstoday .= ", Spirits: $resurrectionturns, DK: $dkff";
    $session['user']['turns']=$turnsperday+$resurrectionturns+$dkff+$keep_turns;
    $session['user']['hitpoints'] = $session['user']['maxhitpoints'];
    $session['user']['spirits'] = $spirits;
    if ($resurrection != "true")
        $session['user']['playerfights'] = $dailypvpfights;
    $session['user']['transferredtoday'] = 0;
    $session['user']['amountouttoday'] = 0;
    $session['user']['seendragon'] = 0;
    $session['user']['seenmaster'] = 0;
    $session['user']['fedmount'] = 0;
    if ($resurrection!="true"){
        $session['user']['soulpoints']=50 + 5 * $session['user']['level'];
        $session['user']['gravefights']=getsetting("gravefightsperday",10);
    }
    $session['user']['boughtroomtoday'] = 0;
    $session['user']['recentcomments']=$session['user']['lasthit'];
    $session['user']['lasthit'] = gmdate("Y-m-d H:i:s");
    if ($session['user']['hashorse']){
        $msg = $playermount['newday'];
        require_once("lib/substitute.php");
        $msg = substitute_array("`n`&".$msg."`0`n");
        require_once("lib/mountname.php");
        list($name, $lcname) = getmountname();

        $mff = (int)$playermount['mountforestfights'];
        $session['user']['turns'] += $mff;
        $turnstoday.=", Mount: $mff";
        if ($mff > 0) {
            $state = "gain";
            $color = "`^";
        } elseif ($mff < 0) {
            $state = "lose";
            $color = "`$";
        }
        $mff = abs($mff);
    }
    
    if ($session['user']['hauntedby']>""){
        $session['user']['turns']--;
        $session['user']['hauntedby']="";
        $turnstoday.=", Haunted: -1";
    }

    require_once("lib/extended-battle.php");
    unsuspend_companions("allowinshades");

    if (!getsetting("newdaycron",0)) {
        //check last time we did this vs now to see if it was a different game day.
        $newDaySemaphore = getsetting(
            'newdaySemaphore',
            '0000-00-00 00:00:00'
        );
        $newDaySemaphoreTime = strtotime($newDaySemaphore . '+0000');
        $lastnewdaysemaphore = (int) convertgametime($newDaySemaphoreTime);
        $gametoday = (int) gametime();
        if (gmdate("Ymd",$gametoday)!=gmdate("Ymd",$lastnewdaysemaphore)){
                // it appears to be a different game day, acquire semaphore and
                // check again.
            $sql = "LOCK TABLES " . db_prefix("settings") . " WRITE";
            db_query($sql);
            clearsettings();
            $lastnewdaysemaphore = (int) convertgametime($newDaySemaphoreTime);
            $gametoday = (int) gametime();
            if (gmdate("Ymd", $gametoday)!=gmdate("Ymd",$lastnewdaysemaphore)){
                //we need to run the hook, update the setting, and unlock.
                savesetting("newdaySemaphore",gmdate("Y-m-d H:i:s"));
                $sql = "UNLOCK TABLES";
                db_query($sql);
                require("lib/newday/newday_runonce.php");
            }else{
                //someone else beat us to it, unlock.
                $sql = "UNLOCK TABLES";
                db_query($sql);
            }
        }

    }
    $args = modulehook("newday",
            array("resurrection"=>$resurrection, "turnstoday"=>$turnstoday));
    $turnstoday = $args['turnstoday'];
    debuglog("New Day Turns: $turnstoday");

}
page_footer();
?>