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
 * Contains the default section controls output class.
 *
 * @package   format_onetopic
 * @copyright 2022 David Herney Bernal - cirano. https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_onetopic\output\courseformat\content\section;

use context_course;
use format_topics\output\courseformat\content\section\controlmenu as controlmenu_format_topics;

/**
 * Base class to render a course section menu.
 *
 * @package   format_onetopic
 * @copyright 2022 David Herney Bernal - cirano. https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class controlmenu extends controlmenu_format_topics {

    /** @var course_format the course format class */
    protected $format;

    /** @var section_info the course section class */
    protected $section;

    /**
     * Generate the edit control items of a section.
     *
     * This method must remain public until the final deprecation of section_edit_control_items.
     *
     * @return array of edit control items
     */
    public function section_control_items() {
        global $USER;

        $format = $this->format;
        $section = $this->section;
        $course = $format->get_course();

        $coursecontext = context_course::instance($course->id);
        $numsections = $format->get_last_section_number();
        $isstealth = $section->section > $numsections;

        $baseurl = course_get_url($course);
        $baseurl->param('sesskey', sesskey());

        $parentcontrols = parent::section_control_items();

        $movecontrols = [];
        if ($section->section && !$isstealth && has_capability('moodle/course:movesections', $coursecontext, $USER)) {
            $horizontal = !$course->hidetabsbar && $course->tabsview != \format_onetopic::TABSVIEW_VERTICAL;
            $rtl = right_to_left();

            // Legacy move up and down links.
            $url = clone($baseurl);
            if ($section->section > 1) { // Add a arrow to move section up.
                $url->param('section', $section->section);
                $url->param('move', -1);
                $strmoveup = $horizontal ? get_string('moveleft') : get_string('moveup');
                $movecontrols['moveup'] = [
                    'url' => $url,
                    'icon' => $horizontal ? ($rtl ? 't/right' : 't/left') : 'i/up',
                    'name' => $strmoveup,
                    'pixattr' => ['class' => ''],
                    'attr' => ['class' => 'icon' . ($horizontal ? '' : ' moveup')],
                ];
            }

            $url = clone($baseurl);
            if ($section->section < $numsections) { // Add a arrow to move section down.
                $url->param('section', $section->section);
                $url->param('move', 1);
                $strmovedown = $horizontal ? get_string('moveright') : get_string('movedown');
                $movecontrols['movedown'] = [
                    'url' => $url,
                    'icon' => $horizontal ? ($rtl ? 't/left' : 't/right') : 'i/down',
                    'name' => $strmovedown,
                    'pixattr' => ['class' => ''],
                    'attr' => ['class' => 'icon' . ($horizontal ? '' : ' movedown')],
                ];
            }
        }

        // ToDo: reload the page is a temporal solution. We need control the delete tab action with JS.
        if (array_key_exists("delete", $parentcontrols)) {
            $url = new \moodle_url('/course/editsection.php', [
                'id' => $section->id,
                'sr' => $section->section - 1,
                'delete' => 1,
                'sesskey' => sesskey(), ]);
            $parentcontrols['delete']['url'] = $url;
            unset($parentcontrols['delete']['attr']['data-action']);
        }

        // Create the permalink according to the Onetopic format.
        if (array_key_exists("permalink", $parentcontrols)) {
            $sectionlink = new \moodle_url(
                '/course/view.php',
                ['id' => $course->id, 'sectionid' => $section->id],
                'tabs-tree-start');

            $parentcontrols['permalink']['url'] = $sectionlink;
        }

        // If the delete key exists, we are going to insert our controls before it.
        $merged = [];
        $movecontrolsbefore = null;
        foreach (['delete', 'permalink'] as $controlname) {
            if (array_key_exists($controlname, $parentcontrols)) {
                $movecontrolsbefore = $controlname;
                break;
            }
        }

        // We can't use splice because we are using associative arrays.
        // Step through the array and merge the arrays.
        foreach ($parentcontrols as $key => $action) {
            if ($key == $movecontrolsbefore) {
                $merged = array_merge($merged, $movecontrols);
            }
            $merged[$key] = $action;
        }

        if (!$movecontrolsbefore) {
            $merged = array_merge($merged, $movecontrols);
        }

        return $merged;
    }
}
