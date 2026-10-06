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
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/csvlib.class.php');
require_once($CFG->dirroot . '/group/lib.php');

$id = required_param('id', PARAM_INT);
$course = get_course($id);
require_login($course);
$context = context_course::instance($id);
require_capability('report/coursepulse:view', $context);
$group = groups_get_course_group($course, true);
$page = max(0, optional_param('page', 0, PARAM_INT));
$userid = max(0, optional_param('userid', 0, PARAM_INT));
$risk = optional_param('risk', '', PARAM_ALPHA);
$search = trim(optional_param('search', '', PARAM_TEXT));
$export = optional_param('export', false, PARAM_BOOL);
$today = usergetmidnight(time());
$from = isset($_GET['from']) && is_array($_GET['from']) ? $today - 29 * DAYSECS :
    optional_param('from', $today - 29 * DAYSECS, PARAM_INT);
$to = isset($_GET['to']) && is_array($_GET['to']) ? $today : optional_param('to', $today, PARAM_INT);
$risks = ['' => get_string('all')];
foreach (['active', 'warning', 'critical', 'never', 'grace', 'completed'] as $key) {
    $risks[$key] = get_string('risk_' . $key, 'report_coursepulse');
}
if (!array_key_exists($risk, $risks)) {
    $risk = '';
}
$params = ['id' => $id, 'group' => $group, 'from' => $from, 'to' => $to, 'risk' => $risk, 'search' => $search];
$url = new moodle_url('/report/coursepulse/index.php', $params);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('pluginname', 'report_coursepulse'));
$PAGE->set_heading(format_string($course->fullname));
$report = new \report_coursepulse\local\report($course, (int)$group);
$form = new \report_coursepulse\form\filters($url, ['courseid' => $id, 'group' => $group, 'risks' => $risks], 'get');
if ($data = $form->get_data()) {
    $from = (int)$data->from;
    $to = (int)$data->to;
    $risk = $data->risk;
    $search = trim($data->search);
    $params = ['id' => $id, 'group' => $group, 'from' => $from, 'to' => $to, 'risk' => $risk, 'search' => $search];
    $url = new moodle_url('/report/coursepulse/index.php', $params);
    $PAGE->set_url($url);
}
if ($from < 0 || $to < $from || $to - $from > 90 * DAYSECS || $to > $today) {
    throw new moodle_exception('invaliddates', 'report_coursepulse');
}
$form->set_data((object)$params);
$until = min(time() + 1, $to + DAYSECS);
$size = 25;
$roster = $report->roster($userid ? '' : $risk, $userid ? '' : $search, $userid ? 0 : $page, $size, $userid);
if ($userid && !isset($roster['rows'][$userid])) {
    throw new moodle_exception('notstudent', 'report_coursepulse');
}
$engagement = $report->engagement(array_keys($roster['rows']), $from, $until);
$formatduration = static function($seconds) {
    return sprintf('%02d:%02d:%02d', intdiv((int)$seconds, 3600), intdiv((int)$seconds % 3600, 60), (int)$seconds % 60);
};
if ($export) {
    require_capability('report/coursepulse:export', $context);
    require_sesskey();
    $csv = new csv_export_writer();
    $csv->set_filename('coursepulse_' . $id . '_page_' . ($page + 1));
    $csv->add_data(array_map(static fn($key) => get_string($key, 'report_coursepulse'),
        ['student', 'progress', 'lastaccess', 'risk', 'estimatedtime', 'sessions', 'averagesession', 'events']));
    foreach ($roster['rows'] as $row) {
        $m = $engagement['users'][$row->id] ?? null;
        // Missing or truncated measurements must never be represented as complete zero measurements.
        $valid = $m && empty($engagement['truncated']);
        $csv->add_data(array_map(['\report_coursepulse\local\metrics', 'csvcell'], [fullname($row),
            $row->progress === null ? '' : $row->progress . '%',
            $row->lastaccess ? userdate($row->lastaccess) : '', $risks[$row->risk],
            $valid ? $formatduration($m['seconds']) : '', $valid ? $m['sessions'] : '',
            $valid ? $formatduration($m['sessions'] ? $m['seconds'] / $m['sessions'] : 0) : '',
            $valid ? $m['events'] : '']));
    }
    $csv->download_file();
    exit;
}
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'report_coursepulse'));
echo $OUTPUT->notification(get_string('methodology', 'report_coursepulse'), 'info');
groups_print_course_menu($course, new moodle_url('/report/coursepulse/index.php', ['id' => $id]));
$form->display();
if (!$report->modules) {
    echo $OUTPUT->notification(get_string('nocompletion', 'report_coursepulse'), 'warning');
}
if (!empty($engagement['unavailable'])) {
    echo $OUTPUT->notification(get_string('nologs', 'report_coursepulse'), 'warning');
}
if ($engagement['truncated']) {
    echo $OUTPUT->notification(get_string('truncated', 'report_coursepulse'), 'warning');
}
$s = $roster['summary'];
echo html_writer::start_div('coursepulse-cards');
foreach (['total', 'active', 'warning', 'critical', 'never', 'completed'] as $key) {
    echo html_writer::div(html_writer::tag('strong', (string)$s[$key]) .
        html_writer::tag('span', get_string('summary_' . $key, 'report_coursepulse')), 'coursepulse-card');
}
echo html_writer::end_div();
if ($report->modules && $s['total']) {
    echo html_writer::tag('p', get_string('meanprogress', 'report_coursepulse', round($s['progresssum'] / $s['total'], 1)));
}
if (!$userid && $s['total']) {
    $chart = new \core\chart_bar();
    $chart->add_series(new \core\chart_series(get_string('students', 'report_coursepulse'),
        array_map(static fn($key) => $s[$key], ['active', 'warning', 'critical', 'never', 'grace', 'completed'])));
    $chart->set_labels(array_map(static fn($key) => get_string('risk_' . $key, 'report_coursepulse'),
        ['active', 'warning', 'critical', 'never', 'grace', 'completed']));
    echo $OUTPUT->render($chart);
}
if ($roster['rows'] && empty($engagement['unavailable']) && !$engagement['truncated']) {
    echo $OUTPUT->heading(get_string('eventtrend', 'report_coursepulse'), 3);
    $labels = [];
    $counts = [];
    for ($day = $from; $day <= $to; $day += DAYSECS) {
        $key = userdate($day, '%Y-%m-%d');
        $labels[] = $key;
        $counts[] = $engagement['daily'][$key] ?? 0;
    }
    $trend = new \core\chart_line();
    $trend->add_series(new \core\chart_series(get_string('events', 'report_coursepulse'), $counts));
    $trend->set_labels($labels);
    echo $OUTPUT->render($trend);
}
if ($userid) {
    $learner = $roster['rows'][$userid];
    echo $OUTPUT->heading(fullname($learner), 3);
    echo html_writer::link($url, get_string('back', 'report_coursepulse'));
    $completion = new completion_info($course);
    $activitytable = new html_table();
    $activitytable->head = [get_string('section', 'report_coursepulse'), get_string('activity', 'report_coursepulse'),
        get_string('completion', 'report_coursepulse')];
    foreach ($report->modules as $cm) {
        $state = $completion->get_data($cm, false, $userid)->completionstate;
        $key = match ((int)$state) {
            COMPLETION_COMPLETE => 'complete', COMPLETION_COMPLETE_PASS => 'passed',
            COMPLETION_COMPLETE_FAIL => 'failed', default => 'pending',
        };
        $activitytable->data[] = [s(get_section_name($course, $cm->sectionnum)),
            format_string($cm->name, true, ['context' => context_module::instance($cm->id)]),
            get_string($key, 'report_coursepulse')];
    }
    echo html_writer::table($activitytable);
    if (has_capability('report/coursepulse:viewips', $context) && empty($engagement['unavailable'])) {
        echo $OUTPUT->heading(get_string('connections', 'report_coursepulse'), 3);
        echo html_writer::tag('p', get_string('ipnotice', 'report_coursepulse'));
        $iptable = new html_table();
        $iptable->head = [get_string('date'), get_string('ip', 'report_coursepulse'), get_string('origin', 'report_coursepulse')];
        foreach ($report->connections($userid, $from, $until) as $event) {
            $iptable->data[] = [userdate($event->timecreated), s($event->ip ?? ''), s($event->origin)];
        }
        echo html_writer::table($iptable);
    }
}
$table = new html_table();
$table->head = array_map(static fn($key) => get_string($key, 'report_coursepulse'),
    ['student', 'progress', 'lastaccess', 'risk', 'estimatedtime', 'sessions', 'averagesession', 'events']);
