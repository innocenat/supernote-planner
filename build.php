<?php

$YEAR = 2023;

function make_regular_year($year): string
{
    return strval($year - 1) . '-12 ' . strval($year + 1) . '-3';
}

function make_school_year($year): string
{
    return strval($year) . '-7 ' . strval($year + 1) . '-10';
}

$languages = ['en'];
$option_names = [
    ['sunday', 'monday'],
    ['lined', 'dot'],
    ['200', '40'],
    ['24hr', '12hr'],
    ['day', 'night'],
];

foreach ($languages as $lang) {
    for ($i = 0; $i < 1 << 5; $i ++) {
        if (($i & 1) === 1 || ($i >> 1 & 1) === 1 || ($i >> 2 & 1) === 1) {
            // Only do more common config
            continue;
        }

        $options = sprintf("%05b", $i);
        $name = [];
        for ($j = 0; $j < 5; $j++) {
            $name[] = $option_names[$j][intval($options[$j])];
        }

//        $filename = 'Planner.' . implode('.', $name) . '.school-' . strval($YEAR) . '.' . $lang . '.pdf';
//        $cmd = 'php make-planner.php ' . $lang . ' ' . $options . ' ' . make_school_year($YEAR) . ' "Planner" "School-' . strval($YEAR) . '" ' . $filename;
//        echo $cmd, "\n";
//        system($cmd);

        $filename = 'Planner.' . implode('.', $name) . '.' . strval($YEAR) . '.' . $lang . '.pdf';
        $cmd = 'php make-planner.php ' . $lang . ' ' . $options . ' ' . make_regular_year($YEAR) . ' "Planner" "' . strval($YEAR) . '" ' . $filename;
        echo $cmd, "\n";
        system($cmd);
    }
}
