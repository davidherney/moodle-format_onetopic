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

namespace format_onetopic\local;

use format_onetopic;

/**
 * Class appearances
 *
 * @package    format_onetopic
 * @copyright  2026 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class appearances {
    /** @var array CSS properties that require units */
    const PROPERTIES_WITH_UNITS = ['font-size', 'line-height', 'margin', 'padding', 'border-width', 'border-radius'];

    /** @var array Precedence order for tab style types */
    const STYLES_PRECEDENCE = ['default', 'childs', 'childindex', 'active', 'parent', 'highlighted', 'disabled', 'hover'];

    /** @var array Static cache for computed resource layouts keyed by section id. */
    private static $resourcelayoutcache = [];

    /**
     * Get the list of available appearances for a specific section.
     *
     * @param ?int $courseid The course id. Null for site-level only.
     * @param ?bool $issubsection Whether the section is a subsection or not.
     * @return array List of available appearances.
     */
    public static function get_availables_appearances(?int $courseid, ?bool $issubsection = false): array {
        global $DB;

        $type = $issubsection ? 'subsection' : 'section';
        $sql = "SELECT uniquecode, name
                  FROM {format_onetopic_appearances}
                 WHERE type = :type AND (courseid = :siteid";

        $params = ['type' => $type, 'siteid' => SITEID];
        if ($courseid) {
            $sql .= " OR courseid = :courseid";
            $params['courseid'] = $courseid;
        }
        $sql .= ")";

        $records = $DB->get_records_sql($sql, $params);

        $appearances = [];
        foreach ($records as $record) {
            $appearances[$record->uniquecode] = $record->name;
        }

        return $appearances;
    }

    /**
     * Get the styles object from an appearance by its uniquecode.
     *
     * @param string $uniquecode The appearance unique code.
     * @return ?object The styles object from configdata, or null if not found.
     */
    public static function get_styles_by_uniquecode(string $uniquecode): ?object {
        global $DB;

        if (empty($uniquecode)) {
            return null;
        }

        $record = $DB->get_record('format_onetopic_appearances', ['uniquecode' => $uniquecode], 'configdata');

        if (!$record) {
            return null;
        }

        $configdata = json_decode($record->configdata);

        if (!is_object($configdata) || !property_exists($configdata, 'styles') || !is_object($configdata->styles)) {
            return null;
        }

        return $configdata->styles;
    }

    /**
     * Get the timemodified of an appearance by its uniquecode.
     *
     * @param string $uniquecode The appearance unique code.
     * @return int The timemodified value, or 0 if not found.
     */
    public static function get_timemodified_by_uniquecode(string $uniquecode): int {
        global $DB;

        if (empty($uniquecode)) {
            return 0;
        }

        $timemodified = $DB->get_field('format_onetopic_appearances', 'timemodified', ['uniquecode' => $uniquecode]);

        return $timemodified ? (int)$timemodified : 0;
    }

    /**
     * Get the styles object from an appearance by its uniquecode.
     *
     * @param string $uniquecode The appearance unique code.
     * @return ?string The current resourcelayout or null if not found.
     */
    public static function get_resourcelayout_by_uniquecode(string $uniquecode): ?string {
        global $DB;

        if (empty($uniquecode)) {
            return null;
        }

        $resourcelayout = $DB->get_field('format_onetopic_appearances', 'resourcelayout', ['uniquecode' => $uniquecode]);

        if (in_array($resourcelayout, format_onetopic::RESOURCESLAYOUTS)) {
            return $resourcelayout;
        }

        return null;
    }

    /**
     * Generate CSS from a styles object using generic selectors (site/course level).
     *
     * @param object $styles The styles object (with keys: default, active, parent, etc.).
     * @return string The generated CSS string.
     */
    public static function generate_generic_css(object $styles): string {
        $csscontent = '';

        $orderedstyles = self::order_styles($styles);

        foreach ($orderedstyles as $type => $properties) {
            $important = false;
            $selector = '';

            switch ($type) {
                case 'active':
                    $selector .= '#tabs-tree-start .verticaltabs .format_onetopic-tabs .nav-item a.nav-link.active, ';
                    $selector .= '#tabs-tree-start .nav-tabs a.nav-link.active, ';
                    $selector .= '#tabs-tree-start .onetopic-tab-body .nav-tabs a.nav-link.active, ';
                    $selector .= '#tabs-tree-start .onetopic-tab-body .nav-tabs .nav-item.subtopic a.nav-link.active';
                    $important = true;
                    break;
                case 'parent':
                    $selector .= '#tabs-tree-start .verticaltabs .format_onetopic-tabs .nav-item.haschilds a.nav-link, ';
                    $selector .= '#tabs-tree-start .nav-tabs .nav-item.haschilds a.nav-link';
                    break;
                case 'highlighted':
                    $selector .= '#tabs-tree-start .verticaltabs .format_onetopic-tabs .nav-item.marker a.nav-link, ';
                    $selector .= '#tabs-tree-start .nav-tabs .nav-item.marker a.nav-link, ';
                    $selector .= '#tabs-tree-start .onetopic-tab-body .nav-tabs .nav-item.marker a.nav-link, ';
                    $selector .= '#tabs-tree-start .onetopic-tab-body .nav-tabs .nav-item.subtopic.marker a.nav-link';
                    $important = true;
                    break;
                case 'disabled':
                    $selector .= '#tabs-tree-start .verticaltabs .format_onetopic-tabs .nav-item.disabled a.nav-link, ';
                    $selector .= '#tabs-tree-start .nav-tabs .nav-item.disabled a.nav-link, ';
                    $selector .= '#tabs-tree-start .onetopic-tab-body .nav-tabs .nav-item.disabled a.nav-link, ';
                    $selector .= '#tabs-tree-start .onetopic-tab-body .nav-tabs .nav-item.subtopic.disabled a.nav-link';
                    $important = true;
                    break;
                case 'hover':
                    $selector .= '#tabs-tree-start .verticaltabs .format_onetopic-tabs .nav-item a.nav-link:hover, ';
                    $selector .= '#tabs-tree-start .format_onetopic-tabs.nav-tabs .nav-item a.nav-link:hover, ';
                    $selector .= '#tabs-tree-start .onetopic-tab-body .format_onetopic-tabs.nav-tabs'
                                    . ' .nav-item a.nav-link:hover';
                    break;
                case 'childs':
                    $selector .= '#tabs-tree-start .onetopic-tab-body .nav-tabs .nav-item.subtopic a.nav-link';
                    break;
                case 'childindex':
                    $selector .= '#tabs-tree-start .onetopic-tab-body .nav-tabs'
                                    . ' .nav-item.subtopic.tab_initial a.nav-link';
                    break;
                default:
                    $selector .= '#tabs-tree-start .verticaltabs .format_onetopic-tabs .nav-item a.nav-link, ';
                    $selector .= '#tabs-tree-start .nav-tabs a.nav-link';
            }

            $csscontent .= $selector . '{' . self::build_declarations($properties, $important) . '} ';
        }

        return self::sanitize_css($csscontent);
    }

    /**
     * Generate CSS from a styles object using section-specific selectors (by section ID).
     *
     * @param object $styles The styles object (with keys: default, active, parent, etc.).
     * @param int $sectionid The section ID for specific selectors.
     * @param array $tabicons Reference array to collect tab icons by type.
     * @return string The generated CSS string.
     */
    public static function generate_section_css(object $styles, int $sectionid, array &$tabicons = []): string {
        $csscontent = '';
        $cssid = '#onetabid-' . $sectionid;
        $cssparentid = '[data-tabid="' . $sectionid . '"]';

        $orderedstyles = self::order_styles($styles);

        foreach ($orderedstyles as $type => $properties) {
            $important = false;
            $selector = '';

            switch ($type) {
                case 'active':
                    $selector .= '#tabs-tree-start .nav-item' . $cssid . ' a.nav-link.active';
                    $important = true;
                    break;
                case 'parent':
                    $selector .= '#tabs-tree-start .nav-item.haschilds' . $cssid . ' a.nav-link';
                    break;
                case 'highlighted':
                    $selector .= '#tabs-tree-start .nav-item.marker' . $cssid . ' a.nav-link';
                    $important = true;
                    break;
                case 'disabled':
                    $selector .= '#tabs-tree-start .nav-item.disabled' . $cssid . ' a.nav-link';
                    $important = true;
                    break;
                case 'hover':
                    $selector .= '#tabs-tree-start .nav-item' . $cssid . ' a.nav-link:hover,';
                    $selector .= '#tabs-tree-start .onetopic-tab-body' . $cssparentid
                                . ' .nav-item.subtopic a.nav-link:hover';
                    break;
                case 'childs':
                    $selector .= '#tabs-tree-start .onetopic-tab-body' . $cssparentid
                                . ' .nav-item.subtopic a.nav-link';
                    break;
                case 'childindex':
                    $selector .= '#tabs-tree-start .onetopic-tab-body' . $cssparentid . ' .nav-tabs'
                                    . ' .nav-item.subtopic.tab_initial a.nav-link';
                    break;
                default:
                    $selector .= '#tabs-tree-start .nav-item' . $cssid . ' a.nav-link,';
                    $selector .= '#tabs-tree-start .onetopic-tab-body' . $cssparentid . ' a.nav-link';
            }

            // Extract tab icons before building declarations.
            foreach ($properties as $key => $value) {
                if ($key == 'tabicon') {
                    $tabicons[$type] = $value;
                    unset($properties->$key);
                }
            }

            $csscontent .= $selector . '{' . self::build_declarations($properties, $important) . '} ';
        }

        return self::sanitize_css($csscontent);
    }

    /**
     * Generate CSS from a styles object for subsections using generic selectors (site/course level).
     *
     * Only the 'default' type is used for subsection styles.
     *
     * @param object $styles The styles object.
     * @return string The generated CSS string.
     */
    public static function generate_subsection_generic_css(object $styles): string {
        $selector = '.format-onetopic .activity.subsection .section.course-section .section-item';
        return self::build_subsection_css($styles, $selector);
    }

    /**
     * Generate CSS from a styles object for a specific subsection (by section ID).
     *
     * Only the 'default' type is used for subsection styles.
     *
     * @param object $styles The styles object.
     * @param int $sectionid The section ID for specific selectors.
     * @return string The generated CSS string.
     */
    public static function generate_subsection_css(object $styles, int $sectionid): string {
        $selector = '.format-onetopic .activity.subsection .section.course-section[data-id="' . $sectionid . '"] .section-item';
        return self::build_subsection_css($styles, $selector);
    }

    /**
     * Build CSS for a subsection given a base selector.
     *
     * @param object $styles The styles object.
     * @param string $selector The CSS selector to apply styles to.
     * @return string The generated CSS string.
     */
    private static function build_subsection_css(object $styles, string $selector): string {
        if (!property_exists($styles, 'default') || !is_object($styles->default)) {
            return '';
        }

        $declarations = self::build_declarations($styles->default);
        $css = $selector . '{' . $declarations . '} ';

        if (property_exists($styles->default, 'color')) {
            $linkselector = $selector . ' .activityname a, ' . $selector . ' .sectionname a';
            $css .= $linkselector . '{color:' . $styles->default->color . ';} ';
        }

        return self::sanitize_css($css);
    }

    /**
     * Order styles according to the precedence order.
     *
     * @param object $styles The styles object.
     * @return object Ordered styles object.
     */
    private static function order_styles(object $styles): object {
        $ordered = new \stdClass();
        foreach (self::STYLES_PRECEDENCE as $type) {
            if (property_exists($styles, $type)) {
                $ordered->$type = $styles->$type;
            }
        }
        return $ordered;
    }

    /**
     * Build CSS declarations from a properties object, resolving units.
     *
     * @param object $properties The CSS properties object.
     * @param bool $important Whether to add !important to declarations.
     * @return string The CSS declarations string.
     */
    private static function build_declarations(object $properties, bool $important = false): string {
        $declarations = '';
        $units = [];

        // First pass: extract units.
        foreach ($properties as $key => $value) {
            if (strpos($key, 'unit-') === 0) {
                $ownerkey = str_replace('unit-', '', $key);
                $units[$ownerkey] = $value;
                unset($properties->$key);
            }
        }

        // Second pass: build declarations.
        foreach ($properties as $key => $value) {
            if (isset($units[$key])) {
                $value = $value . $units[$key];
            } else if (in_array($key, self::PROPERTIES_WITH_UNITS)) {
                $value = $value . 'px';
            }

            if ($key == 'others') {
                $declarations .= $value . ';';
            } else {
                $declarations .= $key . ':' . $value . ($important ? '!important' : '') . ';';
            }
        }

        return $declarations;
    }

    /**
     * Get sample activities data for style preview templates.
     *
     * @param \renderer_base $output The renderer to use for generating icons.
     * @return array The sample activities data.
     */
    public static function get_sampleactivities(\renderer_base $output): array {
        return [
            [
                'modname' => 'forum',
                'iconclass' => 'collaboration',
                'icon' => $output->pix_icon('monologo', '', 'mod_forum'),
                'label' => get_string('sampleforum', 'format_onetopic'),
            ],
            [
                'modname' => 'page',
                'iconclass' => 'content',
                'icon' => $output->pix_icon('monologo', '', 'mod_page'),
                'label' => get_string('samplepage', 'format_onetopic'),
            ],
            [
                'modname' => 'quiz',
                'iconclass' => 'assessment',
                'icon' => $output->pix_icon('monologo', '', 'mod_quiz'),
                'label' => get_string('samplequiz', 'format_onetopic'),
            ],
        ];
    }

    /**
     * Get the resource layout for a given section, resolving the full precedence chain.
     *
     * For sections: site (defaultsectionsappearance) → course (customappearancesection)
     *   → section (customappearancebysections) → section inline (tabstyles).
     * For subsections: site (defaultsubsectionsappearance) → course (customappearancesubsection)
     *   → subsection (customappearancebysubsections) → subsection inline (sectionstyles).
     *
     * @param \format_onetopic $format The course format instance.
     * @param \section_info $section The section to resolve the layout for.
     * @return string The resolved resource layout key, or 'default'.
     */
    public static function get_resourcelayout(\format_onetopic $format, \section_info $section): string {
        $sectionid = $section->id;

        if (isset(self::$resourcelayoutcache[$sectionid])) {
            return self::$resourcelayoutcache[$sectionid];
        }

        $issubsection = !empty($section->component);
        $course = $format->get_course();
        $resourcelayout = 'default';

        // 1. Site level.
        $siteappearancekey = $issubsection ? 'defaultsubsectionsappearance' : 'defaultsectionsappearance';
        $siteuniquecode = get_config('format_onetopic', $siteappearancekey);
        if (!empty($siteuniquecode)) {
            $sitelayout = self::get_resourcelayout_by_uniquecode($siteuniquecode);
            if ($sitelayout !== null) {
                $resourcelayout = $sitelayout;
            }
        }

        // 2. Course level.
        $courseappearanceprop = $issubsection ? 'customappearancesubsection' : 'customappearancesection';
        if (!empty($course->$courseappearanceprop)) {
            $courselayout = self::get_resourcelayout_by_uniquecode($course->$courseappearanceprop);
            if ($courselayout !== null) {
                $resourcelayout = $courselayout;
            }
        }

        // 3. Section/subsection level appearance.
        $formatoptions = $format->get_format_options($section);
        $sectionappearancekey = $issubsection ? 'customappearancebysubsections' : 'customappearancebysections';
        if (!empty($formatoptions[$sectionappearancekey])) {
            $sectionlayout = self::get_resourcelayout_by_uniquecode($formatoptions[$sectionappearancekey]);
            if ($sectionlayout !== null) {
                $resourcelayout = $sectionlayout;
            }
        }

        if (!empty($formatoptions[$resourcelayout])) {
            $resourcelayout = $formatoptions[$resourcelayout];
        }

        if (!in_array($resourcelayout, format_onetopic::RESOURCESLAYOUTS)) {
            $resourcelayout = 'default';
        }

        self::$resourcelayoutcache[$sectionid] = $resourcelayout;

        return $resourcelayout;
    }

    /**
     * Get the mustache template used to render a resource layout list.
     *
     * @param string $resourcelayout The resource layout key.
     * @return string The mustache template name.
     */
    public static function get_resourcelayout_template(string $resourcelayout): string {
        if (!in_array($resourcelayout, format_onetopic::RESOURCESLAYOUTS)) {
            $resourcelayout = 'default';
        }

        return 'format_onetopic/local/content/section/cmlist_' . $resourcelayout;
    }

    /**
     * Sanitize CSS content by removing HTML tags.
     *
     * @param string $css The CSS string to sanitize.
     * @return string Sanitized CSS.
     */
    private static function sanitize_css(string $css): string {
        $css = str_replace('<', '', $css);

        $dangerwords = [
            'expression',
            'javascript:',
            'vbscript:',
            'behavior:',
            '@import',
        ];

        $css = str_ireplace($dangerwords, '', $css);

        return $css;
    }
}
