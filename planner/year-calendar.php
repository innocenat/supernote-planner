<?php

function planner_yearly_make_quarter_tabs(TCPDF $pdf, Year $year): array
{
    $tabs = [];
    $tab_targets = [];
    $actives = [];

    foreach ($year->quarters as $quarter) {
        $tabs[] = ['name' => Loc::_(sprintf('quarter.q%d', $quarter->quarter))];
        $tab_targets[] = Links::quarterly($pdf, $quarter);
        if ($quarter->active()) {
            $actives[] = $quarter->quarter - 1;
        }
    }

    planner_tabs_calculate_size($pdf, $tabs);
    return [$tabs, $tab_targets, $actives];
}

function yearly_calendar_header(TCPDF $pdf, Year $year): void
{
    $year_margin = 15;
    $margin = planner_header_margin();
    $height = planner_header_height();

    $pdf->setFont(Loc::_('fonts.font2'));
    $pdf->setFontSize(Size::fontSize($height, 1.5));
    $pdf->setTextColor(...Colors::g(15));
    $pdf->setFillColor(...Colors::g(0));


    $pdf->Rect(0, PX100, W, $height, 'F');
    $pdf->setAbsXY(0, PX100);
    $pdf->Cell($year_margin - $margin, $height, strval($year->year), align: 'R');

    // Quarterly link
    [$tabs, $tab_targets, $actives] = planner_yearly_make_quarter_tabs($pdf, $year);
    draw_tabs($pdf, $actives, $tabs);
    link_tabs($pdf, $tabs, $tab_targets);
}

function yearly_month_calendar(TCPDF $pdf, Month $month, bool $start_monday, float $x, float $y, float $w, float $h): void
{
    $day_line_height = 1.6;
    $month_line_height = 1.5;

    $weeks = count($month->weeks);
    $active = $month->active();

    $x_step = $w / 8;
    $y_step = $h / 8;

    $pdf->setFillColor(...Colors::$g[12]);
    $pdf->setLineStyle([
        'width' => 0.1,
        'cap' => 'butt',
        'color' => Colors::g(12)
    ]);
    $pdf->setFont(Loc::_('fonts.font2'));
    $pdf->setTextColor(...($active ? Colors::g(0) : Colors::g(6)));
    // Lines
    $pdf->Line($x, $y + $y_step, $x + $w, $y + $y_step);
    $pdf->Line($x, $y + 2 * $y_step, $x + $w, $y + 2 * $y_step);

    // Block
    $pdf->Rect($x, $y + $y_step, $x_step, $y_step * (1 + $weeks), 'F');

    // Month name
    $pdf->setFontSize(Size::fontSize($y_step, $month_line_height));

    $pdf->setAbsXY($x, $y);
    $pdf->Cell(
        8 * $x_step,
        $y_step,
        Loc::_(sprintf('month.l%02d', $month->month)),
        align: 'C'
    );
    $pdf->Link($x, $y, 8 * $x_step, $y_step, Links::monthly($pdf, $month));

    // Header
    $pdf->setFontSize(Size::fontSize($y_step, $day_line_height));

    $pdf->setAbsXY($x, $y += $y_step);
    $pdf->Cell($x_step, $y_step, Loc::_('week.s'), align: 'C');

    $weekday = $start_monday ? 1 : 0;
    for ($i = 0; $i < 7; $i++) {
        $pdf->Cell(
            $x_step,
            $y_step,
            Loc::_(sprintf('weekday.s%d', $i + $weekday)),
            align: 'C'
        );
    }

    $y += $y_step;
    foreach ($month->weeks as $week) {
        $pdf->setAbsXY($x, $y);

        // Week number
        $pdf->Cell($x_step, $y_step, $week->week, align: 'C');
        if ($active) {
            $pdf->Link($x, $y, $x_step, $y_step, Links::weekly($pdf, $week));
        }

        foreach ($week->days as $day) {
            if ($month->hasDay($day)) {
                $pdf->Cell($x_step, $y_step, $day->day, align: 'C');
                if ($active) {
                    $pdf->Link(
                        $pdf->getAbsX() - $x_step,
                        $y,
                        $x_step,
                        $y_step,
                        Links::daily($pdf, $day)
                    );
                }
            } else {
                $pdf->Cell($x_step, $y_step);
            }
        }

        $y += $y_step;
    }
}

function yearly_calendar(TCPDF $pdf, Year $year, bool $start_monday): void
{
    $margin = 2;

    $pdf->AddPage();
    $pdf->setLink(Links::yearly($pdf, $year));

    yearly_calendar_header($pdf, $year);

    // Actual calendar
    [$start_x, $start_y, $width, $height] = planner_size_dimensions($margin);

    // Quarterly arrangement
    $per_row = ($height) / 4 - $margin;
    $per_col = ($width + $margin) / 3 - $margin;

    for ($i = 0; $i < 12; $i++) {
        $month = $year->months[$i];

        $position_x = ($month->month - 1) % 3;
        $position_y = floor(($month->month - 1) / 3);

        $x = $start_x + $position_x * ($per_col + $margin);
        $y = $start_y + $position_y * ($per_row + $margin);

        yearly_month_calendar($pdf, $month, $start_monday, $x, $y, $per_col, $per_row);
    }

    planner_nav_sub($pdf);
    planner_nav_main($pdf, 0);
}
