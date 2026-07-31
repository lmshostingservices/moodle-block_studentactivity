# Student Activity & Participation Evidence (`block_studentactivity`)

A Moodle block that provides per-unit and per-attempt participation evidence for any course, exportable to CSV. Built for VET/RTO audits and ASQA compliance.

Evidence is read from durable Moodle activity tables — not from the standard log — so the report is complete across a learner's entire enrolment even on sites with a short log-retention window (e.g. 30 days).

## Evidence sources

| Table | What it captures |
|---|---|
| `mdl_quiz_attempts` | Every quiz attempt with start, finish, score |
| `mdl_assign_submission` | Every assignment submission with timestamps |
| `mdl_scorm_scoes_track` | SCORM session interactions |
| `mdl_course_modules_completion` | Activity completion marks |
| `mdl_forum_posts` | Forum participation |

## Key features

- **Summary view** — per-unit rollup: attempts, time on task, completion status, score
- **Detail view** — every quiz attempt, assignment submission, SCORM session and completion with exact Start/Finish timestamps, engaged duration, status, score
- **Engaged time-on-task** — reconstructed from interaction timestamps with idle gaps capped at 30 min (avoids inflated multi-day durations)
- **Search, sort & filter** — by student, unit, activity type and date range
- **CSV export** — one-click, ready for ASQA audits or supervisor review
- **Full-width / Fullscreen** — breakout mode for all-columns display
- **Role isolation** — teachers see all students; learners see only their own data
- No AI credits required; no external API calls; no personal data stored by the block itself

## Installation

1. Download the ZIP from lms-labs.com → Plugins → Student Activity & Participation Evidence
2. Moodle → Site administration → Plugins → Install plugins → upload ZIP
3. Add the block to any course page from the block drawer

## Permissions

| Role | Access |
|---|---|
| Student | Own data only (summary + detail) |
| Teacher / Editing teacher | All students in the course |
| Manager | All students site-wide |

## Compatibility

Moodle 4.0 – 5.x · PHP 7.4+

## Licence

GNU GPL v3 or later — see [COPYING](https://www.gnu.org/licenses/gpl-3.0.html)

## Support

support@lmshostingservices.com · https://lms-labs.com/docs/student-activity
