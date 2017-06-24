<?php

function colorpicker_getmoduleinfo()
{
    $info = [
        'name' => 'Color Picker',
        'author'=> 'Stephen Kise',
        'version' => '0.1',
        'category' => 'Administrative',
        'description' =>
            'Create colors on the fly!',
        'download' => 'nope',
    ];
    return $info;
}

function colorpicker_install()
{
    module_addhook('superuser');
    return true;
}

function colorpicker_uninstall()
{
    return true;
}

function colorpicker_dohook($hook, $args)
{
    switch ($hook) {
        case 'superuser':
            addnav("Editors");
            addnav("Color Creator", "runmodule.php?module=colorpicker&op=test");
            break;
    }
    return $args;
}

function colorpicker_run()
{
    page_header('Color Creator');
    $op = httpget('op');
    switch ($op) {
        case 'test':
            rawoutput(
                "<input type='color' name='colorpicker'>
                <input type='text' name='colorpicker' placeholder='Hex Code'><br>
                <div class='testcolors'>Your colored text will appear here.</div>"
            );
            addnav("Superuser Grotto", "superuser.php");
            break;
    }
    page_footer();
}
