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
use core_external\external_value;
use core_external\external_warnings;

/**
 * External function to delete an appearance.
 *
 * @package    format_onetopic
 * @copyright  2026 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delete_appearance extends external_api {
    /**
     * Parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'The appearance id to delete', VALUE_REQUIRED),
        ]);
    }

    /**
     * Delete an appearance and clean all references.
     *
     * @param int $id The appearance id.
     * @return array
     */
    public static function execute(int $id): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), ['id' => $id]);
        $id = $params['id'];

        $record = $DB->get_record('format_onetopic_appearances', ['id' => $id], '*', MUST_EXIST);

        if ($record->courseid == SITEID) {
            // Site level appearance, require 'editallappearances' capability.
            $context = \context_system::instance();
            self::validate_context($context);
            require_capability('format/onetopic:editallappearances', $context);
        } else {
            // Course level appearance, require 'editappearances' capability.
            // A user with 'editappearances' capability in the course context can delete courses appearances from other user.
            $context = \context_course::instance($record->courseid);
            self::validate_context($context);
            require_capability('format/onetopic:editappearances', $context);
        }

        $warnings = [];
        $result = false;

        try {
            $uniquecode = $record->uniquecode;

            // Delete the appearance record.
            $DB->delete_records('format_onetopic_appearances', ['id' => $id]);

            if ($record->courseid == SITEID) {
                // Clean references in config_plugins.
                $sitedefaults = $DB->get_records('config_plugins', ['plugin' => 'format_onetopic', 'value' => $uniquecode]);
                foreach ($sitedefaults as $default) {
                    if (in_array($default->name, ['defaultsectionsappearance'], true)) {
                        set_config('defaultsectionsappearance', '', 'format_onetopic');
                    } else if (in_array($default->name, ['defaultsubsectionsappearance'], true)) {
                        set_config('defaultsubsectionsappearance', '', 'format_onetopic');
                    }
                }
            }

            // Clean references in course_format_options (course level, sectionid = 0).
            $DB->execute(
                "UPDATE {course_format_options}
                    SET value = ''
                  WHERE format = 'onetopic'
                    AND name IN ('customappearancesection', 'customappearancesubsection')
                    AND sectionid = 0
                    AND value = ?",
                [$uniquecode]
            );

            // Clean references in course_format_options (section level, sectionid > 0).
            $DB->execute(
                "UPDATE {course_format_options}
                    SET value = ''
                  WHERE format = 'onetopic'
                    AND name IN ('customappearancebysections', 'customappearancebysubsections')
                    AND sectionid > 0
                    AND value = ?",
                [$uniquecode]
            );

            $result = true;
        } catch (\moodle_exception $e) {
            $warnings[] = [
                'item' => $id,
                'warningcode' => 'exception',
                'message' => $e->getMessage(),
            ];
        }

        return [
            'result' => $result,
            'warnings' => $warnings,
        ];
    }

    /**
     * Return.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'result' => new external_value(PARAM_BOOL, 'The processing result'),
            'warnings' => new external_warnings(),
        ]);
    }
}
