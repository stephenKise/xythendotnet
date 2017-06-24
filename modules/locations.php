<?php

function locations_getmoduleinfo()
{
    $info = [
        'name' => 'Locations',
        'author'=> 'Stephen Kise',
        'version' => '0.1',
        'category' => 'Village',
        'description' =>
            'Create different locations where your players can visit.',
        'download' => 'nope',
        'settings' => [
            'locations' => 'Current locations we have available, viewonly| []',
            'capital' => 'Which city should we consider as the main city?, location| ',
        ],
    ];
    return $info;
}

function locations_install()
{
    module_addhook('validlocation');
    module_addhook('village');
    module_addhook('villagetext');
    module_addhook('blockcommentarea');
    module_addhook('superuser');
    return true;
}

function locations_uninstall()
{
    return true;
}

function locations_dohook($hook, $args)
{
    global $session;
    $locations = json_decode(get_module_setting('locations'), true);
    $current = $session['user']['location'];
    $capital = get_module_setting('capital');
    switch ($hook) {
        case 'validlocation':
            foreach ($locations as $location => $info) {
                $args = array_merge($args, [$info['name'] => $info['name']]);
            }
            unset($args[getsetting('villagename', VILLAGE_NAME)]);
            break;
        case 'village':
            require_once('lib/redirect.php');
            if (!in_array($current, array_keys($locations)) && !in_array($capital, array_keys($locations))) {
                $session['user']['location'] = array_keys($locations)[0];
            }
            else if (in_array($capital, array_keys($locations)) && !in_array($current, array_keys($locations))) {
                $session['user']['location'] = $capital;
            }
            if (count($locations) > 1) {
                addnav($args['gatenav']);
                addnav('Travel', 'runmodule.php?module=locations&op=travel');
                if ($current != get_module_setting('capital')) {
                    blocknav('hof.php');
                    blocknav('inn.php');
                }
            }
            break;
        case 'villagetext':
            $args['section'] = $current;
            $args['title'] = full_sanitize(ucfirst($locations[$current]['name']));
            $args['text'] = "`2{$locations[$current]['description']}`n`n`0";
            $args['image'] = $locations[$current]['image'];
            break;
        case 'blockcommentarea':
                $args['block'] = $locations[$current]['hidechat'];
            break;
        case 'superuser':
            if ($session['user']['superuser'] & SU_MEGAUSER) {
                addnav('Editors');
                addnav(
                    'Locations Manager',
                    'runmodule.php?module=locations&op=enter'
                );
            }
            break;
    }
    return $args;
}

