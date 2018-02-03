<?php

function colorTestBox_getmoduleinfo(): array
{
    return [
        'name' => 'Color Test Box',
        'author' => 'Stephen Kise',
        'version' => '0.0.1',
        'category' => 'Quality of Life',
        'description' => 'Adds a means of testing all of the color codes.',
        'override_forced_nav' => true,
        'settings' => [
            'description' => 'Description to include:, textarea| Hi!',
            'colors' => 'List of colors:, text| []',
        ]
    ];
}

function colorTestBox_install(): bool
{
    module_addhook('village');
    module_addhook('lodge');
    return true;
}

function colorTestBox_uninstall(): bool
{
    return true;
}

function colorTestBox_dohook(string $hook, array $args): array
{
    addnav($args['othernav']?:'Other');
    addnav('Color Test Box', 'runmodule.php?module=colorTestBox', false, true);
    blocknav('colortestbox.html');
    return $args;
}

function colorTestBox_run(): void
{
    require_once('lib/forms.php');
    popup_header('');
    $description = get_module_setting('description');
    $colors = json_decode(get_module_setting('colors'));
    $i = 0;
    output($description . '`n`n', true);
    foreach ($colors as $key => $class) {
        $i++;
        $coloredKey = appoencode("`$key$key");
        rawoutput("<a class='test-color $class' data-key='$key' name='color-$key'>`$coloredKey</a> ");
    }
    output("`n`n");
    previewfield(
        'colors',
        '`^',
        false,
        false,
        [
            'type' => 'textarea',
            'class' => 'input',
            'cols' => 60,
            'rows' => 9
        ]
    );

    rawoutput(
        "
        <style>
        .test-color {
            padding: 5px;
            line-height: 2em;
        }
        </style>
        <script type='text/javascript'>
        var testBox = $('textarea[name=colors]');
        $('a[name^=color]').click(function (e) {
            e.preventDefault();
            var cursorPos = testBox.prop('selectionStart');
            var v = testBox.val();
            var textBefore = v.substring(0,  cursorPos);
            var textAfter  = v.substring(cursorPos, v.length);
            testBox.val(textBefore + '`' + $(this).data('key') + textAfter);
            testBox.focus();
            previewtextcolors($('#inputcolors').val(), 5000);
        });
        </script>"
    );
    popup_footer();
    return;
}