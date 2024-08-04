<?php
define('BASE', dirname(__FILE__));
const DS = DIRECTORY_SEPARATOR;

require BASE . DS . 'lib' . DS . 'calendar.php';
require BASE . DS . 'lib' . DS . 'events.php';
require BASE . DS . 'lib' . DS . 'links.php';
require BASE . DS . 'lib' . DS . 'templates.php';
require BASE . DS . 'lib' . DS . 'igenerator.php';
require BASE . DS . 'lib' . DS . 'pdfgenerator.php';
require BASE . DS . 'lib' . DS . 'size.php';
require BASE . DS . 'lib' . DS . 'loc.php';
require BASE . DS . 'lib' . DS . 'colors.php';

require 'planner/config.php';
require 'vendor/autoload.php';

if (count($argv) < 8) {
    echo 'Usage: php ' . $argv[0] . ' <lang> [options] <start-y-m> <end-y-m> <title> <subtitle> <filename.pdf>', "\n";
    echo '    e.g. php ' . $argv[0] . ' en 11000 2021-12 2023-03 "Planner" "2022" Planner-2022.pdf', "\n";
    echo '    options: start_monday, note_style_dot, extra_40, 12hr, night_shift', "\n";
    exit(1);
}

define('W', Size::px2mm(1404));
define('H', Size::px2mm(1872));
define('PX100', Size::px2mm(100));

$lang = $argv[1];
$options = $argv[2];
[$start_y, $start_m] = array_map('intval', explode('-', $argv[3]));
[$end_y, $end_m] = array_map('intval', explode('-', $argv[4]));
$title = $argv[5];
$subtitle = $argv[6];
$filename = $argv[7];

Loc::load($lang);
Size::$ppi = 300;
Calendar::$start_monday = $options[0] === '1';
Calendar::$start_y = $start_y;
Calendar::$start_m = $start_m;
Calendar::$end_y = $end_y;
Calendar::$end_m = $end_m;

$config = [
    'planner_only' => true,
    'monday_start' => $options[0] === '1',
    'note_style' => $options[1] === '1' ? 'dot' : 'lined',
    'extra_amount' => $options[2] === '1' ? 40 : 200,
    '12hr' => $options[3] === '1',
    'night_shift' => $options[4] === '1',
];

define('PLANNER_ONLY', $config['planner_only']);
# define('USE_ICS', BASE . DS . 'holiday.ics');

if (defined('USE_ICS')) {
    Events::loadFromICS(USE_ICS);
}

$generator = new PDFGenerator($config, W, H);
$generator->generate(new PlannerGenerator($title, $subtitle));
$generator->output('output/' . $filename);
