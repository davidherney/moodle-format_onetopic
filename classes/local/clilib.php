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

namespace format_onetopic\local;

/**
 * Cli library implementation.
 *
 * @package   format_onetopic
 * @copyright 2025 David Herney - https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class clilib {

    /**
     * List styles that need to be migrated.
     *
     * This function retrieves styles from the database that are set for courses in the Onetopic format.
     * It checks for styles that are not yet migrated to the new format and lists them.
     *
     * @param int $limit The maximum number of records to retrieve.
     * @return void
     */
    public static function mstyles_list($limit = 100) {
        global $DB;

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

        $params = [];
        $rs = $DB->get_recordset_sql($sql, $params, 0, $limit);

        if (!$rs->valid()) {
            echo get_string('migratestylesnothing', 'format_onetopic') . "\n";
            $rs->close(); // Not going to iterate (but exit), close rs.
            return;
        }

        $sqlnew = "SELECT DISTINCT s.sectionid, c.id AS courseid
                        FROM {course} c
                        INNER JOIN {course_format_options} s ON s.courseid = c.id
                        WHERE c.format = 'onetopic' AND s.name = 'tabstyles'";
        $stylesnew = $DB->get_records_sql($sqlnew);

        $k = 0;
        foreach ($rs as $record) {

            if (isset($stylesnew[$record->sectionid])) {
                // This section already has new styles, skip it.
                continue;
            }

            $k++;
            echo "[$k] Course ID: {$record->courseid}, Section ID: {$record->sectionid}, " .
                    "Style: {$record->stylename}, Value: {$record->stylevalue}\n";
        }
        $rs->close();

    }

    /**
     * Migrate styles from old format to new format.
     *
     * This function retrieves styles from the database that are set for courses in the Onetopic format.
     * It migrates styles that are not yet in the new format to the new 'tabstyles' format.
     * It processes styles based on their names ('fontcolor', 'bgcolor', 'cssstyles') and inserts them into the new format.
     * @param int $limit The maximum number of records to migrate.
     * @return void
     */
    public static function mstyles_migrate($limit = 100) {
        global $DB;

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
        $params = [];
        $rs = $DB->get_recordset_sql($sql, $params, 0, $limit);
        if (!$rs->valid()) {
            echo get_string('migratestylesnothing', 'format_onetopic') . "\n";
            $rs->close(); // Not going to iterate (but exit), close rs.
            return;
        }

        $sqlnew = "SELECT DISTINCT s.sectionid, c.id AS courseid
                        FROM {course} c
                        INNER JOIN {course_format_options} s ON s.courseid = c.id
                        WHERE c.format = 'onetopic' AND s.name = 'tabstyles'";
        $stylesnew = $DB->get_records_sql($sqlnew);

        $sections = [];
        foreach ($rs as $style) {
            if (isset($stylesnew[$style->sectionid])) {
                // This section already has new styles, skip it.
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
        $rs->close();

        $k = 0;
        foreach ($sections as $sectionid => $section) {

            if (CLI_VERBOSE) {
                echo "Migrating style for Course ID: {$section->courseid}, Section ID: {$sectionid}\n";
            }

            $cssstyles = new \stdClass();
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
                echo "    No compatible styles found\n";
                continue;
            }

            $cssstyles->default = (object)$styleproperties;

            // Migrate the styles to the new format.
            $newstyle = new \stdClass();
            $newstyle->courseid = $section->courseid;
            $newstyle->format = 'onetopic';
            $newstyle->sectionid = $sectionid;
            $newstyle->name = 'tabstyles';
            $newstyle->value = json_encode($cssstyles);

            $DB->insert_record('course_format_options', $newstyle);
            echo ".";
            $k++;
        }

        echo "\n    Total styles migrated: $k\n";
    }
}
