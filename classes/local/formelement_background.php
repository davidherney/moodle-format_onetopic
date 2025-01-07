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
 * Form element to select a background.
 *
 * @package   format_onetopic
 * @copyright 2024 David Herney - https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/form/text.php');
require_once($CFG->libdir . '/adminlib.php');

/**
 * Display a tab styles form field.
 *
 * @package   format_onetopic
 * @copyright 2024 David Herney - https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_onetopic_background_form_element extends MoodleQuickForm_text {
    use templatable_form_element {
        export_for_template as export_for_template_base;
    }

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
        // I did not find any conflict when handling a type different from the parent class but further review is needed.
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
        global $PAGE;

        static $loadedcounter = 0;
        $loadedcounter++;

        $colorpickerid = 'colorpicker' . $loadedcounter;
        $cp = new \admin_setting_configcolourpicker($colorpickerid,
                                                    get_string('colorpicker', 'format_onetopic'),
                                                    get_string('colorpicker_help', 'format_onetopic'),
                                                    '',
        );

        $html = parent::toHtml();

        $attrs = ['class' => 'backgroundpickerselector btn btn-secondary'];
        $html .= html_writer::tag('a', get_string('selectcolor', 'format_onetopic'), $attrs);

        $attrs = [
                    'class' => 'backgroundpickerwindow hidden',
                    'title' => get_string('colorpicker', 'format_onetopic'),
                    'data-savelabel' => get_string('setcolor', 'format_onetopic'),
                ];
        $html .= html_writer::tag('div', $cp->output_html(''), $attrs);

        $attrs = ['class' => 'backgroundpicker'];
        $html = html_writer::tag('div', $html, $attrs);

        $PAGE->requires->js_call_amd('format_onetopic/onetopicbackground', 'init', [$this->getAttribute('id')]);

        return $html;

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
     * Check that all files have the allowed type.
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

        $cleaned = clean_param($value, PARAM_NOTAGS);

        if ($cleaned !== $value) {
            return get_string('backgroundpickerinvalid', 'format_onetopic');
        }

        return;
    }

}
