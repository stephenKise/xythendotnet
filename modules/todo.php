<?php
function todo_getmoduleinfo()
{
    $info = [
        'name' => 'To Do List',
        'author' => '`b`&Stephen Kise`b',
        'version' => '0.1b',
        'description' => 
            'A to do list to track tasks.',
        'category' => 'General',
        'prefs' => [
            'todo' => 'JSON Object of user\'s to do list:, viewonly| []',
            'current' => 'JSON Object of user\'s working list:, viewonly| []',
            'finished' => 'JSON Object of user\'s finished list:, viewonly| []',
            'can_create' => 'Can this user create a to do list?, bool| 0',
        ],
        'override_forced_nav' => true,
    ];
    return $info;
}

function todo_install()
{
    module_addhook('charstats');
    module_addhook('superuser');
    return true;
}

function todo_uninstall()
{
    return true;
}

function todo_dohook($hook, $args)
{
    global $session;
    switch ($hook) {
        case 'charstats':
            if ($session['user']['acctid'] == 779 || get_module_pref('can_create') == 1) {
                addcharstat('Personal Info', 'To do', "<a href='runmodule.php?module=todo&op=view' target='_blank'>Open</a>");
            }
            break;
        case 'superuser':
            addnav('Editors');
            addnav('Edit To Do', 'runmodule.php?module=todo&op=enter');
            break;
    }
    return $args;
}

function todo_run()
{
    page_header('To Do Editor');
    addnav('Superuser', 'superuser.php');
    addnav('To Do Home', 'runmodule.php?module=todo&op=enter');
    $form = [
        'title' => 'Name of item:, text| New Task',
        'description' => 'Description of task:, textarea',
        'claimed' => 'Who should we assign this to?, int| 0',
        'Note that this is based on acctid. If you do not know the number of the staff member this is for - leave this blank!, note',
        'status' => 'Status of this task:, enum,0,Unfinished,1,In Process,2,Finished,3,Help Needed',
    ];
    $id = httpget('id');
    $accounts = db_prefix('accounts');
    $todo = db_prefix('todo');
    switch (httpget('op')) {
        case 'enter':
            addnav('Create To Do', 'runmodule.php?module=todo&op=create');
            $sql = db_query(
                "SELECT *
                FROM $todo
                WHERE status = '0'
                OR status = '3';"
            );
            rawoutput(
                "<table class='todo allToDo'>
                    <tr>
                        <th>
                            Ops
                        </th>
                        <th>
                            Task
                        </th>
                        <th>
                            Description
                        </th>
                        <th>
                            Claimed
                        </th>
                    </tr>
                "
            );
            while ($row = db_fetch_assoc($sql)) {
                if ($row['claimed']) {
                    $select = db_query(
                        "SELECT name
                        FROM $accounts
                        WHERE acctid = '{$row['claimed']}'"
                    );
                    $name = db_fetch_assoc($select);
                    $row['claimed'] = "`@Claimed by {$name['name']}`@!";
                }
                else {
                    $row['claimed'] = '`$Not Claimed!';
                }
                if ($row['status'] == 0) {
                    $class = 'todoOpen';
                }
                else {
                    $class = 'todoRequestedHelp';
                }
                output(
                    "<tr class='todo $class'>
                        <td>
                            `2[<a href='runmodule.php?module=todo&op=edit&id={$row['id']}'>`)E</a> `2| <a href='runmodule.php?module=todo&op=delete&id={$row['id']}'>`)X</a>`2]
                        </td>
                        <td>
                            {$row['title']}
                        </td>
                        <td>
                            {$row['description']}
                        </td>
                        <td>
                            {$row['claimed']}
                        </td>
                    </tr>",
                    true
                );
                addnav('', "runmodule.php?module=todo&op=edit&id={$row['id']}");
                addnav('', "runmodule.php?module=todo&op=delete&id={$row['id']}");
            }
            rawoutput("</table>");
            break;
        case 'edit':
        case 'create':
            require_once('lib/showform.php');
            if ($id) {
                $sql = db_query("SELECT * FROM $todo WHERE id = '$id';");
                $allToDo = db_fetch_assoc($sql);
                $extra = "&id=$id";
            }
            else {
                $allToDo = [];
            }
            rawoutput("<form action='runmodule.php?module=todo&op=save$extra' method='POST'>");
            showform($form, $allToDo);
            rawoutput("</form>");
            break;
        case 'save':
            require_once('lib/redirect.php');
            $post = httpallpost();
            foreach ($post as $key => $val) {
                $post[$key] = addslashes($val);
            }
            if ($id) {
                db_query(
                    "UPDATE $todo
                    SET title = '{$post['title']}',
                    description = '{$post['description']}',
                    claimed = '{$post['claimed']}',
                    status = '{$post['status']}'
                    WHERE id = '$id';"
                );
            }
            else {
                db_query(
                    "INSERT INTO $todo (title, description, claimed, status)
                    VALUES (
                        '{$post['title']}',
                        '{$post['description']}',
                        '{$post['claimed']}',
                        '0'
                    );"
                );
            }
            redirect('runmodule.php?module=todo&op=enter');
            break;
        case 'delete':
            require_once('lib/redirect.php');
            db_query("DELETE FROM $todo WHERE id = '$id';");
            debuglog('deleted an item from the todo list!');
            redirect('runmodule.php?module=todo&op=enter');
            break;
        case 'view':
            break;
        case 'json':
            break;
    }
    page_footer();
}
?>