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
/** Exercise the native chart renderer and navigation API across Moodle 5 branches. */
final class compatibility_test extends \advanced_testcase {
    /** Native renderer exposes accessible containers and serializes datasets for client-side charts. */
    public function test_native_chart_rendering(): void {
        global $PAGE, $OUTPUT;
        $this->resetAfterTest();
        $this->setAdminUser();
        $PAGE->set_context(\context_system::instance());
        $PAGE->set_url('/report/coursepulse/index.php');
        $pie = new \core\chart_pie();
        $pie->set_doughnut(true);
        $pie->add_series(new \core\chart_series('Learners', [2, 3]));
        $pie->set_labels(['Recent access', 'Follow-up']);
        $html = $OUTPUT->render($pie);
        $this->assertStringContainsString('chart-image', $html);
        $this->assertStringContainsString('aria-describedby', $html);
        $data = json_encode($pie, JSON_THROW_ON_ERROR);
        $this->assertStringContainsString('Recent access', $data);
        $this->assertStringContainsString('Follow-up', $data);
        $bar = new \core\chart_bar();
        $bar->set_horizontal(true);
        $bar->add_series(new \core\chart_series('Progress', [1, 4]));
        $bar->set_labels(['Pending', 'Completed']);
        $bar->get_xaxis(0, true)->set_stepsize(1);
        $html = $OUTPUT->render($bar);
        $this->assertStringContainsString('chart-image', $html);
        $this->assertStringContainsString('chart-table-data', $html);
        $data = json_encode($bar, JSON_THROW_ON_ERROR);
        $this->assertStringContainsString('Pending', $data);
        $this->assertStringContainsString('Completed', $data);
    }
    /** Native navigation nodes work with both global and namespaced Moodle APIs. */
    public function test_navigation_entries_and_permissions(): void {
        global $CFG;
        $this->resetAfterTest();
        require_once($CFG->dirroot . '/report/coursepulse/lib.php');
        $g = $this->getDataGenerator();
        $category = $g->create_category();
        $course = $g->create_course(['category' => $category->id]);
        $student = $g->create_user();
        $g->enrol_user($student->id, $course->id, 'student');
        $nodeclass = class_exists(\core\navigation\navigation_node::class) ?
            \core\navigation\navigation_node::class : \navigation_node::class;
        $context = \context_course::instance($course->id);
        $this->setAdminUser();
        $node = new $nodeclass(['text' => 'Reports']);
        \report_coursepulse_extend_navigation_course($node, $course, $context);
        $this->assertCount(1, $node->children);
        $node = new $nodeclass(['text' => 'Category']);
        \report_coursepulse_extend_navigation_category_settings($node, \context_coursecat::instance($category->id));
        $this->assertCount(1, $node->children);
        $this->setUser($student);
        $node = new $nodeclass(['text' => 'Reports']);
        \report_coursepulse_extend_navigation_course($node, $course, $context);
        $this->assertCount(0, $node->children);
    }
}
