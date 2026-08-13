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
 * Block class for block_studentactivity.
 *
 * @package    block_studentactivity
 * @copyright  2026 LMS Hosting Services
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Student Activity & Participation Evidence block.
 *
 * Provides per-unit and per-attempt participation evidence for any course,
 * exportable to CSV. Built for VET/RTO audits. Evidence is read from durable
 * Moodle activity tables so the report is complete regardless of log retention.
 */
class block_studentactivity extends block_base {
    /**
     * Initialise the block.
     */
    public function init(): void {
        $this->title = get_string('pluginname', 'block_studentactivity');
    }

    /**
     * Which page formats are this block allowed on.
     *
     * @return array
     */
    public function applicable_formats(): array {
        return [
            'course-view' => true,
            'my'          => false,
            'site'        => false,
        ];
    }

    /**
     * Allow multiple instances on the same page.
     *
     * @return bool
     */
    public function instance_allow_multiple(): bool {
        return false;
    }

    /**
     * The block has its own configuration form.
     *
     * @return bool
     */
    public function has_config(): bool {
        return false;
    }

    /**
     * Build and return the block content.
     *
     * @return stdClass|null Block content object.
     */
    public function get_content(): ?stdClass {
        global $USER, $COURSE, $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';

        $context = context_course::instance($COURSE->id);
        if (!has_capability('block/studentactivity:view', $context)) {
            $this->content->text = '';
            return $this->content;
        }

        // Teacher view: link to the full report page with student selector.
        // Student view: show own data summary.
        $reporturl = new moodle_url('/blocks/studentactivity/report.php', ['courseid' => $COURSE->id]);
        $exporturl = new moodle_url('/blocks/studentactivity/export.php', ['courseid' => $COURSE->id, 'format' => 'csv']);

        $viewall = has_capability('block/studentactivity:viewall', $context);

        $html  = html_writer::start_div('block-studentactivity');
        $html .= html_writer::tag('p', get_string('pluginname', 'block_studentactivity'),
            ['class' => 'font-weight-bold mb-2']);

        $html .= html_writer::link($reporturl, get_string($viewall ? 'summaryview' : 'summaryview', 'block_studentactivity'),
            ['class' => 'btn btn-sm btn-primary mr-1']);

        if ($viewall) {
            $html .= html_writer::link(
                new moodle_url('/blocks/studentactivity/report.php', ['courseid' => $COURSE->id, 'view' => 'detail']),
                get_string('detailview', 'block_studentactivity'),
                ['class' => 'btn btn-sm btn-secondary mr-1']
            );
        }

        $html .= html_writer::link($exporturl, get_string('exportcsv', 'block_studentactivity'),
            ['class' => 'btn btn-sm btn-outline-secondary']);

        $html .= html_writer::end_div();

        $this->content->text = $html;
        return $this->content;
    }
}
