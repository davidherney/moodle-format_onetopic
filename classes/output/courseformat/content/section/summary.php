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
 * Contains the default section summary (used for multipage format).
 *
 * @package   format_onetopic
 * @copyright 2022 David Herney Bernal - cirano. https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_onetopic\output\courseformat\content\section;

use context_course;
use core\output\named_templatable;
use core_courseformat\base as course_format;
use core_courseformat\output\local\courseformat_named_templatable;
use core_courseformat\output\local\content\cm as cm_base;
use core_courseformat\output\local\content\section\summary as summary_base;
use renderable;
use section_info;
use stdClass;

/**
 * Base class to render a course section summary.
 *
 * @package   format_onetopic
 * @copyright 2022 David Herney Bernal - cirano. https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class summary extends summary_base {

    use courseformat_named_templatable;

    /** @var course_format the course format class */
    protected $format;

    /** @var section_info the course section class */
    private $section;

    /** @var renderer_base the renderer output class */
    private $output;

    /** @var string Text to search */
    public $tpl_string_search;

    /** @var string Text to replace */
    public $tpl_string_replace;

    /** @var string Temporal key */
    public $tpl_tag_string = '{label_tag_replace}';

    /**
     * Constructor.
     *
     * @param course_format $format the course format
     * @param section_info $section the section info
     */
    public function __construct(course_format $format, section_info $section) {
        $this->format = $format;
        $this->section = $section;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return array data context for a mustache template
     */
    public function export_for_template(\renderer_base $output): stdClass {

        $section = $this->section;
        $this->output = $output;

        $data = new stdClass();

        if ($section->uservisible || $section->visible) {
            $data->summarytext = $this->format_summary_text();
        }
        return $data;
    }

    /**
     * Generate html for a section summary text
     *
     * @return string HTML to output.
     */
    public function format_summary_text(): string {
        $section = $this->section;
        $course = $this->format->get_course();
        $context = context_course::instance($section->course);

        if ($course->templatetopic != \format_onetopic::TEMPLATETOPIC_NOT) {
            $section->summary = $this->replace_resources($section);
        }

        $summarytext = file_rewrite_pluginfile_urls($section->summary, 'pluginfile.php',
            $context->id, 'course', 'section', $section->id);

        $options = new stdClass();
        $options->noclean = true;
        $options->overflowdiv = true;
        return format_text($summarytext, $section->summaryformat, $options);
    }

    /**
     * Process the Onetopic summary template.
     *
     * @param section_info $section the section info
     * @return string HTML to output.
     */
    private function replace_resources(section_info $section) {

        global $CFG, $USER, $DB, $PAGE;

        static $initialised;

        static $groupbuttons;
        static $groupbuttonslink;
        static $strunreadpostsone;
        static $usetracking;
        static $groupings;

        $course = $this->format->get_course();

        $completioninfo = new \completion_info($course);

        if (!isset($initialised)) {
            $groupbuttons     = ($course->groupmode || (!$course->groupmodeforce));
            $groupbuttonslink = (!$course->groupmodeforce);
            include_once($CFG->dirroot . '/mod/forum/lib.php');
            if ($usetracking = forum_tp_can_track_forums()) {
                $strunreadpostsone = get_string('unreadpostsone', 'forum');
            }
            $initialised = true;
        }

        $labelformatoptions = new \stdclass();
        $labelformatoptions->noclean = true;

        // Casting $course->modinfo to string prevents one notice when the field is null.
        $modinfo = $this->format->get_modinfo();

        $summary = $section->summary;

        $htmlresource = '';
        $htmlmore     = '';

        if (!empty($section->sequence)) {
            $sectionmods = explode(",", $section->sequence);

            $completioninfo = new \completion_info($course);

            foreach ($sectionmods as $modnumber) {

                if (empty($modinfo->cms[$modnumber])) {
                    continue;
                }

                $mod = $modinfo->cms[$modnumber];

                if ($mod->modname == "label") {
                    continue;
                }

                $instancename = format_string($modinfo->cms[$modnumber]->name, true, $course->id);

                $displayoptions = [];
                $cm = new cm_base($this->format, $section, $mod, $displayoptions);

                // Display the link to the module (or do nothing if module has no url).
                $cmdata = $cm->export_for_template($this->output);
                $cmdata->tplmode = true;
                $cmdata->modinline = true;
                $cmdata->hideicons = $course->templatetopic_icons == 0;

                $url = $mod->url;
                if (!empty($url)) {
                    // If there is content but NO link (eg label), then display the
                    // content here (BEFORE any icons). In this case cons must be
                    // displayed after the content so that it makes more sense visually
                    // and for accessibility reasons, e.g. if you have a one-line label
                    // it should work similarly (at least in terms of ordering) to an
                    // activity.
                    $renderer = $this->format->get_renderer($PAGE);
                    $htmlresource = $renderer->render_from_template('format_onetopic/courseformat/content/cminline', $cmdata);
                }

                if ($completioninfo->is_enabled($mod) !== COMPLETION_TRACKING_NONE) {
                    $completion = $DB->get_record('course_modules_completion',
                                                array('coursemoduleid' => $mod->id, 'userid' => $USER->id, 'completionstate' => 1));

                    $showcompletionconditions = $course->showcompletionconditions == COMPLETION_SHOW_CONDITIONS;

                    if ($completion) {
                        $completedclass = 'complete';
                    } else {
                        $completedclass = 'incomplete';
                    }

                    if ($showcompletionconditions) {
                        $completedclass .= ' hascompletionconditions';
                    }

                    $htmlresource = '<completion class="completiontag ' . $completedclass . '">' . $htmlresource;

                    if ($showcompletionconditions) {

                        // Fetch activity dates.
                        $activitydates = [];
                        if ($course->showactivitydates) {
                            $activitydates = \core\activity_dates::get_dates_for_module($mod, $USER->id);
                        }

                        // Fetch completion details.
                        $completiondetails = \core_completion\cm_completion_details::get_instance($mod,
                                                                                                    $USER->id,
                                                                                                    $showcompletionconditions);

                        $completionhtml = $this->output->activity_information($mod, $completiondetails, $activitydates);

                        $htmlresource .= '<span class="showcompletionconditions">';
                        $htmlresource .= $this->output->image_icon('i/info', '');
                        $htmlresource .= $completionhtml;
                        $htmlresource .= '</span>';
                    }

                    $htmlresource .= '</completion>';
                }

                /*$availabilitytext = trim($this->courserenderer->course_section_cm_availability($mod));

                if (!empty($availabilitytext)) {
                    $uniqueid = 'format_onetopic_winfo_' . time() . '-' . rand(0, 1000);
                    $htmlresource .= '<span class="iconhelp" data-infoid="' . $uniqueid . '">' .
                                        $this->output->pix_icon('e/help', get_string('help')) .
                                     '</span>';

                    $htmlmore .= '<div id="' . $uniqueid . '" class="availability_info_box" style="display: none;">' .
                        $availabilitytext . '</div>';

                    $this->showyuidialogue = true;
                }*/

                // Replace the link in pattern: [[resource name]].
                $this->tpl_string_replace = $htmlresource;
                $this->tpl_string_search = $instancename;

                $newsummary = preg_replace_callback("/(\[\[)(([<][^>]*>)*)((" . preg_quote($this->tpl_string_search, '/') .
                    ")(:?))([^\]]*)\]\]/i", array($this, "replace_tag_in_expresion"), $summary);

                if ($newsummary != $summary) {
                    $this->format->tplcmsused[] = $modnumber;
                }

                $summary = $newsummary;

            }
        }

        return $summary . $htmlmore;

    }

    /**
     * Replace a tag into the summary.
     *
     * @param array $match
     * @return array
     */
    public function replace_tag_in_expresion ($match) {

        $term = $match[0];
        $term = str_replace("[[", '', $term);
        $term = str_replace("]]", '', $term);

        $text = strip_tags($term);

        if (strpos($text, ':') > -1) {

            $pattern = '/([^:])+:/i';
            $text = preg_replace($pattern, '', $text);

            // Change text for alternative text.
            $newreplace = str_replace($this->tpl_string_search, $text, $this->tpl_string_replace);

            // Posible html tags position.
            $pattern = '/([>][^<]*:[^<]*[<])+/i';
            $term = preg_replace($pattern, '><:><', $term);

            $pattern = '/([>][^<]*:[^<]*$)+/i';
            $term = preg_replace($pattern, '><:>', $term);

            $pattern = '/(^[^<]*:[^<]*[<])+/i';
            $term = preg_replace($pattern, '<:><', $term);

            $pattern = '/(^[^<]*:[^<]*$)/i';
            $term = preg_replace($pattern, '<:>', $term);

            $pattern = '/([>][^<^:]*[<])+/i';
            $term = preg_replace($pattern, '><', $term);

            $term = str_replace('<:>', $newreplace, $term);
        } else {
            // Change tag for resource or mod name.
            $newreplace = str_replace($this->tpl_tag_string, $this->tpl_string_search, $this->tpl_string_replace);
            $term = str_replace($this->tpl_string_search, $newreplace, $term);
        }
        return $term;
    }
}
