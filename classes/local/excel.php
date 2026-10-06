<?php
// This file is part of Moodle - https://moodle.org/
// Moodle is free software: you can redistribute it and/or modify it under the
// terms of the GNU General Public License as published by the Free Software
// Foundation, either version 3 of the License, or (at your option) any later version.
// Moodle is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
// without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
/**
 * CoursePulse course engagement dashboard.
 *
 * @package report_coursepulse
 * @copyright 2026 Franklin David Moya Davila / SiteEcuador
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace report_coursepulse\local;
defined('MOODLE_INTERNAL') || die();
/** Native XLSX export; explicit string writes prevent spreadsheet formula injection. */
class excel {
    /** Build a workbook; caller must enforce view/export capability, group scope and sesskey. */
    public static function build(\stdClass $course, array $roster, array $engagement, array $filters): \MoodleExcelWorkbook {
        global $CFG;
        require_once($CFG->libdir . '/excellib.class.php');
        $book = new \MoodleExcelWorkbook('coursepulse_' . $course->id);
        $heading = $book->add_format(['bold' => 1, 'fg_color' => '#17365D', 'color' => '#FFFFFF', 'text_wrap' => 1]);
        // Moodle's wrapper accepts a fixed legacy format list. Extend it for native XLSX formats.
        $percent = new class extends \MoodleExcelFormat {
            /** Accept an XLSX number-format pattern for this workbook. */
            public function set_num_format($numformat) {
                $this->format['numberFormat']['formatCode'] = $numformat;
            }
        };
        $percent->set_num_format('0.0%');
        $duration = clone $percent;
        $duration->set_num_format('[h]:mm:ss');
        $wrap = $book->add_format(['text_wrap' => 1, 'v_align' => 'top']);
        $summary = $book->add_worksheet(get_string('excelsummary', 'report_coursepulse'));
        $summary->set_column(0, 0, 36);
        $summary->set_column(1, 1, 90);
        $summary->write_string(0, 0, get_string('pluginname', 'report_coursepulse'), $heading);
        $summary->write_string(0, 1, format_string($course->fullname), $heading);
        $i = 1;
        foreach (['from', 'to', 'risk', 'search'] as $key) {
            $summary->write_string($i, 0, get_string($key, 'report_coursepulse'));
            $value = $filters[$key] ?? '';
            if ($key === 'from' || $key === 'to') {
                $value = userdate((int)$value, '%Y-%m-%d');
            }
            $summary->write_string($i++, 1, (string)$value);
        }
        $summary->write_string($i, 0, get_string('excelscope', 'report_coursepulse'));
        $summary->write_string($i, 1, get_string('excelscope_desc', 'report_coursepulse'), $wrap);
        $summary->set_row($i++, 72);
        foreach (['total', 'active', 'warning', 'critical', 'never', 'completed'] as $key) {
            $summary->write_string($i, 0, get_string('summary_' . $key, 'report_coursepulse'));
            $summary->write_number($i++, 1, $roster['summary'][$key]);
        }
        $summary->write_string($i, 0, get_string('exportedstudents', 'report_coursepulse'));
        $summary->write_number($i++, 1, count($roster['rows']));
        $summary->write_string($i, 0, get_string('measurement', 'report_coursepulse'));
        $status = !empty($engagement['unavailable']) ? 'nologs' :
            (!empty($engagement['truncated']) ? 'truncated' : 'methodology');
        $summary->write_string($i, 1, get_string($status, 'report_coursepulse'), $wrap);
        $summary->set_row($i, 90);
        $sheet = $book->add_worksheet(get_string('students', 'report_coursepulse'));
        $keys = ['student', 'progress', 'lastaccess', 'risk', 'estimatedtime', 'sessions', 'averagesession', 'events'];
        foreach ($keys as $col => $key) {
            $sheet->write_string(0, $col, get_string($key, 'report_coursepulse'), $heading);
        }
        $sheet->set_row(0, 36);
        $sheet->set_column(0, 0, 40);
        $sheet->set_column(1, 1, 18);
        $sheet->set_column(2, 3, 34);
        $sheet->set_column(4, 7, 22);
        $line = 1;
        foreach ($roster['rows'] as $row) {
            $m = $engagement['users'][$row->id] ?? null;
            $valid = $m && empty($engagement['unavailable']) && empty($engagement['truncated']);
            $sheet->write_string($line, 0, fullname($row));
            if ($row->progress !== null) {
                $sheet->write_number($line, 1, $row->progress / 100, $percent);
            }
            $sheet->write_string($line, 2, $row->lastaccess ? userdate($row->lastaccess) : get_string('never'));
            $sheet->write_string($line, 3, get_string('risk_' . $row->risk, 'report_coursepulse'));
            if ($valid) {
                $sheet->write_number($line, 4, $m['seconds'] / DAYSECS, $duration);
                $sheet->write_number($line, 5, $m['sessions']);
                $sheet->write_number($line, 6, $m['sessions'] ? $m['seconds'] / $m['sessions'] / DAYSECS : 0, $duration);
                $sheet->write_number($line, 7, $m['events']);
            }
            $line++;
        }
        return $book;
    }
}
