<?php
function planner_weekly_task_template(TCPDF $pdf, float $margin, float $line_size): void
{
    [$start_x, $start_y, $width, $height] = planner_size_dimensions($margin);
    $start_y += planner_weekly_extra_day_link_height();
    $height -= planner_weekly_extra_day_link_height();

    $half_width = ($width - $margin) / 2;

    planner_draw_note_area($pdf, $start_x, $start_y, $half_width, $height, 'checkbox', $line_size);
    planner_draw_note_area($pdf, $start_x + $margin + $half_width, $start_y, $half_width, $height, 'checkbox', $line_size);
}

Templates::register('planner-weekly-task', 'planner_weekly_task_template');

function planner_weekly_task(TCPDF $pdf, Week $week): void
{
    [$tabs, $tab_targets] = planner_make_weekly_tabs($pdf, $week);

    $pdf->AddPage();
    $pdf->setLink(Links::weekly($pdf, $week, 'task'));

    planner_weekly_header($pdf, $week, 1, $tabs);
    link_tabs($pdf, $tabs, $tab_targets);

    $margin = 2;
    $line_size = 6;

    Templates::draw('planner-weekly-task', $margin, $line_size);

    planner_nav_sub($pdf, $week->days[0]->month(), $week->days[6]->month());
    planner_nav_main($pdf, 0);
}