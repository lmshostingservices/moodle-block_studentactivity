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
 * CSV export for block_studentactivity.
 *
 * @package    block_studentactivity
 * @copyright  2026 LMS Hosting Services
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$courseid = required_param('courseid', PARAM_INT);
$view     = optional_param('view', 'detail', PARAM_ALPHA);
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

// Collect rows.
$rows = [];
$header = [];

if ($view === 'summary') {
    $header = ['Unit', 'Completed activities', 'Total activities', 'Status'];
    $sections = $DB->get_records('course_sections', ['course' => $courseid], 'section ASC');
    foreach ($sections as $section) {
        if (!$section->name && !$section->summary) {
            continue;
        }
        $unitname  = $section->name ?: 'Section ' . $section->section;
        $completed = $DB->count_records_select('course_modules_completion',
            "userid = :uid AND completionstate > 0 AND coursemoduleid IN
             (SELECT id FROM {course_modules} WHERE course = :cid AND section = :sid)",
            ['uid' => $userid, 'cid' => $courseid, 'sid' => $section->id]);
        $total = $DB->count_records('course_modules', ['course' => $courseid, 'section' => $section->id, 'completion' => 1]);
        $status = $total > 0 && $completed >= $total ? 'Complete'
                : ($completed > 0 ? 'In progress' : 'Not started');
        $rows[] = [strip_tags(format_string($unitname)), $completed, $total, $status];
    }
} else {
    $header = ['Activity', 'Type', 'Start', 'Finish', 'Duration (min)', 'Status', 'Score (%)'];

    // Quiz attempts.
    $quizattempts = $DB->get_records_sql("
        SELECT qa.id, q.name, qa.state, qa.timestart, qa.timefinish, qa.sumgrades, q.grade
          FROM {quiz_attempts} qa
          JOIN {quiz} q ON q.id = qa.quiz
         WHERE q.course = :cid AND qa.userid = :uid ORDER BY qa.timestart DESC
    ", ['cid' => $courseid, 'uid' => $userid]);
    foreach ($quizattempts as $a) {
        $dur   = ($a->timefinish && $a->timefinish > $a->timestart)
               ? round(min($a->timefinish - $a->timestart, 1800) / 60, 1) : '';
        $score = $a->grade > 0 ? round(($a->sumgrades / $a->grade) * 100, 1) : '';
        $rows[] = [
            strip_tags(format_string($a->name)),
            'Quiz',
            $a->timestart ? date('Y-m-d H:i', $a->timestart) : '',
            $a->timefinish ? date('Y-m-d H:i', $a->timefinish) : '',
            $dur, $a->state, $score,
        ];
    }

    // Assignment submissions.
    $assignsubs = $DB->get_records_sql("
        SELECT s.id, a.name, s.status, s.timecreated, s.timemodified
          FROM {assign_submission} s
          JOIN {assign} a ON a.id = s.assignment
         WHERE a.course = :cid AND s.userid = :uid AND s.status <> 'new' ORDER BY s.timecreated DESC
    ", ['cid' => $courseid, 'uid' => $userid]);
    foreach ($assignsubs as $s) {
        $rows[] = [
            strip_tags(format_string($s->name)),
            'Assignment',
            date('Y-m-d H:i', $s->timecreated),
            date('Y-m-d H:i', $s->timemodified),
            '', $s->status, '',
        ];
    }
}

// Output CSV.
$filename = clean_filename('studentactivity_' . $course->shortname . '_' . fullname($targetuser) . '_' . date('Ymd') . '.csv');
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
fputcsv($out, ['Student: ' . fullname($targetuser), 'Course: ' . format_string($course->fullname), 'Exported: ' . date('Y-m-d H:i')]);
fputcsv($out, $header);
foreach ($rows as $row) {
    fputcsv($out, $row);
}
fclose($out);
exit;
