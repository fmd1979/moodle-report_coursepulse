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
use report_coursepulse\local\overview;
use report_coursepulse\local\report;
/** Category-role access without teaching, administration or course enrolment. */
final class overview_test extends \advanced_testcase {
    /** Build an empty report-only role at the supplied category context. */
    private function analyst($category): array {
        $user = $this->getDataGenerator()->create_user();
        $role = create_role('Follow-up analyst', 'coursepulseanalyst', '');
        $context = \context_coursecat::instance($category->id);
        foreach (['report/coursepulse:viewoverview', 'report/coursepulse:view'] as $capability) {
            assign_capability($capability, CAP_ALLOW, $role, $context->id);
        }
        role_assign($role, $user->id, $context->id);
        $this->setUser($user);
        return [$user, $role, $context];
    }
    /** Subcategories inherit report scope; siblings and hidden courses stay outside it. */
    public function test_category_scope_without_enrolment(): void {
        $this->resetAfterTest();
        $g = $this->getDataGenerator();
        $category = $g->create_category();
        $child = $g->create_category(['parent' => $category->id]);
        $other = $g->create_category();
        $course = $g->create_course(['category' => $category->id]);
        $childcourse = $g->create_course(['category' => $child->id]);
        $g->create_course(['category' => $category->id, 'visible' => 0]);
        $g->create_course(['category' => $other->id]);
        [$user] = $this->analyst($category);
        $this->assertFalse(is_enrolled(\context_course::instance($course->id), $user));
        $this->assertFalse(has_capability('moodle/course:view', \context_course::instance($course->id)));
        $this->assertArrayHasKey($category->id, overview::categories());
        $this->assertArrayHasKey($child->id, overview::categories());
        $this->assertArrayNotHasKey($other->id, overview::categories());
        $data = overview::courses($category->id, 0);
        $this->assertEqualsCanonicalizing([$course->id, $childcourse->id], array_keys($data['rows']));
        $this->assertSame(2, $data['matched']);
        $this->assertCount(1, overview::courses($category->id, 1, 1)['rows']);
        $this->expectException(\required_capability_exception::class);
        overview::courses($other->id, 0);
    }
    /** Summaries disclose no student records without the independent student capability. */
    public function test_summary_role_and_student_denial(): void {
        $this->resetAfterTest();
        $g = $this->getDataGenerator();
        $category = $g->create_category();
        $course = $g->create_course(['category' => $category->id]);
        $student = $g->create_user();
        $g->enrol_user($student->id, $course->id, 'student');
        $this->analyst($category);
        $this->assertSame(1, overview::summary($course)['total']);
        $context = \context_course::instance($course->id);
        $this->assertFalse(has_capability('report/coursepulse:viewstudents', $context));
        $this->assertFalse(has_capability('report/coursepulse:viewips', $context));
        $this->assertFalse(has_capability('report/coursepulse:export', $context));
        $this->expectException(\required_capability_exception::class);
        (new report($course, 0))->roster('', '', 0, 25, $student->id);
    }
    /** Student permission can be granted without adding an enrolment or teacher role. */
    public function test_individual_permission_without_teaching_role(): void {
        $this->resetAfterTest();
        $g = $this->getDataGenerator();
        $category = $g->create_category();
        $course = $g->create_course(['category' => $category->id]);
        $student = $g->create_user();
        $g->enrol_user($student->id, $course->id, 'student');
        [$user, $role, $context] = $this->analyst($category);
        assign_capability('report/coursepulse:viewstudents', CAP_ALLOW, $role, $context->id);
        $this->setUser($user);
        $this->assertArrayHasKey($student->id, (new report($course, 0))->roster('', '', 0, 25, $student->id)['rows']);
    }
    /** Course prohibitions and separate groups are preserved in the overview. */
    public function test_course_override_and_separate_groups(): void {
        $this->resetAfterTest();
        $g = $this->getDataGenerator();
        $category = $g->create_category();
        $course = $g->create_course(['category' => $category->id]);
        $separate = $g->create_course(['category' => $category->id, 'groupmode' => SEPARATEGROUPS]);
        [$user, $role] = $this->analyst($category);
        assign_capability('report/coursepulse:view', CAP_PROHIBIT, $role, \context_course::instance($course->id)->id);
        $this->setUser($user);
        $this->assertArrayNotHasKey($course->id, overview::courses($category->id, 0)['rows']);
        $this->assertNull(overview::summary($separate));
    }
}
