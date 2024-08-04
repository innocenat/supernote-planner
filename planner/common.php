<?php
function planner_size_dimensions(float $margin): array
{
    $start_x = $margin;
    $start_y = PX100 + planner_header_height();
    $width = W - 2 * $margin;
    $height = H - 2 * PX100 - planner_header_height() - nav_sub_height();

    return [$start_x, $start_y, $width, $height];
}

function planner_header_height(): float
{
    return 6;
}

function planner_header_margin(): float
{
    return 1.5;
}

function planner_tabs_calculate_size(TCPDF $pdf, array &$tabs): void
{
    $margin = planner_header_margin();
    $h = planner_header_height() - 1;
    foreach ($tabs as $i => $tab) {
        $tabs[$i]['size'] = $pdf->GetStringWidth($tab['name'], Loc::_('fonts.font2'), '', Size::fontSize($h, 2)) + 2 * $margin;
    }
}

function draw_tabs(TCPDF $pdf, int|array $actives, array $tabs): void
{
    $margin = planner_header_margin();

    if (!is_array($actives)) {
        $actives = [$actives];
    }

    $tab_all_size = 0;
    foreach ($tabs as $tab) {
        $tab_all_size += $margin + $tab['size'];
    }

    $x = W - $tab_all_size;
    $y = PX100 + 1;
    $h = planner_header_height() - 1;

    $pdf->setFontSize(Size::fontSize($h, 2));

    $pdf->setAbsY($y);
    foreach ($tabs as $i => $tab) {
        $pdf->setAbsX($x);

        if (isset($tab['type']) && $tab['type'] === 'button') {
            $pdf->setFillColor(...Colors::g(6));
            $pdf->setTextColor(...Colors::g(15));

            $pdf->RoundedRect($x, $y, $tab['size'], $h - 1, 1, '1111', 'F');
            $pdf->Cell($tab['size'], $h - 1, $tab['name'], align: 'C');
        } else {
            if (in_array($i, $actives)) {
                $pdf->setFillColor(...Colors::g(15));
                $pdf->setTextColor(...Colors::g(0));
            } else {
                $pdf->setFillColor(...Colors::g(6));
                $pdf->setTextColor(...Colors::g(15));
            }

            $pdf->RoundedRect($x, $y, $tab['size'], $h, 1, '1001', 'F');
            $pdf->Cell($tab['size'], $h, $tab['name'], align: 'C');
        }

        $x += $tab['size'] + $margin;
    }
}

function link_tabs(TCPDF $pdf, array $tabs, array $targets): void
{
    $margin = planner_header_margin();

    $tab_all_size = 0;
    foreach ($tabs as $tab) {
        $tab_all_size += $margin + $tab['size'];
    }

    $x = W - $tab_all_size;
    $y = PX100; // No margin for links
    $h = planner_header_height();

    foreach ($tabs as $i => $tab) {
        $pdf->Link($x, $y, $tab['size'], $h, $targets[$i]);
        $x += $tab['size'] + $margin;
    }
}

function planner_calculate_marking_offset(float $w, float $h, string $type, float $size): array
{
    $cols = floor($w / $size);
    $rows = floor($h / $size);
    $offset_x = $type === 'dot' || $type === 'table' ? ($w - $size * $cols) / 2 : 0;
    $offset_y = ($h - $size * $rows) / 2;
    return [$offset_x, $offset_y, $cols, $rows];
}

function planner_draw_note_area(TCPDF $pdf, float $x, float $y, float $w, float $h, string $type, float $size): array
{
    [$offset_x, $offset_y, $cols, $rows] = planner_calculate_marking_offset($w, $h, $type, $size);

    planner_note_draw_internal($type, $pdf, $x, $offset_x, $y, $offset_y, $w, $h, $size);
    return [$offset_x, $offset_y, $cols, $rows];
}

function planner_draw_note_area_with_header(TCPDF $pdf, string $header, float $line_height, float $x, float $y, float $w, float $h, string $type, float $size): array
{
    [$offset_x, $offset_y, $cols, $rows] = planner_calculate_marking_offset($w, $h, $type, $size);

    // Header
    $pdf->setAbsXY($x + $offset_x, $y + $offset_y);
    $pdf->setFontSize(Size::fontSize($size, $line_height));
    $pdf->Cell($w - 2 * $offset_x, $size, $header);

    // Skip one line
    $offset_y += $size;
    planner_note_draw_internal($type, $pdf, $x, $offset_x, $y, $offset_y, $w, $h, $size);
    return [$offset_x, $offset_y, $cols, $rows];
}

