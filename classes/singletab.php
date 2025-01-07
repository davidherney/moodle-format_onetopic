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
 * Class containing a single tab.
 *
 * @package   format_onetopic
 * @copyright 2021 David Herney - https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace format_onetopic;

/**
 * Class containing the tab information.
 *
 * @copyright 2021 David Herney - https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class singletab {

    /**
     * @var int Tab id.
     */
    public $id;

    /**
     * @var int Tab index.
     */
    public $index;

    /**
     * @var int Section index.
     */
    public $section;

    /**
     * @var string Tab HTML content.
     */
    public $content;

    /**
     * @var string Tab link.
     */
    public $link;

    /**
     * @var string Tab title.
     */
    public $title;

    /**
     * @var string Available message, in html format, if exist.
     */
    public $availablemessage;

    /**
     * @var string Custom CSS styles.
     */
    public $customstyles;

    /**
     * @var string Custom extra CSS classes.
     */
    public $specialclass;

    /**
     * @var bool If tab is selected.
     */
    public $selected = false;

    /**
     * @var bool If tab is active.
     */
    public $active = true;

    /**
     * @var string Custom CSS styles.
     */
    public $cssstyles = '';

    /**
     * @var array Icons list.
     */
    public $icons = [];

    /**
     * @var \format_onetopic\singletab Parent tab.
     */
    public $parenttab = null;

    /**
     * @var \format_onetopic\tabs Tabs childs list.
     */
    private $childs;

    /**
     * Constructor.
     *
     * @param int $section Section index.
     * @param string $content HTML tab content.
     * @param string $link Tab link.
     * @param string $title Tab title.
     * @param string $availablemessage Available message, in html format, if exist.
     * @param string $customstyles Custom CSS styles.
     * @param string $specialclass Custom extra CSS classes.
     */
    public function __construct($section, $content, $link, $title, $availablemessage = null,
                                    $customstyles = '', $specialclass = '') {

        $this->index = 0;
        $this->section = $section;
        $this->content = $content;
        $this->link = $link;
        $this->title = $title;
        $this->availablemessage = $availablemessage;
        $this->customstyles = $customstyles;
        $this->specialclass = $specialclass;

        $this->childs = new \format_onetopic\tabs();
    }

    /**
     * Add a child or sub tab.
     *
     * @param \format_onetopic\singletab $child A subtab of current tab.
     */
    public function add_child(singletab $child) {
        $child->parenttab = $this;
        $this->childs->add($child);
    }

    /**
     * Check if current tab has sub tabs.
     *
     * @return boolean True: If has sub tabs.
     */
    public function has_childs() {
        return $this->childs->has_tabs();
    }

    /**
     * Count the sub tabs.
     *
     * @return int Amount of sub tabs.
     */
    public function count_childs() {
        return $this->childs->count_tabs();
    }

    /**
     * To get the sub tabs list.
     *
     * @return \format_onetopic\tabs The sub tabs list object.
     */
    public function get_childs() {
        return $this->childs;
    }
}
