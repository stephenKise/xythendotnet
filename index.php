<?php
// translator ready
// addnews ready
// mail ready
if ($_SERVER['HTTP_HOST'] == 'xythen.net'){
	header("Location: /home.php");
}elseif ($_SERVER[SERVER_NAME] == 'arcane.us'){
	header("Location: /otr/home.php");
}else{
	header("Location: home.php?".$_SERVER['QUERY_STRING']);
}
?>