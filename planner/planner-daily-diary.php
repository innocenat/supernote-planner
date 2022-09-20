<?php
function planner_daily_diary_template(TCPDF $pdf, float $margin, float $line_size, float $line_height): void
{
    [$start_x, $start_y, $width, $height] = planner_size_dimensions($margin);
    $start_y += 0.6 * $line_size;
    $height -= 0.6 * $line_size;

    $title_offset = 0.4 * $line_size;
    $title_height = 0.6 * $line_size;

    $pdf->setTextColor(...Colors::g(0));
    $pdf->setFillColor(...Colors::g(12));
    $pdf->setFont(Loc::_('fonts.font2'));
    $pdf->setFontSize(Size::fontSize($line_size, $line_height));

    $half_width = ($width - $margin) / 2;

    [$offset_x, $offset_y] = planner_draw_note_area($pdf, $start_x, $start_y, $half_width, $height, 'rule', $line_size);
    $pdf->setAbsXY($x = $start_x + $offset_x, $y = $start_y + $offset_y - $line_size);
    foreach (['my-goals', 'daily-grateful', 'daily-best-things'] as $key) {
        $pdf->setAbsXY($x, $y + $title_offset);
        $pdf->Rect($x, $y + $title_offset, $half_width, $title_height, 'F');
        $pdf->Cell($half_width, $title_height, Loc::_($key));
        $pdf->setAbsXY($x, $y += $line_size);
        $pdf->Cell($half_width, $line_size, '1.');
        $pdf->setAbsXY($x, $y += 2 * $line_size);
        $pdf->Cell($half_width, $line_size, '2.');
        $pdf->setAbsXY($x, $y += 2 * $line_size);
        $pdf->Cell($half_width, $line_size, '3.');
        $pdf->setAbsXY($x, $y += 2 * $line_size);
    }

    [$offset_x, $offset_y] = planner_draw_note_area($pdf, $start_x + $margin + $half_width, $start_y, $half_width, $height, 'rule', $line_size);
    $pdf->setAbsXY($x = $start_x + $margin + $half_width + $offset_x, $y = $start_y + $offset_y - $line_size + $title_offset);
    $pdf->Rect($x, $y, $half_width, $title_height, 'F');
    $pdf->Cell($half_width, $title_height, Loc::_('daily-log'));
}

Templates::register('planner-daily-diary', 'planner_daily_diary_template');

function planner_daily_diary(TCPDF $pdf, Day $day): void
{
    [$tabs, $tab_targets] = planner_make_daily_tabs($pdf, $day);

    $pdf->AddPage();
    $pdf->setLink(Links::daily($pdf, $day, 'diary'));

    planner_daily_header($pdf, $day, 2, $tabs);
    link_tabs($pdf, $tabs, $tab_targets);

    $margin = 2;
    $line_size = 6;
    $line_height = 2.5;

    Templates::draw('planner-daily-diary', $margin, $line_size, $line_height);

    planner_nav_sub($pdf, $day->month());
    planner_nav_main($pdf, 0);
}