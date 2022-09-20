<?php

final class Size
{
    public static int $ppi = 300;

    static function px2mm(float $px): float
    {
        return 25.4 * $px / self::$ppi;
    }

    static function mm2pt(float $mm): float
    {
        return 72 * $mm / 25.4;
    }

    static function fontSize(float $mm, float $line_height): float
    {
        return self::mm2pt($mm) / $line_height;
    }
}
