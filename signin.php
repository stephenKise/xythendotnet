<?php
define("ALLOW_ANONYMOUS", true);
require_once('common.php');
require_once('lib/http.php');
require_once('lib/checkban.php');

$post = httpallpost();

?>