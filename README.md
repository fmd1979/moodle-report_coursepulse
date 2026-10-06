# CoursePulse — course engagement dashboard

A read-only Moodle course report for teachers and coordinators. It combines current activity completion, inactivity indicators and estimated session time with permission-controlled connection IP history.

**Release:** 0.3.0-beta. **Component:** `report_coursepulse`. **Author:** Franklin David Moya Dávila / SiteEcuador. **License:** GNU GPL v3 or later.

## Features

- Course/group summary cards, a colour-coded follow-up doughnut and a horizontal completion-distribution chart.
- Active learners: active enrolment and `moodle/course:isincompletionreports` capability. Suspended/deleted users and suspended/expired enrolments are excluded.
- Current completion percentage over visible, completion-enabled activities. Hidden sections are excluded. Completed-failed counts as completed, and individual activity status distinguishes pass/fail.
- Learner detail with activity status by section.
- Last course access and configurable warning/high-risk/grace intervals.
- Estimated course sessions, total time, mean session duration, event count and per-page mean learner time.
- Daily event trend for the displayed learners.
- Date (maximum 90 days), name, group and status filters; 25 learners per page.
- Native Excel (.xlsx) export of all filtered learners (up to 5,000), with summary and learner sheets, typed percentages and elapsed durations. CSV export of the displayed page remains available. Both exports protect against spreadsheet formula injection.
- Latest 100 connection events and IPs available only with a separate capability.
- English and Spanish language packs. Native Moodle charts; no third-party JavaScript or external service.

## Requirements and installation

Designed for Moodle 5.0 with PHP 8.2+ and Moodle 5.1 with PHP 8.3+ and MySQL 8.4+. SQL uses Moodle DML and is designed to be portable; see VALIDATION.md for actual tested versions. Do not infer certification from declared supported versions.

1. Back up your site and install on a staging copy first.
2. Upload the release ZIP via **Site administration → Plugins → Install plugins**.
3. Alternatively extract its `coursepulse` directory to `report/coursepulse` (Moodle 5.0) or `public/report/coursepulse` (Moodle 5.1).
4. Run the normal Moodle upgrade through Site administration → Notifications. Do not alter core tables.
5. Visit **Course → Reports → CoursePulse**.
6. Configure thresholds at **Site administration → Plugins → Reports → CoursePulse**.
7. Enable activity completion at site/course/activity level and keep the standard log store enabled.

Teachers can view; editing teachers can export; managers can view IPs. Administrators can assign capabilities via roles. In separate-groups mode, viewers without `moodle/site:accessallgroups` must select a group they belong to. A teacher with no permitted group cannot access the report.

## Category analysts outside courses

Open `/report/coursepulse/overview.php` or `index.php` without a course id. A category-assigned custom role can compare authorised courses without enrolment, teaching or admin permissions. Independent capabilities control category access, individual learner records, exports and IPs. See [docs/ANALISTA_ES.md](docs/ANALISTA_ES.md) for role and menu setup.

Category cards/chart cover the current page (25 courses), counting course enrolments rather than unique students. Separate-group courses without all-groups access require a permitted group inside the course report and are excluded from category totals. Hidden courses require the core hidden-course capability. XLSX remains per course.

## Interpretation and performance

See [docs/METHODOLOGY.md](docs/METHODOLOGY.md). Time is estimated, never verified study time. Indicators are not proof of dropout. IPs are event addresses, not geolocation or proof of identity. No AI, heartbeat tracking, automatic messaging or confirmed-dropout records in this version.

Current completion and last course access are independent of the event date filter. The filter controls events/time/IP only. Excel exports all filtered learners; displayed time statistics and CSV cover only the current page. Course completion and "all tracked activities completed" are not equivalent; grade/course completion rules may differ.

The roster is streamed; only the displayed page is retained. Time calculations are limited to the page or selected learner and at most 100,000 events. A reached event limit suppresses estimates instead of showing incomplete numbers. No plugin cron task or personal-data cache is needed. Large courses still require staging benchmarks: completion aggregation reads tracked activities for the course.

## Privacy

No plugin tables, user preferences, cookies or additional personal-data storage; no outbound traffic. Source data belongs to Moodle core and logstore_standard; their retention/export/deletion policies apply. Downloaded CSV files are controlled by their recipient. The Privacy API null provider describes this read-only design.

## Development and publication

Source: https://github.com/fmd1979/moodle-report_coursepulse

Support and issues: https://github.com/fmd1979/moodle-report_coursepulse/issues

See [CONTRIBUTING.md](CONTRIBUTING.md), [docs/MARKETPLACE.md](docs/MARKETPLACE.md), [docs/GIT_ES.md](docs/GIT_ES.md), [VALIDATION.md](VALIDATION.md) and [CHANGELOG.md](CHANGELOG.md).

A GitHub repository and Marketplace acceptance are separate. This beta is not submitted, approved or certified. Support and issue tracking should be enabled on the actual public repository before submission. The proposed component name must be checked for availability before listing.
