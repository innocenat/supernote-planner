<?php

class Events
{
    static array $events;

    public static function getEventOnDay(Day $day)
    {
        $key = strval($day);
        if (isset(self::$events[$key])) {
            return self::$events[$key];
        }
        return [];
    }

    public static function loadFromICS(string $path)
    {
        try {
            $ical = new \ICal\ICal($path);
            $events = $ical->events();

            foreach ($events as $event) {
                $event_name = $event->summary;
                $event_date = $ical->iCalDateToDateTime($event->dtstart_array[3]);

                $key = strval(Day::make(DateTimeImmutable::createFromMutable($event_date)));
                if (!isset(self::$events[$key])) {
                    self::$events[$key] = [];
                }
                self::$events[$key][] = $event_name;
            }

        } catch (\Exception $e) {
            die($e);
        }
    }
}
