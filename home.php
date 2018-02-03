<?php
// translator ready
// addnews ready
// mail ready
const ALLOW_ANONYMOUS = true;

require_once('common.php');
require_once('lib/http.php');


if (isset($_POST['template'])){
    $skin = $_POST['template'];
    if ($skin > '') {
        setcookie('template', $skin, strtotime('+45 days'));
        $_COOKIE['template'] = $skin;
    }
}
if (!isset($session['loggedin'])) {
    $session['loggedin']=false;
}
if ($session['loggedin']) {
    redirect('badnav.php');
}
if (httpget('op') == 'timeout') {
    $session['message'] .= translate_inline(
        "Your session has timed out, you must log in again.`n"
    );
}
if (!isset($_COOKIE['lgi'])){
    $session['message'] = translate_inline("It appears that you may be ".
        "blocking cookies from this site.  At least session cookies must be ".
        "enabled in order to use this site.`n".
        "`b`#If you are not sure what cookies are, please ".
        "<a href='http://en.wikipedia.org/wiki/WWW_browser_cookie'>read this ".
        "article</a> about them, and how to enable them.`b`n"
    );
}

tlschema('home');
page_header();

clearnav();
addnav('New to Xy?');
addnav('Create an Account', 'create.php');
addnav('Other Info');
addnav('About Xythen', 'about.php');
addnav('', 'login.php');

modulehook('index', []);
rawoutput("<style>html, body {height: 100%}</style>");
output("`c`n`n");
output(getsetting('description', 'Welcome to Legend of the Green Dragon!'), true);
output("`n`n`n`n");

if (isset($session['message']) && $session['message'] != '') {
    output_notl("`n`b`\$%s`b`n", $session['message'], true);
}
unset($session['message']);

rawoutput("<form action='login.php' method='POST'>");
rawoutput(
    templatereplace("login",
        [
            "username" => translate_inline("Username"),
            "password" => translate_inline("Password"),
            "button" => translate_inline("Log in"),
        ]
));
rawoutput("</form>");
modulehook('googlelogin');
output("`c");

page_footer();
?>
