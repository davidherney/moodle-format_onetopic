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
 * Contains the default section selector.
 *
 * @package   format_onetopic
 * @copyright 2022 David Herney Bernal - cirano. https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 namespace format_onetopic\output\courseformat\content;

use core\output\named_templatable;
use core_courseformat\base as course_format;
use core_courseformat\output\local\courseformat_named_templatable;
use core_courseformat\output\local\content\sectionselector as sectionselector_base;
use renderable;
use stdClass;
use url_select;

/**
 * Represents the section selector.
 *
 * @package   format_onetopic
 * @copyright 2022 David Herney Bernal - cirano. https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sectionselector extends sectionselector_base {

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */
    public function export_for_template(\renderer_base $output): stdClass {

        $format = $this->format;
        $course = $format->get_course();

        $modinfo = $this->format->get_modinfo();

        $data = $this->navigation->export_for_template($output);

        $anchortotabstree = get_config('format_onetopic', 'anchortotabstree');

        $anchor = $anchortotabstree ? '#tabs-tree-start' : '';

        // Add the section selector.
        $sectionmenu = [];
        $section = ($course->realcoursedisplay == COURSE_DISPLAY_MULTIPAGE) ? 1 : 0;
        $numsections = $format->get_last_section_number();
        while ($section <= $numsections) {
            $thissection = $modinfo->get_section_info($section);
            $formatoptions = course_get_format($course)->get_format_options($thissection);
            $prefix = is_array($formatoptions) && $formatoptions['level'] > 0 ? '&nbsp;&nbsp;&nbsp;&nbsp;' : '';

            $url = course_get_url($course, $section);
            if ($thissection->uservisible && $url) {
                $sectionmenu[$url->out(false) . $anchor] = $prefix . get_section_name($course, $section);
            }
            $section++;
        }

        $select = new url_select($sectionmenu, '', ['' => get_string('jumpto')]);
        $select->class = 'jumpmenu';
        $select->formid = 'sectionmenu';

        $data->selector = $output->render($select);
        return $data;
    }
}
