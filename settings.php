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
if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configselect('report_coursepulse/sessiongap',
        get_string('sessiongap', 'report_coursepulse'), get_string('sessiongap_desc', 'report_coursepulse'),
        15, [5 => '5', 10 => '10', 15 => '15', 20 => '20', 30 => '30']));
    foreach (['warningdays' => 7, 'criticaldays' => 14, 'gracedays' => 7] as $key => $default) {
        $options = array_combine(range(1, 60), range(1, 60));
        $settings->add(new admin_setting_configselect('report_coursepulse/' . $key,
            get_string($key, 'report_coursepulse'), '', $default, $options));
    }
}
