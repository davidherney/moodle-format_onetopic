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
 * External functions and service definitions for format_onetopic.
 *
 * @package    format_onetopic
 * @copyright  2026 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'format_onetopic_delete_appearance' => [
        'classname' => 'format_onetopic\external\delete_appearance',
        'description' => 'Delete an appearance and clean all references.',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'format/onetopic:editallappearances',
    ],
    'format_onetopic_get_appearance_usages' => [
        'classname' => 'format_onetopic\external\get_appearance_usages',
        'description' => 'Get where an appearance is used.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'format/onetopic:editallappearances',
    ],
    'format_onetopic_check_uniquecode' => [
        'classname' => 'format_onetopic\external\check_uniquecode',
        'description' => 'Check if a uniquecode is available.',
        'type' => 'read',
        'ajax' => true,
        'loginrequired' => true,
    ],
];
