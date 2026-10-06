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
namespace report_coursepulse\local;
defined('MOODLE_INTERNAL') || die();
/** Read-only, group-scoped course report. No persistent user-level cache. */
class report {
    /** @var \stdClass */
    public $course;
    /** @var \context_course */
    public $context;
    /** @var array */
    public $modules = [];
    /** @var int */
    public $group;
    /** @var int */
    public $now;
    /** @var array */
    private $config;
    /** Construct after require_login; reject unauthorised group selection. */
    public function __construct(\stdClass $course, int $group) {
        global $USER, $CFG;
        require_once($CFG->dirroot . '/group/lib.php');
        require_once($CFG->dirroot . '/lib/completionlib.php');
        $this->course = $course;
        $this->context = \context_course::instance($course->id);
        require_capability('report/coursepulse:view', $this->context);
        $this->group = $group;
        $this->now = time();
        $mode = groups_get_course_groupmode($course);
        $allowed = groups_get_all_groups($course->id, $USER->id, $course->defaultgroupingid);
        if ($mode == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $this->context)) {
            if (!$group || !isset($allowed[$group])) {
                throw new \required_capability_exception($this->context, 'moodle/site:accessallgroups', 'nopermissions', '');
            }
        }
        if ($group) {
            $valid = groups_get_all_groups($course->id, 0, $course->defaultgroupingid);
            if (!isset($valid[$group])) {
                throw new \moodle_exception('invalidgroupid');
            }
        }
        $this->config = (array)get_config('report_coursepulse');
        if (\completion_info::is_enabled_for_site() && !empty($course->enablecompletion)) {
            foreach (get_fast_modinfo($course)->get_cms() as $cm) {
                if ($cm->visible && get_fast_modinfo($course)->get_section_info($cm->sectionnum)->visible && $cm->completion != COMPLETION_TRACKING_NONE && !$cm->deletioninprogress) {
                    $this->modules[$cm->id] = $cm;
                }
            }
        }
    }
    /** Build an enrolled learner query using Moodle capability and active enrolment rules. */
    private function roster_sql(): array {
        global $DB;
        [$enrolledsql, $params] = get_enrolled_sql($this->context, 'moodle/course:isincompletionreports', $this->group, true);
        $params['accesscourse'] = $this->course->id;
        $params['enrolcourse'] = $this->course->id;
        $params['nowstart'] = $this->now;
        $params['nowend'] = $this->now;
        $params['uesactive'] = ENROL_USER_ACTIVE;
        $params['esactive'] = ENROL_INSTANCE_ENABLED;
        $completionjoin = '';
        $completionfield = '0 AS completed';
        if ($this->modules) {
            [$insql, $inparams] = $DB->get_in_or_equal(array_keys($this->modules), SQL_PARAMS_NAMED, 'cm');
            $params += $inparams;
            $completionjoin = "LEFT JOIN (SELECT userid, COUNT(1) AS completed
                FROM {course_modules_completion} WHERE coursemoduleid $insql
                AND completionstate IN (1, 2, 3) GROUP BY userid) cp ON cp.userid = u.id";
            $completionfield = 'COALESCE(cp.completed, 0) AS completed';
        }
        $sql = "SELECT u.id, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                    u.middlename, u.alternatename, COALESCE(ula.timeaccess, 0) AS lastaccess,
                    ue.enrolled, $completionfield
                  FROM {user} u
             LEFT JOIN {user_lastaccess} ula ON ula.userid = u.id AND ula.courseid = :accesscourse
                  JOIN (SELECT x.userid, MIN(x.timecreated) AS enrolled FROM {user_enrolments} x
                        JOIN {enrol} e ON e.id = x.enrolid WHERE e.courseid = :enrolcourse
                        AND x.status = :uesactive AND e.status = :esactive
                        AND x.timestart <= :nowstart AND (x.timeend = 0 OR x.timeend > :nowend)
                        GROUP BY x.userid) ue ON ue.userid = u.id
                  $completionjoin
                 WHERE u.id IN ($enrolledsql) AND u.deleted = 0 AND u.suspended = 0
              ORDER BY u.lastname, u.firstname, u.id";
        return [$sql, $params];
    }
    /** Stream learner records; only retain the requested page. */
    public function roster(string $risk, string $search, int $page, int $size, int $userid = 0): array {
        global $DB;
        [$sql, $params] = $this->roster_sql();
        $summary = ['total' => 0, 'active' => 0, 'warning' => 0, 'critical' => 0, 'never' => 0,
            'grace' => 0, 'completed' => 0, 'progresssum' => 0, 'bands' => [0, 0, 0, 0, 0]];
        $rows = [];
        $matched = 0;
        $ongoing = (!$this->course->startdate || $this->course->startdate <= $this->now) &&
            (!$this->course->enddate || $this->course->enddate >= $this->now);
        $records = $DB->get_recordset_sql($sql, $params);
        try {
            foreach ($records as $row) {
                $row->progress = $this->modules ? round(100 * $row->completed / count($this->modules), 1) : null;
                $row->risk = metrics::risk((int)$row->lastaccess,
                    max((int)$row->enrolled, (int)$this->course->startdate), $this->now,
                    (int)($this->config['warningdays'] ?? 7), (int)($this->config['criticaldays'] ?? 14),
                    (int)($this->config['gracedays'] ?? 7),
                    $this->modules && $row->completed == count($this->modules), $ongoing);
                $summary['total']++;
                $summary[$row->risk]++;
                $summary['progresssum'] += $row->progress ?? 0;
                if ($row->progress !== null) {
                    $band = $row->progress >= 100 ? 4 : min(3, (int)floor($row->progress / 25));
                    $summary['bands'][$band]++;
                }
                if (($risk && $row->risk !== $risk) || ($userid && $row->id != $userid) ||
                        ($search !== '' && mb_stripos(fullname($row), $search) === false)) {
                    continue;
                }
                if ($matched >= $page * $size && count($rows) < $size) {
                    $rows[$row->id] = $row;
                }
                $matched++;
            }
        } finally {
            $records->close();
        }
        return ['rows' => $rows, 'matched' => $matched, 'summary' => $summary];
    }
    /** Estimate sessions from real user events in this course, for a bounded page of learners. */
    public function engagement(array $userids, int $since, int $until): array {
        global $DB;
        $result = ['users' => [], 'truncated' => false, 'daily' => []];
        if (!$userids) {
            return $result;
        }
        // Log reader availability is checked rather than presuming historical data exists.
        $manager = get_log_manager();
        $readers = $manager->get_readers('\core\log\sql_reader');
        if (!isset($readers['logstore_standard'])) {
            $result['unavailable'] = true;
            return $result;
        }
        [$insql, $params] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'lu');
        $params += ['lc' => $this->course->id, 'ls' => $since, 'le' => $until];
        $sql = "SELECT id, userid, timecreated FROM {logstore_standard_log}
                 WHERE courseid = :lc AND userid $insql AND timecreated >= :ls AND timecreated < :le
                   AND origin IN ('web', 'ws') AND anonymous = 0
                   AND (realuserid IS NULL OR realuserid = 0)
                 ORDER BY timecreated, id";
        $timestamps = [];
        $rs = $DB->get_recordset_sql($sql, $params, 0, 100001);
        $count = 0;
        try {
            foreach ($rs as $event) {
                if (++$count > 100000) {
                    $result['truncated'] = true;
                    break;
                }
                $timestamps[$event->userid][] = (int)$event->timecreated;
                $day = userdate($event->timecreated, '%Y-%m-%d');
                $result['daily'][$day] = ($result['daily'][$day] ?? 0) + 1;
            }
        } finally {
            $rs->close();
        }
        foreach ($userids as $userid) {
            $result['users'][$userid] = metrics::sessions($timestamps[$userid] ?? [],
                60 * (int)($this->config['sessiongap'] ?? 15));
        }
        return $result;
    }
    /** Recent IP-bearing events. Caller must authorise the learner and viewips capability. */
    public function connections(int $userid, int $since, int $until): array {
        global $DB;
        require_capability('report/coursepulse:viewips', $this->context);
        return $DB->get_records_sql("SELECT id, timecreated, ip, origin FROM {logstore_standard_log}
            WHERE courseid = :c AND userid = :u AND timecreated >= :s AND timecreated < :e
            AND origin IN ('web', 'ws') AND anonymous = 0 AND (realuserid IS NULL OR realuserid = 0)
            ORDER BY timecreated DESC, id DESC", ['c' => $this->course->id, 'u' => $userid,
                's' => $since, 'e' => $until], 0, 100);
    }
}
