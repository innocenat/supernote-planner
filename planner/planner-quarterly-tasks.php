<?php
function planner_quarterly_task_template(TCPDF $pdf, float $margin, float $line_size): void
{
    [$start_x, $start_y, $width, $height] = planner_size_dimensions($margin);

    $half_width = ($width - $margin) / 2;

    planner_draw_note_area($pdf, $start_x, $start_y, $half_width, $height, 'checkbox', $line_size);
    planner_draw_note_area($pdf, $start_x + $margin + $half_width, $start_y, $half_width, $height, 'checkbox', $line_size);
}

Templates::register('planner-quarterly-task', 'planner_quarterly_task_template');

function planner_quarterly_task(TCPDF $pdf, Quarter $quarter): void
{
    [$tabs, $tab_targets] = planner_make_quarterly_tabs($pdf, $quarter);

    $pdf->AddPage();
    $pdf->setLink(Links::quarterly($pdf, $quarter, 'task'));

    planner_quarterly_header($pdf, $quarter, 1, $tabs);
    link_tabs($pdf, $tabs, $tab_targets);

    $margin = 2;
    $line_size = 6;

    Templates::draw('planner-quarterly-task', $margin, $line_size);

    planner_nav_sub($pdf, ...$quarter->months);
    planner_nav_main($pdf, 0);
}
