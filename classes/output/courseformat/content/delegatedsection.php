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
 * Contains the delegated section course format output class.
 *
 * @package   format_onetopic
 * @copyright 2025 David Herney - cirano. https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_onetopic\output\courseformat\content;

use core\output\renderer_base;
use core_courseformat\output\local\content\delegatedsection as delegatedsection_base;
use stdClass;

/**
 * Class to render a delegated section.
 *
 * @package   format_onetopic
 * @copyright 2025 David Herney - cirano. https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delegatedsection extends delegatedsection_base {
    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */
    public function export_for_template(renderer_base $output): stdClass {
        $data = parent::export_for_template($output);
        $data->isdelegated = false; // Otherwise the goto section arrow isn't displayed in display mode summary.
        $displaymode = $this->format->get_section_display_mode($this->section);
        $data->iscollapsible = ($displaymode == 'collapsible');
        $data->islist = ($displaymode == 'list');
        return $data;
    }

    /**
     * Add the section cm list to the data structure.
     *
     * @param stdClass $data the current cm data reference
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return bool if the cm has name data
     */
    protected function add_cm_data(stdClass &$data, renderer_base $output): bool {
        $result = false;

        $section = $this->section;
        $format = $this->format;

        $showsummary = (
            $section->id != $format->get_sectionid() &&
            ($format->get_section_display_mode($section) == 'summary') &&
            !$format->show_editor()
        );

        $showcmlist = $section->uservisible;

        // Add activities summary if necessary.
        if ($showsummary) {
            $cmsummary = new $this->cmsummaryclass($format, $section);
            $data->cmsummary = $cmsummary->export_for_template($output);
            $data->onlysummary = true;
            $result = true;

            if (!$format->is_section_current($section)) {
                // In multipage, only the current section (and the section zero) has elements.
                $showcmlist = false;
            }
        }
        // Add the cm list.
        if ($showcmlist) {
            $cmlist = new $this->cmlistclass($format, $section);
            $data->cmlist = $cmlist->export_for_template($output);
            $result = true;
        }
        return $result;
    }

    /**
     * Add the section format attributes to the data structure.
     *
     * @param stdClass $data the current cm data reference
     * @param bool[] $haspartials the result of loading partial data elements
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return bool if the cm has name data
     */
    protected function add_format_data(stdClass &$data, array $haspartials, renderer_base $output): bool {
        $result = parent::add_format_data($data, $haspartials, $output);

        $data->iscoursedisplaymultipage = (
            $this->format->get_section_display_mode($this->section) == 'summary'
        );

        return $result;
    }

    /**
     * Returns true if the current section should be shown collapsed.
     *
     * @return bool
     */
    protected function is_section_collapsed(): bool {
        return ($this->format->get_section_display_mode($this->section) == 'collapsible')
            ? parent::is_section_collapsed()
            : false;
    }

    /**
     * Get the display mode of the delegated section.
     *
     * @return string
     */
    public function get_display_mode(): string {
        return $this->format->get_section_display_mode($this->section);
    }
}
