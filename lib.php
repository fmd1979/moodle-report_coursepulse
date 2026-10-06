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
defined('MOODLE_INTERNAL') || die();
/** Add the course report navigation entry. */
function report_coursepulse_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/coursepulse:view', $context)) {
        $navigation->add(get_string('pluginname', 'report_coursepulse'),
            new moodle_url('/report/coursepulse/index.php', ['id' => $course->id]),
            navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}

/** Entry point from course-category settings, including report-only category roles. */
function report_coursepulse_extend_navigation_category_settings($navigation, $context) {
    if (has_capability('report/coursepulse:viewoverview', $context)) {
        $navigation->add(get_string('overview', 'report_coursepulse'),
            new moodle_url('/report/coursepulse/overview.php', ['categoryid' => $context->instanceid]),
            navigation_node::TYPE_SETTING, null, 'coursepulseoverview', new pix_icon('i/report', ''));
    }
}
/** Front-page report link. A direct custom-menu URL also works with Boost themes. */
function report_coursepulse_extend_navigation_frontpage($navigation, $course, $context) {
    if (isloggedin() && !isguestuser() && \report_coursepulse\local\overview::categories()) {
        $navigation->add(get_string('overview', 'report_coursepulse'),
            new moodle_url('/report/coursepulse/overview.php'), navigation_node::TYPE_SETTING,
            null, 'coursepulseoverview', new pix_icon('i/report', ''));
    }
}
