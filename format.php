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
 * Display the whole course as "tabs".
 *
 * It is based of the "topics" format.
 *
 * @since 2.0
 * @package format_onetopic
 * @copyright 2012 David Herney Bernal - cirano
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/completionlib.php');

// Horrible backwards compatible parameter aliasing.
if ($topic = optional_param('topic', 0, PARAM_INT)) {
    $url = $PAGE->url;
    $url->param('section', $topic);
    debugging('Outdated topic param passed to course/view.php', DEBUG_DEVELOPER);
    redirect($url);
}
// End backwards-compatible aliasing.

$context = context_course::instance($course->id);
// Retrieve course format option fields and add them to the $course object.
$course = course_get_format($course)->get_course();

if (($marker >= 0) && has_capability('moodle/course:setcurrentsection', $context) && confirm_sesskey()) {
    $course->marker = $marker;
    course_set_marker($course->id, $marker);
}

// Onetopic format is always multipage.
$course->coursedisplay = COURSE_DISPLAY_MULTIPAGE;

$renderer = $PAGE->get_renderer('format_onetopic');

$disableajax = optional_param('onetopic_da', -1, PARAM_INT);

if (!isset($USER->onetopic_da)) {
    $USER->onetopic_da = [];
}

if ($disableajax !== -1) {
    if ($disableajax === 0) {
        $USER->onetopic_da[$course->id] = false;
    } else {
        $USER->onetopic_da[$course->id] = true;
    }
}

if (!is_null($displaysection)) {
    $format->set_sectionnum($displaysection);
}

$outputclass = $format->get_output_classname('content');
$widget = new $outputclass($format);
echo $renderer->render($widget);
