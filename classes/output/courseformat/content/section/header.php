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
 * Contains the default section header format output class.
 *
 * @package   format_onetopic
 * @copyright 2026 James Calder and Otago Polytechnic
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_onetopic\output\courseformat\content\section;

use core\output\renderer_base;
use core_courseformat\output\local\content\section\header as header_base;
use stdClass;

/**
 * Base class to render a section header.
 *
 * @package   format_onetopic
 * @copyright 2026 James Calder and Otago Polytechnic
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class header extends header_base {
    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */
    public function export_for_template(renderer_base $output): stdClass {
        $data = parent::export_for_template($output);

        if ($this->section->component) {
            $data->headerdisplaymultipage = ($this->format->get_section_display_mode($this->section) == 'summary');
        }

        return $data;
    }
}
