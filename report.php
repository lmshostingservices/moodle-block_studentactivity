<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

/**
 * Student Activity & Participation Evidence — report page.
 *
 * @package    block_studentactivity
 * @copyright  2026 LMS Hosting Services
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$courseid = required_param('courseid', PARAM_INT);
$view     = optional_param('view', 'summary', PARAM_ALPHA);
$userid   = optional_param('userid', 0, PARAM_INT);

$course  = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$context = context_course::instance($courseid);

require_login($course);
require_capability('block/studentactivity:view', $context);

$viewall = has_capability('block/studentactivity:viewall', $context);
if (!$viewall) {
    $userid = $USER->id;
}
if (!$userid) {
    $userid = $USER->id;
}

$targetuser = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

$PAGE->set_url('/blocks/studentactivity/report.php', ['courseid' => $courseid, 'view' => $view, 'userid' => $userid]);
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'block_studentactivity'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_pagelayout('incourse');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'block_studentactivity'));

// User selector (teachers only).
if ($viewall) {
    $enrolled = get_enrolled_users($context, 'block/studentactivity:view');
    $options  = [];
    foreach ($enrolled as $u) {
        $options[$u->id] = fullname($u);
    }
    $select = new single_select(
        new moodle_url('/blocks/studentactivity/report.php', ['courseid' => $courseid, 'view' => $view]),
        'userid', $options, $userid
    );
    $select->label = get_string('searchstudents', 'block_studentactivity');
    echo $OUTPUT->render($select);
}

// View tabs.
$tabs = [
    new tabobject('summary',
        new moodle_url('/blocks/studentactivity/report.php', ['courseid' => $courseid, 'view' => 'summary', 'userid' => $userid]),
        get_string('summaryview', 'block_studentactivity')),
    new tabobject('detail',
        new moodle_url('/blocks/studentactivity/report.php', ['courseid' => $courseid, 'view' => 'detail', 'userid' => $userid]),
        get_string('detailview', 'block_studentactivity')),
];
echo $OUTPUT->tabtree($tabs, $view);

// Export button.
$exporturl = new moodle_url('/blocks/studentactivity/export.php', [
    'courseid' => $courseid, 'view' => $view, 'userid' => $userid, 'format' => 'csv',
]);
echo html_writer::link($exporturl, get_string('exportcsv', 'block_studentactivity'), ['class' => 'btn btn-sm btn-outline-secondary mb-3']);

if ($view === 'summary') {
    // Summary: roll up per-section (unit). Use course sections as units.
    $sections = $DB->get_records('course_sections', ['course' => $courseid], 'section ASC');
    $cms      = get_course_and_contact_cache($course);

    $table = new html_table();
    $table->head  = [
        get_string('col_unit', 'block_studentactivity'),
        get_string('col_attempts', 'block_studentactivity'),
        get_string('col_timeonline', 'block_studentactivity'),
        get_string('col_status', 'block_studentactivity'),
        get_string('col_score', 'block_studentactivity'),
    ];
    $table->align = ['left', 'center', 'center', 'center', 'center'];

    foreach ($sections as $section) {
        if (!$section->name && !$section->summary) {
            continue;
        }
        $unitname = $section->name ?: get_string('section') . ' ' . $section->section;
        $completed = $DB->count_records_select('course_modules_completion',
            "userid = :uid AND completionstate > 0 AND coursemoduleid IN
             (SELECT id FROM {course_modules} WHERE course = :cid AND section = :sid)",
            ['uid' => $userid, 'cid' => $courseid, 'sid' => $section->id]);
        $total = $DB->count_records('course_modules', ['course' => $courseid, 'section' => $section->id, 'completion' => 1]);
        $status = $total > 0 && $completed >= $total ? get_string('status_complete', 'block_studentactivity')
                : ($completed > 0 ? get_string('status_inprogress', 'block_studentactivity')
                : get_string('status_notstarted', 'block_studentactivity'));
        $table->data[] = [format_string($unitname), "{$completed}/{$total}", '—', $status, '—'];
    }
    echo html_writer::table($table);

} else {
    // Detail: per-attempt rows from durable tables.
    $rows = [];

    // Quiz attempts.
    $quizattempts = $DB->get_records_sql("
        SELECT qa.id, q.name, qa.state, qa.timestart, qa.timefinish, qa.sumgrades, q.grade
          FROM {quiz_attempts} qa
          JOIN {quiz} q ON q.id = qa.quiz
         WHERE q.course = :cid AND qa.userid = :uid
         ORDER BY qa.timestart DESC
    ", ['cid' => $courseid, 'uid' => $userid]);
    foreach ($quizattempts as $a) {
        $dur = ($a->timefinish && $a->timefinish > $a->timestart)
             ? format_time(min($a->timefinish - $a->timestart, 1800)) : '—';
        $score = $a->grade > 0 ? round(($a->sumgrades / $a->grade) * 100, 1) . '%' : '—';
        $rows[] = [
            format_string($a->name),
            get_string('type_quiz', 'block_studentactivity'),
            userdate($a->timestart),
            $a->timefinish ? userdate($a->timefinish) : '—',
            $dur,
            $a->state,
            $score,
        ];
    }

    // Assignment submissions.
    $assignsubs = $DB->get_records_sql("
        SELECT s.id, a.name, s.status, s.timecreated, s.timemodified
          FROM {assign_submission} s
          JOIN {assign} a ON a.id = s.assignment
         WHERE a.course = :cid AND s.userid = :uid AND s.status <> 'new'
         ORDER BY s.timecreated DESC
    ", ['cid' => $courseid, 'uid' => $userid]);
    foreach ($assignsubs as $s) {
        $rows[] = [
            format_string($s->name),
            get_string('type_assignment', 'block_studentactivity'),
            userdate($s->timecreated),
            userdate($s->timemodified),
            '—', $s->status, '—',
        ];
    }

    $table = new html_table();
    $table->head  = [
        get_string('col_activity', 'block_studentactivity'),
        get_string('col_type', 'block_studentactivity'),
        get_string('col_start', 'block_studentactivity'),
        get_string('col_finish', 'block_studentactivity'),
        get_string('col_duration', 'block_studentactivity'),
        get_string('col_status', 'block_studentactivity'),
        get_string('col_score', 'block_studentactivity'),
    ];
    $table->align = ['left', 'left', 'left', 'left', 'center', 'left', 'center'];
    $table->data  = $rows ?: [array_fill(0, 7, get_string('nodata', 'block_studentactivity'))];
    echo html_writer::table($table);
}

echo $OUTPUT->footer();
