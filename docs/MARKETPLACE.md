# Marketplace publication draft

## Short description (English)

CoursePulse gives teachers a course dashboard with activity completion, configurable inactivity indicators, estimated engagement time and permission-controlled connection IP history.

## Full description (English)

CoursePulse is a read-only course report for Moodle. Teachers can inspect current activity completion, identify learners who may need follow-up and review estimated participation time from retained Moodle events. Managers can inspect recent connection IPs using a separate capability. Group-aware filters, learner detail and page-level CSV export support day-to-day academic monitoring.

Time is estimated from event intervals and does not certify active study. Inactivity indicators are not confirmed dropout classifications. Completion may include failed activities; learner detail distinguishes completion, pass and fail. No external analytics service or API key is required.

## Provider preparation

- Confirm component-name availability and current Marketplace submission requirements with Moodle.
- Publish the actual GitHub repository and enable Issues. Add real support, source and documentation URLs to the listing.
- Verify Moodle/PHP/database compatibility using CI and a real staging installation. Record results in VALIDATION.md. Do not advertise untested combinations.
- Review code style, Privacy API declaration, capability defaults, group boundaries and CSV safety.
- Take actual dashboard, learner-detail, filter, IP-permission and settings screenshots from a synthetic-data course. Do not present mockups as screenshots.
- Include the ZIP, GPL license, beta status, release notes, methodology and known limitations.
- Complete the provider/listing workflow and respond to reviewer feedback. Marketplace listing, approval and any provider agreement remain pending.

Reference starting point: https://moodledev.io/general/community/plugincontribution (this page explicitly marks the old Plugins Directory workflow as legacy and links to current provider documentation). Follow that current provider documentation, not the old submission steps.
