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
/** Pure calculations shared by reports and tests. */
class metrics {
    /** Sum only intervals between consecutive events within the inactivity threshold. */
    public static function sessions(array $timestamps, int $gap): array {
        sort($timestamps, SORT_NUMERIC);
        $result = ['seconds' => 0, 'sessions' => 0, 'events' => count($timestamps)];
        $previous = null;
        foreach ($timestamps as $timestamp) {
            if ($previous === null || $timestamp - $previous > $gap) {
                $result['sessions']++;
            } else {
                $result['seconds'] += max(0, $timestamp - $previous);
            }
            $previous = $timestamp;
        }
        return $result;
    }
    /** Operational risk indicator, never a confirmed dropout classification. */
    public static function risk(int $lastaccess, int $enrolled, int $now, int $warning,
            int $critical, int $grace, bool $complete, bool $ongoing): string {
        if ($complete) {
            return 'completed';
        }
        if (!$ongoing || $now - $enrolled < $grace * DAYSECS) {
            return 'grace';
        }
        if (!$lastaccess) {
            return 'never';
        }
        $days = ($now - $lastaccess) / DAYSECS;
        if ($days >= max($warning, $critical)) {
            return 'critical';
        }
        return $days >= $warning ? 'warning' : 'active';
    }
    /** Make a CSV cell safe for spreadsheet programs interpreting formulas. */
    public static function csvcell($value): string {
        $value = (string)$value;
        if (preg_match('/^[\s]*[=+@-]/u', $value)) {
            return "'" . $value;
        }
        return $value;
    }
}
