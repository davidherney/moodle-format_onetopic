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
 * @package   format_onetopic
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
 * @package   format_onetopic
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
     *
     * @param renderer_base $renderer typically, the renderer that's calling this function
     * @return string format template name
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
        $currentsection = $this->format->get_section_number();

        // If format use the section 0 as a separate section so remove from the list.
        $sections = $this->export_sections($output);
        $initialsection = '';
        if ($firstsection && !empty($sections)) {
            $initialsection = array_shift($sections);
        }

        $tabslist = [];
        $secondtabslist = null;
        if ($format->show_editor() || !$course->hidetabsbar) {
            $tabs = $this->get_tabs($this->format->get_modinfo(), $output);
            $tabslist = $tabs->get_list();
            $secondtabslist = $tabs->get_secondlist($firstsection ? $currentsection - 1 : $currentsection);
        }

        $tabsview = $course->tabsview == \format_onetopic::TABSVIEW_VERTICAL ? 'verticaltabs' :
                        ($course->tabsview == \format_onetopic::TABSVIEW_ONELINE ? 'onelinetabs' : 'defaulttabs');

        foreach (\format_onetopic::$formatmsgs as $key => $msg) {
            if (is_string($msg)) {
                \format_onetopic::$formatmsgs[$key] = (object)['message' => $msg];
            }
        }

        $hassecondrow = is_object($secondtabslist) && count($secondtabslist->tabs) > 0;

        $withunits = ['font-size', 'line-height', 'margin', 'padding', 'border-width', 'border-radius'];
        $csscontent = '';
        $csstabstyles = '';
        $tabstyles = get_config('format_onetopic', 'tabstyles');
        if (!empty($tabstyles)) {
            $tabstyles = @json_decode($tabstyles);

            if (is_object($tabstyles)) {

                $precedence = ['default', 'childs', 'childindex', 'active', 'parent', 'highlighted', 'disabled', 'hover'];

                $orderedtabs = new \stdClass();
                foreach ($precedence as $type) {
                    if (property_exists($tabstyles, $type)) {
                        $orderedtabs->$type = $tabstyles->$type;
                    }
                }

                foreach ($orderedtabs as $type => $styles) {

                    switch ($type) {
                        case 'active':
                            $csscontent .= '#tabs-tree-start .verticaltabs .format_onetopic-tabs .nav-item a.nav-link.active, ';
                            $csscontent .= '#tabs-tree-start .nav-tabs a.nav-link.active, ';
                            $csscontent .= '#tabs-tree-start .onetopic-tab-body .nav-tabs a.nav-link.active, ';
                            $csscontent .= '#tabs-tree-start .onetopic-tab-body .nav-tabs .nav-item.subtopic a.nav-link.active';
                        break;
                        case 'parent':
                            $csscontent .= '#tabs-tree-start .verticaltabs .format_onetopic-tabs .nav-item.haschilds a.nav-link, ';
                            $csscontent .= '#tabs-tree-start .nav-tabs .nav-item.haschilds a.nav-link';
                        break;
                        case 'highlighted':
                            $csscontent .= '#tabs-tree-start .verticaltabs .format_onetopic-tabs .nav-item.marker a.nav-link, ';
                            $csscontent .= '#tabs-tree-start .nav-tabs .nav-item.marker a.nav-link, ';
                            $csscontent .= '#tabs-tree-start .onetopic-tab-body .nav-tabs .nav-item.marker a.nav-link';
                            $csscontent .= '#tabs-tree-start .onetopic-tab-body .nav-tabs .nav-item.subtopic.marker a.nav-link';
                        break;
                        case 'disabled':
                            $csscontent .= '#tabs-tree-start .verticaltabs .format_onetopic-tabs .nav-item.disabled a.nav-link, ';
                            $csscontent .= '#tabs-tree-start .nav-tabs .nav-item.disabled a.nav-link, ';
                            $csscontent .= '#tabs-tree-start .onetopic-tab-body .nav-tabs .nav-item.disabled a.nav-link';
                            $csscontent .= '#tabs-tree-start .onetopic-tab-body .nav-tabs .nav-item.subtopic.disabled a.nav-link';
                        break;
                        case 'hover':
                            $csscontent .= '#tabs-tree-start .verticaltabs .format_onetopic-tabs .nav-item a.nav-link:hover, ';
                            $csscontent .= '#tabs-tree-start .format_onetopic-tabs.nav-tabs .nav-item a.nav-link:hover, ';
                            $csscontent .= '#tabs-tree-start .onetopic-tab-body .format_onetopic-tabs.nav-tabs' .
                                            ' .nav-item a.nav-link:hover';
                        break;
                        case 'childs':
                            $csscontent .= '#tabs-tree-start .onetopic-tab-body .nav-tabs .nav-item.subtopic a.nav-link';
                        break;
                        case 'childindex':
                            $csscontent .= '#tabs-tree-start .onetopic-tab-body .nav-tabs' .
                                            ' .nav-item.subtopic.tab_initial a.nav-link';
                        break;
                        default:
                            $csscontent .= '#tabs-tree-start .verticaltabs .format_onetopic-tabs .nav-item a.nav-link, ';
                            $csscontent .= '#tabs-tree-start .nav-tabs a.nav-link';
                    }

                    $csscontent .= '{';
                    $units = [];

                    // Check if exist units for some rules.
                    foreach ($styles as $key => $value) {

                        // Check if the key start with the units prefix.
                        if (strpos($key, 'unit-') === 0) {

                            // Remove the prefix.
                            $ownerkey = str_replace('unit-', '', $key);
                            $units[$ownerkey] = $value;
                            unset($styles->$key);
                        }
                    }

                    foreach ($styles as $key => $value) {

                        // If exist a unit for the rule, apply it.
                        if (isset($units[$key])) {
                            $value = $value . $units[$key];
                        } else if (in_array($key, $withunits)) {
                            // If the rule need units, apply px by default.
                            $value = $value . 'px';
                        }

                        if ($key == 'others') {
                            $csscontent .= $value . ';';
                        } else {
                            $csscontent .= $key . ':' . $value . ';';
                        }
                    }

                    $csscontent .= '}';
                }

                // Clean the CSS for html tags.
                $csstabstyles = preg_replace('/<[^>]*>/', '', $csscontent);
            }
        }

        $data = (object)[
            'title' => $format->page_title(), // This method should be in the course_format class.
            'initialsection' => $initialsection,
            'sections' => $sections,
            'format' => $format->get_format(),
            'sectionreturn' => 0,
            'hastopictabs' => count($tabslist) > 0,
            'tabs' => $tabslist,
            'hassecondrow' => $hassecondrow,
            'secondrow' => $secondtabslist,
            'tabsviewclass' => $tabsview,
            'hasformatmsgs' => count(\format_onetopic::$formatmsgs) > 0,
            'formatmsgs' => \format_onetopic::$formatmsgs,
            'hidetabsbar' => ($course->hidetabsbar == 1 && $format->show_editor()),
            'sectionclasses' => '',
            'csstabstyles' => $csstabstyles,
        ];

        // The current section format has extra navigation.
        if ($currentsection || $currentsection === 0) {

            $usessectionsnavigation = isset($course->usessectionsnavigation) ? $course->usessectionsnavigation : null;
            if (empty($usessectionsnavigation)) {
                $usessectionsnavigation = get_config('format_onetopic', 'defaultsectionsnavigation');
            }

            if ($usessectionsnavigation != \format_onetopic::SECTIONSNAVIGATION_NOT) {
                if ($usessectionsnavigation != \format_onetopic::SECTIONSNAVIGATION_SUPPORT ||
                        !$PAGE->theme->usescourseindex) {

                    $sectionnavigation = new $this->sectionnavigationclass($format, $currentsection);

                    // Not show navigation in top section if is not both.
                    if ($usessectionsnavigation == \format_onetopic::SECTIONSNAVIGATION_BOTH) {
                        $data->sectionnavigation = $sectionnavigation->export_for_template($output);
                    }

                    $sectionselector = new $this->sectionselectorclass($format, $sectionnavigation);
                    $data->sectionselector = $sectionselector->export_for_template($output);

                    if ($usessectionsnavigation == \format_onetopic::SECTIONSNAVIGATION_SLIDES) {
                        $data->sectionclasses .= ' sectionsnavigation-slides';
                    }
                }
            }

            $data->sectionreturn = $currentsection;
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
                throw new \moodle_exception('unknowncoursesection', 'error', course_get_url($course), s($course->fullname));
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

        global $PAGE;

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
