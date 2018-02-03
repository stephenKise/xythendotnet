<?php

require_once('lib/DiceBag.php');
use LOTGD\DiceBag;

function diceRoll_getmoduleinfo(): array
{
    return [
        'name' => 'Dice Roll',
        'author' => 'Stephen Kise',
        'version' => '1.0.0',
        'category' => 'Quality of Life',
        'description' => 'Allows for dice rolling to be implemented in the commentary'
    ];
}

function diceRoll_install(): bool
{
    module_addhook('chat-intercept');
    module_addhook('chat-inserted');
    module_addhook('chat-format');
    module_addhook('api');
    return true;
}

function diceRoll_uninstall(): bool
{
    return true;
}

function diceRoll_dohook(string $hook, array $args): array
{
    switch ($hook) {
        case 'chat-intercept':
            $args['comment'] = stripslashes($args['comment']);
            if (!array_key_exists('edited', $args)) {
                $args['comment'] = preg_replace(
                    '/diceRoll\(\d+\)/',
                    'diceRoll(0)',
                    $args['comment']
                );
            }
            $args['comment'] = diceReplaceString($args['comment']);
            // check $args['originalComment'] - for each diceRoll(\d)
            // If it's not present in $args['comment'], render each diceRoll(\d+)
            // as diceRoll(0);
            break;
        case 'chat-format':
            $args['formattedComment'] = preg_replace(
                "/diceRoll\((\d+)\)/",
                '<div class="dice-roll colLtOrange">${1} </div>',
                $args['formattedComment']
            );
            break;
        case 'api':
            $args['diceRoll'] = [
                'dice' => [
                    'roll' => [
                        'Roll any amount of die with POST ($amount, $sides, $modifier).'
                    ]
                ]
            ];
            break;
    }
    return $args;
}

function diceReplaceString(string $string): string
{
    global $mysqli_resource;
    preg_match_all(
        "/\/(\d+)d(\d+)(\+\d+|\-\d+)?/",
        $string,
        $matches,
        PREG_SET_ORDER
    );
    foreach ($matches as $data) {
        if ($data[1] > 10 || $data[2] > 60) {
            continue;
        }
        $dice = new DiceBag($data[1], $data[2], $data[3]?:'+0');
        debug($xd);
        $replacement = str_replace("/", "\\/", $data[0]);
        $replacement = str_replace("-", "\\-", $replacement);
        $replacement = str_replace("+", "\\+", $replacement);
        $string = preg_replace(
            "/$replacement/",
            "diceRoll(" . $dice->roll()['total'] . ")",
            $string,
            1
        );
    }
    return mysqli_real_escape_string(
        $mysqli_resource,
        soap($string ?: '', true, true)
    );
}