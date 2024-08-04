<?php

class PlannerGenerator implements IGenerator
{
    public function __construct(public string $title, public string $subtitle)
    {
    }

    public function generate(TCPDF $pdf, array $config): void
    {
        planner_cover($pdf, $this->title, $this->subtitle);

        foreach (Calendar::iterate_year() as $month) {
            yearly_calendar($pdf, $month, $config['monday_start']);
        }
        foreach (Calendar::iterate_quarter() as $quarter) {
            planner_quarterly($pdf, $quarter);
        }
        foreach (Calendar::iterate_month() as $month) {
            planner_monthly($pdf, $month, $config['monday_start']);
        }
        foreach (Calendar::iterate_week() as $week) {
            planner_weekly($pdf, $week);
        }
        foreach (Calendar::iterate_day() as $day) {
            planner_daily($pdf, $day, $config['12hr'], $config['night_shift']);
        }
        foreach (Calendar::iterate_quarter() as $quarter) {
            planner_quarterly_task($pdf, $quarter);
        }
        foreach (Calendar::iterate_month() as $month) {
            planner_monthly_planner($pdf, $month, $config['note_style']);
        }
        foreach (Calendar::iterate_week() as $week) {
            planner_weekly_task($pdf, $week);
        }
        foreach (Calendar::iterate_day() as $day) {
            planner_daily_diary($pdf, $day);
        }
        foreach (Calendar::iterate_quarter() as $quarter) {
            planner_quarterly_note($pdf, $quarter, $config['note_style']);
        }
        foreach (Calendar::iterate_month() as $month) {
            planner_monthly_note($pdf, $month, $config['note_style']);
        }
        foreach (Calendar::iterate_week() as $week) {
            planner_weekly_note($pdf, $week, $config['note_style']);
        }
        foreach (Calendar::iterate_day() as $day) {
            planner_daily_note($pdf, $day, $config['note_style']);
        }

        if (!$config['planner_only']) {
            section_note_index($pdf, $config['extra_amount']);
            section_note_pages($pdf, $config['extra_amount'], $config['note_style']);
            section_list_index($pdf, $config['extra_amount']);
            section_list_pages($pdf, $config['extra_amount']);
            section_event_index($pdf, $config['extra_amount']);
            section_event_pages($pdf, $config['extra_amount']);
        }
    }
}
