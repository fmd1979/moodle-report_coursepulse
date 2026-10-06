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
namespace report_coursepulse;
defined('MOODLE_INTERNAL') || die();
use report_coursepulse\local\metrics;
use report_coursepulse\local\report;
/** Calculations and data access boundaries. */
final class report_test extends \advanced_testcase {
    /** Inactivity gaps, duplicate times and isolated events cannot inflate duration. */
    public function test_session_estimation(): void {
        $this->assertSame(['seconds' => 540, 'sessions' => 2, 'events' => 4],
            metrics::sessions([3600, 0, 240, 540], 900));
        $this->assertSame(['seconds' => 0, 'sessions' => 1, 'events' => 2], metrics::sessions([0, 0], 900));
        $this->assertSame(['seconds' => 0, 'sessions' => 0, 'events' => 0], metrics::sessions([], 900));
        $this->assertSame(900, metrics::sessions([0, 900], 900)['seconds']);
        $this->assertSame(0, metrics::sessions([0, 901], 900)['seconds']);
    }
    /** Risk thresholds, grace and completion precedence. */
    public function test_risk(): void {
        $now = 30 * DAYSECS;
        $this->assertSame('never', metrics::risk(0, 0, $now, 7, 14, 7, false, true));
        $this->assertSame('warning', metrics::risk($now - 7 * DAYSECS, 0, $now, 7, 14, 7, false, true));
        $this->assertSame('critical', metrics::risk($now - 14 * DAYSECS, 0, $now, 7, 14, 7, false, true));
        $this->assertSame('grace', metrics::risk(0, $now, $now, 7, 14, 7, false, true));
        $this->assertSame('grace', metrics::risk(0, 0, $now, 7, 14, 7, false, false));
        $this->assertSame('completed', metrics::risk(0, 0, $now, 7, 14, 7, true, false));
    }
    /** Spreadsheet formulas must not execute when exported. */
    public function test_csv_formulas(): void {
        foreach (['=1+1', '+cmd', '-2+1', '@SUM(1)', '  =1'] as $value) {
            $this->assertSame("'" . $value, metrics::csvcell($value));
        }
        $this->assertSame('Ana', metrics::csvcell('Ana'));
    }
    /** Group boundaries apply even when the caller asks for a specific learner. */
    public function test_separate_groups_and_enrolment_scope(): void {
        global $DB, $CFG;
        $this->resetAfterTest();
        require_once($CFG->dirroot . '/group/lib.php');
        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['groupmode' => SEPARATEGROUPS, 'groupmodeforce' => 1]);
        $teacher = $generator->create_user();
        $a = $generator->create_user();
        $b = $generator->create_user();
        $generator->enrol_user($teacher->id, $course->id, 'editingteacher');
        $generator->enrol_user($a->id, $course->id, 'student');
        $generator->enrol_user($b->id, $course->id, 'student');
        $groupa = $generator->create_group(['courseid' => $course->id]);
        $groupb = $generator->create_group(['courseid' => $course->id]);
        groups_add_member($groupa->id, $teacher->id);
        groups_add_member($groupa->id, $a->id);
        groups_add_member($groupb->id, $b->id);
        $context = \context_course::instance($course->id);
        $teacherrole = $DB->get_field('role', 'id', ['shortname' => 'editingteacher']);
        assign_capability('moodle/site:accessallgroups', CAP_PROHIBIT, $teacherrole, $context->id);
        $this->setUser($teacher);
        $report = new report($course, $groupa->id);
        $data = $report->roster('', '', 0, 25);
        $this->assertSame([(int)$a->id], array_keys($data['rows']));
        $this->assertSame([], $report->roster('', '', 0, 25, $b->id)['rows']);
        $this->expectException(\required_capability_exception::class);
        new report($course, $groupb->id);
    }
    /** Learners must not be able to construct or view the report. */
    public function test_student_has_no_dashboard_access(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $student = $generator->create_user();
        $generator->enrol_user($student->id, $course->id, 'student');
        $this->setUser($student);
        $this->expectException(\required_capability_exception::class);
        new report($course, 0);
    }
    /** A completed but failed activity counts as complete; hidden content is excluded. */
    public function test_completion_and_suspended_enrolments(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('enablecompletion', 1);
        $g = $this->getDataGenerator();
        $course = $g->create_course(['enablecompletion' => 1]);
        $a = $g->create_user();
        $b = $g->create_user();
        $g->enrol_user($a->id, $course->id, 'student');
        $g->enrol_user($b->id, $course->id, 'student');
        $page = $g->create_module('page', ['course' => $course->id, 'completion' => 1]);
        $g->create_module('page', ['course' => $course->id, 'completion' => 1, 'visible' => 0]);
        $DB->insert_record('course_modules_completion', (object)['coursemoduleid' => $page->cmid,
            'userid' => $a->id, 'completionstate' => COMPLETION_COMPLETE_FAIL, 'timemodified' => time()]);
        $DB->set_field('user_enrolments', 'status', ENROL_USER_SUSPENDED, ['userid' => $b->id]);
        $r = new report($course, 0);
        $data = $r->roster('', '', 0, 25);
        $this->assertCount(1, $r->modules);
        $this->assertCount(1, $data['rows']);
        $this->assertSame([0, 0, 0, 0, 1], $data['summary']['bands']);
        $this->assertEquals(100, $data['rows'][$a->id]->progress);
        $this->assertSame('completed', $data['rows'][$a->id]->risk);
    }
    /** Time only includes real user events in the selected course; IPs require a separate permission. */
    public function test_log_scope_and_ip_permissions(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('enabled_stores', 'logstore_standard', 'tool_log');
        get_log_manager(true);
        $g = $this->getDataGenerator();
        $course = $g->create_course();
        $other = $g->create_course();
        $student = $g->create_user();
        $teacher = $g->create_user();
        $g->enrol_user($student->id, $course->id, 'student');
        $g->enrol_user($teacher->id, $course->id, 'editingteacher');
        $context = \context_course::instance($course->id);
        $base = time() - 3600;
        foreach ([[0, 'web', $course->id, null], [240, 'web', $course->id, null],
                [540, 'ws', $course->id, null], [600, 'cli', $course->id, null],
                [660, 'web', $other->id, null], [720, 'web', $course->id, $teacher->id]] as $data) {
            $DB->insert_record('logstore_standard_log', (object)[
                'eventname' => '\\core\\event\\course_viewed', 'component' => 'core',
                'action' => 'viewed', 'target' => 'course', 'crud' => 'r', 'edulevel' => 2,
                'contextid' => $context->id, 'contextlevel' => CONTEXT_COURSE,
                'contextinstanceid' => $course->id, 'userid' => $student->id,
                'courseid' => $data[2], 'anonymous' => 0, 'timecreated' => $base + $data[0],
                'origin' => $data[1], 'ip' => '192.0.2.1', 'realuserid' => $data[3],
            ]);
        }
        $r = new report($course, 0);
        $measure = $r->engagement([$student->id], $base, $base + 1000);
        $this->assertEmpty($measure['unavailable'] ?? false);
        $this->assertSame(['seconds' => 540, 'sessions' => 1, 'events' => 3], $measure['users'][$student->id]);
        $this->assertCount(3, $r->connections($student->id, $base, $base + 1000));
        $this->setUser($teacher);
        $r = new report($course, 0);
        get_log_manager()->dispose();
        $this->expectException(\required_capability_exception::class);
        $r->connections($student->id, $base, $base + 1000);
    }
    /** Absence of tracked completion must be unavailable rather than zero percent. */
    public function test_no_completion_is_unavailable(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $g = $this->getDataGenerator();
        $course = $g->create_course(['enablecompletion' => 0]);
        $student = $g->create_user();
        $g->enrol_user($student->id, $course->id, 'student');
        $r = new report($course, 0);
        $rows = $r->roster('', '', 0, 25)['rows'];
        $this->assertNull($rows[$student->id]->progress);
    }

}
