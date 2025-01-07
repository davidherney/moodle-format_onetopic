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
 * Form element to display a tab styles.
 *
 * @package   format_onetopic
 * @copyright 2024 David Herney - https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/form/textarea.php');
require_once($CFG->libdir . '/adminlib.php');

/**
 * Display a tab styles form field.
 *
 * @package   format_onetopic
 * @copyright 2024 David Herney - https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_onetopic_tabstyles_form_element extends MoodleQuickForm_textarea {

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
        global $OUTPUT, $PAGE;

        $tabs = new \format_onetopic\tabs();

        // Default tab.
        $title = get_string('tablabeldefault', 'format_onetopic', 1);
        $tab = new \format_onetopic\singletab(0, $title, '#', $title);
        $tab->specialclass = 'tpl-tabdefault';
        $tabs->add($tab);

        // Active tab.
        $title = get_string('tablabelactive', 'format_onetopic');
        $tab = new \format_onetopic\singletab(1, $title, '#', $title);
        $tab->specialclass = 'tpl-tabactive';
        $tab->selected = true;
        $tabs->add($tab);

        // Parent tab.
        $title = get_string('tablabelparent', 'format_onetopic');
        $tab = new \format_onetopic\singletab(2, $title, '#', $title);
        $tab->specialclass = 'tpl-tabparent';
        $tab->selected = true;

        // Default child index tab.
        $title = get_string('index', 'format_onetopic');
        $childtab = new \format_onetopic\singletab(3, $title, '#', $title);
        $childtab->specialclass = 'tab_initial tpl-tabchildindex';
        $tab->add_child($childtab);

        // Default child tab.
        $title = get_string('tablabeldefault', 'format_onetopic', '3.1');
        $childtab = new \format_onetopic\singletab(4, $title, '#', $title);
        $childtab->specialclass = 'tpl-tabchild';
        $tab->add_child($childtab);

        // Default child tab 2.
        $title = get_string('tablabeldefault', 'format_onetopic', '3.2');
        $childtab = new \format_onetopic\singletab(5, $title, '#', $title);
        $childtab->specialclass = 'tpl-tabchild';
        $tab->add_child($childtab);
        $tabs->add($tab);

        // Higlighted tab.
        $title = get_string('tablabelhighlighted', 'format_onetopic');
        $tab = new \format_onetopic\singletab(6, $title, '#', $title);
        $tab->specialclass = 'marker tpl-tabhighlighted';
        $tabs->add($tab);

        // Disabled tab.
        $title = get_string('tablabeldisabled', 'format_onetopic');
        $tab = new \format_onetopic\singletab(7, $title, '#', $title);
        $tab->specialclass = 'dimmed disabled tpl-tabdisabled';
        $tabs->add($tab);

        // Other default child tab.
        $title = get_string('tablabeldefault', 'format_onetopic', '5');
        $tab = new \format_onetopic\singletab(8, $title, '#', $title);
        $tab->specialclass = 'tpl-tabdefault';
        $tabs->add($tab);

        $tabslist = $tabs->get_list();
        $secondtabslist = $tabs->get_secondlist();

        $cp = new \admin_setting_configcolourpicker('colorpicker',
                                                    get_string('colorpicker', 'format_onetopic'),
                                                    get_string('colorpicker_help', 'format_onetopic'), '');

        $csssizeoptions = range(0, 100);
        $csssizeoptions[0] = '';

        $cssunits = [
            ['value' => '', 'label' => ''],
            ['value' => 'px', 'label' => get_string('cssunit_px', 'format_onetopic')],
            ['value' => 'em', 'label' => get_string('cssunit_em', 'format_onetopic')],
            ['value' => '%', 'label' => get_string('cssunit_percent', 'format_onetopic')],
            ['value' => 'in', 'label' => get_string('cssunit_in', 'format_onetopic')],
        ];

        $iconsystem = \core\output\icon_system::instance();
        $iconslist = $iconsystem->get_icon_name_map();
        $tabiconoptions = [];

        foreach ($iconslist as $key => $value) {
            $tokens = explode(':', $key);

            if (count($tokens) !== 2) {
                continue;
            }

            $tabiconoptions[] = (object)[
                'style' => $value,
                'identifier' => $key,
                'icon' => $iconsystem->render_pix_icon($OUTPUT, new \pix_icon($tokens[1], $key, $tokens[0])),
            ];
        }

        $context = (object) [
            'id' => $this->getAttribute('id'),
            'name' => $this->getAttribute('name'),
            'value' => $this->getValue(),
            'tabs' => $tabslist,
            'secondrow' => $secondtabslist,
            'tabsviewclass' => 'verticaltabs',
            'colorpicker' => $cp->output_html(''),
            'csssizeoptions' => $csssizeoptions,
            'cssunits' => $cssunits,
            'tabiconoptions' => $tabiconoptions,
        ];
        $element = $OUTPUT->render_from_template('format_onetopic/formelement_tabstyles', $context);

        $PAGE->requires->js_call_amd('format_onetopic/tabstyles', 'init');

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

        $json = json_decode($value);

        if (!is_object($json)) {
            return get_string('tabstyleserrorjsoninvalid', 'format_onetopic');
        }

        return;
    }

}
