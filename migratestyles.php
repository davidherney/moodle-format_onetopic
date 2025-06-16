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
 * Page to migrate styles from old format to new format in the Onetopic course format.
 *
 * @package    format_onetopic
 * @copyright  2025 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');

$tomigrate = optional_param('migrate', 0, PARAM_BOOL);
$limit = optional_param('limit', 100, PARAM_INT);
$onlytochange = optional_param('tochange', 0, PARAM_BOOL);

require_login();

$context = context_system::instance();
require_capability('moodle/site:config', $context);

$url = new moodle_url('/course/format/onetopic/migratestyles.php', []);
$PAGE->set_url($url);
$PAGE->set_context($context);

$PAGE->set_heading($SITE->fullname);
echo $OUTPUT->header();

$sql = "SELECT s.id, c.id AS courseid, c.fullname,
            cs.name AS sectionname, cs.section,
            s.sectionid, s.value AS stylevalue, s.name AS stylename
        FROM {course} c
        INNER JOIN {course_format_options} s ON s.courseid = c.id
        INNER JOIN {course_sections} cs ON cs.course = c.id AND cs.id = s.sectionid
        WHERE c.format = 'onetopic'
                AND s.name IN ('fontcolor', 'bgcolor', 'cssstyles')
                AND (s.value IS NOT NULL AND s.value != '')
        ORDER BY c.id";

// I multiply the limit by three because it would be the maximum amount of data that would have to be brought.
// Considering the three style variables.
$customstyles = $DB->get_records_sql($sql, [], 0, $limit * 3);

$sqlnew = "SELECT DISTINCT s.sectionid, c.id AS courseid
        FROM {course} c
        INNER JOIN {course_format_options} s ON s.courseid = c.id
        WHERE c.format = 'onetopic' AND s.name = 'tabstyles'";
$stylesnew = $DB->get_records_sql($sqlnew);

if ($tomigrate) {
    require_sesskey();
    if (optional_param('confirm', 0, PARAM_BOOL) != 1) {
        $params = ['migrate' => 1, 'confirm' => 1, 'sesskey' => sesskey()];
        $confirmurl = new moodle_url('/course/format/onetopic/migratestyles.php', $params);
        echo $OUTPUT->confirm(
            get_string('migratestylesconfirm', 'format_onetopic'),
            $confirmurl,
            new moodle_url('/course/format/onetopic/migratestyles.php')
        );
        echo $OUTPUT->footer();
        exit;
    }

    // Use extra memory.
    raise_memory_limit(MEMORY_EXTRA);

    $sections = [];
    foreach ($customstyles as $style) {
        if (isset($stylesnew[$style->sectionid])) {
            // If the style already exists, skip it.
            continue;
        }

        if (!isset($sections[$style->sectionid])) {
            $sections[$style->sectionid] = (object)[
                'courseid' => $style->courseid,
                'styles' => [],
            ];
        }

        $sections[$style->sectionid]->styles[] = (object)[
            'name' => $style->stylename,
            'value' => $style->stylevalue,
        ];
    }

    foreach ($sections as $sectionid => $section) {
        $cssstyles = new stdClass();

        $styleproperties = [];
        foreach ($section->styles as $style) {
            // Prepare the styles based on their names.
            switch ($style->name) {
                case 'fontcolor':
                    $styleproperties['color'] = $style->value;
                    break;
                case 'bgcolor':
                    $styleproperties['background-color'] = $style->value;
                    break;
                case 'cssstyles':
                    $styleproperties['others'] = $style->value;
                    break;
            }
        }

        if (empty($styleproperties)) {
            // If no compatible styles are defined, skip this section.
            continue;
        }

        $cssstyles->default = (object)$styleproperties;

        // Migrate the styles to the new format.
        $newstyle = new stdClass();
        $newstyle->courseid = $section->courseid;
        $newstyle->format = 'onetopic';
        $newstyle->sectionid = $sectionid;
        $newstyle->name = 'tabstyles';
        $newstyle->value = json_encode($cssstyles);

        $DB->insert_record('course_format_options', $newstyle);
    }

    echo $OUTPUT->notification(get_string('migratestylesdone', 'format_onetopic'), 'success');
    echo $OUTPUT->continue_button(new moodle_url('/course/format/onetopic/migratestyles.php'));
    echo $OUTPUT->footer();
}

