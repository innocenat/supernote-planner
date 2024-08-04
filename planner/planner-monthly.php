<?php
function planner_monthly_header_template(TCPDF $pdf, float $y, float $h, int $active, float $year_margin, float $quarter_margin, array $tabs): void
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
    $pdf->Rect($year_margin + $quarter_margin - 0.1, $y, 0.2, $h, 'F');

    draw_tabs($pdf, $active, $tabs);
}

Templates::register('planner-monthly-header', 'planner_monthly_header_template');

function planner_monthly_header(TCPDF $pdf, Month $month, int $active, array $tabs): void
{
    $year_margin = 15;
    $quarter_margin = 10;
    $margin = planner_header_margin();
    $height = planner_header_height();

    Templates::draw('planner-monthly-header', PX100, $height, $active, $year_margin, $quarter_margin, $tabs);

    $pdf->setFont(Loc::_('fonts.font2'));
    $pdf->setFontSize(Size::fontSize($height, 1.5));
    $pdf->setTextColor(...Colors::g(15));

    $pdf->setAbsXY(0, PX100);
    $pdf->Cell($year_margin - $margin, $height, strval($month->year), align: 'R');
    $pdf->Link(0, PX100, $year_margin, $height, Links::yearly($pdf, $month->year()));
    $pdf->setAbsXY($year_margin, PX100);
    $pdf->Cell($quarter_margin, $height, Loc::_(sprintf('quarter.q%d', $month->quarter)), align: 'C');
    $pdf->Link($year_margin, PX100, $quarter_margin, $height, Links::quarterly($pdf, $month->quarter()));
    $pdf->setAbsXY($year_margin + $quarter_margin + $margin, PX100);
    $pdf->Cell(W, $height, Loc::_(sprintf('month.l%02d', $month->month)), align: 'L');
}

function planner_monthly_template(TCPDF $pdf, int $rows, bool $monday_start, float $dow_header_height, float $dow_line_height, float $weekly_size): void
{
    [$start_x, $start_y, $width, $height] = planner_size_dimensions(2);

    $pdf->setLineStyle([
        'width' => 0.1,
        'cap' => 'butt',
        'color' => Colors::g(12)
    ]);
    $pdf->setFillColor(...Colors::g(12));
    $pdf->setTextColor(...Colors::g(0));
    $pdf->setFont(Loc::_('fonts.font2'));
    $pdf->setFontSize(Size::fontSize($dow_header_height, $dow_line_height));

    $per_row = ($height - $dow_header_height) / 6;
    $per_col = ($width - $weekly_size) / 7;

    // DOW header
    $pdf->setAbsXY($start_x + $weekly_size, $start_y);
    $weekday = $monday_start ? 1 : 0;
    for ($i = 0; $i < 7; $i++) {
        $pdf->Cell(
            $per_col,
            $dow_header_height,
            Loc::_(sprintf('weekday.m%d', $i + $weekday)),
            align: 'C',
            valign: 'B'
        );
    }

    // Rect (weekly marker)
    $pdf->Rect(
        $start_x,
        $start_y + $dow_header_height,
        $weekly_size,
        $per_row * $rows,
        'F'
    );

    // Table
    for ($i = 0; $i < 7; $i++) {
        $pdf->Line(
            $start_x + $weekly_size + $i * $per_col,
            $start_y + $dow_header_height,
            $start_x + $weekly_size + $i * $per_col,
            $start_y + $dow_header_height + $rows * $per_row
        );
    }
    for ($i = 0; $i <= $rows; $i++) {
        $pdf->Line(
            $start_x,
            $start_y + $dow_header_height + $i * $per_row,
            $start_x + $width,
            $start_y + $dow_header_height + $i * $per_row
        );
    }

    // Table cells
    $circle_size = 1;
    $square_space = 1;
    $square_size = ($per_col - 5 * $square_space) / 4;
    for ($i = 0; $i < 7; $i++) {
        for ($j = 0; $j < $rows; $j++) {
            $x = $start_x + $weekly_size + $i * $per_col;
            $y = $start_y + $dow_header_height + $j * $per_row;

            // Circle marker
            $pdf->Circle($x + $per_col - 2 * $circle_size, $y + 2 * $circle_size, $circle_size);

            // Square marker
            for ($k = 0; $k < 4; $k++) {
                $pdf->Rect($x + $square_space + $k * ($square_size + $square_space), $y + $per_row - $square_size - $square_space, $square_size, $square_size);
            }
        }
    }
}

