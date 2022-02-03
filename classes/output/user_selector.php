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
 * User selector form
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

class user_selector implements renderable, templatable {
    
    private $existingusersselector;
    private $candidateuserselector;
    public function __construct($existingusersselector, $candidateuserselector) {
        $this->existingusersselector = $existingusersselector;
        $this->candidateuserselector = $candidateuserselector;

    }

    public function export_for_template(renderer_base $output) {
        global $OUTPUT, $PAGE;
        $data = new stdClass();
        $data->formid = 'assignform';
        $data->formurl = $PAGE->url;
        $data->sesskey = sesskey();
        $data->returnurl = new moodle_url('/report/adhocreportviewer/');
        $data->removeselectlabel = get_string('currentpeople', 'report_adhocreportviewer');
        $data->existinguserselector = $this->existingusersselector->display(true);
        $data->addbuttonlabel = $OUTPUT->larrow() . '&nbsp;' . s(get_string('addpeople', 'report_adhocreportviewer'));
        $data->addbuttontitle = s(get_string('addpeople', 'report_adhocreportviewer'));
        $data->removebuttonlabel = s(get_string('removepeople', 'report_adhocreportviewer')) . '&nbsp;' . $OUTPUT->rarrow();
        $data->removebuttontitle = s(get_string('removepeople', 'report_adhocreportviewer'));
        $data->addselectlabel = get_string('potentialpeople', 'report_adhocreportviewer');
        $data->potentialuserselector = $this->candidateuserselector->display(true);
        $data->backbuttonlabel = s(get_string('backtoreports', 'report_adhocreportviewer'));

        return $data;
    }
}