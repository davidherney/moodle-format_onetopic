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
 * Settings for format.
 *
 * @package format_onetopic
 * @copyright 2023 David Herney Bernal - cirano. https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot. '/course/format/onetopic/lib.php');

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox('format_onetopic/enablecustomstyles',
                                                    get_string('enablecustomstyles', 'format_onetopic'),
                                                    get_string('enablecustomstyles_help', 'format_onetopic'), 1)
    );

    $settings->add(new admin_setting_configcheckbox('format_onetopic/useoldstylescontrol',
                                                    get_string('useoldstylescontrol', 'format_onetopic'),
                                                    get_string('useoldstylescontrol_help', 'format_onetopic'), 0)
    );

    $settings->add(new admin_setting_configcheckbox('format_onetopic/anchortotabstree',
                                                    get_string('enableanchorposition', 'format_onetopic'),
                                                    get_string('enableanchorposition_help', 'format_onetopic'), 1)
    );

    $options = [
        \format_onetopic::SCOPE_MOD => new lang_string('scope_mod', 'format_onetopic'),
        \format_onetopic::SCOPE_SCORM => new lang_string('scope_scorm', 'format_onetopic'),
    ];
    $settings->add(new admin_setting_configmulticheckbox('format_onetopic/defaultscope',
                                                    get_string('defaultscope', 'format_onetopic'),
                                                    get_string('defaultscope_help', 'format_onetopic'),
                                                    [\format_onetopic::SCOPE_MOD => 1],
                                                    $options));

    // Default settings.
    $name = 'format_onetopic/settingsheaderdefault';
    $heading = get_string('settingsheaderdefault', 'format_onetopic');
    $setting = new admin_setting_heading($name, $heading, '');
    $settings->add($setting);

    // Default hidden sections visibility.
    $options = [
        \format_onetopic::HIDDENSENTIONS_COLLAPSED => new lang_string('hiddensectionscollapsed'),
        \format_onetopic::HIDDENSENTIONS_INVISIBLE => new lang_string('hiddensectionsinvisible'),
        \format_onetopic::HIDDENSENTIONS_HELP => new lang_string('hiddensectionshelp', 'format_onetopic'),
    ];
    $settings->add(new admin_setting_configselect('format_onetopic/defaulthiddensections',
                                                    get_string('hiddensections'),
                                                    get_string('hiddensections_help'),
                                                    \format_onetopic::HIDDENSENTIONS_HELP,
                                                    $options)
    );

    // Default course display.
    $options = [
        COURSE_DISPLAY_SINGLEPAGE => new lang_string('coursedisplay_single', 'format_onetopic'),
        COURSE_DISPLAY_MULTIPAGE => new lang_string('coursedisplay_multi', 'format_onetopic'),
    ];
    $settings->add(new admin_setting_configselect('format_onetopic/defaultcoursedisplay',
                                                    get_string('coursedisplay', 'format_onetopic'),
                                                    get_string('coursedisplay_help', 'format_onetopic'),
                                                    COURSE_DISPLAY_MULTIPAGE,
                                                    $options)
    );

    // Default tabs view.
    $options = [
        \format_onetopic::TABSVIEW_DEFAULT => new lang_string('tabsview_default', 'format_onetopic'),
        \format_onetopic::TABSVIEW_VERTICAL => new lang_string('tabsview_vertical', 'format_onetopic'),
        \format_onetopic::TABSVIEW_ONELINE => new lang_string('tabsview_oneline', 'format_onetopic'),
    ];
    $settings->add(new admin_setting_configselect('format_onetopic/defaulttabsview',
                                                    get_string('tabsview', 'format_onetopic'),
                                                    get_string('tabsview_help', 'format_onetopic'),
                                                    \format_onetopic::TABSVIEW_DEFAULT,
                                                    $options)
    );

    // Default sections navigation.
    $options = [
        \format_onetopic::SECTIONSNAVIGATION_SUPPORT => new lang_string('sectionsnavigation_support', 'format_onetopic'),
        \format_onetopic::SECTIONSNAVIGATION_NOT => new lang_string('sectionsnavigation_not', 'format_onetopic'),
        \format_onetopic::SECTIONSNAVIGATION_BOTTOM => new lang_string('sectionsnavigation_bottom', 'format_onetopic'),
        \format_onetopic::SECTIONSNAVIGATION_BOTH => new lang_string('sectionsnavigation_both', 'format_onetopic'),
        \format_onetopic::SECTIONSNAVIGATION_SLIDES => new lang_string('sectionsnavigation_slides', 'format_onetopic'),
    ];
    $settings->add(new admin_setting_configselect('format_onetopic/defaultsectionsnavigation',
                                                    get_string('defaultsectionsnavigation', 'format_onetopic'),
                                                    get_string('defaultsectionsnavigation_help', 'format_onetopic'),
                                                    \format_onetopic::SECTIONSNAVIGATION_SUPPORT,
                                                    $options)
    );

    // Course index.
    $options = ['0' => get_string('disable'), '1' => get_string('enable')];
    $settings->add(new admin_setting_configselect('format_onetopic/courseindex',
                                                    get_string('courseindex', 'format_onetopic'),
                                                    get_string('courseindex_help', 'format_onetopic'), 1, $options));

    // Styles settings.
    $name = 'format_onetopic/settingsheaderstyles';
    $heading = get_string('settingsheaderstyles', 'format_onetopic');
    $setting = new admin_setting_heading($name, $heading, '');
    $settings->add($setting);

    $name = 'format_onetopic/tabstyles';
    $title = get_string('tabstyles', 'format_onetopic');
    $description = get_string('tabstyles_help', 'format_onetopic');
    $setting = new \format_onetopic\tabstyles($name, $title, $description, '');
    $settings->add($setting);

}
