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

use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for the generated tab CSS.
 *
 * @package   format_onetopic
 * @copyright 2026 Peter Mayer
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(header::class)]
final class header_test extends \advanced_testcase {
    /**
     * Create a course with three sections using the given tabstyles and return the generated CSS.
     *
     * @param array $others Value of the "others" rule for each of the three sections.
     * @return string The CSS passed to the template.
     */
    private function get_generated_css(array $others): string {
        global $PAGE;

        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('enablecustomstyles', 1, 'format_onetopic');

        $course = $this->getDataGenerator()->create_course(['format' => 'onetopic', 'numsections' => 3]);
        $format = course_get_format($course);
        foreach ($others as $number => $value) {
            $section = get_fast_modinfo($course)->get_section_info($number + 1);
            $tabstyles = json_encode(['default' => ['others' => $value]]);
            $format->update_section_format_options(['id' => $section->id, 'tabstyles' => $tabstyles]);
        }
        rebuild_course_cache($course->id, true);

        $PAGE->set_url('/course/view.php', ['id' => $course->id]);
        $PAGE->set_course($course);
        $format = course_get_format($course);
        $header = new header($format);
        $data = $header->export_for_template($PAGE->get_renderer('format_onetopic'));

        return $data->cssstyles;
    }

    /**
     * Markup split over several sections must not end up in the generated CSS.
     */
    public function test_cssstyles_cannot_break_out_of_style_element_violation(): void {
        $css = $this->get_generated_css(['</style ', '><img src=x onerror=alert(1)//', '>']);

        $this->assertStringNotContainsString('<', $css);
        $this->assertStringNotContainsString('</style', $css);
    }

    /**
     * Regular rules must still be generated.
     */
    public function test_cssstyles_regular_rules_are_kept_verification(): void {
        $css = $this->get_generated_css(['color: red', 'text-decoration: underline', 'margin-top: 1px']);

        $this->assertStringContainsString('color: red;', $css);
        $this->assertStringContainsString('text-decoration: underline;', $css);
        $this->assertStringContainsString('margin-top: 1px;', $css);
    }
}
