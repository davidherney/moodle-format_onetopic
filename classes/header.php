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
 * This file contains class for render the header in the course format onetopic.
 *
 * @package   format_onetopic
 * @copyright 2023 David Herney - https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_onetopic;

use core_courseformat\output\local\content as content_base;
use course_modinfo;

/**
 * Class used to render the header content in each course page.
 *
 *
 * @package   format_onetopic
 * @copyright 2016 David Herney - https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class header implements \renderable, \templatable {

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
     * @param \renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */
    public function export_for_template(\renderer_base $output) {
        global $PAGE, $CFG, $OUTPUT;

        $format = $this->format;
        $course = $this->format->get_course();

        // Onetopic format is always multipage.
        $course->realcoursedisplay = property_exists($course, 'coursedisplay') ? $course->coursedisplay : false;

        $firstsection = ($course->realcoursedisplay == COURSE_DISPLAY_MULTIPAGE) ? 1 : 0;
        $currentsection = $this->format->get_section_number();

        $coursecontext = \context_course::instance($course->id);
        $canviewhidden = has_capability('moodle/course:viewhiddensections', $coursecontext);

        $tabslist = [];
        $secondtabslist = null;
        if ($course->tabsview != \format_onetopic::TABSVIEW_COURSEINDEX &&
                ($format->show_editor() || !$course->hidetabsbar)) {
            $tabs = $this->get_tabs($this->format->get_modinfo(), $output);
            $tabslist = $tabs->get_list();
            $secondtabslist = $tabs->get_secondlist($firstsection ? $currentsection - 1 : $currentsection);
        }

        $tabsview = $course->tabsview == \format_onetopic::TABSVIEW_VERTICAL ||
                    $course->tabsview == \format_onetopic::TABSVIEW_COURSEINDEX ? 'verticaltabs' :
                        ($course->tabsview == \format_onetopic::TABSVIEW_ONELINE ? 'onelinetabs' : 'defaulttabs');

        foreach (\format_onetopic::$formatmsgs as $key => $msg) {
            if (is_string($msg)) {
                \format_onetopic::$formatmsgs[$key] = (object)['message' => $msg];
            }
        }

        $format->hastopictabs = count($tabslist) > 0;

        $courseindex = '';
        if ($course->tabsview == \format_onetopic::TABSVIEW_COURSEINDEX) {
            $renderer = $format->get_renderer($PAGE);
            $courseindex = $renderer->render_from_template('core_courseformat/local/courseindex/drawer', []);
            $hassecondrow = false;
            $hastopictabs = true;
        } else {
            $hastopictabs = $format->hastopictabs;
            $hassecondrow = is_object($secondtabslist) && count($secondtabslist->tabs) > 0;
        }

        $data = (object)[
            'uniqid' => $format->uniqid,
            'baseurl' => $CFG->wwwroot,
            'title' => $this->format->page_title(), // This method should be in the course_format class.
            'format' => $this->format->get_format(),
            'templatetopic' => $course->templatetopic,
            'withicons' => $course->templatetopic_icons,
            'canviewhidden' => $canviewhidden,
            'hastopictabs' => $hastopictabs,
            'tabs' => $tabslist,
            'hassecondrow' => $hassecondrow,
            'secondrow' => $secondtabslist,
            'tabsviewclass' => $tabsview,
            'hasformatmsgs' => count(\format_onetopic::$formatmsgs) > 0,
            'formatmsgs' => \format_onetopic::$formatmsgs,
            'hidetabsbar' => ($course->hidetabsbar == 1 && $format->show_editor()),
            'sectionclasses' => '',
            'courseindex' => $courseindex,
        ];

        $initialsection = null;

        // General section if non-empty and course_display is multiple.
        if ($course->realcoursedisplay == COURSE_DISPLAY_MULTIPAGE) {

            // Load the section 0 and export data for template.
            $modinfo = $this->format->get_modinfo();
            $section0 = $modinfo->get_section_info(0);
            $sectionclass = $this->format->get_output_classname('content\\section');
            $section = new $sectionclass($this->format, $section0);

            $sectionoutput = new \format_onetopic\output\renderer($PAGE, null);
            $initialsection = $section->export_for_template($sectionoutput);

        }

        $data->initialsection = $initialsection;

        // Load the JS module.
        $params = [
            'formattype' => $course->tabsview,
            'icons' => [
                'left' => $OUTPUT->pix_icon('t/collapsed_rtl', ''),
                'right' => $OUTPUT->pix_icon('t/collapsed', ''),
            ]
        ];

        // Include course format js module.
        $PAGE->requires->js('/course/format/topics/format.js');
        $PAGE->requires->js('/course/format/onetopic/format.js');
        $PAGE->requires->yui_module('moodle-core-notification-dialogue', 'M.course.format.dialogueinit');
        $PAGE->requires->js_call_amd('format_onetopic/main', 'init', $params);

        return $data;
    }

    /**
     * Return an array of tabs to display.
     *
     * @param course_modinfo $modinfo the current course modinfo object
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return \format_onetopic\tabs an object with tabs information
     */
    private function get_tabs(course_modinfo $modinfo, \renderer_base $output): \format_onetopic\tabs {

        $course = $this->format->get_course();
        $sections = $modinfo->get_section_info_all();
        $numsections = count($sections);
        $displaysection = $this->format->get_section_number();
        $enablecustomstyles = get_config('format_onetopic', 'enablecustomstyles');

        // Can we view the section in question?
        $context = \context_course::instance($course->id);
        $canviewhidden = has_capability('moodle/course:viewhiddensections', $context);

        // Init custom tabs.
        $section = 0;

        $tabs = new \format_onetopic\tabs();
        $selectedparent = null;
        $parenttab = null;
        $firstsection = ($course->realcoursedisplay == COURSE_DISPLAY_MULTIPAGE) ? 1 : 0;

        while ($section < $numsections) {
            $inactivetab = false;

            if ($course->realcoursedisplay == COURSE_DISPLAY_MULTIPAGE && $section == 0) {
                $section++;
                continue;
            }

            $thissection = $sections[$section];

            $showsection = true;
            if (!$thissection->visible || !$thissection->available) {
                $showsection = $canviewhidden || !($course->hiddensections == 1);
            }

            if ($showsection) {

                $formatoptions = course_get_format($course)->get_format_options($thissection);

                $sectionname = get_section_name($course, $thissection);
                $title = $sectionname;

                if (!$thissection->visible || !$thissection->available) {
                    $title .= ': '. get_string('hiddenfromstudents');
                }

                $customstyles = '';
                $level = 0;
                if (is_array($formatoptions)) {

                    if ($enablecustomstyles) {
                        if (!empty($formatoptions['fontcolor'])) {
                            $customstyles .= 'color: ' . $formatoptions['fontcolor'] . '; ';
                        }

                        if (!empty($formatoptions['bgcolor'])) {
                            $customstyles .= 'background-color: ' . $formatoptions['bgcolor'] . '; ';
                        }

                        if (!empty($formatoptions['cssstyles'])) {
                            $customstyles .= $formatoptions['cssstyles'] . '; ';
                        }
                    }

                    if (isset($formatoptions['level']) && $section > $firstsection) {
                        $level = $formatoptions['level'];
                    }
                }

                if ($section == 0) {
                    $url = new \moodle_url('/course/view.php', ['id' => $course->id, 'section' => 0]);
                } else {
                    $url = course_get_url($course, $section);
                }

                $specialclass = 'tab_position_' . $section . ' tab_level_' . $level;
                if ($course->marker == $section) {
                    $specialclass .= ' marker ';
                }

                if (!$thissection->visible || !$thissection->available) {
                    $specialclass .= ' dimmed disabled ';

                    if (!$canviewhidden) {
                        $inactivetab = true;
                    }
                }

                // Check if display available message is required.
                $availablemessage = null;
                if ($course->hiddensections == 2) {
                    $availabilityclass = $this->format->get_output_classname('content\\section\\availability');
                    $availability = new $availabilityclass($this->format, $thissection);
                    $availabledata = $availability->export_for_template($output);

                    if ($availabledata->hasavailability) {
                        $availablemessage = $output->render($availability);
                    }
                }

                $newtab = new \format_onetopic\singletab($section, $sectionname, $url, $title,
                                        $availablemessage, $customstyles, $specialclass);
                $newtab->active = !$inactivetab;

                if ($displaysection == $section) {
                    $newtab->selected = true;
                }

                if (is_array($formatoptions) && isset($formatoptions['level'])) {

                    if ($formatoptions['level'] == 0 || $parenttab == null) {
                        $tabs->add($newtab);
                        $parenttab = $newtab;
                    } else {

                        if (!$parenttab->has_childs()) {
                            $specialclasstmp = str_replace('tab_level_0', 'tab_level_1', $parenttab->specialclass);
                            $indextab = new \format_onetopic\singletab($parenttab->section,
                                                    $parenttab->content,
                                                    $parenttab->link,
                                                    $parenttab->title,
                                                    $parenttab->availablemessage,
                                                    $parenttab->customstyles,
                                                    $specialclasstmp);

                            $prevsectionindex = $section - 1;
                            do {
                                $parentsection = $sections[$prevsectionindex];
                                $parentformatoptions = course_get_format($course)->get_format_options($parentsection);
                                $prevsectionindex--;
                            } while ($parentformatoptions['level'] == 1 && $prevsectionindex >= $firstsection);

                            if ($parentformatoptions['firsttabtext']) {
                                $indextab->content = $parentformatoptions['firsttabtext'];
                            } else {
                                $indextab->content = get_string('index', 'format_onetopic');
                            }
                            $indextab->title = $indextab->content;
                            $indextab->specialclass .= ' tab_initial ';

                            if ($displaysection == $parentsection->section) {
                                $indextab->selected = true;
                                $parenttab->selected = true;
                                $selectedparent = $parenttab;
                            }

                            $parenttab->add_child($indextab);
                        }

                        // Load subtabs.
                        $parenttab->add_child($newtab);

                        if ($displaysection == $section) {
                            $selectedparent = $parenttab;
                            $parenttab->selected = true;
                        }
                    }
                } else {
                    $tabs->add($newtab);
                    $parenttab = $newtab;
                }

            }

            $section++;
        }

        if ($this->format->show_editor()) {

            $maxsections = $this->format->get_max_sections();

            // Only can add sections if it does not exceed the maximum amount.
            if (count($sections) < $maxsections) {

                $straddsection = get_string('increasesections', 'format_onetopic');
                $icon = $output->pix_icon('t/switch_plus', s($straddsection));
                $insertposition = $displaysection + 1;

                $paramstotabs = array('courseid' => $course->id,
                                    'increase' => true,
                                    'sesskey' => sesskey(),
                                    'insertsection' => $insertposition);

                // Define if subtabs are displayed (a subtab is selected or the selected tab has subtabs).
                $selectedsubtabs = $selectedparent ? $tabs->get_tab($selectedparent->index) : null;
                $showsubtabs = $selectedsubtabs && $selectedsubtabs->has_childs();

                if ($showsubtabs) {
                    // Increase number of sections in child tabs.
                    $paramstotabs['aschild'] = 1;
                    $url = new \moodle_url('/course/format/onetopic/changenumsections.php', $paramstotabs);
                    $newtab = new \format_onetopic\singletab('add', $icon, $url, s($straddsection));
                    $selectedsubtabs->add_child($newtab);

                    // The new tab is inserted after the last child because it is a parent tab.
                    // -2 = add subtab button and index subtab.
                    // +1 = because the selectedparent section start in 0.
                    $insertposition = $selectedparent->section + $selectedsubtabs->count_childs() - 2 + 1;
                }

                $paramstotabs['aschild'] = 0;
                $paramstotabs['insertsection'] = $insertposition;
                $url = new \moodle_url('/course/format/onetopic/changenumsections.php', $paramstotabs);
                $newtab = new \format_onetopic\singletab('add', $icon, $url, s($straddsection));
                $tabs->add($newtab);

            }
        }

        return $tabs;
    }

}
