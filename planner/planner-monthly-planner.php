<?php
function planner_monthly_planner_template(TCPDF $pdf, string $note_style, float $margin): void
{
    [$start_x, $start_y, $width, $height] = planner_size_dimensions($margin);
    $size = $height / 33;

    planner_draw_note_area($pdf, $start_x, $start_y, $width, $height, $note_style, $size);
}

Templates::register('planner-monthly-planner', 'planner_monthly_planner_template');

function planner_monthly_planner(TCPDF $pdf, Month $month, string $note_style): void
{
    [$tabs, $tab_targets] = planner_make_monthly_tabs($pdf, $month);

    $pdf->AddPage();
    $pdf->setLink(Links::monthly($pdf, $month, 'planner'));

    planner_monthly_header($pdf, $month, 1, $tabs);
    link_tabs($pdf, $tabs, $tab_targets);

    $margin = 2;
    $line_height = 2;

    Templates::draw('planner-monthly-planner', $note_style, $margin);

    [$start_x, $start_y, $width, $height] = planner_size_dimensions($margin);
    $size = $height / 33;
    [$offset_x, $offset_y] = planner_calculate_marking_offset($width, $height, $note_style, $size);

    $pdf->setFont(Loc::_('fonts.font2'));
    $pdf->setFontSize(Size::fontSize($size, $line_height));
    $pdf->setTextColor(...Colors::g(6));

    $x = $start_x + $offset_x;
    $y = $start_y + $offset_y + $size;

    foreach ($month->days as $day) {
        $pdf->setAbsXY($x, $y);
        $pdf->Cell($size * 2, $size, Loc::_(sprintf('weekday.m%d', $day->dow)), align: 'L', valign: 'B');
        $pdf->Cell($size, $size, $day->day, align: 'R', valign: 'B');
        $pdf->Link($x, $y, $size * 3, $size, Links::daily($pdf, $day));

        $y += $size;
    }

    planner_nav_sub($pdf, $month);
    planner_nav_main($pdf, 0);
}