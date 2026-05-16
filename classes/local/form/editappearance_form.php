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

namespace format_onetopic\local\form;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/adminlib.php');

/**
 * Form for editing an appearance.
 *
 * @package    format_onetopic
 * @copyright  2026 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class editappearance_form extends \moodleform {

    /**
     * Form definition.
     */
    protected function definition() {
        global $OUTPUT, $PAGE, $CFG;

        $mform = $this->_form;
        $data = $this->_customdata;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('hidden', 'type');
        $mform->setType('type', PARAM_ALPHA);

        // Type display (read-only).
        $typelabel = get_string('type_' . $data->type, 'format_onetopic');
        $mform->addElement('static', 'typedisplay', get_string('type', 'format_onetopic'), $typelabel);

        // Name field.
        $mform->addElement('text', 'name', get_string('name'), ['size' => 50]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required');

        // Unique code field.
        $mform->addElement('text', 'uniquecode', get_string('uniquecode', 'format_onetopic'), ['size' => 50]);
        $mform->setType('uniquecode', PARAM_ALPHANUMEXT);
        $mform->addHelpButton('uniquecode', 'uniquecode', 'format_onetopic');

        // Freeze uniquecode when editing an existing appearance.
        if (!empty($data->id)) {
            $mform->freeze('uniquecode');
        } else {
            $mform->addRule('uniquecode', get_string('required'), 'required');
            $PAGE->requires->js_call_amd('format_onetopic/editappearance', 'init');
        }

        // Styles editor based on type.
        if ($data->type === 'subsection') {
            \MoodleQuickForm::registerElementType(
                'subsectionstyles',
                $CFG->dirroot . '/course/format/onetopic/classes/local/formelement_subsectionstyles.php',
                'format_onetopic_subsectionstyles_form_element'
            );
            $mform->addElement('subsectionstyles', 'configdata',
                get_string('subsectionstyles', 'format_onetopic'));
        } else {
            \MoodleQuickForm::registerElementType(
                'tabstyles',
                $CFG->dirroot . '/course/format/onetopic/classes/local/formelement_tabstyles.php',
                'format_onetopic_tabstyles_form_element'
            );
            $mform->addElement('tabstyles', 'configdata', get_string('tabstyles', 'format_onetopic'), ['hidetoggle' => true]);
        }

        $this->add_action_buttons();
    }

    /**
     * Form validation.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        // Validate uniquecode only when creating (it is frozen when editing).
        if (empty($data['id'])) {
            // Validate uniquecode format: only letters, numbers, _ and -.
            if (!empty($data['uniquecode']) && !preg_match('/^[a-zA-Z0-9_-]+$/', $data['uniquecode'])) {
                $errors['uniquecode'] = get_string('uniquecodeinvalid', 'format_onetopic');
            }

            // Validate uniquecode is unique.
            if (!empty($data['uniquecode'])) {
                $existing = $DB->get_record('format_onetopic_appearances', ['uniquecode' => $data['uniquecode']]);
                if ($existing) {
                    $errors['uniquecode'] = get_string('uniquecodeexists', 'format_onetopic');
                }
            }
        }

        return $errors;
    }
}
