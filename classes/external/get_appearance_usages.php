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

namespace format_onetopic\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_multiple_structure;
use core_external\external_value;
use core_external\external_warnings;

/**
 * External function to get appearance usages.
 *
 * @package    format_onetopic
 * @copyright  2026 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_appearance_usages extends external_api {
    /**
     * Parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'uniquecode' => new external_value(PARAM_ALPHANUMEXT, 'The appearance unique code', VALUE_REQUIRED),
        ]);
    }

    /**
     * Get where an appearance is used.
     *
     * @param string $uniquecode The appearance unique code.
     * @return array
     */
    public static function execute(string $uniquecode): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), ['uniquecode' => $uniquecode]);
        $uniquecode = $params['uniquecode'];

        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('format/onetopic:editallappearances', $context);

        $usages = [];

        // 1. Check config_plugins (site-level defaults).
        $siterecords = $DB->get_records_sql(
            "SELECT id, name, value
               FROM {config_plugins}
              WHERE plugin = 'format_onetopic'
                AND name IN ('defaultsectionsappearance', 'defaultsubsectionsappearance')
                AND value = ?",
            [$uniquecode]
        );

        foreach ($siterecords as $record) {
            $settingname = $record->name == 'defaultsectionsappearance'
                ? get_string('defaultsectionsappearance', 'format_onetopic')
                : get_string('defaultsubsectionsappearance', 'format_onetopic');

            $usages[] = [
                'type' => 'site',
                'name' => $settingname,
                'url' => (new \moodle_url('/admin/settings.php', ['section' => 'formatsettingonetopic']))->out(false),
            ];
        }

        // 2. Check course_format_options (course-level, sectionid = 0).
        $courserecords = $DB->get_records_sql(
            "SELECT cfo.id, cfo.courseid, cfo.name, c.fullname
               FROM {course_format_options} cfo
               JOIN {course} c ON c.id = cfo.courseid
              WHERE cfo.format = 'onetopic'
                AND cfo.name IN ('customappearancesection', 'customappearancesubsection')
                AND cfo.sectionid = 0
                AND cfo.value = ?",
            [$uniquecode]
        );

        foreach ($courserecords as $record) {
            $settingname = $record->name == 'customappearancesection'
                ? get_string('customappearance', 'format_onetopic')
                : get_string('customappearance', 'format_onetopic') . ' (' . get_string('subsection', 'format_onetopic') . ')';

            $usages[] = [
                'type' => 'course',
                'name' => $record->fullname . ': ' . $settingname,
                'url' => (new \moodle_url('/course/edit.php', ['id' => $record->courseid]))->out(false),
            ];
        }

        // 3. Check course_format_options (section-level, sectionid > 0).
        $sectionrecords = $DB->get_records_sql(
            "SELECT cfo.id, cfo.courseid, cfo.sectionid, cfo.name, c.fullname, cs.name AS sectionname, cs.section AS sectionnum
               FROM {course_format_options} cfo
               JOIN {course} c ON c.id = cfo.courseid
               JOIN {course_sections} cs ON cs.id = cfo.sectionid
              WHERE cfo.format = 'onetopic'
                AND cfo.name IN ('customappearancebysections', 'customappearancebysubsections')
                AND cfo.sectionid > 0
                AND cfo.value = ?",
            [$uniquecode]
        );

        foreach ($sectionrecords as $record) {
            $sectionname = !empty($record->sectionname) ? $record->sectionname : get_string('section') . ' ' . $record->sectionnum;

            $params = ['id' => $record->courseid, 'section' => $record->sectionnum];
            $usages[] = [
                'type' => 'section',
                'name' => $record->fullname . ': ' . $sectionname,
                'url' => (new \moodle_url('/course/view.php', $params))->out(false),
            ];
        }

        return [
            'usages' => $usages,
        ];
    }

    /**
     * Return.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'usages' => new external_multiple_structure(
                new external_single_structure([
                    'type' => new external_value(PARAM_ALPHA, 'The usage type: site, course, or section'),
                    'name' => new external_value(PARAM_RAW, 'The usage description'),
                    'url' => new external_value(PARAM_URL, 'The URL to the usage location'),
                ])
            ),
        ]);
    }
}
