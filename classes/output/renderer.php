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

namespace format_onetopic\output;

use core_courseformat\output\section_renderer;
use moodle_page;

/**
 * Basic renderer for topics format.
 *
 * @package    format_onetopic
 * @copyright  2022 David Herney Bernal - cirano
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends section_renderer {

    /**
     * Constructor method, calls the parent constructor.
     *
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        parent::__construct($page, $target);

        // Since format_topics_renderer::section_edit_control_items() only displays the 'Highlight' control
        // when editing mode is on we need to be sure that the link 'Turn editing mode on' is available for a user
        // who does not have any other managing capability.
        $page->set_other_editing_capability('moodle/course:setcurrentsection');
    }

    /**
     * Renders the provided widget and returns the HTML to display it.
     *
     * Course format templates uses a similar subfolder structure to the renderable classes.
     * This method find out the specific template for a course widget. That's the reason why
     * this render method is different from the normal plugin renderer one.
     *
     * course format templatables can be rendered using the core_course/local/* templates.
     * Format plugins are free to override the default template location using render_xxx methods as usual.
     *
     * @param \renderable $widget instance with renderable interface
     * @return string the widget HTML
     */
    public function render(\renderable $widget) {
        global $CFG;
        $fullpath = str_replace('\\', '/', get_class($widget));

        // Check for special course format templatables.
        if ($widget instanceof \templatable) {
            // Templatables from both core_courseformat\output\local\* and format_onetopic\output\courseformat\*
            // use format_onetopic/local/* templates, if they exist.
            $corepath = 'core_courseformat\/output\/local';
            $pluginpath = 'format_onetopic\/output\/courseformat';
            $specialrenderers = '/^(?<componentpath>' . $corepath . '|' . $pluginpath . ')\/(?<template>.+)$/';
            $matches = null;

            if (preg_match($specialrenderers, $fullpath, $matches)
                && file_exists($CFG->dirroot . '/course/format/onetopic/templates/local/' . $matches['template'])) {
                $data = $widget->export_for_template($this);
                return $this->render_from_template('format_onetopic/local/' . $matches['template'], $data);
            }
        }

        // If it doesn't work, let the parent class decide.
        return parent::render($widget);
    }

    /**
     * Generate the section title, wraps it in a link to the section page if page is to be displayed on a separate page.
     *
     * @param section_info|stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title($section, $course) {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section));
    }

    /**
     * Generate the section title to be displayed on the section page, without a link.
     *
     * @param section_info|stdClass $section The course_section entry from DB
     * @param int|stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title_without_link($section, $course) {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section, false));
    }
}
