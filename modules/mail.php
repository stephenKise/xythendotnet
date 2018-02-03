<?php

function mail_getmoduleinfo(): array
{
    return [
        'name' => 'New Mail',
        'author' => 'Stephen Kise',
        'version' => '0.0.2',
        'category' => 'Gameplay',
        'description' => 'Replaces the current mail system with a mature one.',
        'override_forced_nav' => true,
        'allowanonymous' => true,
        'settings' => [
            'length' => 'How long should replies be?, int| 2048',
            'post_master' => 'Acctid of the post master:, viewonly',
            'password' => 'Password of post master:, viewonly',
        ],
        'prefs' => [
            'Mail Preferences, title',
            'contacts' => 'Array of contacts saved:, viewonly| []',
            'blocked' => 'Array of people blocked:, viewonly| []',
            'user_offset' => 'How many responses should we display?, int| 10',
            'seen' => 'Array of seen mail:, viewonly| {}',
        ],
        'install' => [
            'mailfunctions' => [
                'function' => 'redirectToInbox',
            ],
            'bioinfo' => [
                'function' => 'mailContactLink',
            ],
        ]
    ];
}

function mail_install(): bool
{
    require_once('lib/tabledescriptor.php');
    $mail = db_prefix('mail');
    $accounts = db_prefix('accounts');
    db_query(
        "ALTER TABLE $mail 
        CHANGE sent sent DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP"
    );
    $sql = db_query(
        "SELECT acctid FROM $accounts
        WHERE name = '`^Post Master' LIMIT 1"
    );
    if (db_num_rows($sql) > 0) {
        $row = db_fetch_assoc($sql);
    }
    else {
        $password = md5(rand(0, 100).time());
        db_query(
            "INSERT INTO $accounts (name, login, password)
            VALUES ('`^Post Master', 'postmaster', '$password')"
        );
        $sql = db_query(
            "SELECT acctid FROM accounts
            WHERE name = '`^Post Master' LIMIT 1"
        );
        $row = db_fetch_assoc($sql);
    }
    synctable(
        'messagesreceived',
        [
            'id' => [
                'name' => 'id',
                'type' => 'int unsigned',
                'extra' => 'auto_increment'
            ],
            'acctid' => [
                'name' => 'acctid',
                'type' => 'mediumint unsigned'
            ],
            'groupid' => [
                'name' => 'groupid',
                'type' => 'mediumint unsigned'
            ],
            'posted' => [
                'name' => 'posted',
                'type' => 'datetime'
            ],
            'viewed' => [
                'name' => 'viewed',
                'type' => 'tinyint unsigned'
            ],
            'key-PRIMARY' => [
                'name' => 'PRIMARY',
                'type' => 'primary key',
                'unique' => '1',
                'columns' => 'id'
            ],
        ]
    );
    synctable(
        'messages',
        [
            'id' => [
                'name' => 'id',
                'type' => 'int unsigned',
                'extra' => 'auto_increment'
            ],
            'groupid' => [
                'name' => 'groupid',
                'type' => 'mediumint unsigned'
            ],
            'acctids' => [
                'name' => 'acctids',
                'type' => 'varchar(255)'
            ],
            'title' => [
                'name' => 'title',
                'type' => 'varchar(255)',
            ],
            'body' => [
                'name' => 'body',
                'type' => 'text',
            ],
            'author' => [
                'name' => 'author',
                'type' => 'mediumint unsigned'
            ],
            'posted' => [
                'name' => 'posted',
                'type' => 'datetime'
            ],
            'key-PRIMARY' => [
                'name' => 'PRIMARY',
                'type' => 'primary key',
                'unique' => '1',
                'columns' => 'id'
            ],
        ]
    );
    set_module_setting('post_master', $row['acctid']);
    set_module_setting('password', $password);
    module_addhook('mailfunctions');
    module_addhook('bioinfo');
    module_addhook('everyfooter-loggedin');
    return true;
}

function mail_uninstall(): bool
{
    $accounts = db_prefix('accounts');
    $settings = get_all_module_settings();
    db_query(
        "DELETE FROM $accounts WHERE name = '`^Post Master'"
    );
    return true;
}

