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
 * Strings for component 'format_onetopic', language 'en'
 *
 * @since 2.4
 * @package format_onetopic
 * @copyright 2012 David Herney - cirano
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['aboutresource'] = 'About the resource';
$string['appearancedeleted'] = 'Appearance deleted successfully.';
$string['appearances'] = 'Custom appearances';
$string['appearancescope_site'] = 'For the entire site';
$string['appearanceusages'] = 'Usages of "{$a}"';
$string['appearanceusagesbutton'] = 'Show usages';
$string['asbrother'] = 'Same level that the previous tab';
$string['aschild'] = 'Child of previous tab';
$string['asprincipal'] = 'Normal, as a first level tab';
$string['availablefor'] = 'Available for';
$string['availableforerror'] = 'You must select at least one option.';
$string['backgroundpickerinvalid'] = 'Invalid background value';
$string['bgcolor'] = 'Background color';
$string['bgcolor_help'] = 'Used to change the tab background color. The value can be a color in a CSS valid representation, for example: <ul><li>Hexadecimal: #ffffff</li><li>RGB: rgb(0,255,0)</li><li>Name: green</li></ul>';
$string['cantcreatesection'] = 'Error creating a new topic';
$string['checkingavailability'] = 'Checking availability...';
$string['cli_help'] = 'Execute cli actions.
Options:
-h,  --help             Print out this help
-ms, --mstyles=X        Executes a migration action. X can be: list, migrate, or all.
                        If X is not specified, the "list" option is used.
                        If X is "migrate", it will migrate the styles that can be migrated.
                        If X is "list", it will list the sections with styles to be migrated.
-msl, --mslimit=100     Limit the number of records to be displayed on screen or migrated. Default is 100.
                        Be careful with the limit. If you set a value that includes some styles but excludes others from a section,
                        only the included styles will be migrated.
