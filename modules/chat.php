<?php

function chat_getmoduleinfo()
{
    $info = [
        'name' => 'Chat System',
        'author' => '`b`&Stephen Kise`b',
        'version' => '0.1b',
        'description' =>
            'The chat and mail system for Xythen.',
        'category' => 'Commentary',
        'prefs' => [
            'user_ooc' => 'Display the OOC chat?, bool| 1',
            'user_sound' => 'Do you wish to enable the sounds?, bool| 1',
            'user_'
        ],
        'settings' => [
            'typing' => 'JSON of those typing:, viewonly| []',
            'amount' => 'Amount of messages to show:, int| 20',
            ''
        ],
    ];
}

function chat_install()
{
    module_addhook();
}

function chat_uninstall()
{}

function chat_dohook($hook, $args)
{
}

function chat_run()
{}
?>