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
 * Class containing a basic tabs structure.
 *
 * @package   format_onetopic
 * @copyright 2021 David Herney - https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace format_onetopic;
defined('MOODLE_INTERNAL') || die();

/**
 * Class containing the tabs information.
 *
 * @copyright 2021 David Herney - https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tabs {

    /**
     * @var array Tabs list.
     */
    private $tabslist;

    /**
     * Constructor.
     *
     */
    public function __construct() {
        $this->tabslist = array();
    }

    /**
     * To get a specific tab by index.
     *
     * @param int $index The tabs position or index. Null: if not found the index.
     */
    public function get_tab($index) {
        return isset($this->tabslist[$index]) ? $this->tabslist[$index] : null;
    }

    /**
     * Add a new tab to the tabs list.
     *
     * @param \format_onetopic\singletab $tab The new instanced tab.
     */
    public function add(singletab $tab) {
        $tab->index = count($this->tabslist);
        $this->tabslist[] = $tab;
    }

    /**
     * To get the tabs list.
     *
     * @return array of \format_onetopic\singletab.
     */
    public function get_list() {
        return $this->tabslist;
    }

    /**
     * Check if exist tabs.
     *
     * @return boolean True: If has tabs.
     */
    public function has_tabs() {
        return count($this->tabslist) > 0;
    }

    /**
     * Count current tabs.
     *
     * @return int Amount of current tabs.
     */
    public function count_tabs() {
        return count($this->tabslist);
    }

}
