<?php
function planner_weekly_extra_day_link_height(): float
{
    return 6;
}

function planner_weekly_header_template(TCPDF $pdf, float $y, float $h, int $active, float $year_margin, float $month_size, int $months, array $tabs, float $day_link_height, bool $day_nav): void
{
    $pdf->setLineStyle([
        'width' => 0.2,
        'cap' => 'butt',
        'color' => Colors::g(15)
    ]);
    $pdf->setFont(Loc::_('fonts.font2'));
    $pdf->setFontSize(Size::fontSize($h, 1.5));

    $pdf->setFillColor(...Colors::g(0));
    $pdf->Rect(0, $y, W, $h, 'F');

    $pdf->setFillColor(...Colors::g(15));
    $pdf->Rect($year_margin - 0.1, $y, 0.2, $h, 'F');

    for ($i = 1; $i <= $months; $i++) {
        $pdf->Rect($year_margin + $i * $month_size - 0.1, $y, 0.2, $h, 'F');
    }

    draw_tabs($pdf, $active, $tabs);

    if ($day_nav) {
        // Day link section
        $per_day = (W - 4) / 7;
        $pdf->setLineStyle([
            'width' => 0.1,
            'cap' => 'butt',
            'color' => Colors::g(0)
        ]);
        for ($i = 0; $i < 7; $i++) {
            $pdf->RoundedRect(2 + $i * $per_day + 0.5, $y + $h + 0.5, $per_day - 1, $day_link_height - 1, 1, '1111', 'S');
        }
    }
}

Templates::register('planner-weekly-header', 'planner_weekly_header_template');

function planner_weekly_make_day_str(Day $day): string
{
    $weekday_short = Loc::_(sprintf('weekday.s%d', $day->dow));
    $date = $day->day;
    return Loc::_('short-date', weekday: $weekday_short, date: $date);
}

function planner_weekly_header(TCPDF $pdf, Week $week, int $active, array $tabs, bool $day_nav = true): void
{
    $year_margin = 15;
    $month_size = 12;
    $margin = planner_header_margin();
    $height = planner_header_height();

    $day_link_height = planner_weekly_extra_day_link_height();
    $day_link_line_height = 2.5;

    $month_count = 1;

    $fd_mon = $week->days[0]->month();
    $ld_mon = $week->days[6]->month();

    if (!$fd_mon->active()) {
        $fd_mon = $ld_mon;
    }

    if (0 !== $fd_mon->compare($ld_mon)) {
        $month_count = 2;
    }

    Templates::draw('planner-weekly-header', PX100, $height, $active, $year_margin, $month_size, $month_count, $tabs, $day_link_height, $day_nav);

    $pdf->setFont(Loc::_('fonts.font2'));
    $pdf->setFontSize(Size::fontSize($height, 1.5));
    $pdf->setTextColor(...Colors::g(15));

    $pdf->setAbsXY($x = 0, PX100);
    $pdf->Cell($year_margin - $margin, $height, strval($week->year), align: 'R');
    $pdf->Link(0, PX100, $year_margin, $height, Links::yearly($pdf, Year::make($week->year)));
    $pdf->setAbsXY($x = $year_margin, PX100);
    $pdf->Cell($month_size, $height, Loc::_(sprintf('month.s%02d', $fd_mon->month)), align: 'C');
    $pdf->Link($x, PX100, $month_size, $height, Links::monthly($pdf, $fd_mon));
    if ($month_count > 1) {
        $pdf->setAbsXY($x += $month_size, PX100);
        $pdf->Cell($month_size, $height, Loc::_(sprintf('month.s%02d', $ld_mon->month)), align: 'C');
        $pdf->Link($x, PX100, $month_size, $height, Links::monthly($pdf, $ld_mon));
    }
    $pdf->setAbsXY($x + $month_size + $margin, PX100);
    $pdf->Cell(W, $height, Loc::_('week.number_s', week: $week->week), align: 'L');

    if ($day_nav) {
        // Daily text and link
        $per_day = (W - 4) / 7;
        $x = 2;
        $y = PX100 + $height;
        $pdf->setFontSize(Size::fontSize($day_link_height, $day_link_line_height));
        $pdf->setTextColor(...Colors::g(0));
        $pdf->setAbsXY($x, $y);
        foreach ($week->days as $day) {
            $pdf->Cell($per_day, $day_link_height, planner_weekly_make_day_str($day), align: 'C');
            $pdf->Link($x, $y, $per_day, $day_link_height, Links::daily($pdf, $day));
            $x += $per_day;
        }
    }
}

