<?php

function planner_nav_main_template(TCPDF $pdf, int $active, bool $has_prev, bool $has_next): void
{
    $pdf->setFillColor(...Colors::g(0));
    $pdf->Rect(0, H - PX100, W, PX100, 'F');
    $pdf->setFillColor(...Colors::g(6));

    $pdf->setLineStyle(['width' => 0.2, 'cap' => 'butt', 'color' => Colors::g(0)]);

    // Central menu
    $menu_spacing = 1;
    $image_offset = 0.25 * PX100;
    $image_size = 0.5 * PX100;
    $tab_height = 0.9 * PX100;
    $menu = ['planner', 'note', 'list', 'event'];
    $image_path = dirname(__FILE__) . DS . 'res' . DS;
    $menu_start = 46; // Put it in the space between page number and file name

    $menu_positions = [
        $menu_spacing,
        $menu_start + 2 * (PX100 + $menu_spacing),
        $menu_start + 3 * (PX100 + $menu_spacing),
        W - PX100 - $menu_spacing
    ];

    for ($i = 0; $i < 4; $i++) {
        if ($active === $i) {
            $pdf->setFillColor(...Colors::g(15));
            $pdf->RoundedRect($menu_positions[$i], H - PX100, PX100, $tab_height, 1, '0110', 'F');
            $pdf->Image($image_path . $menu[$i] . '-b.png', $menu_positions[$i] + $image_offset, H - PX100 + $image_offset - 0.5, $image_size, $image_size);
            $pdf->setFillColor(...Colors::g(6));
        } else {
            $pdf->RoundedRect($menu_positions[$i], H - PX100, PX100, $tab_height, 1, '0110', 'F');
            $pdf->Image($image_path . $menu[$i] . '-w.png', $menu_positions[$i] + $image_offset, H - PX100 + $image_offset - 0.5, $image_size, $image_size);
        }
        $pdf->Link($menu_positions[$i], H - PX100, PX100, $tab_height, $i === 0 ? Links::yearly($pdf, Calendar::startYear()) : Links::series($pdf, $menu[$i] . '-index', 1));
    }
}

Templates::register('nav-main', 'planner_nav_main_template');

function planner_nav_main(TCPDF $pdf, int $active, mixed $prev = null, mixed $next = null): void
{
    if (!PLANNER_ONLY)
        Templates::draw('nav-main', $active, isset($prev), isset($next));
}
