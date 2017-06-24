<?php

function YearlyReset_getmoduleinfo()
{
    $info = [
        'name' => 'Yearly Reset',
        'author' => '`b`&Stephen Kise`b',
        'description' => 'A yearly reset to keep the TKing competitive through the year.',
        'category' => 'Forest',
        'version' => '0.1b',
        'settings' => [
            'Yearly Reset Settings, title',
            'last_reset' => 'Time of the last reset:, viewonly',
        ],
        'prefs' => [
            'dragonkills' => 'DragonKills during last reset:',
        ],
    ];
    return $info;
}

function YearlyReset_install()
{
    module_addhook('dragonkill');
    return true;
}

function YearlyReset_uninstall()
{
    return true;
}

function YearlyReset_dohook($hook, $args)
{
    switch ($hook) {
        case 'dragonkill':

            break;
    }
    return $args;
}

function YearlyReset_run()
{
    page_header("Yearly Reset");
    page_footer();
}

?>