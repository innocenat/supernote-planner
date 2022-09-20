<?php

use Major\Fluent\Parser\FluentParser;
use Major\Fluent\Bundle\FluentBundle;

class Loc
{
    static FluentBundle $bundle;

    static function load(string $lang): void
    {
        $parser = new FluentParser(true);
        $resource = $parser->parse(file_get_contents(dirname(__FILE__, 2) . DS . 'lang' . DS . $lang . '.ftl'));
        self::$bundle = new FluentBundle($lang, true, false);
        self::$bundle->addResource($resource);
    }

    static function _(string $message, mixed ...$arguments): ?string
    {
        return self::$bundle->message($message, ...$arguments);
    }
}