Templates::register('planner-monthly', 'planner_monthly_template');

function planner_make_monthly_tabs(TCPDF $pdf, Month $month): array
{
    $tabs = [
        ['name' => Loc::_('cal')],
        ['name' => Loc::_('planner')],
        ['name' => Loc::_('note')],
    ];
    $tab_targets = [
        Links::monthly($pdf, $month),
        Links::monthly($pdf, $month, 'planner'),
        Links::monthly($pdf, $month, 'note'),
    ];

    planner_tabs_calculate_size($pdf, $tabs);
    return [$tabs, $tab_targets];
}

function planner_monthly(TCPDF $pdf, Month $month, bool $monday_start): void
{
    $dow_header_height = 3;
    $dow_header_line_height = 1.2;
    $weekly_size = 6;
    $week_font_size = 6;
    $day_font_size = 8;
    $event_font_size = 3;
    $event_line_height = 4;
    $event_bottom_margin = 3;

    [$tabs, $tab_targets] = planner_make_monthly_tabs($pdf, $month);

    $pdf->AddPage();
    $pdf->setLink(Links::monthly($pdf, $month));

    planner_monthly_header($pdf, $month, 0, $tabs);
    link_tabs($pdf, $tabs, $tab_targets);

    $weeks = count($month->weeks);
    Templates::draw('planner-monthly', $weeks, $monday_start, $dow_header_height, $dow_header_line_height, $weekly_size);

    [$start_x, $start_y, $width, $height] = planner_size_dimensions(2);

    $per_row = ($height - $dow_header_height) / 6;
    $per_col = ($width - $weekly_size) / 7;

    $y = $start_y + $dow_header_height;

    $pdf->setTextColor(...Colors::g($last_text_color = 0));
    foreach ($month->weeks as $week) {
        $pdf->setAbsXY($x = $start_x, $y);
        $pdf->setFontSize($week_font_size);
        $pdf->Cell($weekly_size, $per_row, $week->week, align: 'C');
        $pdf->Link($x, $y, $weekly_size, $per_row, Links::weekly($pdf, $week));
        $x += $weekly_size;

        $pdf->setFontSize($day_font_size);
        foreach ($week->days as $day) {
            if ($month->hasDay($day)) {
                if ($last_text_color !== 0) {
                    $pdf->setTextColor(...Colors::g($last_text_color = 0));
                }
            } else {
                if ($last_text_color !== 6) {
                    $pdf->setTextColor(...Colors::g($last_text_color = 6));
                }
            }

            $pdf->Cell($per_col, $per_row, $day->day, align: 'L', valign: 'T');
            $pdf->Link($x, $y, $per_col, $per_row, Links::daily($pdf, $day));

            $x += $per_col;
        }

        $y += $per_row;
    }

    // Events
    $y = $start_y + $dow_header_height;
    $pdf->setTextColor(...Colors::g(0));
    foreach ($month->weeks as $week) {
        $pdf->setFontSize($event_font_size);
        $x = $start_x;
        $x += $weekly_size;

        foreach ($week->days as $day) {
            if ($month->hasDay($day)) {
                // Events
                $events = Events::getEventOnDay($day);
                if (count($events) > 0) {
                    $pdf->setAbsXY($x, $y + $per_row - $event_bottom_margin - $event_line_height * count($events));
                    foreach ($events as $event) {
                        $pdf->Cell($per_col, $event_line_height, $event, stretch: 1);
                    }
                }
            }

            $x += $per_col;
        }

        $y += $per_row;
    }


    planner_nav_sub($pdf, $month);
    planner_nav_main($pdf, 0);
}
