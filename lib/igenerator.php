<?php

interface IGenerator
{
    public function generate(TCPDF $pdf, array $config): void;
}
