<?php
function section_event_index_template(TCPDF $pdf, float $margin, float $x, float $y, float $w, float $h, $size): void
{
    planner_draw_note_area($pdf, $x, $y, $w, $h, 'event', $size);
}

Templates::register('section-event-index', 'section_event_index_template');

function _section_print_text_and_line(TCPDF $pdf, $margin, $x, $y, $w, $h, $txt, $underline_size): void
{
    $txt_w = $pdf->GetStringWidth($txt);
    $pdf->setAbsXY($x, $y);
    $pdf->Cell($txt_w, $h, $txt, align: 'C', valign: 'B');
    $pdf->Rect($x + $txt_w + $margin, $y + $h - $underline_size / 2, $w - $txt_w - $margin, $underline_size, 'F');
}

function section_event_individual(TCPDF $pdf, $margin, $x, $y, $w, $h, $line_height): void
{
    $per_line = $h / 6;
    $line_width = 0.1;

    $pdf->setFontSize(Size::fontSize($per_line, $line_height));

    $date_text = Loc::_('event-date');
    $loc_text = Loc::_('event-loc');
    $rem_text = Loc::_('event-remark');

    $pdf->Rect($x, $y + $per_line - 0.5, $w, 0.5, 'F');
    $y += $per_line;
    _section_print_text_and_line($pdf, $margin, $x, $y, $w, $per_line, $date_text, $line_width);
    $y += $per_line;
    _section_print_text_and_line($pdf, $margin, $x, $y, $w, $per_line, $loc_text, $line_width);
    $y += $per_line;
    $pdf->Rect($x, $y + $per_line - $line_width / 2, $w, $line_width, 'F');
    $y += $per_line;
    _section_print_text_and_line($pdf, $margin, $x, $y, $w, $per_line, $rem_text, $line_width);
    $y += $per_line;
    $pdf->Rect($x, $y + $per_line - $line_width / 2, $w, $line_width, 'F');
}

function section_event_template(TCPDF $pdf, float $margin): void
{
    [$start_x, $start_y, $width, $height] = planner_size_dimensions($margin);

    $pdf->setFillColor(...Colors::g(0));
    $pdf->setTextColor(...Colors::g(0));
    $pdf->setFont(Loc::_('fonts.font2'));

    $half_width = ($width - $margin) / 2;
    $third_height = ($height - 4 * $margin) / 3;

    for ($i = 0; $i < 6; $i++) {
        $x = $start_x + ($i % 2) * ($half_width + $margin);
        $y = $start_y + floor($i / 2) * ($third_height + $margin) + $margin;
        section_event_individual($pdf, 1, $x, $y, $half_width, $third_height, 2);
    }
}

Templates::register('section-event', 'section_event_template');

function section_event_index(TCPDF $pdf, int $page_count): void
{
    $margin = 2;
    $index_number_line_height = 2.2;
    $pages = ceil($page_count / 40);

    $pdf->setFont(Loc::_('fonts.font2'));

    for ($page = 1; $page <= $pages; $page++) {
        $pdf->AddPage();
        $pdf->setLink(Links::series($pdf, 'event-index', $page));

        Templates::draw('section-index-header', Loc::_('event-index'));
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
            $pdf->Link($x1, $y, $half_width, $size, Links::series($pdf, 'event', $base_number + $i));
            $pdf->setAbsXY($x2, $y);
            $pdf->Cell($half_width, $size, sprintf('%d.', $base_number + 20 + $i));
            $pdf->Link($x2, $y, $half_width, $size, Links::series($pdf, 'event', $base_number + 20 + $i));

            $y += $size;
        }

        planner_nav_sub($pdf);
        planner_nav_main($pdf, 3);
    }
}

function section_event_pages(TCPDF $pdf, int $pages): void
{
    $margin = 2;
    $line_size = 6;
    $line_bold = 0.5;

    $pdf->setTextColor(...Colors::g(15));
    $pdf->setFontSize(Size::fontSize(planner_header_height(), 1.5));

    for ($page = 1; $page <= $pages; $page++) {
        $pdf->AddPage();
        $pdf->setLink(Links::series($pdf, 'event', $page));

        $index_page = ceil($page / 40);

        Templates::draw('section-page-header', Loc::_('event'));
        $pdf->setAbsXY(0, PX100);
        $pdf->Cell(section_header_indent() - planner_header_margin(), planner_header_height(), sprintf('%03d', $page), align: 'R');
        $pdf->Link(0, PX100, section_header_indent(), planner_header_height(), Links::series($pdf, 'event-index', $index_page));

        Templates::draw('section-event', $margin);

        planner_nav_sub($pdf);
        planner_nav_main($pdf, 3);
    }
}
