<?php

addnav("Actions");
addnav("Browse the books", "runmodule.php?module=greatlibrary&op=browse");
if(get_module_setting("adminonly") == 0){
    $strText = translate_inline("Open to all would-be scribes");
    addnav("Write your own book", "runmodule.php?module=greatlibrary&op=write");
}else{
    $strText = translate_inline("For $strLibraryName staff only");
    if($session["user"]["superuser"] & 16){
    addnav("Write your own book", "runmodule.php?module=greatlibrary&op=write");
}
}
if(get_module_setting("usehooks") == 1){
    modulehook("greatlibrary_enter");
}
addnav("Other");
villagenav();
output("Past the wooden double doors of the Great Library you encounter a large hall, filled with thick rows of richly carved wood that reach far into the air, fusing with the ceiling above to form an interesting labyrinth filled with books of every kind, from ancient leather-bound tomes, to casual novels and even DIY books. The lighting is kept low, and the air is filled with the dusty scent of books old and new, but you can glimpse a reading area composed of worn but comfortable couches and a few table-lamps. To the wall by your right, fusing partially with a shelf you see a desk, and behind it the glassed, expectant look of the local Librarian, Edraude. he beckons you in and gently points to a sigh stating that clients may buy copies of their favorite books if they wish, both in hardcover and soft cover.");
?>