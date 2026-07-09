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
 * Form element to display section content styles editor.
 *
 * @package   format_onetopic
 * @copyright 2026 David Herney - https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/form/textarea.php');
require_once($CFG->libdir . '/adminlib.php');

/**
 * Display a section content styles form field.
 *
 * This form element is used in subsection editing to allow setting
 * content styles (primarily resource layout) for the section.
 *
 * @package   format_onetopic
 * @copyright 2026 David Herney - https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_onetopic_sectionstyles_form_element extends MoodleQuickForm_textarea {
    /**
     * Constructor
     *
     * @param string $name Element name
     * @param mixed $label Label(s) for an element
     * @param mixed $attributes Either a typical HTML attribute string or an associative array.
     */
    public function __construct($name = null, $label = null, $attributes = null) {
        parent::__construct($name, $label, $attributes);

        // The type is used to determine the template to use.
        $this->_type = 'static';
    }

    /**
     * Returns HTML for this form element.
     *
     * The uppercase in the function name needs to be ignored because it is required in the core.
     *
     * @return string
     */
    // @codingStandardsIgnoreLine moodle.NamingConventions.ValidFunctionName.LowercaseMethod
    public function toHtml() {
        return $this->to_html();
    }

    /**
     * Returns HTML for this form element.
     *
     * @return string
     */
    public function to_html() {
        global $OUTPUT, $PAGE;

        $cp = new \admin_setting_configcolourpicker(
            'colorpicker',
            get_string('colorpicker', 'format_onetopic'),
            get_string('colorpicker_help', 'format_onetopic'),
            ''
        );

        $csssizeoptions = range(0, 100);
        $csssizeoptions[0] = '';

        $cssunits = [
            ['value' => '', 'label' => ''],
            ['value' => 'px', 'label' => get_string('cssunit_px', 'format_onetopic')],
            ['value' => 'em', 'label' => get_string('cssunit_em', 'format_onetopic')],
            ['value' => '%', 'label' => get_string('cssunit_percent', 'format_onetopic')],
            ['value' => 'in', 'label' => get_string('cssunit_in', 'format_onetopic')],
        ];

        $sampleactivities = \format_onetopic\local\appearances::get_sampleactivities($OUTPUT);

        $context = (object) [
            'id' => $this->getAttribute('id'),
            'name' => $this->getAttribute('name'),
            'value' => $this->getValue(),
            'colorpicker' => $cp->output_html(''),
            'csssizeoptions' => $csssizeoptions,
            'cssunits' => $cssunits,
            'sampleactivities' => $sampleactivities,
            'modalid' => 'onetopic-sectionstyles-window',
            'modaltitle' => get_string('sectionstylestitle', 'format_onetopic'),
            'showtabicon' => false,
        ];
        $element = $OUTPUT->render_from_template('format_onetopic/formelement_sectionstyles', $context);

        $PAGE->requires->js_call_amd('format_onetopic/sectionstyles', 'init');

        return $element;
    }

    /**
     * Export this element for template renderer.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        $context = $this->export_for_template_base($output);

        $context['html'] = $this->to_html();
        return $context;
    }

    /**
     * Validate the submitted value.
     *
     * The uppercase in the function name needs to be ignored because it is required in the core.
     *
     * @param int $value Draft item id with the uploaded files.
     * @return string|null Validation error message or null.
     */
    // @codingStandardsIgnoreLine moodle.NamingConventions.ValidFunctionName.LowercaseMethod
    public function validateSubmitValue($value) {
        if (empty($value)) {
            return;
        }

        $json = json_decode($value);

        if (!is_object($json)) {
            return get_string('tabstyleserrorjsoninvalid', 'format_onetopic');
        }

        return;
    }
}