Example:
\$sudo -u www-data /usr/bin/php cli.php -ms=list -msl=50
';
$string['cli_migratestylesend'] = 'Styles migration completed successfully.';
$string['cli_migratestylesstart'] = 'Starting styles migration...';
$string['cli_migratestylesstarttitle'] = 'Styles migration...';
$string['colorpicker'] = 'Color picker';
$string['colorpicker_help'] = '';
$string['coursedisplay'] = 'Visualization mode of section 0';
$string['coursedisplay_help'] = 'This define as display the section 0: as a first tab or as section before the tabs bar.';
$string['coursedisplay_multi'] = 'Before the tabs';
$string['coursedisplay_single'] = 'As tab';
$string['courseindex'] = 'Course index';
$string['courseindex_help'] = 'Enable or disable the <em>course index</em> bar to navigate through the sections and resources.
This option is only usable if the <em>Course index</em> feature is available in the current Theme.
This option can be overwrite for each course.';
$string['createnewappearance'] = 'Create new appearance';
$string['createnewappearance_section'] = 'New section appearance';
$string['createnewappearance_subsection'] = 'New subsection appearance';
$string['creating_section'] = 'Creating new topic';
$string['cssbackground'] = 'Background';
$string['cssborder'] = 'Border';
$string['csscolor'] = 'Color';
$string['cssdashed'] = 'Dashed';
$string['cssdotted'] = 'Dotted';
$string['cssdouble'] = 'Double';
$string['cssfont'] = 'Font';
$string['cssgroove'] = 'Groove';
$string['csshidden'] = 'Hidden';
$string['cssinset'] = 'Inset';
$string['cssitalic'] = 'Italic';
$string['cssnone'] = 'None';
$string['cssnormal'] = 'Normal';
$string['cssoblique'] = 'Oblique';
$string['cssother'] = 'Other styles';
$string['cssoutset'] = 'Outset';
$string['cssradius'] = 'Radius';
$string['cssridge'] = 'Ridge';
$string['csssize'] = 'Size';
$string['csssolid'] = 'Solid';
$string['cssstyle'] = 'Style';
$string['cssstyles'] = 'CSS properties';
$string['cssstyles_help'] = 'Used to change CSS properties of the tab. Use a standard value to the attribute <em>style</em> in a html tag. Example: <br /><strong>font-weight: bold; font-size: 16px;</strong>';
$string['cssunit'] = 'Unit';
$string['cssunit_em'] = 'em';
$string['cssunit_in'] = 'in';
$string['cssunit_percent'] = '%';
$string['cssunit_px'] = 'px';
$string['cssweight'] = 'Weight';
$string['cssweightbold'] = 'Bold';
$string['cssweightlighter'] = 'Lighter';
$string['currentsection'] = 'This topic';
$string['customappearance'] = 'Custom appearance';
$string['customappearance_help'] = 'Custom appearance settings for the section.';
$string['customappearancebysections'] = 'Custom appearance by sections';
$string['customappearancebysections_help'] = 'Custom appearance settings for the sections. This is a section level setting, so it will overwrite the default custom appearance settings for the sections that have it configured.';
$string['customappearancebysubsections'] = 'Custom appearance by subsections';
$string['customappearancebysubsections_help'] = 'Custom appearance settings for the subsections. This is a subsection level setting, so it will overwrite the default custom appearance settings for the subsections that have it configured.';
$string['customizeappearances'] = 'Customized appearances';
$string['customizeappearances_help'] = 'You can create new appearances and edit or delete the existing ones. When you create or edit an appearance, you can choose the styles to apply and the scope of the appearance, that is, if it can be applied to sections, subsections, or both.';
$string['defaultappearance'] = 'None: use the current theme styles';
$string['defaultscope'] = 'Default scope';
$string['defaultscope_help'] = 'Default scope used to define the pages on which the tab menu is printed. The SCORM Player scope require the Modules scope.';
$string['defaultsectionsappearance'] = 'Default appearance';
$string['defaultsectionsappearance_help'] = 'Default appearance settings for the sections. This is a section level setting, so it will be used for the sections that do not have a custom appearance configured.';
$string['defaultsectionsnavigation'] = 'Default value to sections navigation';
$string['defaultsectionsnavigation_help'] = 'Default value used in courses to define the "Uses sections navigation" feature. This can be overwrite for each course.';
$string['defaultsubsectionsappearance'] = 'Default appearance for subsections';
$string['defaultsubsectionsappearance_help'] = 'Default appearance settings for the subsections. This is a subsection level setting, so it will be used for the subsections that do not have a custom appearance configured.';
$string['deleteappearance'] = 'Delete appearance';
$string['deleteappearanceconfirm'] = 'Are you sure you want to delete the appearance "{$a}"? All references to this appearance will be cleared.';
$string['displaymode'] = 'Display mode';
$string['displaymode_collapsible'] = 'Default collapsible';
$string['displaymode_help'] = 'Define how to display the content of sections';
$string['displaymode_list'] = 'Default list';
$string['displaymode_summary'] = 'Default summary';
$string['editappearance'] = 'Edit appearance';
$string['enableanchorposition'] = 'Enable anchor position';
$string['enableanchorposition_help'] = 'Use an anchor to navigate to the top of tabs when click in a tab.';
$string['enablecustomstyles'] = 'Enable custom styles';
$string['enablecustomstyles_help'] = 'Enable font color, background color and other custom tab styles in the sections configuration.';
$string['error_nosectioninfo'] = 'The indicated topic have not information';
$string['firsttabtext'] = 'Text of the first tab in sublevel';
$string['firsttabtext_help'] = 'If this tab has sublevels, this will be the text of the first tab';
$string['fontcolor'] = 'Font color';
$string['fontcolor_help'] = 'Used to change the tab font color. The value can be a color in a CSS valid representation, for example: <ul><li>Hexadecimal: #ffffff</li><li>RGB: rgb(0,255,0)</li><li>Name: green</li></ul>';
$string['hidden_message'] = 'The section <em>{$a}</em> is not currently available.';
$string['hiddensectionshelp'] = 'Hidden sections are shown in collapsed form with available message';
$string['hiddentabsbar'] = 'The tabs are set to be hidden. They will not be seen when not in edit mode.';
$string['hidefromothers'] = 'Hide topic';
$string['hidetabsbar'] = 'Hide tabs bar';
$string['hidetabsbar_help'] = 'Hide tabs bar in the course page. The navigation is with the sections navbar.';
$string['increasesections'] = 'Add a section after the currently selected section';
$string['index'] = 'Index';
$string['invalidjsonstyles'] = 'Styles configuration is not valid, fails with: {$a}';
$string['level'] = 'Level';
$string['level_help'] = 'Change the tab level.';
$string['migratecssstyles'] = 'Styles';
$string['migratestyles'] = 'Migrate styles';
$string['migratestylesaction'] = 'Migrate styles';
$string['migratestylesall'] = 'Show all sections';
$string['migratestylesconfirm'] = 'Are you sure you want to migrate the styles? This action will migrate the styles from the old format to the new one, and it cannot be undone. Old styles will also be kept for compatibility.';
$string['migratestylesdone'] = 'Styles migration completed successfully.';
$string['migratestyleslimit'] = 'The number of records to be displayed on screen has been exceeded. Only the first {$a} records are displayed.';
$string['migratestylesnothing'] = 'There are no styles to migrate.';
$string['migratestylesonlytochange'] = 'Show only the sections that are pending migration';
$string['migratewillbemigrated'] = 'Will be migrated';
$string['migrationpagehelp'] = 'You can use the following page to review which styles exist in the old control: <a href="{$a}" target="_blank">Migration</a>. You can also use the automatic migration feature if there are styles that can be migrated. Styles cannot be migrated if the new property already exists in the database.';
$string['movesectionto'] = 'Move current topic';
$string['movesectionto_help'] = 'Move current topic to left/right of selected topic';
$string['nocustomappearances'] = 'There are no custom appearances configured yet.';
$string['nomigratestyles'] = 'There are no styles to migrate.';
$string['nousagesfound'] = 'This appearance is not currently used anywhere.';
$string['onetopic:editallappearances'] = 'Edit all appearances';
$string['onetopic:editappearances'] = 'Edit course appearances';
$string['page-course-view-topics'] = 'Any course main page in onetopic format';
$string['page-course-view-topics-x'] = 'Any course page in onetopic format';
$string['plugin_description'] = 'Course sections are displayed separately in tabs.';
$string['pluginname'] = 'Onetopic format';
$string['privacy:metadata'] = 'The Onetopic format plugin does not store any personal data.';
$string['scope'] = 'Scope';
$string['scope_mod'] = 'Modules';
$string['scope_scorm'] = 'SCORM Player';
$string['sectionname'] = 'Topic';
$string['sectionsnavigation_both'] = 'At top and bottom section';
$string['sectionsnavigation_bottom'] = 'Only at the bottom';
$string['sectionsnavigation_not'] = 'Not use';
$string['sectionsnavigation_sitelevel'] = 'Use the default site value';
$string['sectionsnavigation_slides'] = 'Like slides';
$string['sectionsnavigation_support'] = 'Only if theme not support the "uses course index" feature';
$string['selectcolor'] = 'Select color';
$string['setcolor'] = 'Set color';
$string['settingsheaderdefault'] = 'Default course settings';
$string['sectionstyles'] = 'Content styles';
$string['sectionstyles_help'] = 'Set the content styles for the section, such as the resource layout.';
$string['sectionstylesetdefault'] = 'Set Content styles';
$string['sectionstylestitle'] = 'Content styles';
$string['settingsheaderstyles'] = 'Default tabs styles';
$string['showfromothers'] = 'Show topic';
$string['subsection'] = 'Subsection';
$string['subsectionstylebuttons_help'] = 'Click on the button to configure the appearance of the subsection content.';
$string['subsectionstyles'] = 'Subsection styles';
$string['subsectionstyles_help'] = 'Set the styles for the subsection content area.';
$string['subsectionstylesetdefault'] = 'Set Content styles';
$string['subsectionstylestitle'] = 'Subsection styles';
$string['subtopictoright'] = 'Move to right as subtopic';
$string['tabicon'] = 'Icon';
$string['tabiconremove'] = 'Remove icon';
$string['tabiconselect'] = 'Select icon';
$string['tablabelactive'] = 'Active tab';
$string['tablabeldefault'] = 'Default tab {$a}';
$string['tablabeldisabled'] = 'Disabled';
$string['tablabelhighlighted'] = 'Highlighted';
$string['tablabelparent'] = 'Parent tab';
$string['tabsectionbackground'] = 'Section content background';
$string['tabsectionbackground_help'] = 'Used to change the background of the section content. The value can be a color in a CSS valid representation, for example: <ul><li>Hexadecimal: #ffffff</li><li>RGB: rgb(0,255,0)</li><li>Name: green</li></ul>
It can also be a URL attribute and other CSS background options.';
$string['tabstylebuttons_help'] = 'Click on each button to configure the appearance of the tab in each of its possible states.';
$string['tabstyleclear'] = 'Clear styles';
$string['tabstyles'] = 'Tab styles';
$string['tabstyles_help'] = 'Set the styles for the differents tab states.';
$string['tabstylesdisplay'] = 'Show/hide tab style options';
$string['tabstylesdisplay_help'] = 'The styles that are edited apply only to the tab in this section and to the child tabs when applicable. The entire bar is shown as an example of the different states that the tab can go through, but they will all be on the tab itself.';
$string['tabstyleserrorjsoninvalid'] = 'Invalid JSON format';
$string['tabstylesetactive'] = 'Set Active';
$string['tabstylesetchildindex'] = 'Set Child index';
$string['tabstylesetchilds'] = 'Set Childs';
$string['tabstylesetcontent'] = 'Set Content';
$string['tabstylesetdefault'] = 'Set Default';
$string['tabstylesetdisabled'] = 'Set Disabled';
$string['tabstylesethighlighted'] = 'Set Highlighted';
$string['tabstylesethover'] = 'Set Hover';
$string['tabstylesetparent'] = 'Set Parent';
$string['tabstylesset'] = 'Set styles';
$string['tabstylestitle'] = 'Tab styles';
$string['tabsview'] = 'Tabs view';
$string['tabsview_courseindex'] = 'Embedded course index';
$string['tabsview_default'] = 'By default';
$string['tabsview_help'] = 'By default: is the traditional view.<br />
Vertically: show tabs in vertical direction. Tabs on the left and content on the right.<br />
Vertically including childs: show tabs in vertical direction, including the child tabs. Tabs on the left and content on the right.<br />
One single line: all tabs are displayed in a single line with horizontal scroll. Useful if too many tabs are used.';
$string['tabsview_oneline'] = 'Only one line';
$string['tabsview_vertical'] = 'Vertically';
$string['tabsview_verticalall'] = 'Vertically including childs';
$string['templatetopic'] = 'Use topic summary as template';
$string['templatetopic_help'] = 'This option is used in order to use the summary topic as a template. If it is used as template, you can include the resources in the content, not only as tradicional moodle\'s lists. <br />In order to include a resource, write the resource name between double brackets, for example: [[News forum]]. This functionality is similar to activity name filter, however, it is different because the user can chose if included the resource icon and decide than activities are be included.';
$string['templatetopic_icons'] = 'Show icon in resource links in summary';
$string['templatetopic_icons_help'] = 'This option defines if the icons are displayed in the summary when it is a template.';
$string['templetetopic_list'] = 'Yes, use the summary as template, list the resources that are not referenced';
$string['templetetopic_not'] = 'No, display as default';
$string['templetetopic_single'] = 'Yes, use the summary as template';
$string['timesused'] = 'Times used';
$string['togglemenu'] = 'Toggle menu';
$string['topicslabel'] = 'Topics';
$string['type'] = 'Type';
$string['type_section'] = 'Section';
$string['type_subsection'] = 'Subsection';
$string['uniquecode'] = 'Unique code';
$string['uniquecode_help'] = 'A unique identifier for this appearance. Only letters, numbers, hyphens (-) and underscores (_) are allowed.';
$string['uniquecodeavailable'] = 'This unique code is available.';
$string['uniquecodeexists'] = 'This unique code is already in use by another appearance.';
$string['uniquecodeinvalid'] = 'The unique code can only contain letters, numbers, hyphens (-) and underscores (_).';
$string['usecourseindexsite'] = 'Use the default site value';
$string['useoldstylescontrol'] = 'Use legacy styles control';
$string['useoldstylescontrol_help'] = 'Use legacy style controls. This option is only available for compatibility with older versions of the plugin and will be removed in the future in favor of using only the new style editor.';
$string['usescourseindex'] = 'Uses course index';
$string['usescourseindex_help'] = 'Use the <em>course index</em> bar to navigate through the sections and resources';
$string['usessectionsnavigation'] = 'Uses sections navigation';
$string['usessectionsnavigation_help'] = 'Show buttons for navigate to next or previous section.';

