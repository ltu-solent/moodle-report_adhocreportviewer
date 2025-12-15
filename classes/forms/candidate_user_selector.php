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
 * Candidate list of users to assign to a report
 *
 * @package   report_adhocreportviewer
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2022 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_adhocreportviewer\forms;

use user_selector_base;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/user/selector/lib.php');

/**
 * User selector class for candidate users
 */
class candidate_user_selector extends user_selector_base {
    /**
     * customsql id
     *
     * @var int
     */
    private $cqid;
    /**
     * Access type. Only 'user' currently used.
     *
     * @var string
     */
    private $accesstype = 'user';

    /**
     * Selector form constructor
     *
     * @param string $name
     * @param array $options [cqid, accesstype]
     */
    public function __construct($name, $options) {
        $this->cqid = $options['cqid'];
        $this->accesstype = $options['accesstype'] ?? 'user';
        parent::__construct($name, $options);
    }

    /**
     * Search for user
     *
     * @param string $search
     * @return array Users matching search criteria
     */
    public function find_users($search) {
        global $DB;
        // By default wherecondition retrieves all users except the deleted, not confirmed and guest.
        [$wherecondition, $params] = $this->search_sql($search, 'u');
        $params['cqid'] = $this->cqid;
        $params['accesstype'] = $this->accesstype;

        $fields      = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(1)';

        $sql = " FROM {user} u
                LEFT JOIN {report_adhocreportviewer} v ON (v.accessid = u.id AND v.cqid = :cqid AND accesstype = :accesstype)
                WHERE v.id IS NULL AND $wherecondition";

        [$sort, $sortparams] = users_order_by_sql('u', $search, $this->accesscontext);
        $order = ' ORDER BY ' . $sort;

        if (!$this->is_validating()) {
            $potentialusercount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialusercount > $this->maxusersperpage) {
                return $this->too_many_results($search, $potentialusercount);
            }
        }

        $availableusers = $DB->get_records_sql($fields . $sql . $order, array_merge($params, $sortparams));

        if (empty($availableusers)) {
            return [];
        }

        if ($search) {
            $groupname = get_string('potentialpeoplematching', 'report_adhocreportviewer', $search);
        } else {
            $groupname = get_string('potentialpeople', 'report_adhocreportviewer');
        }

        return [$groupname => $availableusers];
    }

    /**
     * Gets options
     *
     * @return array
     */
    protected function get_options() {
        $options = parent::get_options();
        $options['accesstype'] = $this->accesstype ?? 'user';
        $options['cqid'] = $this->cqid;
        return $options;
    }
}
