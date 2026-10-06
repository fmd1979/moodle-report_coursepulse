# Changelog

## 0.3.0-beta — 2026-10-06

- External category overview with authorised subcategory courses, paginated comparisons and follow-up chart.
- Report-only category roles no longer need course enrolment or course content access.
- Separate overview/student permissions; summary-only roles cannot retrieve individual rows or export them.
- Respect course prohibitions, hidden courses and separate groups; document analyst setup and menu entry.
- Add four category-role regression tests and install required en_AU locale in CI.


## 0.2.0-beta — 2026-10-06

Native XLSX export of all filtered learners (maximum 5,000) with summary and learner sheets, numeric percentages and elapsed-time formats. Group and export permissions remain enforced. Added coloured status doughnut, full-scope completion bands and responsive side-by-side chart cards; daily participation uses blue bars and shorter date labels. Fixed PHP max_input_vars in CI.

## 0.1.0-beta — 2026-10-06

Initial read-only dashboard: group-aware learner roster, current completion percentage, activity detail, inactivity/grace indicators, estimated event-based session time, separate IP permission, CSV page export and English/Spanish strings.

No confirmed dropout records, deadline-based risk, browser activity tracking, global course-category dashboard or automated alerts in this release.
