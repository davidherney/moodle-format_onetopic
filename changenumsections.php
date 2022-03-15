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
 * This script allows the number of sections in a course to be increased or decreased.
 *
 * This script is based in the original feature created by Dan Poltawski.
 *
 * @package   format_onetopic
 * @copyright 2021 David Herney Bernal - cirano
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/course/lib.php');

$courseid = required_param('courseid', PARAM_INT);
$increase = optional_param('increase', null, PARAM_BOOL);
$insertsection = optional_param('insertsection', null, PARAM_INT); // Insert section at position; 0 means at the end.
$numsections = optional_param('numsections', 1, PARAM_INT);        // Number of sections to insert.
$returnurl = optional_param('returnurl', null, PARAM_LOCALURL);    // Where to return to after the action.
$sectionreturn = optional_param('sectionreturn', null, PARAM_INT); // Section to return to, ignored if $returnurl is specified.
$aschild = optional_param('aschild', 0, PARAM_BOOL);               // It is created as child.

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$courseformatoptions = course_get_format($course)->get_format_options();

$PAGE->set_url('/course/format/onetopic/changenumsections.php', array('courseid' => $courseid));
// Authorisation checks.
require_login($course);
require_capability('moodle/course:update', context_course::instance($course->id));
require_sesskey();

$desirednumsections = 0;
$courseformat = course_get_format($course);
$lastsectionnumber = $courseformat->get_last_section_number();
$maxsections = $courseformat->get_max_sections();

if (isset($courseformatoptions['numsections']) && $increase !== null) {
    $desirednumsections = $courseformatoptions['numsections'] + 1;
} else if (course_get_format($course)->uses_sections() && $insertsection !== null) {
    // Count the sections in the course.
    $desirednumsections = $lastsectionnumber + $numsections;
}

if ($desirednumsections > $maxsections) {
    // Increase in number of sections is not allowed.
    \core\notification::warning(get_string('maxsectionslimit', 'moodle', $maxsections));
    $increase = null;
    $insertsection = null;
    $numsections = 0;

    if (!$returnurl) {
        $returnurl = course_get_url($course);
    }
}

if (isset($courseformatoptions['numsections']) && $increase !== null) {
    if ($increase) {
        // Add an additional section.
        $courseformatoptions['numsections']++;
        course_create_sections_if_missing($course, $courseformatoptions['numsections']);
    } else {
        // Remove a section.
        $courseformatoptions['numsections']--;
    }

    // Don't go less than 0, intentionally redirect silently (for the case of
    // double clicks).
    if ($courseformatoptions['numsections'] >= 0) {
        update_course((object)array('id' => $course->id,
            'numsections' => $courseformatoptions['numsections']));
    }
    if (!$returnurl) {
        $returnurl = course_get_url($course);
    }

} else if (course_get_format($course)->uses_sections() && $insertsection !== null) {
    if ($insertsection) {
        // Inserting sections at any position except in the very end requires capability to move sections.
        require_capability('moodle/course:movesections', context_course::instance($course->id));
    }
    $sections = [];
    for ($i = 0; $i < max($numsections, 1); $i ++) {
        $section = course_create_section($course, $insertsection);
        $sections[] = $section;

        if ($aschild) {
            // Set level to one because it is a child tab.
            $courseformat->update_section_format_options(array('level' => 1, 'id' => $section->id));
        }
    }
    if (!$returnurl) {
        $returnurl = course_get_url($course, $sections[0]->section,
            ($sectionreturn !== null) ? ['sr' => $sectionreturn] : []);
    }
}

// Redirect to where we were..
$returnurl->set_anchor('tabs-tree-start');
redirect($returnurl);
