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

namespace format_onetopic\reportbuilder\local\systemreports;

use core_reportbuilder\system_report;
use core_reportbuilder\local\entities\user;
use core_reportbuilder\local\report\action;
use format_onetopic\reportbuilder\local\entities\appearance;
use lang_string;
use moodle_url;
use pix_icon;

/**
 * Appearances system report class implementation
 *
 * @package    format_onetopic
 * @copyright  2026 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class appearances extends system_report {
    /**
     * Initialise report, we need to set the main table, load our entities and set columns/filters
     */
    protected function initialise(): void {
        // Our main entity, it contains all of the column definitions that we need.
        $entity = new appearance();
        $entityalias = $entity->get_table_alias('format_onetopic_appearances');

        $this->set_main_table('format_onetopic_appearances', $entityalias);
        $this->add_entity($entity);

        // Join user entity for the creator column.
        $userentity = new user();
        $useralias = $userentity->get_table_alias('user');
        $this->add_entity(
            $userentity->add_join("LEFT JOIN {user} {$useralias} ON {$useralias}.id = {$entityalias}.userid")
        );

        // Any columns required by actions should be defined here to ensure they're always available.
        $this->add_base_fields("{$entityalias}.id, {$entityalias}.uniquecode, {$entityalias}.name");

        // Now we can call our helper methods to add the content we want to include in the report.
        $this->add_columns();
        $this->add_filters();
        $this->add_actions();

        // Set if report can be downloaded.
        $this->set_downloadable(false);
    }

    /**
     * Validates access to view this report
     *
     * @return bool
     */
    protected function can_view(): bool {
        return has_capability('format/onetopic:editallappearances', \context_system::instance());
    }

    /**
     * Adds the columns we want to display in the report
     *
     * They are provided by the entities we previously added in the {@see initialise} method, referencing each by their
     * unique identifier. If custom columns are needed just for this report, they can be defined here.
     */
    protected function add_columns(): void {
        $this->add_column_from_entity('appearance:uniquecode');
        $this->add_column_from_entity('appearance:name');
        $this->add_column_from_entity('appearance:type');
        $this->add_column_from_entity('appearance:scope');
        $this->add_column_from_entity('user:fullname');
        $this->add_column_from_entity('appearance:timesused');
        $this->add_column_from_entity('appearance:timecreated');
        $this->add_column_from_entity('appearance:timemodified');

        // It's possible to set a default initial sort direction for one column.
        $this->set_initial_sort_column('appearance:name', SORT_ASC);

        // Set default no results notice.
        $this->set_default_no_results_notice(new lang_string('nocustomappearances', 'format_onetopic'));
    }

    /**
     * Adds the filters we want to display in the report
     *
     * They are all provided by the entities we previously added in the {@see initialise} method, referencing each by their
     * unique identifier
     */
    protected function add_filters(): void {
        $this->add_filters_from_entities([
            'appearance:name',
            'appearance:scope',
            'appearance:timecreated',
        ]);
    }

    /**
     * Add the system report actions
     */
    protected function add_actions(): void {
        // Edit action.
        $this->add_action((new action(
            new moodle_url('/course/format/onetopic/editappearance.php', ['id' => ':id']),
            new pix_icon('t/edit', '', 'core'),
            [],
            false,
            new lang_string('editappearance', 'format_onetopic')
        )));

        // Usages action.
        $this->add_action((new action(
            new moodle_url('#'),
            new pix_icon('i/report', '', 'core'),
            [
                'data-action' => 'appearance-usages',
                'data-appearance-uniquecode' => ':uniquecode',
                'data-appearance-name' => ':name',
            ],
            false,
            new lang_string('appearanceusagesbutton', 'format_onetopic')
        )));

        // Delete action.
        $this->add_action((new action(
            new moodle_url('#'),
            new pix_icon('t/delete', '', 'core'),
            [
                'class' => 'text-danger',
                'data-action' => 'appearance-delete',
                'data-appearance-id' => ':id',
                'data-appearance-name' => ':name',
            ],
            false,
            new lang_string('deleteappearance', 'format_onetopic')
        )));
    }
}