function planner_weekly_calender_size_dimension($margin): array
{
    [$start_x, $start_y, $width, $height] = planner_size_dimensions($margin);
    return [$start_x, $start_y, $width, $height];
}

function planner_weekly_template(TCPDF $pdf, float $margin, float $left_size, float $line_size): void
{
    [$start_x, $start_y, $width, $height] = planner_weekly_calender_size_dimension($margin);

    $per_row = $height / 7;

    $lines = round($per_row / $line_size);
    $line_size = $per_row / $lines;
    planner_draw_note_area($pdf, $start_x + $left_size, $start_y, $width - $left_size, $height, 'rule', $line_size);

    $pdf->setLineStyle([
        'width' => 0.2,
        'cap' => 'butt',
        'color' => Colors::g(0)
    ]);
    $pdf->setFillColor(...Colors::g(12));
    $pdf->setTextColor(...Colors::g(0));

    $pdf->Rect($start_x, $start_y, $left_size, $height, 'F');
    $midpoint = $start_x + $left_size + ($width - $left_size) / 2;
    $pdf->Line($midpoint, $start_y, $midpoint, $start_y + $height);

    for ($i = 0; $i < 7; $i++) {
        if ($i !== 0) {
            $pdf->Line($start_x + $left_size, $start_y + $i * $per_row, $start_x + $width, $start_y + $i * $per_row);
        }
    }
}

Templates::register('planner-weekly', 'planner_weekly_template');

function planner_make_weekly_tabs(TCPDF $pdf, Week $week): array
{
    $tabs = [
        ['name' => Loc::_('cal')],
        ['name' => Loc::_('task')],
        ['name' => Loc::_('note')],
    ];
    $tab_targets = [
        Links::weekly($pdf, $week),
        Links::weekly($pdf, $week, 'task'),
        Links::weekly($pdf, $week, 'note'),
    ];

    planner_tabs_calculate_size($pdf, $tabs);
    return [$tabs, $tab_targets];
}

function planner_weekly(TCPDF $pdf, Week $week): void
{
    [$tabs, $tab_targets] = planner_make_weekly_tabs($pdf, $week);

    $pdf->AddPage();
    $pdf->setLink(Links::weekly($pdf, $week));

    $margin = 2;
    $line_size = 6;
    $line_height = 1.5;
    $left_size = 6;

    Templates::draw('planner-weekly', $margin, $left_size, $line_size);

    [$start_x, $start_y, $width, $height] = planner_weekly_calender_size_dimension($margin);
    $per_row = $height / 7;

    $pdf->setTextColor(...Colors::g(0));
    $pdf->setFillColor(...Colors::g(12));
    $pdf->setFont(Loc::_('fonts.font2'));
    $pdf->setFontSize(Size::fontSize($line_size / 2, $line_height));

    foreach ($week->days as $day) {
        $pdf->setAbsXY($start_x, $start_y);
        $pdf->Cell($left_size, $line_size / 2, Loc::_(sprintf('weekday.m%d', $day->dow)), ln: 2, align: 'C', valign: 'C');
        $pdf->Cell($left_size, $line_size / 2, strval($day->day), align: 'C', valign: 'C');

        $pdf->Link($start_x, $start_y, $left_size, $per_row, Links::daily($pdf, $day));

        $start_y += $per_row;
    }

    planner_weekly_header($pdf, $week, 0, $tabs, false);
    link_tabs($pdf, $tabs, $tab_targets);

    planner_nav_sub($pdf, $week->days[0]->month(), $week->days[6]->month());
    planner_nav_main($pdf, 0);
}
