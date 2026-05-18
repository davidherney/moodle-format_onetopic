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

declare(strict_types=1);

namespace format_onetopic\reportbuilder\local\entities;

use lang_string;
use stdClass;
use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\filters\date;
use core_reportbuilder\local\filters\select;
use core_reportbuilder\local\filters\text;
use core_reportbuilder\local\helpers\format;
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\report\filter;

/**
 * Appearance entity
 *
 * @package    format_onetopic
 * @copyright  2026 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class appearance extends base {
    /**
     * Database tables that this entity uses
     *
     * @return string[]
     */
    protected function get_default_tables(): array {
        return [
            'format_onetopic_appearances',
            'course',
        ];
    }

    /**
     * The default title for this entity
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('appearances', 'format_onetopic');
    }

    /**
     * Initialise the entity
     *
     * @return base
     */
    public function initialise(): base {
        $columns = $this->get_all_columns();
        foreach ($columns as $column) {
            $this->add_column($column);
        }

        // All the filters defined by the entity can also be used as conditions.
        $filters = $this->get_all_filters();
        foreach ($filters as $filter) {
            $this
                ->add_filter($filter)
                ->add_condition($filter);
        }

        return $this;
    }

    /**
     * Returns list of all available columns
     *
     * @return column[]
     */
    protected function get_all_columns(): array {
        $tablealias = $this->get_table_alias('format_onetopic_appearances');
        $coursealias = $this->get_table_alias('course');

        // Unique code column.
        $columns[] = (new column(
            'uniquecode',
            new lang_string('uniquecode', 'format_onetopic'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$tablealias}.uniquecode")
            ->set_is_sortable(true);

        // Name column.
        $columns[] = (new column(
            'name',
            new lang_string('name'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$tablealias}.name")
            ->set_is_sortable(true);

        // Type column.
        $columns[] = (new column(
            'type',
            new lang_string('type', 'format_onetopic'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$tablealias}.type")
            ->set_is_sortable(true)
            ->add_callback(static function (string $type): string {
                if ($type === 'subsection') {
                    return get_string('subsection', 'format_onetopic');
                }
                return get_string('section');
            });

        // Scope column.
        $columns[] = (new column(
            'scope',
            new lang_string('scope', 'format_onetopic'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$tablealias}.courseid")
            ->add_join($this->get_course_join())
            ->add_fields("{$coursealias}.fullname")
            ->set_is_sortable(true)
            ->add_callback(static function ($courseid, stdClass $row): string {
                if ($courseid == SITEID) {
                    return get_string('appearancescope_site', 'format_onetopic');
                }
                return $row->fullname ?? '';
            });

        // Times used column.
        $timesusedfield = "
            (SELECT COUNT(*)
               FROM {config_plugins} cp
              WHERE cp.plugin = 'format_onetopic'
                AND cp.name IN ('defaultsectionsappearance', 'defaultsubsectionsappearance')
                AND cp.value = {$tablealias}.uniquecode)
            +
            (SELECT COUNT(*)
               FROM {course_format_options} cfo1
              WHERE cfo1.format = 'onetopic'
                AND cfo1.name IN ('customappearancesection', 'customappearancesubsection')
                AND cfo1.sectionid = 0
                AND cfo1.value = {$tablealias}.uniquecode)
            +
            (SELECT COUNT(*)
               FROM {course_format_options} cfo2
              WHERE cfo2.format = 'onetopic'
                AND cfo2.name IN ('customappearancebysections', 'customappearancebysubsections')
                AND cfo2.sectionid > 0
                AND cfo2.value = {$tablealias}.uniquecode)
        ";
        $columns[] = (new column(
            'timesused',
            new lang_string('timesused', 'format_onetopic'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_field($timesusedfield, 'timesused')
            ->set_is_sortable(true);

        // Time created column.
        $columns[] = (new column(
            'timecreated',
            new lang_string('timecreated', 'core_reportbuilder'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_fields("{$tablealias}.timecreated")
            ->set_is_sortable(true)
            ->set_callback([format::class, 'userdate']);

        // Time modified column.
        $columns[] = (new column(
            'timemodified',
            new lang_string('timemodified', 'core_reportbuilder'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TIMESTAMP)
            ->add_fields("{$tablealias}.timemodified")
            ->set_is_sortable(true)
            ->set_callback([format::class, 'userdate']);

        return $columns;
    }

    /**
     * Return list of all available filters
     *
     * @return filter[]
     */
    protected function get_all_filters(): array {
        $tablealias = $this->get_table_alias('format_onetopic_appearances');

        // Name filter.
        $filters[] = (new filter(
            text::class,
            'name',
            new lang_string('name'),
            $this->get_entity_name(),
            "{$tablealias}.name"
        ))
            ->add_joins($this->get_joins());

        // Scope filter.
        $filters[] = (new filter(
            select::class,
            'scope',
            new lang_string('scope', 'format_onetopic'),
            $this->get_entity_name(),
            "{$tablealias}.courseid"
        ))
            ->add_joins($this->get_joins())
            ->set_options_callback(static function (): array {
                global $DB;

                $options = [SITEID => get_string('appearancescope_site', 'format_onetopic')];

                $courses = $DB->get_records_sql(
                    "SELECT DISTINCT c.id, c.fullname
                      FROM {course} c
                      JOIN {format_onetopic_appearances} a ON a.courseid = c.id
                     WHERE c.id <> :siteid",
                    ['siteid' => SITEID]
                );

                foreach ($courses as $course) {
                    $options[$course->id] = $course->fullname;
                }

                return $options;
            });

        // Time created filter.
        $filters[] = (new filter(
            date::class,
            'timecreated',
            new lang_string('timecreated', 'core_reportbuilder'),
            $this->get_entity_name(),
            "{$tablealias}.timecreated"
        ))
            ->add_joins($this->get_joins());

        return $filters;
    }

    /**
     * Return course join used by columns
     *
     * @return string
     */
    private function get_course_join(): string {
        $tablealias = $this->get_table_alias('format_onetopic_appearances');
        $coursealias = $this->get_table_alias('course');

        return "LEFT JOIN {course} {$coursealias} ON {$coursealias}.id = {$tablealias}.courseid";
    }
}