echo $OUTPUT->heading(get_string('migratestyles', 'format_onetopic'));

if (empty($customstyles)) {
    echo $OUTPUT->notification(get_string('nomigratestyles', 'format_onetopic'), 'info');
} else {
    $tomigrate = false;

    $table = new html_table();
    $table->head = [
        get_string('course'),
        get_string('section'),
        get_string('migratecssstyles', 'format_onetopic'),
        get_string('migratewillbemigrated', 'format_onetopic'),
    ];

    $sections = [];
    foreach ($customstyles as $style) {
        if (!isset($sections[$style->sectionid])) {
            $sections[$style->sectionid] = (object)[
                'courseid' => $style->courseid,
                'coursename' => $style->fullname,
                'sectioname' => empty($style->sectionname) ? get_string('sectionname', 'format_onetopic') . ' ' . $style->section
                                                            : $style->sectionname,
                'styles' => [],
            ];
        }

        $sections[$style->sectionid]->styles[] = (object)[
            'name' => $style->stylename,
            'value' => $style->stylevalue,
        ];
    }

    $k = 0;
    foreach ($sections as $sectionid => $section) {


        $sectionlink = new moodle_url('/course/view.php', ['id' => $section->courseid, 'sectionid' => $sectionid]);
        $sectionlink = html_writer::link($sectionlink, format_string($section->sectioname), ['target' => '_blank']);

        $cssstyles = [];
        foreach ($section->styles as $style) {
            $cssstyles[] = $style->name . ': ' . $style->value;
        }

        $newdefined = isset($stylesnew[$sectionid]);

        if (!$newdefined) {
            $tomigrate = true;
        } else if ($onlytochange) {
            continue; // Skip sections that already have the new styles defined.
        }

        $k++;

        if ($k > $limit) {
            echo $OUTPUT->notification(get_string('migratestyleslimit', 'format_onetopic', $limit), 'warning');
            break; // Limit the number of displayed sections to avoid performance issues.
        }

        $table->data[] = [
            $section->coursename,
            $sectionlink,
            implode(', ', $cssstyles),
            $newdefined ? get_string('no') : get_string('yes'),
        ];

    }

    echo html_writer::table($table);

    echo html_writer::start_div('migrate-btnbox');
    if ($tomigrate) {
        $sesskey = sesskey();
        $migrateurl = new moodle_url('/course/format/onetopic/migratestyles.php', ['migrate' => 1, 'sesskey' => $sesskey]);
        echo html_writer::link(
            $migrateurl,
            get_string('migratestylesaction', 'format_onetopic'),
            ['class' => 'btn btn-primary']
        );
    } else {
        echo $OUTPUT->notification(get_string('migratestylesnothing', 'format_onetopic'), 'info');
    }

    if ($onlytochange) {
        $allstyleslink = new moodle_url('/course/format/onetopic/migratestyles.php', ['limit' => $limit]);
        echo html_writer::link(
            $allstyleslink,
            get_string('migratestylesall', 'format_onetopic'),
            ['class' => 'btn btn-secondary']
        );
    } else {
        $onlytochangelink = new moodle_url('/course/format/onetopic/migratestyles.php', ['tochange' => 1, 'limit' => $limit]);
        echo html_writer::link(
            $onlytochangelink,
            get_string('migratestylesonlytochange', 'format_onetopic'),
            ['class' => 'btn btn-secondary']
        );
    }
    echo html_writer::end_div();
}

echo $OUTPUT->footer();
