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
 * This file contains class for render the footer in the course format onetopic.
 *
 * @package   format_onetopic
 * @copyright 2023 David Herney - https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_onetopic;

use core_courseformat\output\local\content as content_base;
use course_modinfo;

/**
 * Class used to render the footer content in each course page.
 *
 *
 * @package   format_onetopic
 * @copyright 2016 David Herney - https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class footer implements \renderable, \templatable {

    /**
     * @var \format_onetopic
     */
    private $format;

    /**
     * Constructor.
     *
     * @param \format_onetopic $format Course format instance.
     */
    public function __construct(\format_onetopic $format) {
        global $COURSE;

        $this->format = $format;
    }

    /**
     * Export this data so it can be used as the context for a mustache template (core/inplace_editable).
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */
    public function export_for_template(\renderer_base $output) {

        $format = $this->format;
        $currentsection = $this->format->get_sectionnum();

        $data = (object)[
            'uniqid' => $format->uniqid,
            'sectionreturn' => $currentsection ?? 0,
            'hastopictabs' => $format->hastopictabs,
        ];

        return $data;
    }

}
