<?php

class Templates
{
    static array $templates = [];
    static array $generators = [];
    static TCPDF $pdf;

    static function register(string $name, callable $generator): void
    {
        self::$generators[$name] = $generator;
    }

    static function draw(string $name, ...$params): void
    {
        $template_key = $name;
        foreach ($params as $val) {
            $template_key .= ':' . is_array($val) ? serialize($val) : strval($val);
        }

        if (empty(self::$templates[$template_key])) {
            self::$pdf->startTemplate();
            self::$generators[$name](self::$pdf, ...$params);
            self::$templates[$template_key] = self::$pdf->endTemplate();
        }

        self::$pdf->printTemplate(self::$templates[$template_key], 0, 0);
    }
}
