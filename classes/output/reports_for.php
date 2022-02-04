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
 * Output class for report listing
 *
 * @package   report_adhocreportviewer
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2022 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_adhocreportviewer\output;

use moodle_url;
use renderable;
use renderer_base;
use stdClass;
use templatable;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/report/customsql/locallib.php');

class reports_for implements renderable, templatable {

    private $reports;
    private $type;
    public function __construct($reports, $type, $permissions) {
        $this->reports = \report_customsql_sort_reports_by_displayname($reports);
        $this->type = $type;
        $this->permissions = $permissions;
    }

    public function export_for_template(renderer_base $output) {
        global $OUTPUT;
        $data = new stdClass();
        $data->type = new stdClass();
        $data->type->header = get_string($this->type . 'header', 'report_customsql');
        $data->type->helpicon = $OUTPUT->help_icon($this->type . 'header', 'report_customsql');
        $reports = [];
        foreach ($this->reports as $report) {
            $item = new stdClass();
            $item->viewurl = new moodle_url('/report/adhocreportviewer/view.php', ['cqid' => $report->id]);
            $item->viewdisplayname = s($report->displayname);
            if ($this->permissions->canedit) {
                $item->canedit = new stdClass();
                $item->canedit->url = new moodle_url('/report/customsql/edit.php', ['id' => $report->id]);
            }
            if ($this->permissions->canmanageaccess) {
                $item->canmanageaccess = new stdClass();
                $item->canmanageaccess->url = new moodle_url('/report/adhocreportviewer/assign.php', ['cqid' => $report->id]);
            }
            if ($report->lastrun) {
                $item->lastrun = true;
                $lastexecutiontime = $report->lastexecutiontime / 1000;
                if ($lastexecutiontime > 5) { // Five seconds.
                    $item->warning = 'warning';
                }
                $item->lastexecutiontime = $lastexecutiontime;
            }
            $reports[] = $item;
        }
        $data->reports = $reports;
        return $data;
    }
}