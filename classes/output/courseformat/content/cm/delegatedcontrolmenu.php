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

namespace format_onetopic\output\courseformat\content\cm;

use core\output\action_menu\link;
use core_courseformat\output\local\content\cm\delegatedcontrolmenu as delegatedcontrolmenubase;

/**
 * Class to render delegated section controls.
 *
 * @package   format_onetopic
 * @copyright 2026 James Calder and Otago Polytechnic
 * @copyright based on work by 2024 Amaia Anabitarte <amaia@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delegatedcontrolmenu extends delegatedcontrolmenubase {
    /**
     * Retrieves the view item for the section control menu.
     *
     * @return link|null The menu item if applicable, otherwise null.
     */
    protected function get_section_view_item(): ?link {
        return null;
    }

    /**
     * Retrieves the permalink item for the section control menu.
     *
     * @return link|null The menu item if applicable, otherwise null.
     */
    protected function get_section_permalink_item(): ?link {
        $link = parent::get_section_permalink_item();

        if ($link) {
            $link->url = $this->format->get_view_url($this->section, ['permalink' => true]);
        }

        return $link;
    }
}
