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
 * API
 *
 * @package   report_adhocreportviewer
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2022 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_adhocreportviewer\local;

use context_system;
use stdClass;

defined('MOODLE_INTERNAL') || die();

class api {
    
    public static function canview($user, $id): bool {
        global $DB;
        if (is_siteadmin()) {
            return true;
        }
        $recordexists = $DB->record_exists('report_adhocreportviewer', ['cqid' => $id, 'accesstype' => 'user', 'accessid' => $user->id]);
        return $recordexists;
    }

    public static function canmanage(): bool {
        global $DB;
        if (is_siteadmin()) {
            return true;
        }
        return has_capability('report/adhocreportviewer:manage', context_system::instance());
    }

    public static function viewablereports($user): array {
        global $DB;
        if (is_siteadmin()) {
           return $DB->get_records('report_customsql_queries');
        }
        $viewables = $DB->get_records('report_adhocreportviewer', ['accesstype' => 'user' , 'accessid' => $user->id], '', 'cqid');
        $ids = array_keys($viewables);
        list($insql, $inparams) = $DB->get_in_or_equal($ids);
        $reports = $DB->get_records_sql("SELECT * FROM {report_customsql_queries} WHERE id $insql", $inparams);
        return $reports;
    }

    public static function add_access($cqid, $accessid, $accesstype = 'user') {
        global $DB;
        $record = new stdClass();
        $record->cqid = $cqid;
        $record->accesstype = $accesstype;
        $record->accessid = $accessid;
        $DB->insert_record('report_adhocreportviewer', $record);
    }

    public static function remove_access($cqid, $accessid, $accesstype = 'user') {
        global $DB;
        $DB->delete_records('report_adhocreportviewer', [
            'cqid' => $cqid,
            'accessid' => $accessid,
            'accesstype' => $accesstype
        ]);
    }
}


