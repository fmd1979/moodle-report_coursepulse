# Metrics and limitations

## Completion

Denominator: visible course modules in visible sections whose completion mode is automatic or manual, provided course and site completion are enabled. Availability conditions do not remove future activities from the denominator. No weighting by grade, module type or expected effort.

Numerator: completion states 1 (complete), 2 (complete/pass), 3 (complete/fail). Fail counts as completed but is shown explicitly in learner detail. All tracked activities completed is not the core course-completion status and does not certify achievement. With no tracked activities the percentage is unavailable, never 0%.

## Inactivity

Source: course-specific `user_lastaccess`, not site-wide last login. New enrolments get a grace period from the latest of active enrolment creation and course start. Completed learners are classified first. Outside course dates, incomplete learners are exempt from inactivity classification. After grace: no recorded course access → no access; threshold 7 days → warning; 14 days → high risk. Thresholds are configurable.

Users with custom student roles need `moodle/course:isincompletionreports`. Dormant accounts cannot be inferred to be dropouts. Late deadlines, repeated failures, manual dropout confirmations and scheduled alerts are future extensions.

## Time

Only retained standard log events whose courseid is the selected course, origin web or ws, non-anonymous and not "login as" are considered. For each learner, timestamps are sorted. Gaps <= configured threshold add to estimated duration; larger gaps start a new session and add no idle time. An isolated event creates a session with zero measurable duration. Duplicate timestamps add zero seconds.

Example: events at 10:00, 10:04, 10:09 and 11:00 with threshold 15 minutes → 9 estimated minutes, 2 sessions, mean session 4m30s. The 51-minute gap is excluded.

Estimates are clipped to the selected date range; boundary sessions may be partial. Web and mobile API events can overlap and estimates do not represent verified attention. Site navigation with courseid 0, offline study, reading without events and activity in other courses are excluded. The report does not reconstruct global Moodle login sessions.

Per-page mean learner time includes zero-event learners; mean session is duration/sessions. Dates default to 30 calendar dates; max range difference 90 days. At 100,001 fetched events, time metrics are suppressed and user must narrow the query. IP history lists at most the latest 100 event addresses, not every login.

## Security and data scope

Every request requires course login and view capability. Roster uses active enrolments and learner capability. Detail uses this same roster, preventing arbitrary userid access. Separate-group restrictions apply to detail and CSV too. Exports additionally require export capability and sesskey. IP history additionally requires viewips. Parameterised DML, escaped output and formula-safe CSV protect inputs/outputs.

No persistent personal summaries, historical snapshots or outbound requests. Core log retention and privacy exports/deletion remain authoritative. No promise of exact dropout detection, attendance validation or identity validation.

## Excel export (0.2.0)

Native XLSX with summary and learners sheets. The learner sheet contains all authorised learners matching the filters, up to 5,000; exceeding this limit rejects the export explicitly. Its events are estimated across this selection using the existing 100,000-event cap. Reaching the cap leaves event/time cells blank and records the reason in Summary; completion still exports. IPs are not included. Progress bands use current progress rounded to one decimal, with complete at 100%. Status/completion charts cover the entire authorised course/group; the daily trend covers displayed learners.
