<?php

function planner_quarterly_header_template(TCPDF $pdf, float $y, float $h, int $active, float $year_margin, array $tabs): void
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

    draw_tabs($pdf, $active, $tabs);
}

Templates::register('planner-quarterly-header', 'planner_quarterly_header_template');

function planner_quarterly_header(TCPDF $pdf, Quarter $quarter, int $active, array $tabs): void
{
    $year_margin = 15;
    $margin = planner_header_margin();
    $height = planner_header_height();

    Templates::draw('planner-quarterly-header', PX100, $height, $active, $year_margin, $tabs);

    $pdf->setFont(Loc::_('fonts.font2'));
    $pdf->setFontSize(Size::fontSize($height, 1.5));
    $pdf->setTextColor(...Colors::g(15));

    $pdf->setAbsXY(0, PX100);
    $pdf->Cell($year_margin - $margin, $height, strval($quarter->year), align: 'R');
    $pdf->Link(0, PX100, $year_margin, $height, Links::yearly($pdf, $quarter->year()));
    $pdf->setAbsXY($year_margin + $margin, PX100);
    $pdf->Cell(W, $height, Loc::_(sprintf('quarter.q%d', $quarter->quarter)), align: 'L');
}

function planner_quarterly_template(TCPDF $pdf, float $month_header_height, float $day_width): void
{
    [$start_x, $start_y, $width, $height] = planner_size_dimensions(2);
    $margin = planner_header_margin();

    $pdf->setLineStyle([
        'width' => 0.1,
        'cap' => 'butt',
        'color' => Colors::g(0)
    ]);

    $per_row = ($height - $month_header_height) / 32;
    $per_col = ($width - 2*$margin) / 3;

    for ($i = 0; $i < 31; $i++) {
        $x = $start_x;
        $y = $start_y+ $month_header_height + $i * $per_row;
        $pdf->Line($x, $y, $x + $per_col - 1, $y);
        $x += $per_col;
        $pdf->Line($x, $y, $x + $per_col - 1, $y);
        $x += $per_col;
        $pdf->Line($x, $y, $x + $per_col - 1, $y);
    }
}

Templates::register('planner-quarterly', 'planner_quarterly_template');

function planner_make_quarterly_tabs(TCPDF $pdf, Quarter $quarter): array
{
    $tabs = [
        ['name' => Loc::_('planner')],
        ['name' => Loc::_('task')],
        ['name' => Loc::_('note')],
    ];
    $tab_targets = [
        Links::quarterly($pdf, $quarter),
        Links::quarterly($pdf, $quarter, 'task'),
        Links::quarterly($pdf, $quarter, 'note'),
    ];

    planner_tabs_calculate_size($pdf, $tabs);
    return [$tabs, $tab_targets];
}

function planner_quarterly(TCPDF $pdf, Quarter $quarter): void
{
    $month_header_height = 5;
    $month_header_line_height = 1.5;
    $margin = planner_header_margin();
    $day_width = 5;

    [$tabs, $tab_targets] = planner_make_quarterly_tabs($pdf, $quarter);

    $pdf->AddPage();
    $pdf->setLink(Links::quarterly($pdf, $quarter));

    planner_quarterly_header($pdf, $quarter, 0, $tabs);
    link_tabs($pdf, $tabs, $tab_targets);

    Templates::draw('planner-quarterly', $month_header_height, $day_width);

    [$start_x, $start_y, $width, $height] = planner_size_dimensions(2);

    $per_row = ($height - $month_header_height) / 32;
    $per_col = ($width - 2*$margin) / 3;

    $pdf->setFillColor(...Colors::g(12));
    $pdf->setTextColor(...Colors::g(0));
    $pdf->setFont(Loc::_('fonts.font2'));
    $pdf->setFontSize(Size::fontSize($month_header_height, $month_header_line_height));
    $pdf->setAbsXY($start_x, $start_y);
    $i = 0;
    foreach ($quarter->months as $month) {
        $pdf->Cell($per_col, $month_header_height, Loc::_(sprintf('month.l%02d', $month->month)), align: 'C', link: Links::monthly($pdf, $month));
    }

    $start_y += $month_header_height;

    $pdf->setTextColor(...Colors::g(6));
    $pdf->setFontSize(Size::fontSize($per_row, 2.5));

    foreach ($quarter->months as $month) {
        $y = $start_y;
        $pdf->setAbsXY($start_x, $start_y);
        foreach ($month->days as $day) {
            $pdf->setAbsX($start_x);

            if (intval($day->dow) === 0 || intval($day->dow) === 7) {
                // Shade sunday
                $pdf->Rect($start_x, $y, $per_col - 1, $per_row, 'F');
            }

            $pdf->Cell(2, $per_row, Loc::_(sprintf('weekday.s%d', $day->dow)), align: 'C');
            $pdf->Cell(2, $per_row, strval($day->day));
            $pdf->Ln();

            $pdf->Link($start_x, $y, $per_col, $per_row, Links::daily($pdf, $day));
            $y += $per_row;
        }

        $start_x += $per_col;
    }


    planner_nav_sub($pdf, ...$quarter->months);
    planner_nav_main($pdf, 0);
}
