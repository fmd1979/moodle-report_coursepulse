# Validation record — 0.3.0-beta

Date: 2026-10-06.

- Four new PHPUnit tests cover category/subcategory scope, sibling rejection, hidden-course exclusion, course prohibitions, pagination, report-only access without enrolment or course content capability, independent student access, and separate-group summary suppression.
- Previous 0.2 CI installation, PHP lint and Moodle validation passed; PHPUnit stopped before tests because runner locale en_AU.UTF-8 was missing. This release adds locale generation.
- Complete 0.3 CI and staging browser checks pending at packaging time. No stable or Marketplace certification implied.

## Previous release validation

# Validation record — 0.2.0-beta

Date: 2026-10-06.

- All 14 PHP files pass PHP 8.3 syntax checks.
- XLSX export checked against Moodle 5.0 native Excel wrapper and its bundled PhpSpreadsheet: two sheets, text-only formula-like names, numeric percentage and formatting, numeric duration over 24 hours, elapsed format, XLSX save/reopen and blank truncated measurements (8 checks passed).
- Added regression coverage in tests/excel_test.php and completion-band assertion in report_test.php for CI.
- Native chart object serialization checked for coloured doughnut, horizontal integer-count bars and integer-count daily bars.
- Browser visual verification in the actual theme and the new complete CI run remain pending.

## Previous release validation

# Validation record — 0.1.0-beta

Date: 2026-10-06.

## Executed successfully

- PHP syntax validation for all 12 PHP files.
- Actual plugin installation during Moodle PHPUnit environment setup.
- Moodle 5.0.11, core commit `744cc0c19013e7be831dca24fafd6501d7eb8348`.
- PHP 8.3.6, MySQL Community Server 8.4.11, PHPUnit 11.5.55.
- Plugin PHPUnit suite: **8 tests, 30 assertions, all passed**, 3.879 seconds, 79 MB.
- Tests cover session gaps/duplicates, inactivity thresholds/grace, CSV formula escaping, separate-group scoping including learner detail, student access denial, completion including failed states, suspended enrolment exclusion, log course/origin/impersonation filtering, IP permission and missing-completion semantics.

## Pending before a stable release/listing

- Real browser smoke test, including date-selector submission, CSV download and accessibility, on a staging installation.
- Moodle 5.1 / PHP 8.3+ and PostgreSQL compatibility runs. CI matrix is supplied; these combinations are designed targets, not executed evidence.
- Moodle coding-standard checks and Marketplace validation/reviewer assessment.
- Large-course load measurements on the intended hosting and real log volumes.
- Actual synthetic-data screenshots and provider/source/support listing URLs.

No production server was modified. No Marketplace submission, approval or certification has been performed. The source repository is https://github.com/fmd1979/moodle-report_coursepulse.
