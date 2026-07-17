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
 * Edit or create an appearance for format_onetopic.
 *
 * @package    format_onetopic
 * @copyright  2026 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');
require_once($CFG->dirroot . '/course/format/onetopic/lib.php');

require_login();

$id = optional_param('id', 0, PARAM_INT);
$courseid = optional_param('courseid', SITEID, PARAM_INT);
$type = optional_param('type', 'section', PARAM_ALPHA);

// Determine context and capability based on courseid.
if ($courseid == SITEID) {
    $context = context_system::instance();
    require_capability('format/onetopic:editallappearances', $context);
    $returnurl = new moodle_url('/course/format/onetopic/appearances.php');
} else {
    $context = context_course::instance($courseid);
    require_capability('format/onetopic:editappearances', $context);
    $returnurl = new moodle_url('/course/view.php', ['id' => $courseid]);
}

// Load existing record if editing.
$appearance = null;
if ($id) {
    $appearance = $DB->get_record('format_onetopic_appearances', ['id' => $id], '*', MUST_EXIST);

    // Check that the user can edit this appearance.
    if ($appearance->courseid == SITEID) {
        require_capability('format/onetopic:editallappearances', context_system::instance());
    } else {
        require_capability('format/onetopic:editappearances', context_course::instance($appearance->courseid));
    }
    $courseid = $appearance->courseid;
    $type = $appearance->type;
}

// Validate type.
if (!in_array($type, ['section', 'subsection'])) {
    $type = 'section';
}

$PAGE->set_url(
    new moodle_url(
        '/course/format/onetopic/editappearance.php',
        ['id' => $id, 'courseid' => $courseid, 'type' => $type]
    )
);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_heading($SITE->fullname);

$title = $id ? get_string('editappearance', 'format_onetopic') : get_string('createnewappearance', 'format_onetopic');
$PAGE->set_title($title);

// Prepare form data.
$formdata = new stdClass();
$formdata->id = $id;
$formdata->courseid = $courseid;
$formdata->type = $type;
$formdata->resourcelayout = 'default';

if ($appearance) {
    $formdata->name = $appearance->name;
    $formdata->uniquecode = $appearance->uniquecode;
    $formdata->configdata = $appearance->configdata;
    if (in_array($appearance->resourcelayout, \format_onetopic::RESOURCESLAYOUTS)) {
        $formdata->resourcelayout = $appearance->resourcelayout;
    }

    $configdata = json_decode($appearance->configdata);
    // Pass only the styles part as the tabstyles value.
    if (is_object($configdata) && property_exists($configdata, 'styles')) {
        $formdata->configdata = is_object($configdata->styles) ? json_encode($configdata->styles) : '';
    }
}

$mform = new \format_onetopic\local\form\editappearance_form(null, $formdata);
$mform->set_data($formdata);

if ($mform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $mform->get_data()) {
    $now = time();

    // Parse the styles JSON from the tabstyles element.
    $styles = !empty($data->configdata) ? json_decode($data->configdata) : null;

    // Build the full configdata.
    $configdata = json_encode([
        'styles' => $styles,
    ]);

    if ($data->id) {
        // Update existing record.
        $record = $DB->get_record('format_onetopic_appearances', ['id' => $data->id], '*', MUST_EXIST);
        $record->name = $data->name;
        // Uniquecode and type are frozen when editing, so keep the existing values.
        $record->configdata = $configdata;
        $record->resourcelayout = $data->resourcelayout;
        $record->timemodified = $now;
        $DB->update_record('format_onetopic_appearances', $record);
    } else {
        // Create new record.
        $record = new stdClass();
        $record->userid = $USER->id;
        $record->courseid = $data->courseid;
        $record->name = $data->name;
        $record->uniquecode = $data->uniquecode;
        $record->type = $data->type;
        $record->configdata = $configdata;
        $record->resourcelayout = $data->resourcelayout;
        $record->timecreated = $now;
        $record->timemodified = $now;
        $DB->insert_record('format_onetopic_appearances', $record);
    }

    redirect($returnurl, get_string('changessaved'), null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();
echo $OUTPUT->heading($title);
$mform->display();
echo $OUTPUT->footer();
