<?php
if (httpget('op') == 'modulesettings' && httpget('module') == 'git') {
    global $session;
    require_once('lib/systemmail.php');
    systemmail(
        $session['user']['acctid'],
        'Security',
        'As a result of keeping your site safe, and assuring that only the '.
            'intended users edit the \'Git\' module settings, we have blocked the'.
            ' access to the settings. Please activate the module and immediately '.
            'go to the grotto to change settings.`n`&Sincerely, `n`&`bStephen Kise`b`0'
    );
    require_once('lib/redirect.php');
    redirect('modules.php?cat=Administrative');
}

/**
*
 *
 *
 * @todo If the module is installed, instantly redirect to a function to set the settings.
 * @todo Hook to queries, check if a sql query selects our api_key or repository.
 *
 */

function git_getmoduleinfo()
{
    $info = [
        'name' => 'Git Controller',
        'author' => 'Stephen Kise',
        'version' => '0.1b',
        'category' => 'Administrative',
        'description' => 'Controll your repository for your site.',
        'settings' => [
            'repository' => 'Name of our repository, viewonly| stephenKise/Legend-of-the-Green-Dragon',
            'api_key' => 'API Key:, text| ',
            'tracked' => 'JSON of files to track, viewonly| Nothing!',
            'pull' => 'Should we pull all changes when they are made to the server?, bool| 0',
            'In order for this to work you must enable webhooks to post to \'runmodule.php?module=git&op=pull\', note',
            'gitignore' => 'Which files should we ignore when making commits? (Separate with newline!), textarea| ',
            '`^dbconnect.php `2is automatically included for you to be safe., note',
            'verified_users' => 'Lock down the management to only certain users?, bool| 0',
            'Edit your prefs if you want messages of all the changes!, note',
        ],
        'prefs' => [
            'message' => 'Do you want messages of all recent changes?',
            'user' => 'This user\'s GitHub account:, text| ',
            'permissions' => 'This user\'s permissions:, viewonly| []',
        ],
    ];
    return $info;
}

function git_install()
{
    module_addhook('mod-dyn-settings');
    module_addhook('changesetting');
    module_addhook('superuser');
    return true;
}

function git_uninstall()
{
    return true;
}

function git_dohook($hook, $args)
{
    switch ($hook) {
        case 'mod-dyn-settings':
        debug('ayy');
            $args = [
                'Due to security risks -  you must change the settings via the link in the grotto!, note'
            ];
            break;
        case 'changesetting':
            if ($args['old'] != $args['new'] && $args['module'] = 'git') {
                debuglog("changed git setting: ({$args['setting']}) {$args['old']} => {$args['new']}");
            }
            break;
        case 'superuser':
            global $session;
            $verified = get_module_setting('verified_users');
            $permissions = json_decode(get_module_pref('permissions'), true);
            if (($verified == 0 && $session['user']['superuser'] & SU_MEGAUSER) || count($permissions) > 0) {
                addnav('Editors');
                addnav('Git Management', 'runmodule.php?module=git&op=manage');
            }
            break;
    }
    return $args;
}

function git_run()
{
    $op = httpget('op');
    require_once('lib/redirect.php');
    page_header('Git Management');
    switch ($op) {
        case 'manage':
            check_su_access(SU_MEGAUSER);
            if (get_module_setting('api_key') == '') {
                redirect('runmodule.php?module=git&op=API');
            }
            if (get_module_setting('verified_users') == 0) {
                output("`4You should consider setting up users that are allowed to edit this server's repository.`n`n`0");
            }
            //Output list of commits, who made them, and the time they were made
                //When you click view more 'v', the section will expand. Clicking less '^' will hide it.
            //Output last time a pull was made, and what changed.
            addnav('View');
            addnav('History', 'runmodule.php?module=git&op=log');
            addnav('Current Differences', 'runmodule.php?module=git&op=diff');
            addnav('Manage');
            addnav('Users and Permissions', 'runmodule.php?module=git&op=users');
            addnav('Create Commit', 'runmodule.php?module=git&op=commit');
            if (get_module_setting('auto_deploy') == 0) {
                addnav('Set up deployment', 'runmodule.php?module=git&op=deployment');
            }
            break;
        case 'API':
            output(
                "`@Congratulations on installing the Git module! Git may seem new, complex, and weird at first, but rest assured - managing a server with it is easy!`n"
            );
            output("To continue with installation, we need an API key. ");
            output("Currently we only support `&GitHub`@. ");
            output("If you do not have a GitHub account, go there and make one now! ");
            output("After that, access your settings and create an API key that permits full repo control, notifications, and repo hooks. ");
            output("Unfortunately we currently do not support organization repos, since this is still in beta. `n`n`0");
            output("`!As soon as you are done, paste your key below - we will automatically continue when you do:");
            rawoutput(
                "<form id='api' action='runmodule.php?module=git&op=save_key' method='POST'>
                    <input name='api_key' id='key' type='password' placeholder='API Key'>
                </form>"
            );
            rawoutput(
                "<script>
                var key = document.getElementByID('key');
                key.onkeypress = function () {
                    if (key.length > 10) {
                        document.getElementByID('api').submit();
                    }
                };
                </script>"
            );
            break;
        case 'save_key':
            $APIKey = httppost('api_key');
            if ($APIKey != '') {
                set_module_setting('api_key', $APIKey);
                redirect('runmodule.php?module=git&op=manage');
                $permissions = json_decode(get_module_pref('permissions'), true);
                array_push($permissions, 'owner');
                set_module_pref('permissions', json_encode($permissions, true));
            }
            else {
                output("`4Oh, we could not set that API key. Please go back, and try again.");
                addnav('Retry', 'runmodule.php?module=git&op=API');
            }
            break;
        case 'update':
            if (get_module_setting('repository') != '') {
                `git pull`;
            }
            break;
    }
    page_footer();
}