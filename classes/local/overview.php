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
/** Discover report access independently of enrolments and course content permissions. */
class overview {
    /** Categories explicitly available to the current user's overview role. */
    public static function categories(): array {
        global $DB;
        $result = [];
        $rs = $DB->get_recordset('course_categories', null, 'sortorder, id', 'id, name, path');
        try {
            foreach ($rs as $category) {
                if (has_capability('report/coursepulse:viewoverview', \context_coursecat::instance($category->id))) {
                    $result[$category->id] = $category;
                }
            }
        } finally {
            $rs->close();
        }
        return $result;
    }
    /** Authorised courses in a category subtree; size zero returns the complete selection. */
    public static function courses(int $categoryid, int $page, int $size = 25): array {
        global $DB;
        $context = \context_coursecat::instance($categoryid);
        require_capability('report/coursepulse:viewoverview', $context);
        $category = $DB->get_record('course_categories', ['id' => $categoryid], '*', MUST_EXIST);
        $sql = 'SELECT c.* FROM {course} c JOIN {course_categories} cat ON cat.id = c.category
                WHERE (cat.id = :category OR ' . $DB->sql_like('cat.path', ':path') . ')
                ORDER BY c.sortorder, c.id';
        $rs = $DB->get_recordset_sql($sql, ['category' => $categoryid, 'path' => $category->path . '/%']);
        $rows = [];
        $matched = 0;
        try {
            foreach ($rs as $course) {
                $coursecontext = \context_course::instance($course->id);
                if (!has_capability('report/coursepulse:viewoverview', \context_coursecat::instance($course->category)) ||
                        !has_capability('report/coursepulse:view', $coursecontext) ||
                        (!$course->visible && !has_capability('moodle/course:viewhiddencourses', $coursecontext))) {
                    continue;
                }
                if ($size === 0 || ($matched >= max(0, $page) * $size && count($rows) < $size)) {
                    $rows[$course->id] = $course;
                }
                $matched++;
            }
        } finally {
            $rs->close();
        }
        return ['rows' => $rows, 'matched' => $matched];
    }
    /** Course summaries respect separate groups; no implicit all-groups grant. */
    public static function summary(\stdClass $course): ?array {
        global $CFG;
        require_once($CFG->dirroot . '/group/lib.php');
        $context = \context_course::instance($course->id);
        require_capability('report/coursepulse:view', $context);
        if (groups_get_course_groupmode($course) == SEPARATEGROUPS &&
                !has_capability('moodle/site:accessallgroups', $context)) {
            return null;
        }
        return (new report($course, 0))->roster('', '', 0, 0)['summary'];
    }
    /** Shared snapshot for the screen and exports, with independent category export checks. */
    public static function page_data(int $categoryid, int $page, bool $export = false): array {
        if ($export) {
            require_capability('report/coursepulse:exportoverview', \context_coursecat::instance($categoryid));
        }
        $courses = $export ? self::courses($categoryid, 0, 0) : self::courses($categoryid, $page);
        $result = ['rows' => [], 'allcourses' => $export, 'matched' => $courses['matched'],
            'totals' => array_fill_keys(['total', 'warning', 'critical', 'never', 'completed'], 0)];
        foreach ($courses['rows'] as $course) {
            if ($export) {
                // A child/category/course prohibition must not be bypassed via its parent export.
                require_capability('report/coursepulse:exportoverview', \context_course::instance($course->id));
            }
            $summary = self::summary($course);
            $progress = null;
            if ($summary !== null) {
                $measured = array_sum($summary['bands']);
                $progress = $measured ? round($summary['progresssum'] / $measured, 1) : null;
                foreach ($result['totals'] as $key => $value) {
                    $result['totals'][$key] += $summary[$key];
                }
            }
            $result['rows'][$course->id] = ['course' => $course, 'summary' => $summary, 'progress' => $progress];
        }
        return $result;
    }

}
