<?php

class Links
{
    static array $links;

    static function link(TCPDF $pdf, string $target): mixed
    {
        if (isset(self::$links[$target])) {
            return self::$links[$target];
        }
        return self::$links[$target] = $pdf->AddLink();
    }

    static function daily(TCPDF $pdf, Day $day, string $name = 'default', int $no = 1): mixed
    {
        $key = sprintf("%d-%d-%d", $day->year, $day->month, $day->day);
        return self::link($pdf, 'daily__' . $key . '--' . $name . '__' . $no);
    }

    static function weekly(TCPDF $pdf, Week $week, string $name = 'default', int $no = 1): mixed
    {
        $key = sprintf("%d-%d", $week->year, $week->week);
        return self::link($pdf, 'weekly__' . $key . '--' . $name . '__' . $no);
    }

    static function monthly(TCPDF $pdf, Month $month, string $name = 'default', int $no = 1): mixed
    {
        $key = sprintf("%d-%d", $month->year, $month->month);
        return self::link($pdf, 'monthly__' . $key . '--' . $name . '__' . $no);
    }

    static function quarterly(TCPDF $pdf, Quarter $quarter, string $name = 'default', int $no = 1): mixed
    {
        $key = sprintf("%d-%d", $quarter->year, $quarter->quarter);
        return self::link($pdf, 'quarterly__' . $key . '--' . $name . '__' . $no);
    }

    static function yearly(TCPDF $pdf, Year $year, string $name = 'default', int $no = 1): mixed
    {
        $key = $year->year;
        return self::link($pdf, 'yearly__' . $key . '--' . $name . '__' . $no);
    }

    static function series(TCPDF $pdf, string $name, int $number): mixed
    {
        return self::link($pdf, 'series__' . $name . '--' . $number);
    }
}