function mail_dohook(string $hook, array $args): array
{
    switch ($hook) {
        case 'mailfunctions':
            return redirectToInbox($hook, $args);
            break;
        case 'bioinfo':
            //return mailContactLink($hook, $args);
            break;
        case 'everyfooter-loggedin':
            global $header, $footer, $session;
            $messagesReceived = db_prefix('messagesreceived');
            $sql = db_query(
                "SELECT count(id) AS unviewed FROM $messagesReceived
                WHERE acctid = {$session['user']['acctid']} AND viewed = 0"
            );
            $row = db_fetch_assoc($sql);
            if ($row['unviewed'] > 0) {
                $new = 'glow';
                $total += $row['unviewed'];
            }
            $mailLink = "<a href='mail.php' name='mailLink'
                onClick=\"" . popup("mail.php") . ";return false;\">
                <i class='fa fa-envelope fa-lg $new'></i>
                </a>";
            $header = str_replace('{mail}', $mailLink, $header);
            $footer = str_replace('{mail}', $mailLink, $footer);
            break;
    }
    return $args;
}

function redirectToInbox(string $hook, array $args): array
{
    global $SCRIPT_NAME;
    if ($SCRIPT_NAME == 'mail.php' || $hook == 'force') {
        header('Location: runmodule.php?module=mail&op=inbox');
    }
    if (httpget('op') == 'view') {
        $id = httpget('id');
        $args['Invite'] = "runmodule.php?module=mail&op=addUser&id=$id";
        $args['Leave'] = "runmodule.php?module=mail&op=leave&id=$id";
    }
    return $args;
}

function mailContactLink(string $hook, array $args): array
{
    global $session;
    if ($args['acctid'] == $session['user']['acctid']) {
        return $args;
    }
    $contacts = array_keys(json_decode(get_module_pref('contacts'), true));
    $blocked = array_keys(json_decode(get_module_pref('blocked'), true));
    addnav('Return');
    addnav('Contacts');
    if (!in_array($args['acctid'], $contacts) &&
        !in_array($args['acctid'], $blocked)) {
        addnav(
            'Buddy User',
            'runmodule.php?module=mail&op=buddy&id=' . $args['acctid']
        );
    }
    if (in_array($args['acctid'], $blocked)) {
        addnav(
            'Unblock User',
            'runmodule.php?module=mail&op=unblock&id=' . $args['acctid']
        );
    }
    else {
        addnav(
            'Block User',
            'runmodule.php?module=mail&op=block&id=' . $args['acctid']
        );
    }
    addnav('Superuser');
    return $args;
}

function mail_run(): bool
{
    global $session;
    $op = httpget('op');
    if (!$session['user']['loggedin']) {
        require_once('lib/redirect.php');
        $session['message'] = 'You are not logged in!';
        redirect('home.php');
    }
    popup_header('Mail');
    displayMailHeader();
    $function = "mail" . ucfirst($op);
    $function();
    popup_footer();
    return false;
}

function sendMail(int $groupid, string $message, int $sender): bool
{
    require_once('lib/sanitize.php');
    $messages = db_prefix('messages');
    $messagesReceived = db_prefix('messagesreceived');
    $accounts = db_prefix('accounts');
    $message = addslashes(sanitizeHTML($message));
    if ($groupid < 1) {
        return false;
    }
    $sql = db_query(
        "SELECT acctids, title FROM $messages WHERE groupid = $groupid
        ORDER BY id+0 DESC LIMIT 1"
    );
    if (db_num_rows($sql) != 1) {
        $row = [
            'acctids' => json_encode([$sender, (int) httpPostClean('to')]),
            'title' => httpPostClean('subject')
        ];
    }
    else {
        $row = db_fetch_assoc($sql);
    }
    db_query(
        "INSERT INTO $messages (groupid, acctids, title, body, author, posted)
        VALUES ($groupid, '{$row['acctids']}', '{$row['title']}', '$message',
        $sender, NOW())"
    );
    foreach (json_decode($row['acctids']) as $user) {
        $user = (int) $user;
        db_query(
            "INSERT INTO $messagesReceived (acctid, groupid, posted, viewed)
            VALUES ($user, $groupid, NOW(), 0)"
        );
    }
    return true;
}

