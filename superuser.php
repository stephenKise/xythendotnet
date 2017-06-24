<?php
// translator ready
// addnews ready
// mail ready
require_once("common.php");
require_once("lib/commentary.php");
require_once("lib/sanitize.php");
require_once("lib/http.php");

check_su_access(0xFFFFFFFF &~ SU_DOESNT_GIVE_GROTTO);
addcommentary();
tlschema("superuser");

require_once("lib/superusernav.php");
superusernav();

$op = httpget('op');
if ($op=="keepalive"){
	$sql = "UPDATE " . db_prefix("accounts") . " SET laston='".date("Y-m-d H:i:s")."' WHERE acctid='{$session['user']['acctid']}'";
	db_query($sql);
	global $REQUEST_URI;
	echo '<html><meta http-equiv="Refresh" content="30;url='.$REQUEST_URI.'"></html><body>'.date("Y-m-d H:i:s")."</body></html>";
	exit();
}elseif ($op=="newsdelete"){
	$sql = "DELETE FROM " . db_prefix("news") . " WHERE newsid='".httpget('newsid')."'";
	db_query($sql);
	$return = httpget('return');
	$return = cmd_sanitize($return);
	$return = substr($return,strrpos($return,"/")+1);
	redirect($return);
}

page_header("Superuser Grotto");

output("`n");
$args = modulehook("superusertop", array("section"=>"superuser"));
commentdisplay("", $args['section'],"Engage in conversation,",15);

addnav("Navigation");
if ($session['user']['superuser'] &~ SU_DOESNT_GIVE_GROTTO) addnav("Staff Guide", "https://docs.google.com/document/d/1UfZPg-GC1ro9tU6xCcWV3Mfe6qstWOfM51bKXw0bNTE/edit#", true, true);
if ($session['user']['superuser'] &~ SU_DOESNT_GIVE_GROTTO) addnav("Projects Folder", "https://drive.google.com/drive/u/0/folders/0B7t7rzVpK_Uifl9OV2lZRHo1UDBfX1JVbDNTSllTY0oyOTNKM2xfR1ljbVE3WkVMY3VpWFE", true, true);
if ($session['user']['superuser'] &~ SU_DOESNT_GIVE_GROTTO) addnav("RPF Skype", "skype:?chat&blob=sft8cpaHE3Jor_15WGfuEIXj6eL8UXjfXoC3-xwAQya3ebbAcH_6GP2KlBmHaCjALWNo4uhinSOts3orSg", true, true);
if ($session['user']['superuser'] &~ SU_DOESNT_GIVE_GROTTO) addnav("Report a Warning", "https://docs.google.com/forms/d/1udcU5981ZYQ9n-BOfzINbnF6TR1UuqetG2PNX6bQavM/viewform", true, true);



addnav("Actions");
if ($session['user']['superuser'] & SU_EDIT_PETITIONS) addnav("Petition Viewer","viewpetition.php");
if ($session['user']['superuser'] & SU_EDIT_COMMENTS) addnav("Recent Commentary","moderate.php");

addnav("Editors");
if ($session['user']['superuser'] & SU_EDIT_USERS) addnav("User Editor","user.php");
if ($session['user']['superuser'] & SU_EDIT_USERS) addnav("Title Editor","titleedit.php");
if ($session['user']['superuser'] & SU_EDIT_CREATURES) addnav("Creature Editor","creatures.php");
if ($session['user']['superuser'] & SU_EDIT_MOUNTS) addnav("Vehicle Editor","mounts.php");

addnav("Developer");
if ($session['user']['superuser'] & SU_MANAGE_MODULES) addnav("Manage Modules","modules.php");
if ($session['user']['superuser'] & SU_EDIT_CONFIG) addnav("Game Settings","configuration.php");
if ($session['user']['superuser'] & SU_RAW_SQL) addnav("Q?Run Raw SQL", "rawsql.php");


addnav("Statistics");
if ($session['user']['superuser'] & SU_EDIT_CONFIG) addnav("Site Stats","stats.php");
if ($session['user']['superuser'] & SU_EDIT_CONFIG) addnav("Referring URLs","referers.php");
if ($session['user']['superuser'] & SU_EDIT_DONATIONS) addnav("Donator Page","donators.php");
if ($session['user']['superuser'] & SU_EDIT_PAYLOG) addnav("Payment Log","paylog.php");


addnav("Module Configurations");

// modulehook("superuser", array(), true);
modulehook("superuser", array());
page_footer();
?>