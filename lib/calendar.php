<?php

final class Day
{
    public readonly DateTimeImmutable $date;

    public readonly int $day, $month, $year;
    public readonly int $dow;

    private static array $instances = [];

    private function __construct(DateTimeImmutable $date)
    {
        $this->date = $date;
        $this->day = intval($date->format('j'));
        $this->month = intval($date->format('n'));
        $this->year = intval($date->format('Y'));
        $this->dow = intval($date->format('w'));
    }

    public static function ymd(int $y, int $m, int $d): Day
    {
        return self::make((new DateTimeImmutable())->setDate($y, $m, $d)->setTime(0, 0, 0));
    }

    public static function make(DateTimeImmutable $date): Day
    {
        $key = $date->format('Y-m-d');

        if (isset(self::$instances[$key])) {
            return self::$instances[$key];
        }

        return self::$instances[$key] = new self($date);
    }

    public function next(): Day
    {
        return self::make($this->date->modify('+1 day'));
    }

    public function prev(): Day
    {
        return self::make($this->date->modify('-1 day'));
    }

    public function week(): Week
    {
        return Week::fromDate($this->date);
    }

    public function month(): Month
    {
        return Month::make($this->year, $this->month);
    }

    public function quarter(): Quarter
    {
        return Quarter::fromYearMonth($this->year, $this->month);
    }

    public function year(): Year
    {
        return Year::make($this->year);
    }

    public function active(): bool
    {
        return $this->compare(Calendar::startDay()) * $this->compare(Calendar::endDay()) < 1;
    }

    public function compare(Day $that): int
    {
        $result = $this->year <=> $that->year;
        if ($result === 0) {
            $result = $this->month <=> $that->month;
        }
        if ($result === 0) {
            $result = $this->year <=> $that->year;
        }
        return $result;
    }

    public function __toString(): string
    {
        return sprintf('[day:%d-%d-%d]', $this->year, $this->month, $this->day);
    }
}

final class Week
{
    public readonly int $year;
    public readonly int $week;
    /**
     * @var Day[]
     */
    public readonly array $days;

    private static array $instances;

    private function __construct(DateTimeImmutable $startDate)
    {
        $this->year = intval($startDate->format('o'));
        $this->week = intval($startDate->format('W'));

        if (!Calendar::$start_monday) {
            $startDate = $startDate->modify('-1 day');
        }

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $days[] = Day::make($startDate);
            $startDate = $startDate->modify('+1 day');
        }
        $this->days = $days;
    }

    public static function fromDate(DateTimeImmutable $date): Week
    {
        if (!Calendar::$start_monday && $date->format('w') === '0') {
            // Since ISO week ends on Sunday
            // So if we start week on Sunday, the Sunday week is going to be wrong.
            $date = $date->modify('+1 day');
        }

        $week = intval($date->format('W'));
        $year = intval($date->format('o'));
        return self::fromYearWeek($year, $week);
    }

    public static function fromYearWeek(int $year, int $week): Week
    {
        // For week normalisation
        $startDate = new DateTime();
        $startDate->setTime(0, 0, 0)->setISODate($year, $week);

        $key = $startDate->format('o-W');
        if (isset(self::$instances[$key])) {
            return self::$instances[$key];
        }
        return self::$instances[$key] = new self(DateTimeImmutable::createFromMutable($startDate));
    }

    function next(): Week
    {
        return self::fromYearWeek($this->year, $this->week + 1);
    }

    function prev(): Week
    {
        return self::fromYearWeek($this->year, $this->week - 1);
    }

    function months(): array
    {
        $first_month = $this->days[0]->month();
        $last_month = $this->days[6]->month();

        if (0 === $first_month->compare($last_month)) {
            return [$first_month];
        } else {
            return [$first_month, $last_month];
        }
    }

    function quarters(): array
    {
        $first_quarter = $this->days[0]->quarter();
        $last_quarter = $this->days[6]->quarter();

        if (0 === $first_quarter->compare($last_quarter)) {
            return [$first_quarter];
        } else {
            return [$first_quarter, $last_quarter];
        }
    }

    function years(): array
    {
        $first_year = $this->days[0]->year();
        $last_year = $this->days[6]->year();

        if (0 === $first_year->compare($last_year)) {
            return [$first_year];
        } else {
            return [$first_year, $last_year];
        }
    }

    public function active(): bool
    {
        return $this->compare(Calendar::startWeek()) * $this->compare(Calendar::endWeek()) < 1;
    }

    function compare(Week $that): int
    {
        $result = $this->year <=> $that->year;
        if ($result === 0) {
            $result = $this->week <=> $that->week;
        }
        return $result;
    }

    public function __toString(): string
    {
        return sprintf('[week:%d-%d]', $this->year, $this->week);
    }
}

