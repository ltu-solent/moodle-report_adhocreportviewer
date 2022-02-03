<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * List of available reports
 *
 * @package   report_adhocreportviewer
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2022 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/report/customsql/locallib.php');


$reports = \report_adhocreportviewer\local\api::viewablereports($USER);
$canmanage = \report_adhocreportviewer\local\api::canmanage();

$PAGE->set_pagelayout('admin');
$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/report/adhocreportviewer/index.php'));
$PAGE->set_title(format_string("Reports"));
$PAGE->navbar->add(format_string("Reports"));

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string("Reports"));

$reportlist = [];
foreach ($reports as $report) {
    $viewurl = new moodle_url('/report/adhocreportviewer/view.php', ['cqid' => $report->id]);
    
    $html = html_writer::link($viewurl, s($report->displayname));
    if ($canmanage) {
        $editurl = new moodle_url('/report/adhocreportviewer/assign.php', ['cqid' => $report->id]);
        $html .= ' ' . html_writer::link($editurl, 'Edit access');
    }
    if ($report->lastexecutiontime > 5000) { // 5 Seconds.
        $html .= html_writer::div(
            get_string('timeexecutionwarning', 'report_adhocreportviewer', $report->lastexecutiontime),
            'alert alert-warning'
        );
    }
    $html .= format_text($report->description);
    $reportlist[] = $html;

}
echo html_writer::alist($reportlist);

echo $OUTPUT->footer();