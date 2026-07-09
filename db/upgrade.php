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
 * Upgrade scripts for course format "onetopic"
 *
 * @package   format_onetopic
 * @copyright 2018 David Herney Bernal - cirano
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade script for format_onetopic
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_format_onetopic_upgrade($oldversion) {
    global $DB;

    if ($oldversion < 2025051304) {
        // Ensure table exists (may not have been created if upgrade skipped).
        $table = new xmldb_table('format_onetopic_appearances');
        if (!$DB->get_manager()->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('uniquecode', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
            $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
            $table->add_field('type', XMLDB_TYPE_CHAR, '15', null, XMLDB_NOTNULL, null, 'section');
            $table->add_field('configdata', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('resourcelayout', XMLDB_TYPE_CHAR, '15', null, null, null, null);
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

            // Adding keys to table format_onetopic_appearances.
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
            $table->add_key('courseid', XMLDB_KEY_FOREIGN, ['courseid'], 'course', ['id']);

            // Adding indexes to table format_onetopic_appearances.
            $table->add_index('uniquecode_appearances', XMLDB_INDEX_UNIQUE, ['uniquecode']);
            $DB->get_manager()->create_table($table);
        }

        // Migrate site-level tabstyles to an appearance record.
        $tabstyles = get_config('format_onetopic', 'tabstyles');
        if (!empty($tabstyles)) {
            $now = time();
            $record = new \stdClass();
            $record->userid = get_admin()->id;
            $record->courseid = SITEID;
            $record->uniquecode = 'site_tabstyles';
            $record->name = get_string('tabstyles', 'format_onetopic');
            $record->type = 'section';
            $record->configdata = json_encode([
                'styles' => json_decode($tabstyles),
            ]);
            $record->timecreated = $now;
            $record->timemodified = $now;

            if (!$DB->record_exists('format_onetopic_appearances', ['uniquecode' => 'site_tabstyles'])) {
                $DB->insert_record('format_onetopic_appearances', $record);
                set_config('defaultsectionsappearance', 'site_tabstyles', 'format_onetopic');
            }
        }

        upgrade_plugin_savepoint(true, 2025051304, 'format', 'onetopic');
    }

    return true;
}
