<?php

function motdScheduler_getmoduleinfo(): array
{
    return [
        'name' => 'MotD Scheduler',
        'author' => 'Stephen Kise',
        'version' => '0.0.1',
        'category' => 'Administrative',
        'description' => 'Adds the ability to schedule multiple MotDs.',
    ];
}

function motdScheduler_install(): bool
{
    require_once('lib/tabledescriptor.php');
    $scheduledMotds = [
        'id' => [
            'name' => 'id',
            'type' => 'int(11) unsigned',
            'extra' => 'auto_increment'
        ],
        'motdtitle' => [
            'name' => 'motdtitle',
            'type' => 'varchar(255)'
        ],
        'motdbody' => [
            'name' => 'motdbody',
            'type' => 'text'
        ],
        'motddate' => [
            'name' => 'motddate',
            'type' => 'datetime'
        ],
        'motdauthor' => [
            'name' => 'motdauthor',
            'type' => 'int(11) unsigned',
            'default' => 0,

        ],
        'key-PRIMARY' => [
            'name' => 'PRIMARY',
            'type' => 'primary key',
            'unique' => '1',
            'columns' => 'id'
        ]
    ];
    module_addhook('css');
    module_addhook('superuser');
    module_addhook('newday-intercept');
    synctable(db_prefix('scheduled_motds'), $scheduledMotds);
    return true;
}

function motdScheduler_uninstall(): bool
{
    $scheduledMotds = db_prefix('scheduled_motds');
    db_query("DROP TABLE $scheduledMotds;");
    output("`^Removed `#scheduled_motds`^ from the database.`n");
    return true;
}

function motdScheduler_dohook(string $hook, array $args): array
{
    global $session;
    switch ($hook) {
        case 'css':
            $args['motdScheduler'] = 'motdScheduler';
            break;
        case 'superuser':
            if (!$session['user']['superuser'] & SU_POST_MOTD) {
                break;
            }
            addnav('Actions');
            addnav('MotD Scheduler', 'runmodule.php?module=motdScheduler&op=enter');
            break;
        case 'newday-intercept':
            if ($session['user']['superuser'] & SU_POST_MOTD) {
                $scheduledMotds = db_prefix('scheduled_motds');
                $motd = db_prefix('motd');
                $sql = db_query(
                    "SELECT * FROM $scheduledMotds ORDER BY motddate ASC LIMIT 1"
                );
                $row = db_fetch_assoc($sql);
                $id = (int) $row['id'];
                if (strtotime('now') > strtotime($row['motddate']) && db_num_rows($sql) > 0) {
                    db_query(
                        "INSERT INTO $motd
                        (motdtitle, motdbody, motddate, motdauthor, motdtype)
                        VALUES
                        ('{$row['motdtitle']}', '{$row['motdbody']}',
                        CURRENT_TIMESTAMP, '{$row['motdauthor']}', 0);"
                    );
                    db_query("DELETE FROM $scheduledMotds WHERE id = $id");
                }
                db_free_result($sql);
            }
            break;
    }
    return $args;
}

