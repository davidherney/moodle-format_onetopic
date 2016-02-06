<?php
//
// You can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// It is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @since 2.0
 * @package contribution
 * @copyright 2012 David Herney Bernal - cirano
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/course/format/renderer.php');

/**
 * Basic renderer for onetopic format.
 *
 * @copyright 2012 David Herney Bernal - cirano
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_onetopic_renderer extends format_section_renderer_base {

    private $_format_data;
    private $_course;

    /**
     * Constructor method, calls the parent constructor
     *
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        parent::__construct($page, $target);

        // Since format_topics_renderer::section_edit_controls() only displays the 'Set current section' control when editing mode is on
        // we need to be sure that the link 'Turn editing mode on' is available for a user who does not have any other managing capability.
        $page->set_other_editing_capability('moodle/course:setcurrentsection');
    }

    /**
     * Generate the starting container html for a list of sections
     * @return string HTML to output.
     */
    protected function start_section_list() {
        return html_writer::start_tag('ul', array('class' => 'topics'));
    }

    /**
     * Generate the closing container html for a list of sections
     * @return string HTML to output.
     */
    protected function end_section_list() {
        return html_writer::end_tag('ul');
    }

    /**
     * Generate the title for this section page
     * @return string the page title
     */
    protected function page_title() {
        return get_string('topicoutline');
    }

    /**
     * Generate next/previous section links for navigation
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections The course_sections entries from the DB
     * @param int $sectionno The section number in the coruse which is being dsiplayed
     * @return array associative array with previous and next section link
     */
    protected function get_nav_links($course, $sections, $sectionno) {
        // FIXME: This is really evil and should by using the navigation API.
        $course = course_get_format($course)->get_course();
        $canviewhidden = has_capability('moodle/course:viewhiddensections', context_course::instance($course->id))
            or !$course->hiddensections;

        $links = array('previous' => '', 'next' => '');
        $back = $sectionno - 1;

        while ((($back > 0 && $course->realcoursedisplay == COURSE_DISPLAY_MULTIPAGE) || ($back >= 0 && $course->realcoursedisplay != COURSE_DISPLAY_MULTIPAGE)) &&
                empty($links['previous'])) {
            if ($canviewhidden || $sections[$back]->uservisible) {
                $params = array();
                if (!$sections[$back]->visible) {
                    $params = array('class' => 'dimmed_text');
                }
                $previouslink = html_writer::tag('span', $this->output->larrow(), array('class' => 'larrow'));
                $previouslink .= get_section_name($course, $sections[$back]);
                $links['previous'] = html_writer::link(course_get_url($course, $back), $previouslink, $params);
            }
            $back--;
        }

        $forward = $sectionno + 1;
        while ($forward <= $course->numsections and empty($links['next'])) {
            if ($canviewhidden || $sections[$forward]->uservisible) {
                $params = array();
                if (!$sections[$forward]->visible) {
                    $params = array('class' => 'dimmed_text');
                }
                $nextlink = get_section_name($course, $sections[$forward]);
                $nextlink .= html_writer::tag('span', $this->output->rarrow(), array('class' => 'rarrow'));
                $links['next'] = html_writer::link(course_get_url($course, $forward), $nextlink, $params);
            }
            $forward++;
        }

        return $links;
    }

    /**
     * Output the html for a single section page .
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections The course_sections entries from the DB
     * @param array $mods used for print_section()
     * @param array $modnames used for print_section()
     * @param array $modnamesused used for print_section()
     * @param int $displaysection The section number in the course which is being displayed
     */
    public function print_single_section_page($course, $sections, $mods, $modnames, $modnamesused, $displaysection) {
        global $PAGE, $OUTPUT;;

        $real_course_display = $course->realcoursedisplay;
        $modinfo = get_fast_modinfo($course);
        $course = course_get_format($course)->get_course();
        $course->realcoursedisplay = $real_course_display; 
        $sections = $modinfo->get_section_info_all();

        // Can we view the section in question?
        $context = context_course::instance($course->id);
        $canviewhidden = has_capability('moodle/course:viewhiddensections', $context);

        if (!isset($sections[$displaysection])) {
            // This section doesn't exist
            print_error('unknowncoursesection', 'error', null, $course->fullname);
            return;
        }

        // Copy activity clipboard..
        echo $this->course_activity_clipboard($course, $displaysection);

        $format_data = new stdClass();
        $format_data->mods = $mods;
        $format_data->modinfo = $modinfo;
        $this->_course = $course;
        $this->_format_data = $format_data;

        // General section if non-empty and course_display is multiple.
        if ($course->realcoursedisplay == COURSE_DISPLAY_MULTIPAGE) {
            $thissection = $sections[0];
            if ((($thissection->visible && $thissection->available) || $canviewhidden) && ($thissection->summary || $thissection->sequence || $PAGE->user_is_editing())) {
                echo $this->start_section_list();
                echo $this->section_header($thissection, $course, true);

                if ($this->_course->templatetopic == format_onetopic::TEMPLATETOPIC_NOT) {
                    echo $this->courserenderer->course_section_cm_list($course, $thissection, $displaysection);
                }
                else if ($this->_course->templatetopic == format_onetopic::TEMPLATETOPIC_LIST) {
                    echo $this->custom_course_section_cm_list($course, $thissection, $displaysection);
                }

                echo $this->courserenderer->course_section_add_cm_control($course, 0, $displaysection);

                echo $this->section_footer();
                echo $this->end_section_list();
            }
        }

        // Start single-section div
        echo html_writer::start_tag('div', array('class' => 'single-section onetopic'));

        //Move controls
        $can_move = false;
        if ($PAGE->user_is_editing() && has_capability('moodle/course:movesections', $context) && $displaysection > 0) {
            $can_move = true;
        }
        $move_list_html = '';
        $count_move_sections = 0;

        //Init custom tabs
        $section = 0;

        $sectionmenu = array();
        $tabs = array();
        $inactive_tabs = array();

        $default_topic = -1;

        while ($section <= $course->numsections) {

            if ($course->realcoursedisplay == COURSE_DISPLAY_MULTIPAGE && $section == 0) {
                $section++;
                continue;
            }

            $thissection = $sections[$section];

            $showsection = true;
            if (!$thissection->visible || !$thissection->available) {
                $showsection = false;
            }
            else if ($section == 0 && !($thissection->summary || $thissection->sequence || $PAGE->user_is_editing())){
                $showsection = false;
            }

            if (!$showsection) {
                $showsection = $canviewhidden || !$course->hiddensections;
            }

            if (isset($displaysection)) {
                if ($showsection) {

                    if ($default_topic < 0) {
                        $default_topic = $section;

                        if ($displaysection == 0) {
                            $displaysection = $default_topic;
                        }
                    }

                    $format_options = course_get_format($course)->get_format_options($thissection);

                    $sectionname = get_section_name($course, $thissection);

                    if ($displaysection != $section) {
                        $sectionmenu[$section] = $sectionname;
                    }

                    $custom_styles = '';
                    $level = 0;
                    if (is_array($format_options)) {

                        if (!empty($format_options['fontcolor'])) {
                            $custom_styles .= 'color: ' . $format_options['fontcolor'] . ';';
                        }

                        if (!empty($format_options['bgcolor'])) {
                            $custom_styles .= 'background-color: ' . $format_options['bgcolor'] . ';';
                        }

                        if (!empty($format_options['cssstyles'])) {
                            $custom_styles .= $format_options['cssstyles'] . ';';
                        }

                        if (isset($format_options['level'])) {
                            $level = $format_options['level'];
                        }
                    }

                    if ($section == 0) {
                        $url = new moodle_url('/course/view.php', array('id' => $course->id, 'section' => 0));
                    } else {
                        $url = course_get_url($course, $section);
                    }

                    $special_style = 'tab_position_' . $section . ' tab_level_' . $level;
                    if ($course->marker == $section) {
                        $special_style = ' marker '; 
                    }

                    if (!$thissection->visible || !$thissection->available) {
                        $special_style .= ' dimmed ';

                        if (!$canviewhidden) {
                            $inactive_tabs[] = "tab_topic_" . $section;
                        }
                    }

                    $new_tab = new tabobject("tab_topic_" . $section, $url,
                    '<div style="' . $custom_styles . '" class="tab_content ' . $special_style . '">' . s($sectionname) . "</div>", s($sectionname));

                    if (is_array($format_options) && isset($format_options['level'])) {

                        if($format_options['level'] == 0 || count($tabs) == 0) {
                            $tabs[] = $new_tab;
                            $new_tab->level = 1;
                        }
                        else {
                            $parent_index = count($tabs) - 1;
                            if (!is_array($tabs[$parent_index]->subtree)) {
                                $tabs[$parent_index]->subtree = array();
                            }
                            else if (count($tabs[$parent_index]->subtree) == 0) {
                                $tabs[$parent_index]->subtree[0] = clone($tabs[$parent_index]);
                                $tabs[$parent_index]->subtree[0]->id .= '_index';
                                $parent_section = $sections[$section-1];
                                $parentformat_options = course_get_format($course)->get_format_options($parent_section);
                                if ($parentformat_options['firsttabtext']) {
                                    $firsttab_text = $parentformat_options['firsttabtext'];
                                } else {
                                    $firsttab_text = get_string('index', 'format_onetopic');
                                }
                                $tabs[$parent_index]->subtree[0]->text = '<div class="tab_content tab_initial">' . $firsttab_text. "</div>";
                                $tabs[$parent_index]->subtree[0]->level = 2;

                                if($displaysection == $section - 1) {
                                    $tabs[$parent_index]->subtree[0]->selected = true;
                                }
                            }
                            $new_tab->level = 2;
                            $tabs[$parent_index]->subtree[] = $new_tab;
                        }
                    }
                    else {
                        $tabs[] = $new_tab;
                    }

                    //Init move section list***************************************************************************
                    if ($can_move) {
                        if ($section > 0) { // Move section
                            $baseurl = course_get_url($course, $displaysection);
                            $baseurl->param('sesskey', sesskey());

                            $url = clone($baseurl);

                            $url->param('move', $section - $displaysection);

                            //ToDo: For new feature: subtabs. It is not implemented yet
                            /*
                            $strsubtopictoright = get_string('subtopictoright', 'format_onetopic');
                            $url = new moodle_url('/course/view.php', array('id' => $course->id, 'subtopicmove' => 'right', 'subtopic' => $section));
                            $icon = $this->output->pix_icon('t/right', $strsubtopictoright);
                            $subtopic_move = html_writer::link($url, $icon.get_accesshide($strsubtopictoright), array('class' => 'subtopic-increase-sections'));


                            if ($displaysection != $section) {
                                $move_list_html .= html_writer::tag('li', $subtopic_move . html_writer::link($url, $sectionname));
                               }
                            else {
                                $move_list_html .= html_writer::tag('li', $subtopic_move . $sectionname);
                            }
                            */

                            //Define class from sublevels in order to move a margen in the left. Not apply if it is the first element (condition !empty($move_list_html)) because the first element can't be a sublevel
                            $li_class = '';
                            if (is_array($format_options) && isset($format_options['level']) && $format_options['level'] > 0 && !empty($move_list_html)) {
                                $li_class = 'sublevel';
                            }

                            if ($displaysection != $section) {
                                $move_list_html .= html_writer::tag('li', html_writer::link($url, $sectionname), array('class' => $li_class));
                               }
                            else {
                                $move_list_html .= html_writer::tag('li', $sectionname, array('class' => $li_class));
                            }
                        }
                    }
                    //End move section list***************************************************************************                
                }
            }

            $section++;
        }

        // Title with section navigation links.
        $sectionnavlinks = $this->get_nav_links($course, $sections, $displaysection);
        $sectiontitle = '';

        if (!$course->hidetabsbar && count($tabs[0]) > 0) {

            if ($PAGE->user_is_editing() && has_capability('moodle/course:update', $context)) {
                // Increase number of sections.
                $straddsection = get_string('increasesections', 'moodle');
                $url = new moodle_url('/course/changenumsections.php',
                    array('courseid' => $course->id,
                        'increase' => true,
                        'sesskey' => sesskey()));
                $icon = $this->output->pix_icon('t/switch_plus', $straddsection);
                $tabs[] = new tabobject("tab_topic_add", $url, $icon, s($straddsection));

                if ($course->numsections > 0) {
                    // Reduce number of sections.
                    $strremovesection = get_string('reducesections', 'moodle');
                    $url = new moodle_url('/course/changenumsections.php',
                        array('courseid' => $course->id,
                            'increase' => false,
                            'sesskey' => sesskey()));
                    $icon = $this->output->pix_icon('t/switch_minus', $strremovesection);
                    $tabs[] = new tabobject("tab_topic_remove", $url, $icon, s($strremovesection));
                }
            }

            $sectiontitle .= $OUTPUT->tabtree($tabs, "tab_topic_" . $displaysection, $inactive_tabs);//print_tabs($tabs, "tab_topic_" . $displaysection, $inactive_tabs, $active_tabs, true);
        }

        echo $sectiontitle;

        if (!$sections[$displaysection]->uservisible && !$canviewhidden) {
            if (!$course->hiddensections) {
                //Not used more, is controled in /course/view.php
            }
            // Can't view this section.
        }
        else {

            if ($course->realcoursedisplay != COURSE_DISPLAY_MULTIPAGE || $displaysection !== 0) {
                // Now the list of sections..
                echo $this->start_section_list();

                // The requested section page.
                $thissection = $sections[$displaysection];
                echo $this->section_header($thissection, $course, true);
                // Show completion help icon.
                $completioninfo = new completion_info($course);
                echo $completioninfo->display_help_icon();

                if ($this->_course->templatetopic == format_onetopic::TEMPLATETOPIC_NOT) {
                    echo $this->courserenderer->course_section_cm_list($course, $thissection, $displaysection);
                }
                else if ($this->_course->templatetopic == format_onetopic::TEMPLATETOPIC_LIST) {
                    echo $this->custom_course_section_cm_list($course, $thissection, $displaysection);
                }

                echo $this->courserenderer->course_section_add_cm_control($course, $displaysection, $displaysection);
                echo $this->section_footer();
                echo $this->end_section_list();
            }
        }

        // Display section bottom navigation.
        $sectionbottomnav = '';
        $sectionbottomnav .= html_writer::start_tag('div', array('class' => 'section-navigation mdl-bottom'));
        $sectionbottomnav .= html_writer::tag('span', $sectionnavlinks['previous'], array('class' => 'mdl-left'));
        $sectionbottomnav .= html_writer::tag('span', $sectionnavlinks['next'], array('class' => 'mdl-right'));
        $sectionbottomnav .= html_writer::end_tag('div');
        echo $sectionbottomnav;

        // close single-section div.
        echo html_writer::end_tag('div');
        
        if ($PAGE->user_is_editing() && has_capability('moodle/course:update', $context)) {

            echo '<br class="utilities-separator" />';
            print_collapsible_region_start('move-list-box clearfix collapsible mform', 'course_format_onetopic_config_movesection', get_string('utilities', 'format_onetopic'), '', true);
            

            //Move controls
            if ($can_move && !empty($move_list_html)) {
                echo html_writer::start_div("form-item clearfix");
                    echo html_writer::start_div("form-label");
                        echo html_writer::tag('label', get_string('movesectionto', 'format_onetopic'));
                    echo html_writer::end_div();
                    echo html_writer::start_div("form-setting");
                        echo html_writer::tag('ul', $move_list_html, array('class' => 'move-list'));
                    echo html_writer::end_div();
                    echo html_writer::start_div("form-description");
                        echo html_writer::tag('p', get_string('movesectionto_help', 'format_onetopic'));
                    echo html_writer::end_div();
                echo html_writer::end_div();
            }
        
            $baseurl = course_get_url($course, $displaysection);
            $baseurl->param('sesskey', sesskey());

            $url = clone($baseurl);

            global $USER, $OUTPUT;
            if (isset($USER->onetopic_da[$course->id]) && $USER->onetopic_da[$course->id]) {
                $url->param('onetopic_da', 0);
                $text_button_disableajax = get_string('enable', 'format_onetopic');
            }
            else {
                $url->param('onetopic_da', 1);
                $text_button_disableajax = get_string('disable', 'format_onetopic');
            }

            echo html_writer::start_div("form-item clearfix");
                echo html_writer::start_div("form-label");
                    echo html_writer::tag('label', get_string('disableajax', 'format_onetopic'));
                echo html_writer::end_div();
                echo html_writer::start_div("form-setting");
                    echo html_writer::link($url, $text_button_disableajax);
                echo html_writer::end_div();
                echo html_writer::start_div("form-description");
                    echo html_writer::tag('p', get_string('disableajax_help', 'format_onetopic'));
                echo html_writer::end_div();
            echo html_writer::end_div();

            //Duplicate current section option
            if (has_capability('moodle/course:manageactivities', $context)) {
                $url_duplicate = new moodle_url('/course/format/onetopic/duplicate.php', array('courseid' => $course->id, 'section' => $displaysection, 'sesskey' => sesskey()));

                $link = new action_link($url_duplicate, get_string('duplicate', 'format_onetopic'));
                $link->add_action(new confirm_action(get_string('duplicate_confirm', 'format_onetopic'), null, get_string('duplicate', 'format_onetopic')));

                echo html_writer::start_div("form-item clearfix");
                    echo html_writer::start_div("form-label");
                        echo html_writer::tag('label', get_string('duplicatesection', 'format_onetopic'));
                    echo html_writer::end_div();
                    echo html_writer::start_div("form-setting");
                        echo $this->render($link);
                    echo html_writer::end_div();
                    echo html_writer::start_div("form-description");
                        echo html_writer::tag('p', get_string('duplicatesection_help', 'format_onetopic'));
                    echo html_writer::end_div();
                echo html_writer::end_div();
            }

            print_collapsible_region_end();
        }
    }

    /**
     * Generate the display of the header part of a section before
     * course modules are included
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @param bool $onsectionpage true if being printed on a single-section page
     * @param int $sectionreturn The section to return to after an action
     * @return string HTML to output.
     */
    protected function section_header($section, $course, $onsectionpage, $sectionreturn=null) {
        global $PAGE;
    
        $o = '';
        $currenttext = '';
        $sectionstyle = '';
    
        if ($section->section != 0) {
            // Only in the non-general sections.
            if (!$section->visible) {
                $sectionstyle = ' hidden';
            } else if (course_get_format($course)->is_section_current($section)) {
                $sectionstyle = ' current';
            }
        }
    
        $o.= html_writer::start_tag('li', array('id' => 'section-'.$section->section,
                'class' => 'section main clearfix'.$sectionstyle, 'role'=>'region',
                'aria-label'=> get_section_name($course, $section)));
    
        $leftcontent = $this->section_left_content($section, $course, $onsectionpage);
        $o.= html_writer::tag('div', $leftcontent, array('class' => 'left side'));
    
        $rightcontent = $this->section_right_content($section, $course, $onsectionpage);
        $o.= html_writer::tag('div', $rightcontent, array('class' => 'right side'));
        $o.= html_writer::start_tag('div', array('class' => 'content'));
    
        $classes = ' accesshide';

        $sectionname = html_writer::tag('span', $this->section_title($section, $course));
        $o.= $this->output->heading($sectionname, 3, 'sectionname' . $classes);

        $o.= html_writer::start_tag('div', array('class' => 'summary'));
        $o.= $this->format_summary_text($section);
        $o.= html_writer::end_tag('div');
    
        $context = context_course::instance($course->id);
        $o .= $this->section_availability_message($section,
            has_capability('moodle/course:viewhiddensections', $context));

        return $o;
    }

    /**
     * Generate the edit control items of a section
     *
     * @param stdClass $course The course entry from DB
     * @param stdClass $section The course_section entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return array of edit control items
     */
    protected function section_edit_control_items($course, $section, $onsectionpage = false) {
        global $PAGE;

        if (!$PAGE->user_is_editing()) {
            return array();
        }

        $coursecontext = context_course::instance($course->id);

        if ($onsectionpage) {
            $url = course_get_url($course, $section->section);
        } else {
            $url = course_get_url($course);
        }
        $url->param('sesskey', sesskey());

        $isstealth = $section->section > $course->numsections;
        $controls = array();
        if (!$isstealth && $section->section && has_capability('moodle/course:setcurrentsection', $coursecontext)) {
            if ($course->marker == $section->section) {  // Show the "light globe" on/off.
                $url->param('marker', 0);
                $markedthistopic = get_string('markedthistopic');
                $highlightoff = get_string('highlightoff');
                $controls['highlight'] = array('url' => $url, "icon" => 'i/marked',
                                               'name' => $highlightoff,
                                               'pixattr' => array('class' => '', 'alt' => $markedthistopic),
                                               'attr' => array('class' => 'editing_highlight', 'title' => $markedthistopic));
            } else {
                $url->param('marker', $section->section);
                $markthistopic = get_string('markthistopic');
                $highlight = get_string('highlight');
                $controls['highlight'] = array('url' => $url, "icon" => 'i/marker',
                                               'name' => $highlight,
                                               'pixattr' => array('class' => '', 'alt' => $markthistopic),
                                               'attr' => array('class' => 'editing_highlight', 'title' => $markthistopic));
            }
        }

        $parentcontrols = parent::section_edit_control_items($course, $section, $onsectionpage);

        // If the edit key exists, we are going to insert our controls after it.
        if (array_key_exists("edit", $parentcontrols)) {
            $merged = array();
            // We can't use splice because we are using associative arrays.
            // Step through the array and merge the arrays.
            foreach ($parentcontrols as $key => $action) {
                $merged[$key] = $action;
                if ($key == "edit") {
                    // If we have come to the edit key, merge these controls here.
                    $merged = array_merge($merged, $controls);
                }
            }

            return $merged;
        } else {
            return array_merge($controls, $parentcontrols);
        }
    }

    /**
     * Generate html for a section summary text
     *
     * @param stdClass $section The course_section entry from DB
     * @return string HTML to output.
     */
    protected function format_summary_text($section) {

        if ($this->_course->templatetopic != format_onetopic::TEMPLATETOPIC_NOT) {
            $section->summary = $this->replace_resources($section);
        }

        return parent::format_summary_text($section);
    }

    private function replace_resources ($section) {

        global $CFG, $USER;

        static $initialised;

        static $groupbuttons;
        static $groupbuttonslink;
        static $strunreadpostsone;
        static $usetracking;
        static $groupings;

        $course = $this->_course;
        $completioninfo = new completion_info($course);

        if (!isset($initialised)) {
            $groupbuttons     = ($course->groupmode || (!$course->groupmodeforce));
            $groupbuttonslink = (!$course->groupmodeforce);
            include_once($CFG->dirroot.'/mod/forum/lib.php');
            if ($usetracking = forum_tp_can_track_forums()) {
                $strunreadpostsone = get_string('unreadpostsone', 'forum');
            }
            $initialised = true;
        }

        $labelformatoptions = new object();
        $labelformatoptions->noclean = true;

        /// Casting $course->modinfo to string prevents one notice when the field is null
        $modinfo = $this->_format_data->modinfo;

        $summary = $section->summary;

        $html_resource = '';
        $html_more     = '';

        if (!empty($section->sequence)) {
            $sectionmods = explode(",", $section->sequence);

            $obj_replace = new format_onetopic_replace_regularexpression();

            foreach ($sectionmods as $modnumber) {

                if (empty($this->_format_data->mods[$modnumber])) {
                    continue;
                }

                $mod = $this->_format_data->mods[$modnumber];

                if ($mod->modname == "label") {
                    continue;
                }

                $instancename = format_string($modinfo->cms[$modnumber]->name, true, $course->id);

                // Display the link to the module (or do nothing if module has no url)
                $cmname = $this->courserenderer->course_section_cm_name($mod);

                if (!empty($cmname)) {
                    $cmname = str_replace('<div ', '<span ', $cmname);
                    $cmname = str_replace('</div>', '</span>', $cmname);
                    $html_resource = $cmname . $mod->afterlink;
                }
                else {
                    $html_resource = '';
                }

                // If there is content but NO link (eg label), then display the
                // content here (BEFORE any icons). In this case cons must be
                // displayed after the content so that it makes more sense visually
                // and for accessibility reasons, e.g. if you have a one-line label
                // it should work similarly (at least in terms of ordering) to an
                // activity.
                $contentpart = $this->courserenderer->course_section_cm_text($mod);

                $url = $mod->url;
                if (!empty($url)) {
                    // If there is content AND a link, then display the content here
                    // (AFTER any icons). Otherwise it was displayed before
                    $html_resource .= $contentpart;
                }

                $availability_text = trim($this->courserenderer->course_section_cm_availability($mod));

                if (!empty($availability_text)) {
                    $unique_id = 'format_onetopic_winfo_' . time() . '-' . rand(0, 1000);
                    $html_resource .= '<img src="' . $this->output->pix_url('a/help') . '" class="iconhelp" alt="" onclick="M.course.format.show_info(\'' . $unique_id . '\')" />';

                    $html_more .= '<div id="' . $unique_id . '" class="availability_info_box" style="display: none;">' . $availability_text . '</div>';
                }

                //Replace the link in pattern: [[resource name]]
                $obj_replace->_string_replace = $html_resource;
                $obj_replace->_string_search = $instancename;

                $new_summary = preg_replace_callback("/(\[\[)(([<][^>]*>)*)((" . preg_quote($obj_replace->_string_search, '/') . ")(:?))([^\]]*)\]\]/i", array($obj_replace, "replace_tag_in_expresion"), $summary); 

                if ($new_summary != $summary) {
                    unset($this->_format_data->mods[$modnumber]);
                }

                $summary = $new_summary;
            }

        }

        if ($this->_course->templatetopic_icons == 0) {
            $summary = '<span class="onetopic_hideicons">' . $summary . '</span>';
        }

        return $summary . $html_more;

    }


    /**
     * Renders HTML to display a list of course modules in a course section
     * Also displays "move here" controls in Javascript-disabled mode
     *
     * This function calls {@link core_course_renderer::course_section_cm()}
     *
     * @param stdClass $course course object
     * @param int|stdClass|section_info $section relative section number or section object
     * @param int $sectionreturn section number to return to
     * @param array $displayoptions
     * @return void
     */
    public function custom_course_section_cm_list($course, $section, $sectionreturn = null, $displayoptions = array()) {
        global $USER;

        $output = '';
        $modinfo = $this->_format_data->modinfo;

        if (is_object($section)) {
            $section = $modinfo->get_section_info($section->section);
        } else {
            $section = $modinfo->get_section_info($section);
        }
        $completioninfo = new completion_info($course);

        // check if we are currently in the process of moving a module with JavaScript disabled
        $ismoving = $this->page->user_is_editing() && ismoving($course->id);
        if ($ismoving) {
            $movingpix = new pix_icon('movehere', get_string('movehere'), 'moodle', array('class' => 'movetarget'));
            $strmovefull = strip_tags(get_string("movefull", "", "'$USER->activitycopyname'"));
        }

        // Get the list of modules visible to user (excluding the module being moved if there is one)
        $moduleshtml = array();
        if (!empty($modinfo->sections[$section->section])) {
            foreach ($modinfo->sections[$section->section] as $modnumber) {

                //Custom modification in order to hide resources if they are shown in summary
                if (!$this->courserenderer->page->user_is_editing() && !isset($this->_format_data->mods[$modnumber])) {
                    continue;
                }
                //End of custom modification

                $mod = $modinfo->cms[$modnumber];

                if ($ismoving and $mod->id == $USER->activitycopy) {
                    // do not display moving mod
                    continue;
                }

                if ($modulehtml = $this->courserenderer->course_section_cm_list_item($course,
                        $completioninfo, $mod, $sectionreturn, $displayoptions)) {
                    $moduleshtml[$modnumber] = $modulehtml;
                }
            }
        }

        $sectionoutput = '';
        if (!empty($moduleshtml) || $ismoving) {
            foreach ($moduleshtml as $modnumber => $modulehtml) {
                if ($ismoving) {
                    $movingurl = new moodle_url('/course/mod.php', array('moveto' => $modnumber, 'sesskey' => sesskey()));
                    $sectionoutput .= html_writer::tag('li',
                            html_writer::link($movingurl, $this->courserenderer->output->render($movingpix), array('title' => $strmovefull)),
                            array('class' => 'movehere'));
                }

                $sectionoutput .= $modulehtml;
            }

            if ($ismoving) {
                $movingurl = new moodle_url('/course/mod.php', array('movetosection' => $section->id, 'sesskey' => sesskey()));
                $sectionoutput .= html_writer::tag('li',
                        html_writer::link($movingurl, $this->courserenderer->output->render($movingpix), array('title' => $strmovefull)),
                        array('class' => 'movehere'));
            }
        }

        // Always output the section module list.
        $output .= html_writer::tag('ul', $sectionoutput, array('class' => 'section img-text'));

        return $output;
    }

}
