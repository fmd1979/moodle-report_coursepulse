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
/** Printable snapshots built with Moodle's bundled PDF library; never render untrusted HTML. */
class pdf_export {
    /** Convert formatted names to text before spreadsheet writes or PDF escaping. */
    public static function text(string $value): string {
        return html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    /** Escape every dynamic table cell, including names and filter text. */
    public static function table(array $headers, array $rows, array $widths): string {
        $html = '<table cellpadding="4" cellspacing="0" border="1"><thead><tr bgcolor="#17365D">';
        foreach ($headers as $i => $value) {
            $html .= '<th width="' . (int)$widths[$i] . '%"><font color="#FFFFFF"><b>' .
                s(self::text((string)$value)) . '</b></font></th>';
        }
        $html .= '</tr></thead><tbody>';
        foreach ($rows as $i => $row) {
            $html .= '<tr nobr="true" bgcolor="' . ($i % 2 ? '#F1F5F9' : '#FFFFFF') . '">';
            foreach ($row as $j => $value) {
                $html .= '<td width="' . (int)$widths[$j] . '%">' . s(self::text((string)$value)) . '</td>';
            }
            $html .= '</tr>';
        }
        return $html . '</tbody></table>';
    }
    /** Create an A4 landscape document with embedded Unicode font and page numbering. */
    private static function document(string $title, string $name): \pdf {
        global $CFG;
        require_once($CFG->libdir . '/pdflib.php');
        $doc = new \pdf('L', 'mm', 'A4', true, 'UTF-8');
        $doc->SetCreator('CoursePulse');
        $doc->SetTitle(self::text($title . ' - ' . $name));
        $doc->setPrintHeader(false);
        $doc->setPrintFooter(true);
        $doc->SetMargins(12, 12, 12);
        $doc->SetFooterMargin(8);
        $doc->setFooterFont(['freesans', '', 8]);
        $doc->SetAutoPageBreak(true, 15);
        $doc->SetFont('freesans', '', 8.5);
        $doc->AddPage();
        $doc->writeHTML('<h1 style="color:#17365D">' . s(self::text($title)) . '</h1><h2>' .
            s(self::text($name)) . '</h2><p>' . s(get_string('generatedat', 'report_coursepulse')) . ': ' .
            s(userdate(time())) . '</p>');
        return $doc;
    }
    /** Small native vector chart: no browser canvas, remote images or temporary files. */
    private static function chart(\pdf $doc, array $counts): void {
        $height = count($counts) * 6 + 5;
        if ($doc->GetY() + $height > 190) {
            $doc->AddPage();
        }
        $y = $doc->GetY() + 2;
        $max = max(1, max($counts));
        $colours = ['warning' => [202, 138, 4], 'critical' => [220, 38, 38],
            'never' => [100, 116, 139], 'completed' => [22, 163, 74],
            'active' => [37, 99, 235], 'grace' => [147, 104, 230]];
        foreach ($counts as $key => $value) {
            $doc->SetXY(12, $y);
            $doc->Cell(92, 5, get_string('risk_' . $key, 'report_coursepulse'));
            $doc->SetFillColor(...($colours[$key] ?? [37, 99, 235]));
            if ($value > 0) {
                $doc->Rect(105, $y + 1, 130 * $value / $max, 3, 'F');
            }
            $doc->SetXY(240, $y);
            $doc->Cell(25, 5, (string)$value);
            $y += 6;
        }
        $doc->SetXY(12, $y + 3);
    }
    /** Category snapshot with complete-category scope for exports. */
    public static function overview(\stdClass $category, array $data, int $page): \pdf {
        $doc = self::document(get_string('overview', 'report_coursepulse'), format_string($category->name));
        $all = !empty($data['allcourses']);
        $doc->writeHTML('<p><b>' . s($all ? get_string('exportcoursecount', 'report_coursepulse') : get_string('page')) .
            ': ' . ($all ? count($data['rows']) : $page + 1) . '</b></p><p>' .
            s(get_string($all ? 'categoryexportscope' : 'overviewscope', 'report_coursepulse')) . '</p>');
        $headers = [];
        $values = [];
        foreach ($data['totals'] as $key => $value) {
            $headers[] = get_string('summary_' . $key, 'report_coursepulse');
            $values[] = $value;
        }
        $doc->writeHTML(self::table($headers, [$values], [20, 20, 20, 20, 20]));
        self::chart($doc, array_intersect_key($data['totals'], array_flip(['warning', 'critical', 'never', 'completed'])));
        $heads = [get_string('course'), get_string('summary_total', 'report_coursepulse'),
            get_string('progress', 'report_coursepulse'), get_string('summary_warning', 'report_coursepulse'),
            get_string('summary_critical', 'report_coursepulse'), get_string('summary_never', 'report_coursepulse'),
            get_string('summary_completed', 'report_coursepulse')];
        $rows = [];
        foreach ($data['rows'] as $entry) {
            $summary = $entry['summary'];
            $rows[] = [format_string($entry['course']->fullname),
                $summary === null ? get_string('choosegroup', 'report_coursepulse') : $summary['total'],
                $entry['progress'] === null ? '-' : $entry['progress'] . '%',
                $summary['warning'] ?? '-', $summary['critical'] ?? '-', $summary['never'] ?? '-', $summary['completed'] ?? '-'];
        }
        $doc->writeHTML(self::table($heads, $rows, [34, 11, 11, 11, 11, 11, 11]));
        if (!$rows) {
            $doc->writeHTML('<p>' . s(get_string('noauthorisedcourses', 'report_coursepulse')) . '</p>');
        }
        return $doc;
    }
    /** Course/group summary plus all filtered learners or one authorised learner. No IPs. */
    public static function course(\stdClass $course, array $roster, array $engagement, array $filters,
            array $activities = [], bool $showstudents = true): \pdf {
        $doc = self::document(get_string('pluginname', 'report_coursepulse'), format_string($course->fullname));
        $meta = [];
        foreach (['group', 'from', 'to', 'risk', 'search'] as $key) {
            $value = $filters[$key] ?? '';
            if ($key === 'from' || $key === 'to') {
                $value = userdate((int)$value, '%Y-%m-%d');
            }
            $meta[] = [get_string($key === 'group' ? 'exportgroup' : $key, 'report_coursepulse'), $value];
        }
        $doc->writeHTML(self::table([get_string('filter'), get_string('exportvalue', 'report_coursepulse')], $meta, [25, 75]));
        $doc->writeHTML('<p>' . s(get_string(empty($filters['userid']) ? 'pdfscope' : 'pdfstudentscope', 'report_coursepulse')) .
            '</p>');
        $heads = [];
        $values = [];
        foreach (['total', 'active', 'warning', 'critical', 'never', 'completed'] as $key) {
            $heads[] = get_string('summary_' . $key, 'report_coursepulse');
            $values[] = $roster['summary'][$key];
        }
        $doc->writeHTML(self::table($heads, [$values], [20, 16, 16, 16, 16, 16]));
        $doc->writeHTML('<h3>' . s(get_string('statuschart', 'report_coursepulse')) . '</h3>');
        self::chart($doc, array_intersect_key($roster['summary'],
            array_flip(['active', 'warning', 'critical', 'never', 'grace', 'completed'])));
        if ($showstudents) {
            $doc->writeHTML('<p><b>' . s(get_string('exportedstudents', 'report_coursepulse')) . ': ' .
                count($roster['rows']) . '</b></p>');
            $rows = [];
            foreach ($roster['rows'] as $row) {
                $m = $engagement['users'][$row->id] ?? null;
                $valid = $m && empty($engagement['unavailable']) && empty($engagement['truncated']);
                $rows[] = [fullname($row), $row->progress === null ? '-' : $row->progress . '%',
                    $row->lastaccess ? userdate($row->lastaccess) : get_string('never'),
                    get_string('risk_' . $row->risk, 'report_coursepulse'),
                    $valid ? self::duration($m['seconds']) : '-', $valid ? $m['sessions'] : '-',
                    $valid ? self::duration($m['sessions'] ? $m['seconds'] / $m['sessions'] : 0) : '-',
                    $valid ? $m['events'] : '-'];
            }
            $heads = array_map(static fn($key) => get_string($key, 'report_coursepulse'),
                ['student', 'progress', 'lastaccess', 'risk', 'estimatedtime', 'sessions', 'averagesession', 'events']);
            $doc->writeHTML(self::table($heads, $rows, [22, 9, 15, 18, 11, 8, 11, 6]));
            if ($activities) {
                $doc->writeHTML('<h3>' . s(get_string('activity', 'report_coursepulse')) . '</h3>');
                $doc->writeHTML(self::table([get_string('section', 'report_coursepulse'),
                    get_string('activity', 'report_coursepulse'), get_string('completion', 'report_coursepulse')],
                    $activities, [25, 55, 20]));
            }
        }
        $status = !empty($engagement['unavailable']) ? 'nologs' :
            (!empty($engagement['truncated']) ? 'truncated' : 'methodology');
        $doc->writeHTML('<p>' . s(get_string($status, 'report_coursepulse')) . '</p>');
        return $doc;
    }
    /** Elapsed hours are not limited to a 24-hour clock. */
    private static function duration($seconds): string {
        $seconds = (int)$seconds;
        return sprintf('%02d:%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60);
    }
}
