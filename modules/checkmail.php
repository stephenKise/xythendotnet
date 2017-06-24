<?php

function checkmail_getmoduleinfo()
{
    $info = [
        'name' => 'Check Mail',
        'author' => '`b`&Stephen Kise`b',
        'description' => 'Check if a player receives any mail with jQuery',
        'version' => '0.1b',
        'category' => 'Mail',
        'download' => 'nope',
    ];
    return $info;
}

function checkmail_install()
{
    module_addhook('api');
    return true;
}

function checkmail_uninstall()
{
    return true;
}

function checkmail_dohook($hook, $args)
{
    switch ($hook) {
        case 'api':
            global $session;
            $args['function'] = 'checkmail';
            $args['params'] = $session['user']['acctid'];
            $args['override_forced_nav'] = true;
            break;
    }
    return $args;
}

function checkmail($acctid = 0)
{
    global $session;
    $sql = db_query(
        "SELECT * FROM mail
        WHERE msgto = '{$session['user']['acctid']}'
        AND (seen = 1 OR seen = 0)
        ORDER BY seen DESC, messageid+0 DESC");
    $output = [];
    while ($row = db_fetch_assoc($sql)) {
        array_push($output, $row);
    }
    return $output;
}
?>