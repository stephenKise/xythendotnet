<?php

function notepad_getmoduleinfo(): array
{
    return [
        'name' => 'Notepad',
        'version' => '1.1',
        'author' => 'Boris735, Stephen Kise',
        'category' => 'General',
        'description' => 'Per-player editable notes in a new tab.',
        'override_forced_nav' => true,
        'prefs' => [
            'Notepad preferences, title',
            'notetext' => 'Text of user\'s notepad, text|',
        ],
    ];
}

function notepad_install(): bool
{
    module_addhook('charstats');
    return true;
}

function notepad_uninstall(): bool
{
    return true;
}

function notepad_dohook(string $hookName, array $args): array
{
    $link = "<a href='runmodule.php?module=notepad&op=read' target='_blank'>" .
        "Open</a>";
    addcharstat('Other');
    addcharstat('Notepad', $link);
    addnav('','runmodule.php?module=notepad&op=read');
    return $args;
}

function notepad_emptytext(): string
{
    return "The notepad is blank! Maybe you should document your adventures!";
}

function notepad_run(): bool
{
    popup_header('Personal Notepad');
    $op = httpget('op');
    $notes = get_module_pref('notetext');
    $notes = stripslashes($notes);
    $notes = str_replace("\n", "`n", $notes);
    $notes = htmlentities(
        $notes,
        ENT_COMPAT,
        getsetting('charset', 'UTF-8')
    );

    switch ($op) {
        case "read":
            if (array_key_exists('saved', httpallget())) {
                output("`c`b`QNotes saved!`b`c");
            }
            rawoutput(
                "<a href='runmodule.php?module=notepad&op=edit'>Edit Notes</a>"
            );
            if ($notes == '') {
                $notes = notepad_emptytext();
            }
            output("`n`n%s", $notes);
            break;
        case "save":
            $newNotes = httppost('notes');
            $newNotes = stripslashes($newNotes);
            $newNotes = str_replace("`n", "\n", $newNotes);
            if ($newNotes != $notes) {
                set_module_pref("notetext", $newNotes);
                header('Location: runmodule.php?module=notepad&op=read&saved');
            }
        case "edit":
            rawoutput(
                "<a href='runmodule.php?module=notepad&op=read'>View Notes</a>"
            );
            output("`\$(does `inot`i save)`n`n`c`0");
            rawoutput(
                "<form action='runmodule.php?module=notepad&op=save'
                    method='POST'>
                <textarea class='input' name='notes' rows='15'
                    style='width: 90%'>$notes</textarea><br />
                <input type='submit' class='button' value='Save'
                    style='float: right'>
                </form>"
            );
            output_notl("`c");
            break;
    }
    popup_footer();
}
