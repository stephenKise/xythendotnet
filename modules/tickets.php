<?php

function tickets_getmoduleinfo()
{
    $info = [
        "name" => "Tickets System",
        "author" => "`b`&Stephen Kise`b",
        "description" => "A ticket system to replace the petitions.",
        "category" => "Administrative",
        "version" => "0.1b",
        "download" => "nope",
        "settings" => [],
        "prefs" => [],
        "allowanonymous" => "true",
        "override_forced_navs" => "true",
    ];
    return $info;
}

function tickets_install()
{
    //TABLE LAYOUT
    //ID | TITLE | PARENT | RESPONSETYPE | MESSAGE | ACCTID | TIMESTAMP
    module_addhook('everyfooter');
    return true;
}

function tickets_uninstall()
{
    return true;
}

function tickets_dohook($hook, $args)
{
    switch ($hook) {
        case "everyfooter":
            global $output, $header, $footer;
            $header = str_replace('{petition}', "<a href='petition.php' onClick='return false;' target='_blank' align='right' class='petition'>", $header);
            $footer = str_replace('{petition}', "<a href='petition.php' onClick='return false;' target='_blank' align='right' class='petition'>", $footer);
            break;
        case "superuser":
            addnav("View Tickets!", "runmodule.php?module=tickets&op=admin");
            break;
    }
    return $args;
}

function tickets_run()
{
    global $session;
    $op = httpget('op');
    $ticketsMod = "runmodule.php?module=tickets";
    switch ($op) {
        case "send":
            break;
        case "delete":
            break;
        case "close":
            break;
        case "addUser":
            break;
        case "notify":
            break;
        case "addReply":
            break;
        case "compose":
            popup_header('Tickets');
            output("<form action='$ticketsMod&op=send' method='POST'>", true);
            output("<input name='email' type='email' class='tickets email' value='{$session['user']['email']}' placeholder='Email'/>", true);
            output("<input name='title' type='text' class='tickets title' placeholder='What is the issue?' />", true);
            output("<textarea name='message' class='tickets message' placeholder='Description of your issue:''>", true);
            output("</textarea>", true);
            output("<input type='submit' class='tickets submit' value='Submit' />", true);
            output("</form>", true);
            popup_footer();
            break;
        case "view":
            break;
        case "admin":

            $sql = db_query("SELECT * FROM tickets WHERE responseType = 'created' ORDER BY status+0 DESC");
            while ($row = db_fetch_assoc($sql)) {
                output("<tr>", true);
                output("<td></td>", true);
                output("</tr>", true);
            }
            break;
    }
}

?>