<?php
function section_list_template(TCPDF $pdf, float $margin, float $line_size, float $line_bold): void
{
    [$start_x, $start_y, $width, $height] = planner_size_dimensions($margin);

    $pdf->setFillColor(...Colors::g(0));

    $half_width = ($width - $margin) / 2;
    $third_height = ($height) / 2;

    for ($i = 0; $i < 4; $i++) {
        $x = $start_x + ($i % 2) * ($half_width + $margin);
        $y = $start_y + floor($i / 2) * ($third_height) + $line_size;
        [$offset_x, $offset_y] = planner_draw_note_area($pdf, $x, $y, $half_width, $third_height - $line_size, 'checkbox', $line_size);
        $pdf->Rect($x + $offset_x, $y + $offset_y - $line_bold, $half_width, $line_bold, 'F');
    }
}

Templates::register('section-list', 'section_list_template');

function section_list_index(TCPDF $pdf, int $page_count): void
{
    $margin = 2;
    $index_number_line_height = 2.2;
    $pages = ceil($page_count / 40);

    $pdf->setFont(Loc::_('fonts.font2'));

    for ($page = 1; $page <= $pages; $page++) {
        $pdf->AddPage();
        $pdf->setLink(Links::series($pdf, 'list-index', $page));

        Templates::draw('section-index-header', Loc::_('list-index'));
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
            $pdf->Link($x1, $y, $half_width, $size, Links::series($pdf, 'list', $base_number + $i));
            $pdf->setAbsXY($x2, $y);
            $pdf->Cell($half_width, $size, sprintf('%d.', $base_number + 20 + $i));
            $pdf->Link($x2, $y, $half_width, $size, Links::series($pdf, 'list', $base_number + 20 + $i));

            $y += $size;
        }

        planner_nav_sub($pdf);
        planner_nav_main($pdf, 2);
    }
}

function section_list_pages(TCPDF $pdf, int $pages): void
{
    $margin = 2;
    $line_size = 6;
    $line_bold = 0.5;

    $pdf->setTextColor(...Colors::g(15));
    $pdf->setFontSize(Size::fontSize(planner_header_height(), 1.5));

    for ($page = 1; $page <= $pages; $page++) {
        $pdf->AddPage();
        $pdf->setLink(Links::series($pdf, 'list', $page));

        $index_page = ceil($page / 40);

        Templates::draw('section-page-header', Loc::_('list'));
        $pdf->setAbsXY(0, PX100);
        $pdf->Cell(section_header_indent() - planner_header_margin(), planner_header_height(), sprintf('%03d', $page), align: 'R');
        $pdf->Link(0, PX100, section_header_indent(), planner_header_height(), Links::series($pdf, 'list-index', $index_page));

        Templates::draw('section-list', $margin, $line_size, $line_bold);

        planner_nav_sub($pdf);
        planner_nav_main($pdf, 2);
    }
}
