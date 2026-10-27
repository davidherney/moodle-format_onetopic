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
     * @var section The onetopic section output instance.
     */
    public $onesection;

    /**
     * Load the section according to the current format because the delegated class is not aware of the specific format.
     *
     */
    public function load_onesection() {
        $this->onesection = new \format_onetopic\output\courseformat\content\section($this->format, $this->section);
        $this->onesection->insection = true;
    }

    /**
     * Get the display mode of the delegated section.
     *
     * @return string
     */
    public function get_displaymode(): string {
        // Prefer the section_info property so rendering matches get_view_url() after displaymode is cached.
        $mode = $this->section->displaymode;

        if (empty($mode)) {
            $mode = get_config('format_onetopic', 'defaultsubsectionsdisplay');
        }

        return $mode ?? \format_onetopic::SUBSECTIONSDISPLAY_LIST;
    }

    /**
     * Export the activity summary or the activity list for a delegated section.
     *
     * The summary card is used when the section is not the current one and the page is not being edited or moved.
     * The activity list is used otherwise. This does not consult get_course_display().
     *
     * @param stdClass $data The current section data reference.
     * @param renderer_base $output The renderer calling this function.
     * @return bool True when activity data was added.
     */
    protected function add_cm_data(stdClass &$data, renderer_base $output): bool {
        global $destsection, $move;

        $result = false;
        $section = $this->section;
        $format = $this->format;
        $movingsection = !empty($destsection) && !empty($move);

        $showsummary = ($section->section != 0 &&
            $section->section != $format->get_sectionnum() &&
            !$movingsection &&
            !$format->show_editor()
        );

        $showcmlist = $section->uservisible;

        if ($showsummary) {
            $cmsummary = new $this->cmsummaryclass($format, $section);
            $data->cmsummary = $cmsummary->export_for_template($output);
            $data->onlysummary = true;
            $result = true;

            if (!$format->is_section_current($section)) {
                $showcmlist = false;
            }
        }
        if ($showcmlist) {
            $cmlist = new $this->cmlistclass($format, $section);
            $data->cmlist = $cmlist->export_for_template($output);
            $result = true;
        }
        return $result;
    }
}
