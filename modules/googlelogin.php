<?php

function googlelogin_getmoduleinfo()
{
    $info = [
        'name' => 'Google Client',
        'author'=> 'Stephen Kise',
        'version' => '0.1',
        'category' => 'Administrative',
        'description' =>
            'Using the Google API wrapper to login, add calendar entries, etc.',
        'download' => 'nope',
        'override_forced_navs' => 'true',
        'allowanonymous' => 'true',
    ];
    return $info;
}

function googlelogin_install()
{
    module_addhook('superuser');
    module_addhook('googlelogin');
    module_addhook('player-logout');
    return true;
}

function googlelogin_uninstall()
{
    return true;
}

function googlelogin_dohook($hook, $args)
{
    global $session;
    $client = new Google_Client();
    $client->setClientId(
        '26313046065-43o5a5a734bckpjfvsddbd5dfc0meaf9.apps.googleusercontent.com'
    );
    $client->setClientSecret(
        'al7DBt4C53XJc4qbg851WkHQ'
    );
    $client->setRedirectUri(
        'http://xythen.net/runmodule.php?module=googlelogin&op=landing'
    );
    $client->setScopes([
        'email',
        'openid',
        'profile',
        'https://www.googleapis.com/auth/calendar',
        'https://www.googleapis.com/auth/userinfo.email',
        'https://www.googleapis.com/auth/plus.me',
    ]);
    $google = new Google($client);
    switch ($hook) {
        case 'superuser':
            addnav(
                'Google Test',
                'runmodule.php?module=googlelogin&op=enter'
            );
            break;
        case 'googlelogin':
            if (!$google->isLoggedIn() || !$session['user']['loggedin']) {
                output(
                    "<a href='%s'><img class='google-login signing-img' src='images/googleSignin.png' height='40px'/></a>`n",
                    $google->getAuthUrl(),
                    true
                );
            }
            else {
                redirect('runmodule.php?module=googlelogin&op=logout');
            }
            break;
        case 'player-logout':
            //Safety measures. Just in case.
            unset($session);
            unset($_SESSION);
            break;
    }
    return $args;
}

function googlelogin_run()
{
    global $session;
    $client = new Google_Client();
    $client->setClientId(
        '26313046065-43o5a5a734bckpjfvsddbd5dfc0meaf9.apps.googleusercontent.com'
    );
    $client->setClientSecret(
        'al7DBt4C53XJc4qbg851WkHQ'
    );
    $client->setRedirectUri(
        'http://xythen.net/runmodule.php?module=googlelogin&op=landing'
    );
    $client->setScopes([
        'email',
        'openid',
        'profile',
        'https://www.googleapis.com/auth/calendar',
        'https://www.googleapis.com/auth/userinfo.email',
        'https://www.googleapis.com/auth/plus.me',
    ]);
    $google = new Google($client);
    if ($session['access_token'] != '') {
        $token = $google->client->getAccessToken();
        $client->setAccessToken($session['access_token']);
    }
    require_once('lib/redirect.php');
    require_once('lib/checkban.php');
    $op = httpget('op');
    page_header();
    switch ($op) {
        case 'enter':
            if (!$google->isLoggedIn()) {
                output(
                    "Please login: <a href='%s'>Here</a>",
                    $google->getAuthUrl(),
                    true
                );
            }
            else {
                $googlePlus = new Google_Service_Plus($client);
                $user = $googlePlus->people->get('me');
                foreach ($user['emails'] as $key => $email) {
                    if ($email['type'] == 'account' && !$userEmail) {
                        $userEmail = $email['value'];
                    }
                }
                $sql = db_query(
                    "SELECT login, name
                    FROM accounts
                    WHERE emailaddress = '$userEmail'"
                );
                $num = db_num_rows($sql);
                if ($num == 0) {
                    redirect('runmodule.php?module=googlelogin&op=logout');
                }
                else if ($num == 1) {
                    $sql = db_query(
                        "SELECT * FROM accounts
                        WHERE emailaddress = '$userEmail'"
                    );
                    $session['user'] = db_fetch_assoc($sql);
                    $session['loggedin'] = true;
                    $session['user']['loggedin'] = true;
                    $session['laston'] = date('Y-m-d H:i:s');
                    $session['user']['laston'] = date("Y-m-d H:i:s");
                    checkban($session['user']['login'], true);
                    redirect($session['user']['restorepage']);
                    //addnav("Return to the village", "village.php");
                }
                else if ($num > 1) {
                    $session['user_email'] = $userEmail;
                    output("`QWhich user do you want to use?`n");
                    while ($row = db_fetch_assoc($sql)) {
                        output(
                            "<div class='google-Login select-account'>
                            <a href='runmodule.php?module=googlelogin&op=select&login=%s'>`@%s `&(`^%s`&)</a>
                            </div>`0
                            ",
                            $row['login'],
                            $row['name'],
                            $row['login'],
                            true
                        );
                    }

                }
            }
            addnav('Login Page', 'runmodule.php?module=googlelogin&op=logout');
            break;
        case 'landing':
            //This page is used in the Redirect Uri, just to make sure no X attacks are executed.
            //I HEAVILY SUGGEST EDITING THIS MODULE, TO CHANGE THIS PAGE, AND USE A DIFFERENT REDIRECT URI.
            //I will not provide assistance with this. Read the release notes! -Stephen Kise
            if ($google->verifyRedirectCode()) {
                redirect('runmodule.php?module=googlelogin&op=enter');
            }
            break;
        case 'logout':
            $google->logout();
            header('Location: home.php');
            break;
        case 'select':
            $login = httpget('login');
            if ($login != '' && $session['access_token'] != '' && $session['user_email'] != '') {
                $sql = db_query(
                    "SELECT * FROM accounts
                    WHERE login = '$login'
                    AND emailaddress = '{$session['user_email']}'"
                );
                $session['user'] = db_fetch_assoc($sql);
                $session['loggedin'] = true;
                $session['user']['loggedin'] = true;
                $session['laston'] = date('Y-m-d H:i:s');
                $session['user']['laston'] = date("Y-m-d H:i:s");
                checkban($session['user']['login'], true);
                redirect($session['user']['restorepage']);
            }
            else {
                redirect('runmodule.php?module=googlelogin&op=logout');
            }
            break;
    }
    page_footer();
}

require_once('vendor/autoload.php');
class Google {
    public $client;
    public function __construct(Google_Client $client)
    {
        $this->client = $client;
    }

    public function isLoggedIn()
    {
        global $session;
        return isset($session['access_token']);
    }

    public function getAuthUrl()
    {
        return $this->client->createAuthUrl();
    }

    public function verifyRedirectCode()
    {
        $code = httpget('code');
        var_dump($code);
        var_dump($_GET['code']);
        if ($code != '') {
            $this->client->authenticate($code);
            $this->setToken($this->client->getAccessToken());
            return true;
        }
        return false;
    }

    public function setToken($token)
    {
        global $session;
        $session['access_token'] = $token;
        $this->client->setAccessToken($token);
    }

    public function logout()
    {
        global $session;
        unset($session['access_token']);
    }
}