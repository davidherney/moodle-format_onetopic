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
 * Contains the delegated section course format output class.
 *
 * @package   format_onetopic
 * @copyright 2025 David Herney - cirano. https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_onetopic\output\courseformat\content;

use core_courseformat\output\local\content\delegatedsection as delegatedsection_base;

/**
 * Class to render a delegated section.
 *
 * @package   format_onetopic
 * @copyright 2025 David Herney - cirano. https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delegatedsection extends delegatedsection_base {
    /**
     * @var section The onetopic section output instance.
     */
    public $onesection;

    /**
     * Load the section according to the current format because the delegated class is not aware of the specific format.
     *
     */
    public function load_onesection() {
        $this->onesection = new \format_onetopic\output\courseformat\content\section($this->format, $this->section);
        $this->onesection->insection = true;
    }

    /**
     * Get the display mode of the delegated section.
     *
     * @return string
     */
    public function get_displaymode(): string {
        $options = $this->format->get_format_options($this->section);

        return $options['displaymode'] ?? 'list';
    }
}
