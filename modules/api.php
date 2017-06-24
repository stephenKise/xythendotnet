<?php

function api_getmoduleinfo()
{
    $info = [
        'name' => 'Xythen\'s API',
        'author' => '`b`&Stephen Kise`b',
        'description' =>
            'An API handler for all of the JSON work we do here at Xythen.',
        'version' => '0.1b',
        'category' => 'Administrative',
        'override_forced_nav' => true,
        'allow_anonymous' => true,
    ];
    return $info;
}

function api_install()
{
    return true;
}

function api_uninstall()
{
    return true;
}

function api_run()
{
    global $output, $session;
    $modules = modulehook('api', [
        'modulename' => httpget('modulename'),
        'function' => 'test',
        'display' => 'JSON',
        'options' => httpallget(),
        'params' => '',
        'allow_anonymous' => false,
        'override_forced_nav' => true,
    ]);
    define('ALLOW_ANONYMOUS', $modules['allow_anonymous']);
    define('OVERRIDE_FORCED_NAVS', $modules['override_forced_navs']);
    //echo '<pre>';
    require_once("modules/{$modules['modulename']}.php");
    echo json_encode($modules['function']($modules['params']), JSON_PRETTY_PRINT);
    //echo '</pre>';
}
?>