function locations_run()
{
    $op = httpget('op');
    require_once('lib/redirect.php');
    $locations = json_decode(get_module_setting('locations'), true);
    page_header('Locations Manager');
    switch ($op) {
        case 'enter':
            addnav('Refresh', 'runmodule.php?module=locations&op=enter');
            addnav('Return to the Grotto', 'superuser.php');
            addnav('', 'runmodule.php?module=locations&op=create');
            output(
                "<table class='locations locationManager'>
                    <tr>
                        <th class='locationsHeader' colspan='2'>
                        Locations
                        </th>
                    </tr>
                ",
                true);
            foreach ($locations as $location => $info) {
                output(
                    "<tr>
                        <td>
                            `2[
                            <a href='%s' class='locationsEdit'>
                                Edit
                            </a>
                            `2 |
                            <a href='%s' class='locationsDelete' onclick=\"return confirm('Are you sure you want to delete this location? Players will be moved to the capital or %s instead.');\">
                                Del
                            </a>
                            `2]`0
                        </td>
                        <td>
                            `^%s `@-> `Q%s
                        </td>
                    </tr>",
                    "runmodule.php?module=locations&op=edit&location=$location",
                    "runmodule.php?module=locations&op=delete&location=$location",
                    (array_keys($locations)[0] == $location ? array_keys($locations)[1] : array_keys($locations)[0]),
                    $location,
                    $info['description'],
                    true
                );
                addnav('', "runmodule.php?module=locations&op=edit&location=$location");
                addnav('', "runmodule.php?module=locations&op=delete&location=$location");
            }
            output(
                "<tr>
                    <td colspan='2' class='locationsCreate'>
                    %s
                    </td>
                </tr>",
                locationsform(),
                true
            );
            output("</table>", true);
            break;
        case 'delete':
            $accounts = db_prefix('accounts');
            $location = httpget('location');
            unset($locations[$location]);
            $newHome = get_module_setting('capital');
            if ($newHome == $location) {
                $newHome = (array_keys($locations)[0] == $location ? array_keys($locations)[1] : array_keys($locations)[0]);
                set_module_setting('capital', $newHome);
            }
            db_query("UPDATE $accounts SET location = '$newHome' WHERE location = '$location'");
            set_module_setting('locations', json_encode($locations));
            redirect('runmodule.php?module=locations&op=enter');
            break;
        case 'edit':
            output(locationsform(httpget('location')), true);
            addnav('Go back', 'runmodule.php?module=locations&op=enter');
            addnav('', 'runmodule.php?module=locations&op=create');
            break;
        case 'create':
            $post = httpallpost();
            foreach ($post as $key => $val) {
                $post[$key] = stripslashes($val);
            }
            addnav('Go back', 'runmodule.php?module=locations&op=enter');
            if (is_array($post) && $post['edit'] == '') {
                $newLocations = array_merge(
                    $locations,
                    [$post['name'] => $post]
                );
            }
            else if ($post['edit'] != '') {
                $locations[$post['edit']] = $post;
                $newLocations = $locations;
            }
            str_replace(PHP_EOL, '`n', $post['description']);
            unset($post['edit']);
            set_module_setting('locations', json_encode($newLocations));
            redirect('runmodule.php?module=locations&op=enter');
            break;
        case 'travel':
            global $session;
            require_once('lib/villagenav.php');
            villagenav();
            output(
                "`@Eager to travel the world, you get ready at the gates of %s and prepare to leave the world. But where should you go?`n`n`0",
                ucfirst($session['user']['location'])
            );
            foreach ($locations as $location => $info) {
                if ($session['user']['location'] != $info['name']) {
                    output(
                        "<a href='runmodule.php?module=locations&op=select&location=%s' class='locationsSelect'>`b`@%s`b</a>`n`Q%s`n`n`0",
                        $location,
                        $info['name'],
                        $info['description'],
                        true
                    );
                    addnav('', "runmodule.php?module=locations&op=select&location=$location");
                    addnav(
                        $info['name'],
                        "runmodule.php?module=locations&op=select&location=$location"
                    );
                }
            }
            break;
        case 'select':
            global $session;
            if (httpget('location') != '') {
                $session['user']['location'] = $locations[httpget('location')]['name'];
            }
                redirect('village.php');
            break;
    }
    page_footer();
}

function locationsform($name = false)
{
    if ($name) {
        $edit = "<input type='hidden' name='edit' value='$name'>";
        $locations = json_decode(get_module_setting('locations'), true);
    }
    $form = sprintf(
        "<form action='runmodule.php?module=locations&op=create' method='POST'>
        %s
            <input name='name' placeholder='Location Name' value='%s'><br />
            <input name='image' placeholder='Path to city image' value='%s'><br />
            <textarea name='description' placeholder='Description of the location' class='input' cols='35' rows='5'>%s</textarea><br />
            <input type='checkbox' name='hideforest' value='yes' id='hideforest'>
            <label for='hideforest'>
                Remove forest and training.<br />
            </label>
            <input type='checkbox' name='hidechat' value='yes' id='hidechat'>
            <label for='hidechat'>
                Remove commentary.<br />
            </label>
            <input type='submit' value='Save'>
        </form>",
        $edit,
        $locations[$name]['name'],
        $locations[$name]['image'],
        $locations[$name]['description'],
        true
    );
    return $form;
}