// Deprecated since Moodle 4.0.
$string['disable'] = 'Disable';
$string['disableajax'] = 'Asynchronous edit actions';
$string['disableajax_help'] = 'Use this action in order to move resources between topic tabs. It only disables the asynchronous actions in current session, it is not permanently.';
$string['enable'] = 'Enable';
$string['utilities'] = 'Tabs edition utilities';

// Deprecated since Moodle 4.3.
$string['duplicate'] = 'Duplicate';
$string['duplicate_confirm'] = 'Are you sure you want to duplicate the current topic? The task can take a while depending on the amount of resources.';
$string['duplicatesection'] = 'Duplicate current topic';
$string['duplicatesection_help'] = 'Used to duplicate the resources of current topic in a new topic';
$string['duplicating'] = 'Duplicating';
$string['progress_counter'] = 'Duplicating activities ({$a->current}/{$a->size})';
$string['progress_full'] = 'Duplicating topic';
$string['rebuild_course_cache'] = 'Rebuild course cache';
$string['sampleresource'] = 'Sample resource';
$string['sampleactivity'] = 'Sample activity';
$string['samplesubsection'] = 'Subsection';
$string['sampleforum'] = 'Discussion forum';
$string['samplepage'] = 'Content page';
$string['samplequiz'] = 'Assessment quiz';
$string['resourcelayout'] = 'Resource layout';
$string['resourcelayout_default'] = 'Default';
$string['resourcelayout_inline'] = 'Inline';
$string['resourcelayout_cards'] = 'Cards';
$string['contentstylestitle'] = 'Content styles';
$string['resourcelayout_help'] = 'Resources are displayed:<br>
<b>Default:</b> as usually<br>
<b>Inline:</b> horizontally and vertically<br>
<b>Card:</b> single card';
$string['resourcelayoutinvalid'] = 'The layout is invalid';
