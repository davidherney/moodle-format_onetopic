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

use format_onetopic\local\hooks\output\before_http_headers;

/**
 * Tests for styles.php caching functionality.
 *
 * @package   format_onetopic
 * @author    Jonathan Archer <jonathanarcher@catalyst-au.net>
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \format_onetopic\local\hooks\output\before_http_headers::get_tabstyles_revision
 */
final class styles_caching_test extends \advanced_testcase {
    /**
     * Test revision is '0' when no tab styles configured.
     */
    public function test_revision_empty_when_no_styles(): void {
        $this->resetAfterTest(true);

        set_config('tabstyles', '', 'format_onetopic');

        $revision = before_http_headers::get_tabstyles_revision();
        $this->assertEquals('0', $revision);
    }

    /**
     * Test revision is valid hash when tab styles exist.
     */
    public function test_revision_is_hash_when_styles_exist(): void {
        $this->resetAfterTest(true);

        $tabstyles = json_encode(['default' => ['color' => 'red']]);
        set_config('tabstyles', $tabstyles, 'format_onetopic');

        $revision = before_http_headers::get_tabstyles_revision();

        $this->assertIsString($revision);
        $this->assertEquals(8, strlen($revision));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{8}$/', $revision);
    }

    /**
     * Test revision changes when tab styles change.
     */
    public function test_revision_changes_when_styles_change(): void {
        $this->resetAfterTest(true);

        $tabstyles1 = json_encode(['default' => ['color' => 'red']]);
        set_config('tabstyles', $tabstyles1, 'format_onetopic');
        $revision1 = before_http_headers::get_tabstyles_revision();

        $tabstyles2 = json_encode(['default' => ['color' => 'blue']]);
        set_config('tabstyles', $tabstyles2, 'format_onetopic');
        $revision2 = before_http_headers::get_tabstyles_revision();

        $this->assertNotEquals($revision1, $revision2);
    }

    /**
     * Test revision stays same for identical styles.
     */
    public function test_revision_consistent_for_same_styles(): void {
        $this->resetAfterTest(true);

        $tabstyles = json_encode(['default' => ['color' => 'red']]);
        set_config('tabstyles', $tabstyles, 'format_onetopic');

        $revision1 = before_http_headers::get_tabstyles_revision();
        $revision2 = before_http_headers::get_tabstyles_revision();

        $this->assertEquals($revision1, $revision2);
    }

    /**
     * Test revision is deterministic (same input = same hash).
     */
    public function test_revision_is_deterministic(): void {
        $this->resetAfterTest(true);

        $tabstyles = json_encode(['default' => ['color' => 'red']]);
        set_config('tabstyles', $tabstyles, 'format_onetopic');

        $expected = substr(md5($tabstyles), 0, 8);
        $actual = before_http_headers::get_tabstyles_revision();

        $this->assertEquals($expected, $actual);
    }
}
