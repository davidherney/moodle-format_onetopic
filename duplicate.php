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
 * Duplicate resources on a section as a new section
 *
 * @since 2.8
 * @package contribution
 * @copyright 2015 David Herney Bernal - cirano
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->dirroot.'/course/lib.php');

$courseid = required_param('courseid', PARAM_INT);
$section = required_param('section', PARAM_INT);
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

$PAGE->set_url('/course/format/onetopic/duplicate.php', array('courseid' => $courseid, 'section' => $section));

// Authorization checks.
require_login($course);
$context = context_course::instance($course->id);
require_capability('moodle/course:update', $context);
require_capability('moodle/course:manageactivities', $context);
require_sesskey();

$course = course_get_format($course)->get_course();
$modinfo = get_fast_modinfo($course);
$sectioninfo = $modinfo->get_section_info($section);
$context = context_course::instance($course->id);
$num_newsection = null;

$PAGE->set_pagelayout('course');
$PAGE->set_heading($course->fullname);

$PAGE->set_title(get_string('coursetitle', 'moodle', array('course' => $course->fullname)));

echo $OUTPUT->header();

if (!empty($sectioninfo)) {

    $pbar = new progress_bar('onetopic_duplicate_bar', 500, true);
    $pbar->update_full(1, get_string('duplicating', 'format_onetopic'));

    $course_format = course_get_format($course);

    $last_section_num = $DB->get_field('course_sections', 'MAX(section)', array('course' => $courseid), MUST_EXIST);

    //$courseformatoptions = $course_format->get_format_options();
    //$courseformatoptions['numsections']++;
    $num_newsection = $last_section_num + 1;

    if (!$course_format->update_course_format_options(array('numsections' => $num_newsection))) {
        print_error('cantcreatesection', 'error', null, $course->fullname);
        return;
    }

    $pbar->update_full(5, get_string('creating_section', 'format_onetopic'));

    //Assign same section info
    $data = new stdClass();
    $data->course = $sectioninfo->course;
    $data->section = $num_newsection;
    //The name is not duplicated
    //$data->name = $sectioninfo->name;
    $data->summary = $sectioninfo->summary;
    $data->summaryformat = $sectioninfo->summaryformat;
    $data->visible = $sectioninfo->visible;
    $data->availability = $sectioninfo->availability;

    $new_section_id = $DB->insert_record('course_sections', $data, true);

    $moved = move_section_to($course, $num_newsection, $section + 1);
    if ($moved) {
        $num_newsection = $section + 1;
    }

    $format_options = $course_format->get_format_options($section);
    if (is_array($format_options) && count($format_options) > 0) {
        $format_options['id'] = $new_section_id;
        $course_format->update_section_format_options($format_options);
    }

    // Trigger an event for course section update.
    $event = \core\event\course_section_updated::create(
            array(
                'objectid' => $new_section_id,
                'courseid' => $course->id,
                'context' => $context,
                'other' => array('sectionnum' => $num_newsection)
            )
        );
    $event->trigger();

    $pbar->update_full(10, get_string('rebuild_course_cache', 'format_onetopic'));
    //rebuild_course_cache($course->id, true);
    $new_sectioninfo = $modinfo->get_section_info($num_newsection);

    $modules = array();

    if (is_object($modinfo) && isset($modinfo->sections[$section])) {
        $section_mods = $modinfo->sections[$section];

        if (is_array($section_mods)) {

            $progress_bar_elements = count($section_mods);
            $data_progress = new stdClass();
            $data_progress->current = 0;
            $data_progress->size = $progress_bar_elements;
            $k = 0;
            $pbar->update_full(40, get_string('progress_counter', 'format_onetopic', $data_progress));
            foreach ($section_mods as $modnumber) {
                $k++;
                $mod = $modinfo->cms[$modnumber];
                $cm  = get_coursemodule_from_id('', $mod->id, 0, true, MUST_EXIST);

                $modcontext = context_module::instance($cm->id);
                if (has_capability('moodle/course:manageactivities', $modcontext)) {
                    // Duplicate the module.
                    $newcm = duplicate_module($course, $cm);

                    //Move new module to new section
                    if($newcm && is_object($newcm)) {
                        moveto_module($newcm, $new_sectioninfo);
                    }
                }
                $data_progress->current = $k;
                $percent = 40 + ($k / $progress_bar_elements) * 60;
                $pbar->update_full($percent, get_string('progress_counter', 'format_onetopic', $data_progress));
            }
        }
    }
    else {
        $pbar->update_full(100, get_string('progress_full', 'format_onetopic'));
    }

    $section_togo = $num_newsection;
}
else {
    $section_togo = $section;
    echo get_string('error_nosectioninfo', 'format_onetopic');
    echo $OUTPUT->continue_button(course_get_url($course, $section));
    echo $OUTPUT->footer();
}

echo $OUTPUT->continue_button(course_get_url($course, $num_newsection));
echo $OUTPUT->footer();

//Redirect to new section or to current section if the new was not created
//redirect(course_get_url($course, $num_newsection));
