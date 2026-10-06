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
$plugin->component = 'report_coursepulse';
$plugin->version = 2026100601;
$plugin->requires = 2025041400;
$plugin->maturity = MATURITY_BETA;
$plugin->release = '0.2.0-beta';
$plugin->supported = [500, 501];
