<?php

function tickettutorial_getmoduleinfo()
{
    $info = [
        'name' => 'Ticket Tutorial',
        'author' => '`b`&Stephen Kise`b',
        'description' => 'Tutorial on how to use the tickets system',
        'version' => '0.1b',
        'category' => 'FAQ',
        'settings' => [
            'message' => 'Message to be given after a petition:, textarea| Thank you for sending in a ticket!',
        ],
        'prefs' => [
            'disable' => 'Has this player disabled the message?, bool| 0',
        ]
    ];
    return $info;
}

function tickettutorial_install()
{
    return true;
}

function tickettutorial_uninstall()
{
    module_addhook('addpetition');
    return true;
}

function tickettutorial_dohook($hook, $args)
{
    switch ($hook) {
        case 'addpetition':
            global $session;
            debug(get_module_pref('disable'));
            $disable = "`n`n`QYou can 
                `2[<a href='runmodule.php?module=tickettutorial&op=disable'>
                disable</a>`2]`Q this message at any time.";
            require_once('lib/systemmail.php');
            //if (get_module_pref('disable') == 0) {
                systemmail(
                    $session['user']['acctid'],
                    'About your Ticket',
                    get_module_setting('message').$disable
                );
            //}
            break;
    }
    return $args;
}
?>