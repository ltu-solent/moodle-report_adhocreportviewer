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
 * Assign users to reports
 *
 * @package   report_adhocreportviewer
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2022 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\context;
use core\url;

require_once(dirname(__FILE__) . '/../../config.php');

$cqid = required_param('cqid', PARAM_INT);
$accesstype = optional_param('accesstype', 'user', PARAM_ALPHA);

require_login();

$report = $DB->get_record('report_customsql_queries', ['id' => $cqid], '*', MUST_EXIST);
$context = context\system::instance();


require_capability('report/adhocreportviewer:manage', $context);

$PAGE->set_context($context);
$PAGE->set_url('/report/adhocreportviewer/assign.php', ['cqid' => $cqid]);
$PAGE->set_pagelayout('admin');

$returnurl = new url('/report/adhocreportviewer/index.php');

if (optional_param('cancel', false, PARAM_BOOL)) {
    redirect($returnurl);
}
$PAGE->navbar->add(get_string('pluginname', 'report_adhocreportviewer'), $returnurl);
$PAGE->navbar->add(get_string('assign', 'report_adhocreportviewer'));


$PAGE->set_title(get_string('assignusers', 'report_adhocreportviewer'));
$PAGE->set_heading($report->displayname);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('assignto', 'report_adhocreportviewer', format_string($report->displayname)));

// Get the user_selectors we will need.
$potentialuserselector = new \report_adhocreportviewer\forms\candidate_user_selector('addselect',
    ['cqid' => $report->id, 'accesstype' => 'user', 'accesscontext' => $context]);
$existinguserselector = new \report_adhocreportviewer\forms\existing_user_selector('removeselect',
    ['cqid' => $report->id, 'accesstype' => 'user', 'accesscontext' => $context]);



// Process incoming user assignments to the report.
if (optional_param('add', false, PARAM_BOOL) && confirm_sesskey()) {
    $userstoassign = $potentialuserselector->get_selected_users();
    if (!empty($userstoassign)) {

        foreach ($userstoassign as $adduser) {
            \report_adhocreportviewer\local\api::add_access($report->id, $adduser->id, $accesstype);
        }

        $potentialuserselector->invalidate_selected_users();
        $existinguserselector->invalidate_selected_users();
    }
}


// Process removing user assignments to the report.
if (optional_param('remove', false, PARAM_BOOL) && confirm_sesskey()) {
    $userstoremove = $existinguserselector->get_selected_users();
    if (!empty($userstoremove)) {
        foreach ($userstoremove as $removeuser) {
            \report_adhocreportviewer\local\api::remove_access($report->id, $removeuser->id, $accesstype);
        }
        $potentialuserselector->invalidate_selected_users();
        $existinguserselector->invalidate_selected_users();
    }
}

$userselector = new \report_adhocreportviewer\output\user_selector(
    $existinguserselector,
    $potentialuserselector);
echo $OUTPUT->render($userselector);

echo $OUTPUT->footer();
