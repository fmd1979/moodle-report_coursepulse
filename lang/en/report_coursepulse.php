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
$string['pluginname'] = 'CoursePulse — Course dashboard';
$string['coursepulse:view'] = 'View the course engagement dashboard';
$string['coursepulse:viewips'] = 'View learner connection IP addresses';
$string['coursepulse:export'] = 'Export course engagement data';
$string['privacy:metadata'] = 'CoursePulse reads existing Moodle enrolment, completion, last access and standard log data. It does not persist additional personal data or send data externally.';
$string['sessiongap'] = 'Inactivity threshold (minutes)';
$string['sessiongap_desc'] = 'A gap larger than this threshold starts a new estimated session. Single-event sessions have zero measurable duration.';
$string['warningdays'] = 'Days without course access: warning';
$string['criticaldays'] = 'Days without course access: high risk';
$string['gracedays'] = 'Grace period after enrolment or course start (days)';
$string['from'] = 'Events from';
$string['to'] = 'Events through';
$string['risk'] = 'Follow-up status';
$string['search'] = 'Search learner name';
$string['invaliddates'] = 'Choose a past or current date range of up to 90 days, with the end on or after the start.';
$string['notstudent'] = 'This learner is not in the active, authorised course and group scope.';
$string['risk_active'] = 'Recent course access';
$string['risk_warning'] = 'Needs follow-up';
$string['risk_critical'] = 'High inactivity risk';
$string['risk_never'] = 'No recorded course access';
$string['risk_grace'] = 'Grace period / course outside active dates';
$string['risk_completed'] = 'All tracked activities completed';
$string['methodology'] = 'Time is an estimate from consecutive course events, not verified study time. Inactivity is a follow-up indicator, not confirmed dropout. Progress includes visible activities with completion tracking; completed-but-failed activities count as completed, not passed. Progress and inactivity reflect the current course state; date filters apply only to event/time/IP measurements.';
$string['nocompletion'] = 'No visible activities with completion tracking are available. Progress is unavailable; enable course and activity completion.';
$string['nologs'] = 'The standard log reader is not enabled. Time and connection history are unavailable.';
$string['truncated'] = 'The event limit (100,000) was reached. Time measurements are suppressed to avoid showing incomplete estimates. Reduce the period or open a single learner.';
$string['summary_total'] = 'Active enrolments';
$string['summary_active'] = 'Recent access';
$string['summary_warning'] = 'Needs follow-up';
$string['summary_critical'] = 'High inactivity risk';
$string['summary_never'] = 'No recorded access';
$string['summary_completed'] = 'Tracked activities completed';
$string['meanprogress'] = 'Mean completion progress (entire authorised group): {$a}%';
$string['students'] = 'Learners';
$string['student'] = 'Learner';
$string['progress'] = 'Completion progress';
$string['lastaccess'] = 'Last course access';
$string['estimatedtime'] = 'Estimated time (hh:mm:ss)';
$string['sessions'] = 'Estimated sessions';
$string['averagesession'] = 'Mean session (hh:mm:ss)';
$string['events'] = 'Course events';
$string['section'] = 'Section';
$string['activity'] = 'Activity';
$string['completion'] = 'Completion status';
$string['complete'] = 'Completed';
$string['passed'] = 'Completed, passed';
$string['failed'] = 'Completed, failed';
$string['pending'] = 'Pending';
$string['connections'] = 'Latest 100 course events with connection IP';
$string['ip'] = 'IP address';
$string['origin'] = 'Origin';
$string['back'] = 'Back to dashboard';
$string['ipnotice'] = 'These are event IPs, not necessarily login events. Shared networks, proxies and VPNs can share or change an IP. IPs do not establish a person’s identity.';
$string['notavailable'] = 'Unavailable';
$string['pagemean'] = 'Mean estimated time for learners on this page (including those with no events): {$a}';
$string['pagescope'] = 'Cards and progress chart cover the entire authorised course/group. On-screen event/time statistics and CSV cover the displayed page. Excel exports all filtered learners (maximum 5,000). No events can also mean old logs were deleted; zero duration does not prove zero study time.';
$string['exportpage'] = 'Export this page to CSV';
$string['eventtrend'] = 'Daily course events (learners on this page)';
$string['exportexcel'] = 'Download Excel (.xlsx) — all filtered learners';
$string['excelsummary'] = 'Summary';
$string['excelscope'] = 'Summary scope';
$string['excelscope_desc'] = 'Summary figures cover the entire authorised course/group. The learners sheet includes everyone matching the filters, up to 5,000. Dates filter events/time; completion is current.';
$string['exportedstudents'] = 'Exported learners';
$string['measurement'] = 'Time measurement';
$string['excellimit'] = 'Excel export supports up to 5,000 learners. Select a group or use filters to reduce the selection.';
$string['statuschart'] = 'Course/group follow-up status';
$string['progresschart'] = 'Course/group completion distribution';
$string['short_active'] = 'Recent access';
$string['short_warning'] = 'Follow-up';
$string['short_critical'] = 'High risk';
$string['short_never'] = 'No access';
$string['short_grace'] = 'Grace / outside dates';
$string['short_completed'] = 'Completed';

$string['coursepulse:viewoverview'] = 'View category overview outside courses';
$string['coursepulse:viewstudents'] = 'View individual student data and details';
$string['overview'] = 'CoursePulse: category overview';
$string['overviewscope'] = 'Includes authorised courses in this category and its subcategories. Cards and chart summarise only the current page (up to 25 courses). Counts are course enrolments: a student in two courses counts twice. Courses requiring group selection are excluded from these totals. Time estimates and individual details are available inside each authorised course report.';
$string['choosegroup'] = 'Open report and select an authorised group';
$string['followup'] = 'Enrolments requiring follow-up by course';
$string['noauthorisedcourses'] = 'No authorised courses in this category.';
