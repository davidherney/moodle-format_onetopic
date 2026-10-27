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

namespace format_onetopic;

use format_onetopic\output\courseformat\content\delegatedsection;

/**
 * Tests for the summary display of a delegated section.
 *
 * @package   format_onetopic
 * @copyright 2026 David Herney - cirano. https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(delegatedsection::class)]
final class delegatedsection_test extends \advanced_testcase {
    /**
     * Create an onetopic course with a summary subsection and one page inside it.
     *
     * @return array{0: \stdClass, 1: \format_onetopic, 2: \section_info, 3: \section_info}
     */
    private function create_summary_subsection(): array {
        $this->resetAfterTest();
        $this->setAdminUser();

        $manager = \core_plugin_manager::resolve_plugininfo_class('mod');
        $manager::enable_plugin('subsection', 1);

        $course = $this->getDataGenerator()->create_course([
            'format' => 'onetopic',
            'numsections' => 1,
        ]);
        course_create_sections_if_missing($course, [0, 1]);

        $subsection = $this->getDataGenerator()->create_module(
            'subsection',
            ['course' => $course, 'section' => 1, 'name' => 'Subsection one']
        );

        $format = course_get_format($course);
        $modinfo = get_fast_modinfo($course);
        $parentsection = $modinfo->get_section_info(1, MUST_EXIST);
        $subsectioninfo = $modinfo->get_section_info_by_component('mod_subsection', $subsection->id);

        // Function section_format_options(true) only exposes displaymode when $section is a subsection.
        global $section;
        $section = $subsectioninfo;
        $format->update_section_format_options([
            'id' => $subsectioninfo->id,
            'displaymode' => \format_onetopic::SUBSECTIONSDISPLAY_SUMMARY,
        ]);

        $this->getDataGenerator()->create_module('page', [
            'course' => $course,
            'section' => $subsectioninfo->section,
            'name' => 'Inside page',
        ]);
        rebuild_course_cache($course->id, true);

        $format = course_get_format($course);
        $modinfo = get_fast_modinfo($course);
        $parentsection = $modinfo->get_section_info(1, MUST_EXIST);
        $subsectioninfo = $modinfo->get_section_info_by_id($subsectioninfo->id, MUST_EXIST);

        return [$course, $format, $parentsection, $subsectioninfo];
    }

    /**
     * Export and render the delegated section for the given current section.
     *
     * @param \stdClass $course The course.
     * @param \format_onetopic $format The course format.
     * @param \section_info $subsectioninfo The delegated section.
     * @param int $sectionnum The section number currently displayed.
     * @param bool $editing Whether editing is on.
     * @return array{0: \stdClass, 1: string}
     */
    private function render_subsection(
        \stdClass $course,
        \format_onetopic $format,
        \section_info $subsectioninfo,
        int $sectionnum,
        bool $editing
    ): array {
        global $PAGE, $USER;

        $PAGE->set_url('/course/view.php', ['id' => $course->id]);
        $PAGE->set_pagetype('course-view-onetopic');
        $PAGE->set_course($course);
        $format->currentscope = \format_onetopic::SCOPE_COURSE;
        $USER->editing = $editing ? 1 : 0;
        $format->set_sectionnum($sectionnum);

        $widget = new delegatedsection($format, $subsectioninfo);
        $renderer = $PAGE->get_renderer('format_onetopic');
        $data = $widget->export_for_template($renderer);

        return [$data, $renderer->render($widget)];
    }

    /**
     * On the parent tab the subsection is a summary card with a link and no activity list.
     */
    public function test_summary_on_parent_section_exports_activity_counter(): void {
        [$course, $format, $parentsection, $subsectioninfo] = $this->create_summary_subsection();

        [$data, $html] = $this->render_subsection(
            $course,
            $format,
            $subsectioninfo,
            $parentsection->section,
            false
        );

        $this->assertTrue($data->onlysummary);
        $this->assertObjectHasProperty('cmsummary', $data);
        $this->assertFalse(property_exists($data, 'cmlist') && !empty($data->cmlist));
        $this->assertStringContainsString('section-summary-activities', $html);
        $this->assertStringContainsString('section_goto', $html);
        $this->assertStringContainsString('section=' . $subsectioninfo->section, $html);
        $this->assertStringNotContainsString('Inside page', $html);
        $this->assertStringNotContainsString('sectiontoggler', $html);
    }

    /**
     * The subsection page lists its activities and does not repeat the summary card.
     */
    public function test_summary_on_own_page_exports_activity_list(): void {
        [$course, $format, , $subsectioninfo] = $this->create_summary_subsection();

        [$data, $html] = $this->render_subsection(
            $course,
            $format,
            $subsectioninfo,
            $subsectioninfo->section,
            false
        );

        $this->assertTrue(empty($data->onlysummary));
        $this->assertObjectHasProperty('cmlist', $data);
        $this->assertFalse(property_exists($data, 'cmsummary') && !empty($data->cmsummary));
        $this->assertStringContainsString('Inside page', $html);
        $this->assertStringNotContainsString('section_goto', $html);
        $this->assertStringNotContainsString('sectiontoggler', $html);
    }

    /**
     * Editing shows the activity list so activities inside the subsection can be changed.
     */
    public function test_editing_exports_activity_list(): void {
        [$course, $format, $parentsection, $subsectioninfo] = $this->create_summary_subsection();

        [$data, $html] = $this->render_subsection(
            $course,
            $format,
            $subsectioninfo,
            $parentsection->section,
            true
        );

        $this->assertTrue(empty($data->onlysummary));
        $this->assertObjectHasProperty('cmlist', $data);
        $this->assertStringContainsString('Inside page', $html);
        $this->assertStringNotContainsString('section_goto', $html);
    }
}
