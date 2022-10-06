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

require_login();

$reports = \report_adhocreportviewer\local\api::viewablereports($USER);
$categories = \report_adhocreportviewer\local\api::categories($reports);
$canmanageaccess = \report_adhocreportviewer\local\api::canmanageaccess();
$canedit = has_capability('report/customsql:definequeries', context_system::instance());
if (count($reports) == 0) {
    throw new moodle_exception('noaccess');
}
$permissions = new stdClass();
$permissions->canedit = $canedit;
$permissions->canmanageaccess = $canmanageaccess;

$PAGE->set_pagelayout('admin');
$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/report/adhocreportviewer/index.php'));
$PAGE->set_title(get_string('pluginname', 'report_adhocreportviewer'));
$PAGE->navbar->add(get_string('pluginname', 'report_adhocreportviewer'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'report_adhocreportviewer'));

$showcat = optional_param('showcat', 0, PARAM_INT);
$hidecat = optional_param('hidecat', 0, PARAM_INT);
if (!$showcat && count($categories) == 1) {
    $showcat = reset($categories)->id;
}

$reportlist = new \report_adhocreportviewer\output\report_list($categories, $permissions, $showcat, $hidecat);
echo $OUTPUT->render($reportlist);

// Initialise the expand/collapse JavaScript.
$PAGE->requires->js_call_amd('report_customsql/reportcategories', 'init');

echo $OUTPUT->footer();
