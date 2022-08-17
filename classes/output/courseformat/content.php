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
 * Contains the default content output class.
 *
 * @package   format_onetopics
 * @copyright 2022 David Herney Bernal - cirano. https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_onetopic\output\courseformat;

use core_courseformat\output\local\content as content_base;
use core\output\named_templatable;
use core_courseformat\base as course_format;
use course_modinfo;
use renderable;

/**
 * Base class to render a course content.
 *
 * @package   format_onetopics
 * @copyright 2022 David Herney Bernal - cirano. https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content extends content_base {

    /**
     * @var bool Topic format has add section after each topic.
     *
     * The responsible for the buttons is core_courseformat\output\local\content\section.
     */
    protected $hasaddsection = false;

    /**
     * Returns the output class template path.
     *
     * This method redirects the default template when the course content is rendered.
     */
    public function get_template_name(\renderer_base $renderer): string {
        return 'format_onetopic/local/content';
    }

    /**
     * Export this data so it can be used as the context for a mustache template (core/inplace_editable).
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */
    public function export_for_template(\renderer_base $output) {
        global $PAGE;
        $format = $this->format;
        $course = $format->get_course();
        $firstsection = ($course->realcoursedisplay == COURSE_DISPLAY_MULTIPAGE) ? 1 : 0;

        // If format use the section 0 as a separate section so remove from the list.
        $sections = $this->export_sections($output);
        $initialsection = '';
        if ($firstsection && !empty($sections)) {
            $initialsection = array_shift($sections);
        }

        $tabs = $this->get_tabs($this->format->get_modinfo(), $output);

        $tabsview = $course->tabsview == \format_onetopic::TABSVIEW_VERTICAL ? 'verticaltabs' :
                        ($course->tabsview == \format_onetopic::TABSVIEW_ONELINE ? 'onelinetabs' : 'defaulttabs');

        $data = (object)[
            'title' => $format->page_title(), // This method should be in the course_format class.
            'initialsection' => $initialsection,
            'sections' => $sections,
            'format' => $format->get_format(),
            'sectionreturn' => 0,
            'hastopictabs' => true,
            'tabs' => $tabs->get_list(),
            'tabsviewclass' => $tabsview
        ];

        // The single section format has extra navigation.
        $singlesection = $this->format->get_section_number();
        if ($singlesection || $singlesection === 0) {
            if (!$PAGE->theme->usescourseindex) {
                $sectionnavigation = new $this->sectionnavigationclass($format, $singlesection);
                $data->sectionnavigation = $sectionnavigation->export_for_template($output);

                $sectionselector = new $this->sectionselectorclass($format, $sectionnavigation);
                $data->sectionselector = $sectionselector->export_for_template($output);
            }
            $data->sectionreturn = $singlesection;
        }

        $data->singlesection = array_shift($data->sections);

        if ($this->hasaddsection) {
            $addsection = new $this->addsectionclass($format);
            $data->numsections = $addsection->export_for_template($output);
        }

        return $data;
    }

    /**
     * Export sections array data.
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return array data context for a mustache template
     */
    protected function export_sections(\renderer_base $output): array {

        $format = $this->format;
        $course = $format->get_course();
        $modinfo = $this->format->get_modinfo();

        // Generate section list.
        $sections = [];
        $stealthsections = [];
        $numsections = $format->get_last_section_number();
        foreach ($this->get_sections_to_display($modinfo) as $sectionnum => $thissection) {
            // The course/view.php check the section existence but the output can be called
            // from other parts so we need to check it.
            if (!$thissection) {
                print_error('unknowncoursesection', 'error', course_get_url($course), format_string($course->fullname));
            }

            $section = new $this->sectionclass($format, $thissection);

            if ($sectionnum > $numsections) {
                // Activities inside this section are 'orphaned', this section will be printed as 'stealth' below.
                if (!empty($modinfo->sections[$sectionnum])) {
                    $stealthsections[] = $section->export_for_template($output);
                }
                continue;
            }

            if (!$format->is_section_visible($thissection)) {
                continue;
            }

            $sections[] = $section->export_for_template($output);
        }
        if (!empty($stealthsections)) {
            $sections = array_merge($sections, $stealthsections);
        }
        return $sections;
    }

    /**
     * Return an array of sections to display.
     *
     * This method is used to differentiate between display a specific section
     * or a list of them.
     *
     * @param course_modinfo $modinfo the current course modinfo object
     * @return section_info[] an array of section_info to display
     */
    private function get_sections_to_display(course_modinfo $modinfo): array {
        $sections = [];
        $course = $this->format->get_course();
        $firstsection = ($course->realcoursedisplay == COURSE_DISPLAY_MULTIPAGE) ? 1 : 0;

        if ($firstsection) {
            $sections[] = $modinfo->get_section_info(0);
        }

        $singlesection = $this->format->get_section_number();
        $sections[] = $modinfo->get_section_info($singlesection);

        return $sections;
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

                $customstyles = '';
                $level = 0;
                if (is_array($formatoptions)) {

                    if (!empty($formatoptions['fontcolor'])) {
                        $customstyles .= 'color: ' . $formatoptions['fontcolor'] . ';';
                    }

                    if (!empty($formatoptions['bgcolor'])) {
                        $customstyles .= 'background-color: ' . $formatoptions['bgcolor'] . ';';
                    }

                    if (!empty($formatoptions['cssstyles'])) {
                        $customstyles .= $formatoptions['cssstyles'] . ';';
                    }

                    if (isset($formatoptions['level'])) {
                        $level = $formatoptions['level'];
                    }
                }

                if ($section == 0) {
                    $url = new \moodle_url('/course/view.php', array('id' => $course->id, 'section' => 0));
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
                    $sectiontpl = new content_base\section($this->format, $thissection);
                    $availabilityclass = $this->format->get_output_classname('content\\section\\availability');
                    $availability = new $availabilityclass($this->format, $thissection);
                    $availabledata = $availability->export_for_template($output);

                    if ($availabledata->hasavailability) {
                        $availablemessage = $output->render($availability);
                    }
                }

                $newtab = new \format_onetopic\singletab($section, $sectionname, $url, $sectionname,
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
                                                    $parenttab->url,
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

        return $tabs;
    }
}
