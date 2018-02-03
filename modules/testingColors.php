<?php

function testingColors_getmoduleinfo(): array
{
    return [
        'name' => 'Color Test',
        'author' => 'Stephen Kise',
        'version' => '0.0.1',
        'category' => 'Gameplay',
        'download' => 'nope',
        'description' => 'Testing the global colors.'
    ];
}

function testingColors_install(): bool
{
    module_addhook('everyfooter-loggedin');
    module_addhook('everhit');
    module_addhook('everyheader');
    module_addhook('}collapse');
    return true;
}

function testingColors_uninstall(): bool
{
    return true;
}

function testingColors_dohook(string $hook, array $args): array
{
    global $colors;
    debug($hook);
    debug($colors);
    return $args;
}
