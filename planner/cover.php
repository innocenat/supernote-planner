<?php
function planner_cover(TCPDF $pdf, $title, $subtitle): void
{
    $pdf->AddPage();
    $pdf->setLineStyle(['width' => 0.5, 'cap' => 'butt', 'color' => [0, 0, 0]]);


    $pdf->Rect(PX100 + 10, PX100 + 10, W - 2 * PX100 - 20, H - 2 * PX100 - 20);
    $pdf->setFont(Loc::_('fonts.font1'));

    $pdf->setFontSize(28);
    $pdf->setAbsXY(PX100, PX100);
    $pdf->Cell(W - 2 * PX100, (H - 2 * PX100) / 2 - 2.5, $title, align: 'C', valign: 'B');

    $pdf->setFontSize(14);
    $pdf->setAbsXY(PX100, PX100 + (H - 2 * PX100) / 2);
    $pdf->Cell(W - 2 * PX100, (H - 2 * PX100) / 2 - 2.5, $subtitle, align: 'C', valign: 'T');

    $pdf->setFont(Loc::_('fonts.font2'));
}

