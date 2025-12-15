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
 * ReportList output renderer
 *
 * @package   report_adhocreportviewer
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2022 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_adhocreportviewer\output;

use core\url;
use core\output\renderable;
use core\output\renderer_base;
use stdClass;
use core\output\templatable;

/**
 * Output class for list of reports
 */
class report_list implements renderable, templatable {
    /**
     * Report categories
     *
     * @var array
     */
    private $categories;
    /**
     * List of permissions linked to this page
     *
     * @var stdClass
     */
    private $permissions;
    /**
     * Show categoryid
     *
     * @var int
     */
    private $showcat;
    /**
     * Hide specified categoryid
     *
     * @var int
     */
    private $hidecat;

    /**
     * Constructor for output class
     *
     * @param array $categories
     * @param stdClass $permissions
     * @param integer $showcat
     * @param integer $hidecat
     */
    public function __construct($categories, $permissions, $showcat = 1, $hidecat = 0) {
        $this->categories = \report_adhocreportviewer\local\api::sortcats($categories);
        $this->permissions = $permissions;
        $this->showcat = $showcat;
        $this->hidecat = $hidecat;
    }

    /**
     * {@inheritDoc}
     *
     * @param renderer_base $output
     * @return stdClass Data context for template
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        if (count($this->categories) > 5) {
            $data->showexpandable = true;
        }
        $data->categories = [];
        foreach ($this->categories as $category) {
            $cat = new stdClass();
            $cat->show = ($category->id == $this->showcat && $category->id != $this->hidecat) ? 'shown' : 'hidden';

            if ($category->id == $this->showcat) {
                $params = ['hidecat' => $category->id];
            } else {
                $params = ['showcat' => $category->id];
            }
            $cat->catname = $category->name;
            $cat->linkhref = new url('/report/adhocreportviewer/index.php', $params);
            // Category content.
            $cc = new stdClass();
            $cc->manual = count($category->types['manual']);
            $cc->daily = count($category->types['daily']);
            $cc->weekly = count($category->types['weekly']);
            $cc->monthly = count($category->types['monthly']);
            $reportcounts = get_string('categorycontent', 'report_customsql', $cc);
            $cat->reportcounts = $reportcounts;
            if (
                empty($category->types['manual']) &&
                empty($category->types['daily']) &&
                empty($category->types['weekly']) &&
                empty($category->types['monthly'])
            ) {
                    $cat->noreports = true;
            } else {
                if ($cc->manual > 0) {
                    $manual = new \report_adhocreportviewer\output\reports_for(
                        $category->types['manual'],
                        'manual',
                        $this->permissions
                    );
                    $cat->manual = $manual->export_for_template($output);
                }
                if ($cc->daily > 0) {
                    $daily = new \report_adhocreportviewer\output\reports_for(
                        $category->types['daily'],
                        'daily',
                        $this->permissions
                    );
                    $cat->daily = $daily->export_for_template($output);
                }
                if ($cc->weekly > 0) {
                    $weekly = new \report_adhocreportviewer\output\reports_for(
                        $category->types['weekly'],
                        'weekly',
                        $this->permissions
                    );
                    $cat->weekly = $weekly->export_for_template($output);
                }
                if ($cc->monthly > 0) {
                    $monthly = new \report_adhocreportviewer\output\reports_for(
                        $category->types['monthly'],
                        'monthly',
                        $this->permissions
                    );
                    $cat->monthly = $monthly->export_for_template($output);
                }
            }
            $data->categories[] = $cat;
        }
        return $data;
    }
}