final class Month
{
    public readonly int $year, $quarter, $month, $day_in_month;

    /**
     * @var Day[]
     */
    public readonly array $days;

    /**
     * @var Week[]
     */
    public readonly array $weeks;

    private static array $instances;

    private function __construct(int $year, int $month)
    {
        $first_of_month = (new DateTimeImmutable())->setDate($year, $month, 1)->setTime(0, 0, 0);
        $this->year = $year;
        $this->month = $month;
        $this->day_in_month = intval($first_of_month->format('t'));

        $days = [];
        for ($i = 1; $i <= $this->day_in_month; $i++) {
            $days[] = Day::ymd($year, $month, $i);
        }
        $this->days = $days;

        $weeks = [];
        $week = Week::fromDate($first_of_month);
        do {
            $weeks[] = $week;
            $week = $week->next();
        } while ($week->days[0]->month === $month);
        $this->weeks = $weeks;

        $this->quarter = Quarter::calculateFromMonth($month);
    }

    private static function normalize(int $year, int $month): array
    {
        while ($month > 12) {
            $month -= 12;
            $year++;
        }
        while ($month < 1) {
            $month += 12;
            $year--;
        }

        return [$year, $month];
    }

    public static function make(int $year, int $month): Month
    {
        [$year, $month] = self::normalize($year, $month);
        $key = "$year-$month";

        if (isset(self::$instances[$key])) {
            return self::$instances[$key];
        }

        return self::$instances[$key] = new self($year, $month);
    }

    public function next(): Month
    {
        return self::make($this->year, $this->month + 1);
    }

    public function prev(): Month
    {
        return self::make($this->year, $this->month - 1);
    }

    public function quarter(): Quarter
    {
        return Quarter::make($this->year, $this->quarter);
    }

    public function year(): Year
    {
        return Year::make($this->year);
    }

    public function hasDay(Day $day): bool
    {
        return $day->year === $this->year && $day->month === $this->month;
    }

    public function active(): bool
    {
        return $this->compare(Calendar::startMonth()) * $this->compare(Calendar::endMonth()) < 1;
    }

    public function compare(Month $that): int
    {
        $result = $this->year <=> $that->year;
        if ($result === 0) {
            $result = $this->month <=> $that->month;
        }
        return $result;
    }

    public function __toString(): string
    {
        return sprintf('[month:%d-%d]', $this->year, $this->month);
    }
}

final class Quarter
{
    public readonly int $year;
    public readonly int $quarter;

    /**
     * @var Month[]
     */
    public readonly array $months;
    public readonly int $start_month, $end_month;

    private static array $instances;

    private function __construct(int $year, int $quarter)
    {
        $this->year = $year;
        $this->quarter = $quarter;

        $this->start_month = 1 + 3 * ($quarter - 1);
        $this->end_month = 3 * $quarter;

        $months = [];
        for ($i = $this->start_month; $i <= $this->end_month; $i++) {
            $months[] = Month::make($year, $i);
        }
        $this->months = $months;
    }

    private static function normalize(int $year, int $quarter): array
    {
        while ($quarter > 4) {
            $quarter -= 4;
            $year++;
        }
        while ($quarter < 1) {
            $quarter += 4;
            $year--;
        }
        return [$year, $quarter];
    }

    public static function calculateFromMonth(int $month): int
    {
        return ceil(($month - 1) / 4) + 1;
    }

    public static function make(int $year, int $quarter): Quarter
    {
        [$year, $quarter] = self::normalize($year, $quarter);
        $key = "$year-$quarter";

        if (isset(self::$instances[$key])) {
            return self::$instances[$key];
        }

        return self::$instances[$key] = new self($year, $quarter);
    }

    public static function fromYearMonth(int $year, int $month): Quarter
    {
        return self::make($year, self::calculateFromMonth($month));
    }

