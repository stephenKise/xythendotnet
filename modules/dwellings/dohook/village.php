<?php
	addnav($args['gatenav']);
	addnav(array("%s",translate_inline(get_module_setting("villagenav"))),"runmodule.php?module=dwellings");
	set_module_pref("dwelling_saver",0);
	set_module_pref("playerlocation",$session['user']['location']);
?>