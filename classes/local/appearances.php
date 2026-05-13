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
 * Class appearances
 *
 * @package    format_onetopic
 * @copyright  2026 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class appearances {
    /**
     * Get the list of available appearances for a specific section.
     *
     * @param bool $issubsection Whether the section is a subsection or not.
     * @return array List of available appearances.
     */
    public static function get_availables_appearances(?int $courseid, ?bool $issubsection = false): array {
        global $DB;

        $sql = "SELECT name, configdata FROM {format_onetopic_appearances} WHERE courseid = :siteid";

        $params = ['siteid' => SITEID];
        if ($courseid) {
            $sql .= " OR courseid = :courseid";
            $params['courseid'] = $courseid;
        }

        $records = $DB->get_records_sql($sql, $params);

        $appearances = [];
        foreach ($records as $record) {
            $configdata = json_decode($record->configdata);

            if (!is_object($configdata) || !property_exists($configdata, 'availablefor')) {
                continue;
            }

            $availablefor = $configdata->availablefor;
            if ($issubsection && in_array('subsection', $availablefor)) {
                $appearances[$record->name] = $record->name;
            } else if (!$issubsection && in_array('section', $availablefor)) {
                $appearances[$record->name] = $record->name;
            }
        }

        return $appearances;
    }
}