    public function next(): Quarter
    {
        return self::make($this->year, $this->quarter + 1);
    }

    public function prev(): Quarter
    {
        return self::make($this->year, $this->quarter - 1);
    }

    public function year(): Year
    {
        return Year::make($this->year);
    }

    public function hasDay(Day $day): bool
    {
        return $day->year === $this->year && ($day->month >= $this->start_month && $day->month <= $this->end_month);
    }

    public function active(): bool
    {
        return Month::make($this->year, $this->start_month)->compare(Calendar::startMonth()) * Month::make($this->year, $this->end_month)->compare(Calendar::endMonth()) < 1;
    }

    public function compare(Quarter $that): int
    {
        $result = $this->year <=> $that->year;
        if ($result === 0) {
            $result = $this->quarter <=> $that->quarter;
        }
        return $result;
    }

    public function __toString(): string
    {
        return sprintf('[quarter:%d-%d]', $this->year, $this->quarter);
    }
}

final class Year
{
    public readonly int $year;
    /**
     * @var Month[]
     */
    public readonly array $months;
    /**
     * @var Quarter[]
     */
    public readonly array $quarters;

    private static array $instances;

    private function __construct(int $year)
    {
        $this->year = $year;

        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[] = Month::make($year, $i);
        }
        $quarters = [];
        for ($i = 1; $i <= 4; $i++) {
            $quarters[] = Quarter::make($year, $i);
        }
        $this->months = $months;
        $this->quarters = $quarters;
    }

    public static function make(int $year): Year
    {
        if (isset(self::$instances[$year])) {
            return self::$instances[$year];
        }

        return self::$instances[$year] = new self($year);
    }

    public function next(): Year
    {
        return self::make($this->year + 1);
    }

    public function prev(): Year
    {
        return self::make($this->year - 1);
    }

    public function active(): bool
    {
        return $this->compare(Calendar::startYear()) * $this->compare(Calendar::endYear()) < 1;
    }

    public function compare(Year $that): int
    {
        return $this->year <=> $that->year;
    }

    public function __toString(): string
    {
        return sprintf('year:%d', $this->year);
    }
}

final class Calendar
{
    public static bool $start_monday = false;
    public static int $start_y, $start_m;
    public static int $end_y, $end_m;

    static function startMonth(): Month
    {
        return Month::make(self::$start_y, self::$start_m);
    }

    static function endMonth(): Month
    {
        return Month::make(self::$end_y, self::$end_m);
    }

    static function startYear(): Year
    {
        return self::startMonth()->year();
    }

    static function endYear(): Year
    {
        return self::endMonth()->year();
    }

    static function startQuarter(): Quarter
    {
        return self::startMonth()->quarter();
    }

    static function endQuarter(): Quarter
    {
        return self::endMonth()->quarter();
    }

    static function startWeek(): Week
    {
        return self::startMonth()->weeks[0];
    }

    static function endWeek(): Week
    {
        $week_array = self::endMonth()->weeks;
        return $week_array[array_key_last($week_array)];
    }

    static function startDay(): Day
    {
        return self::startMonth()->days[0];
    }

    static function endDay(): Day
    {
        $day_array = self::endMonth()->days;
        return $day_array[array_key_last($day_array)];
    }

    static function iterate_year(): Generator
    {
        $current = self::startYear();
        $end = self::endYear();
        do {
            yield $current;
            $current = $current->next();
        } while ($current->compare($end) <= 0);
    }

    static function iterate_quarter(): Generator
    {
        $current = self::startQuarter();
        $end = self::endQuarter();
        do {
            yield $current;
            $current = $current->next();
        } while ($current->compare($end) <= 0);
    }

    static function iterate_month(): Generator
    {
        $current = self::startMonth();
        $end = self::endMonth();
        do {
            yield $current;
            $current = $current->next();
        } while ($current->compare($end) <= 0);
    }

    static function iterate_week(): Generator
    {
        $current = self::startWeek();
        $end = self::endWeek();
        do {
            yield $current;
            $current = $current->next();
        } while ($current->compare($end) <= 0);
    }

    static function iterate_day(): Generator
    {
        $current = self::startDay();
        $end = self::endDay();
        do {
            yield $current;
            $current = $current->next();
        } while ($current->compare($end) <= 0);
    }
}
