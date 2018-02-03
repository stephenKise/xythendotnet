<?php

function replaceHeaders_getmoduleinfo(): array
{
    return replaceHeadersInfo();
}

function replaceHeadersInfo(): array
{
    return [
        'name' => 'Replace Header Links',
        'author' => 'Stephen Kise',
        'version' => '0.0.1',
        'category' => 'Quality of Life',
        'description' =>
            'Replaces the normal header links with more flavored ones.'
    ];
}

function replaceHeaders_install(): bool
{
    module_addhook('everyfooter');
    return true;
}
function replaceHeaders_uninstall(): bool
{
    return true;
}

function replaceHeaders_dohook(string $hook, array $args): array
{
    global $session, $header, $footer;
    $pet = 'petition.php';
    $motd = 'motd.php';
    $newMotd = ($session['needtoviewmotd']) ? 'alert' : '';
    $question= "<i class='fa fa-flag fa-lg alert'></i>";
    $newspaper = "<i class='fa fa-rss fa-lg $newMotd'></i>";
    $petitionLink =
        "<a href='$pet' onClick=\"" . popup('petition.php') . ";return false;\">
            $question 
        </a>&nbsp;";
    $motdLink =
        "<a href='$motd' onClick=\"" . popup('motd.php') . ";return false;\">
            $newspaper
        </a>&nbsp;";
    $header = str_replace('{petition}', $petitionLink, $header);
    $footer = str_replace('{petition}', $petitionLink, $footer);
    $header = str_replace('{motd}', $motdLink, $header);
    $footer = str_replace('{motd}', $motdLink, $footer);
    return $args;
}