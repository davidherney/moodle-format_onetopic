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
 * Appearances management page for format_onetopic.
 *
 * @package    format_onetopic
 * @copyright  2026 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');

use core_reportbuilder\system_report_factory;
use format_onetopic\reportbuilder\local\systemreports\appearances;

require_login();

$context = context_system::instance();
require_capability('format/onetopic:editallappearances', $context);

$PAGE->set_url(new moodle_url('/course/format/onetopic/appearances.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_heading($SITE->fullname);
$PAGE->set_title(get_string('appearances', 'format_onetopic'));

$PAGE->requires->js_call_amd('format_onetopic/appearances', 'init');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('appearances', 'format_onetopic'));

// Buttons to create a new appearance.
$createsectionurl = new moodle_url('/course/format/onetopic/editappearance.php', ['type' => 'section']);
$createsubsectionurl = new moodle_url('/course/format/onetopic/editappearance.php', ['type' => 'subsection']);
echo html_writer::div(
    $OUTPUT->single_button($createsectionurl, get_string('createnewappearance_section', 'format_onetopic'), 'get')
    . $OUTPUT->single_button($createsubsectionurl, get_string('createnewappearance_subsection', 'format_onetopic'), 'get'),
    'mb-3 d-flex gap-2'
);

// Render the system report.
$report = system_report_factory::create(appearances::class, $context);
echo $report->output();

echo $OUTPUT->footer();
