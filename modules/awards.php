<?php

function awards_getmoduleinfo()
{
    $info = [
        'name' => 'Player Awards',
        'author' => '`b`&Stephen Kise`b',
        'version' => '0.1',
        'description' =>
            'Awards to give the players for their efforts.',
        'category' => 'Character',
        'prefs' => [
            'Awards Prefs, title',
            'can_edit' => 'Can this player edit the rewards?, bool| 0',
            'rewards' => 'JSON of player rewards:, viewonly| []',
        ],
    ];
    return $info;
}

function awards_install()
{
    module_addhook('biostat');
    module_addhook('superuser');
    return true;
}

function awards_uninstall()
{
    return true;
}

function awards_dohook($hook, $args)
{
    global $session;

    switch ($hook) {
        case 'biostat':
            if ($session['user']['superuser'] & SU_EDIT_USERS) {
                addnav('Admin Functions');
                addnav('Give Award', 'runmodule.php?module=awards&op=give&char=' . httpget('char') . '&ret=' . $_SERVER['REQUEST_URI']);
            }
            $json = json_decode(get_module_pref('rewards', 'awards', httpget('char')), true);
            $string = '';
            $count = count($json);
            $i = 0;
            foreach ($json as $key => $val) {
                $i++;
                $awardId = key($val);
                $name = $val[$awardId];
                if ($i == $count && $count > 1) {
                    $string .= "`^ and `0$name";
                }
                else if ($i < $count && $i != 1) {
                    $string .= "`^, `0$name";
                }
                else if ($i == 1) {
                    $string .= "`0$name";
                }
            }
            $args['tablebiostat']['Companions/Items']['Awards'] = $string;
            break;
        case 'superuser':
            if ($session['user']['superuser'] & SU_EDIT_USERS) {
                addnav('Editors');
                addnav('Awards Editor', 'runmodule.php?module=awards&op=enter');
            }
            break;
    }
    return $args;
}

function awards_run()
{
    page_header('Player Awards');
    $id = httpget('id');
    $char = httpget('char');
    $awards = db_prefix('awards');
    $form = [
        'name' => 'Name of the Item:, text',
        'description' => 'Description:, textarea',
    ];
    addnav('Return to the Grotto', 'superuser.php');
    addnav('Awards Editor Home', 'runmodule.php?module=awards&op=enter');
    switch (httpget('op')) {
        case 'enter':
            addnav('Create Award', 'runmodule.php?module=awards&op=create');
            output('Hey everybody');
            $sql = db_query(
                "SELECT * FROM $awards WHERE 1=1;"
            );
            rawoutput("<table class='awards allAwards'><tr><th width='50px'>Ops</th><th width='250px'>Name</th><th width='500px'>Description</th></tr>");
            while ($row = db_fetch_assoc($sql)) {
                foreach ($row as $key => $val) {
                    $row[$key] = stripslashes($val);
                }
                output(
                    "<tr>
                        <td>
                            `2[`)<a class='awards awardsOps' href='runmodule.php?module=awards&op=edit&id={$row['id']}'>E</a> `2| <a class='awards awardsOps' href='runmodule.php?module=awards&op=delete&id={$row['id']}'>X</a>`2]
                        </td>
                        <td>
                            {$row['name']}
                        </td>
                        <td>
                            {$row['description']}
                        </td>
                    </tr>",
                    true
                );
                addnav('', "runmodule.php?module=awards&op=edit&id={$row['id']}");
                addnav('', "runmodule.php?module=awards&op=delete&id={$row['id']}");
            }
            rawoutput("</table>");
            break;
        case 'create':
            require_once('lib/showform.php');
            $row = [];
            rawoutput("<form action='runmodule.php?module=awards&op=save' method='POST'>");
            showform($form, $row);
            rawoutput("</form>");
            addnav('', "runmodule.php?module=awards&op=save");
            break;
        case 'edit':
            require_once('lib/showform.php');
            if ($id) {
                $sql = db_query(
                    "SELECT name, description FROM $awards WHERE id = '$id'"
                );
                $row = db_fetch_assoc($sql);
                foreach ($row as $key => $val) {
                    $row[$key] = stripslashes($val);
                }
            }
            else {
                $row = [];
            }
            rawoutput("<form action='runmodule.php?module=awards&op=save&id=$id' method='POST'>");
            showform($form, $row);
            rawoutput("</form>");
            addnav('', "runmodule.php?module=awards&op=save&id=$id");
            break;
        case 'delete':
            require_once('lib/redirect.php');
            if ($id) {
                db_query("DELETE FROM awards WHERE id = '$id'");
            }
            redirect('runmodule.php?module=awards&op=enter');
            break;
        case 'save':
            require_once('lib/redirect.php');
            $post = httpallpost();
            foreach ($post as $key => $val) {
                $post[$key] = addslashes($val);
            }
            debug($post);
            if ($id) {
                db_query("UPDATE awards SET name = '{$post['name']}', description = '{$post['description']}';");
            }
            else {
                db_query("INSERT INTO awards (name, description) VALUES ('{$post['name']}', '{$post['description']}');");
            }
            redirect('runmodule.php?module=awards&op=enter');
            break;
        case 'give':
            debug('Ayy');
            addnav('Return to the Grotto', 'superuser.php');
            addnav('Refresh', 'runmodule.php?module=awards&op=give&char='.$char);
            $sql = db_query("SELECT * FROM $awards WHERE 1=1;");
            while ($row = db_fetch_assoc($sql)) {
                output(
                    "<a class='awards giveAward' href='runmodule.php?module=awards&op=set&char=$char&id={$row['id']}'>
                        <fieldset class='awards selectAward'>
                        <legend class='awards awardTitle'>{$row['name']}</legend>
                        {$row['description']}
                        </fieldset>
                    </a>",
                    true
                );
                addnav('', "runmodule.php?module=awards&op=set&char=$char&id={$row['id']}");
            }
            break;
        case 'set':
            require_once('lib/redirect.php');
            $prefs = json_decode(get_module_pref('rewards', 'awards', $char), true);
            $sql = db_query(
                "SELECT name FROM $awards WHERE id = '$id';"
            );
            $row = db_fetch_assoc($sql);
            if (!in_array($id, $prefs)) {
                array_push($prefs, [$id => $row['name']]);
                set_module_pref('rewards', json_encode($prefs), 'awards', $char);
            }
            redirect("runmodule.php?module=newbio&char=$char");
            break;
    }
    page_footer();
}
?>