function motdScheduler_run(): bool
{
    $scheduledMotds = db_prefix('scheduled_motds');
    page_header('MotD Scheduler');
    switch (httpget('op')) {
        case 'enter':
            addnav('Superuser Grotto', 'superuser.php');
            addnav(
                'Schedule MotD',
                'runmodule.php?module=motdScheduler&op=create'
            );
            $sql = db_query(
                "SELECT * FROM $scheduledMotds ORDER BY motddate ASC LIMIT 0, 25"
            );
            rawoutput(
                "<table class='motd-scheduler'>
                    <tr>
                        <th colspan='2'>Title</th>
                        <th>Description</th>
                    </tr>"
            );
            while ($row = db_fetch_assoc($sql)) {
                if (strlen($row['motdbody']) > 255) {
                    $row['motdbody'] = substr($row['motdbody'], 0, 255) . "[...]";
                }
                output(
                    "<tr>
                        <td>
                        `2[<a href='runmodule.php?module=motdScheduler&op=edit&id=%s'>Edit</a>`2]
                        </td>
                        <td>`Q%s`@(%s)</td>
                        <td>`^%s</td>
                    </tr>",
                    $row['id'],
                    $row['motdtitle'],
                    date('m/d', strtotime($row['motddate'])),
                    $row['motdbody'],
                    true
                );
                addnav(
                    '',
                    "runmodule.php?module=motdScheduler&op=edit&id={$row['id']}"
                );
            }
            rawoutput("</table>");
            break;
        case 'create':
            $datetime = date('Y-m-d', strtotime('next friday'));
             output(
                "`@`bScheduled MotDs`b`n
                `^You can schedule a message to be automatically placed in your name,
                at a scheduled date. After you schedule a message, the MotD can be edited
                by any staff who have MotD access, and visible by all. It would be best to
                check the MotD on the day before the message is posted.`n`n"
            );
            rawoutput(
                "<form action='runmodule.php?module=motdScheduler&op=save' method='POST'>
                    <input type='text' size='20' name='motdtitle' placeholder='Title'>
                    <input type='date' name='motddate' value='$datetime'>
                    <input type='submit'value='Save'><br/>
                    <textarea class='input' name='motdbody' rows='10' cols='60'></textarea>
                </form>"
            );
            addnav('', 'runmodule.php?module=motdScheduler&op=save');
            addnav('Superuser Grotto', 'superuser.php');
            addnav(
                'Go back',
                'runmodule.php?module=motdScheduler&op=enter'
            );
            break;
        case 'edit':
            $id = (int) httpget('id');
            $sql = db_query("SELECT * FROM $scheduledMotds WHERE id = $id LIMIT 1");
            $row = db_fetch_assoc($sql);
            $datetime = date('Y-m-d', strtotime($row['motddate']));
             output(
                "`@`bEdting a MotD`b`n
                `^Please be sure not to overwrite any work done by someone else!
                Communicate that you are going to be working on the MotD, so there
                is no issue with saving. If you need to stall a scheduled MotD,
                move the post time to a further date.`n`n"
            );
            rawoutput(
                sprintf_translate(
                    "<form action='runmodule.php?module=motdScheduler&op=update'
                    method='POST'>
                    <input type='hidden' name='id' value='%s'>
                    <input type='text' size='20' name='motdtitle' value='%s'>
                    <input type='date' name='motddate' value='%s'>
                    <input type='submit'value='Save'><br/>
                    <textarea class='input' name='motdbody' rows='10' cols='60'
                        >%s</textarea>
                    </form>",
                    $id,
                    $row['motdtitle'],
                    $datetime,
                    $row['motdbody']
                )
            );
            addnav('', 'runmodule.php?module=motdScheduler&op=update');
            addnav('Superuser Grotto', 'superuser.php');
            addnav(
                'Go back',
                'runmodule.php?module=motdScheduler&op=enter'
            );
            break;
        case 'save':
            require_once('lib/redirect.php');
            global $session;
            $post = httpAllPostClean();
            addnav('Superuser Grotto', 'superuser.php');
            addnav(
                'Go back',
                'runmodule.php?module=motdScheduler&op=enter'
            );
            db_query("INSERT INTO $scheduledMotds
                (motdtitle, motdbody, motddate, motdauthor)
                VALUES
                ('{$post['motdtitle']}', '{$post['motdbody']}',
                '{$post['motddate']}', '{$session['user']['acctid']}');");
            output(db_error());
            redirect('runmodule.php?module=motdScheduler&op=enter');
            break;
        case 'update':
            require_once('lib/redirect.php');
            $post = httpAllPostClean();
            $id = (int) $post['id'];
            addnav('Superuser Grotto', 'superuser.php');
            addnav(
                'Go back',
                'runmodule.php?module=motdScheduler&op=enter'
            );
            db_query("UPDATE $scheduledMotds SET motdtitle = '{$post['motdtitle']}',
                motdbody = '{$post['motdbody']}', motddate = '{$post['motddate']}'
                WHERE id = $id");
            output(db_error());
            redirect('runmodule.php?module=motdScheduler&op=enter');
            break;
    }
    page_footer();
    return true;
}