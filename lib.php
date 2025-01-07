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
 * This file contains main class for the course format Onetopic
 *
 * @since     2.0
 * @package   format_onetopic
 * @copyright 2012 David Herney Bernal - cirano
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot. '/course/format/lib.php');

use core\output\inplace_editable;

/**
 * Main class for the Onetopic course format
 *
 * @since 2.0
 * @package format_onetopic
 * @copyright 2012 David Herney Bernal - cirano
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_onetopic extends core_courseformat\base {

    /** @var int Hidden sections are shown collapsed */
    const HIDDENSENTIONS_COLLAPSED = 0;

    /** @var int Hidden sections are invisible */
    const HIDDENSENTIONS_INVISIBLE = 1;

    /** @var int Hidden sections has the help information in a icon */
    const HIDDENSENTIONS_HELP = 2;

    /** @var int The summary is not a template */
    const TEMPLATETOPIC_NOT = 0;

    /** @var int The summary is a single template */
    const TEMPLATETOPIC_SINGLE = 1;

    /** @var int The summary is a template, list the resources that are not referenced */
    const TEMPLATETOPIC_LIST = 2;

    /** @var int Default tabs view */
    const TABSVIEW_DEFAULT = 0;

    /** @var int Vertical view */
    const TABSVIEW_VERTICAL = 1;

    /** @var int One line view */
    const TABSVIEW_ONELINE = 2;

    /** @var int Embedded course index */
    const TABSVIEW_COURSEINDEX = 3;

    /** @var int Only if theme not support "usescourseindex" */
    const SECTIONSNAVIGATION_SUPPORT = 1;

    /** @var int Not use */
    const SECTIONSNAVIGATION_NOT = 2;

    /** @var int Only at the bottom */
    const SECTIONSNAVIGATION_BOTTOM = 3;

    /** @var int Only at the bottom */
    const SECTIONSNAVIGATION_BOTH = 4;

    /** @var int Like slides */
    const SECTIONSNAVIGATION_SLIDES = 5;

    /** @var string Course index scope */
    const SCOPE_COURSE = 'course';

    /** @var string Course modules scope */
    const SCOPE_MOD = 'mod';

    /** @var string Scorm modules scope */
    const SCOPE_SCORM = 'scorm';

    /** @var bool If the class was previously instanced, in one execution cycle */
    private static $loaded = false;

    /** @var array Messages to display */
    public static $formatmsgs = [];

    /** @var stdClass Onetopic-specific extra section information */
    private $parentsections = null;

    /** @var array Modules used in template */
    public $tplcmsused = [];

    /** @var bool If the course has topic tabs */
    public $hastopictabs = false;

    /** @var string Unique id for the course format */
    public $uniqid;

    /** @var bool If print the tabs menu in the current scope */
    public $printable = true;

    /** @var string Current format scope */
    public $currentscope = null;

    /**
     * Creates a new instance of class
     *
     * Please use course_get_format($courseorid) to get an instance of the format class
     *
     * @param string $format
     * @param int $courseid
     */
    protected function __construct($format, $courseid) {
        parent::__construct($format, $courseid);

        $this->uniqid = uniqid();

        // Hack for section number, when not is like a param in the url or section is not available.
        global $section, $sectionid, $PAGE, $USER, $urlparams, $DB, $context;

        $inpopup = optional_param('inpopup', 0, PARAM_INT);

        if ($inpopup) {
            $this->printable = false;
        } else {

            $defaultscope = get_config('format_onetopic', 'defaultscope');

            if ($defaultscope) {
                $scope = explode(',', $defaultscope);
            } else {
                $scope = [];
            }

            $pagesavailable = ['course-view-onetopic', 'course-view', 'lib-ajax-service'];

            if (!in_array($PAGE->pagetype, $pagesavailable)) {

                if (in_array(self::SCOPE_MOD, $scope)) {
                    $this->currentscope = self::SCOPE_MOD;
                    $patternavailable = '/^mod-.*-view$/';
                    $this->printable = preg_match($patternavailable, $PAGE->pagetype);

                    if (!$this->printable) {
                        if (in_array(self::SCOPE_SCORM, $scope) && $PAGE->pagetype == 'mod-scorm-player') {
                            $this->printable = true;
                        }
                    }

                } else {
                    $this->printable = false;
                }
            } else {
                $this->currentscope = self::SCOPE_COURSE;
            }
        }

        $course = $this->get_course();

        if (!isset($section) && ($PAGE->pagetype == 'course-view-onetopic' || $PAGE->pagetype == 'course-view')) {

            if ($sectionid <= 0) {
                $section = optional_param('section', -1, PARAM_INT);
            }

            if ($section < 0) {
                $sectionname = optional_param('sectionname', '', PARAM_TEXT);
                $sectionbyname = null;

                if (!empty($sectionname)) {
                    $conditions = ['course' => $courseid, 'name' => $sectionname];
                    $sectionbyname = $DB->get_field('course_sections', 'section', $conditions, IGNORE_MULTIPLE);
                }

                if (!empty($sectionbyname)) {
                    $section = $sectionbyname;
                } else {
                    if (isset($USER->display[$course->id])) {
                        $section = $USER->display[$course->id];
                    } else if ($course->marker && $course->marker > 0) {
                        $section = (int)$course->marker;
                    } else {
                        $section = 0;
                    }
                }
            }
        }

        if ($this->printable) {
            if (!self::$loaded && isset($section) && $courseid &&
                    ($PAGE->pagetype == 'course-view-onetopic' || $PAGE->pagetype == 'course-view')) {
                self::$loaded = true;

                $this->singlesection = $section;

                $course = $this->get_course();

                // Onetopic format is always multipage.
                $course->realcoursedisplay = property_exists($course, 'coursedisplay') ? $course->coursedisplay : false;
                $numsections = (int)$DB->get_field('course_sections', 'MAX(section)', ['course' => $courseid], MUST_EXIST);

                if ($section >= 0 && $numsections >= $section) {
                    $realsection = $section;
                } else {
                    $realsection = 0;
                }

                if ($course->realcoursedisplay == COURSE_DISPLAY_MULTIPAGE && $realsection === 0 && $numsections >= 1) {
                    $realsection = null;
                }

                $modinfo = get_fast_modinfo($course);
                $sections = $modinfo->get_section_info_all();

                // Check if the display section is available.
                if ($realsection === null || !$sections[$realsection]->uservisible) {

                    if ($realsection) {
                        self::$formatmsgs[] = get_string('hidden_message',
                                                            'format_onetopic',
                                                            $this->get_section_name($realsection));
                    }

                    $valid = false;
                    $k = $course->realcoursedisplay ? 1 : 0;

                    do {
                        $formatoptions = $this->get_format_options($k);
                        if ($formatoptions['level'] == 0 && $sections[$k]->uservisible) {
                            $valid = true;
                            break;
                        }

                        $k++;

                    } while (!$valid && $k <= $numsections);

                    $realsection = $valid ? $k : 0;
                }

                $realsection = $realsection ?? 0;
                // The $section var is a global var, we need to set it to the real section.
                $section = $realsection;
                $this->set_sectionnum($section);
                $USER->display[$course->id] = $realsection;
                $urlparams['section'] = $realsection;
                $PAGE->set_url('/course/view.php', $urlparams);

            }
        }
    }

    /**
     * Returns true if this course format uses sections.
     *
     * @return bool
     */
    public function uses_sections() {
        return true;
    }

    /**
     * Returns true if this course format uses course index
     *
     * @return bool
     */
    public function uses_course_index() {

        $course = $this->get_course();

        if ($course->tabsview == self::TABSVIEW_COURSEINDEX) {
            return false;
        }

        if ($this->show_editor()) {
            return true;
        }

        // The 2 value is Use the site configuration.
        if (isset($course->usescourseindex) && $course->usescourseindex < 2) {
            return $course->usescourseindex;
        }

        return get_config('format_onetopic', 'courseindex') == 1;
    }

    /**
     * Returns true if this course format uses activity indentation.
     *
     * @return bool if the course format uses indentation.
     */
    public function uses_indentation(): bool {
        return true;
    }

    /**
     * Returns the display name of the given section that the course prefers.
     *
     * Use section name is specified by user. Otherwise use default ("Topic #").
     *
     * @param int|stdClass $section Section object from database or just field section.section
     * @return string Display name that the course format prefers, e.g. "Topic 2"
     */
    public function get_section_name($section) {
        $section = $this->get_section($section);
        if ((string)$section->name !== '') {
            return format_string($section->name, true,
                ['context' => context_course::instance($this->courseid)]);
        } else {
            return $this->get_default_section_name($section);
        }
    }

    /**
     * Get the current section number to display.
     * Some formats has the hability to swith from one section to multiple sections per page.
     *
     * @since Moodle 4.4
     * @return int|null the current section number or null when there is no single section.
     */
    public function get_sectionnum(): ?int {
        return $this->singlesection == null ? 0 : $this->singlesection;
    }

    /**
     * Returns the default section name for the topics course format.
     *
     * If the section number is 0, it will use the string with key = section0name from the course format's lang file.
     * If the section number is not 0, the base implementation of course_format::get_default_section_name which uses
     * the string with the key = 'sectionname' from the course format's lang file + the section number will be used.
     *
     * @param stdClass $section Section object from database or just field course_sections section
     * @return string The default value for the section name.
     */
    public function get_default_section_name($section) {
        if ($section->section == 0) {
            // Return the general section.
            return get_string('section0name', 'format_topics');
        } else {
            // Use course_format::get_default_section_name implementation which
            // will display the section name in "Topic n" format.
            return parent::get_default_section_name($section);
        }
    }

    /**
     * Get if the current format instance will show multiple sections or an individual one.
     *
     * Some formats has the hability to swith from one section to multiple sections per page,
     * output components will use this method to know if the current display is a single or
     * multiple sections.
     *
     * @return int|null null for all sections or the sectionid.
     */
    public function get_sectionid(): ?int {
        return null;
    }

    /**
     * Generate the title for this section page.
     *
     * @return string the page title
     */
    public function page_title(): string {
        return get_string('sectionoutline');
    }

    /**
     * The URL to use for the specified course (with section).
     *
     * @param int|stdClass $section Section object from database or just field course_sections.section
     *     if omitted the course view page is returned
     * @param array $options options for view URL. At the moment core uses:
     *     'navigation' (bool) if true and section has no separate page, the function returns null
     *     'sr' (int) used by multipage formats to specify to which section to return
     * @return null|moodle_url
     */
    public function get_view_url($section, $options = []) {

        $course = $this->get_course();
        $url = new moodle_url('/course/view.php', ['id' => $course->id]);

        $sr = null;
        if (array_key_exists('sr', $options)) {
            $sr = $options['sr'];
        }
        if (is_object($section)) {
            $sectionno = $section->section;
        } else {
            $sectionno = $section;
        }
        if ($sectionno !== null) {
            if ($sr !== null) {
                if ($sr) {
                    $sectionno = $sr;
                }
            }
            $url->param('section', $sectionno);
        }
        return $url;
    }

    /**
     * Returns the information about the ajax support in the given source format.
     *
     * The returned object's property (boolean)capable indicates that
     * the course format supports Moodle course ajax features.
     *
     * @return stdClass
     */
    public function supports_ajax() {
        global $COURSE, $USER;

        if (!isset($USER->onetopic_da)) {
            $USER->onetopic_da = [];
        }

        if (empty($COURSE)) {
            $disableajax = false;
        } else {
            $disableajax = isset($USER->onetopic_da[$COURSE->id]) ? $USER->onetopic_da[$COURSE->id] : false;
        }

        $ajaxsupport = new stdClass();
        $ajaxsupport->capable = !$disableajax;
        return $ajaxsupport;
    }

    /**
     * Returns true if this course format is compatible with content components.
     *
     * Using components means the content elements can watch the frontend course state and
     * react to the changes. Formats with component compatibility can have more interactions
     * without refreshing the page, like having drag and drop from the course index to reorder
     * sections and activities.
     *
     * @return bool if the format is compatible with components.
     */
    public function supports_components() {
        return true;
    }

    /**
     * Loads all of the course sections into the navigation.
     *
     * @param global_navigation $navigation
     * @param navigation_node $node The course node within the navigation
     * @return void
     */
    public function extend_course_navigation($navigation, navigation_node $node) {
        global $PAGE, $COURSE, $USER;

        // Set the section number for the course node.
        $node->action->param('section', 0);

        // If section is specified in course/view.php, make sure it is expanded in navigation.
        if ($navigation->includesectionnum === false) {
            $selectedsection = optional_param('section', null, PARAM_INT);
            if ((!defined('AJAX_SCRIPT') || AJAX_SCRIPT == '0') &&
                    $PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)) {

                if ($selectedsection !== null) {
                    $navigation->includesectionnum = $selectedsection;
                } else if (isset($USER->display[$COURSE->id])) {
                    $navigation->includesectionnum = $USER->display[$COURSE->id];
                }
            }
        }

        // Check if there are callbacks to extend course navigation.
        parent::extend_course_navigation($navigation, $node);

        // We want to remove the general section if it is empty.
        $modinfo = get_fast_modinfo($this->get_course());
        $sections = $modinfo->get_sections();
        if (!isset($sections[0])) {
            // The general section is empty to find the navigation node for it we need to get its ID.
            $section = $modinfo->get_section_info(0);
            $generalsection = $node->get($section->id, navigation_node::TYPE_SECTION);
            if ($generalsection) {
                // We found the node - now remove it.
                $generalsection->remove();
            }
        }
    }

    /**
     * Custom action after section has been moved in AJAX mode.
     *
     * Used in course/rest.php
     *
     * @return array This will be passed in ajax respose
     */
    public function ajax_section_move() {
        global $PAGE;
        $titles = [];
        $course = $this->get_course();
        $modinfo = get_fast_modinfo($course);
        $renderer = $this->get_renderer($PAGE);
        if ($renderer && ($sections = $modinfo->get_section_info_all())) {
            foreach ($sections as $number => $section) {
                $titles[$number] = $renderer->section_title($section, $course);
            }
        }
        return ['sectiontitles' => $titles, 'action' => 'move'];
    }

    /**
     * Returns the list of blocks to be automatically added for the newly created course.
     *
     * @return array of default blocks, must contain two keys BLOCK_POS_LEFT and BLOCK_POS_RIGHT
     *     each of values is an array of block names (for left and right side columns)
     */
    public function get_default_blocks() {
        return [
            BLOCK_POS_LEFT => [],
            BLOCK_POS_RIGHT => [],
        ];
    }

    /**
     * Definitions of the additional options that this course format uses for course.
     *
     * Topics format uses the following options:
     * - coursedisplay
     * - hiddensections
     *
     * @param bool $foreditform
     * @return array of options
     */
    public function course_format_options($foreditform = false) {
        static $courseformatoptions = false;
        if ($courseformatoptions === false) {
            $courseconfig = get_config('format_onetopic');
            $courseformatoptions = [
                'hiddensections' => [
                    'default' => $courseconfig->defaulthiddensections,
                    'type' => PARAM_INT,
                ],
                'hidetabsbar' => [
                    'default' => 0,
                    'type' => PARAM_INT,
                ],
                'coursedisplay' => [
                    'default' => $courseconfig->defaultcoursedisplay,
                    'type' => PARAM_INT,
                ],
                'templatetopic' => [
                    'default' => self::TEMPLATETOPIC_NOT,
                    'type' => PARAM_INT,
                ],
                'templatetopic_icons' => [
                    'default' => 0,
                    'type' => PARAM_INT,
                ],
                'tabsview' => [
                    'default' => $courseconfig->defaulttabsview,
                    'type' => PARAM_INT,
                ],
                'usessectionsnavigation' => [
                    'default' => 0, // The 0 value is the site level.
                    'type' => PARAM_INT,
                ],
                'usescourseindex' => [
                    'default' => 2,
                    'type' => PARAM_INT,
                ],
            ];
        }

        if ($foreditform && !isset($courseformatoptions['coursedisplay']['label'])) {
            $courseformatoptionsedit = [
                'hiddensections' => [
                    'label' => new lang_string('hiddensections'),
                    'help' => 'hiddensections',
                    'help_component' => 'moodle',
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            self::HIDDENSENTIONS_COLLAPSED => new lang_string('hiddensectionscollapsed'),
                            self::HIDDENSENTIONS_INVISIBLE => new lang_string('hiddensectionsinvisible'),
                            self::HIDDENSENTIONS_HELP => new lang_string('hiddensectionshelp', 'format_onetopic'),
                        ],
                    ],
                ],
                'hidetabsbar' => [
                    'label' => get_string('hidetabsbar', 'format_onetopic'),
                    'help' => 'hidetabsbar',
                    'help_component' => 'format_onetopic',
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            0 => new lang_string('no'),
                            1 => new lang_string('yes'),
                        ],
                    ],
                ],
                'coursedisplay' => [
                    'label' => new lang_string('coursedisplay', 'format_onetopic'),
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            COURSE_DISPLAY_SINGLEPAGE => new lang_string('coursedisplay_single', 'format_onetopic'),
                            COURSE_DISPLAY_MULTIPAGE => new lang_string('coursedisplay_multi', 'format_onetopic'),
                        ],
                    ],
                    'help' => 'coursedisplay',
                    'help_component' => 'format_onetopic',
                ],
                'templatetopic' => [
                    'label' => new lang_string('templatetopic', 'format_onetopic'),
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            self::TEMPLATETOPIC_NOT => new lang_string('templetetopic_not', 'format_onetopic'),
                            self::TEMPLATETOPIC_SINGLE => new lang_string('templetetopic_single', 'format_onetopic'),
                            self::TEMPLATETOPIC_LIST => new lang_string('templetetopic_list', 'format_onetopic'),
                        ],
                    ],
                    'help' => 'templatetopic',
                    'help_component' => 'format_onetopic',
                ],
                'templatetopic_icons' => [
                    'label' => get_string('templatetopic_icons', 'format_onetopic'),
                    'help' => 'templatetopic_icons',
                    'help_component' => 'format_onetopic',
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            0 => new lang_string('no'),
                            1 => new lang_string('yes'),
                        ],
                    ],
                ],
                'tabsview' => [
                    'label' => new lang_string('tabsview', 'format_onetopic'),
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            self::TABSVIEW_DEFAULT => new lang_string('tabsview_default', 'format_onetopic'),
                            self::TABSVIEW_VERTICAL => new lang_string('tabsview_vertical', 'format_onetopic'),
                            self::TABSVIEW_ONELINE => new lang_string('tabsview_oneline', 'format_onetopic'),
                        ],
                    ],
                    'help' => 'tabsview',
                    'help_component' => 'format_onetopic',
                ],
                'usessectionsnavigation' => [
                    'label' => new lang_string('usessectionsnavigation', 'format_onetopic'),
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            '0' => new lang_string('sectionsnavigation_sitelevel', 'format_onetopic'),
                            self::SECTIONSNAVIGATION_SUPPORT => new lang_string('sectionsnavigation_support', 'format_onetopic'),
                            self::SECTIONSNAVIGATION_NOT => new lang_string('sectionsnavigation_not', 'format_onetopic'),
                            self::SECTIONSNAVIGATION_BOTTOM => new lang_string('sectionsnavigation_bottom', 'format_onetopic'),
                            self::SECTIONSNAVIGATION_BOTH => new lang_string('sectionsnavigation_both', 'format_onetopic'),
                            self::SECTIONSNAVIGATION_SLIDES => new lang_string('sectionsnavigation_slides', 'format_onetopic'),
                        ],
                    ],
                    'help' => 'usessectionsnavigation',
                    'help_component' => 'format_onetopic',
                ],
                'usescourseindex' => [
                    'label' => get_string('usescourseindex', 'format_onetopic'),
                    'help' => 'usescourseindex',
                    'help_component' => 'format_onetopic',
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            2 => new lang_string('usecourseindexsite', 'format_onetopic'),
                            0 => new lang_string('no'),
                            1 => new lang_string('yes'),
                        ],
                    ],
                ],
            ];
            $courseformatoptions = array_merge_recursive($courseformatoptions, $courseformatoptionsedit);
        }

        return $courseformatoptions;
    }

    /**
     * Adds format options elements to the course/section edit form.
     *
     * This function is called from {@see course_edit_form::definition_after_data()}.
     *
     * @param MoodleQuickForm $mform form the elements are added to.
     * @param bool $forsection 'true' if this is a section edit form, 'false' if this is course edit form.
     * @return array array of references to the added form elements.
     */
    public function create_edit_form_elements(&$mform, $forsection = false) {
        global $COURSE, $CFG;
        $elements = parent::create_edit_form_elements($mform, $forsection);

        if (!$forsection && (empty($COURSE->id) || $COURSE->id == SITEID)) {
            // Add "numsections" element to the create course form - it will force new course to be prepopulated
            // with empty sections.
            // The "Number of sections" option is no longer available when editing course, instead teachers should
            // delete and add sections when needed.
            $courseconfig = get_config('moodlecourse');
            $max = (int)$courseconfig->maxsections;
            $element = $mform->addElement('select', 'numsections', get_string('numberweeks'), range(0, $max ?: 52));
            $mform->setType('numsections', PARAM_INT);
            if (is_null($mform->getElementValue('numsections'))) {
                $mform->setDefault('numsections', $courseconfig->numsections);
            }
            array_unshift($elements, $element);
        }

        if ($forsection) {
            $onetopicconfig = get_config('format_onetopic');

            if ($onetopicconfig->enablecustomstyles) {

                $mform->removeElement('tabsectionbackground');
                MoodleQuickForm::registerElementType('tabsectionbackground',
                                            $CFG->dirroot . '/course/format/onetopic/classes/local/formelement_background.php',
                                            'format_onetopic_background_form_element');
                $element = $mform->addElement('tabsectionbackground', 'tabsectionbackground',
                                                get_string('tabsectionbackground', 'format_onetopic'));

                $elements[] = $element;

                if (empty($onetopicconfig->useoldstylescontrol)) {
                    $mform->removeElement('tabstyles');
                    MoodleQuickForm::registerElementType('tabstyles',
                                                $CFG->dirroot . '/course/format/onetopic/classes/local/formelement_tabstyles.php',
                                                'format_onetopic_tabstyles_form_element');
                    $element = $mform->addElement('tabstyles', 'tabstyles', get_string('tabstyles', 'format_onetopic'));

                    $elements[] = $element;
                }
            }
        }

        return $elements;
    }

    /**
     * Updates format options for a course.
     *
     * In case if course format was changed to 'onetopic', we try to copy
     * special options from the previous format.
     *
     * @param stdClass|array $data return value from {@see moodleform::get_data()} or array with data
     * @param stdClass $oldcourse if this function is called from {@see update_course()}
     *     this object contains information about the course before update
     * @return bool whether there were any changes to the options values
     */
    public function update_course_format_options($data, $oldcourse = null) {

        $data = (array)$data;
        if ($oldcourse !== null) {
            $oldcourse = (array)$oldcourse;
            $options = $this->course_format_options();
            $defaultconfig = get_config('format_onetopic');

            foreach ($options as $key => $unused) {
                if (!array_key_exists($key, $data)) {
                    if (array_key_exists($key, $oldcourse)) {
                        $data[$key] = $oldcourse[$key];
                    } else if ($key === 'hidetabsbar') {
                        // If previous format does not have the field 'hidetabsbar' and $data['hidetabsbar'] is not set,
                        // we fill it with the default option.
                        $data['hidetabsbar'] = 0;
                    } else if ($key === 'templatetopic') {
                        $data['templatetopic'] = self::TEMPLATETOPIC_NOT;
                    } else if ($key === 'templatetopic_icons') {
                        $data['templatetopic_icons'] = 0;
                    } else if ($key === 'tabsview') {
                        $data['tabsview'] = $defaultconfig->defaulttabsview;
                    } else if ($key === 'usessectionsnavigation') {
                        $data['usessectionsnavigation'] = $defaultconfig->defaultsectionsnavigation;
                    } else if ($key === 'usescourseindex') {
                        $data['usescourseindex'] = $defaultconfig->courseindex;
                    }
                }
            }
        }
        return $this->update_format_options($data);
    }

    /**
     * Definitions of the additional options that this course format uses for section
     *
     * See course_format::course_format_options() for return array definition.
     *
     * Additionally section format options may have property 'cache' set to true
     * if this option needs to be cached in get_fast_modinfo(). The 'cache' property
     * is recommended to be set only for fields used in course_format::get_section_name(),
     * course_format::extend_course_navigation() and course_format::get_view_url()
     *
     * For better performance cached options are recommended to have 'cachedefault' property
     * Unlike 'default', 'cachedefault' should be static and not access get_config().
     *
     * Regardless of value of 'cache' all options are accessed in the code as
     * $sectioninfo->OPTIONNAME
     * where $sectioninfo is instance of section_info, returned by
     * get_fast_modinfo($course)->get_section_info($sectionnum)
     * or get_fast_modinfo($course)->get_section_info_all()
     *
     * All format options for particular section are returned by calling:
     * $this->get_format_options($section);
     *
     * @param bool $foreditform
     * @return array
     */
    public function section_format_options($foreditform = false) {
        static $sectionformatoptions = false;

        $onetopicconfig = get_config('format_onetopic');

        if ($sectionformatoptions === false) {
            $sectionformatoptions = [
                'level' => [
                    'default' => 0,
                    'type' => PARAM_INT,
                ],
                'firsttabtext' => [
                    'default' => (string)(new lang_string('index', 'format_onetopic')),
                    'type' => PARAM_TEXT,
                ],
            ];

            if ($onetopicconfig->enablecustomstyles) {
                $sectionformatoptions['tabsectionbackground'] = [
                    'default' => '',
                    'type' => PARAM_RAW,
                ];

                if ($onetopicconfig->useoldstylescontrol) {
                    $sectionformatoptions['fontcolor'] = [
                        'default' => '',
                        'type' => PARAM_RAW,
                    ];

                    $sectionformatoptions['bgcolor'] = [
                        'default' => '',
                        'type' => PARAM_RAW,
                    ];

                    $sectionformatoptions['cssstyles'] = [
                        'default' => '',
                        'type' => PARAM_RAW,
                    ];
                } else {
                    $sectionformatoptions['tabstyles'] = [
                        'default' => '',
                        'type' => PARAM_RAW,
                    ];
                }
            }
        }

        if ($foreditform) {
            $sectionformatoptionsedit = [
                'level' => [
                    'default' => 0,
                    'type' => PARAM_INT,
                    'label' => new lang_string('level', 'format_onetopic'),
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            0 => new lang_string('asprincipal', 'format_onetopic'),
                            1 => new lang_string('aschild', 'format_onetopic'),
                        ],
                        ],
                    'help' => 'level',
                    'help_component' => 'format_onetopic',
                ],
                'firsttabtext' => [
                    'default' => (string)(new lang_string('index', 'format_onetopic')),
                    'type' => PARAM_TEXT,
                    'label' => new lang_string('firsttabtext', 'format_onetopic'),
                    'help' => 'firsttabtext',
                    'help_component' => 'format_onetopic',
                ],
            ];

            if ($onetopicconfig->enablecustomstyles) {

                $sectionformatoptionsedit['tabsectionbackground'] = [
                    'default' => '',
                    'type' => PARAM_RAW,
                    'label' => new lang_string('tabsectionbackground', 'format_onetopic'),
                    'element_type' => 'text',
                    'help' => 'tabsectionbackground',
                    'help_component' => 'format_onetopic',
                ];

                if ($onetopicconfig->useoldstylescontrol) {
                    $sectionformatoptionsedit['fontcolor'] = [
                        'default' => '',
                        'type' => PARAM_RAW,
                        'label' => new lang_string('fontcolor', 'format_onetopic'),
                        'help' => 'fontcolor',
                        'help_component' => 'format_onetopic',
                    ];

                    $sectionformatoptionsedit['bgcolor'] = [
                        'default' => '',
                        'type' => PARAM_RAW,
                        'label' => new lang_string('bgcolor', 'format_onetopic'),
                        'help' => 'bgcolor',
                        'help_component' => 'format_onetopic',
                    ];

                    $sectionformatoptionsedit['cssstyles'] = [
                        'default' => '',
                        'type' => PARAM_RAW,
                        'label' => new lang_string('cssstyles', 'format_onetopic'),
                        'help' => 'cssstyles',
                        'help_component' => 'format_onetopic',
                    ];
                } else {
                    $sectionformatoptionsedit['tabstyles'] = [
                        'default' => '',
                        'type' => PARAM_RAW,
                        'label' => new lang_string('cssstyles', 'format_onetopic'),
                        'element_type' => 'textarea',
                        'help' => 'tabstyles',
                        'help_component' => 'format_onetopic',
                    ];
                }

            }

            $sectionformatoptions = $sectionformatoptionsedit;
        }
        return $sectionformatoptions;
    }

    /**
     * Whether this format allows to delete sections.
     *
     * Do not call this function directly, instead use {@see course_can_delete_section()}
     *
     * @param int|stdClass|section_info $section
     * @return bool
     */
    public function can_delete_section($section) {
        return true;
    }

    /**
     * Allows course format to execute code on moodle_page::set_cm()
     *
     * Current module can be accessed as $page->cm (returns instance of cm_info)
     *
     * @param moodle_page $page instance of page calling set_cm
     */
    public function page_set_cm(moodle_page $page) {
        $this->set_sectionnum($page->cm->sectionnum);
    }

    /**
     * Course-specific information to be output immediately above content on any course page
     *
     * See {@see core_courseformat\base::course_header()} for usage
     *
     * @return null|renderable null for no output or object with data for plugin renderer
     */
    public function course_content_header() {
        if ($this->printable) {
            return new \format_onetopic\header($this);
        } else {
            return null;
        }
    }

    /**
     * Course-specific information to be output immediately below content on any course page
     *
     * See course_format::course_header() for usage
     *
     * @return null|renderable null for no output or object with data for plugin renderer
     */
    public function course_content_footer() {
        if ($this->printable) {
            return new \format_onetopic\footer($this);
        } else {
            return null;
        }
    }

    /**
     * Prepares the templateable object to display section name.
     *
     * @param \section_info|\stdClass $section
     * @param bool $linkifneeded
     * @param bool $editable
     * @param null|lang_string|string $edithint
     * @param null|lang_string|string $editlabel
     * @return inplace_editable
     */
    public function inplace_editable_render_section_name($section, $linkifneeded = true,
            $editable = null, $edithint = null, $editlabel = null) {
        if (empty($edithint)) {
            $edithint = new lang_string('editsectionname');
        }
        if (empty($editlabel)) {
            $title = get_section_name($section->course, $section);
            $editlabel = new lang_string('newsectionname', 'core', $title);
        }
        return parent::inplace_editable_render_section_name($section, $linkifneeded, $editable, $edithint, $editlabel);
    }

    /**
     * Indicates whether the course format supports the creation of a news forum.
     *
     * @return bool
     */
    public function supports_news() {
        return true;
    }

    /**
     * Returns whether this course format allows the activity to
     * have "triple visibility state" - visible always, hidden on course page but available, hidden.
     *
     * @param stdClass|cm_info $cm course module (may be null if we are displaying a form for adding a module)
     * @param stdClass|section_info $section section where this module is located or will be added to
     * @return bool
     */
    public function allow_stealth_module_visibility($cm, $section) {
        // Allow the third visibility state inside visible sections or in section 0.
        return !$section->section || $section->visible;
    }

    /**
     * Callback used in WS core_course_edit_section when teacher performs an AJAX action on a section (show/hide).
     *
     * Access to the course is already validated in the WS but the callback has to make sure
     * that particular action is allowed by checking capabilities
     *
     * Course formats should register.
     *
     * @param section_info|stdClass $section
     * @param string $action
     * @param int $sr
     * @return null|array any data for the Javascript post-processor (must be json-encodeable)
     */
    public function section_action($section, $action, $sr) {
        global $PAGE;

        if ($section->section && ($action === 'setmarker' || $action === 'removemarker')) {
            // Format 'topics' allows to set and remove markers in addition to common section actions.
            require_capability('moodle/course:setcurrentsection', context_course::instance($this->courseid));
            course_set_marker($this->courseid, ($action === 'setmarker') ? $section->section : 0);
            return null;
        }

        // For show/hide actions call the parent method and return the new content for .section_availability element.
        $rv = parent::section_action($section, $action, $sr);
        $renderer = $PAGE->get_renderer('format_topics');

        if (!($section instanceof section_info)) {
            $modinfo = course_modinfo::instance($this->courseid);
            $section = $modinfo->get_section_info($section->section);
        }
        $elementclass = $this->get_output_classname('content\\section\\availability');
        $availability = new $elementclass($this, $section);

        $rv['section_availability'] = $renderer->render($availability);
        return $rv;
    }

    /**
     * Return the plugin configs for external functions.
     *
     * @return array the list of configuration settings
     * @since Moodle 3.5
     */
    public function get_config_for_external() {
        // Return everything (nothing to hide).
        return $this->get_format_options();
    }

    /**
     * Return Onetopic-specific extra section information.
     *
     * @return bool
     */
    public function fot_get_sections_extra() {

        if (isset($this->parentsections)) {
            return $this->parentsections;
        }

        $course = $this->get_course();
        $realcoursedisplay = property_exists($course, 'realcoursedisplay') ? $course->realcoursedisplay : false;
        $firstsection = ($realcoursedisplay == COURSE_DISPLAY_MULTIPAGE) ? 1 : 0;
        $sections = $this->get_sections();
        $parentsections = [];
        $level0section = null;
        foreach ($sections as $section) {

            if ($section->section <= $firstsection || $section->level <= 0) {
                $parent = null;
                $level0section = $section;
            } else {
                $parent = $level0section;
            }
            $parentsections[$section->section] = $parent;
        }
        $this->parentsections = $parentsections;
        return $parentsections;
    }

    /**
     * Allows to specify for modinfo that section is not available even when it is visible and conditionally available.
     *
     * @param section_info $section
     * @param bool $available the 'available' propery of the section_info as it was evaluated by conditional availability.
     * @param string $availableinfo the 'availableinfo' propery of the section_info as it was evaluated by conditional availability.
     */
    public function section_get_available_hook(section_info $section, &$available, &$availableinfo) {

        // Only check childs tabs visibility.
        if ($section->level == 0) {
            return;
        }

        // The tab visibility depend of parent visibility.
        $parentsections = $this->fot_get_sections_extra();
        $parent = $parentsections[$section->section];
        if ($parent) {
            if (!($parent->visible && $parent->available)) {
                $available = false;
                if (!$parent->uservisible) {
                    $availableinfo = '';
                }
            }
        }
    }

    /**
     * return true if the course editor must be displayed.
     *
     * @param array|null $capabilities array of capabilities a user needs to have to see edit controls in general.
     *  If null or not specified, the user needs to have 'moodle/course:manageactivities'.
     * @return bool true if edit controls must be displayed
     */
    public function show_editor(?array $capabilities = ['moodle/course:manageactivities']): bool {

        if ($this->currentscope != self::SCOPE_COURSE) {
            return false;
        }

        return parent::show_editor($capabilities);
    }

    /**
     * Get the course display value for the current course.
     *
     * Formats extending topics or weeks will use coursedisplay as this setting name
     * so they don't need to override the method. However, if the format uses a different
     * display logic it must override this method to ensure the core renderers know
     * if a COURSE_DISPLAY_MULTIPAGE or COURSE_DISPLAY_SINGLEPAGE is being used.
     *
     * @return int The current value (COURSE_DISPLAY_MULTIPAGE or COURSE_DISPLAY_SINGLEPAGE)
     */
    public function get_course_display(): int {
        global $destsection, $move;

        // The display is SINGLEPAGE when we move a section, in other case we use the MULTIPAGE.
        return $destsection && $move ? COURSE_DISPLAY_SINGLEPAGE : COURSE_DISPLAY_MULTIPAGE;
    }
}

/**
 * Implements callback inplace_editable() allowing to edit values in-place.
 *
 * @param string $itemtype
 * @param int $itemid
 * @param mixed $newvalue
 * @return \core\output\inplace_editable
 */
function format_onetopic_inplace_editable($itemtype, $itemid, $newvalue) {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/course/lib.php');
    if ($itemtype === 'sectionname' || $itemtype === 'sectionnamenl') {
        $section = $DB->get_record_sql(
            'SELECT s.* FROM {course_sections} s JOIN {course} c ON s.course = c.id WHERE s.id = ? AND c.format = ?',
            [$itemid, 'onetopic'], MUST_EXIST);
        return course_get_format($section->course)->inplace_editable_update_section_name($section, $itemtype, $newvalue);
    }
}
