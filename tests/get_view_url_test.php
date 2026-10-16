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

/**
 * Tests for format_onetopic::get_view_url().
 *
 * @package   format_onetopic
 * @copyright 2026 David Herney - cirano. https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\format_onetopic::class)]
final class get_view_url_test extends \advanced_testcase {
    /**
     * Create an onetopic course with a subsection in section 1.
     *
     * @return array{0: \stdClass, 1: \format_onetopic, 2: \section_info, 3: \section_info}
     */
    private function create_course_with_subsection(): array {
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

        return [$course, $format, $parentsection, $subsectioninfo];
    }

    /**
     * Set the display mode for a subsection and refresh modinfo.
     *
     * @param \format_onetopic $format The course format.
     * @param \section_info $subsectioninfo The delegated section.
     * @param string $mode Display mode value.
     * @return \section_info Updated subsection info.
     */
    private function set_subsection_displaymode(
        \format_onetopic $format,
        \section_info $subsectioninfo,
        string $mode
    ): \section_info {
        // The section_format_options(true) only exposes displaymode when $section is a subsection.
        global $section;
        $section = $subsectioninfo;

        $format->update_section_format_options([
            'id' => $subsectioninfo->id,
            'displaymode' => $mode,
        ]);
        rebuild_course_cache($format->get_courseid(), true);
        $modinfo = get_fast_modinfo($format->get_courseid());
        return $modinfo->get_section_info_by_id($subsectioninfo->id, MUST_EXIST);
    }

    /**
     * List mode opens the parent tab and anchors to the subsection.
     */
    public function test_list_mode_uses_parent_section_and_anchor(): void {
        $this->resetAfterTest();

        [, $format, $parentsection, $subsectioninfo] = $this->create_course_with_subsection();
        $subsectioninfo = $this->set_subsection_displaymode(
            $format,
            $subsectioninfo,
            \format_onetopic::SUBSECTIONSDISPLAY_LIST
        );
        $this->assertEquals(\format_onetopic::SUBSECTIONSDISPLAY_LIST, $subsectioninfo->displaymode);

        $url = $format->get_view_url($subsectioninfo);

        $this->assertEquals($parentsection->section, (int)$url->get_param('section'));
        $this->assertNull($url->get_param('sectionid'));
        $this->assertEquals('#section-' . $subsectioninfo->section, $url->get_encoded_anchor());
    }

    /**
     * Summary mode keeps the subsection as its own page.
     */
    public function test_summary_mode_uses_subsection_page(): void {
        $this->resetAfterTest();

        [, $format, $parentsection, $subsectioninfo] = $this->create_course_with_subsection();
        $subsectioninfo = $this->set_subsection_displaymode(
            $format,
            $subsectioninfo,
            \format_onetopic::SUBSECTIONSDISPLAY_SUMMARY
        );
        $this->assertEquals(
            \format_onetopic::SUBSECTIONSDISPLAY_SUMMARY,
            $format->get_format_options($subsectioninfo)['displaymode']
        );
        $this->assertEquals(\format_onetopic::SUBSECTIONSDISPLAY_SUMMARY, $subsectioninfo->displaymode);

        $url = $format->get_view_url($subsectioninfo);

        $this->assertEquals($subsectioninfo->section, (int)$url->get_param('section'));
        $this->assertNotEquals($parentsection->section, (int)$url->get_param('section'));
        $this->assertSame('', $url->get_encoded_anchor());
    }

    /**
     * sr = 0 means the general section page, with an anchor to the target section.
     */
    public function test_sr_zero_is_general_section(): void {
        $this->resetAfterTest();

        [, $format, , $subsectioninfo] = $this->create_course_with_subsection();

        $url = $format->get_view_url($subsectioninfo, ['sr' => 0]);

        $this->assertEquals(0, (int)$url->get_param('section'));
        $this->assertEquals('#section-' . $subsectioninfo->section, $url->get_encoded_anchor());
    }

    /**
     * Permalink in list mode uses the parent section id and the subsection title anchor.
     */
    public function test_permalink_list_mode_uses_parent_sectionid(): void {
        $this->resetAfterTest();

        [, $format, $parentsection, $subsectioninfo] = $this->create_course_with_subsection();
        $subsectioninfo = $this->set_subsection_displaymode(
            $format,
            $subsectioninfo,
            \format_onetopic::SUBSECTIONSDISPLAY_LIST
        );

        $url = $format->get_view_url($subsectioninfo, ['permalink' => true]);

        $this->assertEquals($parentsection->id, (int)$url->get_param('sectionid'));
        $this->assertNull($url->get_param('section'));
        $this->assertEquals('#sectionid-' . $subsectioninfo->id . '-title', $url->get_encoded_anchor());
    }
}
