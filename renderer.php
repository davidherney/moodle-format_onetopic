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
     * Generate the edit controls of a section
     *
     * @param stdClass $course The course entry from DB
     * @param stdClass $section The course_section entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return array of links with edit controls
     */
    protected function section_edit_controls($course, $section, $onsectionpage = false) {
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
        if (!$isstealth && has_capability('moodle/course:setcurrentsection', $coursecontext)) {
            if ($course->marker == $section->section) {  // Show the "light globe" on/off.
                $url->param('marker', 0);
                $controls[] = html_writer::link($url,
                                    html_writer::empty_tag('img', array('src' => $this->output->pix_url('i/marked'),
                                        'class' => 'icon ', 'alt' => get_string('markedthistopic'))),
                                    array('title' => get_string('markedthistopic'), 'class' => 'editing_highlight'));
            } else {
                $url->param('marker', $section->section);
                $controls[] = html_writer::link($url,
                                html_writer::empty_tag('img', array('src' => $this->output->pix_url('i/marker'),
                                    'class' => 'icon', 'alt' => get_string('markthistopic'))),
                                array('title' => get_string('markthistopic'), 'class' => 'editing_highlight'));
            }
        }

        return array_merge($controls, parent::section_edit_controls($course, $section, $onsectionpage));
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

        // General section if non-empty and course_display is multiple.
        if ($course->realcoursedisplay == COURSE_DISPLAY_MULTIPAGE) {
            $thissection = $sections[0];
            if ((($thissection->visible && $thissection->available) || $canviewhidden) && ($thissection->summary || $thissection->sequence || $PAGE->user_is_editing())) {
                echo $this->start_section_list();
                echo $this->section_header($thissection, $course, true);
                echo $this->courserenderer->course_section_cm_list($course, $thissection, $displaysection);
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

                echo $this->courserenderer->course_section_cm_list($course, $thissection, $displaysection);
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

        $o.= $this->output->heading($this->section_title($section, $course), 3, 'sectionname' . $classes);
    
        $o.= html_writer::start_tag('div', array('class' => 'summary'));
        $o.= $this->format_summary_text($section);
    
        $context = context_course::instance($course->id);
        if ($PAGE->user_is_editing() && has_capability('moodle/course:update', $context)) {
            $url = new moodle_url('/course/editsection.php', array('id'=>$section->id, 'sr'=>$sectionreturn));
            $o.= html_writer::link($url,
                html_writer::empty_tag('img', array('src' => $this->output->pix_url('i/settings'),
                    'class' => 'iconsmall edit', 'alt' => get_string('edit'))),
                array('title' => get_string('editsummary')));
        }
        $o.= html_writer::end_tag('div');
    
        $o .= $this->section_availability_message($section,
            has_capability('moodle/course:viewhiddensections', $context));
    
        return $o;
    }

    /**
     * Generate the content to displayed on the right part of a section
     * before course modules are included
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return string HTML to output.
     */
    protected function section_right_content($section, $course, $onsectionpage) {
        $o = $this->output->spacer();
    
        $controls = $this->section_edit_controls($course, $section, $onsectionpage);
        if (!empty($controls)) {
            $o = implode('<br />', $controls);
        }
    
        return $o;
    }

}
