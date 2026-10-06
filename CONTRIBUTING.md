# Contributing

Use Moodle coding conventions, Moodle DML placeholders and native APIs. All code is GPL-3.0-or-later. Do not add outbound analytics or persistent personal data without updating the privacy implementation and documentation.

Run PHP syntax checks and plugin PHPUnit tests in a Moodle test installation. The GitHub Actions workflow uses moodlehq/moodle-plugin-ci and tests Moodle 5.0/5.1 with MySQL/PostgreSQL. Verify database compatibility before widening the supported listing.

Changes to reports must preserve login, capability, enrolment, separate-group, detail and export boundaries. Never treat inactivity as confirmed dropout or event-based duration as verified study time. Add regression tests for permission and calculation changes.

Report issues through the actual public GitHub repository once it is created. Include Moodle/PHP/database versions and a synthetic reproduction. Do not upload student records, real IPs, tokens or production database dumps.