function planner_note_draw_internal(string $type, TCPDF $pdf, float $x, float $offset_x, float $y, float $offset_y, float $w, float $h, float $size): void
{
    if ($type === 'dot') {
        draw_dot_grid($pdf, $x + $offset_x, $y + $offset_y, $w - 2 * $offset_x, $h - 2 * $offset_y, $size);
    } else if ($type === 'table') {
        draw_table($pdf, $x + $offset_x, $y + $offset_y, $w - 2 * $offset_x, $h - 2 * $offset_y, $size);
    } else if ($type === 'checkbox') {
        draw_checkbox($pdf, $x + $offset_x, $y + $offset_y, $w - 2 * $offset_x, $h - 2 * $offset_y, $size);
    } else {
        draw_rules($pdf, $x + $offset_x, $y + $offset_y, $w - 2 * $offset_x, $h - 2 * $offset_y, $size, planner_rule_pattern($type, $size));
    }
}

function planner_rule_pattern(string $name, float $size): array|bool
{
    return match ($name) {
        'task' => [$size, -20],
        'event' => [$size, $size + 20, $size + 40],
        default => false,
    };
}

function section_index_header(TCPDF $pdf, string $title): void
{
    $height = planner_header_height();

    $pdf->setFont(Loc::_('fonts.font2'));
    $pdf->setFontSize(Size::fontSize($height, 1.5));
    $pdf->setTextColor(...Colors::g(15));
    $pdf->setFillColor(...Colors::g(0));

    $pdf->Rect(0, PX100, W, $height, 'F');
    $pdf->setAbsXY(planner_header_margin(), PX100);
    $pdf->Cell(W - 2 * planner_header_margin(), $height, $title, align: 'L');
}

function section_page_header(TCPDF $pdf, string $title): void
{
    $height = planner_header_height();
    $indent = section_header_indent();

    $pdf->setFont(Loc::_('fonts.font2'));
    $pdf->setFontSize(Size::fontSize($height, 1.5));
    $pdf->setTextColor(...Colors::g(15));
    $pdf->setFillColor(...Colors::g(0));

    $pdf->Rect(0, PX100, W, $height, 'F');
    $pdf->setAbsXY(planner_header_margin() + $indent, PX100);
    $pdf->Cell(W, $height, $title, align: 'L');

    $pdf->setFillColor(...Colors::g(15));
    $pdf->Rect($indent - 0.1, PX100, 0.2, $height);
}

function section_index_body(TCPDF $pdf, float $margin, float $x, float $y, float $w, float $h, int $rows): void
{
    $half_width = ($w - $margin) / 2;
    $size = ($h - 2 * $margin) / $rows;

    planner_draw_note_area($pdf, $x, $y, $half_width, $h, 'rules', $size);
    planner_draw_note_area($pdf, $x + $margin + $half_width, $y, $half_width, $h, 'rules', $size);
}

Templates::register('section-index-header', 'section_index_header');
Templates::register('section-index-body', 'section_index_body');
Templates::register('section-page-header', 'section_page_header');

function section_header_indent(): float
{
    return 15;
}

function draw_dot_grid(TCPDF $pdf, $x, $y, $w, $h, $size): void
{
    $pdf->setFillColor(...Colors::g(0));
    for ($yy = $y; $yy <= $y + $h; $yy += $size) {
        for ($xx = $x; $xx <= $x + $w; $xx += $size) {
            $pdf->Circle($xx, $yy, 0.1, style: 'F');
        }
    }
}

function draw_table(TCPDF $pdf, $x, $y, $w, $h, $size): void
{
    $pdf->setLineStyle(['width' => 0.1, 'cap' => 'butt', 'color' => Colors::g(0)]);
    for ($yy = $y; $yy <= $y + $h; $yy += $size) {
        $pdf->Line($x, $yy, $x + $w, $yy);
    }
    for ($xx = $x; $xx <= $x + $w; $xx += $size) {
        $pdf->Line($xx, $y, $xx, $y + $h);
    }
}

function draw_checkbox(TCPDF $pdf, $x, $y, $w, $h, $size): void
{
    $s2 = $size / 2;
    $s4 = $size / 4;
    $pdf->setLineStyle(['width' => 0.1, 'cap' => 'butt', 'color' => Colors::g(0)]);
    for ($yy = $y; $yy <= $y + $h; $yy += $size) {
        $pdf->Line($x, $yy, $x + $w, $yy);

        if ($yy + $size <= $y + $h)
            $pdf->Rect($x + $s4, $yy + $s4, $s2, $s2, 'S');
    }
}

function draw_rules(TCPDF $pdf, $x, $y, $w, $h, $size, $verticals = false): void
{
    $pdf->setLineStyle(['width' => 0.1, 'cap' => 'butt', 'color' => Colors::g(0)]);
    for ($yy = $y; $yy <= $y + $h; $yy += $size) {
        $pdf->Line($x, $yy, $x + $w, $yy);
    }

    if ($verticals) {
        foreach ($verticals as $rule) {
            $xx = $x + $rule;
            if ($rule < 0) {
                $xx += $w;
            }
            $pdf->Line($xx, $y, $xx, $y + $h);
        }
    }
}
