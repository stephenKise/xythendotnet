<?php
function roll($amt = 1, $sides = 6)
{
    global $session;
    $roll = [];
    for ($i = 0; $i < $amt; $i++) {
        $rand = rand(1,$sides);
        if ($session['user']['acctid'] == 779) $rand = rand($sides/2, $sides);
        $roll['total'] += $rand;
        $roll['dice'][$i] = $rand;
    }
    if ($session['roll']['total'] == $roll['total']) {
        $roll['total'] = 0;
        dicebag($amt,$sides);
    }
    else {
        $session['roll']['total'] = $roll['total'];
    }
    $roll['sides'] = $sides;
    $roll['amt'] = $amt;
    $session['roll'] = $roll;
    return $roll;
}
?>