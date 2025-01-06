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
        $this->tabslist = [];
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
     * @param bool $assubtabs It's a subtabs list.
     * @return array of object.
     */
    public function get_list(bool $assubtabs = false): array {

        $tabstree = [];

        $anchortotabstree = get_config('format_onetopic', 'anchortotabstree');

        foreach ($this->tabslist as $tab) {

            if ($assubtabs && strpos($tab->specialclass, ' subtopic ') === false) {
                $tab->specialclass .= ' subtopic ';
            }

            $newtab = new \stdClass();
            $newtab->link = $tab->link . ($anchortotabstree ? '#tabs-tree-start' : '');
            $newtab->title = $tab->title;
            $newtab->text = $tab->content;
            $newtab->active = $tab->selected;
            $newtab->inactive = !$tab->active;
            // The new CSS styles feature ovewrite the custom styles.
            $newtab->styles = empty($tab->cssstyles) ? $tab->customstyles : '';
            $newtab->specialclass = $tab->specialclass;
            $newtab->availablemessage = $tab->availablemessage;
            $newtab->uniqueid = 'tab-' . time() . '-' . rand(0, 1000);
            $newtab->id = !empty($tab->id) ? $tab->id : null;

            if (!$assubtabs && $tab->has_childs()) {
                $newtab->secondrow = $tab->get_childs()->get_list(true);
                $newtab->haschilds = true;
            }

            $tabstree[] = $newtab;
        }

        return $tabstree;
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

    /**
     * To get the second tabs list according the selected tab.
     *
     * @return object With list of tabs in a tabs attribute.
     */
    public function get_secondlist(): object {

        $tabstree = new \stdClass();
        $tabstree->tabs = [];

        foreach ($this->tabslist as $tab) {
            if ($tab->selected) {
                $tabstree->tabs = $tab->get_childs()->get_list(true);
            }
        }

        return $tabstree;
    }

    /**
     * To get the CSS styles of the tabs, including childs.
     *
     * @return string CSS styles.
     */
    public function get_allcssstyles(): string {
        $css = [];
        foreach ($this->tabslist as $tab) {
            $css[] = $tab->cssstyles;
            if ($tab->selected) {
                $css[] = $tab->get_childs()->get_allcssstyles();
            }
        }

        return implode(' ', $css);
    }

    /**
     * To get the active tab.
     *
     * @return \format_onetopic\singletab The active tab.
     */
    public function get_active(): ?singletab {
        foreach ($this->tabslist as $tab) {
            if ($tab->selected) {
                return $tab;
            }
        }
        return null;
    }

}
