<?php
$clan=$u['clanid'];
if ($clan==$owned1 && $clan==$owned2 && $clan==$owned3 && $clan<>0) output("`n`b`c`^Your guild owns all three Labyrinths, you receive a experience bonus for forest fights.`c`n`b");
else if ($clan>0) output("`n`b`c`^As your guild doesn't control all three Labyrinths, you don't receive a experience bonus.`c`n`b");
else output("`n`b`c`^You should join a guild and take control of the Labyrinths to earn an experience bonus!`c`b`n");
?>