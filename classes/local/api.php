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

use core\context;
use stdClass;

/**
 * General helper class for plugin.
 */
class api {
    /**
     * Add access entry to DB
     *
     * @param int $cqid Customsql id
     * @param int $accessid ID of the entity that has access (currently userid, but could be cohortid include the future)
     * @param string $accesstype Currently only 'user'
     * @return void
     */
    public static function add_access($cqid, $accessid, $accesstype = 'user') {
        global $DB;
        $record = new stdClass();
        $record->cqid = $cqid;
        $record->accesstype = $accesstype;
        $record->accessid = $accessid;
        $DB->insert_record('report_adhocreportviewer', $record);
    }

    /**
     * Can current user manage access to reports?
     *
     * @return boolean
     */
    public static function canmanageaccess(): bool {
        global $DB;
        if (is_siteadmin()) {
            return true;
        }
        return has_capability('report/adhocreportviewer:manage', context\system::instance());
    }

    /**
     * Can current specified user view given report
     *
     * @param stdClass $user User object
     * @param int $id Customsql ID
     * @return boolean
     */
    public static function canview($user, $id): bool {
        global $DB;
        if (is_siteadmin()) {
            return true;
        }
        $recordexists = $DB->record_exists(
            'report_adhocreportviewer',
            ['cqid' => $id, 'accesstype' => 'user', 'accessid' => $user->id]
        );
        return $recordexists;
    }

    /**
     * Returns list of categories for given reports and puts reports in those categories grouped by run method.
     *
     * @param array $reports
     * @return array
     */
    public static function categories($reports): array {
        global $DB;
        if (count($reports) == 0) {
            return [];
        }
        $cats = [];
        foreach ($reports as $report) {
            $cat = $report->categoryid;
            if (!in_array($cat, $cats)) {
                $cats[] = $cat;
            }
        }
        [$insql, $inparams] = $DB->get_in_or_equal($cats);
        $categories = $DB->get_records_select('report_customsql_categories', "id {$insql}", $inparams);
        foreach ($categories as $category) {
            $category->types = [];
            $category->types['manual'] = [];
            $category->types['daily'] = [];
            $category->types['weekly'] = [];
            $category->types['monthly'] = [];
        }
        foreach ($reports as $report) {
                $categories[$report->categoryid]->types[$report->runable][] = $report;
        }
        return $categories;
    }

    /**
     * Remove access for given report to the specified entity
     *
     * @param int $cqid CustomSQL ID
     * @param int $accessid Entity ID (user, cohort...)
     * @param string $accesstype ('user' only)
     * @return void
     */
    public static function remove_access($cqid, $accessid, $accesstype = 'user') {
        global $DB;
        $DB->delete_records('report_adhocreportviewer', [
            'cqid' => $cqid,
            'accessid' => $accessid,
            'accesstype' => $accesstype,
        ]);
    }

    /**
     * Get a list of reports the given user can view
     *
     * @param stdClass $user
     * @return array
     */
    public static function viewablereports($user): array {
        global $DB;
        if (is_siteadmin()) {
            return $DB->get_records('report_customsql_queries');
        }
        $viewables = $DB->get_records('report_adhocreportviewer', ['accesstype' => 'user', 'accessid' => $user->id], '', 'cqid');
        if (count($viewables) == 0) {
            return [];
        }
        $ids = array_keys($viewables);
        [$insql, $inparams] = $DB->get_in_or_equal($ids);
        $reports = $DB->get_records_sql("SELECT * FROM {report_customsql_queries} WHERE id $insql", $inparams);
        return $reports;
    }

    /**
     * Sort report categories nicely
     *
     * @param array $records
     * @return array
     */
    public static function sortcats($records): array {
        $sortedrecords = [];

        foreach ($records as $record) {
            $sortedrecords[$record->name] = $record;
        }

        ksort($sortedrecords, SORT_NATURAL);

        return $sortedrecords;
    }
}
