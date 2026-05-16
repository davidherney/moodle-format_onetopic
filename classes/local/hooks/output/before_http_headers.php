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

namespace format_onetopic\local\hooks\output;

/**
 * Hook callbacks for format_onetopic
 *
 * @package    format_onetopic
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class before_http_headers {
    /**
     * Moodle native lib/navigationlib.php calls this hook allowing us to override UI.
     *
     * @param \core\hook\output\before_http_headers $unused
     */
    public static function callback(\core\hook\output\before_http_headers $unused): void {
        global $PAGE, $COURSE;

        // Don't require styles script if the course format isn't 'onetopic'.
        if ($PAGE->course && isset($COURSE->id) && $COURSE->format == 'onetopic') {
            $sectionsappearance = get_config('format_onetopic', 'defaultsectionsappearance');
            $subsectionsappearance = get_config('format_onetopic', 'defaultsubsectionsappearance');

            // Check if any site-wide default appearance is configured, if not, do nothing.
            if (empty($sectionsappearance) && empty($subsectionsappearance)) {
                return;
            }

            $revision = self::get_appearance_revision();
            $PAGE->requires->css(new \core\url('/course/format/onetopic/styles.php', [
                'revision' => $revision,
            ]));
        }
    }

    /**
     * Generates an 8-character hash from the default appearances.
     * When the appearance changes, the hash changes, creating a new URL that
     * busts the cache.
     *
     * @return string
     */
    public static function get_appearance_revision(): string {
        $sectionsappearance = get_config('format_onetopic', 'defaultsectionsappearance');
        $subsectionsappearance = get_config('format_onetopic', 'defaultsubsectionsappearance');

        if (empty($sectionsappearance) && empty($subsectionsappearance)) {
            return '0';
        }

        $hashdata = '';
        if (!empty($sectionsappearance)) {
            $hashdata .= $sectionsappearance
                . \format_onetopic\local\appearances::get_timemodified_by_uniquecode($sectionsappearance);
        }
        if (!empty($subsectionsappearance)) {
            $hashdata .= $subsectionsappearance
                . \format_onetopic\local\appearances::get_timemodified_by_uniquecode($subsectionsappearance);
        }

        // Use first 8 chars of md5 hash as revision.
        return substr(md5($hashdata), 0, 8);
    }
}
