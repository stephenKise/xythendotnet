<?php


function whoshere_getmoduleinfo()
{
    $info = [
        'name' => 'Who\'s Here',
        'author' => '`b`&Stephen Kise`b',
        'description' =>
            'An addition to multiple pages to let the user know who is around.',
        'version' => '1.0',
        'category' => 'Village',
        'download' => 'nope',
        'override_forced_nav' => true,
        'prefs'=> [
            'hook' => 'Hook location, viewonly| village',
        ],
    ];
    return $info;
}


function whoshere_install()
{
    module_addhook('forest');
    module_addhook('village');
    module_addhook('gardens');
    module_addhook('graveyard');
    module_addhook('gypsy');
    module_addhook('inn');
    module_addhook('lodge');
    module_addhook('stables');
    module_addhook('rock');
    module_addhook('header-superuser');
    return true;
}


function whoshere_uninstall()
{
    return true;
}

function whoshere_dohook($hook, $args)
{
    global $_SERVER;
    if (get_module_pref('hook') != $hook) {
        set_module_pref('hook', $hook);
    }
    if ($hook != 'newday') {
        output("`n`b`@Residents Nearby:`b`n");
        rawoutput("<hr><span class='colLtYellow'><div class='whoshere'>");
        output('Loading...');
        rawoutput("</div></span><hr>");
    }
    $timeOut = date(
        'Y-m-d H:i:s',
        strtotime('-' . getsetting('LOGINTIMEOUT', 300) . ' seconds')
    );
    $r = urlencode($_SERVER['REQUEST_URI']);
    $sql = db_query(
        "SELECT acctid FROM accounts
        WHERE loggedin = '1'
        AND laston > '$timeOut'"
    );
    while ($row = db_fetch_assoc($sql)) {
        addnav('', "bio.php?char={$row['acctid']}&ret=$r");
    }
    rawoutput(
        "<script type='text/javascript'>
            $(document).ready(function(){
                renewWhosHere();
            });
            function renewWhosHere()
            {
                $.getJSON('runmodule.php?module=whoshere&op=json&where=$hook',function(data){
                    var items = [];
                    var count = 0;
                    var current = 0;
                    for (k in data) {
                        count++;
                    }
                    if (count > 0) {
                        $.each(data, function(key, val){
                            current++;
                            if (current == 1) {
                                items.push('<a href=\"bio.php?char=' + key + '&ret=$r\">' + val + '</a>');
                            }
                            else if (current != 1 && current != count) {
                                items.push(' <a href=\"bio.php?char=' + key + '&ret=$r\">' + val + '</a>'); 
                            }
                            else if (current == count) {
                                items.push(' and <a href=\"bio.php?char=' + key + '&ret=$r\">' + val + '</a>.');
                            }
                        });
                    }
                    else {
                        items.push('No one...');
                    }
                    $('.whoshere').html(' ' + items);
                });
            }
        </script>"
    );
    return $args;
}

function whoshere_run()
{
    global $session;
    switch (httpget('op'))
    {
        case "json":
            $where = httpget('where');
            $timeOut = date(
                'Y-m-d H:i:s',
                strtotime('-' . getsetting('LOGINTIMEOUT', 300) . ' seconds')
            );
            $accounts = db_prefix('accounts');
            $sql = db_query(
                "SELECT a.name AS name, m.userid AS acctid
                FROM accounts AS a
                LEFT JOIN module_userprefs AS m ON m.userid = a.acctid
                WHERE m.modulename = 'whoshere'
                AND m.setting = 'hook'
                AND m.value = '$where'
                AND a.acctid != '{$session['user']['acctid']}'
                AND a.loggedin = 1
                AND a.laston > '$timeOut'
                AND a.location = '{$session['user']['location']}'
                ORDER BY a.acctid+0 ASC"
            );

            while ($row = db_fetch_assoc($sql)) {
                $json[$row['acctid']] = appoencode($row['name']);
            }
            echo (json_encode($json));
            break;
    }
}
?>