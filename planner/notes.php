<?php
function section_note_index(TCPDF $pdf, int $page_count): void
{
    $margin = 2;
    $index_number_line_height = 2.2;
    $pages = ceil($page_count / 40);

    $pdf->setFont(Loc::_('fonts.font2'));

    for ($page = 1; $page <= $pages; $page++) {
        $pdf->AddPage();
        $pdf->setLink(Links::series($pdf, 'note-index', $page));

        Templates::draw('section-index-header', Loc::_('note-index'));
        if ($pages > 1) {
            $pdf->setFontSize(Size::fontSize(planner_header_height(), 1.5));
            $pdf->setTextColor(...Colors::g(15));
            $pdf->setAbsXY(planner_header_margin(), PX100);
            $pdf->Cell(W - 2 * planner_header_margin(), planner_header_height(), Loc::_('paginate', page: $page, pages: $pages), align: 'R');
        }

        [$start_x, $start_y, $width, $height] = planner_size_dimensions($margin);
        $half_width = ($width - $margin) / 2;
        $size = ($height - 2 * $margin) / 20;

        Templates::draw('section-index-body', $margin, $start_x, $start_y, $width, $height, 20);

        $pdf->setTextColor(...Colors::g(0));
        $pdf->setFontSize(Size::fontSize($size, $index_number_line_height));

        [$offset_x, $offset_y] = planner_calculate_marking_offset($half_width, $height, 'rules', $size);

        $x1 = $start_x + $offset_x;
        $x2 = $x1 + $margin + $half_width;
        $y = $start_y + $offset_y;
        $base_number = ($page - 1) * 40;
        for ($i = 1; $i <= 20; $i++) {
            $pdf->setAbsXY($x1, $y);
            $pdf->Cell($half_width, $size, sprintf('%d.', $base_number + $i));
            $pdf->Link($x1, $y, $half_width, $size, Links::series($pdf, 'note', $base_number + $i));
            $pdf->setAbsXY($x2, $y);
            $pdf->Cell($half_width, $size, sprintf('%d.', $base_number + 20 + $i));
            $pdf->Link($x2, $y, $half_width, $size, Links::series($pdf, 'note', $base_number + 20 + $i));

            $y += $size;
        }

        planner_nav_sub($pdf);
        planner_nav_main($pdf, 1);
    }
}

function section_note_pages(TCPDF $pdf, int $pages, $note_style): void
{
    $margin = 2;

    $pdf->setTextColor(...Colors::g(15));
    $pdf->setFontSize(Size::fontSize(planner_header_height(), 1.5));

    for ($page = 1; $page <= $pages; $page++) {
        $pdf->AddPage();
        $pdf->setLink(Links::series($pdf, 'note', $page));

        $index_page = ceil($page / 40);

        Templates::draw('section-page-header', Loc::_('note'));
        $pdf->setAbsXY(0, PX100);
        $pdf->Cell(section_header_indent() - planner_header_margin(), planner_header_height(), sprintf('%03d', $page), align: 'R');
        $pdf->Link(0, PX100, section_header_indent(), planner_header_height(), Links::series($pdf, 'note-index', $index_page));


        Templates::draw('planner-note', $note_style, ...planner_size_dimensions($margin));

        planner_nav_sub($pdf);
        planner_nav_main($pdf, 1);
    }
}

// Planner note section
function planner_note_template(TCPDF $pdf, string $note_style, float $x, float $y, float $w, float $h): void
{
    $size = $note_style === 'dot' || $note_style === 'table' ? 5 : 6.5;
    planner_draw_note_area($pdf, $x, $y, $w, $h, $note_style, $size);
}

Templates::register('planner-note', 'planner_note_template');

function planner_quarterly_note(TCPDF $pdf, Quarter $quarter, string $note_style): void
{
    [$tabs, $tab_targets] = planner_make_quarterly_tabs($pdf, $quarter);

    $pdf->AddPage();
    $pdf->setLink(Links::quarterly($pdf, $quarter, 'note'));

    planner_quarterly_header($pdf, $quarter, 2, $tabs);
    link_tabs($pdf, $tabs, $tab_targets);

    Templates::draw('planner-note', $note_style, ...planner_size_dimensions(2));

    planner_nav_sub($pdf, ...$quarter->months);
    planner_nav_main($pdf, 0);
}

function planner_monthly_note(TCPDF $pdf, Month $month, string $note_style): void
{
    [$tabs, $tab_targets] = planner_make_monthly_tabs($pdf, $month);

    $pdf->AddPage();
    $pdf->setLink(Links::monthly($pdf, $month, 'note'));

    planner_monthly_header($pdf, $month, 2, $tabs);
    link_tabs($pdf, $tabs, $tab_targets);

    Templates::draw('planner-note', $note_style, ...planner_size_dimensions(2));

    planner_nav_sub($pdf, $month);
    planner_nav_main($pdf, 0);
}

function planner_weekly_note(TCPDF $pdf, Week $week, string $note_style): void
{
    [$tabs, $tab_targets] = planner_make_weekly_tabs($pdf, $week);

    $pdf->AddPage();
    $pdf->setLink(Links::weekly($pdf, $week, 'note'));

    planner_weekly_header($pdf, $week, 2, $tabs);
    link_tabs($pdf, $tabs, $tab_targets);

    [$start_x, $start_y, $width, $height] = planner_size_dimensions(2);
    $start_y += planner_weekly_extra_day_link_height();
    $height -= planner_weekly_extra_day_link_height();

    Templates::draw('planner-note', $note_style, $start_x, $start_y, $width, $height);

    planner_nav_sub($pdf, $week->days[0]->month(), $week->days[6]->month());
    planner_nav_main($pdf, 0);
}

function planner_daily_note(TCPDF $pdf, Day $day, string $note_style): void
{
    [$tabs, $tab_targets] = planner_make_daily_tabs($pdf, $day);

    $pdf->AddPage();
    $pdf->setLink(Links::daily($pdf, $day, 'note'));

    planner_daily_header($pdf, $day, 3, $tabs);
    link_tabs($pdf, $tabs, $tab_targets);

    Templates::draw('planner-note', $note_style, ...planner_size_dimensions(2));

    planner_nav_sub($pdf, $day->month());
    planner_nav_main($pdf, 0);
}
