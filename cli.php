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
 * This file contains cli commands for the Onetopic course format.
 *
 * @package    format_onetopic
 * @copyright  2025 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php'); // Cli only functions.
require_once(__DIR__ . '/classes/local/clilib.php'); // Local cli functions.

// Global options.
$hr = "----------------------------------------\n";

// Get cli options.
[$options, $unrecognized] = cli_get_params(
    [
        'help' => false,
        'mstyles' => false,
        'mslimit' => false,
        'verbose' => false,
    ],
    [
        'h'  => 'help',
        'ms' => 'mstyles',
        'msl' => 'mslimit',
        'v'  => 'verbose',
    ]
);

$any = false;
foreach ($options as $option) {
    if ($option) {
        $any = true;
        break;
    }
}

if (!$any) {
    $options['help'] = true;
}

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    echo get_string('cli_help', 'format_onetopic');
    die;
}

$cliverbose = $options['verbose'] ?? false;
define('CLI_VERBOSE', $cliverbose);

if ($options['mstyles']) {
    $action = $options['mstyles'];

    if ($action === true || !in_array($action, ['migrate', 'list'])) {
        $action = 'list';
    }

    $limit = isset($options['mslimit']) && is_number($options['mslimit']) ? (int)$options['mslimit'] : 100;

    echo get_string('cli_migratestylesstarttitle', 'format_onetopic') . "\n";
    echo $hr;

    if ($action === 'migrate') {
        echo get_string('cli_migratestylesstart', 'format_onetopic') . "\n";
        \format_onetopic\local\clilib::mstyles_migrate($limit);
        echo get_string('cli_migratestylesend', 'format_onetopic') . "\n";
    } else {
         \format_onetopic\local\clilib::mstyles_list($limit);
    }
    echo $hr;

    die;
}
