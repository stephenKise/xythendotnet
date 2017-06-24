<?php
require_once('lib/redirect.php');
$email = httpget('email');
$email_string = file_get_contents('./game_cache/banned_emails.csv');
debug($email_string);
$email_string .= ','.$email;
debug($email_string);
$email_content = file_put_contents('game_cache/banned_emails.csv', $email_string);
// redirect('user.php?op=search');
?>