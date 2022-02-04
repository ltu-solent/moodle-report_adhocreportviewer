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
 * Adhoc report viewer Lib file
 *
 * @package   report_adhocreportviewer
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2022 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function report_adhocreportviewer_myprofile_navigation(\core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    global $USER;
    if ($user->id != $USER->id) {
        return true; // Only show the link for the actual person, not visitors.
    }
    // Only show the report link if they have access to any reports.
    $reports = \report_adhocreportviewer\local\api::viewablereports($USER);
    if (count($reports) == 0) {
        return true;
    }
    $url = new moodle_url('/report/adhocreportviewer/index.php');
    $node = new core_user\output\myprofile\node('reports',
        'adhocreportviewer', get_string('pluginname', 'report_adhocreportviewer'),
        null, $url);
        $tree->add_node($node);
    return true;
}