function displayMailHeader(): bool
{
    rawoutput(
        "<link href='modules/css/mail.css' rel='stylesheet' type='text/css'>
        <script src='modules/js/mail.js'></script>"
    );
    $mailFunctions = modulehook(
        'mailfunctions',
        [
            'Inbox' => 'runmodule.php?module=mail&op=inbox',
            'Compose' => 'runmodule.php?module=mail&op=compose',
            //'Contacts' => 'runmodule.php?module=mail&op=contacts'
        ]
    );
    rawoutput(
        "<div class='mail-header'>
            <ul>");
    foreach ($mailFunctions as $text => $link) {
        rawoutput("<li><a href='$link'>$text</a></li>");
    }
    rawoutput(
        "   </ul>
        </div>"
    );
    return false;
}

function canViewMessage(int $id): bool
{
    global $session;
    $messagesReceived = db_prefix('messagesreceived');
    $user = (int) $session['user']['acctid'];
    if (!is_numeric($id)) {
        return false;
    }
    $sql = db_query(
        "SELECT id FROM $messagesReceived
        WHERE acctid = $user AND groupid = $id
        LIMIT 0, 1"
    );
    if (db_num_rows($sql) < 1) {
        return false;
    }
    db_free_result($sql);
    return true;
}

function mailBlock(): bool
{
    require_once('lib/redirect.php');
    global $session;
    $id = httpget('id');
    $contacts = json_decode(get_module_pref('contacts'), true);
    $blocked = json_decode(get_module_pref('blocked'), true);
    $blocked[$id] = date('Y-m-d H:i:s', time());
    set_module_pref('blocked', json_encode($blocked));
    unset($contacts[$id]);
    set_module_pref('contacts', json_encode($contacts));
    redirect($session['user']['restorepage']);
    return false;
}

function mailUnblock(): bool
{
    require_once('lib/redirect.php');
    global $session;
    $id = httpget('id');
    $blocked = json_decode(get_module_pref('blocked'), true);
    unset($blocked[$id]);
    set_module_pref('blocked', json_encode($blocked));
    redirect($session['user']['restorepage']);
    return false;
}

function mailBuddy(): bool
{
    require_once('lib/redirect.php');
    global $session;
    $id = httpget('id');
    $contacts = json_decode(get_module_pref('contacts'), true);
    $contacts[$id] = date('Y-m-d H:i:s', time());
    set_module_pref('contacts', json_encode($contacts));
    redirect($session['user']['restorepage']);
    return false;
}

function mailInbox(): bool
{
    global $session;
    $messages = db_prefix('messages');
    $messagesReceived = db_prefix('messagesreceived');
    $accounts = db_prefix('accounts');
    $user = (int) $session['user']['acctid'];
    rawoutput(
        "<div class='mail-inbox'>
            <h1>Current Messages</h1>
            <form action='runmodule.php?module=mail&op=del'>
            </form>
            <table class='mail-list-messages'>
                <thead>
                    <th>Subject</th>
                    <th>Last Sender</th>
                    <th>Received</th>
                </thead>");

    $sql = db_query(
        "SELECT * FROM 
        (SELECT m.title, m.posted, a.name, mr.viewed, m.groupid
        FROM $messages AS m
        LEFT JOIN $accounts AS a ON a.acctid = m.author
        RIGHT JOIN $messagesReceived AS mr ON mr.groupid = m.groupid
        WHERE mr.acctid = {$user} GROUP BY m.id DESC
        )
        AS tmp GROUP BY groupid ORDER BY posted DESC"
    );
    while ($row = db_fetch_assoc($sql)) {
        rawoutput(
            sprintf(
            "<tr name='messages' data-originator='%s'>
                <td>
                    <span class='mail-message-subject'>%s</span>
                </td>
                <td>
                    <span class='mail-message-last-responder'>%s%s</span>
                </td>
                <td>
                    <span class='mail-message-received'>%s</span>
                </td>
            </tr>",
            $row['groupid'],
            trim($row['title'])?:'No Subject',
            appoencode("&#x270E;", true),
            appoencode($row['name']),
            $row['posted']
            )
        );
    }
    rawoutput(
        "   </table>
        </div>"
    );
    return false;
}

// USE ORIGINATOR TO GROUP MESSAGES. WHEN COMPOSING A NEW MESSAGE, CREATE A NEW ORIGINATOR ID

function mailView(): bool
{
    global $session;
    $messages = db_prefix('messages');
    $accounts = db_prefix('accounts');
    $messagesReceived = db_prefix('messagesreceived');
    $id = (int) httpget('id');
    $user = (int) $session['user']['acctid'];
    $userOffset = (int) get_module_pref('user_offset');
    $usersList = "`@In conversation: `^";
    $offset = (int) httpget('page');
    $offsetString = "LIMIT " . $offset * $userOffset .
        ", " . ($offset + 1) * $userOffset;
    $lastMessage = [];
    if (!canViewMessage($id)) {
        debuglog(
            sprintf(
                "tried to view mail with origin of id %s but was not allowed",
                $id
            )
        );
        redirectToInbox('force', []);
        return false;
    }
    $usersList = appoencode(trim($usersList, ', '));
    $sql = db_query(
        "SELECT m.body, m.title, m.posted, m.author, m.acctids, a.name FROM $messages AS m
        RIGHT JOIN $accounts AS a ON m.author = a.acctid
        WHERE m.groupid = $id
        ORDER BY m.id+0 DESC $offsetString"
    );
    $sortedMessages = [];
    while ($row = db_fetch_assoc($sql)) {
        $title = trim($row['title'])?:'No Subject';
        $row['body'] = stripslashes($row['body']);
        $row['body'] = nl2br($row['body']);
        array_push($sortedMessages, $row);
        if (empty($lastMessage)) {
            $lastMessage = $row;
        }
    }
    foreach (json_decode($lastMessage['acctids']) AS $acct) {
        $acct = (int) $acct;
        $sql = db_query("SELECT name FROM $accounts WHERE acctid = $acct");
        $row = db_fetch_assoc($sql);
        $usersList .= "`^{$row['name']}`^, ";
    }
    $usersList = appoencode(trim($usersList, ', '), true);
    /*$sql = db_query(
        "SELECT a.name FROM $accounts a
        RIGHT JOIN $messages AS m ON mo.acctid = a.acctid
        WHERE mo.origin = $id
        GROUP BY a.acctid"
    );
    while ($row = db_fetch_assoc($sql)) {
        $usersList .= "`^{$row['name']}`^, ";
    }*/
    $sortedMessages = array_reverse($sortedMessages);
    rawoutput(
        "<div class='mail-inbox'>
            <h1 id='message-subject'>{$title}</h1>
            <form action='runmodule.php?module=mail&op=title&id={$id}'
                class='message-title-edit' id='message-subject-form'
                method='POST'>
                <input type='hidden' name='id' value='$id'>
                <input name='message-subject-edit'
                    value='{$title}'>
                <input type='submit' value='Submit'>
            </form>"
    );
    foreach ($sortedMessages as $number => $row) {
        $class = 'mail-reply-from-user';
        if ($session['user']['acctid'] == $row['author']) {
            $class = 'mail-reply-from-me';
        }
        if ($row['author'] < 1) {
            $class = 'mail-reply-from-system';
        }
        output(
            "<div class='mail-message-container'>
                <div class='$class'>
                    <div class='message-details'>
                        {$row['name']}
                    </div>
                    {$row['body']}
                </div>
            </div>",
            true
        );
    }
    rawoutput(
        "
            <div class='mail-users-in-convo'>{$usersList}</div><br/>
            <form action='runmodule.php?module=mail&op=reply&id={$id}'
                method='POST'>
                <div class='message-reply' id='message-reply'>
                    <textarea name='reply' id='message-reply-form'
                        class='input'></textarea>
                    <input type='submit' value=' Send'>
                    <input type='hidden' name='id' value='{$id}'>
                    <input type='hidden' name='to' value='{$row['author']}'>
                    <input type='hidden' name='subject'
                        value='{$title}'>
                </div>
            </form>
            <a name='last'></a>
        </div>"
    );
    db_query(
        "UPDATE $messagesReceived SET viewed = 1
        WHERE acctid = $user AND groupid = $id"
    );
    return false;
}

function mailReply(): bool
{
    global $session;
    $post = httpallpost();
    sendMail($post['id'], $post['reply'], $session['user']['acctid']);
    header("Location: runmodule.php?module=mail&op=view&id={$post['id']}#last");
    return false;
}

function mailCompose(): bool
{
    $accounts = db_prefix('accounts');
    $to = httpPostClean('message-to');
    $search = implode('%', str_split($to));
    $timeOut = date(
        'Y-m-d H:i:s',
        strtotime('-' . getsetting('LOGINTIMEOUT', 900) . ' seconds')
    );
    $extraSql = "loggedin = 1 AND laston > '$timeOut'";
    if ($to != '') {
        $extraSql = "(name LIKE '%$search%' OR login LIKE '%$search%')";
    }

    output("<div class='mail-inbox'>`@", true);
    rawoutput(
        "<div class='message-to' id='message-to'>
        Who would you like to message?</span><br>
            <form action='runmodule.php?module=mail&op=compose'
                method='POST'>
                <input type='text' name='message-to' id='message-to' value='{$to}'>
                <input type='submit' value='Search'>
            </form>
            <table class='compose-list-users'>
            <thead>
                <th>User</th>
            </thead>"
    );
    $sql = db_query(
        "SELECT name, login, loggedin, acctid FROM $accounts
        WHERE $extraSql
        ORDER BY loggedin DESC LIMIT 0, 10"
    );
    while ($row = db_fetch_assoc($sql)) {
        output(
            sprintf(
                "<tr name='mail-message-users' data-acctid='%s' data-name=\"%s\">
                    <td>`^%s `#%s</td>
                </tr>",
                $row['acctid'],
                appoencode($row['name']),
                $row['name'],
                $row['loggedin'] ? "`#(online)" : ""
            ),
            true
        );
    }
    rawoutput(
        "       </table>
            </form>
        </div>"
    );
    rawoutput(
        "<form action='runmodule.php?module=mail&op=newMessage' id='new-message'
                class='new-message' method='POST'>
            <input type='text' name='subject' id='subject'
                placeholder='Message Subject' required>
            <div class='message-reply' id='message-reply'>
                <input type='hidden' name='to' id='to' value='{$row['msgfrom']}'>
                <textarea name='reply' id='message-reply-form'
                    class='input'></textarea>
                <input type='submit' value=' Send'>
            </div>
        </form>
        </div>"
    );
    return false;
}

function mailTitle(): bool
{
    global $session;
    $messages = db_prefix('messages');
    $title = httpPostClean('message-subject-edit');
    $id = httpPostClean('id');
    db_query("UPDATE $messages SET title = '$title' WHERE groupid = $id");
    header("Location: runmodule.php?module=mail&op=view&id=$id");
    return false;
}

function mailAddUser(): bool
{
    global $session;
    if (!canViewMessage((int) httpget('id'))) {
        header("Location: mail.php");
        exit;
    }
    $id = httpget('id');
    $accounts = db_prefix('accounts');
    $to = httpPostClean('message-to');
    $search = implode('%', str_split($to));
    $timeOut = date(
        'Y-m-d H:i:s',
        strtotime('-' . getsetting('LOGINTIMEOUT', 900) . ' seconds')
    );
    $extraSql = "loggedin = 1 AND laston > '$timeOut'";
    if ($to != '') {
        $extraSql = "(name LIKE '%$search%' OR login LIKE '%$search%')";
    }

    output("<div class='mail-inbox'>`@", true);
    rawoutput(
        "<div class='message-to' id='message-to'>
        Who would you like to add to this message?</span><br>
            <form action='runmodule.php?module=mail&op=addUser&id=$id'
                method='POST'>
                <input type='text' name='message-to' id='message-to' value='{$to}'>
                <input type='submit' value='Search'>
            </form>
            <table>
            <thead>
                <th>User</th>
            </thead>"
    );
    $sql = db_query(
        "SELECT name, login, loggedin, acctid FROM $accounts
        WHERE $extraSql
        ORDER BY loggedin DESC LIMIT 0, 10"
    );
    while ($row = db_fetch_assoc($sql)) {
        output(
            sprintf(
                "<tr>
                    <td>
                    <a href='runmodule.php?module=mail&op=invite&user=%s&id=$id'>%s</a>
                    </td>
                </tr>",
                $row['acctid'],
                appoencode($row['name'])
            ),
            true
        );
    }
    rawoutput(
        "       </table>
            </form>
        </div>"
    );
    return false;
}

function mailInvite(): bool
{
    global $session;
    $id = (int) httpget('id');
    if (!canViewMessage($id)) {
        header("Location: mail.php");
        exit;
    }
    $messages = db_prefix('messages');
    $messagesReceived = db_prefix('messagesreceived');
    $user = (int) httpget('user');
    $acctid = (int) $session['user']['acctid'];
    $author = (int) get_module_setting('post_master');
    $body = "`Q{$session['user']['name']}`@ has invited a user.";
    $sql = db_query(
        "SELECT acctids, title FROM $messages WHERE groupid = $id
        ORDER BY id+0 DESC LIMIT 1"
    );
    $row = db_fetch_assoc($sql);
    $row['acctids'] = json_decode($row['acctids']);
    if (in_array($user, $row['acctids'])) {
        header("Location: runmodule.php?module=mail&op=view&id=$id");
        return false;
    }
    array_push($row['acctids'], $user);
    foreach ($row['acctids'] as $acct) {
        db_query(
            "INSERT INTO $messagesReceived (acctid, groupid, posted, viewed)
            VALUES ($acct, $id, NOW(), 0)"
        );
    }
    $row['acctids'] = json_encode($row['acctids']);
    db_query(
        "INSERT INTO $messages (groupid, acctids, title, body, author, posted)
        VALUES ($id, '{$row['acctids']}', '{$row['title']}', '$body', $author,
        NOW())"
    );
    header("Location: runmodule.php?module=mail&op=view&id=$id");
    return false;
}

function mailLeave(): bool
{
    global $session;
    $id = (int) httpget('id');
    if (!canViewMessage($id)) {
        header('Location: mail.php');
        exit;
    }
    $messagesReceived = db_prefix('messagesreceived');
    $messages = db_prefix('messages');
    $accounts = db_prefix('accounts');
    $body = "`Q{$session['user']['name']} `@left the conversation.";
    $postMaster = (int) get_module_setting('post_master');
    db_query(
        "UPDATE $messagesReceived SET acctid = $postMaster
        WHERE acctid = {$session['user']['acctid']} AND groupid = $id"
    );
    $sql = db_query(
        "SELECT acctids, title FROM $messages
        WHERE groupid = $id ORDER BY id+0 DESC LIMIT 1"
    );
    $row = db_fetch_assoc($sql);
    $row['acctids'] = json_decode($row['acctids']);
    $acctids = [];
    /* ARRAY PUSH TO KEEP DATA CONSISTENT */
    foreach ($row['acctids'] as $user) {
        if ($user != $session['user']['acctid']) {
            array_push($acctids, $user);
            db_query(
                "INSERT INTO $messagesReceived (groupid, acctid, posted, viewed)
                VALUES ($id, $user, NOW(), 0)"
            );
            continue;
        }
    }
    $row['acctids'] = json_encode($acctids);
    debug($row);
    db_query(
        "INSERT INTO $messages (groupid, acctids, title, body, author, posted)
        VALUES($id, '{$row['acctids']}', '{$row['title']}', '$body',
        $postMaster, NOW())"
    );
    header('Location: mail.php');
    return false;
}

function mailNewMessage(): bool
{
    global $session;
    $target = (int) httpPostClean('to');
    $body = httpPostClean('reply');
    $user = (int) $session['user']['acctid'];
    $messages = db_prefix('messages');
    $messagesReceived = db_prefix('messagesreceived');
    $sql = db_query("SELECT MAX(groupid) AS max FROM $messages LIMIT 1");
    $row = db_fetch_assoc($sql);
    $groupID = (int) $row['max'] + 1;
    db_query(
        "INSERT INTO $messagesReceived (acctid, groupid, posted, viewed)
        VALUES ($target, $groupID, NOW(), 0)"
    );
    sendMail($row['max'] + 1, $body, $user);
    header("Location: runmodule.php?module=mail&op=inbox");
    return false;
}
