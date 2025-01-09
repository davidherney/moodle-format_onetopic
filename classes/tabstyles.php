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
 * Class containing a tab styles implementation.
 *
 * @package   format_onetopic
 * @copyright 2023 David Herney - https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace format_onetopic;

/**
 * Implement the tab styles control.
 *
 * @copyright 2023 David Herney - https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tabstyles extends \admin_setting_configtextarea {

    /**
     * Return an XHTML string for the setting
     *
     * @param array $data The data to be output
     * @param string $query The query string
     * @return string Returns an XHTML string
     */
    public function output_html($data, $query = '') {
        global $OUTPUT, $PAGE;

        $tabs = new \format_onetopic\tabs();

        // Default tab.
        $title = get_string('tablabeldefault', 'format_onetopic', 1);
        $tab = new \format_onetopic\singletab(0, $title, '#', $title);
        $tabs->add($tab);

        // Active tab.
        $title = get_string('tablabelactive', 'format_onetopic');
        $tab = new \format_onetopic\singletab(1, $title, '#', $title);
        $tab->selected = true;
        $tabs->add($tab);

        // Parent tab.
        $title = get_string('tablabelparent', 'format_onetopic');
        $tab = new \format_onetopic\singletab(2, $title, '#', $title);
        $tab->selected = true;

        // Default child index tab.
        $title = get_string('index', 'format_onetopic');
        $childtab = new \format_onetopic\singletab(2, $title, '#', $title);
        $childtab->specialclass = 'tab_initial';
        $tab->add_child($childtab);

        // Default child tab.
        $title = get_string('tablabeldefault', 'format_onetopic', '3.1');
        $childtab = new \format_onetopic\singletab(3, $title, '#', $title);
        $tab->add_child($childtab);

        // Default child tab 2.
        $title = get_string('tablabeldefault', 'format_onetopic', '3.2');
        $childtab = new \format_onetopic\singletab(3, $title, '#', $title);
        $tab->add_child($childtab);
        $tabs->add($tab);

        // Higlighted tab.
        $title = get_string('tablabelhighlighted', 'format_onetopic');
        $tab = new \format_onetopic\singletab(3, $title, '#', $title);
        $tab->specialclass = 'marker';
        $tabs->add($tab);

        // Disabled tab.
        $title = get_string('tablabeldisabled', 'format_onetopic');
        $tab = new \format_onetopic\singletab(4, $title, '#', $title);
        $tab->specialclass = 'dimmed disabled';
        $tabs->add($tab);

        // Other default child tab.
        $title = get_string('tablabeldefault', 'format_onetopic', '5');
        $tab = new \format_onetopic\singletab(5, $title, '#', $title);
        $tabs->add($tab);

        $tabslist = $tabs->get_list();

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

        $secondtabslist = $tabs->get_secondlist();

        $default = $this->get_defaultsetting();
        $context = (object) [
            'id' => $this->get_id(),
            'name' => $this->get_full_name(),
            'value' => $data,
            'tabs' => $tabslist,
            'secondrow' => $secondtabslist,
            'tabsviewclass' => 'verticaltabs',
            'colorpicker' => $cp->output_html(''),
            'csssizeoptions' => $csssizeoptions,
            'cssunits' => $cssunits,
        ];
        $element = $OUTPUT->render_from_template('format_onetopic/setting_tabstyles', $context);

        $PAGE->requires->js_call_amd('format_onetopic/tabstyles', 'init');

        return format_admin_setting($this, $this->visiblename, $element, $this->description, true, '', $default, $query);
    }

    /**
     * Validate the contents of the SCSS to ensure its parsable. Does not
     * attempt to detect undefined scss variables.
     *
     * @param string $data The scss code from text field.
     * @return mixed bool true for success or string:error on failure.
     */
    public function validate($data) {
        if (empty($data)) {
            return true;
        }

        $object = @json_decode($data);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return get_string('invalidjsonstyles', 'format_onetopic', json_last_error_msg());
        }

        return true;
    }
}
