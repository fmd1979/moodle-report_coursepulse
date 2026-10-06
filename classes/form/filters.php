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
namespace report_coursepulse\form;
defined('MOODLE_INTERNAL') || die();
/** GET report filters, no state mutation. */
class filters extends \moodleform {
    /** Define filter elements. */
    public function definition() {
        $mform = $this->_form;
        $mform->addElement('hidden', 'id', $this->_customdata['courseid']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'group', $this->_customdata['group']);
        $mform->setType('group', PARAM_INT);
        $mform->addElement('date_selector', 'from', get_string('from', 'report_coursepulse'));
        $mform->addElement('date_selector', 'to', get_string('to', 'report_coursepulse'));
        $mform->addElement('select', 'risk', get_string('risk', 'report_coursepulse'), $this->_customdata['risks']);
        $mform->addElement('text', 'search', get_string('search', 'report_coursepulse'));
        $mform->setType('search', PARAM_TEXT);
        $mform->addElement('submit', 'submitbutton', get_string('filter'));
    }
    /** Date validation also runs before any database queries in index.php. */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if ($data['to'] < $data['from'] || $data['to'] - $data['from'] > 90 * DAYSECS) {
            $errors['to'] = get_string('invaliddates', 'report_coursepulse');
        }
        return $errors;
    }
}