foreach ($roster['rows'] as $row) {
    $m = $engagement['users'][$row->id] ?? null;
    $valid = $m && empty($engagement['truncated']);
    $detail = new moodle_url('/report/coursepulse/index.php', $params + ['userid' => $row->id]);
    $progress = $row->progress === null ? get_string('notavailable', 'report_coursepulse') :
        html_writer::tag('progress', '', ['max' => 100, 'value' => $row->progress,
            'aria-label' => get_string('progress', 'report_coursepulse')]) . ' ' . $row->progress . '%';
    $table->data[] = [html_writer::link($detail, s(fullname($row))), $progress,
        $row->lastaccess ? userdate($row->lastaccess) : get_string('never'),
        html_writer::span($risks[$row->risk], 'coursepulse-risk coursepulse-' . $row->risk),
        $valid ? $formatduration($m['seconds']) : '—', $valid ? $m['sessions'] : '—',
        $valid ? $formatduration($m['sessions'] ? $m['seconds'] / $m['sessions'] : 0) : '—',
        $valid ? $m['events'] : '—'];
}
echo html_writer::div(html_writer::table($table), 'table-responsive');
$measured = array_values($engagement['users']);
if ($measured && !$engagement['truncated']) {
    $mean = array_sum(array_column($measured, 'seconds')) / count($measured);
    echo html_writer::tag('p', get_string('pagemean', 'report_coursepulse', $formatduration($mean)));
}
if (!$userid) {
    echo $OUTPUT->paging_bar($roster['matched'], $page, $size, $url);
}
echo html_writer::tag('p', get_string('pagescope', 'report_coursepulse'));
if (has_capability('report/coursepulse:export', $context)) {
    $exporturl = new moodle_url('/report/coursepulse/index.php', $params +
        ['page' => $page, 'userid' => $userid, 'export' => 1, 'sesskey' => sesskey()]);
    echo html_writer::link($exporturl, get_string('exportpage', 'report_coursepulse'), ['class' => 'btn btn-secondary']);
}
echo $OUTPUT->footer();
