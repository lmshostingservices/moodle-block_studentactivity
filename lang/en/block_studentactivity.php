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
 * Language strings for block_studentactivity.
 *
 * @package    block_studentactivity
 * @copyright  2026 LMS Hosting Services
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname']             = 'Student Activity & Participation Evidence';
$string['studentactivity:addinstance'] = 'Add a new Student Activity block';
$string['studentactivity:myaddinstance'] = 'Add a new Student Activity block to My home';
$string['studentactivity:view']   = 'View Student Activity block';
$string['studentactivity:viewall'] = 'View activity for all students (teacher view)';

// Views.
$string['summaryview']            = 'Summary';
$string['detailview']             = 'Detail';
$string['exportcsv']              = 'Export CSV';
$string['fullscreen']             = 'Fullscreen';
$string['fullwidth']              = 'Full width';
$string['searchstudents']         = 'Search students…';
$string['filterstatus']           = 'Filter by status';
$string['filtertype']             = 'Filter by activity type';
$string['daterange']              = 'Date range';

// Summary view columns.
$string['col_unit']               = 'Unit';
$string['col_attempts']           = 'Attempts';
$string['col_timeonline']         = 'Time on task';
$string['col_status']             = 'Completion';
$string['col_score']              = 'Score';
$string['col_lastaccess']         = 'Last access';

// Detail view columns.
$string['col_activity']           = 'Activity';
$string['col_type']               = 'Type';
$string['col_start']              = 'Start';
$string['col_finish']             = 'Finish';
$string['col_duration']           = 'Duration';

// Activity types.
$string['type_quiz']              = 'Quiz';
$string['type_assignment']        = 'Assignment';
$string['type_scorm']             = 'SCORM';
$string['type_forum']             = 'Forum';
$string['type_completion']        = 'Activity completion';

// Status.
$string['status_complete']        = 'Complete';
$string['status_incomplete']      = 'Incomplete';
$string['status_inprogress']      = 'In progress';
$string['status_notstarted']      = 'Not started';
$string['nodata']                 = 'No participation data found for this period.';

// Privacy.
$string['privacy:metadata']       = 'The Student Activity block reads participation data from existing ' .
    'Moodle tables (quiz_attempts, assign_submission, scorm_scoes_track, forum_posts, ' .
    'course_modules_completion). It does not store any additional personal data.';
