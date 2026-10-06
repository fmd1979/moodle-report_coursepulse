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
require_once(__DIR__ . '/../../config.php');
require_login();
if (isguestuser()) {
    throw new moodle_exception('noguest');
}
$categoryid = max(0, optional_param('categoryid', 0, PARAM_INT));
$page = max(0, optional_param('page', 0, PARAM_INT));
$categories = \report_coursepulse\local\overview::categories();
if (!$categories) {
    throw new required_capability_exception(context_system::instance(), 'report/coursepulse:viewoverview', 'nopermissions', '');
}
if (!$categoryid) {
    $categoryid = (int)array_key_first($categories);
}
// Recheck arbitrary URL selections, including categories outside the assigned role.
$context = context_coursecat::instance($categoryid);
require_capability('report/coursepulse:viewoverview', $context);
$url = new moodle_url('/report/coursepulse/overview.php', ['categoryid' => $categoryid]);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('overview', 'report_coursepulse'));
$PAGE->set_heading(get_string('overview', 'report_coursepulse'));
$courses = \report_coursepulse\local\overview::courses($categoryid, $page);
$options = [];
foreach ($categories as $category) {
    $options[$category->id] = format_string($category->name, true,
        ['context' => context_coursecat::instance($category->id)]);
}
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('overview', 'report_coursepulse'));
echo $OUTPUT->single_select(new moodle_url('/report/coursepulse/overview.php'), 'categoryid', $options, $categoryid, null);
echo $OUTPUT->notification(get_string('overviewscope', 'report_coursepulse'), 'info');
$table = new html_table();
$table->head = [get_string('course'), get_string('summary_total', 'report_coursepulse'),
    get_string('progress', 'report_coursepulse'), get_string('summary_warning', 'report_coursepulse'),
    get_string('summary_critical', 'report_coursepulse'), get_string('summary_never', 'report_coursepulse'),
    get_string('summary_completed', 'report_coursepulse')];
$totals = array_fill_keys(['total', 'warning', 'critical', 'never', 'completed'], 0);
$chartlabels = [];
$chartvalues = [];
foreach ($courses['rows'] as $course) {
    $coursecontext = context_course::instance($course->id);
    $name = html_writer::link(new moodle_url('/report/coursepulse/index.php', ['id' => $course->id]),
        format_string($course->fullname, true, ['context' => $coursecontext]));
    $summary = \report_coursepulse\local\overview::summary($course);
    if ($summary === null) {
        $table->data[] = [$name, get_string('choosegroup', 'report_coursepulse'), '—', '—', '—', '—', '—'];
        continue;
    }
    $measured = array_sum($summary['bands']);
    $table->data[] = [$name, $summary['total'], $measured ? round($summary['progresssum'] / $measured, 1) . '%' : '—',
        $summary['warning'], $summary['critical'], $summary['never'], $summary['completed']];
    foreach ($totals as $key => $value) {
        $totals[$key] += $summary[$key];
    }
    $chartlabels[] = format_string($course->shortname, true, ['context' => $coursecontext]);
    $chartvalues[] = $summary['warning'] + $summary['critical'] + $summary['never'];
}
echo html_writer::start_div('coursepulse-cards');
foreach ($totals as $key => $value) {
    echo html_writer::div(html_writer::tag('strong', (string)$value) .
        html_writer::tag('span', get_string('summary_' . $key, 'report_coursepulse')), 'coursepulse-card');
}
echo html_writer::end_div();
if ($chartlabels) {
    $chart = new \core\chart_bar();
    $chart->set_horizontal(true);
    $series = new \core\chart_series(get_string('followup', 'report_coursepulse'), $chartvalues);
    $series->set_color('#dc2626');
    $chart->add_series($series);
    $chart->set_labels($chartlabels);
    $chart->set_legend_options(['display' => false]);
    $chart->get_xaxis(0, true)->set_min(0);
    $chart->get_xaxis(0, true)->set_stepsize(max(1, (int)ceil(max($chartvalues) / 5)));
    echo $OUTPUT->heading(get_string('followup', 'report_coursepulse'), 3);
    echo $OUTPUT->render($chart);
}
echo html_writer::div(html_writer::table($table), 'table-responsive');
if (!$courses['matched']) {
    echo $OUTPUT->notification(get_string('noauthorisedcourses', 'report_coursepulse'), 'info');
}
echo $OUTPUT->paging_bar($courses['matched'], $page, 25, $url);
echo $OUTPUT->footer();
