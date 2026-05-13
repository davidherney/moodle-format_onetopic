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

use core\lang_string;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/course/format/onetopic/lib.php');

if ($ADMIN->fulltree) {
    $settings->add(
        new admin_setting_configcheckbox(
            'format_onetopic/enablecustomstyles',
            get_string('enablecustomstyles', 'format_onetopic'),
            get_string('enablecustomstyles_help', 'format_onetopic'),
            1
        )
    );

    $url = new \core\url('/course/format/onetopic/migratestyles.php');
    $help = get_string('useoldstylescontrol_help', 'format_onetopic') .
            '<br /><strong>' . get_string('migrationpagehelp', 'format_onetopic', $url) . '</strong> ';
    $settings->add(
        new admin_setting_configcheckbox(
            'format_onetopic/useoldstylescontrol',
            get_string('useoldstylescontrol', 'format_onetopic'),
            $help,
            0
        )
    );

    $settings->add(
        new admin_setting_configcheckbox(
            'format_onetopic/anchortotabstree',
            get_string('enableanchorposition', 'format_onetopic'),
            get_string('enableanchorposition_help', 'format_onetopic'),
            1
        )
    );

    $options = [
        \format_onetopic::SCOPE_MOD => new lang_string('scope_mod', 'format_onetopic'),
        \format_onetopic::SCOPE_SCORM => new lang_string('scope_scorm', 'format_onetopic'),
    ];
    $settings->add(
        new admin_setting_configmulticheckbox(
            'format_onetopic/defaultscope',
            get_string('defaultscope', 'format_onetopic'),
            get_string('defaultscope_help', 'format_onetopic'),
            [],
            $options
        )
    );

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
    $settings->add(
        new admin_setting_configselect(
            'format_onetopic/defaulthiddensections',
            get_string('hiddensections'),
            get_string('hiddensections_help'),
            \format_onetopic::HIDDENSENTIONS_HELP,
            $options
        )
    );

    // Default course display.
    $options = [
        COURSE_DISPLAY_SINGLEPAGE => new lang_string('coursedisplay_single', 'format_onetopic'),
        COURSE_DISPLAY_MULTIPAGE => new lang_string('coursedisplay_multi', 'format_onetopic'),
    ];
    $settings->add(
        new admin_setting_configselect(
            'format_onetopic/defaultcoursedisplay',
            get_string('coursedisplay', 'format_onetopic'),
            get_string('coursedisplay_help', 'format_onetopic'),
            COURSE_DISPLAY_MULTIPAGE,
            $options
        )
    );

    // Default tabs view.
    $options = [
        \format_onetopic::TABSVIEW_DEFAULT => new lang_string('tabsview_default', 'format_onetopic'),
        \format_onetopic::TABSVIEW_VERTICAL => new lang_string('tabsview_vertical', 'format_onetopic'),
        \format_onetopic::TABSVIEW_VERTICALALL => new lang_string('tabsview_verticalall', 'format_onetopic'),
        \format_onetopic::TABSVIEW_ONELINE => new lang_string('tabsview_oneline', 'format_onetopic'),
    ];
    $settings->add(
        new admin_setting_configselect(
            'format_onetopic/defaulttabsview',
            get_string('tabsview', 'format_onetopic'),
            get_string('tabsview_help', 'format_onetopic'),
            \format_onetopic::TABSVIEW_DEFAULT,
            $options
        )
    );

    // Default sections navigation.
    $options = [
        \format_onetopic::SECTIONSNAVIGATION_SUPPORT => new lang_string('sectionsnavigation_support', 'format_onetopic'),
        \format_onetopic::SECTIONSNAVIGATION_NOT => new lang_string('sectionsnavigation_not', 'format_onetopic'),
        \format_onetopic::SECTIONSNAVIGATION_BOTTOM => new lang_string('sectionsnavigation_bottom', 'format_onetopic'),
        \format_onetopic::SECTIONSNAVIGATION_BOTH => new lang_string('sectionsnavigation_both', 'format_onetopic'),
        \format_onetopic::SECTIONSNAVIGATION_SLIDES => new lang_string('sectionsnavigation_slides', 'format_onetopic'),
    ];
    $settings->add(
        new admin_setting_configselect(
            'format_onetopic/defaultsectionsnavigation',
            get_string('defaultsectionsnavigation', 'format_onetopic'),
            get_string('defaultsectionsnavigation_help', 'format_onetopic'),
            \format_onetopic::SECTIONSNAVIGATION_SUPPORT,
            $options
        )
    );

    // Course index.
    $options = ['0' => get_string('disable'), '1' => get_string('enable')];
    $settings->add(
        new admin_setting_configselect(
            'format_onetopic/courseindex',
            get_string('courseindex', 'format_onetopic'),
            get_string('courseindex_help', 'format_onetopic'),
            1,
            $options
        )
    );

    // Subsection default display mode.
    $options = [
        \format_onetopic::SUBSECTIONSDISPLAY_LIST => new lang_string('displaymode_list', 'format_onetopic'),
        \format_onetopic::SUBSECTIONSDISPLAY_SUMMARY => new lang_string('displaymode_summary', 'format_onetopic'),
        \format_onetopic::SUBSECTIONSDISPLAY_COLLAPSIBLE => new lang_string('displaymode_collapsible', 'format_onetopic'),
    ];
    $settings->add(
        new admin_setting_configselect(
            'format_onetopic/defaultsubsectionsdisplay',
            get_string('displaymode', 'format_onetopic'),
            get_string('displaymode_help', 'format_onetopic'),
            \format_onetopic::SUBSECTIONSDISPLAY_LIST,
            $options
        )
    );

    // Styles settings.
    $name = 'format_onetopic/settingsheaderstyles';
    $heading = get_string('settingsheaderstyles', 'format_onetopic');
    $setting = new admin_setting_heading($name, $heading, '');
    $settings->add($setting);

    // Sections and Subsection default appearance.
    $customappearancesections = \format_onetopic\local\appearances::get_availables_appearances(null);
    $customappearancesubsections = \format_onetopic\local\appearances::get_availables_appearances(null, true);

    $hascustomappearances = false;
    if (count($customappearancesections) > 0) {
        $options = ['' => get_string('defaultappearance', 'format_onetopic')] + $customappearancesections;
        $settings->add(
            new admin_setting_configselect(
                'format_onetopic/defaultsectionsappearance',
                get_string('defaultsectionsappearance', 'format_onetopic'),
                get_string('defaultsectionsappearance_help', 'format_onetopic'),
                '',
                $options
            )
        );
        $hascustomappearances = true;
    }

    if (count($customappearancesubsections) > 0) {
        $options = ['' => get_string('defaultappearance', 'format_onetopic')] + $customappearancesubsections;
        $settings->add(
            new admin_setting_configselect(
                'format_onetopic/defaultsubsectionsappearance',
                get_string('defaultsubsectionsappearance', 'format_onetopic'),
                get_string('defaultsubsectionsappearance_help', 'format_onetopic'),
                '',
                $options
            )
        );
        $hascustomappearances = true;
    }

    if (!$hascustomappearances) {
        $settings->add(
            new admin_setting_description(
                'format_onetopic/nocustomappearances',
                '',
                get_string('nocustomappearances', 'format_onetopic')
            )
        );
    }

    $gotoappearancesurl = new \core\output\action_link(
        new \core\url('/course/format/onetopic/appearances.php'),
        get_string('customizeappearances', 'format_onetopic'),
        null,
        ['class' => 'btn btn-secondary']
    );
    $settings->add(
        new admin_setting_description(
            'format_onetopic/customizeappearances',
            get_string('customizeappearances', 'format_onetopic'),
            get_string('customizeappearances_help', 'format_onetopic') . '<br>' . $OUTPUT->render($gotoappearancesurl) . '<hr>'
        )
    );
}
