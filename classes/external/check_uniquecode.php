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

/**
 * External function to check if a uniquecode is available.
 *
 * @package    format_onetopic
 * @copyright  2026 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class check_uniquecode extends external_api {
    /**
     * Parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'uniquecode' => new external_value(PARAM_ALPHANUMEXT, 'The unique code to check', VALUE_REQUIRED),
        ]);
    }

    /**
     * Check if a uniquecode is available.
     *
     * @param string $uniquecode The unique code to check.
     * @return array
     */
    public static function execute(string $uniquecode): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), ['uniquecode' => $uniquecode]);
        $uniquecode = $params['uniquecode'];

        $context = \context_system::instance();
        self::validate_context($context);

        $available = !$DB->record_exists('format_onetopic_appearances', ['uniquecode' => $uniquecode]);

        return [
            'available' => $available,
        ];
    }

    /**
     * Return.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'available' => new external_value(PARAM_BOOL, 'Whether the unique code is available'),
        ]);
    }
}
