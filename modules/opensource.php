<?php

function opensource_getmoduleinfo()
{
    $info = [
        'name' => 'Open Source',
        'author' => '`b`&Stephen Kise`b',
        'version' => '0.1b',
        'description' =>
            'Opening Xythen\'s source for those who wish to join in helping',
        'category' => 'Administrative',
        'download' => 'nope',
        'settings' => [
        ],
        'prefs' => [
        ]
    ];
    return $info;
}

function opensource_install()
{
    module_addhook('village');
}

function opensource_uninstall()
{}

function opensource_dohook($hook, $args)
{}

function opensource_run()
{}