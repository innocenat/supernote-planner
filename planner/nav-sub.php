<?php
function __in_array_equals(mixed $needle, array $haystacks): bool
{
    foreach ($haystacks as $item) {
        if (0 === $item->compare($needle)) {
            return true;
        }
    }
    return false;
}

function nav_sub_height(): float
{
    return 6;
}

function planner_nav_sub_template(TCPDF $pdf, ...$months): void
{
    $height = 5;
    $space = 1;
    $y = H - PX100 - $height - $space;

    $start_x = 2;
    $end_x = W - 2;

    $count = 0;
    foreach (Calendar::iterate_month() as $_) {
        $count++;
    }

    $per_month = ($end_x - $start_x + $space) / $count - $space;
    $x = $start_x;

    $pdf->setAbsY($y);
    $pdf->setLineStyle(['width' => 0.2, 'cap' => 'butt', 'color' => Colors::g(0)]);
    $pdf->setFont(Loc::_('fonts.font2'));
    $pdf->setFontSize(Size::fontSize($height, 1.5));

    $pdf->setFillColor(...Colors::g(0));
    $pdf->Rect(0, $y - 0.1, W, 0.2, 'F');

    $pdf->setFillColor(...Colors::g(6));
    $pdf->setTextColor(...Colors::g(15));

    foreach (Calendar::iterate_month() as $month) {
        $pdf->setAbsX($x);

        if (!__in_array_equals($month, $months)) {
            $pdf->RoundedRect($x, $y, $per_month, $height, 1, '0110', 'F');
            $pdf->Cell($per_month, $height, $month->month, align: 'C');
        } else {
            $pdf->setFillColor(...Colors::g(15));
            $pdf->setTextColor(...Colors::g(0));

            $pdf->RoundedRect($x, $y, $per_month, $height, 1, '0110', 'S');
            $pdf->Cell($per_month, $height, $month->month, align: 'C');

            $pdf->setFillColor(...Colors::g(6));
            $pdf->setTextColor(...Colors::g(15));
        }

        $pdf->Link($x, $y, $per_month, $height, Links::monthly($pdf, $month));

        $x += $per_month + $space;
    }

}

Templates::register('nav-sub', 'planner_nav_sub_template');

function planner_nav_sub(TCPDF $pdf, ...$months): void
{
    Templates::draw('nav-sub', ...$months);
}
