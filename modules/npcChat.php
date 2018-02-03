<?php

function npcChat_getmoduleinfo(): array
{
    return [
        'name' => 'NPC Chat',
        'author' => 'Stephen Kise',
        'version' => '1.0.0',
        'category' => 'Gameplay',
        'description' => 'Adds NPC messages to the chat',
        'prefs' => [
            'commentids' => 'List of ids, viewonly| []',
        ]
    ];
}

function npcChat_install(): bool
{
    module_addhook('chat-intercept');
    module_addhook('chat-inserted');;
    module_addhook('chat-delete');
    module_addhook('chat-format');
    return true;
}

function npcChat_uninstall(): bool
{
    return true;
}

function npcChat_dohook(string $hook, array $args): array
{
    $commentIDs = json_decode(get_module_pref('commentids'));
    switch ($hook) {
        case 'chat-intercept':
            if (strpos($args['comment'], '/npc') !== false) {
                $args['acctid'] = 0;
                $args['comment'] = str_replace('/npc', ':', $args['comment']);
            }
            break;
        case 'chat-inserted':
            if ($args['acctid'] != 0) {
                break;
            }
            array_push($commentIDs, (int) $args['lastID']);
            set_module_pref('commentids', json_encode($commentIDs));
            break;
        case 'chat-delete':
            if (!in_array($args['commentid'], $commentIDs)) {
                break;
            }
            $args['skipPermission'] = true;
            break;
        case 'chat-format':
            require_once('modules/jQueryCommentary.php');
            if (in_array($args['id'], $commentIDs) && $args['author'] == 0) {
                $edit = appoencode(
                    "<a class='removeMessage'
                        onclick='removeChatMessage({$args['id']});'>
                        `^ &#x274C;
                    </a>
                    <a class='editMessage'
                        onclick='editMessageOf({$args['id']});'>
                        `^&#x1F4DD;
                    </a>",
                    true
                );
                $args['comment'] = appoencode($args['rawComment'], true);
                $formattedComment = formatComment(
                    [(int) $args['author'],
                    $args['name'],
                    $args['rawComment']]
                );
                $formattedComment = "<div class='chatMessage' data-author='{$args['author']}'
                    data-commentid='{$args['id']}'>
                        {$args['comment']}
                    </div>";
                $args['formattedComment'] = "<div class='jQuery-message'>
                    $edit $formattedComment
                </div>";
            }
            break;
    }
    return $args;
}