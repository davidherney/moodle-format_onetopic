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

namespace format_onetopic\output\courseformat\state;

use core_availability\info_section;
use core_courseformat\base as course_format;
use section_info;
use renderable;
use stdClass;
use context_course;

/**
 * Contains the ajax update section structure.
 *
 * @package   format_onetopic
 * @copyright 2023 Friederike Schwager, RWTH Aachen University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class section extends \core_courseformat\output\local\state\section {

    /** @var course_format the course format class */
    protected $format;

    /** @var section_info the course section class */
    protected $section;

    /**
     * Constructor.
     *
     * @param course_format $format the course format
     * @param section_info $section the section info
     */
    public function __construct(course_format $format, section_info $section) {
        $this->format = $format;
        $this->section = $section;
    }

    /**
     * Export this data so it can be used as state object in the course editor.
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return array data context for a mustache template
     */
    public function export_for_template(\renderer_base $output): stdClass {
        $data = parent::export_for_template($output);
  
        //$formatoptions = course_get_format($course)->get_format_options($thissection);
        $formatoptions = $this->format->get_format_options($this->section);
        $issubtopic = false;
        if (isset($formatoptions['level']) && $formatoptions['level'] > 0) {
                $issubtopic = true;
        }
        $data->issubtopic = $issubtopic;

        return $data;
    }

}
